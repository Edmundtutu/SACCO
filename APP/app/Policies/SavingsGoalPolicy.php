<?php

namespace App\Policies;

use App\Models\SavingsGoal;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SavingsGoalPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isActiveMember() || $user->isStaff();
    }

    public function view(User $user, SavingsGoal $goal): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $goal->member_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isActiveMember();
    }

    public function update(User $user, SavingsGoal $goal): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $goal->member_id === $user->id;
    }

    public function delete(User $user, SavingsGoal $goal): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        return $goal->member_id === $user->id;
    }
}
