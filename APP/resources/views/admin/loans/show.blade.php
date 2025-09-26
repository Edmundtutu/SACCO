@extends('admin.layouts.app')

@section('title', 'Loan Details')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Loan Details</h1>
            <p class="text-muted">Loan #{{ $loan->loan_number ?? 'L' . str_pad($loan->id, 6, '0', STR_PAD_LEFT) }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.loans.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Loans
            </a>
            @if($loan->status == 'pending')
                <form action="{{ route('admin.loans.approve', $loan->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Approve this loan?')">
                        <i class="fas fa-check-circle"></i> Approve
                    </button>
                </form>
            @endif
            @if($loan->status == 'approved')
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#disburseModal">
                    <i class="fas fa-money-bill-wave"></i> Disburse
                </button>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Loan Information -->
            <div class="card shadow mb-4 modern-card">
                <div class="card-header py-3 modern-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text">
                            <i class="fas fa-hand-holding-usd me-2"></i>Loan Information
                        </h6>
                        <span class="badge-icon
                            @switch($loan->status)
                                @case('pending')
                                    bg-warning
                                    @break
                                @case('approved')
                                    bg-info
                                    @break
                                @case('active')
                                    bg-success
                                    @break
                                @case('completed')
                                    bg-secondary
                                    @break
                                @case('rejected')
                                    bg-danger
                                    @break
                                @default
                                    bg-light
                            @endswitch
                        ">
                            <i class="fas fa-{{ $loan->status == 'pending' ? 'clock' : ($loan->status == 'approved' ? 'check-circle' : ($loan->status == 'active' ? 'play-circle' : ($loan->status == 'completed' ? 'check-double' : 'times-circle'))) }} me-1"></i>
                            {{ ucfirst($loan->status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body modern-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-section">
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="fas fa-hashtag text-primary me-2"></i>Loan Number:
                                    </span>
                                    <span class="info-value">{{ $loan->loan_number ?? 'L' . str_pad($loan->id, 6, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="fas fa-user text-primary me-2"></i>Member:
                                    </span>
                                    <span class="info-value">{{ $loan->member->name ?? 'N/A' }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="fas fa-cogs text-primary me-2"></i>Loan Product:
                                    </span>
                                    <span class="info-value">{{ $loan->loanProduct->name ?? 'N/A' }}</span>
                                </div>
                                <div class="info-row amount-row">
                                    <span class="info-label">
                                        <i class="fas fa-dollar-sign text-primary me-2"></i>Principal Amount:
                                    </span>
                                    <span class="info-value amount-display text-primary">UGX {{ number_format($loan->principal_amount, 2) }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="fas fa-percentage text-primary me-2"></i>Interest Rate:
                                    </span>
                                    <span class="info-value">{{ $loan->loanProduct->interest_rate ?? 'N/A' }}% per annum</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-section">
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="fas fa-calendar-alt text-primary me-2"></i>Repayment Period:
                                    </span>
                                    <span class="info-value">{{ $loan->repayment_period_months ?? $loan->repayment_period ?? 'N/A' }} months</span>
                                </div>
                                <div class="info-row amount-row">
                                    <span class="info-label">
                                        <i class="fas fa-wallet text-primary me-2"></i>Outstanding Balance:
                                    </span>
                                    <span class="info-value amount-display text-danger">UGX {{ number_format($loan->outstanding_balance ?? $loan->principal_amount, 2) }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="fas fa-calendar text-primary me-2"></i>Application Date:
                                    </span>
                                    <span class="info-value">{{ $loan->created_at ? $loan->created_at->format('M d, Y H:i') : 'N/A' }}</span>
                                </div>
                                @if($loan->approved_at)
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="fas fa-check-circle text-primary me-2"></i>Approved Date:
                                    </span>
                                    <span class="info-value">{{ $loan->approved_at ? $loan->approved_at->format('M d, Y H:i') : 'N/A' }}</span>
                                </div>
                                @endif
                                @if($loan->disbursed_at)
                                <div class="info-row">
                                    <span class="info-label">
                                        <i class="fas fa-money-bill-wave text-primary me-2"></i>Disbursed Date:
                                    </span>
                                    <span class="info-value">{{ $loan->disbursed_at ? $loan->disbursed_at->format('M d, Y H:i') : 'N/A' }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @if($loan->purpose)
                    <div class="mt-4 description-section">
                        <h6 class="font-weight-bold section-title">
                            <i class="fas fa-align-left text-primary me-2"></i>Loan Purpose:
                        </h6>
                        <div class="description-content">
                            <p class="text-muted mb-0">{{ $loan->purpose }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Guarantors -->
            @if($loan->guarantors->count() > 0)
            <div class="card shadow mb-4 modern-card">
                <div class="card-header py-3 modern-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text">
                            <i class="fas fa-users me-2"></i>Guarantors
                            <span class="badge bg-light text-dark ms-2">{{ $loan->guarantors->count() }} guarantors</span>
                        </h6>
                    </div>
                </div>
                <div class="card-body modern-card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="modern-table-header">
                                <tr>
                                    <th>Name</th>
                                    <th>Member Number</th>
                                    <th>Phone</th>
                                    <th class="text-end">Guarantee Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($loan->guarantors as $guarantor)
                                <tr>
                                    <td>
                                        <div>
                                            <strong class="text-nowrap">{{ $guarantor->guarantor->name ?? 'N/A' }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong class="text-nowrap">{{ $guarantor->guarantor->member_number ?? 'N/A' }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong class="text-nowrap">{{ $guarantor->guarantor->phone ?? 'N/A' }}</strong>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-nowrap text-primary">UGX {{ number_format($guarantor->guarantee_amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge-icon bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Active
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Repayments -->
            @if($loan->repayments->count() > 0)
            <div class="card shadow mb-4 modern-card">
                <div class="card-header py-3 modern-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text">
                            <i class="fas fa-credit-card me-2"></i>Repayment History
                            <span class="badge bg-light text-dark ms-2">{{ $loan->repayments->count() }} payments</span>
                        </h6>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.loans.repayments', $loan->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View All
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body modern-card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="modern-table-header">
                                <tr>
                                    <th>Date</th>
                                    <th class="text-end">Amount</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th>Recorded By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($loan->repayments->take(5) as $repayment)
                                <tr>
                                    <td>
                                        <div>
                                            <strong class="text-nowrap">{{ $repayment->payment_date ? $repayment->payment_date->format('M d, Y') : 'N/A' }}</strong><br>
                                            <small class="text-muted">{{ $repayment->payment_date ? $repayment->payment_date->format('H:i') : 'N/A' }}</small>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-nowrap text-success">UGX {{ number_format($repayment->amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge-icon bg-info">
                                            <i class="fas fa-{{ $repayment->payment_method == 'cash' ? 'money-bill' : ($repayment->payment_method == 'bank_transfer' ? 'university' : 'mobile-alt') }} me-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $repayment->payment_method)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong class="text-nowrap">{{ $repayment->reference ?? 'N/A' }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong class="text-nowrap">{{ $repayment->recordedBy->name ?? 'System' }}</strong>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Member Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Member Information</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-user fa-2x"></i>
                        </div>
                    </div>
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="font-weight-bold">Name:</td>
                            <td>{{ $loan->member->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Member Number:</td>
                            <td>{{ $loan->member->member_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Email:</td>
                            <td>{{ $loan->member->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Phone:</td>
                            <td>{{ $loan->member->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Status:</td>
                            <td>
                                <span class="badge-icon bg-{{ $loan->member->status == 'active' ? 'success' : 'warning' }}">
                                    {{ ucfirst($loan->member->status ?? 'Unknown') }}
                                </span>
                            </td>
                        </tr>
                    </table>
                    <div class="text-center">
                        <a href="{{ route('admin.members.show', $loan->member_id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user"></i> View Member Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Loan Summary -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Loan Summary</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Principal Amount:</span>
                            <span class="font-weight-bold">UGX {{ number_format($loan->principal_amount, 2) }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Total Repaid:</span>
                            <span class="font-weight-bold text-success">UGX {{ number_format($loan->repayments->sum('amount'), 2) }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Outstanding:</span>
                            <span class="font-weight-bold text-danger">UGX {{ number_format($loan->outstanding_balance ?? $loan->principal_amount, 2) }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Progress:</span>
                            <span class="font-weight-bold">
                                {{ $loan->principal_amount > 0 ? round((($loan->principal_amount - ($loan->outstanding_balance ?? $loan->principal_amount)) / $loan->principal_amount) * 100, 1) : 0 }}%
                            </span>
                        </div>
                        <div class="progress mt-1">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $loan->principal_amount > 0 ? (($loan->principal_amount - ($loan->outstanding_balance ?? $loan->principal_amount)) / $loan->principal_amount) * 100 : 0 }}%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($loan->status == 'active')
                        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#repaymentModal">
                            <i class="bi bi-plus-circle"></i> Add Repayment
                        </button>
                        @endif
                        <a href="{{ route('admin.loans.repayments', $loan->id) }}" class="btn btn-outline-info">
                            <i class="bi bi-list-ul"></i> View Repayments
                        </a>
                        <button type="button" class="btn btn-outline-primary" onclick="printLoan()">
                            <i class="bi bi-printer"></i> Print Details
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Disburse Modal -->
@if($loan->status == 'approved')
<div class="modal fade" id="disburseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Disburse Loan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.loans.disburse', $loan->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="disbursement_date" class="form-label">Disbursement Date *</label>
                        <input type="date" class="form-control" id="disbursement_date" name="disbursement_date" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="disbursement_method" class="form-label">Disbursement Method *</label>
                        <select class="form-select" id="disbursement_method" name="disbursement_method" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="disbursement_reference" class="form-label">Reference Number</label>
                        <input type="text" class="form-control" id="disbursement_reference" name="disbursement_reference">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Disburse Loan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Repayment Modal -->
@if($loan->status == 'active')
<div class="modal fade" id="repaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Repayment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.loans.add-repayment', $loan->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount *</label>
                        <input type="number" class="form-control" id="amount" name="amount" 
                               step="0.01" min="1" max="{{ $loan->outstanding_balance ?? $loan->principal_amount }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Payment Date *</label>
                        <input type="date" class="form-control" id="payment_date" name="payment_date" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method *</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="reference" class="form-label">Reference Number</label>
                        <input type="text" class="form-control" id="reference" name="reference">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Record Repayment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function printLoan() {
    window.print();
}

// Load loan schedule from API
function loadLoanSchedule() {
    fetch(`{{ route('admin.loans.schedule', $loan->id) }}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Loan Schedule:', data.data);
                // You can display the schedule in a modal or update the page
            }
        })
        .catch(error => {
            console.error('Error loading loan schedule:', error);
        });
}

// Load loan history from API
function loadLoanHistory() {
    fetch(`{{ route('admin.loans.history', $loan->id) }}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Loan History:', data.data);
                // You can display the history in a modal or update the page
            }
        })
        .catch(error => {
            console.error('Error loading loan history:', error);
        });
}

// Load loan summary from API
function loadLoanSummary() {
    fetch(`{{ route('admin.loans.summary', $loan->id) }}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Loan Summary:', data.data);
                // You can update the summary section with API data
            }
        })
        .catch(error => {
            console.error('Error loading loan summary:', error);
        });
}

// Load data when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadLoanSchedule();
    loadLoanHistory();
    loadLoanSummary();
});
</script>
@endpush
