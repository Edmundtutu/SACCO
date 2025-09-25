<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
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

    /**
     * Disburse loan Logic is already implemented in the api end-point
     *
     * This method is just a placeholder for the frontend to call the api end-point
     *
     * The api end-point will be called from the frontend when the user clicks the disburse button
     *
     * The api end-point will then call the LoanDisbursementController::disburse method
     *
     * The LoanDisbursementController::disburse method will then call the LoanDisbursementService::disburse method
     *
     *
     * @param Request $request
     * @param int $id
     * @return void
     *
    */
    public function disburse(Request $request, $id)
    {
        // TODO: Redirect to and api endpoint where Loans disbursement is being managed
        // ELSE: Cut out this method and directly call the api end-point directly in the frontend Js
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
}
