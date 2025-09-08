<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Membership\IndividualProfile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Membership\Membership;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'membership_date',
        'account_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'membership_date' => 'date',
        'account_verified_at' => 'date',
    ];

    /**
     * Member profile relationship
     */
    public function membership(): HasOne
    {
        return $this->hasOne(Membership::class);
    }

    /**
     * Member accounts relationship
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'member_id');
    }

    /**
     * Member loans relationship
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'member_id');
    }

    /**
     * Member shares relationship
     */
    public function shares(): HasMany
    {
        return $this->hasMany(Share::class, 'member_id');
    }

    /**
     * Member transactions relationship
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'member_id');
    }

    /**
     * Loan guarantees given by this member
     */
    public function guaranteesGiven(): HasMany
    {
        return $this->hasMany(LoanGuarantor::class, 'guarantor_id');
    }

    /**
     * User who refereed another
     */
    public function referred():HasMany
    {
        return $this->hasMany(IndividualProfile::class);
    }

    /**
     * Memberships approved by this user
     */
    public function levelOneApprovedMembers(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function levelTwoApprovedMembers(): HasMany
    {
        return $this->hasMany(User::class);
    }
    public function levelThreeApprovedMembers(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Dividend payments for this member
     */
    public function dividendPayments(): HasMany
    {
        return $this->hasMany(DividendPayment::class, 'member_id');
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is admin or staff
     */
    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'staff_level_1', 'staff_level_2', 'staff_level_3']);
    }

    /**
     * Check if user is active member
     */
    public function isActiveMember(): bool
    {
        return $this->role === 'member' && $this->status === 'active';
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
        return $this->accounts()->sum('balance');
    }

    /**
     * Get active loan balance
     */
    public function getActiveLoanBalance(): float
    {
        return $this->loans()->whereIn('status', ['disbursed', 'active'])->sum('outstanding_balance');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
            'member_number' => $this->member_number,
            'status' => $this->status,
        ];
    }
}
