<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Membership\StoreIndividualProfileRequest;
use App\Http\Requests\Membership\StoreMfiProfileRequest;
use App\Http\Requests\Membership\StoreVslaProfileRequest;
use App\Http\Resources\Membership\IndividualProfileResource;
use App\Http\Resources\Membership\MfiProfileResource;
use App\Http\Resources\Membership\VslaProfileResource;
use App\Models\Membership\Membership;
use App\Models\Membership\MfiProfile;
use App\Models\User;
use App\Models\Membership\VslaProfile;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MembersController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'member')->with(['accounts', 'loans', 'membership']);

        /*Search functionality
            TODO: To implement a MemberFilter instead of a simple search ie
            use App\Filters\MemberFilter;
            $filter = new MemberFilter();
            $requestedQuery = $filter->transfrom($request);
        */
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('member_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $members = $query->orderBy('created_at', 'desc')->paginate(20);

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Members', 'url' => route('admin.members.index')]
        ];

        return view('admin.members.index', compact('members', 'breadcrumbs'));
    }

    public function show($id)
    {
        $member = User::where('role', 'member')
            ->with(['accounts.transactions', 'loans.repayments', 'shares'])
            ->findOrFail($id);

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Members', 'url' => route('admin.members.index')],
            ['text' => $member->name, 'url' => '']
        ];

        return view('admin.members.show', compact('member', 'breadcrumbs'));
    }

    public function edit($id)
    {
        $member = User::where('role', 'member')->findOrFail($id);

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Members', 'url' => route('admin.members.index')],
            ['text' => $member->name, 'url' => route('admin.members.show', $member->id)],
            ['text' => 'Edit', 'url' => '']
        ];

        return view('admin.members.edit', compact('member', 'breadcrumbs'));
    }
    /*
     * Complex Feature
     * TODO : to implement creation of a member at admin side:
     * Creation of User + their membership with approval in three phases
     * Membership presents in 3 kinds(Morphs) : i.e. individual, vlsa, mfi
     * steps:
     * 1. Create a user with status pending_approval
     * 2. Approve the user with approvals from staff_level_1, staff_level_2, staff_level_3
     * 3. Last staff members activates the user and assigns an account
     * */
    public function create(Request $requst){
        /*
         * if route request has create membership-individual, show create membership-individual form
         * if route request has create membership-vlsa, show create membership-vlsa form
         * if route request has create membership-mfi, show create membership-mfi form
        */



    }

    public function storeIndividualMembership(StoreIndividualProfileRequest $request, User $user){
        // create a profile first
        $profile = new IndividualProfileResource($request->all());

        // then add the profile to a new membership record
        $membership = Membership::create([
            'profile_id'   => $profile->id,
            'profile_type' => get_class($profile),
            'user_id'      => $user->id,
        ]);
        // update the status of member to active
        $user->update([
            'status' => 'pending_approval',
            'membership_date'=> date(now('')),
        ]);
        // return the membership record
        return $membership;
    }

    public function storeVlsaMembership(StoreVslaProfileRequest $request, User $user){
        // create a profile first
        $profile = new VslaProfileResource($request->all());

        // then add the profile to a new membership record
        $membership = Membership::create([
            'profile_id'   => $profile->id,
            'profile_type' => get_class($profile),
            'user_id'      => $user->id,
        ]);
        // update the status of member to active
        $user->update([
            'status' => 'pending_approval',
            'membership_date'=> date(now('')),
        ]);
        // return the membership record
        return $membership;
    }
    public function storeMfiMembership(StoreMfiProfileRequest $request, User $user){
        // create a profile first
        $profile = new MfiProfileResource($request->all());

        // then add the profile to a new membership record
        $membership = Membership::create([
            'profile_id'   => $profile->id,
            'profile_type' => get_class($profile),
            'user_id'      => $user->id,
        ]);
        // update the status of member to active
        $user->update([
            'status' => 'pending_approval',
            'membership_date'=> now(),
        ]);
        // return the membership record
        return $membership;
    }
    public function update(Request $request, $id)
    // TODO: to implement update membership profiles instead users' Important: Like store for individual, vlsa, mfi profiles: so should the update work
    {
        $member = User::where('role', 'member')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $member->id,
            'phone' => 'required|string|max:15',
            'national_id' => 'required|string|max:20',
            'address' => 'required|string',
            'occupation' => 'required|string|max:100',
            'monthly_income' => 'required|numeric|min:0',
            'status' => 'required|in:pending,active,suspended,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $member->update($request->only([
            'name', 'email', 'phone', 'national_id', 'address',
            'occupation', 'monthly_income', 'status'
        ]));

        return redirect()->route('admin.members.show', $member->id)
            ->with('success', 'Member information updated successfully.');
    }

    public function approveLevel1(Membership $membership)
    {
        $this->authorize('approveLevel1', $membership);

        $membership->update([
            'approved_by_level_1' => auth()->id(),
            'approved_at_level_1' => now(),
        ]);

        return response()->json(['message' => 'Membership approved at level 1']);
    }

    public function approveLevel2(Membership $membership)
    {
        $this->authorize('approveLevel2', $membership);

        $membership->update([
            'approved_by_level_2' => auth()->id(),
            'approved_at_level_2' => now(),
        ]);

        return response()->json(['message' => 'Membership approved at level 2']);
    }

    public function approveLevel3(Membership $membership)
    {
        $this->authorize('approveLevel3', $membership);

        $member = $membership->user();

        $membership->update([
            'approved_by_level_3' => auth()->id(),
            'approved_at_level_3' => now(),
            'approval_status' => 'approved', // Final approval
        ]);

        // assign user an account
        $new_account =Account::create([
            'user_id' => $member->id,
            'account_number' => 'SAV' . str_pad($member->id, 6, '0', STR_PAD_LEFT),
            'account_type' => 'savings',
            'product_id' => 1, // Default savings product
            'balance' => 0,
            'status' => 'active',
        ]);

        // now update the status of the user.
        $member->update([
           'status' => 'active',
           'account_verified_at'=> now(),
        ]);

        return response()->json(['message' => 'Membership fully approved with account numeber: '.$new_account->account_number]);;
    }

    public function suspend($id)
    {
        $member = User::where('role', 'member')->findOrFail($id);

        $member->update(['status' => 'suspended']);

        return redirect()->back()
            ->with('success', 'Member suspended successfully.');
    }

    public function activate($id)
    {
        $member = User::where('role', 'member')->findOrFail($id);

        $member->update(['status' => 'active']);

        return redirect()->back()
            ->with('success', 'Member activated successfully.');
    }
}
