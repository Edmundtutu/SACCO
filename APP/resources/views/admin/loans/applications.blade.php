@extends('admin.layouts.app')

@section('title', 'Loan Applications')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Pending Loan Applications</h1>
            <p class="text-muted">Review and approve loan applications</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.loans.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Loans
            </a>
            <a href="{{ route('admin.loans.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Loan
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Applications
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $applications->total() }}
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Amount
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                UGX {{ number_format($applications->sum('principal_amount'), 0) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cash-register fa-2x text-gray-300"></i>
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
                                Average Amount
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                UGX {{ $applications->avg('principal_amount') ? number_format($applications->avg('principal_amount'), 0) : 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                This Week
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $applications->where('created_at', '>=', now()->subDays(7))->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-week fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-file-alt"></i> Loan Applications
            </h6>
            <div class="d-flex gap-2">
                <span class="badge bg-warning">{{ $applications->total() }} pending</span>
                <button class="btn btn-sm btn-outline-primary" onclick="refreshApplications()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($applications->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover" id="applicationsTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-nowrap">Application #</th>
                            <th class="text-nowrap">Member</th>
                            <th class="text-nowrap">Product</th>
                            <th class="text-nowrap">Amount</th>
                            <th class="text-nowrap">Period</th>
                            <th class="text-nowrap">Applied Date</th>
                            <th class="text-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($applications as $application)
                        <tr>
                            <td>
                                <strong>
                                    <a href="{{ route('admin.loans.show', $application->id) }}" class="text-decoration-none">
                                        {{ $application->loan_number ?? 'L' . str_pad($application->id, 6, '0', STR_PAD_LEFT) }}
                                    </a>
                                </strong>
                            </td>
                            <td>
                                <div>
                                    <strong class="text-nowrap">{{ $application->member->name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $application->member->member_number ?? $application->member->email ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong class="text-nowrap">{{ $application->loanProduct->name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $application->loanProduct->interest_rate ?? 'N/A' }}% p.a.</small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong class="text-nowrap">UGX {{ number_format($application->principal_amount, 0) }}</strong><br>
                                    <small class="text-muted">{{ $application->repayment_period }} months</small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="badge-icon bg-info">{{ $application->guarantors->count() }} guarantors</span>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong class="text-nowrap">{{ $application->created_at ? $application->created_at->format('M d, Y') : 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $application->created_at ? $application->created_at->format('H:i') : 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group gap-1" role="group">
                                    <a href="{{ route('admin.loans.show', $application->id) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <form action="{{ route('admin.loans.approve', $application->id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-success" 
                                                title="Approve" 
                                                onclick="return confirm('Approve this loan application?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            title="Reject" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#rejectModal{{ $application->id }}">
                                        <i class="fas fa-times"></i>
                                    </button>
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
                        Showing {{ $applications->firstItem() }} to {{ $applications->lastItem() }} of {{ $applications->total() }} results
                    </small>
                </div>
                <div>
                    {{ $applications->links('pagination.bootstrap-5') }}
                </div>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h5 class="mt-3 text-muted">No pending applications</h5>
                <p class="text-muted">All loan applications have been processed.</p>
                <a href="{{ route('admin.loans.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Application
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Reject Modals -->
@foreach($applications as $application)
<div class="modal fade" id="rejectModal{{ $application->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Loan Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.loans.reject', $application->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Are you sure you want to reject this loan application?
                    </div>
                    <div class="mb-3">
                        <label for="reason{{ $application->id }}" class="form-label">Rejection Reason *</label>
                        <textarea class="form-control" id="reason{{ $application->id }}" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Application</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection

@push('scripts')
<script>
function refreshApplications() {
    location.reload();
}
</script>
@endpush
