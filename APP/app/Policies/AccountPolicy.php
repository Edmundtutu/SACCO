<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Account $account)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Account $account)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Account $account)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Account $account)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Account $account)
    {
        //
    }

    public function viewTransactionHistory(User $user, Account $account)
    {
        // Admin and staff can view all transaction history
        if (in_array($user->role, ['admin', 'staff', 'loan_officer'])) {
            return true;
        }

        // Members can only view their own account transaction history
        return $account->member_id === $user->id;
    }

    public function viewAccountBalance(User $user, Account $account)
    {
        // Admin and staff can view all account balances
        if (in_array($user->role, ['admin', 'staff', 'loan_officer'])) {
            return true;
        }

        // Members can only view their own account balance
        return $account->member_id === $user->id;
    }

    public function reverseTransaction(User $user, Transaction $transaction)
    {
        // Admin and staff can reverse any transaction
        if (in_array($user->role, ['admin', 'staff'])) {
            return true;
        }

        // Members can only reverse their own transactions if they're pending
        return $transaction->member_id === $user->id && $transaction->status === 'pending';
    }
}
