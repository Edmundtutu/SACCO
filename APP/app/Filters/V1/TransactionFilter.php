<?php
namespace App\Filters\V1;

use App\Filters\ApiFilter;

use Illuminate\Http\Request;


class TransactionFilter extends ApiFilter{
    protected $allowedparams =[
        'id' => ['eq'],
        'tranactionType' => ['eq'],
        'amountTransacted' => ['eq', 'lt','lte','gt','gte'],
        'dateOfTransaction' => ['eq', 'lt', 'gt'],
        'transactedById' => ['eq'],
        'accountId' => ['eq']
    ];

    protected $column_Map = [
        'transactionType' => 'transaction_type',
        'amountTransacted' => 'amount',
        'dateOfTransaction'=> 'Date_of_transaction',
        'transactedById'=> 'member_id',
        'accountId' => 'account_id',
    ];

    

}