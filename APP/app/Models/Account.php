<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Account extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'account_number',
        'member_id',
        'accountable_type',
        'accountable_id',
        'status',
        'closure_reason',
        'closed_at',
        'closed_by',
        'last_transaction_date',
        'opening_date'
    ];

    /**
     * Attributes that should never be mass-assigned
     */
    protected $guarded = [
        'tenant_id',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
        'last_transaction_date' => 'datetime',
        'opening_date' => 'datetime',
    ];

    /**
     * Generate unique account number with type-specific prefix
     */
    protected static function boot()
    {
        parent::boot();

        // Set temporary unique placeholder to satisfy NOT NULL/UNIQUE constraints
        static::creating(function ($account) {
            if (empty($account->account_number)) {
                // Temporary UUID-based placeholder
                $account->account_number = 'TMP-' . \Illuminate\Support\Str::uuid();
            }
        });

        // Replace with final account number after insert (using actual ID)
        static::created(function ($account) {
            if (str_starts_with($account->account_number, 'TMP-')) {
                // Determine prefix based on accountable_type
                $prefix = match($account->accountable_type) {
                    SavingsAccount::class => 'SAV',
                    LoanAccount::class => 'LN',
                    ShareAccount::class => 'SHR',
                    default => 'ACC',
                };

                $accountNumber = $prefix . '-' . str_pad(
                    $account->id,
                    8,
                    '0',
                    STR_PAD_LEFT
                );

                // Direct DB update to avoid firing extra model events
                \Illuminate\Support\Facades\DB::table($account->getTable())
                    ->where('id', $account->id)
                    ->update(['account_number' => $accountNumber]);

                // Update in-memory model
                $account->account_number = $accountNumber;
            }
        });
    }

    /**
     * Member who owns this account
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    /**
     * Get the underlying accountable model (SavingsAccount, LoanAccount, or ShareAccount)
     */
    public function accountable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * User who closed this account
     */
    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Transactions for this account
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Helper: Check if this is a savings account
     */
    public function isSavingsAccount(): bool
    {
        return $this->accountable_type === SavingsAccount::class;
    }

    /**
     * Helper: Check if this is a loan account
     */
    public function isLoanAccount(): bool
    {
        return $this->accountable_type === LoanAccount::class;
    }

    /**
     * Helper: Check if this is a share account
     */
    public function isShareAccount(): bool
    {
        return $this->accountable_type === ShareAccount::class;
    }

    /**
     * Helper: Get the account type as a string
     */
    public function getAccountTypeAttribute(): string
    {
        return match($this->accountable_type) {
            SavingsAccount::class => 'savings',
            LoanAccount::class => 'loan',
            ShareAccount::class => 'share',
            default => 'unknown',
        };
    }

    /**
     * Helper: Delegate balance updates to accountable model
     */
    public function updateBalance(float $amount, string $type = 'credit'): void
    {
        if ($this->accountable && method_exists($this->accountable, 'updateBalance')) {
            $this->accountable->updateBalance($amount, $type);
        }
    }

    /**
     * Helper: Delegate withdrawal check to accountable model
     */
    public function canWithdraw(float $amount): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->accountable && method_exists($this->accountable, 'canWithdraw')) {
            return $this->accountable->canWithdraw($amount);
        }

        return false;
    }

    /**
     * Scope: Get accounts by type
     */
    public function scopeOfType($query, string $type)
    {
        $modelClass = match($type) {
            'savings' => SavingsAccount::class,
            'loan' => LoanAccount::class,
            'share' => ShareAccount::class,
            default => null,
        };

        if ($modelClass) {
            return $query->where('accountable_type', $modelClass);
        }

        return $query;
    }

    /**
     * Check if this is a wallet account
     */
    public function isWalletAccount(): bool
    {
        if (!$this->isSavingsAccount()) {
            return false;
        }

        $savingsAccount = $this->accountable;
        if (!$savingsAccount || !$savingsAccount->savingsProduct) {
            return false;
        }

        return $savingsAccount->savingsProduct->code === 'WL001' ||
               $savingsAccount->savingsProduct->type === 'wallet';
    }

    /**
     * Get wallet balance (if wallet account)
     */
    public function getWalletBalance(): ?float
    {
        if (!$this->isWalletAccount()) {
            return null;
        }

        return $this->accountable->balance ?? 0;
    }

    /**
     * Get savings product (if savings account)
     */
    public function getSavingsProduct()
    {
        if (!$this->isSavingsAccount()) {
            return null;
        }

        return $this->accountable->savingsProduct ?? null;
    }

    /**
     * Scope: Get wallet accounts only
     */
    public function scopeWalletAccounts($query)
    {
        return $query->whereHasMorph('accountable', [SavingsAccount::class], function($q) {
            $q->whereHas('savingsProduct', function($q2) {
                $q2->where('code', 'WL001')
                   ->orWhere('type', 'wallet');
            });
        });
    }
}
