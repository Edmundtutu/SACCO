<!-- Approve Transaction Modal -->
<div class="modal fade" id="approveTransactionModal" tabindex="-1" aria-labelledby="approveTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveTransactionForm" action="#" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="approveTransactionModalLabel">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Approve Transaction
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> This action will approve the transaction and mark it as completed. This action cannot be undone.
                    </div>
                    
                    <div class="mb-3">
                        <label for="approve_notes" class="form-label">Approval Notes <span class="text-danger">*</span></label>
                        <textarea 
                            class="form-control @error('approve_notes') is-invalid @enderror" 
                            id="approve_notes" 
                            name="approve_notes" 
                            rows="3" 
                            placeholder="Enter approval notes or comments..."
                            required
                        >{{ old('approve_notes') }}</textarea>
                        @error('approve_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="approve_processed_by" class="form-label">Processed By</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="approve_processed_by" 
                            name="processed_by" 
                            value="{{ auth()->user()->name }}" 
                            readonly
                        >
                    </div>

                    <!-- Transaction Details Preview -->
                    <div class="card bg-light">
                        <div class="card-header">
                            <h6 class="mb-0">Transaction Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Transaction #:</small>
                                    <div id="approve_transaction_number" class="fw-bold">-</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Amount:</small>
                                    <div id="approve_transaction_amount" class="fw-bold">-</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Type:</small>
                                    <div id="approve_transaction_type" class="fw-bold">-</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Member:</small>
                                    <div id="approve_transaction_member" class="fw-bold">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Approve Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle approve button clicks
    document.querySelectorAll('[data-action="approve"]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const transactionId = this.dataset.transactionId;
            const transactionNumber = this.dataset.transactionNumber;
            const transactionAmount = this.dataset.transactionAmount;
            const transactionType = this.dataset.transactionType;
            const transactionMember = this.dataset.transactionMember;
            
            // Validate required data
            if (!transactionId) {
                console.error('Transaction ID is missing');
                alert('Error: Transaction ID is missing');
                return;
            }
            
            // Update form action URL
            const form = document.getElementById('approveTransactionForm');
            if (!form) {
                console.error('Approve form not found');
                return;
            }
            
            form.action = `/admin/transactions/${transactionId}/approve`;
            
            // Reset form
            form.reset();
            
            // Update modal content
            const numberEl = document.getElementById('approve_transaction_number');
            const amountEl = document.getElementById('approve_transaction_amount');
            const typeEl = document.getElementById('approve_transaction_type');
            const memberEl = document.getElementById('approve_transaction_member');
            
            if (numberEl) numberEl.textContent = transactionNumber || 'N/A';
            if (amountEl) amountEl.textContent = 'UGX ' + (transactionAmount ? parseFloat(transactionAmount).toLocaleString() : '0');
            if (typeEl) typeEl.textContent = transactionType ? transactionType.replace('_', ' ').toUpperCase() : 'N/A';
            if (memberEl) memberEl.textContent = transactionMember || 'N/A';
            
            // Show modal
            const modalElement = document.getElementById('approveTransactionModal');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                console.error('Approve modal not found');
            }
        });
    });
});
</script>
