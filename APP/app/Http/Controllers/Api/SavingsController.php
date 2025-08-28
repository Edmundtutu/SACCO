<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\SavingsProduct;
use App\Models\GeneralLedger;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SavingsController extends Controller
{
    /**
     * Get member accounts
     */
    public function getAccounts(): JsonResponse
    {
        try {
            $user = auth()->user();
            $accounts = $user->accounts()->with('savingsProduct')->get();

            return response()->json([
                'success' => true,
                'data' => $accounts
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not retrieve accounts'
            ], 500);
        }
    }

    /**
     * Make a deposit
     */
    public function deposit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money,check',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $account = Account::with('savingsProduct')->findOrFail($request->account_id);

            // Check if user owns the account or is staff
            if ($account->member_id !== $user->id && !$user->isStaff()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $balanceBefore = $account->balance;

            // Create transaction
            $transaction = Transaction::create([
                'member_id' => $account->member_id,
                'account_id' => $account->id,
                'type' => 'deposit',
                'category' => 'savings',
                'amount' => $request->amount,
                'fee_amount' => 0,
                'net_amount' => $request->amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore + $request->amount,
                'description' => $request->description ?? 'Savings deposit',
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'transaction_date' => now(),
                'processed_by' => auth()->id(),
            ]);

            // Update account balance
            $account->updateBalance($request->amount, 'credit');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Deposit successful',
                'data' => [
                    'transaction' => $transaction,
                    'new_balance' => $account->fresh()->balance,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Deposit failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Make a withdrawal
     */
    public function withdraw(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $account = Account::with('savingsProduct')->findOrFail($request->account_id);

            // Check if user owns the account or is staff
            if ($account->member_id !== $user->id && !$user->isStaff()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Check if withdrawal is allowed
            if (!$account->canWithdraw($request->amount)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Withdrawal not allowed. Check account status and minimum balance.'
                ], 400);
            }

            $balanceBefore = $account->balance;
            $withdrawalFee = $account->savingsProduct->withdrawal_fee;
            $totalDeduction = $request->amount + $withdrawalFee;

            // Create transaction
            $transaction = Transaction::create([
                'member_id' => $account->member_id,
                'account_id' => $account->id,
                'type' => 'withdrawal',
                'category' => 'savings',
                'amount' => $request->amount,
                'fee_amount' => $withdrawalFee,
                'net_amount' => $request->amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore - $totalDeduction,
                'description' => $request->description ?? 'Savings withdrawal',
                'payment_method' => 'cash',
                'status' => 'completed',
                'transaction_date' => now(),
                'processed_by' => auth()->id(),
            ]);

            // Update account balance
            $account->updateBalance($totalDeduction, 'debit');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal successful',
                'data' => [
                    'transaction' => $transaction,
                    'withdrawal_fee' => $withdrawalFee,
                    'new_balance' => $account->fresh()->balance,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Withdrawal failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get savings products
     */
    public function getSavingsProducts(): JsonResponse
    {
        try {
            $products = SavingsProduct::active()->get();

            return response()->json([
                'success' => true,
                'data' => $products
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not retrieve savings products'
            ], 500);
        }
    }
}