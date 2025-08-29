<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MembersController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'member')->with(['accounts', 'loans']);
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('member_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $members = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Members', 'url' => route('admin.members.index')]
        ];

        return view('admin.members.index', compact('members', 'breadcrumbs'));
    }

    public function show($id)
    {
        $member = User::where('role', 'member')
            ->with(['accounts.transactions', 'loans.repayments', 'shares'])
            ->findOrFail($id);

        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Members', 'url' => route('admin.members.index')],
            ['text' => $member->name, 'url' => '']
        ];

        return view('admin.members.show', compact('member', 'breadcrumbs'));
    }

    public function edit($id)
    {
        $member = User::where('role', 'member')->findOrFail($id);
        
        $breadcrumbs = [
            ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['text' => 'Members', 'url' => route('admin.members.index')],
            ['text' => $member->name, 'url' => route('admin.members.show', $member->id)],
            ['text' => 'Edit', 'url' => '']
        ];

        return view('admin.members.edit', compact('member', 'breadcrumbs'));
    }

    public function update(Request $request, $id)
    {
        $member = User::where('role', 'member')->findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $member->id,
            'phone' => 'required|string|max:15',
            'national_id' => 'required|string|max:20',
            'address' => 'required|string',
            'occupation' => 'required|string|max:100',
            'monthly_income' => 'required|numeric|min:0',
            'status' => 'required|in:pending,active,suspended,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $member->update($request->only([
            'name', 'email', 'phone', 'national_id', 'address', 
            'occupation', 'monthly_income', 'status'
        ]));

        return redirect()->route('admin.members.show', $member->id)
            ->with('success', 'Member information updated successfully.');
    }

    public function approve($id)
    {
        $member = User::where('role', 'member')
            ->where('status', 'pending')
            ->findOrFail($id);

        $member->update([
            'status' => 'active',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        // Create default savings account
        Account::create([
            'user_id' => $member->id,
            'account_number' => 'SAV' . str_pad($member->id, 6, '0', STR_PAD_LEFT),
            'account_type' => 'savings',
            'product_id' => 1, // Default savings product
            'balance' => 0,
            'status' => 'active',
        ]);

        return redirect()->back()
            ->with('success', 'Member approved successfully and savings account created.');
    }

    public function suspend($id)
    {
        $member = User::where('role', 'member')->findOrFail($id);
        
        $member->update(['status' => 'suspended']);
        
        return redirect()->back()
            ->with('success', 'Member suspended successfully.');
    }

    public function activate($id)
    {
        $member = User::where('role', 'member')->findOrFail($id);
        
        $member->update(['status' => 'active']);
        
        return redirect()->back()
            ->with('success', 'Member activated successfully.');
    }
}