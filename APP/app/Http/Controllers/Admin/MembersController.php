<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Membership\StoreIndividualProfileRequest;
use App\Http\Requests\Membership\StoreMfiProfileRequest;
use App\Http\Requests\Membership\StoreVslaProfileRequest;
use App\Http\Requests\Membership\StoreUserRequest;
use App\Models\Membership\Membership;
use App\Models\Membership\MfiProfile;
use App\Models\Membership\IndividualProfile;
use App\Models\User;
use App\Models\Membership\VslaProfile;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MembersController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'member')->with(['accounts', 'loans', 'membership.profile']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('membership', function($membershipQuery) use ($search) {
                      $membershipQuery->where('id', 'like', "%{$search}%");
                  })
                  ->orWhereHas('membership.profile', function($profileQuery) use ($search) {
                      $profileQuery->where('phone', 'like', "%{$search}%")
                               ->orWhere('national_id', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Membership approval status filter
        if ($request->has('approval_status') && $request->approval_status) {
            $query->whereHas('membership', function($membershipQuery) use ($request) {
                $membershipQuery->where('approval_status', $request->approval_status);
            });
        }

        $members = $query->orderBy('created_at', 'desc')->paginate(60);

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Members', 'url' => route('admin.members.index')]
        ];

        return view('admin.members.index', compact('members', 'breadcrumbs'));
    }

    /**
     * Display a listing of membership requests (pending approvals)
     */
    public function requests(Request $request)
    {
        $query = User::where('role', 'member')
            ->with(['accounts', 'loans', 'membership.profile'])
            ->whereHas('membership', function($membershipQuery) {
                $membershipQuery->where('approval_status', 'pending');
            });

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('membership', function($membershipQuery) use ($search) {
                      $membershipQuery->where('id', 'like', "%{$search}%");
                  })
                  ->orWhereHas('membership.profile', function($profileQuery) use ($search) {
                      $profileQuery->where('phone', 'like', "%{$search}%")
                               ->orWhere('national_id', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Membership approval status filter (if user wants to override the default 'pending')
        if ($request->has('approval_status') && $request->approval_status) {
            $query->whereHas('membership', function($membershipQuery) use ($request) {
                $membershipQuery->where('approval_status', $request->approval_status);
            });
        }

        $members = $query->orderBy('created_at', 'desc')->paginate(60);

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Members', 'url' => route('admin.members.index')],
            ['text' => 'Membership Requests', 'url' => route('admin.members.requests')]
        ];

        return view('admin.members.index-requests', compact('members', 'breadcrumbs'));
    }

    public function show($id)
    {
        $member = User::where('role', 'member')
            ->with(['accounts.transactions', 'loans.repayments', 'shares', 'membership.profile'])
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
        $member = User::where('role', 'member')
            ->with(['membership.profile'])
            ->findOrFail($id);

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Members', 'url' => route('admin.members.index')],
            ['text' => $member->name, 'url' => route('admin.members.show', $member->id)],
            ['text' => 'Edit', 'url' => '']
        ];

        return view('admin.members.edit', compact('member', 'breadcrumbs'));
    }

    /**
     * Return HTML for the membership approval modal (AJAX-loaded)
     */
    public function requestModal($id)
    {
        $member = User::where('role', 'member')
            ->with(['membership.profile', 'accounts', 'loans'])
            ->findOrFail($id);

        return view('admin.members.partials.approval-modal', compact('member'));
    }

    /**
     * Show the form for creating a new member
     */
    public function create(Request $request)
    {
        $memberType = $request->get('type', 'individual'); // Default to individual

        // Validate member type
        if (!in_array($memberType, ['individual', 'vsla', 'mfi'])) {
            return redirect()->route('admin.members.index')
                ->with('error', 'Invalid member type specified.');
        }

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Members', 'url' => route('admin.members.index')],
            ['text' => 'Create ' . ucfirst($memberType) . ' Member', 'url' => '']
        ];

        // Get potential referees (existing active members for individual profiles)
        $potentialReferees = [];
        if ($memberType === 'individual') {
            $potentialReferees = User::where('role', 'member')
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        }

        return view('admin.members.create', compact('memberType', 'breadcrumbs', 'potentialReferees'));
    }

    /**
     * Store a new member (unified method for all member types)
     */
    public function store(Request $request)
    {
        $memberType = $request->input('member_type');

        // Validate member type
        if (!in_array($memberType, ['individual', 'vsla', 'mfi'])) {
            return redirect()->back()
                ->with('error', 'Invalid member type specified.')
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Create user first
            $userData = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'role' => 'member',
                'status' => 'pending_approval',
                'membership_date' => now(),
            ];

            $user = User::create($userData);

            // Create the appropriate profile based on member type
            switch ($memberType) {
                case 'individual':
                    $profile = $this->createIndividualProfile($request, $user);
                    break;
                case 'vsla':
                    $profile = $this->createVslaProfile($request, $user);
                    break;
                case 'mfi':
                    $profile = $this->createMfiProfile($request, $user);
                    break;
            }

            // Create membership record
            $membership = Membership::create([
                'profile_id' => $profile->id,
                'profile_type' => get_class($profile),
                'user_id' => $user->id,
                'approval_status' => 'pending',
            ]);

            DB::commit();

            return redirect()->route('admin.members.show', $user->id)
                ->with('success', ucfirst($memberType) . ' member created successfully. Membership ID: ' . $membership->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create member: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Create individual profile
     */
    private function createIndividualProfile(Request $request, User $user): IndividualProfile
    {
        $profileData = [
            'phone' => $request->input('phone'),
            'national_id' => $request->input('national_id'),
            'date_of_birth' => $request->input('date_of_birth'),
            'gender' => $request->input('gender'),
            'address' => $request->input('address'),
            'occupation' => $request->input('occupation'),
            'monthly_income' => $request->input('monthly_income'),
            'referee' => $request->input('referee'),
            'next_of_kin_name' => $request->input('next_of_kin_name'),
            'next_of_kin_relationship' => $request->input('next_of_kin_relationship'),
            'next_of_kin_phone' => $request->input('next_of_kin_phone'),
            'next_of_kin_address' => $request->input('next_of_kin_address'),
            'emergency_contact_name' => $request->input('emergency_contact_name'),
            'emergency_contact_phone' => $request->input('emergency_contact_phone'),
            'employer_name' => $request->input('employer_name'),
            'bank_name' => $request->input('bank_name'),
            'bank_account_number' => $request->input('bank_account_number'),
            'additional_notes' => $request->input('additional_notes'),
        ];

        return IndividualProfile::create($profileData);
    }

    /**
     * Create VSLA profile
     */
    private function createVslaProfile(Request $request, User $user): VslaProfile
    {
        $profileData = [
            'village' => $request->input('village'),
            'sub_county' => $request->input('sub_county'),
            'district' => $request->input('district'),
            'membership_count' => $request->input('membership_count'),
            'registration_certificate' => $request->input('registration_certificate'),
            'constitution_copy' => $request->input('constitution_copy'),
            'resolution_minutes' => $request->input('resolution_minutes'),
            'executive_contacts' => $request->input('executive_contacts', []),
            'recommendation_lc1' => $request->input('recommendation_lc1'),
            'recommendation_cdo' => $request->input('recommendation_cdo'),
        ];

        return VslaProfile::create($profileData);
    }

    /**
     * Create MFI profile
     */
    private function createMfiProfile(Request $request, User $user): MfiProfile
    {
        $profileData = [
            'contact_person' => $request->input('contact_person'),
            'contact_number' => $request->input('contact_number'),
            'address' => $request->input('address'),
            'membership_count' => $request->input('membership_count'),
            'registration_certificate' => $request->input('registration_certificate'),
            'board_members' => $request->input('board_members', []),
            'bylaws_copy' => $request->input('bylaws_copy'),
            'resolution_minutes' => $request->input('resolution_minutes'),
            'operating_license' => $request->input('operating_license'),
        ];

        return MfiProfile::create($profileData);
    }
    /**
     * Update member information
     */
    public function update(Request $request, $id)
    {
        $member = User::where('role', 'member')
            ->with(['membership.profile'])
            ->findOrFail($id);

        if (!$member->membership) {
            return redirect()->back()
                ->with('error', 'Member has no associated membership profile.');
        }

        try {
            DB::beginTransaction();

            // Update user basic information
            $userData = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'status' => $request->input('status'),
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->input('password'));
            }

            $member->update($userData);

            // Update profile based on profile type
            $profile = $member->membership->profile;

            if ($profile instanceof IndividualProfile) {
                $this->updateIndividualProfile($request, $profile);
            } elseif ($profile instanceof VslaProfile) {
                $this->updateVslaProfile($request, $profile);
            } elseif ($profile instanceof MfiProfile) {
                $this->updateMfiProfile($request, $profile);
            }

            DB::commit();

            return redirect()->route('admin.members.show', $member->id)
                ->with('success', 'Member information updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update member: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update individual profile
     */
    private function updateIndividualProfile(Request $request, IndividualProfile $profile): void
    {
        $profileData = [
            'phone' => $request->input('phone'),
            'national_id' => $request->input('national_id'),
            'date_of_birth' => $request->input('date_of_birth'),
            'gender' => $request->input('gender'),
            'address' => $request->input('address'),
            'occupation' => $request->input('occupation'),
            'monthly_income' => $request->input('monthly_income'),
            'referee' => $request->input('referee'),
            'next_of_kin_name' => $request->input('next_of_kin_name'),
            'next_of_kin_relationship' => $request->input('next_of_kin_relationship'),
            'next_of_kin_phone' => $request->input('next_of_kin_phone'),
            'next_of_kin_address' => $request->input('next_of_kin_address'),
            'emergency_contact_name' => $request->input('emergency_contact_name'),
            'emergency_contact_phone' => $request->input('emergency_contact_phone'),
            'employer_name' => $request->input('employer_name'),
            'bank_name' => $request->input('bank_name'),
            'bank_account_number' => $request->input('bank_account_number'),
            'additional_notes' => $request->input('additional_notes'),
        ];

        $profile->update(array_filter($profileData, function($value) {
            return $value !== null;
        }));
    }

    /**
     * Update VSLA profile
     */
    private function updateVslaProfile(Request $request, VslaProfile $profile): void
    {
        $profileData = [
            'village' => $request->input('village'),
            'sub_county' => $request->input('sub_county'),
            'district' => $request->input('district'),
            'membership_count' => $request->input('membership_count'),
            'registration_certificate' => $request->input('registration_certificate'),
            'constitution_copy' => $request->input('constitution_copy'),
            'resolution_minutes' => $request->input('resolution_minutes'),
            'executive_contacts' => $request->input('executive_contacts', []),
            'recommendation_lc1' => $request->input('recommendation_lc1'),
            'recommendation_cdo' => $request->input('recommendation_cdo'),
        ];

        $profile->update(array_filter($profileData, function($value) {
            return $value !== null;
        }));
    }

    /**
     * Update MFI profile
     */
    private function updateMfiProfile(Request $request, MfiProfile $profile): void
    {
        $profileData = [
            'contact_person' => $request->input('contact_person'),
            'contact_number' => $request->input('contact_number'),
            'address' => $request->input('address'),
            'membership_count' => $request->input('membership_count'),
            'registration_certificate' => $request->input('registration_certificate'),
            'board_members' => $request->input('board_members', []),
            'bylaws_copy' => $request->input('bylaws_copy'),
            'resolution_minutes' => $request->input('resolution_minutes'),
            'operating_license' => $request->input('operating_license'),
        ];

        $profile->update(array_filter($profileData, function($value) {
            return $value !== null;
        }));
    }

    public function approve_level_1(Membership $membership)
    {
        $this->authorize('approve_level_1', $membership);

        $membership->update([
            'approved_by_level_1' => auth()->id(),
            'approved_at_level_1' => now(),
        ]);

        return response()->json(['message' => 'Membership approved at level 1']);
    }

    public function approve_level_2(Membership $membership)
    {
        $this->authorize('approve_level_2', $membership);

        $membership->update([
            'approved_by_level_2' => auth()->id(),
            'approved_at_level_2' => now(),
        ]);

        return response()->json(['message' => 'Membership approved at level 2']);
    }

    public function approve_level_3(Membership $membership)
    {
        $this->authorize('approve_level_3', $membership);

        $member = $membership->user;

        $membership->update([
            'approved_by_level_3' => auth()->id(),
            'approved_at_level_3' => now(),
            'approval_status' => 'approved', // Final approval
        ]);

        // assign user an account
        $new_account = Account::create([
            'member_id' => $member->id, // Use member_id instead of user_id
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

        return response()->json(['message' => 'Membership fully approved with account number: '.$new_account->account_number]);
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
