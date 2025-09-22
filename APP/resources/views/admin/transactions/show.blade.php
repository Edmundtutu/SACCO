@extends('admin.layouts.app')

@section('title', 'Transaction Details')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Transaction Details</h1>
            <p class="text-muted">Transaction #{{ $transaction->transaction_number }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Transactions
            </a>
            @if($transaction->status == 'pending')
                <button 
                    class="btn btn-success" 
                    data-action="approve"
                    data-transaction-id="{{ $transaction->id }}"
                    data-transaction-number="{{ $transaction->transaction_number }}"
                    data-transaction-amount="{{ $transaction->amount }}"
                    data-transaction-type="{{ $transaction->type }}"
                    data-transaction-member="{{ $transaction->member->name ?? 'N/A' }}"
                >
                    <i class="fas fa-check"></i> Approve
                </button>
                <button 
                    class="btn btn-danger" 
                    data-action="reject"
                    data-transaction-id="{{ $transaction->id }}"
                    data-transaction-number="{{ $transaction->transaction_number }}"
                    data-transaction-amount="{{ $transaction->amount }}"
                    data-transaction-type="{{ $transaction->type }}"
                    data-transaction-member="{{ $transaction->member->name ?? 'N/A' }}"
                >
                    <i class="fas fa-times"></i> Reject
                </button>
            @endif
            @if($transaction->status == 'completed')
                <button 
                    class="btn btn-warning" 
                    data-action="reverse"
                    data-transaction-id="{{ $transaction->id }}"
                    data-transaction-number="{{ $transaction->transaction_number }}"
                    data-transaction-amount="{{ $transaction->amount }}"
                    data-transaction-type="{{ $transaction->type }}"
                    data-transaction-member="{{ $transaction->member->name ?? 'N/A' }}"
                    data-transaction-date="{{ $transaction->created_at->format('M d, Y H:i') }}"
                    data-transaction-status="{{ $transaction->status }}"
                >
                    <i class="fas fa-undo"></i> Reverse
                </button>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Transaction Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Transaction Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="font-weight-bold">Transaction Number:</td>
                                    <td>{{ $transaction->transaction_number }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Date:</td>
                                    <td>{{ $transaction->transaction_date->format('M d, Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Value Date:</td>
                                    <td>{{ $transaction->value_date ? $transaction->value_date->format('M d, Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Type:</td>
                                    <td>
                                        <span class="badge badge-{{ $transaction->type == 'deposit' ? 'success' : ($transaction->type == 'withdrawal' ? 'warning' : 'info') }}">
                                            {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Status:</td>
                                    <td>
                                        @switch($transaction->status)
                                            @case('pending')
                                                <span class="badge badge-warning">Pending</span>
                                                @break
                                            @case('completed')
                                                <span class="badge badge-success">Completed</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge badge-danger">Rejected</span>
                                                @break
                                            @case('reversed')
                                                <span class="badge badge-secondary">Reversed</span>
                                                @break
                                            @default
                                                <span class="badge badge-light">{{ ucfirst($transaction->status) }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="font-weight-bold">Amount:</td>
                                    <td class="h5 {{ $transaction->type == 'deposit' ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->type == 'deposit' ? '+' : '-' }}UGX {{ number_format($transaction->amount, 2) }}
                                    </td>
                                </tr>
                                @if($transaction->fee_amount > 0)
                                <tr>
                                    <td class="font-weight-bold">Fee Amount:</td>
                                    <td>UGX {{ number_format($transaction->fee_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="font-weight-bold">Net Amount:</td>
                                    <td class="font-weight-bold">UGX {{ number_format($transaction->net_amount, 2) }}</td>
                                </tr>
                                @if($transaction->balance_before)
                                <tr>
                                    <td class="font-weight-bold">Balance Before:</td>
                                    <td>UGX {{ number_format($transaction->balance_before, 2) }}</td>
                                </tr>
                                @endif
                                @if($transaction->balance_after)
                                <tr>
                                    <td class="font-weight-bold">Balance After:</td>
                                    <td>UGX {{ number_format($transaction->balance_after, 2) }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    
                    @if($transaction->description)
                    <div class="mt-4">
                        <h6 class="font-weight-bold">Description:</h6>
                        <p class="text-muted">{{ $transaction->description }}</p>
                    </div>
                    @endif

                    @if($transaction->metadata)
                    <div class="mt-4">
                        <h6 class="font-weight-bold">Additional Information:</h6>
                        <div class="bg-light p-3 rounded">
                            <pre class="mb-0">{{ json_encode($transaction->metadata, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- General Ledger Entries -->
            @if($transaction->generalLedgerEntries->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">General Ledger Entries</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Account Code</th>
                                    <th>Account Name</th>
                                    <th>Account Type</th>
                                    <th class="text-end">Debit Amount</th>
                                    <th class="text-end">Credit Amount</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transaction->generalLedgerEntries as $entry)
                                <tr>
                                    <td>{{ $entry->account_code }}</td>
                                    <td>{{ $entry->account_name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $entry->account_type == 'asset' ? 'primary' : ($entry->account_type == 'liability' ? 'warning' : 'info') }}">
                                            {{ ucfirst($entry->account_type) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if($entry->debit_amount > 0)
                                            UGX {{ number_format($entry->debit_amount, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($entry->credit_amount > 0)
                                            UGX {{ number_format($entry->credit_amount, 2) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $entry->description }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="font-weight-bold">
                                    <td colspan="3">Total</td>
                                    <td class="text-end">UGX {{ number_format($transaction->generalLedgerEntries->sum('debit_amount'), 2) }}</td>
                                    <td class="text-end">UGX {{ number_format($transaction->generalLedgerEntries->sum('credit_amount'), 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar Information -->
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
                            <td>{{ $transaction->member->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Member #:</td>
                            <td>{{ $transaction->member->member_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Email:</td>
                            <td>{{ $transaction->member->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Phone:</td>
                            <td>{{ $transaction->member->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Status:</td>
                            <td>
                                <span class="badge badge-{{ $transaction->member->status == 'active' ? 'success' : 'warning' }}">
                                    {{ ucfirst($transaction->member->status ?? 'Unknown') }}
                                </span>
                            </td>
                        </tr>
                    </table>
                    <div class="text-center">
                        <a href="{{ route('admin.members.show', $transaction->member_id) }}" class="btn btn-outline-primary btn-sm">
                            View Member Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            @if($transaction->account)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Account Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="font-weight-bold">Account #:</td>
                            <td>{{ $transaction->account->account_number }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Product:</td>
                            <td>{{ $transaction->account->savingsProduct->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Current Balance:</td>
                            <td class="font-weight-bold">UGX {{ number_format($transaction->account->balance, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Available Balance:</td>
                            <td>UGX {{ number_format($transaction->account->available_balance, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Status:</td>
                            <td>
                                <span class="badge badge-{{ $transaction->account->status == 'active' ? 'success' : 'warning' }}">
                                    {{ ucfirst($transaction->account->status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif

            <!-- Loan Information -->
            @if($transaction->relatedLoan)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Loan Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="font-weight-bold">Loan #:</td>
                            <td>{{ $transaction->relatedLoan->loan_number }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Product:</td>
                            <td>{{ $transaction->relatedLoan->loanProduct->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Principal:</td>
                            <td>UGX {{ number_format($transaction->relatedLoan->principal_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Outstanding:</td>
                            <td class="font-weight-bold">UGX {{ number_format($transaction->relatedLoan->outstanding_balance, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Status:</td>
                            <td>
                                <span class="badge badge-{{ $transaction->relatedLoan->status == 'active' ? 'success' : 'warning' }}">
                                    {{ ucfirst($transaction->relatedLoan->status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                    <div class="text-center">
                        <a href="{{ route('admin.loans.show', $transaction->related_loan_id) }}" class="btn btn-outline-primary btn-sm">
                            View Loan Details
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Processing Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Processing Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        @if($transaction->processedBy)
                        <tr>
                            <td class="font-weight-bold">Processed By:</td>
                            <td>{{ $transaction->processedBy->name }}</td>
                        </tr>
                        @endif
                        @if($transaction->approved_at)
                        <tr>
                            <td class="font-weight-bold">Approved At:</td>
                            <td>{{ $transaction->approved_at->format('M d, Y H:i:s') }}</td>
                        </tr>
                        @endif
                        @if($transaction->rejected_at)
                        <tr>
                            <td class="font-weight-bold">Rejected At:</td>
                            <td>{{ $transaction->rejected_at->format('M d, Y H:i:s') }}</td>
                        </tr>
                        @endif
                        @if($transaction->reversed_at)
                        <tr>
                            <td class="font-weight-bold">Reversed At:</td>
                            <td>{{ $transaction->reversed_at->format('M d, Y H:i:s') }}</td>
                        </tr>
                        @endif
                        @if($transaction->reversal_reason)
                        <tr>
                            <td class="font-weight-bold">Reversal Reason:</td>
                            <td>{{ $transaction->reversal_reason }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include the same modals from index page -->
@include('admin.transactions.partials.approve-modal')
@include('admin.transactions.partials.reject-modal')
@include('admin.transactions.partials.reverse-modal')
@endsection

@push('scripts')
<script>
// Debug: Check if transaction data is available
console.log('Transaction data:', {
    id: {{ $transaction->id }},
    number: '{{ $transaction->transaction_number }}',
    amount: {{ $transaction->amount }},
    type: '{{ $transaction->type }}',
    member: '{{ $transaction->member->name ?? 'N/A' }}'
});

// Modal functionality is handled by the modal partials

// Form submission handlers (same as index page)
document.getElementById('approveTransactionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const transactionId = document.getElementById('approve_transaction_id').value;
    const formData = new FormData(this);
    
    fetch(`/admin/transactions/${transactionId}/approve`, {
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

document.getElementById('rejectTransactionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const transactionId = document.getElementById('reject_transaction_id').value;
    const formData = new FormData(this);
    
    fetch(`/admin/transactions/${transactionId}/reject`, {
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

document.getElementById('reverseTransactionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const transactionId = document.getElementById('reverse_transaction_id').value;
    const formData = new FormData(this);
    
    fetch(`/admin/transactions/${transactionId}/reverse`, {
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
</script>
@endpush
