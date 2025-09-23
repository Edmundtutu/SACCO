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
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-journal-bookmark"></i> General Ledger Entries 
                <span class="badge bg-primary ms-2">{{ $ledgerEntries->total() }} entries</span>
            </h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" onclick="refreshLedger()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
                <button class="btn btn-sm btn-outline-primary" onclick="printLedger()">
                    <i class="bi bi-printer"></i> Print
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($ledgerEntries->count() > 0)
            <div class="table-responsive">
                <table class="table table-hovered" id="ledgerTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-nowrap">Date</th>
                            <th class="text-nowrap">Transaction #</th>
                            <th class="text-nowrap">Account Code</th>
                            <th class="text-nowrap">Account Name</th>
                            <th class="text-nowrap">Account Type</th>
                            <th class="text-end">Debit Amount</th>
                            <th class="text-end">Credit Amount</th>
                            <th class="text-nowrap">Description</th>
                            <th class="text-nowrap">Posted By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ledgerEntries as $entry)
                        <tr>
                            <td>
                                <div>
                                    <strong class="text-nowrap">{{ $entry->posted_at->format('M d, Y') }}</strong><br>
                                    <small class="text-muted">{{ $entry->posted_at->format('H:i') }}</small>
                                </div>
                            </td>
                            <td>
                                @if($entry->transaction)
                                    <strong>
                                        <a href="{{ route('admin.transactions.show', $entry->transaction->id) }}" class="text-decoration-none">
                                            {{ $entry->transaction->transaction_number ?? 'T' . str_pad($entry->transaction->id, 6, '0', STR_PAD_LEFT) }}
                                        </a>
                                    </strong>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-nowrap font-monospace">{{ $entry->account_code }}</strong>
                            </td>
                            <td>
                                <div>
                                    <strong class="text-nowrap">{{ $entry->account_name }}</strong>
                                </div>
                            </td>
                            <td>
                                @switch($entry->account_type)
                                    @case('asset')
                                        <span class="badge-icon bg-primary">
                                            <i class="bi bi-coin me-1"></i>Asset
                                        </span>
                                        @break
                                    @case('liability')
                                        <span class="badge-icon bg-warning">
                                            <i class="bi bi-handshake me-1"></i>Liability
                                        </span>
                                        @break
                                    @case('equity')
                                        <span class="badge-icon bg-info">
                                            <i class="bi bi-shield-check me-1"></i>Equity
                                        </span>
                                        @break
                                    @case('income')
                                        <span class="badge-icon bg-success">
                                            <i class="bi bi-arrow-up-circle me-1"></i>Income
                                        </span>
                                        @break
                                    @case('expense')
                                        <span class="badge-icon bg-danger">
                                            <i class="bi bi-arrow-down-circle me-1"></i>Expense
                                        </span>
                                        @break
                                    @default
                                        <span class="badge-icon bg-secondary">
                                            <i class="bi bi-info-circle me-1"></i>{{ ucfirst($entry->account_type) }}
                                        </span>
                                @endswitch
                            </td>
                            <td class="text-end">
                                @if($entry->debit_amount > 0)
                                    <strong class="text-nowrap text-danger">UGX {{ number_format($entry->debit_amount, 2) }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($entry->credit_amount > 0)
                                    <strong class="text-nowrap text-success">UGX {{ number_format($entry->credit_amount, 2) }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;" title="{{ $entry->description }}">
                                    {{ $entry->description ?? 'No description' }}
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong class="text-nowrap">{{ $entry->posted_by ?? 'System' }}</strong>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bi bi-journal-bookmark display-1 text-muted"></i>
                                <h4 class="text-muted mt-3">No ledger entries found</h4>
                                <p class="text-muted">Try adjusting your search criteria.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="font-weight-bold">
                            <td colspan="5">
                                <i class="bi bi-calculator me-2"></i>Total
                            </td>
                            <td class="text-end">
                                <strong class="text-danger">UGX {{ number_format($ledgerEntries->sum('debit_amount'), 2) }}</strong>
                            </td>
                            <td class="text-end">
                                <strong class="text-success">UGX {{ number_format($ledgerEntries->sum('credit_amount'), 2) }}</strong>
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                <div class="pagination-info">
                    <small>
                        <i class="bi bi-info-circle me-1"></i>
                        Showing {{ $ledgerEntries->firstItem() }} to {{ $ledgerEntries->lastItem() }} of {{ $ledgerEntries->total() }} results
                    </small>
                </div>
                <div>
                    {{ $ledgerEntries->appends(request()->query())->links('pagination.bootstrap-5') }}
                </div>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-journal-bookmark display-1 text-muted"></i>
                <h4 class="text-muted mt-3">No ledger entries found</h4>
                <p class="text-muted">Try adjusting your search criteria.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Account Summary -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-pie-chart"></i> Account Type Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
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
                                        @switch($type)
                                            @case('asset')
                                                <span class="badge-icon bg-primary">
                                                    <i class="bi bi-coin me-1"></i>Asset
                                                </span>
                                                @break
                                            @case('liability')
                                                <span class="badge-icon bg-warning">
                                                    <i class="bi bi-handshake me-1"></i>Liability
                                                </span>
                                                @break
                                            @case('equity')
                                                <span class="badge-icon bg-info">
                                                    <i class="bi bi-shield-check me-1"></i>Equity
                                                </span>
                                                @break
                                            @case('income')
                                                <span class="badge-icon bg-success">
                                                    <i class="bi bi-arrow-up-circle me-1"></i>Income
                                                </span>
                                                @break
                                            @case('expense')
                                                <span class="badge-icon bg-danger">
                                                    <i class="bi bi-arrow-down-circle me-1"></i>Expense
                                                </span>
                                                @break
                                            @default
                                                <span class="badge-icon bg-secondary">
                                                    <i class="bi bi-info-circle me-1"></i>{{ ucfirst($type) }}
                                                </span>
                                        @endswitch
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-danger">UGX {{ number_format($summary['debits'], 2) }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">UGX {{ number_format($summary['credits'], 2) }}</strong>
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
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-shield-check"></i> Balance Verification
                    </h6>
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
                                <i class="bi bi-check-circle-fill fa-2x mb-2"></i>
                                <h5>Ledger is Balanced</h5>
                                <p class="mb-0">Total debits equal total credits</p>
                            </div>
                        @else
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle-fill fa-2x mb-2"></i>
                                <h5>Ledger is Out of Balance</h5>
                                <p class="mb-0">Difference: UGX {{ number_format(abs($totalDebits - $totalCredits), 2) }}</p>
                            </div>
                        @endif
                        
                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="text-center">
                                    <h6 class="text-danger">Total Debits</h6>
                                    <h4 class="font-weight-bold text-danger">
                                        UGX {{ number_format($totalDebits, 2) }}
                                    </h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h6 class="text-success">Total Credits</h6>
                                    <h4 class="font-weight-bold text-success">
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

function refreshLedger() {
    location.reload();
}

function printLedger() {
    window.print();
}

// Initialize DataTable if entries exist
@if($ledgerEntries->count() > 0)
$(document).ready(function() {
    $('#ledgerTable').DataTable({
        "pageLength": 25,
        "order": [[ 0, "desc" ]],
        "columnDefs": [
            { "orderable": false, "targets": [7, 8] }
        ],
        "searchForm":false
    });
});
// @endif
</script>
@endpush

