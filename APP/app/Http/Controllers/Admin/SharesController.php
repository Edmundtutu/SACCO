<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Share;
use App\Models\Dividend;
use App\Models\DividendPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SharesController extends Controller
{
    public function index()
    {
        $stats = [
            'total_shares' => Share::sum('shares_count'),
            'total_value' => Share::sum('amount'),
            'total_members' => Share::distinct('user_id')->count(),
            'recent_purchases' => Share::with(['user'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Shares', 'url' => route('admin.shares.index')]
        ];

        return view('admin.shares.index', compact('stats', 'breadcrumbs'));
    }

    public function purchases(Request $request)
    {
        $query = Share::with(['user']);
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('member_number', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $purchases = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Shares', 'url' => route('admin.shares.index')],
            ['text' => 'Purchases', 'url' => '']
        ];

        return view('admin.shares.purchases', compact('purchases', 'breadcrumbs'));
    }

    public function approvePurchase($id)
    {
        $share = Share::where('status', 'pending')->findOrFail($id);

        $share->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return redirect()->back()
            ->with('success', 'Share purchase approved successfully.');
    }

    public function dividends()
    {
        $dividends = Dividend::with(['payments.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Shares', 'url' => route('admin.shares.index')],
            ['text' => 'Dividends', 'url' => '']
        ];

        return view('admin.shares.dividends', compact('dividends', 'breadcrumbs'));
    }

    public function declareDividend(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
            'rate' => 'required|numeric|min:0|max:100',
            'total_amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Create dividend declaration
            $dividend = Dividend::create([
                'year' => $request->year,
                'rate' => $request->rate,
                'total_amount' => $request->total_amount,
                'declared_by' => auth()->id(),
                'declared_at' => now(),
            ]);

            // Calculate and create dividend payments for all shareholders
            $shareholders = Share::where('status', 'approved')
                ->selectRaw('user_id, SUM(shares_count) as total_shares, SUM(amount) as total_amount')
                ->groupBy('user_id')
                ->get();

            foreach ($shareholders as $shareholder) {
                $dividendAmount = ($shareholder->total_amount * $request->rate) / 100;
                
                DividendPayment::create([
                    'dividend_id' => $dividend->id,
                    'user_id' => $shareholder->user_id,
                    'shares_count' => $shareholder->total_shares,
                    'amount' => $dividendAmount,
                    'status' => 'pending',
                ]);
            }

            DB::commit();
            
            return redirect()->back()
                ->with('success', 'Dividend declared successfully for ' . $shareholders->count() . ' shareholders.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to declare dividend: ' . $e->getMessage());
        }
    }
}