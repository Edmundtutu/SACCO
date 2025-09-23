<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProcessTransactionRequest;
use App\Http\Requests\Admin\ApproveTransactionRequest;
use App\Http\Requests\Admin\RejectTransactionRequest;
use App\Services\TransactionService;
use App\Services\BalanceService;
use App\Services\LedgerService;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\Loan;
use App\Models\User;
use App\DTOs\TransactionDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TransactionsController extends Controller
{
    protected TransactionService $transactionService;
    protected BalanceService $balanceService;
    protected LedgerService $ledgerService;

    public function __construct(
        TransactionService $transactionService,
        BalanceService $balanceService,
        LedgerService $ledgerService
    ) {
        $this->transactionService = $transactionService;
        $this->balanceService = $balanceService;
        $this->ledgerService = $ledgerService;
    }

    /**
     * Display transactions dashboard
     */
    public function index(Request $request): View
    {
        $query = Transaction::with(['member', 'account', 'relatedLoan', 'processedBy'])
            ->orderBy('transaction_date', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $transactions = $query->paginate(20);

        // Get summary statistics
        $stats = $this->getTransactionStats();

        // Get pending transactions for quick access
        $pendingTransactions = Transaction::where('status', 'pending')
            ->with(['member', 'account'])
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        return view('admin.transactions.index', compact(
            'transactions',
            'stats',
            'pendingTransactions'
        ));
    }

    /**
     * Show transaction details
     */
    public function show(string $id): View
    {
        $transaction = Transaction::with([
            'member',
            'account.savingsProduct',
            'relatedLoan.loanProduct',
            'processedBy',
            'reversedBy',
            'generalLedgerEntries'
        ])->findOrFail($id);

        return view('admin.transactions.show', compact('transaction'));
    }

    /**
     * Process a new transaction (admin initiated)
     */
    public function process(ProcessTransactionRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $transactionDTO = new TransactionDTO(
                memberId: $request->member_id,
                type: $request->type,
                amount: $request->amount,
                accountId: $request->account_id,
                feeAmount: $request->fee_amount,
                description: $request->description,
                relatedLoanId: $request->related_loan_id,
                processedBy: auth()->id(),
                metadata: $request->metadata
            );

            $transaction = $this->transactionService->processTransaction($transactionDTO);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction processed successfully',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'status' => $transaction->status,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Transaction processing failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Approve a pending transaction
     */
    public function approve(ApproveTransactionRequest $request, int $id): JsonResponse
    {
        try {
            $transaction = Transaction::findOrFail($id);

            if ($transaction->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction is not pending approval',
                ], 422);
            }

            DB::beginTransaction();

            // Process the transaction
            $transactionDTO = new TransactionDTO(
                memberId: $transaction->member_id,
                type: $transaction->type,
                amount: $transaction->amount,
                accountId: $transaction->account_id,
                feeAmount: $transaction->fee_amount,
                description: $transaction->description,
                relatedLoanId: $transaction->related_loan_id,
                processedBy: auth()->id(),
                metadata: $transaction->metadata
            );

            $processedTransaction = $this->transactionService->processTransaction($transactionDTO);

            // Update original transaction
            $transaction->update([
                'status' => 'approved',
                'processed_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction approved successfully',
                'data' => [
                    'transaction_id' => $processedTransaction->id,
                    'transaction_number' => $processedTransaction->transaction_number,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Transaction approval failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Reject a pending transaction
     */
    public function reject(RejectTransactionRequest $request, int $id): JsonResponse
    {
        try {
            $transaction = Transaction::findOrFail($id);

            if ($transaction->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction is not pending approval',
                ], 422);
            }

            $transaction->update([
                'status' => 'rejected',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $request->reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaction rejected successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction rejection failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Reverse a completed transaction
     */
    public function reverse(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ]);

        try {
            $reversalTransaction = $this->transactionService->reverseTransaction(
                $id,
                $request->reason,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaction reversed successfully',
                'data' => [
                    'reversal_transaction_id' => $reversalTransaction->id,
                    'reversal_transaction_number' => $reversalTransaction->transaction_number,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction reversal failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get transaction statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $stats = $this->getTransactionStats($request->date_from, $request->date_to);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get general ledger entries
     */
    public function generalLedger(Request $request): View
    {
        $query = \App\Models\GeneralLedger::with(['transaction'])
            ->orderBy('posted_at', 'desc');

        // Apply filters
        if ($request->filled('account_code')) {
            $query->where('account_code', $request->account_code);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('posted_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('posted_at', '<=', $request->date_to);
        }

        $ledgerEntries = $query->paginate(50);

        // Get chart of accounts for filter
        $chartOfAccounts = \App\Models\ChartOfAccount::orderBy('account_code')->get();

        return view('admin.transactions.general-ledger', compact(
            'ledgerEntries',
            'chartOfAccounts'
        ));
    }

    /**
     * Get trial balance
     */
    public function trialBalance(Request $request): View
    {
        $date = $request->date ?? now()->toDateString();
        
        $trialBalance = $this->ledgerService->getTrialBalance($date);

        return view('admin.transactions.trial-balance', compact('trialBalance', 'date'));
    }

    /**
     * Export transactions
     */
    public function export(Request $request)
    {
        $query = Transaction::with(['member', 'account', 'relatedLoan']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $transactions = $query->get();

        // Generate CSV
        $filename = 'transactions_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Transaction Number',
                'Date',
                'Member',
                'Type',
                'Amount',
                'Status',
                'Description',
                'Account',
                'Processed By'
            ]);

            // CSV data
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->transaction_number,
                    $transaction->transaction_date->format('Y-m-d H:i:s'),
                    $transaction->member->name ?? 'N/A',
                    ucfirst(str_replace('_', ' ', $transaction->type)),
                    number_format($transaction->amount, 2),
                    ucfirst($transaction->status),
                    $transaction->description,
                    $transaction->account->account_number ?? 'N/A',
                    $transaction->processedBy->name ?? 'System'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get transaction statistics
     */
    private function getTransactionStats(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = Transaction::where('status', 'completed');

        if ($dateFrom) {
            $query->whereDate('transaction_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('transaction_date', '<=', $dateTo);
        }

        $transactions = $query->get();

        return [
            'total_transactions' => $transactions->count(),
            'total_amount' => $transactions->sum('amount'),
            'total_deposits' => $transactions->where('type', 'deposit')->sum('amount'),
            'total_withdrawals' => $transactions->where('type', 'withdrawal')->sum('amount'),
            'total_loan_disbursements' => $transactions->where('type', 'loan_disbursement')->sum('amount'),
            'total_loan_repayments' => $transactions->where('type', 'loan_repayment')->sum('amount'),
            'total_share_purchases' => $transactions->where('type', 'share_purchase')->sum('amount'),
            'pending_transactions' => Transaction::where('status', 'pending')->count(),
            'rejected_transactions' => Transaction::where('status', 'rejected')->count(),
            'reversed_transactions' => Transaction::where('status', 'reversed')->count(),
        ];
    }
}
