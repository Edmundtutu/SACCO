<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\SavingsProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SavingsController extends Controller
{
    public function index()
    {
        $stats = [
            'total_accounts' => Account::where('account_type', 'savings')->count(),
            'total_balance' => Account::where('account_type', 'savings')->sum('balance'),
            'active_accounts' => Account::where('account_type', 'savings')->where('status', 'active')->count(),
            'recent_transactions' => Transaction::with(['account.member'])
                ->whereHas('account', function($q) {
                    $q->where('account_type', 'savings');
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Savings', 'url' => route('admin.savings.index')]
        ];

        return view('admin.savings.index', compact('stats', 'breadcrumbs'));
    }

    public function accounts(Request $request)
    {
        $query = Account::where('account_type', 'savings')->with(['member', 'savingsProduct']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('member_number', 'like', "%{$search}%");
            })->orWhere('account_number', 'like', "%{$search}%");
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $accounts = $query->orderBy('created_at', 'desc')->paginate(20);

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Savings', 'url' => route('admin.savings.index')],
            ['text' => 'Accounts', 'url' => '']
        ];

        return view('admin.savings.accounts', compact('accounts', 'breadcrumbs'));
    }

    public function showAccount($id)
    {
        $account = Account::where('account_type', 'savings')
            ->with(['member', 'transactions' => function($q) {
                $q->orderBy('created_at', 'desc');
            }, 'savingsProduct'])
            ->findOrFail($id);

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Savings', 'url' => route('admin.savings.index')],
            ['text' => 'Accounts', 'url' => route('admin.savings.accounts')],
            ['text' => $account->account_number, 'url' => '']
        ];

        return view('admin.savings.show-account', compact('account', 'breadcrumbs'));
    }

    public function transactions(Request $request)
    {
        $query = Transaction::with(['account.member'])
            ->whereHas('account', function($q) {
                $q->where('account_type', 'savings');
            });

        // Date filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Transaction type filter
        if ($request->has('type') && $request->type) {
            $query->where('transaction_type', $request->type);
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Savings', 'url' => route('admin.savings.index')],
            ['text' => 'Transactions', 'url' => '']
        ];

        return view('admin.savings.transactions', compact('transactions', 'breadcrumbs'));
    }

    public function products()
    {
        $products = SavingsProduct::orderBy('name')->get();

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Savings', 'url' => route('admin.savings.index')],
            ['text' => 'Products', 'url' => '']
        ];

        return view('admin.savings.products', compact('products', 'breadcrumbs'));
    }

    public function manualTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|exists:accounts,id',
            'transaction_type' => 'required|in:deposit,withdrawal',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $account = Account::findOrFail($request->account_id);

        // Check if withdrawal amount is available
        if ($request->transaction_type == 'withdrawal' && $account->balance < $request->amount) {
            return redirect()->back()
                ->with('error', 'Insufficient balance for withdrawal.')
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Create transaction
            $transaction = Transaction::create([
                'account_id' => $account->id,
                'transaction_type' => $request->transaction_type,
                'amount' => $request->amount,
                'description' => $request->description,
                'status' => 'completed',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            // Update account balance
            if ($request->transaction_type == 'deposit') {
                $account->increment('balance', $request->amount);
            } else {
                $account->decrement('balance', $request->amount);
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Manual transaction completed successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to process transaction: ' . $e->getMessage())
                ->withInput();
        }
    }
}
