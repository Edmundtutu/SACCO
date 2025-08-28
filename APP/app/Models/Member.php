<?php

namespace App\Models;

use App\Models\Loan;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Share;
use App\Models\LoanGuarantor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'firstname',
        'lastname',
        'contact',
        'ninno',
        'dob',
        'joined'
    ];

    protected $casts = [
        'dob' => 'datetime',
        'joined' => 'datetime',
    ];

    /**
     * Member accounts relationship
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Member loans relationship
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Member transactions relationship
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Member shares relationship
     */
    public function shares(): HasMany
    {
        return $this->hasMany(Share::class);
    }

    /**
     * Loan guarantees given by this member
     */
    public function guaranteesGiven(): HasMany
    {
        return $this->hasMany(LoanGuarantor::class, 'guarantor_id');
    }

    /**
     * Get member's full name
     */
    public function getFullNameAttribute(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * Get total shares owned
     */
    public function getTotalShares(): int
    {
        return $this->shares()->where('status', 'active')->sum('shares_count');
    }

    /**
     * Get total savings balance
     */
    public function getTotalSavingsBalance(): float
    {
        return $this->accounts()->where('status', 'active')->sum('balance');
    }

    /**
     * Get active loan balance
     */
    public function getActiveLoanBalance(): float
    {
        return $this->loans()->whereIn('status', ['disbursed', 'active'])->sum('outstanding_balance');
    }
}
