/**
 * Admin Panel Transaction Management JavaScript
 * 
 * This file handles all transaction-related functionality in the admin panel
 * by consuming the existing API endpoints directly, avoiding duplication.
 */

class AdminTransactionManager {
    constructor() {
        this.baseUrl = '/api';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadTransactionData();
    }

    setupEventListeners() {
        // Manual transaction form
        const manualTransactionForm = document.getElementById('manualTransactionForm');
        if (manualTransactionForm) {
            manualTransactionForm.addEventListener('submit', this.handleManualTransaction.bind(this));
        }

        // Loan disbursement
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="disburse-loan"]')) {
                e.preventDefault();
                this.handleLoanDisbursement(e.target.dataset.loanId);
            }
        });

        // Transaction reversal
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="reverse-transaction"]')) {
                e.preventDefault();
                this.handleTransactionReversal(e.target.dataset.transactionId);
            }
        });

        // Real-time transaction updates
        this.setupRealTimeUpdates();
    }

    /**
     * Handle manual transaction processing
     */
    async handleManualTransaction(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            this.showLoading('Processing transaction...');
            
            let endpoint, payload;
            
            if (data.transaction_type === 'deposit') {
                endpoint = '/transactions/deposit';
                payload = {
                    member_id: parseInt(data.member_id),
                    account_id: parseInt(data.account_id),
                    amount: parseFloat(data.amount),
                    description: data.description,
                    payment_reference: data.payment_reference || `MANUAL-${Date.now()}`,
                    metadata: {
                        processed_by: 'admin',
                        manual_transaction: true
                    }
                };
            } else if (data.transaction_type === 'withdrawal') {
                endpoint = '/transactions/withdrawal';
                payload = {
                    member_id: parseInt(data.member_id),
                    account_id: parseInt(data.account_id),
                    amount: parseFloat(data.amount),
                    description: data.description,
                    metadata: {
                        processed_by: 'admin',
                        manual_transaction: true
                    }
                };
            } else {
                throw new Error('Invalid transaction type');
            }

            const response = await this.apiCall('POST', endpoint, payload);
            
            if (response.success) {
                this.showSuccess('Transaction processed successfully!');
                this.refreshTransactionData();
                this.closeModal('manualTransactionModal');
                form.reset();
            } else {
                throw new Error(response.message || 'Transaction failed');
            }

        } catch (error) {
            console.error('Transaction error:', error);
            this.showError(`Transaction failed: ${error.message}`);
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Handle loan disbursement
     */
    async handleLoanDisbursement(loanId) {
        if (!confirm('Are you sure you want to disburse this loan?')) {
            return;
        }

        try {
            this.showLoading('Processing loan disbursement...');

            const payload = {
                loan_id: parseInt(loanId),
                disbursement_account_id: 1, // Default to primary account
                notes: 'Loan disbursed by admin',
                metadata: {
                    processed_by: 'admin',
                    disbursement_date: new Date().toISOString()
                }
            };

            const response = await this.apiCall('POST', '/transactions/loan-disbursement', payload);
            
            if (response.success) {
                this.showSuccess('Loan disbursed successfully!');
                this.refreshLoanData();
            } else {
                throw new Error(response.message || 'Loan disbursement failed');
            }

        } catch (error) {
            console.error('Loan disbursement error:', error);
            this.showError(`Loan disbursement failed: ${error.message}`);
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Handle transaction reversal
     */
    async handleTransactionReversal(transactionId) {
        const reason = prompt('Enter reason for reversal:');
        if (!reason) return;

        if (!confirm('Are you sure you want to reverse this transaction?')) {
            return;
        }

        try {
            this.showLoading('Reversing transaction...');

            const payload = {
                reversal_reason: reason,
                metadata: {
                    reversed_by: 'admin',
                    reversal_date: new Date().toISOString()
                }
            };

            const response = await this.apiCall('POST', `/transactions/${transactionId}/reverse`, payload);
            
            if (response.success) {
                this.showSuccess('Transaction reversed successfully!');
                this.refreshTransactionData();
            } else {
                throw new Error(response.message || 'Transaction reversal failed');
            }

        } catch (error) {
            console.error('Transaction reversal error:', error);
            this.showError(`Transaction reversal failed: ${error.message}`);
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Load transaction data for the current page
     */
    async loadTransactionData() {
        try {
            // Get current filter values
            const filters = this.getCurrentFilters();
            
            // Load transaction statistics
            await this.loadTransactionStatistics();
            
            // Load pending transactions for approval
            const pendingResponse = await this.apiCall('GET', '/transactions/history', {
                status: 'pending',
                per_page: 20
            });

            if (pendingResponse.success) {
                this.updatePendingTransactions(pendingResponse.data);
                this.updatePendingCount(pendingResponse.data.length);
            }

            // Load transactions with filters
            const transactionsResponse = await this.apiCall('GET', '/transactions/history', {
                ...filters,
                per_page: 25
            });

            if (transactionsResponse.success) {
                this.updateTransactionTable(transactionsResponse.data);
                this.updatePagination(transactionsResponse);
            }

        } catch (error) {
            console.error('Error loading transaction data:', error);
        }
    }

    /**
     * Load transaction statistics
     */
    async loadTransactionStatistics() {
        try {
            const today = new Date().toISOString().split('T')[0];
            
            // Get today's statistics
            const todayResponse = await this.apiCall('GET', '/transactions/history', {
                start_date: today,
                end_date: today,
                per_page: 1000 // Get all for counting
            });

            if (todayResponse.success) {
                const transactions = todayResponse.data;
                const stats = {
                    total: transactions.length,
                    pending: transactions.filter(t => t.status === 'pending').length,
                    completed: transactions.filter(t => t.status === 'completed').length,
                    volume: transactions
                        .filter(t => t.status === 'completed')
                        .reduce((sum, t) => sum + parseFloat(t.amount), 0)
                };
                
                this.updateStatistics(stats);
            }

            // Get total transactions count
            const totalResponse = await this.apiCall('GET', '/transactions/history', {
                per_page: 1 // Just get count
            });

            if (totalResponse.success && totalResponse.meta) {
                this.updateTotalCount(totalResponse.meta.total);
            }

        } catch (error) {
            console.error('Error loading statistics:', error);
        }
    }

    /**
     * Get current filter values from the form
     */
    getCurrentFilters() {
        const form = document.getElementById('transactionFilters');
        if (!form) return {};

        const formData = new FormData(form);
        const filters = {};
        
        for (const [key, value] of formData.entries()) {
            if (value) {
                filters[key] = value;
            }
        }

        return filters;
    }

    /**
     * Refresh transaction data
     */
    async refreshTransactionData() {
        await this.loadTransactionData();
        
        // Trigger custom event for other components
        document.dispatchEvent(new CustomEvent('transactionsUpdated'));
    }

    /**
     * Refresh loan data
     */
    async refreshLoanData() {
        // Reload the current page to reflect changes
        window.location.reload();
    }

    /**
     * Update transaction table with new data
     */
    updateTransactionTable(transactions) {
        const tableBody = document.querySelector('#transactionsTable tbody');
        if (!tableBody) return;

        tableBody.innerHTML = transactions.map(transaction => `
            <tr>
                <td>${transaction.transaction_number}</td>
                <td>${transaction.member?.name || 'N/A'}</td>
                <td>${transaction.account?.account_number || 'N/A'}</td>
                <td>
                    <span class="badge bg-${this.getTransactionTypeColor(transaction.type)}">
                        ${this.formatTransactionType(transaction.type)}
                    </span>
                </td>
                <td>UGX ${this.formatCurrency(transaction.amount)}</td>
                <td>UGX ${this.formatCurrency(transaction.balance_after || 0)}</td>
                <td>
                    <span class="badge bg-${this.getStatusColor(transaction.status)}">
                        ${this.formatStatus(transaction.status)}
                    </span>
                </td>
                <td>${this.formatDate(transaction.transaction_date)}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-outline-primary btn-sm" 
                                onclick="adminTransactionManager.viewTransactionDetails(${transaction.id})"
                                title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                        ${transaction.status === 'completed' ? `
                            <button class="btn btn-outline-danger btn-sm" 
                                    data-action="reverse-transaction"
                                    data-transaction-id="${transaction.id}"
                                    title="Reverse Transaction">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `).join('');
    }

    /**
     * Update pending transactions section
     */
    updatePendingTransactions(transactions) {
        const pendingSection = document.getElementById('pendingTransactions');
        if (!pendingSection) return;

        if (transactions.length === 0) {
            pendingSection.innerHTML = `
                <div class="text-center py-3">
                    <p class="text-muted mb-0">No pending transactions</p>
                </div>
            `;
            return;
        }

        pendingSection.innerHTML = transactions.map(transaction => `
            <div class="pending-transaction-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="transaction-type-icon ${transaction.type}">
                            <i class="bi bi-${this.getTransactionTypeIcon(transaction.type)}"></i>
                        </div>
                        <div>
                            <strong>${transaction.member?.name || 'Unknown'}</strong><br>
                            <small class="text-muted">
                                ${this.formatTransactionType(transaction.type)} - UGX ${this.formatCurrency(transaction.amount)}
                            </small>
                        </div>
                    </div>
                    <div class="action-buttons">
                        <button class="btn btn-success btn-sm me-1" 
                                onclick="adminTransactionManager.approveTransaction(${transaction.id})">
                            <i class="bi bi-check"></i> Approve
                        </button>
                        <button class="btn btn-danger btn-sm" 
                                onclick="adminTransactionManager.rejectTransaction(${transaction.id})">
                            <i class="bi bi-x"></i> Reject
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }

    /**
     * Update statistics display
     */
    updateStatistics(stats) {
        const elements = {
            totalTransactions: document.getElementById('totalTransactions'),
            pendingTransactions: document.getElementById('pendingTransactions'),
            completedTransactions: document.getElementById('completedTransactions'),
            totalVolume: document.getElementById('totalVolume')
        };

        if (elements.totalTransactions) elements.totalTransactions.textContent = stats.total || 0;
        if (elements.pendingTransactions) elements.pendingTransactions.textContent = stats.pending || 0;
        if (elements.completedTransactions) elements.completedTransactions.textContent = stats.completed || 0;
        if (elements.totalVolume) elements.totalVolume.textContent = `UGX ${this.formatCurrency(stats.volume || 0)}`;
    }

    /**
     * Update pending count badge
     */
    updatePendingCount(count) {
        const badge = document.getElementById('pendingCount');
        if (badge) {
            badge.textContent = count;
        }
    }

    /**
     * Update total count badge
     */
    updateTotalCount(count) {
        const badge = document.getElementById('totalCount');
        if (badge) {
            badge.textContent = count;
        }
    }

    /**
     * Update pagination
     */
    updatePagination(response) {
        const container = document.getElementById('paginationContainer');
        if (!container || !response.meta) return;

        const { current_page, last_page, per_page, total } = response.meta;
        const startItem = (current_page - 1) * per_page + 1;
        const endItem = Math.min(current_page * per_page, total);

        // Update showing text
        const showingStart = document.getElementById('showingStart');
        const showingEnd = document.getElementById('showingEnd');
        const totalResults = document.getElementById('totalResults');

        if (showingStart) showingStart.textContent = startItem;
        if (showingEnd) showingEnd.textContent = endItem;
        if (totalResults) totalResults.textContent = total;

        // Generate pagination buttons
        let paginationHtml = '<nav><ul class="pagination pagination-sm mb-0">';
        
        // Previous button
        if (current_page > 1) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="adminTransactionManager.loadPage(${current_page - 1})">Previous</a></li>`;
        }

        // Page numbers
        const startPage = Math.max(1, current_page - 2);
        const endPage = Math.min(last_page, current_page + 2);

        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === current_page ? 'active' : '';
            paginationHtml += `<li class="page-item ${activeClass}"><a class="page-link" href="#" onclick="adminTransactionManager.loadPage(${i})">${i}</a></li>`;
        }

        // Next button
        if (current_page < last_page) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="adminTransactionManager.loadPage(${current_page + 1})">Next</a></li>`;
        }

        paginationHtml += '</ul></nav>';
        container.innerHTML = paginationHtml;
    }

    /**
     * Load specific page
     */
    async loadPage(page) {
        const currentFilters = this.getCurrentFilters();
        currentFilters.page = page;
        
        try {
            const response = await this.apiCall('GET', '/transactions/history', currentFilters);
            if (response.success) {
                this.updateTransactionTable(response.data);
                this.updatePagination(response);
            }
        } catch (error) {
            console.error('Error loading page:', error);
        }
    }

    /**
     * Get transaction type icon
     */
    getTransactionTypeIcon(type) {
        const icons = {
            'deposit': 'arrow-down-circle',
            'withdrawal': 'arrow-up-circle',
            'share_purchase': 'graph-up',
            'loan_disbursement': 'cash',
            'loan_repayment': 'arrow-counterclockwise'
        };
        return icons[type] || 'circle';
    }

    /**
     * View transaction details
     */
    async viewTransactionDetails(transactionId) {
        try {
            const response = await this.apiCall('GET', `/transactions/${transactionId}`);
            
            if (response.success) {
                this.showTransactionDetailsModal(response.data);
            } else {
                throw new Error(response.message || 'Failed to load transaction details');
            }

        } catch (error) {
            console.error('Error loading transaction details:', error);
            this.showError(`Failed to load transaction details: ${error.message}`);
        }
    }

    /**
     * Approve transaction
     */
    async approveTransaction(transactionId) {
        try {
            this.showLoading('Approving transaction...');

            const response = await this.apiCall('POST', `/transactions/${transactionId}/approve`);
            
            if (response.success) {
                this.showSuccess('Transaction approved successfully!');
                this.refreshTransactionData();
            } else {
                throw new Error(response.message || 'Transaction approval failed');
            }

        } catch (error) {
            console.error('Transaction approval error:', error);
            this.showError(`Transaction approval failed: ${error.message}`);
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Reject transaction
     */
    async rejectTransaction(transactionId) {
        const reason = prompt('Enter rejection reason:');
        if (!reason) return;

        try {
            this.showLoading('Rejecting transaction...');

            const response = await this.apiCall('POST', `/transactions/${transactionId}/reject`, {
                rejection_reason: reason
            });
            
            if (response.success) {
                this.showSuccess('Transaction rejected successfully!');
                this.refreshTransactionData();
            } else {
                throw new Error(response.message || 'Transaction rejection failed');
            }

        } catch (error) {
            console.error('Transaction rejection error:', error);
            this.showError(`Transaction rejection failed: ${error.message}`);
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Setup real-time updates
     */
    setupRealTimeUpdates() {
        // Refresh data every 30 seconds
        setInterval(() => {
            this.loadTransactionData();
        }, 30000);

        // Listen for browser focus to refresh data
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.loadTransactionData();
            }
        });
    }

    /**
     * Make API call
     */
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

    /**
     * Utility methods
     */
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
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    formatTransactionType(type) {
        return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    formatStatus(status) {
        return status.charAt(0).toUpperCase() + status.slice(1);
    }

    getTransactionTypeColor(type) {
        const colors = {
            'deposit': 'success',
            'withdrawal': 'warning',
            'share_purchase': 'info',
            'loan_disbursement': 'primary',
            'loan_repayment': 'secondary'
        };
        return colors[type] || 'light';
    }

    getStatusColor(status) {
        const colors = {
            'pending': 'warning',
            'completed': 'success',
            'failed': 'danger',
            'reversed': 'secondary'
        };
        return colors[status] || 'light';
    }

    /**
     * UI Helper methods
     */
    showLoading(message = 'Loading...') {
        // Create or show loading overlay
        let overlay = document.getElementById('loadingOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'loadingOverlay';
            overlay.className = 'loading-overlay';
            overlay.innerHTML = `
                <div class="loading-content">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">${message}</p>
                </div>
            `;
            document.body.appendChild(overlay);
        }
        overlay.style.display = 'flex';
    }

    hideLoading() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
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

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        }
    }

    showTransactionDetailsModal(transaction) {
        // Create and show transaction details modal
        const modalHtml = `
            <div class="modal fade" id="transactionDetailsModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Transaction Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Transaction Information</h6>
                                    <p><strong>Number:</strong> ${transaction.transaction_number}</p>
                                    <p><strong>Type:</strong> ${this.formatTransactionType(transaction.type)}</p>
                                    <p><strong>Amount:</strong> UGX ${this.formatCurrency(transaction.amount)}</p>
                                    <p><strong>Status:</strong> 
                                        <span class="badge bg-${this.getStatusColor(transaction.status)}">
                                            ${this.formatStatus(transaction.status)}
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Account Information</h6>
                                    <p><strong>Member:</strong> ${transaction.member?.name || 'N/A'}</p>
                                    <p><strong>Account:</strong> ${transaction.account?.account_number || 'N/A'}</p>
                                    <p><strong>Balance Before:</strong> UGX ${this.formatCurrency(transaction.balance_before || 0)}</p>
                                    <p><strong>Balance After:</strong> UGX ${this.formatCurrency(transaction.balance_after || 0)}</p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6>Transaction Details</h6>
                                    <p><strong>Description:</strong> ${transaction.description}</p>
                                    <p><strong>Date:</strong> ${this.formatDate(transaction.transaction_date)}</p>
                                    ${transaction.payment_reference ? `<p><strong>Reference:</strong> ${transaction.payment_reference}</p>` : ''}
                                    ${transaction.metadata ? `
                                        <h6>Additional Information</h6>
                                        <pre class="bg-light p-2 rounded">${JSON.stringify(transaction.metadata, null, 2)}</pre>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            ${transaction.status === 'completed' ? `
                                <button type="button" class="btn btn-danger" 
                                        onclick="adminTransactionManager.handleTransactionReversal(${transaction.id})">
                                    Reverse Transaction
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        const existingModal = document.getElementById('transactionDetailsModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Add modal to DOM
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('transactionDetailsModal'));
        modal.show();
    }
}

// Initialize the transaction manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.adminTransactionManager = new AdminTransactionManager();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdminTransactionManager;
}
