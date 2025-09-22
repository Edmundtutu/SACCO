<!-- Reject Transaction Modal -->
<div class="modal fade" id="rejectTransactionModal" tabindex="-1" aria-labelledby="rejectTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectTransactionForm" action="#" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectTransactionModalLabel">
                        <i class="fas fa-times-circle text-danger me-2"></i>
                        Reject Transaction
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action will reject the transaction and mark it as rejected. This action cannot be undone.
                    </div>
                    
                    <div class="mb-3">
                        <label for="reject_reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <select 
                            class="form-select @error('reject_reason') is-invalid @enderror" 
                            id="reject_reason" 
                            name="reject_reason" 
                            required
                        >
                            <option value="">Select a reason...</option>
                            <option value="insufficient_funds">Insufficient Funds</option>
                            <option value="invalid_documentation">Invalid Documentation</option>
                            <option value="policy_violation">Policy Violation</option>
                            <option value="member_not_eligible">Member Not Eligible</option>
                            <option value="duplicate_transaction">Duplicate Transaction</option>
                            <option value="technical_error">Technical Error</option>
                            <option value="other">Other</option>
                        </select>
                        @error('reject_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="reject_notes" class="form-label">Additional Notes</label>
                        <textarea 
                            class="form-control @error('reject_notes') is-invalid @enderror" 
                            id="reject_notes" 
                            name="reject_notes" 
                            rows="3" 
                            placeholder="Provide additional details about the rejection..."
                        >{{ old('reject_notes') }}</textarea>
                        @error('reject_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="reject_processed_by" class="form-label">Rejected By</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="reject_processed_by" 
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
                                    <div id="reject_transaction_number" class="fw-bold">-</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Amount:</small>
                                    <div id="reject_transaction_amount" class="fw-bold">-</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Type:</small>
                                    <div id="reject_transaction_type" class="fw-bold">-</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Member:</small>
                                    <div id="reject_transaction_member" class="fw-bold">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times-circle me-1"></i> Reject Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle reject button clicks
    document.querySelectorAll('[data-action="reject"]').forEach(button => {
        button.addEventListener('click', function() {
            const transactionId = this.dataset.transactionId;
            const transactionNumber = this.dataset.transactionNumber;
            const transactionAmount = this.dataset.transactionAmount;
            const transactionType = this.dataset.transactionType;
            const transactionMember = this.dataset.transactionMember;
            
            // Update form action URL
            const form = document.getElementById('rejectTransactionForm');
            form.action = `/admin/transactions/${transactionId}/reject`;
            
            // Reset form
            form.reset();
            
            // Update modal content
            document.getElementById('reject_transaction_number').textContent = transactionNumber;
            document.getElementById('reject_transaction_amount').textContent = 'UGX ' + parseFloat(transactionAmount).toLocaleString();
            document.getElementById('reject_transaction_type').textContent = transactionType.replace('_', ' ').toUpperCase();
            document.getElementById('reject_transaction_member').textContent = transactionMember;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('rejectTransactionModal'));
            modal.show();
        });
    });
});
</script>
