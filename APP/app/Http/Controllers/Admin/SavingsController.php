<?php

namespace App\Http\Controllers\Admin;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\SavingsAccount;
use App\Models\SavingsProduct;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SavingsController extends Controller
{
    public function index()
    {
        $stats = [
            'total_accounts' => Account::where('accountable_type', SavingsAccount::class)->count(), // returns all account with in all saving products type [wallet, compulsory, voluntary, fixed_deposit, special]
            'total_balance' => SavingsAccount::whereHas('savingsProduct', function ($qn) {
                $qn->whereIn('type', ['compulsory', 'voluntary', 'fixed_deposit']);
            })->sum('balance'),
            'active_accounts' => Account::where('accountable_type', SavingsAccount::class)->where('status', 'active')->count(),
            'recent_transactions' => Transaction::with(['account.member'])
                ->whereHas('account', function ($q) {
                    $q->where('accountable_type', SavingsAccount::class);
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
        // $query = Account::where('accountable_type', SavingsAccount::class)->with(['member', 'accountable.savingsProduct']); // this query extrats even Wallet accounts.
        $query = Account::where('accountable_type', SavingsAccount::class)
            ->whereHasMorph('accountable', [SavingsAccount::class], function ($query) {
                $query->whereHas('savingsProduct', function ($q) {
                    $q->where('code', '!=', 'WL001');
                });
            })
            ->with(['member', 'accountable.savingsProduct']);
        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
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
        $account = Account::where('accountable_type', SavingsAccount::class)
            ->with([
                'member',
                'transactions' => function ($q) {
                    $q->orderBy('created_at', 'desc');
                },
                'accountable.savingsProduct'
            ])
            ->findOrFail($id);


        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Savings', 'url' => route('admin.savings.index')],
            ['text' => 'Accounts', 'url' => route('admin.savings.accounts')],
            ['text' => $account->account_number, 'url' => '']
        ];

        return view('admin.savings.show', compact('account', 'breadcrumbs'));
    }

    public function transactions(Request $request)
    {
        // $query = Transaction::with(['account.member'])
        //     ->whereHas('account', function ($q) {
        //         $q->where('accountable_type', SavingsAccount::class); // this query extrats even Wallet accounts.
        //     });
        
        // Tis query version excludes tranasctions made for the wallet accounts.
        $query = Transaction::with(['account.member'])
            ->whereHas('account', function ($q) {
                $q->where('accountable_type', SavingsAccount::class)
                    ->whereHasMorph('accountable', [SavingsAccount::class], function ($sq) {
                        $sq->whereHas('savingsProduct', function ($p) {
                            $p->where('code', '!=', 'WL001');
                        });
                    });
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
        $products = SavingsProduct::orderBy('created_at', 'desc')->get();

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Savings', 'url' => route('admin.savings.index')],
            ['text' => 'Products', 'url' => '']
        ];

        return view('admin.savings.products', compact('products', 'breadcrumbs'));
    }

    public function createProduct()
    {
        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Savings', 'url' => route('admin.savings.index')],
            ['text' => 'Products', 'url' => route('admin.savings.products')],
            ['text' => 'Create', 'url' => '']
        ];

        return view('admin.savings.partials.create-product', compact('breadcrumbs'));
    }

    public function storeProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:savings_products,code',
            'description' => 'nullable|string',
            'type' => 'required|in:wallet,compulsory,voluntary,fixed_deposit,special',
            'minimum_balance' => 'required|numeric|min:0',
            'maximum_balance' => 'nullable|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'interest_calculation' => 'required|in:simple,compound',
            'interest_payment_frequency' => 'required|in:daily,weekly,monthly,quarterly,annually',
            'minimum_monthly_contribution' => 'nullable|numeric|min:0',
            'maturity_period_months' => 'nullable|integer|min:0',
            'withdrawal_fee' => 'required|numeric|min:0',
            'allow_partial_withdrawals' => 'boolean',
            'minimum_notice_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $product = SavingsProduct::create([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'type' => $request->type,
                'minimum_balance' => $request->minimum_balance,
                'maximum_balance' => $request->maximum_balance,
                'interest_rate' => $request->interest_rate,
                'interest_calculation' => $request->interest_calculation,
                'interest_payment_frequency' => $request->interest_payment_frequency,
                'minimum_monthly_contribution' => $request->minimum_monthly_contribution,
                'maturity_period_months' => $request->maturity_period_months,
                'withdrawal_fee' => $request->withdrawal_fee,
                'allow_partial_withdrawals' => $request->has('allow_partial_withdrawals'),
                'minimum_notice_days' => $request->minimum_notice_days,
                'is_active' => $request->has('is_active'),
            ]);

            DB::commit();

            return redirect()->route('admin.savings.products')
                ->with('success', 'Savings product created successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to create product: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function editProduct($id)
    {
        $product = SavingsProduct::findOrFail($id);

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Savings', 'url' => route('admin.savings.index')],
            ['text' => 'Products', 'url' => route('admin.savings.products')],
            ['text' => 'Edit', 'url' => '']
        ];

        return view('admin.savings.partials.edit-product', compact('product', 'breadcrumbs'));
    }

    public function updateProduct(Request $request, $id)
    {
        $product = SavingsProduct::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:savings_products,code,' . $id,
            'description' => 'nullable|string',
            'type' => 'required|in:wallet,compulsory,voluntary,fixed_deposit,special',
            'minimum_balance' => 'required|numeric|min:0',
            'maximum_balance' => 'nullable|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'interest_calculation' => 'required|in:simple,compound',
            'interest_payment_frequency' => 'required|in:daily,weekly,monthly,quarterly,annually',
            'minimum_monthly_contribution' => 'nullable|numeric|min:0',
            'maturity_period_months' => 'nullable|integer|min:0',
            'withdrawal_fee' => 'required|numeric|min:0',
            'allow_partial_withdrawals' => 'boolean',
            'minimum_notice_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $product->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'type' => $request->type,
                'minimum_balance' => $request->minimum_balance,
                'maximum_balance' => $request->maximum_balance,
                'interest_rate' => $request->interest_rate,
                'interest_calculation' => $request->interest_calculation,
                'interest_payment_frequency' => $request->interest_payment_frequency,
                'minimum_monthly_contribution' => $request->minimum_monthly_contribution,
                'maturity_period_months' => $request->maturity_period_months,
                'withdrawal_fee' => $request->withdrawal_fee,
                'allow_partial_withdrawals' => $request->has('allow_partial_withdrawals'),
                'minimum_notice_days' => $request->minimum_notice_days,
                'is_active' => $request->has('is_active'),
            ]);

            DB::commit();

            return redirect()->route('admin.savings.products')
                ->with('success', 'Savings product updated successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to update product: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function deleteProduct($id)
    {
        $product = SavingsProduct::findOrFail($id);

        // Check if product has active accounts
        if ($product->accounts()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete product with active accounts. Please deactivate it instead.');
        }

        DB::beginTransaction();
        try {
            $product->delete();
            DB::commit();

            return redirect()->route('admin.savings.products')
                ->with('success', 'Savings product deleted successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to delete product: ' . $e->getMessage());
        }
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
