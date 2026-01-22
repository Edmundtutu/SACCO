<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    /**
     * Display a listing of tenants (super-admin only)
     */
    public function index()
    {
        // Get all tenants with counts (no scoping needed as Tenant is the root entity)
        $tenants = Tenant::withCount(['users', 'loans'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate statistics
        $stats = [
            'total' => $tenants->count(),
            'active' => $tenants->where('status', 'active')->count(),
            'trial' => $tenants->where('status', 'trial')->count(),
            'suspended' => $tenants->where('status', 'suspended')->count(),
        ];

        return view('admin.tenants.index', compact('tenants', 'stats'));
    }

    /**
     * Show the form for creating a new tenant
     */
    public function create()
    {
        return view('admin.tenants.create');
    }

    /**
     * Store a newly created tenant
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sacco_name' => 'required|string|max:255',
            'sacco_code' => 'required|string|max:50|unique:tenants,sacco_code',
            'slug' => 'required|string|max:255|unique:tenants,slug',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'nullable|string',
            'country' => 'required|string|max:100',
            'currency' => 'required|string|max:10',
            'subscription_plan' => 'required|in:basic,standard,premium,enterprise',
            'status' => 'required|in:trial,active,suspended,inactive',
            'max_members' => 'required|integer|min:1',
            'max_staff' => 'required|integer|min:1',
            'max_loans' => 'required|integer|min:1',
        ]);

        $tenant = Tenant::create($validated);

        return redirect()->route('admin.tenants.index')
            ->with('success', 'SACCO created successfully');
    }

    /**
     * Display the specified tenant
     */
    public function show(Tenant $tenant)
    {
        $tenant->loadCount(['users', 'loans', 'transactions']);
        
        return view('admin.tenants.show', compact('tenant'));
    }

    /**
     * Show the form for editing the specified tenant
     */
    public function edit(Tenant $tenant)
    {
        return view('admin.tenants.edit', compact('tenant'));
    }

    /**
     * Update the specified tenant
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'sacco_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'nullable|string',
            'subscription_plan' => 'required|in:basic,standard,premium,enterprise',
            'status' => 'required|in:trial,active,suspended,inactive',
            'max_members' => 'required|integer|min:1',
            'max_staff' => 'required|integer|min:1',
            'max_loans' => 'required|integer|min:1',
        ]);

        $tenant->update($validated);

        return redirect()->route('admin.tenants.index')
            ->with('success', 'SACCO updated successfully');
    }

    /**
     * Switch tenant context (super-admin only)
     */
    public function switchTenant(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'nullable|exists:tenants,id',
        ]);

        if (isset($validated['tenant_id'])) {
            session(['admin_tenant_id' => $validated['tenant_id']]);
        } else {
            session()->forget('admin_tenant_id');
        }

        return response()->json([
            'success' => true,
            'message' => 'Tenant context switched successfully',
        ]);
    }
}
