<?php

namespace App\Filters\V1;

use Illuminate\Http\Request;
use App\Filters\ApiFilter;


class LoanFilter extends ApiFilter {


    protected $allowedparams = [
        'id'=> ['eq'],
        'loanType' => ['eq', 'ne'],
        'DOA' =>['gt','lt','eq'],
        'DOR' => ['gt','lt','eq'],
        'loanAmount' => ['gt','lt','eq','lte','gte'],
        'intrestRate'=> ['gt','lt','eq'],
        'loanStatus' =>['eq','ne'],
        'repaymentTerms'=> ['eq'],
        'loanOwnerId'=> ['eq'],
        'loanAccountId' => ['eq']
    ];

    protected $column_Map =[
        'id'=> 'id',
        'loanType' => 'loan_type',
        'loanAmount' => 'loan_amount',
        'intrestRate'=> 'intrest_rate',
        'loanStatus' => ' loan_status',
        'repaymentTerms'=> ' repayment_terms',
        'loanOwnerId'=> 'member_id',
        'loanAccountId' => 'account_id'
    ];

   
}