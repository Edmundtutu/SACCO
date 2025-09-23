<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account;
use App\Models\Loan;
use App\Models\Transaction;
use App\Models\Share;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Get dashboard statistics
        $stats = [
            'total_members' => User::where('role', 'member')->count(),
            'pending_members' => User::where('role', 'member')->where('status', 'pending')->count(),
            'active_members' => User::where('role', 'member')->where('status', 'active')->count(),
            'total_savings' => Account::where('account_type', 'savings')->sum('balance'),
            'total_loans' => Loan::sum('principal_amount'),
            'active_loans' => Loan::where('status', 'active')->count(),
            'pending_loans' => Loan::where('status', 'pending')->count(),
        //    'total_shares' => Share::sum('amount'),
            'total_shares' => Transaction::where('type', 'share_purchase')->sum('net_amount'),
            'recent_transactions' => Transaction::where('created_at', '>=', now()->subDays(5))->with(['account.member'])
                ->orderBy('created_at', 'desc')
                ->paginate(8),
            'recent_loans' => Loan::with(['member'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')]
        ];

        return view('admin.dashboard.index', compact('stats', 'breadcrumbs'));
    }
}
