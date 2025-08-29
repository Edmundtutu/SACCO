<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account;
use App\Models\Loan;
use App\Models\Share;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function index()
    {
        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Reports', 'url' => route('admin.reports.index')]
        ];

        return view('admin.reports.index', compact('breadcrumbs'));
    }

    public function membersReport(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth());
        $dateTo = $request->input('date_to', Carbon::now()->endOfMonth());

        $stats = [
            'total_members' => User::where('role', 'member')->count(),
            'new_members' => User::where('role', 'member')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'active_members' => User::where('role', 'member')
                ->where('status', 'active')
                ->count(),
            'pending_members' => User::where('role', 'member')
                ->where('status', 'pending')
                ->count(),
        ];

        $members = User::where('role', 'member')
            ->with(['accounts', 'loans', 'shares'])
            ->when($request->filled('date_from'), function($q) use ($dateFrom) {
                return $q->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->filled('date_to'), function($q) use ($dateTo) {
                return $q->whereDate('created_at', '<=', $dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Reports', 'url' => route('admin.reports.index')],
            ['text' => 'Members Report', 'url' => '']
        ];

        return view('admin.reports.members', compact('stats', 'members', 'breadcrumbs', 'dateFrom', 'dateTo'));
    }

    public function savingsReport(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth());
        $dateTo = $request->input('date_to', Carbon::now()->endOfMonth());

        $stats = [
            'total_accounts' => Account::where('account_type', 'savings')->count(),
            'total_balance' => Account::where('account_type', 'savings')->sum('balance'),
            'total_deposits' => Transaction::where('transaction_type', 'deposit')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('amount'),
            'total_withdrawals' => Transaction::where('transaction_type', 'withdrawal')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('amount'),
        ];

        $accounts = Account::where('account_type', 'savings')
            ->with(['user', 'transactions' => function($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom, $dateTo]);
            }])
            ->orderBy('balance', 'desc')
            ->paginate(50);

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Reports', 'url' => route('admin.reports.index')],
            ['text' => 'Savings Report', 'url' => '']
        ];

        return view('admin.reports.savings', compact('stats', 'accounts', 'breadcrumbs', 'dateFrom', 'dateTo'));
    }

    public function loansReport(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth());
        $dateTo = $request->input('date_to', Carbon::now()->endOfMonth());

        $stats = [
            'total_loans' => Loan::count(),
            'active_loans' => Loan::where('status', 'active')->count(),
            'pending_loans' => Loan::where('status', 'pending')->count(),
            'total_disbursed' => Loan::whereIn('status', ['active', 'completed'])
                ->sum('principal_amount'),
            'total_outstanding' => Loan::where('status', 'active')
                ->sum('outstanding_balance'),
        ];

        $loans = Loan::with(['user', 'loanProduct', 'repayments'])
            ->when($request->filled('date_from'), function($q) use ($dateFrom) {
                return $q->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->filled('date_to'), function($q) use ($dateTo) {
                return $q->whereDate('created_at', '<=', $dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Reports', 'url' => route('admin.reports.index')],
            ['text' => 'Loans Report', 'url' => '']
        ];

        return view('admin.reports.loans', compact('stats', 'loans', 'breadcrumbs', 'dateFrom', 'dateTo'));
    }

    public function financialReport(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth());
        $dateTo = $request->input('date_to', Carbon::now()->endOfMonth());

        $stats = [
            'total_assets' => Account::sum('balance'),
            'total_loans_outstanding' => Loan::where('status', 'active')->sum('outstanding_balance'),
            'total_shares' => Share::where('status', 'approved')->sum('amount'),
            'total_deposits' => Transaction::where('transaction_type', 'deposit')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('amount'),
            'total_withdrawals' => Transaction::where('transaction_type', 'withdrawal')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->sum('amount'),
        ];

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Reports', 'url' => route('admin.reports.index')],
            ['text' => 'Financial Report', 'url' => '']
        ];

        return view('admin.reports.financial', compact('stats', 'breadcrumbs', 'dateFrom', 'dateTo'));
    }

    public function trialBalance()
    {
        // This would typically pull from your chart of accounts
        // For now, we'll show basic account balances
        
        $assets = [
            ['account' => 'Cash and Bank', 'debit' => Account::sum('balance'), 'credit' => 0],
            ['account' => 'Loans Receivable', 'debit' => Loan::where('status', 'active')->sum('outstanding_balance'), 'credit' => 0],
        ];

        $liabilities = [
            ['account' => 'Member Deposits', 'debit' => 0, 'credit' => Account::where('account_type', 'savings')->sum('balance')],
            ['account' => 'Share Capital', 'debit' => 0, 'credit' => Share::where('status', 'approved')->sum('amount')],
        ];

        $totalDebits = collect($assets)->sum('debit') + collect($liabilities)->sum('debit');
        $totalCredits = collect($assets)->sum('credit') + collect($liabilities)->sum('credit');

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Reports', 'url' => route('admin.reports.index')],
            ['text' => 'Trial Balance', 'url' => '']
        ];

        return view('admin.reports.trial-balance', compact('assets', 'liabilities', 'totalDebits', 'totalCredits', 'breadcrumbs'));
    }

    public function balanceSheet()
    {
        $assets = [
            'current_assets' => [
                'cash_and_bank' => Account::sum('balance'),
                'loans_receivable' => Loan::where('status', 'active')->sum('outstanding_balance'),
            ],
        ];

        $liabilities = [
            'current_liabilities' => [
                'member_deposits' => Account::where('account_type', 'savings')->sum('balance'),
            ],
        ];

        $equity = [
            'share_capital' => Share::where('status', 'approved')->sum('amount'),
        ];

        $totalAssets = collect($assets['current_assets'])->sum();
        $totalLiabilities = collect($liabilities['current_liabilities'])->sum();
        $totalEquity = collect($equity)->sum();

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Reports', 'url' => route('admin.reports.index')],
            ['text' => 'Balance Sheet', 'url' => '']
        ];

        return view('admin.reports.balance-sheet', compact('assets', 'liabilities', 'equity', 'totalAssets', 'totalLiabilities', 'totalEquity', 'breadcrumbs'));
    }
}