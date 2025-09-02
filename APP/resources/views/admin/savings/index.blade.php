@extends('admin.layouts.app')

@section('title', 'Savings Management')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">Savings Management</h1>
                <p class="text-muted">Manage member savings accounts, deposits, and withdrawals</p>
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
                    <div class="stats-number">{{ number_format($stats['total_accounts']) }}</div>
                    <div class="stats-label">Total Accounts</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-piggy-bank"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card text-white" style="background: linear-gradient(135deg, #28a745, #20c997);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number">UGX {{ number_format($stats['total_balance'], 2) }}</div>
                    <div class="stats-label">Total Balance</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card text-white" style="background: linear-gradient(135deg, #fd7e14, #e83e8c);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number">{{ number_format($stats['active_accounts']) }}</div>
                    <div class="stats-label">Active Accounts</div>
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
                    <div class="stats-number">{{ $stats['recent_transactions']->count() }}</div>
                    <div class="stats-label">Recent Transactions</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-arrow-repeat"></i>
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
                        <a href="{{ route('admin.savings.accounts') }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
                            <i class="bi bi-list"></i> View All Accounts
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.savings.transactions') }}" class="btn btn-outline-success btn-sm w-100 mb-2">
                            <i class="bi bi-arrow-repeat"></i> View Transactions
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.savings.products') }}" class="btn btn-outline-info btn-sm w-100 mb-2">
                            <i class="bi bi-gear"></i> Savings Products
                        </a>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-warning btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#manualTransactionModal">
                            <i class="bi bi-plus-circle"></i> Manual Transaction
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Transactions</h5>
            </div>
            <div class="card-body">
                @if($stats['recent_transactions']->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Member</th>
                                <th>Account</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['recent_transactions'] as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <strong>{{ $transaction->account->user->name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $transaction->account->user->member_number ?? 'N/A' }}</small>
                                </td>
                                <td>{{ $transaction->account->account_number }}</td>
                                <td>
                                    <span class="badge bg-{{ $transaction->transaction_type == 'deposit' ? 'success' : 'warning' }}">
                                        {{ ucfirst($transaction->transaction_type) }}
                                    </span>
                                </td>
                                <td>UGX {{ number_format($transaction->amount, 2) }}</td>
                                <td>UGX {{ number_format($transaction->account->balance, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $transaction->status == 'completed' ? 'success' : ($transaction->status == 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.savings.accounts.show', $transaction->account->id) }}" 
                                       class="btn btn-outline-primary btn-sm" 
                                       title="View Account">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('admin.savings.transactions') }}" class="btn btn-primary">
                        View All Transactions
                    </a>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-arrow-repeat display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No recent transactions</h4>
                    <p class="text-muted">Recent savings transactions will appear here.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Manual Transaction Modal -->
<div class="modal fade" id="manualTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manual Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.savings.manual-transaction') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="account_search" class="form-label">Search Account</label>
                        <input type="text" class="form-control" id="account_search" 
                               placeholder="Search by member name, email, or account number">
                        <input type="hidden" id="account_id" name="account_id" required>
                        <div id="account_results" class="mt-2"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="transaction_type" class="form-label">Transaction Type</label>
                        <select class="form-select" id="transaction_type" name="transaction_type" required>
                            <option value="">Select Type</option>
                            <option value="deposit">Deposit</option>
                            <option value="withdrawal">Withdrawal</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount (UGX)</label>
                        <input type="number" class="form-control" id="amount" name="amount" 
                               min="0.01" step="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" 
                                  rows="3" required placeholder="Enter transaction description"></textarea>
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
@endsection

@push('scripts')
<script>
// Account search functionality
$(document).ready(function() {
    $('#account_search').on('input', function() {
        const search = $(this).val();
        if (search.length >= 3) {
            // This would typically call an AJAX endpoint to search accounts
            // For now, just show a placeholder
            $('#account_results').html('<div class="alert alert-info">Search functionality would be implemented here</div>');
        } else {
            $('#account_results').empty();
        }
    });
});
</script>
@endpush