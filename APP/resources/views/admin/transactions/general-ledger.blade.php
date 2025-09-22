@extends('admin.layouts.app')

@section('title', 'General Ledger')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">General Ledger</h1>
            <p class="text-muted">Double-entry bookkeeping entries for all transactions</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.transactions.trial-balance') }}" class="btn btn-outline-primary">
                <i class="fas fa-balance-scale"></i> Trial Balance
            </a>
            <button class="btn btn-outline-secondary" onclick="exportLedger()">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Ledger Entries</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.transactions.general-ledger') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label for="account_code" class="form-label">Account</label>
                        <select name="account_code" id="account_code" class="form-select">
                            <option value="">All Accounts</option>
                            @foreach($chartOfAccounts as $account)
                                <option value="{{ $account->account_code }}" {{ request('account_code') == $account->account_code ? 'selected' : '' }}>
                                    {{ $account->account_code }} - {{ $account->account_name }}
                                </option>
                            @endforeach
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
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.transactions.general-ledger') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Ledger Entries Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">General Ledger Entries</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="ledgerTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Transaction #</th>
                            <th>Account Code</th>
                            <th>Account Name</th>
                            <th>Account Type</th>
                            <th class="text-end">Debit Amount</th>
                            <th class="text-end">Credit Amount</th>
                            <th>Description</th>
                            <th>Posted By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ledgerEntries as $entry)
                        <tr>
                            <td>{{ $entry->posted_at->format('M d, Y') }}</td>
                            <td>
                                @if($entry->transaction)
                                    <a href="{{ route('admin.transactions.show', $entry->transaction->id) }}" class="text-decoration-none">
                                        {{ $entry->transaction->transaction_number }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="font-monospace">{{ $entry->account_code }}</span>
                            </td>
                            <td>{{ $entry->account_name }}</td>
                            <td>
                                <span class="badge badge-{{ $entry->account_type == 'asset' ? 'primary' : ($entry->account_type == 'liability' ? 'warning' : ($entry->account_type == 'equity' ? 'success' : ($entry->account_type == 'income' ? 'info' : 'secondary'))) }}">
                                    {{ ucfirst($entry->account_type) }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if($entry->debit_amount > 0)
                                    <span class="text-success font-weight-bold">
                                        UGX {{ number_format($entry->debit_amount, 2) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($entry->credit_amount > 0)
                                    <span class="text-danger font-weight-bold">
                                        UGX {{ number_format($entry->credit_amount, 2) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;" title="{{ $entry->description }}">
                                    {{ $entry->description }}
                                </div>
                            </td>
                            <td>
                                <small class="text-muted">{{ $entry->posted_by ?? 'System' }}</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>No ledger entries found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold bg-light">
                            <td colspan="5">Total</td>
                            <td class="text-end">
                                <span class="text-success">
                                    UGX {{ number_format($ledgerEntries->sum('debit_amount'), 2) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="text-danger">
                                    UGX {{ number_format($ledgerEntries->sum('credit_amount'), 2) }}
                                </span>
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $ledgerEntries->firstItem() }} to {{ $ledgerEntries->lastItem() }} of {{ $ledgerEntries->total() }} entries
                </div>
                <div>
                    {{ $ledgerEntries->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Account Summary -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Account Type Summary</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Account Type</th>
                                    <th class="text-end">Total Debits</th>
                                    <th class="text-end">Total Credits</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $accountTypeSummary = $ledgerEntries->groupBy('account_type')->map(function($entries) {
                                        return [
                                            'debits' => $entries->sum('debit_amount'),
                                            'credits' => $entries->sum('credit_amount')
                                        ];
                                    });
                                @endphp
                                @foreach($accountTypeSummary as $type => $summary)
                                <tr>
                                    <td>
                                        <span class="badge badge-{{ $type == 'asset' ? 'primary' : ($type == 'liability' ? 'warning' : ($type == 'equity' ? 'success' : ($type == 'income' ? 'info' : 'secondary'))) }}">
                                            {{ ucfirst($type) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-success">
                                            UGX {{ number_format($summary['debits'], 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-danger">
                                            UGX {{ number_format($summary['credits'], 2) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Balance Verification</h6>
                </div>
                <div class="card-body">
                    @php
                        $totalDebits = $ledgerEntries->sum('debit_amount');
                        $totalCredits = $ledgerEntries->sum('credit_amount');
                        $isBalanced = abs($totalDebits - $totalCredits) < 0.01;
                    @endphp
                    
                    <div class="text-center">
                        @if($isBalanced)
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <h5>Ledger is Balanced</h5>
                                <p class="mb-0">Total debits equal total credits</p>
                            </div>
                        @else
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                <h5>Ledger is Out of Balance</h5>
                                <p class="mb-0">Difference: UGX {{ number_format(abs($totalDebits - $totalCredits), 2) }}</p>
                            </div>
                        @endif
                        
                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="text-center">
                                    <h6 class="text-success">Total Debits</h6>
                                    <h4 class="font-weight-bold text-success">
                                        UGX {{ number_format($totalDebits, 2) }}
                                    </h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h6 class="text-danger">Total Credits</h6>
                                    <h4 class="font-weight-bold text-danger">
                                        UGX {{ number_format($totalCredits, 2) }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportLedger() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', '1');
    window.location.href = '{{ route("admin.transactions.general-ledger") }}?' + params.toString();
}

// Initialize DataTable if entries exist
@if($ledgerEntries->count() > 0)
$(document).ready(function() {
    $('#ledgerTable').DataTable({
        "pageLength": 25,
        "order": [[ 0, "desc" ]],
        "columnDefs": [
            { "orderable": false, "targets": [7, 8] }
        ]
    });
});
@endif
</script>
@endpush
