<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    /**
     * Boot the trait
     */
    protected static function bootBelongsToTenant()
    {
        // Automatically set tenant_id when creating a new model
        static::creating(function (Model $model) {
            if (!$model->tenant_id && tenantId()) {
                $model->tenant_id = tenantId();
            }
        });

        // Apply global scope to all queries to filter by tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (tenantId()) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', tenantId());
            }
        });
    }

    /**
     * Relationship to tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Scope: Query without tenant scope (bypass for super-admin)
     */
    public function scopeWithoutTenantScope(Builder $builder)
    {
        return $builder->withoutGlobalScope('tenant');
    }

    /**
     * Scope: Query for a specific tenant
     */
    public function scopeForTenant(Builder $builder, int $tenantId)
    {
        return $builder->withoutGlobalScope('tenant')
            ->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
    }
}
