@extends('admin.layouts.app')

@section('title', 'Transaction Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
            <h1 class="h3 mb-0 text-gray-800">Transaction Management</h1>
            <p class="text-muted">Manage and monitor all SACCO transactions</p>
            </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#processTransactionModal">
                <i class="fas fa-plus"></i> Process Transaction
                </button>
            <a href="{{ route('admin.transactions.export', request()->query()) }}" class="btn btn-outline-secondary">
                <i class="fas fa-download"></i> Export
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
                                Total Transactions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_transactions']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exchange-alt fa-2x text-gray-300"></i>
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
                                Total Amount
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                UGX {{ number_format($stats['total_amount']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                {{ $stats['pending_transactions'] }}
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
                                Today's Transactions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $transactions->where('transaction_date', '>=', today())->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
    </div>
                </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Transactions Alert -->
    @if($pendingTransactions->count() > 0)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Attention!</strong> You have {{ $pendingTransactions->count() }} pending transactions that require approval.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
    @endif

<!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Transactions</h6>
        </div>
            <div class="card-body">
            <form method="GET" action="{{ route('admin.transactions.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="reversed" {{ request('status') == 'reversed' ? 'selected' : '' }}>Reversed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="type" class="form-label">Type</label>
                        <select name="type" id="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="deposit" {{ request('type') == 'deposit' ? 'selected' : '' }}>Deposit</option>
                            <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                            <option value="share_purchase" {{ request('type') == 'share_purchase' ? 'selected' : '' }}>Share Purchase</option>
                            <option value="loan_disbursement" {{ request('type') == 'loan_disbursement' ? 'selected' : '' }}>Loan Disbursement</option>
                            <option value="loan_repayment" {{ request('type') == 'loan_repayment' ? 'selected' : '' }}>Loan Repayment</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label for="member_id" class="form-label">Member</label>
                        <select name="member_id" id="member_id" class="form-select">
                            <option value="">All Members</option>
                            @foreach(\App\Models\User::where('role', 'member')->get() as $member)
                                <option value="{{ $member->id }}" {{ request('member_id') == $member->id ? 'selected' : '' }}>
                                    {{ $member->name }} ({{ $member->member_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
    </div>
</div>

    <!-- Transactions Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Transactions</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-bordered" id="transactionsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Transaction #</th>
                            <th>Date</th>
                                <th>Member</th>
                                <th>Type</th>
                                <th>Amount(UGX)</th>
                                <th>Status</th>
                            <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($transactions as $transaction)
                        <tr>
                            <td>
                                <a href="{{ route('admin.transactions.show', $transaction->id) }}" class="text-decoration-none">
                                    {{ $transaction->transaction_number }}
                                </a>
                            </td>
                            <td>{{ $transaction->transaction_date->format('M d, Y H:i') }}</td>
                            <td>
                                <div>
                                    <div class="font-weight-bold">{{ $transaction->member->name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $transaction->member->member_number ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                @php
                                    $badgeClass = 'secondary'; // Default for unknown types
                                    switch ($transaction->type) {
                                        case 'deposit':
                                        case 'repayment': // Loan repayment is also money coming in
                                            $badgeClass = 'success';
                                            break;
                                        case 'withdrawal':
                                            $badgeClass = 'warning';
                                            break;
                                        case 'purchase': // Share purchase
                                            $badgeClass = 'primary';
                                            break;
                                        case 'disburse': // Loan disbursement
                                            $badgeClass = 'info';
                                            break;
                                        default:
                                            $badgeClass = 'secondary';
                                            break;
                                    }
                                @endphp
                                <span class="badge bg-{{ $badgeClass }}">
                                    {{ (is_string($transaction->type) && $transaction->type) ? ucfirst(str_replace('_', ' ', $transaction->type)) : 'N/A' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="font-weight-bold {{ $transaction->type == 'deposit' ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->type == 'deposit' ? '+' : '-' }}{{ number_format($transaction->amount) }}
                                </span>
                            </td>
                            <td>
                                @switch($transaction->status)
                                    @case('pending')
                                        <span class="badge bg-warning">Pending</span>
                                        @break
                                    @case('completed')
                                        <span class="badge bg-success">Completed</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                        @break
                                    @case('reversed')
                                        <span class="badge bg-secondary">Reversed</span>
                                        @break
                                    @default
                                        <span class="badge badge-light">{{ ucfirst($transaction->status) }}</span>
                                @endswitch
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;" title="{{ $transaction->description }}">
                                    {{ $transaction->description }}
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.transactions.show', $transaction->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($transaction->status == 'pending')
                                        <button 
                                            class="btn btn-sm btn-outline-success" 
                                            data-action="approve"
                                            data-transaction-id="{{ $transaction->id }}"
                                            data-transaction-number="{{ $transaction->transaction_number }}"
                                            data-transaction-amount="{{ $transaction->amount }}"
                                            data-transaction-type="{{ $transaction->type }}"
                                            data-transaction-member="{{ $transaction->member->name ?? 'N/A' }}"
                                        >
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button 
                                            class="btn btn-sm btn-outline-danger" 
                                            data-action="reject"
                                            data-transaction-id="{{ $transaction->id }}"
                                            data-transaction-number="{{ $transaction->transaction_number }}"
                                            data-transaction-amount="{{ $transaction->amount }}"
                                            data-transaction-type="{{ $transaction->type }}"
                                            data-transaction-member="{{ $transaction->member->name ?? 'N/A' }}"
                                        >
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                    
                                    @if($transaction->status == 'completed')
                                        <button 
                                            class="btn btn-sm btn-outline-warning" 
                                            data-action="reverse"
                                            data-transaction-id="{{ $transaction->id }}"
                                            data-transaction-number="{{ $transaction->transaction_number }}"
                                            data-transaction-amount="{{ $transaction->amount }}"
                                            data-transaction-type="{{ $transaction->type }}"
                                            data-transaction-member="{{ $transaction->member->name ?? 'N/A' }}"
                                            data-transaction-date="{{ $transaction->created_at->format('M d, Y H:i') }}"
                                            data-transaction-status="{{ $transaction->status }}"
                                        >
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>No transactions found matching your criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} entries
                </div>
                    <div>
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Process Transaction Modal -->
<div class="modal fade" id="processTransactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Process New Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="processTransactionForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="member_id" class="form-label">Member *</label>
                            <select name="member_id" id="member_id" class="form-select" required>
                                <option value="">Select Member</option>
                                @foreach(\App\Models\User::where('role', 'member')->get() as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->member_number }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="type" class="form-label">Transaction Type *</label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="deposit">Deposit</option>
                                <option value="withdrawal">Withdrawal</option>
                                <option value="share_purchase">Share Purchase</option>
                                <option value="loan_disbursement">Loan Disbursement</option>
                                <option value="loan_repayment">Loan Repayment</option>
                                </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="amount" class="form-label">Amount *</label>
                            <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="fee_amount" class="form-label">Fee Amount</label>
                            <input type="number" name="fee_amount" id="fee_amount" class="form-control" step="0.01" min="0">
                        </div>
                            </div>
                    <div class="row mt-3" id="accountRow" style="display: none;">
                        <div class="col-md-12">
                            <label for="account_id" class="form-label">Account</label>
                            <select name="account_id" id="account_id" class="form-select">
                                <option value="">Select Account</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3" id="loanRow" style="display: none;">
                        <div class="col-md-12">
                            <label for="related_loan_id" class="form-label">Loan</label>
                            <select name="related_loan_id" id="related_loan_id" class="form-select">
                                <option value="">Select Loan</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                                <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Process Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Approve Transaction Modal -->
<div class="modal fade" id="approveTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveTransactionForm">
                <div class="modal-body">
                    <input type="hidden" id="approve_transaction_id" name="transaction_id">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Are you sure you want to approve this transaction?
                    </div>
                    <div class="mb-3">
                        <label for="approval_notes" class="form-label">Approval Notes (Optional)</label>
                        <textarea name="notes" id="approval_notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Transaction Modal -->
<div class="modal fade" id="rejectTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectTransactionForm">
                <div class="modal-body">
                    <input type="hidden" id="reject_transaction_id" name="transaction_id">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Are you sure you want to reject this transaction?
                    </div>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Rejection Reason *</label>
                        <textarea name="reason" id="rejection_reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reverse Transaction Modal -->
<div class="modal fade" id="reverseTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reverse Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reverseTransactionForm">
                <div class="modal-body">
                    <input type="hidden" id="reverse_transaction_id" name="transaction_id">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        This action will create a reversal transaction. Are you sure?
                    </div>
                    <div class="mb-3">
                        <label for="reversal_reason" class="form-label">Reversal Reason *</label>
                        <textarea name="reason" id="reversal_reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Reverse Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Transaction type change handler
document.getElementById('type').addEventListener('change', function() {
    const type = this.value;
    const accountRow = document.getElementById('accountRow');
    const loanRow = document.getElementById('loanRow');
    
    if (type === 'deposit' || type === 'withdrawal') {
        accountRow.style.display = 'block';
        loanRow.style.display = 'none';
        loadMemberAccounts();
    } else if (type === 'loan_disbursement' || type === 'loan_repayment') {
        accountRow.style.display = 'none';
        loanRow.style.display = 'block';
        loadMemberLoans();
        } else {
        accountRow.style.display = 'none';
        loanRow.style.display = 'none';
    }
});

// Load member accounts
function loadMemberAccounts() {
    const memberId = document.getElementById('member_id').value;
    const accountSelect = document.getElementById('account_id');
    
    if (!memberId) {
        accountSelect.innerHTML = '<option value="">Select Account</option>';
        return;
    }
    
    fetch(`/admin/api/members/${memberId}/accounts`)
        .then(response => response.json())
        .then(data => {
            accountSelect.innerHTML = '<option value="">Select Account</option>';
            data.accounts.forEach(account => {
                accountSelect.innerHTML += `<option value="${account.id}">${account.account_number} - ${account.savings_product.name}</option>`;
            });
        });
}

// Load member loans
function loadMemberLoans() {
    const memberId = document.getElementById('member_id').value;
    const loanSelect = document.getElementById('related_loan_id');
    
    if (!memberId) {
        loanSelect.innerHTML = '<option value="">Select Loan</option>';
            return;
        }

    fetch(`/admin/api/members/${memberId}/loans`)
        .then(response => response.json())
        .then(data => {
            loanSelect.innerHTML = '<option value="">Select Loan</option>';
            data.loans.forEach(loan => {
                loanSelect.innerHTML += `<option value="${loan.id}">${loan.loan_number} - UGX ${loan.principal_amount.toLocaleString()}</option>`;
            });
        });
}

// Process transaction form
document.getElementById('processTransactionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/admin/transactions/process', {
        method: 'POST',
        body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
});

// Modal functionality is handled by the modal partials
</script>

<!-- Include modals -->
@include('admin.transactions.partials.process-modal')
@include('admin.transactions.partials.approve-modal')
@include('admin.transactions.partials.reject-modal')
@include('admin.transactions.partials.reverse-modal')
@endpush