<!-- Reverse Transaction Modal -->
<div class="modal fade" id="reverseTransactionModal" tabindex="-1" aria-labelledby="reverseTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="reverseTransactionForm" action="#" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="reverseTransactionModalLabel">
                        <i class="fas fa-undo text-warning me-2"></i>
                        Reverse Transaction
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Critical Action:</strong> This will reverse the transaction and create a reversal entry. This action should only be used for correcting errors and requires proper authorization.
                    </div>
                    
                    <div class="mb-3">
                        <label for="reverse_reason" class="form-label">Reversal Reason <span class="text-danger">*</span></label>
                        <select 
                            class="form-select @error('reverse_reason') is-invalid @enderror" 
                            id="reverse_reason" 
                            name="reverse_reason" 
                            required
                        >
                            <option value="">Select a reason...</option>
                            <option value="duplicate_entry">Duplicate Entry</option>
                            <option value="incorrect_amount">Incorrect Amount</option>
                            <option value="wrong_account">Wrong Account</option>
                            <option value="fraudulent_transaction">Fraudulent Transaction</option>
                            <option value="system_error">System Error</option>
                            <option value="member_request">Member Request</option>
                            <option value="regulatory_requirement">Regulatory Requirement</option>
                            <option value="other">Other</option>
                        </select>
                        @error('reverse_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="reverse_notes" class="form-label">Detailed Explanation <span class="text-danger">*</span></label>
                        <textarea 
                            class="form-control @error('reverse_notes') is-invalid @enderror" 
                            id="reverse_notes" 
                            name="reverse_notes" 
                            rows="4" 
                            placeholder="Provide a detailed explanation for the reversal..."
                            required
                        >{{ old('reverse_notes') }}</textarea>
                        @error('reverse_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="reverse_authorized_by" class="form-label">Authorized By</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="reverse_authorized_by" 
                            name="authorized_by" 
                            value="{{ auth()->user()->name }}" 
                            readonly
                        >
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="reverse_confirmation" 
                                name="confirmation" 
                                required
                            >
                            <label class="form-check-label" for="reverse_confirmation">
                                I confirm that I have verified the need for this reversal and have proper authorization to proceed.
                            </label>
                        </div>
                    </div>

                    <!-- Transaction Details Preview -->
                    <div class="card bg-light">
                        <div class="card-header">
                            <h6 class="mb-0">Transaction to be Reversed</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Transaction #:</small>
                                    <div id="reverse_transaction_number" class="fw-bold">-</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Amount:</small>
                                    <div id="reverse_transaction_amount" class="fw-bold">-</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Type:</small>
                                    <div id="reverse_transaction_type" class="fw-bold">-</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Member:</small>
                                    <div id="reverse_transaction_member" class="fw-bold">-</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Date:</small>
                                    <div id="reverse_transaction_date" class="fw-bold">-</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Status:</small>
                                    <div id="reverse_transaction_status" class="fw-bold">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Impact Warning -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Reversal Impact:</h6>
                        <ul class="mb-0">
                            <li>Account balances will be adjusted</li>
                            <li>A reversal transaction will be created</li>
                            <li>General ledger entries will be reversed</li>
                            <li>This action will be logged for audit purposes</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-warning" id="reverseSubmitBtn" disabled>
                        <i class="fas fa-undo me-1"></i> Reverse Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle reverse button clicks
    document.querySelectorAll('[data-action="reverse"]').forEach(button => {
        button.addEventListener('click', function() {
            const transactionId = this.dataset.transactionId;
            const transactionNumber = this.dataset.transactionNumber;
            const transactionAmount = this.dataset.transactionAmount;
            const transactionType = this.dataset.transactionType;
            const transactionMember = this.dataset.transactionMember;
            const transactionDate = this.dataset.transactionDate;
            const transactionStatus = this.dataset.transactionStatus;
            
            // Update form action URL
            const form = document.getElementById('reverseTransactionForm');
            form.action = `/admin/transactions/${transactionId}/reverse`;
            
            // Reset form
            form.reset();
            
            // Update modal content
            document.getElementById('reverse_transaction_number').textContent = transactionNumber;
            document.getElementById('reverse_transaction_amount').textContent = 'UGX ' + parseFloat(transactionAmount).toLocaleString();
            document.getElementById('reverse_transaction_type').textContent = transactionType.replace('_', ' ').toUpperCase();
            document.getElementById('reverse_transaction_member').textContent = transactionMember;
            document.getElementById('reverse_transaction_date').textContent = transactionDate;
            document.getElementById('reverse_transaction_status').textContent = transactionStatus.toUpperCase();
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('reverseTransactionModal'));
            modal.show();
        });
    });

    // Enable/disable reverse button based on confirmation checkbox
    const confirmationCheckbox = document.getElementById('reverse_confirmation');
    const reverseSubmitBtn = document.getElementById('reverseSubmitBtn');
    
    if (confirmationCheckbox && reverseSubmitBtn) {
        confirmationCheckbox.addEventListener('change', function() {
            reverseSubmitBtn.disabled = !this.checked;
        });
    }
});
</script>
