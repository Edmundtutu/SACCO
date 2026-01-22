<?php

namespace App\Services;

use App\Models\Tenant;

class TenantContext
{
    /**
     * The current tenant instance
     */
    protected ?Tenant $tenant = null;

    /**
     * The current tenant ID
     */
    protected ?int $tenantId = null;

    /**
     * Get the current tenant
     */
    public function getTenant(): ?Tenant
    {
        return $this->tenant;
    }

    /**
     * Get the current tenant ID
     */
    public function getTenantId(): ?int
    {
        return $this->tenantId;
    }

    /**
     * Set the current tenant
     */
    public function setTenant($tenant): void
    {
        if ($tenant instanceof Tenant) {
            $this->tenant = $tenant;
            $this->tenantId = $tenant->id;
        } elseif (is_int($tenant)) {
            $this->tenantId = $tenant;
            $this->tenant = Tenant::find($tenant);
        } elseif (is_null($tenant)) {
            $this->clearTenant();
        }
    }

    /**
     * Clear the current tenant
     */
    public function clearTenant(): void
    {
        $this->tenant = null;
        $this->tenantId = null;
    }

    /**
     * Check if tenant is set
     */
    public function hasTenant(): bool
    {
        return $this->tenantId !== null;
    }
}
