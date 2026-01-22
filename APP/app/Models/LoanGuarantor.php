<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanGuarantor extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'loan_id',
        'guarantor_id',
        'guarantor_savings_balance',
        'guarantee_amount',
        'status',
        'accepted_at',
        'rejected_at',
        'notes',
    ];

    protected $casts = [
        'guarantor_savings_balance' => 'decimal:2',
        'guarantee_amount' => 'decimal:2',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Loan being guaranteed
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * Member providing the guarantee
     */
    public function guarantor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guarantor_id');
    }

    /**
     * Get accepted guarantors
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Get pending guarantors
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get rejected guarantors
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Accept guarantee
     */
    public function accept(): void
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    /**
     * Reject guarantee
     */
    public function reject(string $reason = null): void
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'notes' => $reason ? $this->notes . "\nRejection reason: " . $reason : $this->notes,
        ]);
    }
}
