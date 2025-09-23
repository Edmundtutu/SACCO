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
     * Get all transactions (admin only)
     */
    public function index(Request $request): JsonResponse
    {
        $query = \App\Models\Transaction::with(['member', 'account', 'relatedLoan']);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
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
     * Get single transaction details
     */
    public function show(int $transactionId): JsonResponse
    {
        $transaction = \App\Models\Transaction::with([
            'member',
            'account.savingsProduct',
            'relatedLoan.loanProduct',
            'processedBy',
            'reversedBy',
            'generalLedgerEntries'
        ])->findOrFail($transactionId);

        return response()->json([
            'success' => true,
            'data' => new TransactionResource($transaction)
        ]);
    }

    /**
     * Get transaction history for a member
     */
    public function memberTransactions(Request $request, int $memberId): JsonResponse
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'type' => ['nullable', 'string', 'in:deposit,withdrawal,share_purchase,loan_disbursement,loan_repayment'],
        ]);

        $query = \App\Models\Transaction::where('member_id', $memberId);

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
    public function memberSummary(Request $request, int $memberId): JsonResponse
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $dateRange = null;
        if ($request->start_date && $request->end_date) {
            $dateRange = [$request->start_date, $request->end_date];
        }

        $summary = $this->transactionService->getMemberTransactionSummary(
            $memberId,
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
}
