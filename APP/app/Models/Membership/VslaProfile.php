<?php

namespace App\Models\Membership;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Membership\Membership;

class VslaProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'village',
        'sub_county',
        'district',
        'membership_count',
        'registration_certificate',
        'constitution_copy',
        'resolution_minutes',
        'executive_contacts',
        'recommendation_lc1',
        'recommendation_cdo',
    ];

    protected $casts = [
        'executive_contacts'=> 'array',

    ];

    public function profile()
    {
        return $this->morphOne(Membership::class, 'profile');
    }
}
