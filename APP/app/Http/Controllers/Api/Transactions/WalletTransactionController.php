<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Models\Account;
use App\Models\Transaction;
use App\DTOs\TransactionDTO;
use Illuminate\Http\Request;
use App\Models\SavingsAccount;
use App\Services\BalanceService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Gate;

class WalletTransactionController extends Controller
{
    protected TransactionService $transactionService;
    protected BalanceService $balanceService;

    public function __construct(TransactionService $transactionService, BalanceService $balanceService)
    {
        $this->transactionService = $transactionService;
        $this->balanceService = $balanceService;
    }

    /**
     * Top-up wallet with cash
     */
    public function topup(Request $request): JsonResponse
    {
        $request->validate([
            'member_id' => 'required|exists:users,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:500',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $transactionDTO = new TransactionDTO(
                memberId: $request->member_id,
                type: 'wallet_topup',
                amount: $request->amount,
                accountId: $request->account_id,
                description: $request->description ?? 'Wallet top-up',
                processedBy: auth()->id()
            );

            $transaction = $this->transactionService->processTransaction($transactionDTO);

            return response()->json([
                'success' => true,
                'message' => 'Wallet topped up successfully',
                'data' => [
                    'transaction' => $transaction,
                    'new_balance' => $transaction->balance_after,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet top-up failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Withdraw cash from wallet
     */
    public function withdrawal(Request $request): JsonResponse
    {
        $request->validate([
            'member_id' => 'required|exists:users,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:500',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $transactionDTO = new TransactionDTO(
                memberId: $request->member_id,
                type: 'wallet_withdrawal',
                amount: $request->amount,
                accountId: $request->account_id,
                description: $request->description ?? 'Wallet withdrawal',
                processedBy: auth()->id()
            );

            $transaction = $this->transactionService->processTransaction($transactionDTO);

            return response()->json([
                'success' => true,
                'message' => 'Wallet withdrawal processed successfully',
                'data' => [
                    'transaction' => $transaction,
                    'new_balance' => $transaction->balance_after,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet withdrawal failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Transfer from wallet to savings account
     */
    public function transferToSavings(Request $request): JsonResponse
    {
        $request->validate([
            'member_id' => 'required|exists:users,id',
            'wallet_account_id' => 'required|exists:accounts,id',
            'savings_account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:500',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            // Process wallet withdrawal (from wallet)
            $walletTransactionDTO = new TransactionDTO(
                memberId: $request->member_id,
                type: 'wallet_to_savings',
                amount: $request->amount,
                accountId: $request->wallet_account_id,
                description: $request->description ?? 'Transfer from wallet to savings',
                processedBy: auth()->id(),
                metadata: ['target_account_id' => $request->savings_account_id]
            );

            $walletTransaction = $this->transactionService->processTransaction($walletTransactionDTO);

            // Process savings deposit (to savings account)
            $savingsTransactionDTO = new TransactionDTO(
                memberId: $request->member_id,
                type: 'deposit',
                amount: $request->amount,
                accountId: $request->savings_account_id,
                description: $request->description ?? 'Deposit from wallet',
                processedBy: auth()->id(),
                metadata: ['source_account_id' => $request->wallet_account_id]
            );

            $savingsTransaction = $this->transactionService->processTransaction($savingsTransactionDTO);

            return response()->json([
                'success' => true,
                'message' => 'Transfer to savings completed successfully',
                'data' => [
                    'wallet_transaction' => $walletTransaction,
                    'savings_transaction' => $savingsTransaction,
                    'wallet_balance' => $walletTransaction->balance_after,
                    'savings_balance' => $savingsTransaction->balance_after,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transfer to savings failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Use wallet balance to repay loan
     */
    public function repayLoan(Request $request): JsonResponse
    {
        $request->validate([
            'member_id' => 'required|exists:users,id',
            'account_id' => 'required|exists:accounts,id',
            'loan_id' => 'required|exists:loans,id',
            'amount' => 'required|numeric|min:500',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $transactionDTO = new TransactionDTO(
                memberId: $request->member_id,
                type: 'wallet_to_loan',
                amount: $request->amount,
                accountId: $request->account_id,
                relatedLoanId: $request->loan_id,
                description: $request->description ?? 'Loan repayment from wallet',
                processedBy: auth()->id()
            );

            $transaction = $this->transactionService->processTransaction($transactionDTO);

            return response()->json([
                'success' => true,
                'message' => 'Loan repayment from wallet processed successfully',
                'data' => [
                    'transaction' => $transaction,
                    'wallet_balance' => $transaction->balance_after,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Loan repayment failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get wallet balance
     */
    public function balance(Request $request, int $accountId): JsonResponse
    {
        try {
            $account = Account::with([
                'accountable' => function ($morphTo) {
                    $morphTo->morphWith([
                        SavingsAccount::class => ['savingsProduct'],
                    ]);
                }
            ])->findOrFail($accountId);

            // Verify it's a wallet account
            if (!$account->isWalletAccount()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This is not a wallet account',
                ], 422);
            }

            // Check permission
            if (!Gate::allows('viewAccountBalance', $account)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this wallet balance',
                ], 403);
            }

            $availableBalance = $this->balanceService->getAvailableBalance($account);

            return response()->json([
                'success' => true,
                'data' => [
                    'account_id' => $account->id,
                    'account_number' => $account->account_number,
                    'balance' => $account->accountable->balance ?? 0,
                    'available_balance' => $availableBalance,
                    'last_transaction_date' => $account->last_transaction_date,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving wallet balance',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get wallet transaction history
     */
    public function history(Request $request, int $accountId): JsonResponse
    {
        try {
            $account = Account::with([
                'accountable' => function ($morphTo) {
                    $morphTo->morphWith([
                        SavingsAccount::class => ['savingsProduct'],
                    ]);
                }
            ])->findOrFail($accountId);

            // Verify it's a wallet account
            if (!$account->isWalletAccount()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This is not a wallet account',
                ], 422);
            }

            if (!Gate::allows('viewTransactionHistory', $account)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view wallet transaction history',
                ], 403);
            }

            $query = Transaction::where('account_id', $accountId)
                ->whereIn('type', ['wallet_topup', 'wallet_withdrawal', 'wallet_to_savings', 'wallet_to_loan'])
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
                'data' => $transactions->items(),
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
                'message' => 'Error retrieving wallet transaction history',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
