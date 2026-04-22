<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Phase 2 — Expense detail record linked to a Transaction.
 *
 * Every row is created atomically inside ExpenseHandler::execute()
 * and therefore always has a corresponding transactions row and
 * balanced GL entries.
 */
class ExpenseRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'category',
        'gl_account_code',
        'gl_account_name',
        'amount',
        'payment_method',
        'payment_reference',
        'description',
        'receipt_number',
        'recorded_by',
        'tenant_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
