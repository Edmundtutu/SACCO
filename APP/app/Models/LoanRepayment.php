<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRepayment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'loan_id',
        'receipt_number',
        'installment_number',
        'due_date',
        'scheduled_amount',
        'principal_amount',
        'interest_amount',
        'penalty_amount',
        'total_amount',
        'payment_date',
        'payment_method',
        'payment_reference',
        'status',
        'notes',
        'balance_after_payment',
        'days_late',
        'collected_by',
        'approved_by',
        'processed_by',
        // Compatibility aliases – routed through accessors/mutators below.
        // These virtual names allow legacy code that passes 'amount' or
        // 'reference' to work seamlessly; they are NOT real DB columns.
        'amount',
        'reference',
    ];

    protected $casts = [
        'scheduled_amount' => 'decimal:2',
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date',
    ];

    /**
     * Generate unique receipt number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($repayment) {
            if (empty($repayment->receipt_number)) {
                $repayment->receipt_number = 'RCP' . now()->format('Ymd') . str_pad(
                    (static::max('id') ?? 0) + 1,
                    4,
                    '0',
                    STR_PAD_LEFT
                );
            }
        });
    }

    /**
     * Loan being repaid
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * User who processed this repayment
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get completed repayments
     *
     * Note: 'completed' is not a valid enum value in the loan_repayments table.
     * The correct value is 'paid'.  This scope is kept for backward
     * compatibility but now filters on 'paid'.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Get pending repayments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get overdue repayments
     */
    public function scopeOverdue($query)
    {
        return $query->where('payment_date', '<', now())
            ->where('status', 'pending');
    }

    /**
     * Mark repayment as paid (was incorrectly using 'completed').
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'paid']);
    }

    // -------------------------------------------------------------------------
    // Compatibility accessors / mutators
    // -------------------------------------------------------------------------
    // The legacy Api\LoansController::repay() created repayment records using
    // the field names 'amount' and 'reference', but the actual DB columns are
    // 'total_amount' and 'payment_reference'.  These accessors/mutators provide
    // a transparent mapping so both field names work at the model layer.

    /**
     * Read 'amount' as an alias for 'total_amount'.
     */
    public function getAmountAttribute(): float
    {
        return (float) ($this->attributes['total_amount'] ?? 0);
    }

    /**
     * Write 'amount' as an alias for 'total_amount'.
     */
    public function setAmountAttribute($value): void
    {
        $this->attributes['total_amount'] = $value;
    }

    /**
     * Read 'reference' as an alias for 'payment_reference'.
     */
    public function getReferenceAttribute(): ?string
    {
        return $this->attributes['payment_reference'] ?? null;
    }

    /**
     * Write 'reference' as an alias for 'payment_reference'.
     */
    public function setReferenceAttribute(?string $value): void
    {
        $this->attributes['payment_reference'] = $value;
    }

    /**
     * Get total amount paid (principal + interest + penalty)
     */
    public function getTotalPaidAttribute(): float
    {
        return $this->principal_amount + $this->interest_amount + $this->penalty_amount;
    }
}
