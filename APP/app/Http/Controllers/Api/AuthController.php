<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Membership\IndividualProfile;
use App\Models\Membership\Membership;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Register a new member
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'national_id' => 'required|string',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'address' => 'required|string',
            'occupation' => 'required|string',
            'monthly_income' => 'required|numeric|min:0',

            // Tenant information (required for registration)
            'tenant_id' => 'required|exists:tenants,id',

            // Member profile data
            'next_of_kin_name' => 'required|string',
            'next_of_kin_relationship' => 'required|string',
            'next_of_kin_phone' => 'required|string',
            'next_of_kin_address' => 'required|string',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
            'employer_name' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'bank_account_number' => 'nullable|string',
            'referee' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Validate tenant
            $tenant = \App\Models\Tenant::find($request->tenant_id);
            if (!$tenant || !$tenant->canOperate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected SACCO is not accepting new members at this time.'
                ], 400);
            }

            // Check tenant limits
            if ($tenant->hasReachedMemberLimit()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This SACCO has reached its maximum member limit.'
                ], 400);
            }

            // Check email uniqueness within tenant
            $existingUser = \App\Models\User::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->where('email', $request->email)
                ->first();

            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already registered in this SACCO.'
                ], 422);
            }

            // Check national_id uniqueness within tenant
            $existingProfile = \App\Models\Membership\IndividualProfile::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->where('national_id', $request->national_id)
                ->first();

            if ($existingProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'This national ID is already registered in this SACCO.'
                ], 422);
            }

            DB::beginTransaction();

            // Set tenant context for automatic tenant_id assignment
            setTenant($tenant);

            // Create user
            $user = User::create([
                'tenant_id' => $tenant->id, // Explicitly set even though trait will handle it
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'member',
                'status' => 'pending_approval',
                'membership_date' => now(),
            ]);

            // Create individual profile
            $profile = IndividualProfile::create([
                'tenant_id' => $tenant->id,
                'phone' => $request->phone,
                'national_id' => $request->national_id,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'occupation' => $request->occupation,
                'monthly_income' => $request->monthly_income,
                'referee' => $request->referee,
                'next_of_kin_name' => $request->next_of_kin_name,
                'next_of_kin_relationship' => $request->next_of_kin_relationship,
                'next_of_kin_phone' => $request->next_of_kin_phone,
                'next_of_kin_address' => $request->next_of_kin_address,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'employer_name' => $request->employer_name,
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
            ]);

            // Create membership record
            $membership = Membership::create([
                'tenant_id' => $tenant->id,
                'profile_id' => $profile->id,
                'profile_type' => IndividualProfile::class,
                'user_id' => $user->id,
                'approval_status' => 'pending',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registration successful. Your membership is pending approval.',
                'data' => [
                    'membership_id' => $membership->id,
                    'status' => $user->status,
                    'tenant' => [
                        'id' => $tenant->id,
                        'name' => $tenant->sacco_name,
                        'code' => $tenant->sacco_code,
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        try {
            // Note: JWT auth will not use tenant scope during login,
            // so we need to handle multi-tenant login carefully
            
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            /** @var User|null $user */
            $user = auth()->user();

            // Guard: ensure user is present and a concrete User model (static analysis + runtime safety)
            if (!$user instanceof User) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication failed'
                ], 500);
            }

            // Validate user has a tenant
            if (!$user->tenant_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not associated with any SACCO. Please contact support.'
                ], 403);
            }

            // Load and validate tenant
            $tenant = \App\Models\Tenant::find($user->tenant_id);
            if (!$tenant) {
                return response()->json([
                    'success' => false,
                    'message' => 'SACCO not found. Please contact support.'
                ], 404);
            }

            // Check if tenant can operate
            if (!$tenant->canOperate()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your SACCO is currently ' . $tenant->status . '. ' .
                                ($tenant->trialExpired() ? 'Trial period has expired.' : 'Please contact support.'),
                    'tenant_status' => $tenant->status
                ], 403);
            }

            // Set tenant context
            setTenant($tenant);

            // Check if user is active
            if ($user->status !== 'active' && $user->role === 'member') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is not active. Please contact the administrator.',
                    'status' => $user->status
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'member_number' => $user->membership ? $user->membership->id : null,
                        'role' => $user->role,
                        'status' => $user->status,
                    ],
                    'tenant' => [
                        'id' => $tenant->id,
                        'name' => $tenant->sacco_name,
                        'code' => $tenant->sacco_code,
                        'status' => $tenant->status,
                    ],
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token'
            ], 500);
        }
    }

    /**
     * Get user profile
     */
    public function profile(): JsonResponse
    {
        try {
            /** @var User|null $user */
            $user = auth()->user();

            if (!$user instanceof User) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $user->load([
                'membership.profile',
                'accounts.accountable',  // Load accountable for polymorphic access
                'accounts.accountable.savingsProduct',  // For wallet detection
                'loans.loanAccount',  // Load loanAccount for current_outstanding
                'shares'
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'membership' => $user->membership,
                    'profile' => $user->membership ? $user->membership->profile : null,
                    'summary' => [
                        'total_savings' => $user->getTotalSavingsBalance(),
                        'total_shares' => $user->getTotalShares(),
                        'active_loan_balance' => $user->getActiveLoanBalance(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not retrieve profile'
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string',
            'occupation' => 'sometimes|string',
            'monthly_income' => 'sometimes|numeric|min:0',
            'next_of_kin_name' => 'sometimes|string',
            'next_of_kin_relationship' => 'sometimes|string',
            'next_of_kin_phone' => 'sometimes|string',
            'next_of_kin_address' => 'sometimes|string',
            'emergency_contact_name' => 'sometimes|string',
            'emergency_contact_phone' => 'sometimes|string',
            'employer_name' => 'sometimes|string',
            'bank_name' => 'sometimes|string',
            'bank_account_number' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            /** @var User|null $user */
            $user = auth()->user();

            if (!$user instanceof User) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $user->load('membership.profile');

            // Update user basic info
            $user->update($request->only(['name']));

            // Update profile if exists and is Individual Profile
            if ($user->membership && $user->membership->profile instanceof IndividualProfile) {
                $profile = $user->membership->profile;
                $profileData = $request->only([
                    'phone', 'address', 'occupation', 'monthly_income',
                    'next_of_kin_name', 'next_of_kin_relationship',
                    'next_of_kin_phone', 'next_of_kin_address',
                    'emergency_contact_name', 'emergency_contact_phone',
                    'employer_name', 'bank_name', 'bank_account_number'
                ]);

                $profile->update(array_filter($profileData, function($value) {
                    return $value !== null;
                }));
            }

            // Reload the user with updated relationships
            $user->load('membership.profile');

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => $user,
                    'membership' => $user->membership,
                    'profile' => $user->membership ? $user->membership->profile : null,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not update profile'
            ], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            /** @var User|null $user */
            $user = auth()->user();

            if (!$user instanceof User) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 400);
            }

            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not change password'
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(): JsonResponse
    {
        try {
            /** @var User|null $user */
            $user = auth()->user();

            if (!$user instanceof User) {
                // It's okay to call logout even if there's no user, but return unauthenticated
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out'
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not log out'
            ], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refresh(): JsonResponse
    {
        try {
            $token = JWTAuth::refresh();

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not refresh token'
            ], 500);
        }
    }

    /**
     * Approve member (Admin only)
     */
    public function approveMember(Request $request, $memberId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            /** @var User $member */
            $member = User::findOrFail($memberId);
            /** @var User|null $admin */
            $admin = auth()->user();

            if (!$admin instanceof User) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            if (!$admin->isStaff()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($request->action === 'approve') {
                $member->update([
                    'status' => 'active',
                    'approved_at' => now(),
                    'approved_by' => $admin->id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Member approved successfully'
                ]);
            } else {
                $member->update([
                    'status' => 'rejected',
                    // You might want to add a rejection_reason field to the users table
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Member rejected successfully'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not process member approval'
            ], 500);
        }
    }
}
