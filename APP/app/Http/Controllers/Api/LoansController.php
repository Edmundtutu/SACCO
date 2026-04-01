<?php

namespace App\Http\Controllers\Api;

use App\DTOs\TransactionDTO;
use App\Exceptions\TransactionProcessingException;
use App\Models\Loan;
use App\Models\LoanAccount;
use App\Models\LoanRepayment;
use App\Models\Account;
use App\Models\LoanProduct;
use App\Services\LoanCalculationService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\LoanResource;

/**
 * This controller handles all endpoint requests related to loans.
 * It does not handle the creation of transactions, which is managed
 * by a dedicated controller in the /Transactions folder.
 */
class LoansController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService,
        protected LoanCalculationService $loanCalculationService,
    ) {}

    /*
     * Function to fetch loans of a specific member
     *
     * */
    public function index(Request $request){
        // Get the authenticated user
        $user = auth()->user();

        // Get the user's loans with loanAccount for consistent current_outstanding
        $loans = Loan::where('member_id', $user->id)
            ->with(['loanProduct', 'guarantors', 'repayments', 'loanAccount'])
            ->get();

        // Return the loans data
        return response()->json([
            'success' => true,
            'message' => 'Loans retrieved successfully',
            'data' => LoanResource::collection($loans)
        ]);
    }

    /*
     * Function to apply for a loan
     *
     * */
    public function apply(Request $request){
        // Validate the request
        $request->validate([
            'loan_product_id' => 'required|exists:loan_products,id',
            'principal_amount' => 'required|numeric|min:0',
            'repayment_period_months' => 'required|integer|min:1',
            'purpose' => 'required|string',
            'guarantor_ids' => 'sometimes|array',
            'guarantor_ids.*' => 'exists:users,id'
        ]);

        // Get the authenticated user
        $user = auth()->user();

        // ✅ VERIFY: User must have a LoanAccount
        $loanAccountRecord = Account::where('member_id', $user->id)
            ->where('accountable_type', LoanAccount::class)
            ->first();

        if (!$loanAccountRecord) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have a loan account. Please contact administrator to complete your account setup.',
                'error_code' => 'LOAN_ACCOUNT_NOT_FOUND'
            ], 404);
        }

        $loanAccount = $loanAccountRecord->accountable;

        // Get the loan product
        $loanProduct = LoanProduct::findOrFail($request->loan_product_id);

        // Calculate loan details
        $principalAmount = $request->principal_amount;
        $interestRate = $loanProduct->interest_rate;
        $processingFee = $principalAmount * ($loanProduct->processing_fee_rate / 100);
        $insuranceFee = $principalAmount * ($loanProduct->insurance_rate / 100);
        $totalAmount = $principalAmount + ($principalAmount * $interestRate / 100 * $request->repayment_period_months / 12);

        // Calculate monthly payment (simple interest)
        $monthlyPayment = $totalAmount / $request->repayment_period_months;

        // ✅ CHECK: Validate against loan account limits
        if (!$loanAccount->canAccommodateNewLoan($principalAmount)) {
            return response()->json([
                'success' => false,
                'message' => "Loan amount exceeds your limits. Min: {$loanAccount->min_loan_limit}, Max: {$loanAccount->max_loan_limit}",
                'error_code' => 'AMOUNT_EXCEEDS_LIMITS'
            ], 422);
        }

        // Create the loan linked to LoanAccount
        $loan = Loan::create([
            'member_id' => $user->id,
            'loan_account_id' => $loanAccount->id,  // ✅ LINKED!
            'loan_product_id' => $loanProduct->id,
            'principal_amount' => $principalAmount,
            'interest_rate' => $interestRate,
            'processing_fee' => $processingFee,
            'insurance_fee' => $insuranceFee,
            'total_amount' => $totalAmount,
            'repayment_period_months' => $request->repayment_period_months,
            'monthly_payment' => $monthlyPayment,
            'application_date' => now(),
            'status' => 'pending',
            'outstanding_balance' => $totalAmount,
            'principal_balance' => $principalAmount,
            'interest_balance' => $totalAmount - $principalAmount,
            'penalty_balance' => 0,
            'total_paid' => 0,
            'purpose' => $request->purpose
        ]);

        // Add guarantors if provided
        if ($request->has('guarantor_ids')) {
            foreach ($request->guarantor_ids as $guarantorId) {
                $loan->guarantors()->create([
                    'guarantor_id' => $guarantorId,
                    'amount_guaranteed' => $principalAmount / count($request->guarantor_ids),
                    'status' => 'pending'
                ]);
            }
        }

        // Return the loan data
        return response()->json([
            'success' => true,
            'message' => 'Loan application submitted successfully',
            'data' => new LoanResource($loan)
        ]);
    }

    /*
     * Function to get a specific loan
     *
     * */
    public function show(Request $request, $loanId){
        // Get the authenticated user
        $user = auth()->user();

        // Get the loan with loanAccount for current_outstanding
        $loan = Loan::where('id', $loanId)
            ->where('member_id', $user->id)
            ->with(['loanProduct', 'guarantors', 'repayments', 'loanAccount'])
            ->firstOrFail();

        // Return the loan data
        return response()->json([
            'success' => true,
            'message' => 'Loan retrieved successfully',
            'data' => new LoanResource($loan)
        ]);
    }

    /*
     * Function to repay a loan
     *
     * Request:  amount (required), payment_method (required), reference (optional)
     * Response: { success, message, data: { repayment, loan_id,
     *             outstanding_balance, next_payment_date } }
     */
    public function repay(Request $request, $loanId): JsonResponse
    {
        // Validate the request
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'reference' => 'sometimes|string'
        ]);

        // Get the authenticated user
        $user = auth()->user();

        // Get the loan
        $loan = Loan::where('id', $loanId)
            ->where('member_id', $user->id)
            ->firstOrFail();

        // Check if loan is active
        if (!in_array($loan->status, ['disbursed', 'active'])) {
            return response()->json([
                'success' => false,
                'message' => 'Loan is not active',
                'data' => null
            ], 400);
        }

        try {
            $paymentAllocation = $this->loanCalculationService->calculatePaymentAllocation(
                $loan,
                $request->amount
            );

            // Resolve account ID – null-safe for loans without a linked account.
            $accountId = $loan->loanAccount?->account?->id;

            $transactionDTO = new TransactionDTO(
                memberId: $loan->member_id,
                type: 'loan_repayment',
                amount: $request->amount,
                accountId: $accountId,
                relatedLoanId: $loan->id,
                description: "Loan repayment - loan #{$loan->id}",
                processedBy: auth()->id(),
                metadata: [
                    'payment_method'    => $request->payment_method,
                    'payment_reference' => $request->reference ?? null,
                    'principal_amount'  => $paymentAllocation['principal'],
                    'interest_amount'   => $paymentAllocation['interest'],
                    'penalty_amount'    => $paymentAllocation['penalty'] ?? 0,
                ]
            );

            $transaction = $this->transactionService->processTransaction($transactionDTO);

            $loan->refresh();
            $metadata  = json_decode($transaction->metadata, true);
            $repayment = isset($metadata['repayment_id'])
                ? LoanRepayment::find($metadata['repayment_id'])
                : null;

            return response()->json([
                'success' => true,
                'message' => 'Payment applied successfully',
                'data' => [
                    'repayment'           => $repayment,
                    'loan_id'             => $loan->id,
                    'outstanding_balance' => $loan->outstanding_balance,
                    'next_payment_date'   => $loan->first_payment_date
                        ? $loan->first_payment_date->addMonths($loan->repayments()->count())->format('Y-m-d')
                        : null,
                ]
            ]);

        } catch (TransactionProcessingException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
            ], 422);
        }
    }

    /*
     * Function to get loan products
     *
     * */
    public function getLoanProducts(): JsonResponse
    {
        // Get all active loan products
        $products = LoanProduct::where('is_active', true)->get();
        
        if($products->isEmpty())
        {
            return response()->json([
                'success' => false,
                'message' => 'No loan products found',
                'data' => null
            ], 404);
        }

        // Return the products data
        return response()->json([
            'success' => true,
            'message' => 'Loan products retrieved successfully',
            'data' => $products
        ]);
    }

    /*
     * Function to get repayment schedule
     *
     * */
    public function getRepaymentSchedule(Request $request, $loanId){
        // Get the authenticated user
        $user = auth()->user();

        // Get the loan
        $loan = Loan::where('id', $loanId)
            ->where('member_id', $user->id)
            ->firstOrFail();

        // Generate the repayment schedule
        $schedule = $loan->generateRepaymentSchedule();

        // Return the schedule data
        return response()->json([
            'success' => true,
            'message' => 'Repayment schedule retrieved successfully',
            'data' => $schedule
        ]);
    }
}
