<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MemberProfile;
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
    // TODO: The entire register function should be refactored process a user record and temporary pending_approval membership registration as required in the database
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Generate member number
            $lastMember = User::where('role', 'member')->orderBy('id', 'desc')->first();
            $memberNumber = 'M' . str_pad(($lastMember->id ?? 0) + 1, 6, '0', STR_PAD_LEFT);

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'member_number' => $memberNumber,
                'role' => 'member',
                'status' => 'pending_approval',
                'phone' => $request->phone,
                'national_id' => $request->national_id,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'address' => $request->address,
                'occupation' => $request->occupation,
                'monthly_income' => $request->monthly_income,
                'membership_date' => now(),
            ]);

            // Create member profile
            $user->memberProfile()->create([
                'next_of_kin_name' => $request->next_of_kin_name,
                'next_of_kin_relationship' => $request->next_of_kin_relationship,
                'next_of_kin_phone' => $request->next_of_kin_phone,
                'next_of_kin_address' => $request->next_of_kin_address,
                'employer_name' => $request->employer_name,
                'employer_address' => $request->employer_address,
                'employer_phone' => $request->employer_phone,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registration successful. Your membership is pending approval.',
                'data' => [
                    'member_number' => $user->member_number,
                    'status' => $user->status,
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
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $user = auth()->user();

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
                        'member_number' => $user->member_number,
                        'role' => $user->role,
                        'status' => $user->status,
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
            $user = auth()->user();
            $user->load('membership.profile', 'accounts', 'loans', 'shares');

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = auth()->user();
            // update the user's name
            $user->update($request->only([ 'name' ]));
            // update the user's membership profilw instead
            $membership_profile = $user->load('membership.profile')->membership->profile;
            $membership_profile->update($request->only([
                'name', 'phone', 'address', 'occupation', 'monthly_income'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user
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
            $user = auth()->user();

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
            JWTAuth::logout();

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
            $member = User::findOrFail($memberId);
            $admin = auth()->user();

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
