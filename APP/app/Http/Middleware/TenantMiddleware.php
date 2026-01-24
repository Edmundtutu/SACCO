<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * This middleware resolves the tenant from:
     * 1. Authenticated user's tenant_id (PRIMARY METHOD)
     * 2. X-Tenant-ID header (only for super-admin or unauthenticated flows)
     * 3. Request parameter (only for registration/invitation flows)
     *
     * ❌ NEVER uses domain or subdomain for tenant resolution
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\ResponseRedirect)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\ResponseRedirect
     */
    public function handle(Request $request, Closure $next)
    {
        $tenantId = null;
        $tenant = null;

        // Strategy 1: Resolve tenant from authenticated user (PRIMARY)
        $user = auth()->user();
        if ($user && isset($user->tenant_id)) {
            $tenantId = $user->tenant_id;
        }

        // Strategy 2: Resolve from X-Tenant-ID header (for super-admin or specific flows)
        if (!$tenantId && $request->header('X-Tenant-ID')) {
            $headerTenantId = $request->header('X-Tenant-ID');
            
            // Only allow header-based tenant resolution in specific cases:
            // 1. Super-admin users
            // 2. Unauthenticated requests to specific routes (registration, invitations)
            if ($this->canUseHeaderTenantId($request, $user)) {
                $tenantId = $headerTenantId;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }
        }

        // Strategy 3: Resolve from request parameter (ONLY for registration/invitation)
        if (!$tenantId && $request->input('tenant_id')) {
            if ($this->isAllowedTenantParameterRoute($request)) {
                $tenantId = $request->input('tenant_id');
            }
        }

        // If we still don't have a tenant_id, check if it's required for this route
        if (!$tenantId) {
            // Allow certain routes to proceed without tenant (e.g., tenant creation by super-admin)
            if ($this->isExemptFromTenantRequirement($request)) {
                return $next($request);
            }

            return response()->json([
                'success' => false,
                'message' => 'Tenant context could not be resolved'
            ], 400);
        }

        // Load the tenant
        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid tenant'
            ], 404);
        }

        // Check if tenant can operate
        if (!$tenant->canOperate()) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant is ' . $tenant->status . '. ' . 
                            ($tenant->trialExpired() ? 'Trial period has expired.' : 'Please contact support.'),
                'tenant_status' => $tenant->status
            ], 403);
        }

        // Set tenant in the application context
        setTenant($tenant);

        return $next($request);
    }

    /**
     * Check if the request can use X-Tenant-ID header
     */
    protected function canUseHeaderTenantId(Request $request, $user): bool
    {
        // Super-admin can always use header
        if ($user && ($user->role === 'super_admin' || ($user->is_super_admin ?? false))) {
            return true;
        }

        // Allow for unauthenticated requests to specific routes
        if (!$user) {
            return $this->isUnauthenticatedTenantRoute($request);
        }

        return false;
    }

    /**
     * Check if route is allowed to use tenant parameter
     */
    protected function isAllowedTenantParameterRoute(Request $request): bool
    {
        $allowedRoutes = [
            'api/auth/register',
            'api/auth/invitation/accept',
            'api/auth/join',
        ];

        $path = $request->path();
        
        foreach ($allowedRoutes as $allowedRoute) {
            if (str_starts_with($path, $allowedRoute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if route is unauthenticated and tenant-aware
     */
    protected function isUnauthenticatedTenantRoute(Request $request): bool
    {
        $allowedRoutes = [
            'api/tenants/info',
            'api/public/',
        ];

        $path = $request->path();
        
        foreach ($allowedRoutes as $allowedRoute) {
            if (str_starts_with($path, $allowedRoute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if route is exempt from tenant requirement
     */
    protected function isExemptFromTenantRequirement(Request $request): bool
    {
        $exemptRoutes = [
            'api/tenants/create',
            'api/super-admin/',
            'api/health',
        ];

        $path = $request->path();
        
        foreach ($exemptRoutes as $exemptRoute) {
            if (str_starts_with($path, $exemptRoute)) {
                return true;
            }
        }

        return false;
    }
}
