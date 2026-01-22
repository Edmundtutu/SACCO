<?php

namespace App\Models\Membership;

use App\Models\User;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class IndividualProfile extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'phone',
        'national_id',
        'date_of_birth',
        'gender',
        'occupation',
        'monthly_income',
        'referee',
        'next_of_kin_name',
        'next_of_kin_relationship',
        'next_of_kin_phone',
        'next_of_kin_address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'employer_name',
        'bank_name',
        'bank_account_number',
        'profile_photo_path',
        'id_copy_path',
        'signature_path',
        'additional_notes'
    ];

    public function memberShip():MorphOne
    {
        return $this->morphOne(Membership::class, 'profile');
    }

    public function refereedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referee');
    }
}
