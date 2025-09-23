@extends('admin.layouts.app')

@section('title', 'Loans Management')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">Loans Management</h1>
                <p class="text-muted">Manage loan applications, approvals, and disbursements</p>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number">{{ number_format($stats['total_loans']) }}</div>
                    <div class="stats-label">Total Loans</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card text-white" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number">{{ number_format($stats['pending_loans']) }}</div>
                    <div class="stats-label">Pending Approval</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-clock"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card text-white" style="background: linear-gradient(135deg, #28a745, #20c997);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number">{{ number_format($stats['active_loans']) }}</div>
                    <div class="stats-label">Active Loans</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card text-white" style="background: linear-gradient(135deg, #6f42c1, #e83e8c);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number">UGX {{ number_format($stats['total_disbursed'], 0) }}</div>
                    <div class="stats-label">Total Disbursed</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="{{ route('admin.loans.applications') }}" class="btn btn-outline-warning btn-sm w-100 mb-2">
                            <i class="bi bi-clock"></i> Pending Applications ({{ $stats['pending_loans'] }})
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.loans.index', ['status' => 'active']) }}" class="btn btn-outline-success btn-sm w-100 mb-2">
                            <i class="bi bi-check-circle"></i> Active Loans
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.loans.products') }}" class="btn btn-outline-info btn-sm w-100 mb-2">
                            <i class="bi bi-gear"></i> Loan Products
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.reports.loans') }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
                            <i class="bi bi-file-earmark-text"></i> Loan Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
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
                                <i class="bi bi-search"></i> Search
                            </button>
                            <a href="{{ route('admin.loans.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Loans Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-currency-dollar"></i> Loans List 
                    <span class="badge bg-primary ms-2">{{ $loans->total() }} total</span>
                </h5>
            </div>
            <div class="card-body">
                @if($loans->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
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
                                    <strong>{{ $loan->loan_number ?? 'L' . str_pad($loan->id, 6, '0', STR_PAD_LEFT) }}</strong>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $loan->member->name }}</strong><br>
                                        <small class="text-muted">{{ $loan->member->member_number ?? $loan->member->email }}</small>
                                    </div>
                                </td>
                                <td>{{ $loan->loanProduct->name ?? 'N/A' }}</td>
                                <td>UGX {{ number_format($loan->principal_amount, 2) }}</td>
                                <td>UGX {{ number_format($loan->outstanding_balance ?? $loan->principal_amount, 2) }}</td>
                                <td>
                                    @switch($loan->status)
                                        @case('pending')
                                            <span class="badge bg-warning">Pending</span>
                                            @break
                                        @case('approved')
                                            <span class="badge bg-info">Approved</span>
                                            @break
                                        @case('active')
                                            <span class="badge bg-success">Active</span>
                                            @break
                                        @case('completed')
                                            <span class="badge bg-secondary">Completed</span>
                                            @break
                                        @case('rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark">{{ ucfirst($loan->status) }}</span>
                                    @endswitch
                                </td>
                                <td>{{ $loan->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.loans.show', $loan->id) }}" 
                                           class="btn btn-outline-primary btn-sm" 
                                           title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        
                                        @if($loan->status == 'pending')
                                        <form action="{{ route('admin.loans.approve', $loan->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-outline-success btn-sm" 
                                                    title="Approve" 
                                                    onclick="return confirm('Approve this loan?')">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>
                                        @endif
                                        
                                        @if($loan->status == 'approved')
                                        <button type="button" 
                                                class="btn btn-outline-info btn-sm" 
                                                title="Disburse" 
                                                data-action="disburse-loan"
                                                data-loan-id="{{ $loan->id }}">
                                            <i class="bi bi-cash"></i>
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
                        <small>
                            <i class="bi bi-info-circle me-1"></i>
                            Showing {{ $loans->firstItem() }} to {{ $loans->lastItem() }} of {{ $loans->total() }} results
                        </small>
                    </div>
                    <div>
                        {{ $loans->appends(request()->query())->links('pagination.bootstrap-5') }}
                    </div>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-currency-dollar display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No loans found</h4>
                    <p class="text-muted">Try adjusting your search criteria.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection