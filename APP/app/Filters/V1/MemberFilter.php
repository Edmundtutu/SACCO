<?php

namespace App\Filters\V1;

use App\Filters\ApiFilter;

class MemberFilter extends ApiFilter {

    protected $allowedparams =[
        'id'=> ['eq','gt','lt', 'lte', 'gte'],
        'firstname'=> ['eq'],
        'lastname'=>['eq'],
        'contact'=>['eq'],
        'NIN'=>['eq'],
        'dob'=>['eq','gt','gte', 'lt', 'lte'],
        'joined'=>['eq','gt','gte', 'lt', 'lte','ne']

    ];

    // protected $operator_Map=[];

    protected $colum_Map = [
        'NIN' => 'ninno'
    ];

}