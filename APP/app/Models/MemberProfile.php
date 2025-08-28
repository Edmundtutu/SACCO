<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'next_of_kin_name',
        'next_of_kin_relationship',
        'next_of_kin_phone',
        'next_of_kin_address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'employer_name',
        'employer_address',
        'employer_phone',
        'bank_name',
        'bank_account_number',
        'bank_branch',
        'additional_notes',
        'profile_photo_path',
        'id_copy_path',
        'signature_path',
    ];

    /**
     * Get the user that owns the profile
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}