<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Loan;
use Illuminate\Http\Request;
use App\Filters\V1\LoanFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\LoanResource;
use App\Http\Resources\V1\LoanCollection;
use App\Http\V1\Requests\UpdateLoanRequest;
use App\Http\Requests\V1\Improved\StoreLoanRequest;


class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = new LoanFilter();

        $requiredrequest = $filter->transform($request); // returns an array in the form [['columnname', 'operator','value']]

        if(count($requiredrequest)==0){
            return new LoanCollection(Loan::paginate());
        }else{
            return new LoanCollection(Loan::where($requiredrequest)->paginate());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\V1\StoreLoanRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLoanRequest $request)
    {
        return new LoanResource(Loan::create($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function show(Loan $loan)
    {
        return new LoanResource($loan);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateLoanRequest  $request
     * @param  \App\Models\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLoanRequest $request, Loan $loan)
    {
        $loan->update($request->all());


        return response(['success'=>true,'message'=>'Loan has been updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Loan  $loan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Loan $loan)
    {
        //
    }
}
