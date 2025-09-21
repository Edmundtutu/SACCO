<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dividend extends Model
{
    use HasFactory;

    protected $fillable = [
        'dividend_period',
        'dividend_rate',
        'total_dividend_amount',
        'declaration_date',
        'payment_date',
        'status',
        'notes',
        'declared_by',
    ];

    protected $casts = [
        'dividend_rate' => 'decimal:4',
        'total_dividend_amount' => 'decimal:2',
        'declaration_date' => 'date',
        'payment_date' => 'date',
    ];

    /**
     * Dividend payments for this dividend
     */
    public function dividendPayments(): HasMany
    {
        return $this->hasMany(DividendPayment::class);
    }

    /**
     * User who declared this dividend
     */
    public function declaredBy()
    {
        return $this->belongsTo(User::class, 'declared_by');
    }

    /**
     * Get paid dividends
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Get declared dividends
     */
    public function scopeDeclared($query)
    {
        return $query->where('status', 'declared');
    }
}
