<?php

namespace App\Filters\V1;

use App\Filters\ApiFilter;

class AccountFilter extends ApiFilter {

    protected $allowedparams =[
        'id'=> ['eq','gt','lt', 'lte', 'gte'],
        'accountno'=> ['eq'],
        'type'=>['eq'],
        'status'=>['eq'],
        'amount'=>['eq','gt','lt', 'lte', 'gte'],
        'balance'=>['eq','gt','gte', 'lt', 'lte'],
        'accountHolder'=>['eq','gt','gte', 'lt', 'lte','ne']

    ];

    // protected $operator_Map=[];

    protected $colum_Map = [
        'accountHolder' => 'member_id'
    ];

}