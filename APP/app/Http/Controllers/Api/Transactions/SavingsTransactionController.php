<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\WithdrawalRequest;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\BalanceResource;
use App\Services\TransactionService;
use App\Services\BalanceService;
use App\DTOs\TransactionDTO;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SavingsTransactionController extends Controller
{
    protected TransactionService $transactionService;
    protected BalanceService $balanceService;

    public function __construct(TransactionService $transactionService, BalanceService $balanceService)
    {
        $this->transactionService = $transactionService;
        $this->balanceService = $balanceService;
    }

    /**
     * Process a cash deposit
     */
    public function deposit(DepositRequest $request): JsonResponse
    {
        try {
            $transactionDTO = new TransactionDTO(
                memberId: $request->member_id,
                type: 'deposit',
                amount: $request->amount,
                accountId: $request->account_id,
                description: $request->description ?? 'Cash deposit',
                processedBy: auth()->id()
            );

            $transaction = $this->transactionService->processTransaction($transactionDTO);

            return response()->json([
                'success' => true,
                'message' => 'Deposit processed successfully',
                'data' => new TransactionResource($transaction),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Deposit processing failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Process a cash withdrawal
     */
    public function withdrawal(WithdrawalRequest $request): JsonResponse
    {
        try {
            $transactionDTO = new TransactionDTO(
                memberId: $request->member_id,
                type: 'withdrawal',
                amount: $request->amount,
                accountId: $request->account_id,
                description: $request->description ?? 'Cash withdrawal',
                processedBy: auth()->id()
            );

            $transaction = $this->transactionService->processTransaction($transactionDTO);

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal processed successfully',
                'data' => new TransactionResource($transaction),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Withdrawal processing failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get account balance
     */
    public function balance(Request $request, int $accountId): JsonResponse
    {
        try {
            $account = Account::findOrFail($accountId);

            // Check if user has permission to view this balance
            if (!auth()->user()->can('view-account-balance', $account)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this account balance',
                ], 403);
            }

            $availableBalance = $this->balanceService->getAvailableBalance($account);

            return response()->json([
                'success' => true,
                'data' => new BalanceResource($account, $availableBalance),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving balance',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get transaction history for an account
     */
    public function history(Request $request, int $accountId): JsonResponse
    {
        try {
            $account = Account::findOrFail($accountId);

            if (!auth()->user()->can('view-transaction-history', $account)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view transaction history',
                ], 403);
            }

            $query = Transaction::where('account_id', $accountId)
                ->where('status', 'completed')
                ->orderBy('transaction_date', 'desc');

            // Apply date filters if provided
            if ($request->has('start_date')) {
                $query->whereDate('transaction_date', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->whereDate('transaction_date', '<=', $request->end_date);
            }

            // Apply transaction type filter
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            $transactions = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => TransactionResource::collection($transactions),
                'meta' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving transaction history',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Reverse a transaction
     */
    public function reverse(Request $request, int $transactionId): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ]);

        try {
            if (!auth()->user()->can('reverse-transactions')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to reverse transactions',
                ], 403);
            }

            $reversalTransaction = $this->transactionService->reverseTransaction(
                $transactionId,
                $request->reason,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaction reversed successfully',
                'data' => new TransactionResource($reversalTransaction),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction reversal failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
