<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\SavingsAccount;
use App\Models\Loan;
use App\Models\Transaction;
use App\Models\Share;
use App\Models\ExpenseRecord;
use App\Models\IncomeRecord;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $activeTenant = tenant();
        $user = auth()->user();

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
        ];

        // Super admin with no tenant selected — show platform-wide overview.
        if ($user->isSuperAdmin() && !$activeTenant) {
            $platformStats = [
                'total_saccos'       => Tenant::withTrashed(false)->count(),
                'active_saccos'      => Tenant::where('status', 'active')->count(),
                'trial_saccos'       => Tenant::where('status', 'trial')->count(),
                'suspended_saccos'   => Tenant::where('status', 'suspended')->count(),
                'total_members'      => User::withoutGlobalScopes()->where('role', 'member')->count(),
                'total_staff'        => User::withoutGlobalScopes()->whereIn('role', ['admin', 'staff_level_1', 'staff_level_2', 'staff_level_3'])->count(),
                'total_loans'        => Loan::withoutGlobalScopes()->count(),
                'active_loans'       => Loan::withoutGlobalScopes()->where('status', 'active')->count(),
                'today_transactions' => Transaction::withoutGlobalScopes()->whereDate('created_at', today())->count(),
                'recent_saccos'      => Tenant::withCount(['users', 'loans'])->orderByDesc('created_at')->limit(5)->get(),
            ];

            return view('admin.dashboard.index', [
                'isSuperAdminNeutral' => true,
                'platformStats'       => $platformStats,
                'stats'               => null,
                'limits'              => null,
                'activeTenant'        => null,
                'breadcrumbs'         => $breadcrumbs,
            ]);
        }

        // Normal path: tenant-bound staff OR super admin that has selected a tenant.
        $stats = [
            'total_members'       => User::where('role', 'member')->count(),
            'pending_members'     => User::where('role', 'member')->where('status', 'pending_approval')->count(),
            'active_members'      => User::where('role', 'member')->where('status', 'active')->count(),
            'active_staff'        => User::whereIn('role', ['admin', 'staff_level_1', 'staff_level_2', 'staff_level_3'])->count(),
            'total_savings'       => SavingsAccount::whereHas('savingsProduct', function ($query) {
                $query->whereIn('type', ['compulsory', 'voluntary', 'fixed_deposit']);
            })->sum('balance'),
            'total_loans'         => Loan::sum('principal_amount'),
            'active_loans'        => Loan::where('status', 'active')->count(),
            'pending_loans'       => Loan::where('status', 'pending')->count(),
            'total_shares'        => Transaction::where('type', 'share_purchase')->sum('net_amount'),
            'pending_transactions' => Transaction::where('status', 'pending')->count(),
            'recent_transactions' => Transaction::where('created_at', '>=', now()->subDays(5))
                ->with(['account.member'])
                ->orderBy('created_at', 'desc')
                ->paginate(8),
            'recent_loans'        => Loan::with(['member'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'today_transactions'  => Transaction::whereDate('created_at', today())->sum('amount'),
        ];

        // Phase 2 — feature-flagged expense / income summary for this calendar month
        if (config('financial.enable_expense_transactions')) {
            $stats['monthly_expenses']      = ExpenseRecord::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount');
            $stats['monthly_expense_count'] = ExpenseRecord::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
        }

        if (config('financial.enable_income_transactions')) {
            $stats['monthly_income']        = IncomeRecord::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount');
            $stats['monthly_income_count']  = IncomeRecord::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
        }

        if (config('financial.enable_expense_transactions') || config('financial.enable_income_transactions')) {
            $stats['monthly_net'] = ($stats['monthly_income'] ?? 0) - ($stats['monthly_expenses'] ?? 0);
        }

        $limits = $activeTenant ? [
            'max_members'    => $activeTenant->max_members,
            'max_staff'      => $activeTenant->max_staff,
            'max_loans'      => $activeTenant->max_loans,
            'max_loan_amount' => $activeTenant->max_loan_amount,
        ] : null;

        return view('admin.dashboard.index', [
            'isSuperAdminNeutral' => false,
            'platformStats'       => null,
            'stats'               => $stats,
            'limits'              => $limits,
            'activeTenant'        => $activeTenant,
            'breadcrumbs'         => $breadcrumbs,
        ]);
    }
}
