<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'member_id',
        'account_id',
        'type',
        'category',
        'amount',
        'fee_amount',
        'net_amount',
        'balance_before',
        'balance_after',
        'description',
        'payment_method',
        'payment_reference',
        'status',
        'transaction_date',
        'value_date',
        'related_loan_id',
        'related_account_id',
        'reversal_reason',
        'reversed_by',
        'reversed_at',
        'processed_by',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'transaction_date' => 'datetime',
        'value_date' => 'datetime',
        'reversed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Generate unique transaction number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_number)) {
                $transaction->transaction_number = 'TXN' . str_pad(
                    (static::max('id') ?? 0) + 1,
                    10,
                    '0',
                    STR_PAD_LEFT
                );
            }
        });
    }

    /**
     * Member who made this transaction
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    /**
     * Account for this transaction
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Related loan if applicable
     */
    public function relatedLoan(): BelongsTo
    {
        return $this->belongsTo(Loan::class, 'related_loan_id');
    }

    /**
     * Related account for transfers
     */
    public function relatedAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'related_account_id');
    }

    /**
     * User who processed this transaction
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * User who reversed this transaction
     */
    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    /**
     * Get completed transactions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Get transactions by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get transactions by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * General ledger entries for this transaction
     */
    public function generalLedgerEntries(): HasMany
    {
        return $this->hasMany(GeneralLedger::class, 'reference_id')
            ->where('reference_type', 'Transaction');
    }
}