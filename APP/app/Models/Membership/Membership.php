<?php

namespace App\Models\Membership;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membership extends Model
{
    use HasFactory;
    public $incrementing = false; // non-numeric PK
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'profile_type',
        'profile_id',
        'user_id',
        'approval_status',
        'approved_by_level_1',
        'approved_at_level_1',
        'approved_by_level_2',
        'approved_at_level_2',
        'approved_by_level_3',
        'approved_at_level_3',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($membership) {
            $year = date('Y');

            // Map FQCN to prefix
            $prefixMap = [
                \App\Models\Membership\IndividualProfile::class => 'M',
                \App\Models\Membership\VslaProfile::class       => 'G',
                \App\Models\Membership\MfiProfile::class        => 'S',
            ];

            $profileType = $membership->profile_type;
            $prefix = $prefixMap[$profileType] ?? 'M'; // fallback

            // Get the last number for this prefix and year using ID ordering (zero-padded)
            $last = self::where('id', 'like', $prefix . $year . '-%')
                ->orderBy('id', 'desc')
                ->first();

            if ($last) {
                // Format: <PREFIX><YEAR>-NNNN e.g., M2025-0002 → extract after '-'
                $dashPosition = strpos($last->id, '-');
                $lastNumber = $dashPosition !== false ? (int) substr($last->id, $dashPosition + 1) : 0;
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            // Set the membership ID
            $membership->id = sprintf('%s%s-%04d', $prefix, $year, $nextNumber);
        });
    }


    public function profile()
    {
        return $this->morphTo();
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * User who approved this member
     */
    public function levelOneApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_level_1');
    }
    public function levelTwoApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_level_2');
    }
    public function levelThreeApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_level_3');
    }
}
