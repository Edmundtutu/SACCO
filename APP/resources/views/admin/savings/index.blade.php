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
                                    @switch($transaction->type)
                                        @case('deposit')
                                            <span class="badge-icon bg-success">Deposit</span>
                                            @break
                                        @case('withdrawal')
                                            <span class="badge-icon bg-warning">Withdrawal</span>
                                            @break
                                        @case('share_purchase')
                                            <span class="badge-icon bg-info">Share Purchase</span>
                                            @break
                                        @case('loan_disbursement')
                                            <span class="badge-icon bg-primary">Loan Disbursement</span>
                                            @break
                                        @case('loan_repayment')
                                            <span class="badge-icon bg-secondary">Loan Repayment</span>
                                            @break
                                        @default
                                            <span class="badge-icon bg-light">{{ ucfirst($transaction->type) }}</span>
                                    @endswitch
                                </td>
                                <td>UGX {{ number_format($transaction->amount, 2) }}</td>
                                <td>UGX {{ number_format($transaction->account->balance, 2) }}</td>
                                <td>
                                    @switch($transaction->status)
                                        @case('completed')
                                            <span class="badge-status completed">Completed</span>
                                            @break
                                        @case('pending')
                                            <span class="badge-status pending">Pending</span>
                                            @break
                                        @case('failed')
                                            <span class="badge-status failed">Failed</span>
                                            @break
                                        @case('reversed')
                                            <span class="badge-status reversed">Reversed</span>
                                            @break
                                        @default
                                            <span class="badge-status default">{{ ucfirst($transaction->status) }}</span>
                                    @endswitch
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manual Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="manualTransactionForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="member_search" class="form-label">Search Member</label>
                                <input type="text" class="form-control" id="member_search" 
                                       placeholder="Search by member name, email, or member number">
                                <input type="hidden" id="member_id" name="member_id" required>
                                <div id="member_results" class="search-results" style="display: none;"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_search" class="form-label">Account</label>
                                <select class="form-select" id="account_id" name="account_id" required disabled>
                                    <option value="">Select account after choosing member</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="transaction_type" class="form-label">Transaction Type</label>
                                <select class="form-select" id="transaction_type" name="transaction_type" required>
                                    <option value="">Select Type</option>
                                    <option value="deposit">Deposit</option>
                                    <option value="withdrawal">Withdrawal</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount (UGX)</label>
                                <input type="number" class="form-control" id="amount" name="amount" 
                                       min="0.01" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_reference" class="form-label">Payment Reference</label>
                                <input type="text" class="form-control" id="payment_reference" name="payment_reference" 
                                       placeholder="Optional payment reference">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="2" required placeholder="Enter transaction description"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Process Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Transaction Management Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-gear"></i> Transaction Management
                    <button class="btn btn-sm btn-outline-primary float-end" onclick="adminTransactionManager.loadTransactionData()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </h5>
            </div>
            <div class="card-body">
                <!-- Pending Transactions -->
                <div class="mb-4">
                    <h6 class="text-muted mb-3">Pending Transactions</h6>
                    <div id="pendingTransactions" class="pending-transactions">
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mt-2">Loading pending transactions...</p>
                        </div>
                    </div>
                </div>

                <!-- Transaction History -->
                <div class="table-responsive">
                    <table id="transactionsTable" class="table table-hover transaction-table">
                        <thead>
                            <tr>
                                <th>Transaction #</th>
                                <th>Member</th>
                                <th>Account</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="text-muted mt-2">Loading transactions...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Member search functionality
    let searchTimeout;
    $('#member_search').on('input', function() {
        const search = $(this).val();
        
        clearTimeout(searchTimeout);
        
        if (search.length >= 3) {
            searchTimeout = setTimeout(() => {
                searchMembers(search);
            }, 300);
        } else {
            $('#member_results').hide().empty();
            $('#member_id').val('');
            $('#account_id').prop('disabled', true).html('<option value="">Select account after choosing member</option>');
        }
    });

    // Hide search results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#member_search, #member_results').length) {
            $('#member_results').hide();
        }
    });

    async function searchMembers(query) {
        try {
            const response = await fetch(`/api/members/search?q=${encodeURIComponent(query)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                displayMemberResults(data.members || []);
            } else {
                $('#member_results').html('<div class="alert alert-warning">No members found</div>').show();
            }
        } catch (error) {
            console.error('Search error:', error);
            $('#member_results').html('<div class="alert alert-danger">Search failed</div>').show();
        }
    }

    function displayMemberResults(members) {
        const resultsContainer = $('#member_results');
        
        if (members.length === 0) {
            resultsContainer.html('<div class="alert alert-warning">No members found</div>').show();
            return;
        }

        const resultsHtml = members.map(member => `
            <div class="search-result-item" data-member-id="${member.id}" data-member-name="${member.name}">
                <div class="d-flex justify-content-between">
                    <div>
                        <strong>${member.name}</strong><br>
                        <small class="text-muted">${member.member_number || member.email}</small>
                    </div>
                    <small class="text-muted">${member.accounts?.length || 0} accounts</small>
                </div>
            </div>
        `).join('');

        resultsContainer.html(resultsHtml).show();

        // Handle member selection
        resultsContainer.on('click', '.search-result-item', function() {
            const memberId = $(this).data('member-id');
            const memberName = $(this).data('member-name');
            
            $('#member_id').val(memberId);
            $('#member_search').val(memberName);
            $('#member_results').hide();
            
            // Load member accounts
            loadMemberAccounts(memberId);
        });
    }

    async function loadMemberAccounts(memberId) {
        try {
            const response = await fetch(`/api/members/${memberId}/accounts`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                const accounts = data.accounts || [];
                
                const accountOptions = accounts.map(account => 
                    `<option value="${account.id}">${account.account_number} - ${account.account_type} (UGX ${parseFloat(account.balance).toLocaleString()})</option>`
                ).join('');
                
                $('#account_id').html('<option value="">Select account</option>' + accountOptions).prop('disabled', false);
            } else {
                $('#account_id').html('<option value="">No accounts found</option>').prop('disabled', false);
            }
        } catch (error) {
            console.error('Error loading accounts:', error);
            $('#account_id').html('<option value="">Error loading accounts</option>').prop('disabled', false);
        }
    }

    // Transaction type change handler
    $('#transaction_type').on('change', function() {
        const type = $(this).val();
        const amountInput = $('#amount');
        
        if (type === 'withdrawal') {
            amountInput.attr('max', ''); // Remove max limit for now
        } else {
            amountInput.removeAttr('max');
        }
    });

    // Form validation
    $('#manualTransactionForm').on('submit', function(e) {
        const memberId = $('#member_id').val();
        const accountId = $('#account_id').val();
        const type = $('#transaction_type').val();
        const amount = $('#amount').val();
        
        if (!memberId || !accountId || !type || !amount) {
            e.preventDefault();
            alert('Please fill in all required fields');
            return false;
        }
        
        if (parseFloat(amount) <= 0) {
            e.preventDefault();
            alert('Amount must be greater than 0');
            return false;
        }
    });
});
</script>
@endpush