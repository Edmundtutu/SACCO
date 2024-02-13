<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Filters\V1\TransactionFilter;
use App\Http\Resources\V1\TransactionResource;
use App\Http\Resources\V1\TransactionCollection;
use App\Http\Requests\V1\Improved\StoreTransactionRequest;
use App\Http\Requests\V1\Improved\UpdateTransactionRequest;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = new TransactionFilter();

        $requiredrequest = $filter->transform($request); // returns an array in the form [['columnname', 'operator','value']]

        if(count($requiredrequest)==0){
            return new TransactionCollection(Transaction::paginate());
        }else{
            return new TransactionCollection(Transaction::where($requiredrequest)->paginate());
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\V1\StoreTransactionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTransactionRequest $request)
    {
        return new TransactionResource(Transaction::create($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        return new TransactionResource($transaction);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTransactionRequest  $request
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {   
        $transaction->update($request->all());

        return response(['success'=>true,'message'=> 'Transaction Updated'],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
