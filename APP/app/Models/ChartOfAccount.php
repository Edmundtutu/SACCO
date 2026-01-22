<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'code',
        'name',
        'type',
        'parent_code',
        'level',
        'is_active',
        'description',
        'normal_balance',
        'is_system_account',
    ];

    protected $casts = [
        'level' => 'integer',
        'is_active' => 'boolean',
        'is_system_account' => 'boolean',
    ];

    /**
     * Parent account
     */
    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_code', 'code');
    }

    /**
     * Child accounts
     */
    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_code', 'code');
    }

    /**
     * General ledger entries for this account
     */
    public function generalLedgerEntries(): HasMany
    {
        return $this->hasMany(GeneralLedger::class, 'account_code', 'code');
    }

    /**
     * Get active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get accounts by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get root level accounts (no parent)
     */
    public function scopeRootLevel($query)
    {
        return $query->whereNull('parent_code');
    }

    /**
     * Get leaf accounts (no children)
     */
    public function scopeLeafAccounts($query)
    {
        return $query->whereDoesntHave('children');
    }

    /**
     * Get account balance (debits - credits)
     */
    public function getBalance(): float
    {
        $debits = $this->generalLedgerEntries()->sum('debit_amount');
        $credits = $this->generalLedgerEntries()->sum('credit_amount');
        
        return $debits - $credits;
    }

    /**
     * Check if account is a debit account
     */
    public function isDebitAccount(): bool
    {
        return in_array($this->type, ['asset', 'expense']);
    }

    /**
     * Check if account is a credit account
     */
    public function isCreditAccount(): bool
    {
        return in_array($this->type, ['liability', 'equity', 'income']);
    }

    /**
     * Get full account path (e.g., "1000 - Assets > 1100 - Current Assets > 1101 - Cash")
     */
    public function getFullPath(): string
    {
        $path = $this->code . ' - ' . $this->name;
        
        if ($this->parent) {
            $path = $this->parent->getFullPath() . ' > ' . $path;
        }
        
        return $path;
    }
}
