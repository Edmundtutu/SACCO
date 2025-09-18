@extends('admin.layouts.app')

@section('title', 'Transaction Management')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">Transaction Management</h1>
                <p class="text-muted">Monitor, approve, and manage all SACCO transactions</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manualTransactionModal">
                    <i class="bi bi-plus-circle"></i> Manual Transaction
                </button>
                <button class="btn btn-outline-primary" onclick="adminTransactionManager.loadTransactionData()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
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
                    <div class="stats-number" id="totalTransactions">-</div>
                    <div class="stats-label">Total Transactions</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card text-white" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number" id="pendingTransactions">-</div>
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
                    <div class="stats-number" id="completedTransactions">-</div>
                    <div class="stats-label">Completed Today</div>
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
                    <div class="stats-number" id="totalVolume">-</div>
                    <div class="stats-label">Volume Today</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form id="transactionFilters" class="row g-3">
                    <div class="col-md-3">
                        <label for="transaction_type" class="form-label">Transaction Type</label>
                        <select class="form-select" id="transaction_type" name="type">
                            <option value="">All Types</option>
                            <option value="deposit">Deposit</option>
                            <option value="withdrawal">Withdrawal</option>
                            <option value="share_purchase">Share Purchase</option>
                            <option value="loan_disbursement">Loan Disbursement</option>
                            <option value="loan_repayment">Loan Repayment</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="transaction_status" class="form-label">Status</label>
                        <select class="form-select" id="transaction_status" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="reversed">Reversed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="start_date">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="end_date">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">
                            <i class="bi bi-x-circle"></i> Clear
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Pending Transactions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-clock"></i> Pending Transactions
                    <span class="badge bg-warning ms-2" id="pendingCount">0</span>
                </h5>
            </div>
            <div class="card-body">
                <div id="pendingTransactions" class="pending-transactions">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2">Loading pending transactions...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transaction History -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul"></i> Transaction History
                    <span class="badge bg-primary ms-2" id="totalCount">0</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="transactionsTable" class="table table-hover transaction-table">
                        <thead>
                            <tr>
                                <th>Transaction #</th>
                                <th>Member</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="text-muted mt-2">Loading transactions...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <small class="text-muted">
                            Showing <span id="showingStart">0</span> to <span id="showingEnd">0</span> of <span id="totalResults">0</span> results
                        </small>
                    </div>
                    <div id="paginationContainer">
                        <!-- Pagination will be inserted here -->
                    </div>
                </div>
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
                                    <option value="share_purchase">Share Purchase</option>
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize filters with default values
    const today = new Date().toISOString().split('T')[0];
    $('#date_from').val(today);
    $('#date_to').val(today);
    
    // Load initial data
    adminTransactionManager.loadTransactionData();
    
    // Handle filter form submission
    $('#transactionFilters').on('submit', function(e) {
        e.preventDefault();
        adminTransactionManager.loadTransactionData();
    });
    
    // Member search functionality (reuse from savings page)
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
});

function clearFilters() {
    $('#transactionFilters')[0].reset();
    const today = new Date().toISOString().split('T')[0];
    $('#date_from').val(today);
    $('#date_to').val(today);
    adminTransactionManager.loadTransactionData();
}

// Update statistics display
function updateStatistics(stats) {
    $('#totalTransactions').text(stats.total || 0);
    $('#pendingTransactions').text(stats.pending || 0);
    $('#completedTransactions').text(stats.completed || 0);
    $('#totalVolume').text('UGX ' + (stats.volume || 0).toLocaleString());
}
</script>
@endpush
