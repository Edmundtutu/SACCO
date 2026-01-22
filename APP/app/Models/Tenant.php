<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sacco_code',
        'sacco_name',
        'slug',
        'email',
        'phone',
        'address',
        'country',
        'currency',
        'subscription_plan',
        'status',
        'trial_ends_at',
        'subscription_starts_at',
        'subscription_ends_at',
        'max_members',
        'max_staff',
        'max_loans',
        'max_loan_amount',
        'enabled_features',
        'settings',
        'owner_name',
        'owner_email',
        'owner_phone',
        'notes',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'subscription_starts_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'max_loan_amount' => 'decimal:2',
        'enabled_features' => 'array',
        'settings' => 'array',
    ];

    /**
     * Generate unique SACCO code
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tenant) {
            if (empty($tenant->sacco_code)) {
                $tenant->sacco_code = 'SAC' . str_pad(
                    (static::max('id') ?? 0) + 1,
                    6,
                    '0',
                    STR_PAD_LEFT
                );
            }
            
            if (empty($tenant->slug)) {
                $tenant->slug = \Illuminate\Support\Str::slug($tenant->sacco_name);
            }
        });
    }

    /**
     * Users belonging to this tenant
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    /**
     * Accounts belonging to this tenant
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'tenant_id');
    }

    /**
     * Loans belonging to this tenant
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'tenant_id');
    }

    /**
     * Transactions belonging to this tenant
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'tenant_id');
    }

    /**
     * Check if tenant is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if tenant is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if tenant is on trial
     */
    public function isOnTrial(): bool
    {
        return $this->status === 'trial' && 
               $this->trial_ends_at && 
               $this->trial_ends_at->isFuture();
    }

    /**
     * Check if tenant trial has expired
     */
    public function trialExpired(): bool
    {
        return $this->status === 'trial' && 
               $this->trial_ends_at && 
               $this->trial_ends_at->isPast();
    }

    /**
     * Check if tenant can operate
     */
    public function canOperate(): bool
    {
        return in_array($this->status, ['active', 'trial']) && !$this->trialExpired();
    }

    /**
     * Check if feature is enabled for this tenant
     */
    public function hasFeature(string $feature): bool
    {
        if (!$this->enabled_features) {
            return false;
        }

        return in_array($feature, $this->enabled_features);
    }

    /**
     * Get a setting value
     */
    public function getSetting(string $key, $default = null)
    {
        if (!$this->settings) {
            return $default;
        }

        return $this->settings[$key] ?? $default;
    }

    /**
     * Set a setting value
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Check if tenant has reached member limit
     */
    public function hasReachedMemberLimit(): bool
    {
        return $this->users()->where('role', 'member')->count() >= $this->max_members;
    }

    /**
     * Check if tenant has reached staff limit
     */
    public function hasReachedStaffLimit(): bool
    {
        return $this->users()
            ->whereIn('role', ['admin', 'staff_level_1', 'staff_level_2', 'staff_level_3'])
            ->count() >= $this->max_staff;
    }

    /**
     * Check if tenant has reached loan limit
     */
    public function hasReachedLoanLimit(): bool
    {
        return $this->loans()
            ->whereIn('status', ['pending', 'approved', 'disbursed', 'active'])
            ->count() >= $this->max_loans;
    }

    /**
     * Scope: Active tenants only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Operational tenants (active or on trial)
     */
    public function scopeOperational($query)
    {
        return $query->whereIn('status', ['active', 'trial'])
            ->where(function ($q) {
                $q->where('status', 'active')
                  ->orWhere(function ($q2) {
                      $q2->where('status', 'trial')
                         ->where('trial_ends_at', '>', now());
                  });
            });
    }
}
