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
        $query = Loan::with(['user', 'loanProduct']);
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('member_number', 'like', "%{$search}%");
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
        $loan = Loan::with(['user', 'loanProduct', 'repayments', 'guarantors.guarantor'])
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

        DB::beginTransaction();
        try {
            $loan->update([
                'status' => 'active',
                'disbursed_at' => now(),
                'disbursed_by' => auth()->id(),
            ]);

            // Here you would typically integrate with your accounting system
            // to record the disbursement transaction

            DB::commit();
            
            return redirect()->back()
                ->with('success', 'Loan disbursed successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to disburse loan: ' . $e->getMessage());
        }
    }

    public function applications()
    {
        $applications = Loan::where('status', 'pending')
            ->with(['user', 'loanProduct'])
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