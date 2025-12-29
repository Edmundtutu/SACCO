@extends('admin.layouts.app')

@section('title', 'Savings Transactions')

@push('styles')
<style>
    .transactions-header {
        background: linear-gradient(135deg, #2980b9 0%, #1a3a6e 100%);
        border-radius: 15px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }

    .filter-panel {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: transform 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-3px);
    }

    .stat-card .icon {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }

    .stat-card .value {
        font-size: 1.8rem;
        font-weight: bold;
        margin-bottom: 0.25rem;
    }

    .stat-card .label {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .transaction-row {
        background: white;
        border-radius: 4px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border-left: 2px solid;
        transition: all 0.3s;
    }

    .transaction-row:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateX(5px);
    }

    .transaction-row.deposit {
        border-left-color: #28a745;
    }

    .transaction-row.withdrawal {
        border-left-color: #dc3545;
    }

    .transaction-row.transfer {
        border-left-color: #17a2b8;
    }

    .transaction-row.loan_disbursement {
        border-left-color: #6f42c1;
    }

    .transaction-row.loan_repayment {
        border-left-color: #fd7e14;
    }

    .filter-badge-active {
        background: #667eea;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        margin: 0.25rem;
        display: inline-block;
    }

    .amount-positive {
        color: #28a745;
        font-weight: bold;
    }

    .amount-negative {
        color: #dc3545;
        font-weight: bold;
    }

    .member-info {
        display: flex;
        align-items: center;
    }

    .member-avatar-sm {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2980b9, #1a3a6e);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 1rem;
        margin-right: 0.75rem;
    }

    .export-buttons .btn {
        margin: 0.25rem;
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="transactions-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="mb-2">
                <i class="bi bi-arrow-repeat"></i> Savings Transactions
            </h1>
            <p class="mb-0 opacity-75">View and manage all savings account transactions</p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="export-buttons">
                <button class="btn btn-light btn-sm">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                </button>
                <button class="btn btn-light btn-sm">
                    <i class="bi bi-file-earmark-pdf"></i> Export PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="stats-row">
    <div class="stat-card">
        <div class="icon text-primary">
            <i class="bi bi-list-ul"></i>
        </div>
        <div class="value">{{ $transactions->total() }}</div>
        <div class="label">Total Transactions</div>
    </div>
    <div class="stat-card">
        <div class="icon text-success">
            <i class="bi bi-arrow-down-circle"></i>
        </div>
        <div class="value">{{ $transactions->where('type', 'deposit')->count() }}</div>
        <div class="label">Deposits</div>
    </div>
    <div class="stat-card">
        <div class="icon text-danger">
            <i class="bi bi-arrow-up-circle"></i>
        </div>
        <div class="value">{{ $transactions->where('type', 'withdrawal')->count() }}</div>
        <div class="label">Withdrawals</div>
    </div>
    <div class="stat-card">
        <div class="icon text-warning">
            <i class="bi bi-cash-stack"></i>
        </div>
        <div class="value">UGX {{ number_format($transactions->sum('amount'), 0) }}</div>
        <div class="label">Total Volume</div>
    </div>
</div>

<!-- Filter Panel -->
<div class="filter-panel">
    <form action="{{ route('admin.savings.transactions') }}" method="GET" id="filterForm">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="bi bi-calendar-range"></i> Date From
                </label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="bi bi-calendar-range"></i> Date To
                </label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="bi bi-tag"></i> Transaction Type
                </label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="deposit" {{ request('type') == 'deposit' ? 'selected' : '' }}>Deposit</option>
                    <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                    <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="loan_disbursement" {{ request('type') == 'loan_disbursement' ? 'selected' : '' }}>Loan Disbursement</option>
                    <option value="loan_repayment" {{ request('type') == 'loan_repayment' ? 'selected' : '' }}>Loan Repayment</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">
                    <i class="bi bi-check-circle"></i> Status
                </label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="reversed" {{ request('status') == 'reversed' ? 'selected' : '' }}>Reversed</option>
                </select>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Apply Filters
                </button>
                <a href="{{ route('admin.savings.transactions') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </a>
            </div>
        </div>

        @if(request()->hasAny(['date_from', 'date_to', 'type', 'status']))
        <div class="mt-3">
            <strong>Active Filters:</strong>
            @if(request('date_from'))
                <span class="filter-badge-active">
                    From: {{ request('date_from') }}
                    <a href="{{ route('admin.savings.transactions', array_merge(request()->except('date_from'))) }}" class="text-white ms-2">×</a>
                </span>
            @endif
            @if(request('date_to'))
                <span class="filter-badge-active">
                    To: {{ request('date_to') }}
                    <a href="{{ route('admin.savings.transactions', array_merge(request()->except('date_to'))) }}" class="text-white ms-2">×</a>
                </span>
            @endif
            @if(request('type'))
                <span class="filter-badge-active">
                    Type: {{ ucfirst(str_replace('_', ' ', request('type'))) }}
                    <a href="{{ route('admin.savings.transactions', array_merge(request()->except('type'))) }}" class="text-white ms-2">×</a>
                </span>
            @endif
            @if(request('status'))
                <span class="filter-badge-active">
                    Status: {{ ucfirst(request('status')) }}
                    <a href="{{ route('admin.savings.transactions', array_merge(request()->except('status'))) }}" class="text-white ms-2">×</a>
                </span>
            @endif
        </div>
        @endif
    </form>
</div>

@if($transactions->isEmpty())
<!-- Empty State -->
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-inbox display-1 text-muted"></i>
        <h4 class="text-muted mt-3">No Transactions Found</h4>
        <p class="text-muted">
            @if(request()->hasAny(['date_from', 'date_to', 'type', 'status']))
                Try adjusting your filters to see more results.
            @else
                There are no transactions in the system yet.
            @endif
        </p>
    </div>
</div>
@else

<!-- Transactions List -->
<div class="row">
    <div class="col-12">
        @foreach($transactions as $transaction)
        <div class="transaction-row {{ $transaction->type }}">
            <div class="row align-items-center">
                <!-- Member Info -->
                <div class="col-md-4">
                    <div class="member-info">
                        <div class="member-avatar-sm">
                            {{ strtoupper(substr($transaction->account->member->name ?? 'N', 0, 1)) }}
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $transaction->account->member->name ?? 'N/A' }}</h6>
                            <small class="text-muted">
                                <i class="bi bi-credit-card"></i> {{ $transaction->account->account_number }}
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Transaction Details -->
                <div class="col-md-3">
                    <div>
                        <strong>{{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</strong>
                        <p class="text-muted small mb-0">{{ $transaction->description ?? 'No description' }}</p>
                        <small class="text-muted">
                            <i class="bi bi-calendar"></i> {{ $transaction->created_at->format('M d, Y H:i') }}
                        </small>
                    </div>
                </div>

                <!-- Amount -->
                <div class="col-md-2 text-center">
                    <h5 class="mb-0 {{ in_array($transaction->type, ['deposit', 'loan_disbursement']) ? 'amount-positive' : 'amount-negative' }}">
                        {{ in_array($transaction->type, ['deposit', 'loan_disbursement']) ? '+' : '-' }}UGX {{ number_format($transaction->amount, 0) }}
                    </h5>
                </div>

                <!-- Status -->
                <div class="col-md-2 text-center">
                    <span class="badge bg-{{ $transaction->status == 'completed' ? 'success' : ($transaction->status == 'pending' ? 'warning' : ($transaction->status == 'failed' ? 'danger' : 'secondary')) }} px-3 py-2">
                        {{ ucfirst($transaction->status) }}
                    </span>
                </div>

                <!-- Actions -->
                <div class="col-md-1 text-end">
                    <a href="{{ route('admin.savings.accounts.show', $transaction->account->id) }}" 
                       class="btn btn-outline-primary btn-sm" 
                       title="View Account">
                        <i class="bi bi-eye"></i>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
    <div class="pagination-info">
        <small class="text-muted">
            <i class="fas fa-info-circle me-1"></i>
            Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} results
        </small>
    </div>
    <div>
        {{ $transactions->appends(request()->query())->links('pagination.bootstrap-5') }}
    </div>
</div>

<!-- Summary Card -->
<div class="card mt-4">
    <div class="card-body">
        <h5 class="mb-3"><i class="bi bi-bar-chart"></i> Transaction Summary</h5>
        <div class="row">
            <div class="col-md-4">
                <div class="text-center p-3 border-end">
                    <h4 class="text-success mb-2">
                        UGX {{ number_format($transactions->whereIn('type', ['deposit', 'loan_disbursement'])->sum('amount'), 0) }}
                    </h4>
                    <p class="text-muted mb-0">Total Credits</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-3 border-end">
                    <h4 class="text-danger mb-2">
                        UGX {{ number_format($transactions->whereIn('type', ['withdrawal', 'loan_repayment', 'transfer'])->sum('amount'), 0) }}
                    </h4>
                    <p class="text-muted mb-0">Total Debits</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-3">
                    <h4 class="text-primary mb-2">
                        UGX {{ number_format(
                            $transactions->whereIn('type', ['deposit', 'loan_disbursement'])->sum('amount') - 
                            $transactions->whereIn('type', ['withdrawal', 'loan_repayment', 'transfer'])->sum('amount'), 
                            0
                        ) }}
                    </h4>
                    <p class="text-muted mb-0">Net Flow</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form when filters change (optional)
    $('#filterForm select').on('change', function() {
        // Uncomment to auto-submit
        // $('#filterForm').submit();
    });

    // Date range validation
    $('input[name="date_to"]').on('change', function() {
        const dateFrom = $('input[name="date_from"]').val();
        const dateTo = $(this).val();
        
        if (dateFrom && dateTo && dateTo < dateFrom) {
            alert('End date cannot be before start date');
            $(this).val('');
        }
    });
});
</script>
@endpush
