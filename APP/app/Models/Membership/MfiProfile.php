<?php

namespace App\Models\Membership;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Membership\Membership;

class MfiProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_person',
        'contact_number',
        'address',
        'membership_count',
        'registration_certificate',
        'board_members',
        'bylaws_copy',
        'resolution_minutes',
        'operating_license'
    ];

    protected  $casts = [
      'board_members' => 'array',
    ];

    public function profile(){
        return $this->morphOne(Membership::class, 'profile');
    }
}
