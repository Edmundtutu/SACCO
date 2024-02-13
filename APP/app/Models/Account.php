<?php

namespace App\Models;

use App\Models\Loan;
use App\Models\Member;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'accountno',
        'type', 
        'status',
        'amount',
        'netamount', 
        'member_id' 
    ];

    protected $table = 'accounts';

    public function members(){
        return $this->belongsTo(Member::class);
    }

    public function loans(){
        return $this->hasMany(Loan::class);
    }

    public function transactions(){
        return $this->hasMany(Transaction::class);
    }
}
