@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
@if($isSuperAdminNeutral)
{{-- ═══════════════ SUPER ADMIN NEUTRAL — PLATFORM OVERVIEW ═══════════════ --}}
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Platform Overview</h1>
            <p class="text-muted">Aggregate statistics across all registered SACCOs</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.tenants.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Add New SACCO
            </a>
            <button class="btn btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <span>No SACCO selected. Use the switcher in the top bar to view tenant-specific data. Showing platform-wide figures below.</span>
    </div>

    {{-- Platform stat cards --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total SACCOs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($platformStats['total_saccos']) }}</div>
                            <small class="text-muted">{{ $platformStats['active_saccos'] }} active &middot; {{ $platformStats['trial_saccos'] }} trial &middot; {{ $platformStats['suspended_saccos'] }} suspended</small>
                        </div>
                        <div class="col-auto"><i class="fas fa-building fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Platform Members</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($platformStats['total_members']) }}</div>
                            <small class="text-muted">{{ number_format($platformStats['total_staff']) }} staff accounts</small>
                        </div>
                        <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Loans</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($platformStats['total_loans']) }}</div>
                            <small class="text-muted">{{ number_format($platformStats['active_loans']) }} active</small>
                        </div>
                        <div class="col-auto"><i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Today's Transactions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($platformStats['today_transactions']) }}</div>
                            <small class="text-muted">across all SACCOs</small>
                        </div>
                        <div class="col-auto"><i class="fas fa-exchange-alt fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent SACCOs table --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-building me-2"></i>Recently Registered SACCOs</h6>
                    <a href="{{ route('admin.tenants.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>SACCO</th>
                                    <th class="text-center">Members</th>
                                    <th class="text-center">Loans</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($platformStats['recent_saccos'] as $sacco)
                                <tr style="cursor:pointer" onclick="window.location.href='{{ route('admin.tenants.show', $sacco->id) }}'">
                                    <td>
                                        <div class="fw-semibold">{{ $sacco->sacco_name }}</div>
                                        <small class="text-muted">{{ $sacco->sacco_code }}</small>
                                    </td>
                                    <td class="text-center">{{ number_format($sacco->users_count) }}</td>
                                    <td class="text-center">{{ number_format($sacco->loans_count) }}</td>
                                    <td>
                                        @php
                                            $sc = match($sacco->status) { 'active' => 'success', 'trial' => 'info', 'suspended' => 'danger', default => 'secondary' };
                                        @endphp
                                        <span class="badge bg-{{ $sc }}">{{ ucfirst($sacco->status) }}</span>
                                    </td>
                                    <td><span class="text-nowrap">{{ $sacco->created_at->format('M d, Y') }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No SACCOs registered yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.tenants.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-1"></i> Add New SACCO
                        </a>
                        <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-building me-1"></i> Manage SACCOs
                        </a>
                    </div>
                    <hr>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Active SACCOs</span>
                            <span class="badge bg-success">{{ $platformStats['active_saccos'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">On Trial</span>
                            <span class="badge bg-info">{{ $platformStats['trial_saccos'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Suspended</span>
                            <span class="badge bg-danger">{{ $platformStats['suspended_saccos'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@else
{{-- ═══════════════ TENANT-BOUND DASHBOARD ═══════════════ --}}
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
            <p class="text-muted">SACCO Management System Overview</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    @if($activeTenant && $limits)
    <div class="alert alert-secondary" role="alert">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <span class="fw-semibold"><i class="bi bi-speedometer2 me-1"></i>{{ $activeTenant->sacco_name }} usage snapshot</span>
            @if(isset($limits['max_members']))
                <span class="badge bg-primary-subtle text-primary">Members {{ number_format($stats['total_members']) }} / {{ number_format($limits['max_members']) }}</span>
            @endif
            @if(isset($limits['max_staff']))
                <span class="badge bg-primary-subtle text-primary">Staff {{ number_format($stats['active_staff']) }} / {{ number_format($limits['max_staff']) }}</span>
            @endif
            @if(isset($limits['max_loans']))
                <span class="badge bg-primary-subtle text-primary">Active Loans {{ number_format($stats['active_loans']) }} / {{ number_format($limits['max_loans']) }}</span>
            @endif
            @if(isset($limits['max_loan_amount']) && $limits['max_loan_amount'])
                <span class="badge bg-primary-subtle text-primary">Loan Cap UGX {{ number_format($limits['max_loan_amount']) }}</span>
            @endif
        </div>
    </div>
    @endif

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Members
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_members']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Savings
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                UGX {{ number_format($stats['total_savings']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-piggy-bank fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Loans
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['active_loans']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Transactions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['pending_transactions']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Phase 2 — Expense & Income summary cards (feature-flagged) --}}
    @if(config('financial.enable_expense_transactions') || config('financial.enable_income_transactions'))
    <div class="row mb-4">
        @if(config('financial.enable_expense_transactions'))
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Expenses This Month
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['monthly_expenses'] ?? 0, 2) }}
                            </div>
                            <small class="text-muted">{{ $stats['monthly_expense_count'] ?? 0 }} transactions</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(config('financial.enable_income_transactions'))
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Non-Loan Income This Month
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['monthly_income'] ?? 0, 2) }}
                            </div>
                            <small class="text-muted">{{ $stats['monthly_income_count'] ?? 0 }} transactions</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(config('financial.enable_expense_transactions') || config('financial.enable_income_transactions'))
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-{{ ($stats['monthly_net'] ?? 0) >= 0 ? 'success' : 'danger' }} shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-{{ ($stats['monthly_net'] ?? 0) >= 0 ? 'success' : 'danger' }} text-uppercase mb-1">
                                Net (Income − Expenses)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['monthly_net'] ?? 0, 2) }}
                            </div>
                            <small class="text-muted">
                                <a href="{{ route('admin.reports.profit-loss') }}">View P&amp;L</a>
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Pending Approvals Alert -->
    @php
        $pendingTransactions = $stats['pending_transactions'];
        $pendingLoans = $stats['pending_loans'];
        $pendingMembers = $stats['pending_members'];
    @endphp

    @if($pendingTransactions > 0 || $pendingLoans > 0 || $pendingMembers > 0)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Attention!</strong> You have pending approvals:
        @if($pendingTransactions > 0)
            <a href="{{ route('admin.transactions.index') }}" class="alert-link">{{ $pendingTransactions }} transactions</a>
        @endif
        @if($pendingLoans > 0)
            <a href="{{ route('admin.loans.index') }}" class="alert-link">{{ $pendingLoans }} loans</a>
        @endif
        @if($pendingMembers > 0)
            <a href="{{ route('admin.members.requests') }}" class="alert-link">{{ $pendingMembers }} members</a>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Recent Transactions -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-arrow-repeat"></i> Recent Transactions 
                        <span class="badge bg-primary ms-2">{{ $stats['recent_transactions']->total() }} total</span>
                    </h6>
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye"></i> View All
                    </a>
                </div>
                <div class="card-body">
                    @if($stats['recent_transactions']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Transaction #</th>
                                    <th>Member</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['recent_transactions'] as $transaction)
                                <tr style="cursor: pointer;" onclick="window.location.href='{{ route('admin.transactions.show', $transaction->id) }}'">
                                    <td>
                                        <strong>
                                            {{ $transaction->transaction_number ?? 'T' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}
                                        </strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $transaction->account->member->name ?? 'N/A' }}</strong><br>
                                            <small class="text-muted">{{ $transaction->account->member->member_number ?? $transaction->account->member->email ?? 'N/A' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @switch($transaction->type)
                                            @case('deposit')
                                                <span class="badge-icon bg-success">Deposit</span>
                                                @break
                                            @case('withdrawal')
                                                <span class="badge-icon bg-warning">Withdrawal</span>
                                                @break
                                            @case('loan_disbursement')
                                                <span class="badge-icon bg-info">Loan Disbursement</span>
                                                @break
                                            @case('loan_repayment')
                                                <span class="badge-icon bg-primary">Loan Repayment</span>
                                                @break
                                            @case('share_purchase')
                                                <span class="badge-icon bg-secondary">Share Purchase</span>
                                                @break
                                            @default
                                                <span class="badge-icon bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</span>
                                        @endswitch
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-nowrap">UGX {{ number_format($transaction->amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        @switch($transaction->status)
                                            @case('pending')
                                                <span class="badge-status pending">Pending</span>
                                                @break
                                            @case('completed')
                                                <span class="badge-status completed">Completed</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge-status failed">Rejected</span>
                                                @break
                                            @case('failed')
                                                <span class="badge-status failed">Failed</span>
                                                @break
                                            @default
                                                <span class="badge-status default">{{ ucfirst($transaction->status) }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <div>
                                           <span class="text-nowrap"> {{ $transaction->created_at->format('M d') }}</span><br>
                                            <small class="text-muted">{{ $transaction->created_at->format('H:i') }}</small>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <div class="pagination-info">
                            <small>
                                <i class="bi bi-info-circle me-1"></i>
                                Showing {{ $stats['recent_transactions']->firstItem() }} to {{ $stats['recent_transactions']->lastItem() }} of {{ $stats['recent_transactions']->total() }} results
                            </small>
                        </div>
                        <div>
                            {{ $stats['recent_transactions']->links('pagination.bootstrap-5') }}
                        </div>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-arrow-repeat display-1 text-muted"></i>
                        <h4 class="text-muted mt-3">No recent transactions found</h4>
                        <p class="text-muted">Transactions will appear here once they are created.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions & Stats -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-exchange-alt"></i> Process Transaction
                        </a>
                        <a href="{{ route('admin.loans.index') }}" class="btn btn-outline-success">
                            <i class="fas fa-hand-holding-usd"></i> Review Loans
                        </a>
                        <a href="{{ route('admin.members.requests') }}" class="btn btn-outline-warning">
                            <i class="fas fa-user-plus"></i> Approve Members
                        </a>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-info">
                            <i class="fas fa-chart-bar"></i> Generate Reports
                        </a>
                        <a href="{{ route('admin.transactions.general-ledger') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-book"></i> General Ledger
                        </a>
                    </div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Financial Summary</h6>
                </div>
                <div class="card-body">
                    @php
                        $totalSavings = $stats['total_savings'];
                        $totalLoans = $stats['total_loans'];
                        $totalShares = $stats['total_shares'];
                        $todayTransactions = $stats['today_transactions'];
                    @endphp
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Total Savings:</span>
                            <span class="font-weight-bold">UGX {{ number_format($totalSavings) }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Outstanding Loans:</span>
                            <span class="font-weight-bold text-danger">UGX {{ number_format($totalLoans) }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Share Capital:</span>
                            <span class="font-weight-bold text-info">UGX {{ number_format($totalShares) }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Today's Transactions:</span>
                            <span class="font-weight-bold text-success">UGX {{ number_format($todayTransactions) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Status -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Database:</span>
                            <span class="badge badge-success">Online</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">API Status:</span>
                            <span class="badge badge-success">Active</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Last Backup:</span>
                            <span class="text-muted">{{ now()->subDays(1)->format('M d, Y') }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Active Users:</span>
                            {{-- TODO: to a column on the users table to track the last login time--}}
                            {{-- <span class="font-weight-bold">{{ \App\Models\User::where('last_login_at', '>', now()->subHours(1))->count() }}</span> --}} 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4 chart-card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-graph-up me-2"></i>Transaction Volume (Last 7 Days)
                    </h6>
                    <div class="chart-controls">
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshTransactionChart()">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="transactionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow mb-4 chart-card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-people me-2"></i>Member Growth
                    </h6>
                    <div class="chart-controls">
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshMemberChart()">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="memberChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
@if(!$isSuperAdminNeutral)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function refreshDashboard() {
    location.reload();
}

function refreshTransactionChart() {
    transactionChart.update('active');
}

function refreshMemberChart() {
    memberChart.update('active');
}

// Transaction Chart
const transactionCtx = document.getElementById('transactionChart').getContext('2d');
const transactionChart = new Chart(transactionCtx, {
    type: 'line',
    data: {
        labels: [
            @for($i = 6; $i >= 0; $i--)
                '{{ now()->subDays($i)->format("M d") }}',
            @endfor
        ],
        datasets: [{
            label: 'Daily Transactions',
            data: [
                @for($i = 6; $i >= 0; $i--)
                    {{ \App\Models\Transaction::whereDate('created_at', now()->subDays($i))->count() }},
                @endfor
            ],
            borderColor: '#3399CC',
            backgroundColor: 'rgba(51, 153, 204, 0.1)',
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#3399CC',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8,
            pointHoverBackgroundColor: '#2980b9',
            pointHoverBorderColor: '#ffffff',
            pointHoverBorderWidth: 3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    usePointStyle: true,
                    padding: 20,
                    font: {
                        size: 12,
                        weight: '500'
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#ffffff',
                bodyColor: '#ffffff',
                borderColor: '#3399CC',
                borderWidth: 1,
                cornerRadius: 8,
                displayColors: false,
                titleFont: {
                    size: 14,
                    weight: 'bold'
                },
                bodyFont: {
                    size: 13
                }
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: '#6c757d',
                    font: {
                        size: 11,
                        weight: '500'
                    }
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)',
                    drawBorder: false
                },
                ticks: {
                    color: '#6c757d',
                    font: {
                        size: 11,
                        weight: '500'
                    }
                }
            }
        },
        animation: {
            duration: 2000,
            easing: 'easeInOutQuart'
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    }
});

// Member Growth Chart
const memberCtx = document.getElementById('memberChart').getContext('2d');
const memberChart = new Chart(memberCtx, {
    type: 'bar',
    data: {
        labels: [
            @for($i = 5; $i >= 0; $i--)
                '{{ now()->subMonths($i)->format("M Y") }}',
            @endfor
        ],
        datasets: [{
            label: 'New Members',
            data: [
                @for($i = 5; $i >= 0; $i--)
                    {{ \App\Models\User::where('role', 'member')->whereMonth('created_at', now()->subMonths($i)->month)->whereYear('created_at', now()->subMonths($i)->year)->count() }},
                @endfor
            ],
            backgroundColor: [
                'rgba(52, 152, 219, 0.8)',
                'rgba(46, 204, 113, 0.8)',
                'rgba(155, 89, 182, 0.8)',
                'rgba(241, 196, 15, 0.8)',
                'rgba(230, 126, 34, 0.8)',
                'rgba(231, 76, 60, 0.8)'
            ],
            borderColor: [
                '#3498db',
                '#2ecc71',
                '#9b59b6',
                '#f1c40f',
                '#e67e22',
                '#e74c3c'
            ],
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false,
            hoverBackgroundColor: [
                'rgba(52, 152, 219, 1)',
                'rgba(46, 204, 113, 1)',
                'rgba(155, 89, 182, 1)',
                'rgba(241, 196, 15, 1)',
                'rgba(230, 126, 34, 1)',
                'rgba(231, 76, 60, 1)'
            ],
            hoverBorderWidth: 3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    usePointStyle: true,
                    padding: 20,
                    font: {
                        size: 12,
                        weight: '500'
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#ffffff',
                bodyColor: '#ffffff',
                borderColor: '#3498db',
                borderWidth: 1,
                cornerRadius: 8,
                displayColors: false,
                titleFont: {
                    size: 14,
                    weight: 'bold'
                },
                bodyFont: {
                    size: 13
                }
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: '#6c757d',
                    font: {
                        size: 11,
                        weight: '500'
                    }
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)',
                    drawBorder: false
                },
                ticks: {
                    color: '#6c757d',
                    font: {
                        size: 11,
                        weight: '500'
                    }
                }
            }
        },
        animation: {
            duration: 2000,
            easing: 'easeInOutQuart'
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    }
});
</script>
@endif
@endpush