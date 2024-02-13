<?php

namespace App\Models;

use App\Models\Loan;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    // protected $table = 'members';
    
    
    public function accounts(){
        return $this->hasMany(Account::class);
    }

    public function loans(){
        return $this->hasMany(Loan::class);
    }

    public function transactions(){
        return $this->hasMany(Transaction::class);
    }

}
