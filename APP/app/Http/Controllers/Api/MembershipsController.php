<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membership\Membership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MembershipsController extends Controller
{
    /**
     * List membership requests with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Membership::with(['user', 'profile'])
            ->when($request->get('approval_status'), function ($q, $status) {
                $q->where('approval_status', $status);
            }, function ($q) {
                $q->where('approval_status', 'pending');
            })
            ->when($request->get('profile_type'), function ($q, $type) {
                $map = [
                    'individual' => '\\App\\Models\\Membership\\IndividualProfile',
                    'vsla' => '\\App\\Models\\Membership\\VslaProfile',
                    'mfi' => '\\App\\Models\\Membership\\MfiProfile',
                ];
                if (isset($map[$type])) {
                    $q->where('profile_type', $map[$type]);
                }
            })
            ->when($request->get('search'), function ($q, $term) {
                $q->where(function ($sub) use ($term) {
                    $sub->where('id', 'like', "%$term%")
                        ->orWhereHas('user', function ($uq) use ($term) {
                            $uq->where('name', 'like', "%$term%")
                               ->orWhere('email', 'like', "%$term%");
                        });
                });
            })
            ->orderByDesc('created_at');

        $memberships = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $memberships,
        ]);
    }

    /**
     * Show a membership with related user and profile.
     */
    public function show(Membership $membership): JsonResponse
    {
        $membership->load(['user', 'profile', 'levelOneApprovedBy', 'levelTwoApprovedBy', 'levelThreeApprovedBy']);
        return response()->json([
            'success' => true,
            'data' => $membership,
        ]);
    }

    public function approveLevel1(Membership $membership): JsonResponse
    {
        $this->authorize('approve_level_1', $membership);

        if ($membership->approved_at_level_1) {
            return response()->json(['success' => true, 'message' => 'Already approved at level 1']);
        }

        $membership->update([
            'approved_by_level_1' => auth()->id(),
            'approved_at_level_1' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Membership approved at level 1']);
    }

    public function approveLevel2(Membership $membership): JsonResponse
    {
        $this->authorize('approve_level_2', $membership);

        if ($membership->approved_at_level_2) {
            return response()->json(['success' => true, 'message' => 'Already approved at level 2']);
        }

        $membership->update([
            'approved_by_level_2' => auth()->id(),
            'approved_at_level_2' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Membership approved at level 2']);
    }

    public function approveLevel3(Membership $membership): JsonResponse
    {
        $this->authorize('approve_level_3', $membership);

        if ($membership->approved_at_level_3) {
            return response()->json(['success' => true, 'message' => 'Already approved at level 3']);
        }

        // Final approval, mirror Admin controller behavior
        $member = $membership->user;

        $membership->update([
            'approved_by_level_3' => auth()->id(),
            'approved_at_level_3' => now(),
            'approval_status' => 'approved',
        ]);

        // Activate the user on final approval
        $member->update([
            'status' => 'active',
            'account_verified_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Membership fully approved']);
    }
}

