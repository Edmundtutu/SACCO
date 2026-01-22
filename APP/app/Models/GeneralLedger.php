<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneralLedger extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'general_ledger';

    protected $fillable = [
        'transaction_id',
        'transaction_date',
        'account_code',
        'account_name',
        'account_type',
        'debit_amount',
        'credit_amount',
        'description',
        'reference_type',
        'reference_id',
        'member_id',
        'batch_id',
        'status',
        'posted_by',
        'posted_at',
        'reversed_by',
        'reversed_at',
        'reversal_reason',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'posted_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    /**
     * Member related to this entry
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    /**
     * User who posted this entry
     */
    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * User who reversed this entry
     */
    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    /**
     * Transaction related to this ledger entry
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'reference_id');
    }

    /**
     * Get posted entries
     */
    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    /**
     * Get entries by account type
     */
    public function scopeByAccountType($query, string $accountType)
    {
        return $query->where('account_type', $accountType);
    }

    /**
     * Get entries by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }
}
