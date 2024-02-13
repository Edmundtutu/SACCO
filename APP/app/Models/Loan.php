<?php

namespace App\Models;

use App\Models\Member;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Loan extends Model
{
    use HasFactory;

    protected $fillable =[
        'id',
        'loan_type',
        'loan_amount',
        'intrest_rate',
        ' loan_status',
        ' repayment_terms',
        'member_id',
        'account_id'
    ];


    protected $table = 'loans';


    public function members(){
        return $this->belongsTo(Member::class);
    }

    public function accounts(){
        return $this->belongsTo(Account::class);
    }

    public function transactions(){
        return $this->hasMany(Transaction::class);
    }
}
