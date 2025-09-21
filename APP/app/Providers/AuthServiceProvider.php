<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Account;
use App\Models\Loan;
use App\Models\Transaction;
use App\Policies\AccountPolicy;
use App\Policies\LoanPolicy;
use App\Policies\MembershipPolicy;
use App\Policies\TransactionPolicy;
use App\Models\Membership\Membership;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Membership::class => MembershipPolicy::class,
        Account::class => AccountPolicy::class,
        Loan::class => LoanPolicy::class,
        Transaction::class => TransactionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
