<?php

namespace App\Http\Controllers\Api\Transactions;


use App\Http\Controllers\Controller;
use App\Http\Requests\SharePurchaseRequest;
use App\Http\Resources\TransactionResource;
use App\Services\TransactionService;
use App\DTOs\TransactionDTO;
use App\Models\Share;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShareTransactionController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Purchase shares with cash
     */
    public function purchase(SharePurchaseRequest $request): JsonResponse
    {
        try {
            $transactionDTO = new TransactionDTO(
                memberId: $request->member_id,
                type: 'share_purchase',
                amount: $request->amount,
                description: $request->description ?? 'Share purchase',
                processedBy: auth()->id()
            );

            $transaction = $this->transactionService->processTransaction($transactionDTO);

            return response()->json([
                'success' => true,
                'message' => 'Share purchase processed successfully',
                'data' => new TransactionResource($transaction),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Share purchase failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get member's share portfolio
     */
    public function portfolio(Request $request, int $memberId): JsonResponse
    {
        try {
            if (!auth()->user()->can('view-member-shares', $memberId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view share portfolio',
                ], 403);
            }

            $shares = Share::where('member_id', $memberId)
                ->where('status', 'active')
                ->orderBy('purchase_date', 'desc')
                ->get();

            $totalShares = $shares->sum('shares_count');
            $totalValue = $shares->sum(function ($share) {
                return $share->shares_count * $share->share_value;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'member_id' => $memberId,
                    'total_shares' => $totalShares,
                    'total_value' => $totalValue,
                    'share_certificates' => $shares->map(function ($share) {
                        return [
                            'certificate_number' => $share->certificate_number,
                            'shares_count' => $share->shares_count,
                            'share_value' => $share->share_value,
                            'total_value' => $share->shares_count * $share->share_value,
                            'purchase_date' => $share->purchase_date->format('Y-m-d'),
                            'status' => $share->status,
                        ];
                    }),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving share portfolio',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get share transaction history
     */
    public function history(Request $request, int $memberId): JsonResponse
    {
        try {
            if (!auth()->user()->can('view-member-transactions', $memberId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view transaction history',
                ], 403);
            }

            $query = Transaction::where('member_id', $memberId)
                ->where('type', 'share_purchase')
                ->where('status', 'completed')
                ->orderBy('transaction_date', 'desc');

            if ($request->has('start_date')) {
                $query->whereDate('transaction_date', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->whereDate('transaction_date', '<=', $request->end_date);
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
                'message' => 'Error retrieving share transaction history',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
