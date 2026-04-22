<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\TransactionDTO;
use App\Http\Controllers\Controller;
use App\Models\ExpenseRecord;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Phase 2 — Admin controller for recording and listing expenses.
 *
 * All writes go through TransactionService (no direct DB writes here).
 * The feature flag financial.enable_expense_transactions gates access.
 */
class ExpenseController extends Controller
{
    public function __construct(private TransactionService $transactionService) {}

    /**
     * Expense listing page.
     */
    public function index(Request $request)
    {
        abort_unless(config('financial.enable_expense_transactions'), 404);

        $query = ExpenseRecord::with(['transaction', 'recordedBy'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        $expenses   = $query->paginate(25)->withQueryString();
        $categories = config('financial.expense_categories', []);
        $totalShown = $query->sum('amount');

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Expenses', 'url' => route('admin.expenses.index')],
        ];

        return view('admin.expenses.index', compact(
            'expenses', 'categories', 'totalShown', 'breadcrumbs'
        ));
    }

    /**
     * Show expense creation form.
     */
    public function create()
    {
        abort_unless(config('financial.enable_expense_transactions'), 404);

        $categories     = config('financial.expense_categories', []);
        $paymentMethods = ['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'mobile_money' => 'Mobile Money'];

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Expenses', 'url' => route('admin.expenses.index')],
            ['text' => 'Record Expense', 'url' => ''],
        ];

        return view('admin.expenses.create', compact(
            'categories', 'paymentMethods', 'breadcrumbs'
        ));
    }

    /**
     * Process and store a new expense via TransactionService.
     */
    public function store(Request $request)
    {
        abort_unless(config('financial.enable_expense_transactions'), 404);

        $validated = $request->validate([
            'amount'            => ['required', 'numeric', 'min:1'],
            'category'          => ['required', 'string', 'in:' . implode(',', array_keys(config('financial.expense_categories', [])))],
            'payment_method'    => ['required', 'in:cash,bank_transfer,mobile_money'],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'description'       => ['nullable', 'string', 'max:500'],
        ]);

        $dto = TransactionDTO::fromArray([
            'member_id'         => auth()->id(),
            'type'              => 'expense',
            'amount'            => $validated['amount'],
            'payment_method'    => $validated['payment_method'],
            'payment_reference' => $validated['payment_reference'] ?? null,
            'description'       => $validated['description'] ?? null,
            'processed_by'      => auth()->id(),
            'metadata'          => [
                'category' => $validated['category'],
            ],
        ]);

        try {
            $transaction = $this->transactionService->processTransaction($dto);
            $expense = $transaction->expenseRecord;

            return redirect()
                ->route('admin.expenses.receipt', $expense->id)
                ->with('success', 'Expense recorded successfully.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to record expense: ' . $e->getMessage());
        }
    }

    /**
     * Show a single expense record.
     */
    public function show(int $id)
    {
        abort_unless(config('financial.enable_expense_transactions'), 404);

        $expense = ExpenseRecord::with(['transaction.generalLedgerEntries', 'recordedBy'])
            ->findOrFail($id);

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Expenses', 'url' => route('admin.expenses.index')],
            ['text' => 'Expense #' . $expense->receipt_number, 'url' => ''],
        ];

        return view('admin.expenses.show', compact('expense', 'breadcrumbs'));
    }

    /**
     * Printable receipt for an expense.
     */
    public function receipt(int $id)
    {
        abort_unless(config('financial.enable_expense_transactions'), 404);

        $expense = ExpenseRecord::with(['transaction', 'recordedBy'])->findOrFail($id);

        return view('admin.expenses.receipt', compact('expense'));
    }
}
