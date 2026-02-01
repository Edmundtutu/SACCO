<?php

use App\Models\Tenant;
use App\Services\TenantContext;

if (!function_exists('tenant')) {
    /**
     * Get the current tenant instance
     *
     * @return Tenant|null
     */
    function tenant(): ?Tenant
    {
        return app(TenantContext::class)->getTenant();
    }
}

if (!function_exists('tenantId')) {
    /**
     * Get the current tenant ID
     *
     * @return int|null
     */
    function tenantId(): ?int
    {
        return app(TenantContext::class)->getTenantId();
    }
}

if (!function_exists('setTenant')) {
    /**
     * Set the current tenant
     *
     * @param Tenant|int|null $tenant
     * @return void
     */
    function setTenant($tenant): void
    {
        app(TenantContext::class)->setTenant($tenant);
    }
}

if (!function_exists('clearTenant')) {
    /**
     * Clear the current tenant
     *
     * @return void
     */
    function clearTenant(): void
    {
        app(TenantContext::class)->clearTenant();
    }
}

if (!function_exists('isSuperAdmin')) {
    /**
     * Check if the current user is a super admin
     *
     * @return bool
     */
    function isSuperAdmin(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Super admin check: role is 'super_admin' or user has super_admin flag
        return $user->role === 'super_admin' ||
            ($user->is_super_admin ?? false);
    }
}

if (!function_exists('canBypassTenantScope')) {
    /**
     * Check if the current context allows bypassing tenant scope
     *
     * @return bool
     */
    function canBypassTenantScope(): bool
    {
        return isSuperAdmin() && app()->runningInConsole();
    }
}
