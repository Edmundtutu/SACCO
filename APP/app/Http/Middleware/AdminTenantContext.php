<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;

class AdminTenantContext
{
    /**
     * Resolve and inject tenant context for admin web requests.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $tenantId = null;

        // Tenant-bound admins rely on their own tenant ID
        if ($user && $this->isTenantStaff($user)) {
            $tenantId = $user->tenant_id;
        }

        // Super admin may impersonate tenants via the session switcher
        if (!$tenantId && $user && $this->isSuperAdmin($user)) {
            $tenantId = (int) $request->session()->get('admin_tenant_id');
        }

        if ($tenantId) {
            $tenant = Tenant::find($tenantId);

            if (!$tenant) {
                $this->clearTenantSession($request);
                return abort(404, 'Selected tenant is unavailable.');
            }

            if (!$tenant->canOperate()) {
                $this->clearTenantSession($request);
                return abort(403, 'Tenant is not permitted to operate currently.');
            }

            setTenant($tenant);
        } else {
            clearTenant();
        }

        return $next($request);
    }

    private function isTenantStaff($user): bool
    {
        return in_array($user->role, ['admin', 'staff_level_1', 'staff_level_2', 'staff_level_3'], true);
    }

    private function isSuperAdmin($user): bool
    {
        return $user->role === 'super_admin' || ($user->is_super_admin ?? false);
    }

    private function clearTenantSession(Request $request): void
    {
        $request->session()->forget('admin_tenant_id');
        clearTenant();
    }
}
