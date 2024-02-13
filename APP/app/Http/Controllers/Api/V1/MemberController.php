<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Member;
use Illuminate\Http\Request;
use App\Filters\V1\MemberFilter;
use App\Http\V1\Improved\Requests;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MemberResource;
use App\Http\Resources\V1\MemberCollection;
use App\Http\Requests\V1\Improved\StoreMemberRequest;
use App\Http\Requests\V1\Improved\UpdateMemberRequest;


class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = new  MemberFilter();

        // returns an array from the request-query in the format [[column, operator, value]]
        $requiredrequest = $filter->transform($request);

        if(count($requiredrequest)== 0){
            return new MemberCollection(Member::paginate());
        }else{
            return new MemberCollection(Member::where($requiredrequest)->paginate());
        }
        
        
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreMemberRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMemberRequest $request)
    {
         
        // dd($request->all());
         new MemberResource(Member::create($request->all()));

        return response(['success'=>true, 'message'=>'Member created successfully'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function show(Member $member)
    {
        return new MemberResource($member);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateMemberRequest  $request
     * @param  \App\Models\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMemberRequest $request, Member $member)
    {
        $member->update($request->all());

        return response(['success'=>true, 'message'=>'Member updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function destroy(Member $member)
    {
        //
    }
}
