@extends('admin.layouts.app')

@section('title', 'Loans Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Loans Management</h1>
            <p class="text-muted">Manage loan applications, approvals, and disbursements</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.loans.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Loan
            </a>
            <a href="{{ route('admin.loans.applications') }}" class="btn btn-outline-secondary">
                <i class="fas fa-file-alt"></i> Applications
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Loans
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_loans']) }}
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
                                Pending Approval
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['pending_loans']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                Active Loans
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['active_loans']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                Total Disbursed
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                UGX {{ number_format($stats['total_disbursed'], 0) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cash-register fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('admin.loans.applications') }}" class="btn btn-outline-warning btn-sm w-100 mb-2">
                                <i class="fas fa-clock"></i> Pending Applications ({{ $stats['pending_loans'] }})
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.loans.index', ['status' => 'active']) }}" class="btn btn-outline-success btn-sm w-100 mb-2">
                                <i class="fas fa-check-circle"></i> Active Loans
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.loans.products') }}" class="btn btn-outline-info btn-sm w-100 mb-2">
                                <i class="fas fa-cogs"></i> Loan Products
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.reports.loans') }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
                                <i class="fas fa-file-alt"></i> Loan Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Loans</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.loans.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search Loans</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Member name, loan number...">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="{{ route('admin.loans.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Loans Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Loans List
            </h6>
            <div class="d-flex gap-2">
                <span class="badge bg-primary">{{ $loans->total() }} total</span>
                <button class="btn btn-sm btn-outline-primary" onclick="refreshTable()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($loans->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover" id="loansTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Loan #</th>
                            <th>Member</th>
                            <th>Product</th>
                            <th>Principal</th>
                            <th>Outstanding</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loans as $loan)
                        <tr>
                            <td>
                                <strong>
                                    <a href="{{ route('admin.loans.show', $loan->id) }}" class="text-decoration-none">
                                        {{ $loan->loan_number ?? 'L' . str_pad($loan->id, 6, '0', STR_PAD_LEFT) }}
                                    </a>
                                </strong>
                            </td>
                            <td>
                                <div>
                                    <strong class="text-nowrap">{{ $loan->member->name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $loan->member->member_number ?? $loan->member->email ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong class="text-nowrap">{{ $loan->loanProduct->name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $loan->loanProduct->type ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong class="text-nowrap">UGX {{ number_format($loan->principal_amount, 0) }}</strong><br>
                                    <small class="text-muted">{{ $loan->repayment_period_months }} months</small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong class="text-nowrap">UGX {{ number_format($loan->outstanding_balance ?? $loan->principal_amount, 0) }}</strong><br>
                                    <small class="text-muted">{{ $loan->status == 'active' ? 'Outstanding' : 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                @switch($loan->status)
                                    @case('pending')
                                        <span class="badge-icon bg-warning">Pending</span>
                                        @break
                                    @case('approved')
                                        <span class="badge-icon bg-info">Approved</span>
                                        @break
                                    @case('active')
                                        <span class="badge-icon bg-success">Active</span>
                                        @break
                                    @case('completed')
                                        <span class="badge-icon bg-secondary">Completed</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge-icon bg-danger">Rejected</span>
                                        @break
                                    @default
                                        <span class="badge-icon bg-light text-dark">{{ ucfirst($loan->status) }}</span>
                                @endswitch
                            </td>
                            <td>
                                <div>
                                    <strong class="text-nowrap">{{ $loan->created_at ? $loan->created_at->format('M d, Y') : 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $loan->created_at ? $loan->created_at->format('H:i') : 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group gap-1" role="group">
                                    <a href="{{ route('admin.loans.show', $loan->id) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($loan->status == 'pending')
                                    <form action="{{ route('admin.loans.approve', $loan->id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-success" 
                                                title="Approve" 
                                                onclick="return confirm('Approve this loan?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    @endif
                                    
                                    @if($loan->status == 'approved')
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-info" 
                                            title="Disburse" 
                                            data-action="disburse-loan"
                                            data-loan-id="{{ $loan->id }}">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </button>
                                    @endif
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
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Showing {{ $loans->firstItem() }} to {{ $loans->lastItem() }} of {{ $loans->total() }} results
                    </small>
                </div>
                <div>
                    {{ $loans->appends(request()->query())->links('pagination.bootstrap-5') }}
                </div>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="mt-3 text-muted">No loans found</h5>
                <p class="text-muted">No loans match your current search criteria.</p>
                <a href="{{ route('admin.loans.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create First Loan
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection