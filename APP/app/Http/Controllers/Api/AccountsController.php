<?php

namespace App\Http\Controllers\Api;

use App\Models\Account;
use App\Models\LoanAccount;
use App\Models\ShareAccount;
use Illuminate\Http\Request;
use App\Models\SavingsAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

/**
 * Handles polymorphic Account operations
 * Returns Account wrappers with nested accountable relationships
 */
class AccountsController extends Controller
{
    /**
     * Get accounts for authenticated member
     * Can filter by type: savings, loan, share
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $type = $request->query('type'); // savings, loan, share
            $status = $request->query('status'); // active, dormant, closed, suspended

            $query = Account::where('member_id', $user->id)->with([
                'accountable',
                'accountable.savingsProduct' // Eager load savings product for wallet detection
            ]);

            // Filter by accountable type
            if ($type) {
                $accountableType = match($type) {
                    'savings' => SavingsAccount::class,
                    'loan' => LoanAccount::class,
                    'share' => ShareAccount::class,
                    default => null
                };

                if ($accountableType) {
                    $query->where('accountable_type', $accountableType);
                }
            }

            // Filter by status
            if ($status) {
                $query->where('status', $status);
            }

            $accounts = $query->get();

            return response()->json([
                'success' => true,
                'data' => $accounts,
                'message' => 'Accounts retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching accounts: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Could not retrieve accounts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific account by ID
     */
    public function show(int $accountId): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $account = Account::with([
                'accountable',
                'accountable.savingsProduct' // Eager load savings product for wallet detection
            ])
                ->where('member_id', $user->id)
                ->findOrFail($accountId);

            return response()->json([
                'success' => true,
                'data' => $account,
                'message' => 'Account retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found'
            ], 404);
        }
    }

    /**
     * Get account summary for authenticated member
     */
    public function summary(): JsonResponse
    {
        try {
            $user = auth()->user();
            
            // Get all accounts with their accountable relationships
            $savingsAccounts = Account::with([
                'accountable',
                'accountable.savingsProduct' // Eager load savings product for wallet detection
            ])
                ->where('member_id', $user->id)
                ->where('accountable_type', SavingsAccount::class)
                ->get();

            $loanAccount = Account::with('accountable')
                ->where('member_id', $user->id)
                ->where('accountable_type', LoanAccount::class)
                ->first();

            $shareAccount = Account::with('accountable')
                ->where('member_id', $user->id)
                ->where('accountable_type', ShareAccount::class)
                ->first();

            // Calculate totals
            $totalSavings = $savingsAccounts->sum(function ($account) {
                return $account->accountable->balance ?? 0;
            });

            $totalLoans = $loanAccount?->accountable?->current_outstanding ?? 0;
            $totalShares = $shareAccount?->accountable?->total_share_value ?? 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_savings' => $totalSavings,
                    'total_loans' => $totalLoans,
                    'total_shares' => $totalShares,
                    'accounts_count' => $savingsAccounts->count() + ($loanAccount ? 1 : 0) + ($shareAccount ? 1 : 0),
                ],
                'message' => 'Account summary retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching account summary: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Could not retrieve account summary'
            ], 500);
        }
    }

    /**
     * Get transactions for a specific account
     */
    public function transactions(int $accountId): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $account = Account::where('member_id', $user->id)
                ->findOrFail($accountId);

            // Get transactions related to this account
            $transactions = \App\Models\Transaction::where('account_id', $accountId)
                ->orderBy('transaction_date', 'desc')
                ->paginate(50);

            return response()->json([
                'success' => true,
                'data' => $transactions,
                'message' => 'Transactions retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not retrieve transactions'
            ], 404);
        }
    }
}
