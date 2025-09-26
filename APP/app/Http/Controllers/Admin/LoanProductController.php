<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanProductController extends Controller
{
    public function index()
    {
        $products = LoanProduct::orderBy('name')->get();

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Loans', 'url' => route('admin.loans.index')],
            ['text' => 'Products', 'url' => '']
        ];

        return view('admin.loans.products', compact('products', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'min_amount' => 'required|numeric|min:1000',
            'max_amount' => 'required|numeric|min:1000',
            'min_period' => 'required|integer|min:1|max:60',
            'max_period' => 'required|integer|min:1|max:60',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            LoanProduct::create([
                'name' => $request->name,
                'interest_rate' => $request->interest_rate,
                'minimum_amount' => $request->min_amount,
                'maximum_amount' => $request->max_amount,
                'minimum_period_months' => $request->min_period,
                'maximum_period_months' => $request->max_period,
                'description' => $request->description,
                'is_active' => $request->has('is_active'),
                'type' => 'standard',
                'interest_calculation' => 'reducing_balance',
                'processing_fee_rate' => 0,
                'insurance_fee_rate' => 0,
                'required_guarantors' => 1,
                'guarantor_savings_multiplier' => 1,
                'grace_period_days' => 0,
                'penalty_rate' => 0,
                'minimum_savings_months' => 0,
                'savings_to_loan_ratio' => 1,
                'require_collateral' => false,
                'eligibility_criteria' => [],
                'required_documents' => []
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Loan product created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to create loan product: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $product = LoanProduct::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'min_amount' => 'required|numeric|min:1000',
            'max_amount' => 'required|numeric|min:1000',
            'min_period' => 'required|integer|min:1|max:60',
            'max_period' => 'required|integer|min:1|max:60',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $product->update([
                'name' => $request->name,
                'interest_rate' => $request->interest_rate,
                'minimum_amount' => $request->min_amount,
                'maximum_amount' => $request->max_amount,
                'minimum_period_months' => $request->min_period,
                'maximum_period_months' => $request->max_period,
                'description' => $request->description,
                'is_active' => $request->has('is_active')
            ]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Loan product updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to update loan product: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function activate($id)
    {
        $product = LoanProduct::findOrFail($id);

        DB::beginTransaction();
        try {
            $product->update(['is_active' => true]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Loan product activated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to activate loan product: ' . $e->getMessage());
        }
    }

    public function deactivate($id)
    {
        $product = LoanProduct::findOrFail($id);

        DB::beginTransaction();
        try {
            $product->update(['is_active' => false]);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Loan product deactivated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to deactivate loan product: ' . $e->getMessage());
        }
    }
}
