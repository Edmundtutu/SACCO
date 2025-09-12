<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\LoanResource;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\User;
use Illuminate\Http\Request;

class LoansController extends Controller
{
    /*
     * Function to fetch loans of a specific member
     *
     * */
    public function index(Request $request){
        // Get the authenticated user
        $user = auth()->user();

        // Get the user's loans
        $loans = Loan::where('member_id', $user->id)
            ->with(['loanProduct', 'guarantors', 'repayments'])
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
            'product_id' => 'required|exists:loan_products,id',
            'amount' => 'required|numeric|min:0',
            'term_months' => 'required|integer|min:1',
            'purpose' => 'required|string',
            'guarantor_ids' => 'sometimes|array',
            'guarantor_ids.*' => 'exists:users,id'
        ]);

        // Get the authenticated user
        $user = auth()->user();

        // Get the loan product
        $loanProduct = LoanProduct::findOrFail($request->product_id);

        // Calculate loan details
        $principalAmount = $request->amount;
        $interestRate = $loanProduct->interest_rate;
        $processingFee = $principalAmount * ($loanProduct->processing_fee_rate / 100);
        $insuranceFee = $principalAmount * ($loanProduct->insurance_rate / 100);
        $totalAmount = $principalAmount + ($principalAmount * $interestRate / 100 * $request->term_months / 12);

        // Calculate monthly payment (simple interest)
        $monthlyPayment = $totalAmount / $request->term_months;

        // Create the loan
        $loan = Loan::create([
            'member_id' => $user->id,
            'loan_product_id' => $loanProduct->id,
            'principal_amount' => $principalAmount,
            'interest_rate' => $interestRate,
            'processing_fee' => $processingFee,
            'insurance_fee' => $insuranceFee,
            'total_amount' => $totalAmount,
            'repayment_period_months' => $request->term_months,
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

        // Get the loan
        $loan = Loan::where('id', $loanId)
            ->where('member_id', $user->id)
            ->with(['loanProduct', 'guarantors', 'repayments'])
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
     * */
    public function repay(Request $request, $loanId){
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

        // Apply the payment
        $allocation = $loan->applyPayment($request->amount);

        // Create a repayment record
        $repayment = $loan->repayments()->create([
            'amount' => $request->amount,
            'principal_amount' => $allocation['principal'],
            'interest_amount' => $allocation['interest'],
            'penalty_amount' => $allocation['penalty'],
            'payment_date' => now(),
            'payment_method' => $request->payment_method,
            'reference' => $request->reference ?? null
        ]);

        // Return the repayment data
        return response()->json([
            'success' => true,
            'message' => 'Payment applied successfully',
            'data' => [
                'repayment' => $repayment,
                'loan_id' => $loan->id,
                'outstanding_balance' => $loan->outstanding_balance,
                'next_payment_date' => $loan->first_payment_date ? $loan->first_payment_date->addMonths($loan->repayments()->count())->format('Y-m-d') : null
            ]
        ]);
    }

    /*
     * Function to get loan products
     *
     * */
    public function getLoanProducts(Request $request){
        // Get all active loan products
        $products = LoanProduct::where('is_active', true)->get();

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
