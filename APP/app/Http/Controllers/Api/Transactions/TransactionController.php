<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\LoanDisbursementRequest;
use App\Http\Requests\LoanRepaymentRequest;
use App\Http\Requests\SharePurchaseRequest;
use App\Http\Resources\TransactionResource;
use App\Services\TransactionService;
use App\DTOs\TransactionDTO;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Process a deposit transaction
     */
    public function deposit(DepositRequest $request): JsonResponse
    {
        $transactionData = TransactionDTO::fromRequest($request);
        $transactionData->processedBy = Auth::id();

        $transaction = $this->transactionService->processTransaction($transactionData);

        return response()->json([
            'success' => true,
            'message' => 'Deposit processed successfully',
            'data' => new TransactionResource($transaction)
        ], 201);
    }

    /**
     * Process a withdrawal transaction
     */
    public function withdrawal(Request $request): JsonResponse
    {
        $request->validate([
            'member_id' => ['required', 'integer', 'exists:users,id'],
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'amount' => [
                'required',
                'numeric',
                'min:' . config('sacco.minimum_withdrawal_amount', 100),
                'max:' . config('sacco.maximum_transaction_amount', 10000000)
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $transactionData = TransactionDTO::fromRequest($request);
        $transactionData->processedBy = Auth::id();

        $transaction = $this->transactionService->processTransaction($transactionData);

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal processed successfully',
            'data' => new TransactionResource($transaction)
        ], 201);
    }

    /**
     * Process a share purchase transaction
     */
    public function sharePurchase(SharePurchaseRequest $request): JsonResponse
    {
        $transactionData = TransactionDTO::fromRequest($request);
        $transactionData->processedBy = Auth::id();

        $transaction = $this->transactionService->processTransaction($transactionData);

        return response()->json([
            'success' => true,
            'message' => 'Share purchase processed successfully',
            'data' => new TransactionResource($transaction)
        ], 201);
    }

    /**
     * Process a loan disbursement transaction
     */
    public function loanDisbursement(LoanDisbursementRequest $request): JsonResponse
    {
        // Get loan details for transaction amount
        $loan = \App\Models\Loan::findOrFail($request->loan_id);
        
        $transactionData = new TransactionDTO(
            memberId: $loan->member_id,
            type: 'loan_disbursement',
            amount: $loan->principal_amount,
            description: "Loan disbursement - " . ($request->notes ?? ''),
            relatedLoanId: $loan->id,
            processedBy: Auth::id(),
            metadata: [
                'disbursement_method' => $request->disbursement_method,
                'notes' => $request->notes
            ]
        );

        $transaction = $this->transactionService->processTransaction($transactionData);

        return response()->json([
            'success' => true,
            'message' => 'Loan disbursement processed successfully',
            'data' => new TransactionResource($transaction)
        ], 201);
    }

    /**
     * Process a loan repayment transaction
     */
    public function loanRepayment(LoanRepaymentRequest $request): JsonResponse
    {
        $transactionData = TransactionDTO::fromRequest($request);
        $transactionData->processedBy = Auth::id();

        $transaction = $this->transactionService->processTransaction($transactionData);

        return response()->json([
            'success' => true,
            'message' => 'Loan repayment processed successfully',
            'data' => new TransactionResource($transaction)
        ], 201);
    }

    /**
     * Get transaction history for a member
     */
    public function history(Request $request): JsonResponse
    {
        $request->validate([
            'member_id' => ['required', 'integer', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'type' => ['nullable', 'string', 'in:deposit,withdrawal,share_purchase,loan_disbursement,loan_repayment'],
        ]);

        $query = \App\Models\Transaction::where('member_id', $request->member_id);

        if ($request->start_date) {
            $query->where('transaction_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->where('transaction_date', '<=', $request->end_date);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => TransactionResource::collection($transactions),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
            ]
        ]);
    }

    /**
     * Get transaction summary for a member
     */
    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'member_id' => ['required', 'integer', 'exists:users,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $dateRange = null;
        if ($request->start_date && $request->end_date) {
            $dateRange = [$request->start_date, $request->end_date];
        }

        $summary = $this->transactionService->getMemberTransactionSummary(
            $request->member_id,
            $dateRange
        );

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Reverse a transaction
     */
    public function reverse(Request $request): JsonResponse
    {
        $request->validate([
            'transaction_id' => ['required', 'integer', 'exists:transactions,id'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $reversedTransaction = $this->transactionService->reverseTransaction(
            $request->transaction_id,
            $request->reason,
            Auth::id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Transaction reversed successfully',
            'data' => new TransactionResource($reversedTransaction)
        ]);
    }

    /**
     * Get all transactions (admin)
     */
    public function index(Request $request): JsonResponse
    {
        $query = \App\Models\Transaction::with(['member', 'account', 'loan']);

        // Apply filters
        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->start_date) {
            $query->where('transaction_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->where('transaction_date', '<=', $request->end_date);
        }

        if ($request->member_id) {
            $query->where('member_id', $request->member_id);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(25);

        return response()->json([
            'success' => true,
            'data' => TransactionResource::collection($transactions),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
                'last_page' => $transactions->lastPage(),
            ]
        ]);
    }

    /**
     * Get single transaction details
     */
    public function show($id): JsonResponse
    {
        $transaction = \App\Models\Transaction::with(['member', 'account', 'loan', 'processedBy', 'reversedBy'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new TransactionResource($transaction)
        ]);
    }

    /**
     * Get member transactions
     */
    public function memberTransactions($memberId, Request $request): JsonResponse
    {
        $query = \App\Models\Transaction::where('member_id', $memberId);

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->start_date) {
            $query->where('transaction_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->where('transaction_date', '<=', $request->end_date);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => TransactionResource::collection($transactions),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
                'last_page' => $transactions->lastPage(),
            ]
        ]);
    }

    /**
     * Get member transaction summary
     */
    public function memberSummary($memberId, Request $request): JsonResponse
    {
        $dateRange = null;
        if ($request->start_date && $request->end_date) {
            $dateRange = [$request->start_date, $request->end_date];
        }

        $summary = $this->transactionService->getMemberTransactionSummary($memberId, $dateRange);

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Get pending transactions (admin)
     */
    public function getPending(): JsonResponse
    {
        $transactions = \App\Models\Transaction::with(['member', 'account'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => TransactionResource::collection($transactions)
        ]);
    }

    /**
     * Approve transaction (admin)
     */
    public function approve($id, Request $request): JsonResponse
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $transaction = \App\Models\Transaction::findOrFail($id);
        
        if ($transaction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Transaction is not pending approval'
            ], 400);
        }

        $transaction->update([
            'status' => 'completed',
            'processed_by' => Auth::id(),
            'metadata' => array_merge($transaction->metadata ?? [], [
                'approval_notes' => $request->notes,
                'approved_at' => now()->toISOString()
            ])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction approved successfully',
            'data' => new TransactionResource($transaction)
        ]);
    }

    /**
     * Reject transaction (admin)
     */
    public function reject($id, Request $request): JsonResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $transaction = \App\Models\Transaction::findOrFail($id);
        
        if ($transaction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Transaction is not pending approval'
            ], 400);
        }

        $transaction->update([
            'status' => 'failed',
            'processed_by' => Auth::id(),
            'metadata' => array_merge($transaction->metadata ?? [], [
                'rejection_reason' => $request->rejection_reason,
                'rejected_at' => now()->toISOString()
            ])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction rejected successfully',
            'data' => new TransactionResource($transaction)
        ]);
    }

    /**
     * Get general ledger entries
     */
    public function getGeneralLedger(Request $request): JsonResponse
    {
        $query = \App\Models\GeneralLedger::query();

        // Apply filters
        if ($request->start_date) {
            $query->where('transaction_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->where('transaction_date', '<=', $request->end_date);
        }

        if ($request->account_code) {
            $query->where('account_code', $request->account_code);
        }

        $entries = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Calculate totals
        $totalDebits = $entries->sum('debit_amount');
        $totalCredits = $entries->sum('credit_amount');

        return response()->json([
            'success' => true,
            'data' => $entries->items(),
            'meta' => [
                'current_page' => $entries->currentPage(),
                'total' => $entries->total(),
                'per_page' => $entries->perPage(),
                'last_page' => $entries->lastPage(),
                'total_debits' => $totalDebits,
                'total_credits' => $totalCredits,
                'balance' => $totalDebits - $totalCredits
            ]
        ]);
    }

    /**
     * Get trial balance
     */
    public function getTrialBalance(Request $request): JsonResponse
    {
        $query = \App\Models\GeneralLedger::selectRaw('
            account_code,
            account_name,
            SUM(debit_amount) as total_debits,
            SUM(credit_amount) as total_credits,
            (SUM(debit_amount) - SUM(credit_amount)) as balance
        ');

        if ($request->start_date) {
            $query->where('transaction_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->where('transaction_date', '<=', $request->end_date);
        }

        $trialBalance = $query->groupBy('account_code', 'account_name')
            ->orderBy('account_code')
            ->get();

        $totalDebits = $trialBalance->sum('total_debits');
        $totalCredits = $trialBalance->sum('total_credits');

        return response()->json([
            'success' => true,
            'data' => $trialBalance,
            'meta' => [
                'total_debits' => $totalDebits,
                'total_credits' => $totalCredits,
                'balance' => $totalDebits - $totalCredits,
                'is_balanced' => abs($totalDebits - $totalCredits) < 0.01
            ]
        ]);
    }
}
