<?php

namespace App\Http\Controllers\Api\Transactions;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoanDisbursementRequest;
use App\Http\Requests\LoanRepaymentRequest;
use App\Http\Resources\TransactionResource;
use App\Services\TransactionService;
use App\Services\LoanCalculationService;
use App\DTOs\TransactionDTO;
use App\Models\Loan;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanTransactionController extends Controller
{
    protected TransactionService $transactionService;
    protected LoanCalculationService $loanCalculationService;

    public function __construct(
        TransactionService     $transactionService,
        LoanCalculationService $loanCalculationService
    )
    {
        $this->transactionService = $transactionService;
        $this->loanCalculationService = $loanCalculationService;
    }

    /**
     * Disburse a loan
     */
    public function disburse(LoanDisbursementRequest $request): JsonResponse
    {
        try {
            $loan = Loan::findOrFail($request->loan_id);

            $transactionDTO = new TransactionDTO(
                memberId: $loan->member_id,
                type: 'loan_disbursement',
                amount: $loan->principal_amount,
                relatedLoanId: $loan->id,
                description: "Loan disbursement - {$loan->loan_number}",
                processedBy: auth()->id(),
                metadata: [
                    'disbursement_method' => $request->disbursement_method,
                    'notes' => $request->notes,
                ]
            );

            $transaction = $this->transactionService->processTransaction($transactionDTO);

            return response()->json([
                'success' => true,
                'message' => 'Loan disbursed successfully',
                'data' => new TransactionResource($transaction),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Loan disbursement failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Process loan repayment
     */
    public function repayment(LoanRepaymentRequest $request): JsonResponse
    {
        try {
            $loan = Loan::findOrFail($request->loan_id);

            // Calculate payment allocation
            $paymentAllocation = $this->loanCalculationService->calculatePaymentAllocation(
                $loan,
                $request->amount
            );

            $transactionDTO = new TransactionDTO(
                memberId: $loan->member_id,
                type: 'loan_repayment',
                amount: $request->amount,
                relatedLoanId: $loan->id,
                description: "Loan repayment - {$loan->loan_number}",
                processedBy: auth()->id(),
                metadata: [
                    'payment_method' => $request->payment_method,
                    'payment_reference' => $request->payment_reference,
                    'notes' => $request->notes,
                    'principal_amount' => $paymentAllocation['principal'],
                    'interest_amount' => $paymentAllocation['interest'],
                    'penalty_amount' => $paymentAllocation['penalty'] ?? 0,
                ]
            );

            $transaction = $this->transactionService->processTransaction($transactionDTO);

            return response()->json([
                'success' => true,
                'message' => 'Loan repayment processed successfully',
                'data' => [
                    'transaction' => new TransactionResource($transaction),
                    'payment_allocation' => $paymentAllocation,
                    'remaining_balance' => $loan->fresh()->outstanding_balance,
                ]
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
     * Get loan repayment schedule
     */
    public function schedule(Request $request, int $loanId): JsonResponse
    {
        try {
            $loan = Loan::findOrFail($loanId);

            if (!auth()->user()->can('view-loan-details', $loan)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view loan details',
                ], 403);
            }

            $schedule = $this->loanCalculationService->generateRepaymentSchedule($loan);

            return response()->json([
                'success' => true,
                'data' => [
                    'loan_id' => $loan->id,
                    'loan_number' => $loan->loan_number,
                    'principal_amount' => $loan->principal_amount,
                    'interest_rate' => $loan->interest_rate,
                    'repayment_period_months' => $loan->repayment_period_months,
                    'outstanding_balance' => $loan->outstanding_balance,
                    'schedule' => $schedule,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating repayment schedule',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get loan transaction history
     */
    public function history(Request $request, int $loanId): JsonResponse
    {
        try {
            $loan = Loan::findOrFail($loanId);

            if (!auth()->user()->can('view-loan-details', $loan)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view loan history',
                ], 403);
            }

            $query = Transaction::where('related_loan_id', $loanId)
                ->whereIn('type', ['loan_disbursement', 'loan_repayment'])
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
                'message' => 'Error retrieving loan transaction history',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get loan summary
     */
    public function summary(Request $request, int $loanId): JsonResponse
    {
        try {
            $loan = Loan::findOrFail($loanId);

            if (!auth()->user()->can('view-loan-details', $loan)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view loan summary',
                ], 403);
            }

            $transactions = Transaction::where('related_loan_id', $loanId)
                ->where('status', 'completed')
                ->get();

            $disbursements = $transactions->where('type', 'loan_disbursement');
            $repayments = $transactions->where('type', 'loan_repayment');

            $totalDisbursed = $disbursements->sum('amount');
            $totalRepaid = $repayments->sum('amount');

            // Calculate interest and principal from repayments
            $totalPrincipalPaid = 0;
            $totalInterestPaid = 0;

            foreach ($repayments as $repayment) {
                $metadata = json_decode($repayment->metadata, true);
                $totalPrincipalPaid += $metadata['principal_amount'] ?? 0;
                $totalInterestPaid += $metadata['interest_amount'] ?? 0;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'loan_id' => $loan->id,
                    'loan_number' => $loan->loan_number,
                    'member_id' => $loan->member_id,
                    'principal_amount' => $loan->principal_amount,
                    'outstanding_balance' => $loan->outstanding_balance,
                    'status' => $loan->status,
                    'disbursement_date' => $loan->disbursement_date?->format('Y-m-d'),
                    'total_disbursed' => $totalDisbursed,
                    'total_repaid' => $totalRepaid,
                    'total_principal_paid' => $totalPrincipalPaid,
                    'total_interest_paid' => $totalInterestPaid,
                    'payment_count' => $repayments->count(),
                    'last_payment_date' => $repayments->max('transaction_date'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving loan summary',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
