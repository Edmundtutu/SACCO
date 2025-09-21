<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DividendPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'dividend_id',
        'member_id',
        'shares_count',
        'dividend_amount',
        'payment_date',
        'payment_method',
        'payment_reference',
        'status',
        'processed_by',
    ];

    protected $casts = [
        'shares_count' => 'integer',
        'dividend_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Dividend this payment belongs to
     */
    public function dividend(): BelongsTo
    {
        return $this->belongsTo(Dividend::class);
    }

    /**
     * Member who received this payment
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    /**
     * User who processed this payment
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get paid dividend payments
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Get pending dividend payments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
