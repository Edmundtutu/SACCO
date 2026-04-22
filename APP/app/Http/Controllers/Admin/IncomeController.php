<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\TransactionDTO;
use App\Http\Controllers\Controller;
use App\Models\IncomeRecord;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Http\Request;

/**
 * Phase 2 — Admin controller for recording and listing non-loan income.
 *
 * All writes go through TransactionService (no direct DB writes here).
 * The feature flag financial.enable_income_transactions gates access.
 */
class IncomeController extends Controller
{
    public function __construct(private TransactionService $transactionService) {}

    /**
     * Income listing page.
     */
    public function index(Request $request)
    {
        abort_unless(config('financial.enable_income_transactions'), 404);

        $query = IncomeRecord::with(['transaction', 'payerMember', 'recordedBy'])
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

        $incomes     = $query->paginate(25)->withQueryString();
        $categories  = config('financial.income_categories', []);
        $totalShown  = $query->sum('amount');

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Income', 'url' => route('admin.incomes.index')],
        ];

        return view('admin.incomes.index', compact(
            'incomes', 'categories', 'totalShown', 'breadcrumbs'
        ));
    }

    /**
     * Show income creation form.
     */
    public function create()
    {
        abort_unless(config('financial.enable_income_transactions'), 404);

        $categories     = config('financial.income_categories', []);
        $paymentMethods = ['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'mobile_money' => 'Mobile Money'];
        $members        = User::where('role', 'member')->where('status', 'active')->orderBy('name')->get();

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Income', 'url' => route('admin.incomes.index')],
            ['text' => 'Record Income', 'url' => ''],
        ];

        return view('admin.incomes.create', compact(
            'categories', 'paymentMethods', 'members', 'breadcrumbs'
        ));
    }

    /**
     * Process and store a new income record via TransactionService.
     */
    public function store(Request $request)
    {
        abort_unless(config('financial.enable_income_transactions'), 404);

        $validated = $request->validate([
            'amount'            => ['required', 'numeric', 'min:1'],
            'category'          => ['required', 'string', 'in:' . implode(',', array_keys(config('financial.income_categories', [])))],
            'payment_method'    => ['required', 'in:cash,bank_transfer,mobile_money'],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'description'       => ['nullable', 'string', 'max:500'],
            'payer_member_id'   => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $dto = TransactionDTO::fromArray([
            'member_id'         => auth()->id(),
            'type'              => 'income',
            'amount'            => $validated['amount'],
            'payment_method'    => $validated['payment_method'],
            'payment_reference' => $validated['payment_reference'] ?? null,
            'description'       => $validated['description'] ?? null,
            'processed_by'      => auth()->id(),
            'metadata'          => [
                'category'        => $validated['category'],
                'payer_member_id' => $validated['payer_member_id'] ?? null,
            ],
        ]);

        try {
            $transaction = $this->transactionService->processTransaction($dto);
            $income = $transaction->incomeRecord;

            return redirect()
                ->route('admin.incomes.receipt', $income->id)
                ->with('success', 'Income recorded successfully.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to record income: ' . $e->getMessage());
        }
    }

    /**
     * Show a single income record.
     */
    public function show(int $id)
    {
        abort_unless(config('financial.enable_income_transactions'), 404);

        $income = IncomeRecord::with(['transaction.generalLedgerEntries', 'payerMember', 'recordedBy'])
            ->findOrFail($id);

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Income', 'url' => route('admin.incomes.index')],
            ['text' => 'Income #' . $income->receipt_number, 'url' => ''],
        ];

        return view('admin.incomes.show', compact('income', 'breadcrumbs'));
    }

    /**
     * Printable receipt for an income record.
     */
    public function receipt(int $id)
    {
        abort_unless(config('financial.enable_income_transactions'), 404);

        $income = IncomeRecord::with(['transaction', 'payerMember', 'recordedBy'])->findOrFail($id);

        return view('admin.incomes.receipt', compact('income'));
    }
}
