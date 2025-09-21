@extends('admin.layouts.app')

@section('title', 'General Ledger & Trial Balance')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">General Ledger & Trial Balance</h1>
                <p class="text-muted">View and analyze the general ledger entries and trial balance</p>
            </div>
            <div>
                <button class="btn btn-outline-primary" onclick="adminLedgerManager.refreshData()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
                <button class="btn btn-primary" onclick="adminLedgerManager.exportLedger()">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number" id="totalDebits">-</div>
                    <div class="stats-label">Total Debits</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-arrow-down-circle"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card text-white" style="background: linear-gradient(135deg, #28a745, #20c997);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number" id="totalCredits">-</div>
                    <div class="stats-label">Total Credits</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-arrow-up-circle"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card text-white" style="background: linear-gradient(135deg, #6f42c1, #e83e8c);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number" id="ledgerBalance">-</div>
                    <div class="stats-label">Balance</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-scale"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card text-white" style="background: linear-gradient(135deg, #fd7e14, #e83e8c);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number" id="isBalanced">-</div>
                    <div class="stats-label">Status</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-check-circle"></i>
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
                <form id="ledgerFilters" class="row g-3">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date">
                    </div>
                    <div class="col-md-3">
                        <label for="account_code" class="form-label">Account Code</label>
                        <select class="form-select" id="account_code" name="account_code">
                            <option value="">All Accounts</option>
                            <option value="1000">Cash & Bank</option>
                            <option value="2000">Member Deposits</option>
                            <option value="3000">Loans Outstanding</option>
                            <option value="4000">Interest Income</option>
                            <option value="5000">Operating Expenses</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Filter
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="clearLedgerFilters()">
                            <i class="bi bi-x-circle"></i> Clear
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs" id="ledgerTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="general-ledger-tab" data-bs-toggle="tab" data-bs-target="#general-ledger" type="button" role="tab">
            <i class="bi bi-journal-text"></i> General Ledger
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="trial-balance-tab" data-bs-toggle="tab" data-bs-target="#trial-balance" type="button" role="tab">
            <i class="bi bi-scale"></i> Trial Balance
        </button>
    </li>
</ul>

<div class="tab-content" id="ledgerTabContent">
    <!-- General Ledger Tab -->
    <div class="tab-pane fade show active" id="general-ledger" role="tabpanel">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-journal-text"></i> General Ledger Entries
                            <span class="badge bg-primary ms-2" id="ledgerCount">0</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="generalLedgerTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Transaction #</th>
                                        <th>Account Code</th>
                                        <th>Account Name</th>
                                        <th>Description</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="text-muted mt-2">Loading ledger entries...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <small class="text-muted">
                                    Showing <span id="ledgerShowingStart">0</span> to <span id="ledgerShowingEnd">0</span> of <span id="ledgerTotalResults">0</span> results
                                </small>
                            </div>
                            <div id="ledgerPaginationContainer">
                                <!-- Pagination will be inserted here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trial Balance Tab -->
    <div class="tab-pane fade" id="trial-balance" role="tabpanel">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-scale"></i> Trial Balance
                            <span class="badge bg-success ms-2" id="trialBalanceStatus">Balanced</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="trialBalanceTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Account Code</th>
                                        <th>Account Name</th>
                                        <th>Total Debits</th>
                                        <th>Total Credits</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="text-muted mt-2">Loading trial balance...</p>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="table-dark">
                                        <th colspan="2">TOTALS</th>
                                        <th id="trialTotalDebits">0.00</th>
                                        <th id="trialTotalCredits">0.00</th>
                                        <th id="trialBalance">0.00</th>
                                    </tr>
                                </tfoot>
                            </table>
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
class AdminLedgerManager {
    constructor() {
        this.baseUrl = '/api';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadLedgerData();
        this.loadTrialBalance();
    }

    setupEventListeners() {
        // Filter form submission
        document.getElementById('ledgerFilters').addEventListener('submit', (e) => {
            e.preventDefault();
            this.loadLedgerData();
            this.loadTrialBalance();
        });

        // Tab change events
        document.getElementById('trial-balance-tab').addEventListener('click', () => {
            this.loadTrialBalance();
        });
    }

    async loadLedgerData() {
        try {
            const filters = this.getCurrentFilters();
            const response = await this.apiCall('GET', '/transactions/ledger/general', filters);
            
            if (response.success) {
                this.updateGeneralLedgerTable(response.data);
                this.updateLedgerPagination(response.meta);
                this.updateLedgerSummary(response.meta);
            }
        } catch (error) {
            console.error('Error loading ledger data:', error);
            this.showError('Failed to load general ledger data');
        }
    }

    async loadTrialBalance() {
        try {
            const filters = this.getCurrentFilters();
            const response = await this.apiCall('GET', '/transactions/ledger/trial-balance', filters);
            
            if (response.success) {
                this.updateTrialBalanceTable(response.data);
                this.updateTrialBalanceSummary(response.meta);
            }
        } catch (error) {
            console.error('Error loading trial balance:', error);
            this.showError('Failed to load trial balance');
        }
    }

    getCurrentFilters() {
        const form = document.getElementById('ledgerFilters');
        const formData = new FormData(form);
        const filters = {};
        
        for (const [key, value] of formData.entries()) {
            if (value) {
                filters[key] = value;
            }
        }

        return filters;
    }

    updateGeneralLedgerTable(entries) {
        const tableBody = document.querySelector('#generalLedgerTable tbody');
        if (!tableBody) return;

        if (entries.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <p class="text-muted">No ledger entries found</p>
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = entries.map(entry => `
            <tr>
                <td>${this.formatDate(entry.transaction_date)}</td>
                <td>${entry.transaction?.transaction_number || 'N/A'}</td>
                <td><code>${entry.account_code}</code></td>
                <td>${entry.account_name}</td>
                <td>${entry.description}</td>
                <td class="text-end">${entry.debit_amount > 0 ? this.formatCurrency(entry.debit_amount) : '-'}</td>
                <td class="text-end">${entry.credit_amount > 0 ? this.formatCurrency(entry.credit_amount) : '-'}</td>
                <td class="text-end">${this.formatCurrency(entry.balance || 0)}</td>
            </tr>
        `).join('');
    }

    updateTrialBalanceTable(accounts) {
        const tableBody = document.querySelector('#trialBalanceTable tbody');
        if (!tableBody) return;

        if (accounts.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <p class="text-muted">No trial balance data found</p>
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = accounts.map(account => `
            <tr>
                <td><code>${account.account_code}</code></td>
                <td>${account.account_name}</td>
                <td class="text-end">${this.formatCurrency(account.total_debits)}</td>
                <td class="text-end">${this.formatCurrency(account.total_credits)}</td>
                <td class="text-end ${account.balance >= 0 ? 'text-success' : 'text-danger'}">
                    ${this.formatCurrency(account.balance)}
                </td>
            </tr>
        `).join('');
    }

    updateLedgerPagination(meta) {
        const container = document.getElementById('ledgerPaginationContainer');
        if (!container) return;

        const { current_page, last_page, per_page, total } = meta;
        const startItem = (current_page - 1) * per_page + 1;
        const endItem = Math.min(current_page * per_page, total);

        // Update showing text
        document.getElementById('ledgerShowingStart').textContent = startItem;
        document.getElementById('ledgerShowingEnd').textContent = endItem;
        document.getElementById('ledgerTotalResults').textContent = total;
        document.getElementById('ledgerCount').textContent = total;

        // Generate pagination buttons
        let paginationHtml = '<nav><ul class="pagination pagination-sm mb-0">';
        
        // Previous button
        if (current_page > 1) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="adminLedgerManager.loadLedgerPage(${current_page - 1})">Previous</a></li>`;
        }

        // Page numbers
        const startPage = Math.max(1, current_page - 2);
        const endPage = Math.min(last_page, current_page + 2);

        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === current_page ? 'active' : '';
            paginationHtml += `<li class="page-item ${activeClass}"><a class="page-link" href="#" onclick="adminLedgerManager.loadLedgerPage(${i})">${i}</a></li>`;
        }

        // Next button
        if (current_page < last_page) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="adminLedgerManager.loadLedgerPage(${current_page + 1})">Next</a></li>`;
        }

        paginationHtml += '</ul></nav>';
        container.innerHTML = paginationHtml;
    }

    updateLedgerSummary(meta) {
        document.getElementById('totalDebits').textContent = this.formatCurrency(meta.total_debits);
        document.getElementById('totalCredits').textContent = this.formatCurrency(meta.total_credits);
        document.getElementById('ledgerBalance').textContent = this.formatCurrency(meta.balance);
    }

    updateTrialBalanceSummary(meta) {
        document.getElementById('trialTotalDebits').textContent = this.formatCurrency(meta.total_debits);
        document.getElementById('trialTotalCredits').textContent = this.formatCurrency(meta.total_credits);
        document.getElementById('trialBalance').textContent = this.formatCurrency(meta.balance);
        
        const statusBadge = document.getElementById('trialBalanceStatus');
        if (meta.is_balanced) {
            statusBadge.textContent = 'Balanced';
            statusBadge.className = 'badge bg-success ms-2';
        } else {
            statusBadge.textContent = 'Out of Balance';
            statusBadge.className = 'badge bg-danger ms-2';
        }
    }

    async loadLedgerPage(page) {
        const currentFilters = this.getCurrentFilters();
        currentFilters.page = page;
        
        try {
            const response = await this.apiCall('GET', '/transactions/ledger/general', currentFilters);
            if (response.success) {
                this.updateGeneralLedgerTable(response.data);
                this.updateLedgerPagination(response.meta);
            }
        } catch (error) {
            console.error('Error loading ledger page:', error);
        }
    }

    async refreshData() {
        this.loadLedgerData();
        this.loadTrialBalance();
    }

    exportLedger() {
        // TODO: Implement CSV/PDF export
        this.showSuccess('Export feature coming soon!');
    }

    async apiCall(method, endpoint, data = null) {
        const url = `${this.baseUrl}${endpoint}`;
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json'
            }
        };

        if (data && (method === 'POST' || method === 'PUT' || method === 'PATCH')) {
            options.body = JSON.stringify(data);
        } else if (data && method === 'GET') {
            const params = new URLSearchParams(data);
            const fullUrl = `${url}?${params}`;
            const response = await fetch(fullUrl, options);
            return await response.json();
        }

        const response = await fetch(url, options);
        return await response.json();
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('en-KE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('en-KE', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    showSuccess(message) {
        this.showAlert(message, 'success');
    }

    showError(message) {
        this.showAlert(message, 'danger');
    }

    showAlert(message, type) {
        const alertContainer = document.getElementById('alertContainer') || this.createAlertContainer();
        
        const alertId = `alert-${Date.now()}`;
        const alert = document.createElement('div');
        alert.id = alertId;
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        alertContainer.appendChild(alert);

        // Auto remove after 5 seconds
        setTimeout(() => {
            const alertElement = document.getElementById(alertId);
            if (alertElement) {
                alertElement.remove();
            }
        }, 5000);
    }

    createAlertContainer() {
        const container = document.createElement('div');
        container.id = 'alertContainer';
        container.className = 'position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }
}

// Initialize the ledger manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.adminLedgerManager = new AdminLedgerManager();
    
    // Set default date range (current month)
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    document.getElementById('start_date').value = firstDay.toISOString().split('T')[0];
    document.getElementById('end_date').value = lastDay.toISOString().split('T')[0];
});

function clearLedgerFilters() {
    document.getElementById('ledgerFilters').reset();
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    document.getElementById('start_date').value = firstDay.toISOString().split('T')[0];
    document.getElementById('end_date').value = lastDay.toISOString().split('T')[0];
    
    adminLedgerManager.loadLedgerData();
    adminLedgerManager.loadTrialBalance();
}
</script>
@endpush