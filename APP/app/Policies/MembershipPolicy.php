<?php

namespace App\Policies;

use App\Models\Membership\Membership;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;


class MembershipPolicy
{
    /**
     * Determine if the user can approve membership at staff_level_1.
     */
    public function approveLevel1(User $user, Membership $membership): Response
    {
        return $user->role === 'staff_level_1'
            ? Response::allow()
            : Response::deny('Only staff_level_1 can approve at this level.');
    }

    /**
     * Determine if the user can approve membership at staff_level_2.
     */
    public function approveLevel2(User $user, Membership $membership): Response
    {
        if ($user->role !== 'staff_level_2') {
            return Response::deny('Only staff_level_2 can approve at this level.');
        }

        if ($membership->approved_by_level_1 === null) {
            return Response::deny('This membership must first be approved by staff_level_1.');
        }

        return Response::allow();
    }

    /**
     * Determine if the user can approve membership at staff_level_3.
     */
    public function approveLevel3(User $user, Membership $membership): Response
    {
        if ($user->role !== 'staff_level_3') {
            return Response::deny('Only staff_level_3 can approve at this level.');
        }

        if ($membership->approved_by_level_2 === null) {
            return Response::deny('This membership must first be approved by staff_level_2.');
        }

        return Response::allow();
    }
}

