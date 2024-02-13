<?php

namespace App\Models;

use App\Models\Loan;
use App\Models\Member;
use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_type',
        'amount',
        'Date_of_transaction' ,
        'member_id',
        'account_id',
        'loan_id' 
    ];

    protected $table ='transactions';

    public function members(){
        return $this->belongsTo(Member::class);
    }

    public function accounts(){
        return $this->belongsTo(Account::class);
    }

    public function loans(){
        return $this->belongsTo(Loan::class);
    }
}

