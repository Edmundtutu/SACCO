<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRepayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'receipt_number',
        'installment_number',
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
        'processed_by',
    ];

    protected $casts = [
        'scheduled_amount' => 'decimal:2',
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
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
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
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
     * Mark repayment as completed
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Get total amount paid (principal + interest + penalty)
     */
    public function getTotalPaidAttribute(): float
    {
        return $this->principal_amount + $this->interest_amount + $this->penalty_amount;
    }
}
