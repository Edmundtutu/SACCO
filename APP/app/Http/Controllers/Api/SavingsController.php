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


/**
 * This controller handles all endpoint requests related to savings.
 * It does not handle the creation of transactions, which is managed
 * by a dedicated controller in the /Transactions folder.
 */
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
     * Get transactions that have been processed by the account
     * @param Account $account
     */
    public function getTransactions(Account $account): JsonResponse
    {
        // TODO: Return all transactions gone through this account

        return response()->json([
            'success' => true,
        ]);
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
