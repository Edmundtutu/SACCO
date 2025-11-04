<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanAccount;
use App\Models\Account;
use App\Models\LoanProduct;
use App\Models\LoanRepayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoansController extends Controller
{
    public function index(Request $request)
    {
        $query = Loan::with(['member', 'loanProduct']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('member', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('loan_number', 'like', "%{$search}%");
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $loans = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total_loans' => Loan::count(),
            'pending_loans' => Loan::where('status', 'pending')->count(),
            'active_loans' => Loan::where('status', 'active')->count(),
            'total_disbursed' => Loan::where('status', '!=', 'pending')->sum('principal_amount'),
        ];

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Loans', 'url' => route('admin.loans.index')]
        ];

        return view('admin.loans.index', compact('loans', 'stats', 'breadcrumbs'));
    }

    public function show($id)
    {
        $loan = Loan::with(['member', 'loanProduct', 'repayments', 'guarantors.guarantor'])
            ->findOrFail($id);

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Loans', 'url' => route('admin.loans.index')],
            ['text' => $loan->loan_number, 'url' => '']
        ];

        return view('admin.loans.show', compact('loan', 'breadcrumbs'));
    }

    public function approve(Request $request, $id)
    {
        $loan = Loan::where('status', 'pending')->findOrFail($id);

        DB::beginTransaction();
        try {
            $loan->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Loan approved successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to approve loan: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $loan = Loan::where('status', 'pending')->findOrFail($id);

        $loan->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
            'rejection_reason' => $request->input('reason', 'No reason provided'),
        ]);

        return redirect()->back()
            ->with('success', 'Loan rejected successfully.');
    }

    public function disburse(Request $request, $id)
    {
        $loan = Loan::where('status', 'approved')->findOrFail($id);

        $request->validate([
            'disbursement_date' => 'required|date',
            'disbursement_method' => 'required|in:cash,bank_transfer,mobile_money',
            'disbursement_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            // Call the API LoanTransactionController for disbursement
            $response = $this->callApiDisbursement($loan, $request);
            
            if ($response['success']) {
                return redirect()->back()
                    ->with('success', 'Loan disbursed successfully.');
            } else {
                return redirect()->back()
                    ->with('error', 'Failed to disburse loan: ' . $response['message']);
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to disburse loan: ' . $e->getMessage());
        }
    }

    /**
     * Call the API LoanTransactionController for disbursement
     */
    private function callApiDisbursement($loan, $request)
    {
        // Create a proper LoanDisbursementRequest instance
        $apiRequest = new \App\Http\Requests\LoanDisbursementRequest();
        
        // Set the request data
        $apiRequest->merge([
            'loan_id' => $loan->id,
            'disbursement_method' => $request->disbursement_method,
            'notes' => $request->notes,
        ]);

        // Set the authenticated user for the API call
        $apiRequest->setUserResolver(function () {
            return auth()->user();
        });

        // Set the route resolver to make the request work properly
        $apiRequest->setRouteResolver(function () {
            return app('router')->getRoutes()->match(
                app('request')->create('/api/loans/disburse', 'POST')
            );
        });

        // Call the API controller
        $apiController = new \App\Http\Controllers\Api\Transactions\LoanTransactionController(
            app(\App\Services\TransactionService::class),
            app(\App\Services\LoanCalculationService::class)
        );

        $response = $apiController->disburse($apiRequest);
        $responseData = $response->getData(true);

        return $responseData;
    }

    public function applications()
    {
        $applications = Loan::where('status', 'pending')
            ->with(['member', 'loanProduct'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Loans', 'url' => route('admin.loans.index')],
            ['text' => 'Applications', 'url' => '']
        ];

        return view('admin.loans.applications', compact('applications', 'breadcrumbs'));
    }

    public function products()
    {
        $products = LoanProduct::orderBy('name')->get();

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Loans', 'url' => route('admin.loans.index')],
            ['text' => 'Products', 'url' => '']
        ];

        return view('admin.loans.products', compact('products', 'breadcrumbs'));
    }

    public function create()
    {
        $members = \App\Models\User::where('role', 'member')->where('status', 'active')->get();
        $products = LoanProduct::where('is_active', true)->get();

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Loans', 'url' => route('admin.loans.index')],
            ['text' => 'Create Loan', 'url' => '']
        ];

        return view('admin.loans.create', compact('members', 'products', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:users,id',
            'loan_product_id' => 'required|exists:loan_products,id',
            'principal_amount' => 'required|numeric|min:1000',
            'purpose' => 'required|string|max:500',
            'repayment_period' => 'required|integer|min:1|max:60',
            'guarantors' => 'required|array|min:1',
            'guarantors.*' => 'exists:users,id'
        ]);

        // ✅ VERIFY: Member must have LoanAccount
        $loanAccountRecord = Account::where('member_id', $request->member_id)
            ->where('accountable_type', LoanAccount::class)
            ->first();

        if (!$loanAccountRecord) {
            return redirect()->back()
                ->with('error', 'Member does not have a loan account. Please ensure member is fully approved.')
                ->withInput();
        }

        $loanAccount = $loanAccountRecord->accountable;

        // ✅ CHECK: Validate against loan limits
        if (!$loanAccount->canAccommodateNewLoan($request->principal_amount)) {
            return redirect()->back()
                ->with('error', "Loan amount exceeds member's limits. Min: {$loanAccount->min_loan_limit}, Max: {$loanAccount->max_loan_limit}")
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $loan = Loan::create([
                'member_id' => $request->member_id,
                'loan_account_id' => $loanAccount->id,  // ✅ LINKED!
                'loan_product_id' => $request->loan_product_id,
                'principal_amount' => $request->principal_amount,
                'purpose' => $request->purpose,
                'repayment_period' => $request->repayment_period,
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            // Add guarantors
            foreach ($request->guarantors as $guarantorId) {
                $loan->guarantors()->create([
                    'guarantor_id' => $guarantorId,
                    'guarantee_amount' => $request->principal_amount / count($request->guarantors)
                ]);
            }

            DB::commit();

            return redirect()->route('admin.loans.show', $loan->id)
                ->with('success', 'Loan application created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to create loan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function repayments($id)
    {
        $loan = Loan::with(['member', 'loanProduct', 'repayments'])->findOrFail($id);
        
        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Loans', 'url' => route('admin.loans.index')],
            ['text' => $loan->loan_number, 'url' => route('admin.loans.show', $loan->id)],
            ['text' => 'Repayments', 'url' => '']
        ];

        return view('admin.loans.repayments', compact('loan', 'breadcrumbs'));
    }

    public function addRepayment(Request $request, $id)
    {
        $loan = Loan::where('status', 'active')->findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            // Call the API LoanTransactionController for repayment
            $response = $this->callApiRepayment($loan, $request);
            
            if ($response['success']) {
                return redirect()->back()
                    ->with('success', 'Repayment recorded successfully.');
            } else {
                return redirect()->back()
                    ->with('error', 'Failed to record repayment: ' . $response['message']);
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to record repayment: ' . $e->getMessage());
        }
    }

    /**
     * Call the API LoanTransactionController for repayment
     */
    private function callApiRepayment($loan, $request)
    {
        // Create a proper LoanRepaymentRequest instance
        $apiRequest = new \App\Http\Requests\LoanRepaymentRequest();
        
        // Set the request data
        $apiRequest->merge([
            'loan_id' => $loan->id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->reference,
            'notes' => $request->notes,
        ]);

        // Set the authenticated user for the API call
        $apiRequest->setUserResolver(function () {
            return auth()->user();
        });

        // Set the route resolver to make the request work properly
        $apiRequest->setRouteResolver(function () {
            return app('router')->getRoutes()->match(
                app('request')->create('/api/loans/repayment', 'POST')
            );
        });

        // Call the API controller
        $apiController = new \App\Http\Controllers\Api\Transactions\LoanTransactionController(
            app(\App\Services\TransactionService::class),
            app(\App\Services\LoanCalculationService::class)
        );

        $response = $apiController->repayment($apiRequest);
        $responseData = $response->getData(true);

        return $responseData;
    }

    /**
     * Get loan repayment schedule from API
     */
    public function getSchedule($id)
    {
        $loan = Loan::findOrFail($id);
        
        try {
            $response = $this->callApiSchedule($loan);
            
            if ($response['success']) {
                return response()->json($response);
            } else {
                return response()->json($response, 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get loan schedule: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get loan transaction history from API
     */
    public function getHistory($id)
    {
        $loan = Loan::findOrFail($id);
        
        try {
            $response = $this->callApiHistory($loan);
            
            if ($response['success']) {
                return response()->json($response);
            } else {
                return response()->json($response, 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get loan history: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get loan summary from API
     */
    public function getSummary($id)
    {
        $loan = Loan::findOrFail($id);
        
        try {
            $response = $this->callApiSummary($loan);
            
            if ($response['success']) {
                return response()->json($response);
            } else {
                return response()->json($response, 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get loan summary: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Call the API LoanTransactionController for schedule
     */
    private function callApiSchedule($loan)
    {
        $apiRequest = new \Illuminate\Http\Request();
        $apiRequest->setUserResolver(function () {
            return auth()->user();
        });

        $apiController = new \App\Http\Controllers\Api\Transactions\LoanTransactionController(
            app(\App\Services\TransactionService::class),
            app(\App\Services\LoanCalculationService::class)
        );

        $response = $apiController->schedule($apiRequest, $loan->id);
        return $response->getData(true);
    }

    /**
     * Call the API LoanTransactionController for history
     */
    private function callApiHistory($loan)
    {
        $apiRequest = new \Illuminate\Http\Request();
        $apiRequest->setUserResolver(function () {
            return auth()->user();
        });

        $apiController = new \App\Http\Controllers\Api\Transactions\LoanTransactionController(
            app(\App\Services\TransactionService::class),
            app(\App\Services\LoanCalculationService::class)
        );

        $response = $apiController->history($apiRequest, $loan->id);
        return $response->getData(true);
    }

    /**
     * Call the API LoanTransactionController for summary
     */
    private function callApiSummary($loan)
    {
        $apiRequest = new \Illuminate\Http\Request();
        $apiRequest->setUserResolver(function () {
            return auth()->user();
        });

        $apiController = new \App\Http\Controllers\Api\Transactions\LoanTransactionController(
            app(\App\Services\TransactionService::class),
            app(\App\Services\LoanCalculationService::class)
        );

        $response = $apiController->summary($apiRequest, $loan->id);
        return $response->getData(true);
    }
}
