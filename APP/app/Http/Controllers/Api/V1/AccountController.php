<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Account;
use Illuminate\Http\Request;
use App\Filters\V1\AccountFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Improved\StoreAccountRequest;
use App\Http\Resources\V1\AccountResource;
use App\Http\Requests\V1\Improved\UpdateAccountRequest;
use App\Http\Resources\V1\AccountCollection;


class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = new  AccountFilter();

        // returns an array from the request-query in the format [[column, operator, value]]
        $requiredrequest = $filter->transform($request);

        if(count($requiredrequest)== 0){
            return new AccountCollection(Account::paginate());
        }else{
            return new AccountCollection(Account::where($requiredrequest)->paginate());
        }
        
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAccountRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAccountRequest $request)
    {
        dd($request->all());
        new AccountResource(Account::create($request->all()));

        // return a response for success
        return response(['success' => true ,'message' => 'A new account with has been created'],200);
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function show(Account $account)
    {
        return new AccountResource($account);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAccountRequest  $request
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAccountRequest $request, Account $account)
    {
        $account->update($request->all());

        return response(['success'=>true, 'message'=>'Account updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function destroy(Account $account)
    {
        //
    }
}
