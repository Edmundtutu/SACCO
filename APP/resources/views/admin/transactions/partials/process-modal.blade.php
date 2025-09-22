<!-- Process Transaction Modal -->
<div class="modal fade" id="processTransactionModal" tabindex="-1" aria-labelledby="processTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="processTransactionForm" action="{{ route('admin.transactions.process') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="processTransactionModalLabel">
                        <i class="fas fa-plus-circle text-primary me-2"></i>
                        Process New Transaction
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="member_id" class="form-label">Member <span class="text-danger">*</span></label>
                                <select class="form-select @error('member_id') is-invalid @enderror" id="member_id" name="member_id" required>
                                    <option value="">Select Member...</option>
                                    @foreach(\App\Models\User::where('role', 'member')->get() as $member)
                                        <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                            {{ $member->name }} ({{ $member->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('member_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label">Transaction Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="">Select Type...</option>
                                    <option value="deposit" {{ old('type') == 'deposit' ? 'selected' : '' }}>Deposit</option>
                                    <option value="withdrawal" {{ old('type') == 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                                    <option value="share_purchase" {{ old('type') == 'share_purchase' ? 'selected' : '' }}>Share Purchase</option>
                                    <option value="loan_disbursement" {{ old('type') == 'loan_disbursement' ? 'selected' : '' }}>Loan Disbursement</option>
                                    <option value="loan_repayment" {{ old('type') == 'loan_repayment' ? 'selected' : '' }}>Loan Repayment</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount (UGX) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" step="0.01" min="0" value="{{ old('amount') }}" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method">
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="mobile_money" {{ old('payment_method') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                    <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Account Selection (for deposits/withdrawals) -->
                    <div class="row" id="accountRow" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="account_id" class="form-label">Savings Account</label>
                                <select class="form-select @error('account_id') is-invalid @enderror" id="account_id" name="account_id">
                                    <option value="">Select Account...</option>
                                </select>
                                @error('account_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Loan Selection (for loan transactions) -->
                    <div class="row" id="loanRow" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="loan_id" class="form-label">Loan</label>
                                <select class="form-select @error('loan_id') is-invalid @enderror" id="loan_id" name="loan_id">
                                    <option value="">Select Loan...</option>
                                </select>
                                @error('loan_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="reference" class="form-label">Reference/Receipt Number</label>
                        <input type="text" class="form-control @error('reference') is-invalid @enderror" id="reference" name="reference" value="{{ old('reference') }}" placeholder="Enter reference number">
                        @error('reference')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="Enter any additional notes...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="auto_approve" name="auto_approve" value="1">
                            <label class="form-check-label" for="auto_approve">
                                Auto-approve this transaction
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Process Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Transaction type change handler
    document.getElementById('type').addEventListener('change', function() {
        const type = this.value;
        const accountRow = document.getElementById('accountRow');
        const loanRow = document.getElementById('loanRow');
        
        if (type === 'deposit' || type === 'withdrawal') {
            accountRow.style.display = 'block';
            loanRow.style.display = 'none';
            loadMemberAccounts();
        } else if (type === 'loan_disbursement' || type === 'loan_repayment') {
            accountRow.style.display = 'none';
            loanRow.style.display = 'block';
            loadMemberLoans();
        } else {
            accountRow.style.display = 'none';
            loanRow.style.display = 'none';
        }
    });

    // Member change handler
    document.getElementById('member_id').addEventListener('change', function() {
        const type = document.getElementById('type').value;
        if (type === 'deposit' || type === 'withdrawal') {
            loadMemberAccounts();
        } else if (type === 'loan_disbursement' || type === 'loan_repayment') {
            loadMemberLoans();
        }
    });

    function loadMemberAccounts() {
        const memberId = document.getElementById('member_id').value;
        const accountSelect = document.getElementById('account_id');
        
        if (!memberId) {
            accountSelect.innerHTML = '<option value="">Select Account...</option>';
            return;
        }

        fetch(`/api/members/${memberId}/accounts`)
            .then(response => response.json())
            .then(data => {
                accountSelect.innerHTML = '<option value="">Select Account...</option>';
                if (data.success && data.data) {
                    data.data.forEach(account => {
                        const option = document.createElement('option');
                        option.value = account.id;
                        option.textContent = `${account.account_type} - UGX ${account.balance.toLocaleString()}`;
                        accountSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading accounts:', error);
            });
    }

    function loadMemberLoans() {
        const memberId = document.getElementById('member_id').value;
        const loanSelect = document.getElementById('loan_id');
        
        if (!memberId) {
            loanSelect.innerHTML = '<option value="">Select Loan...</option>';
            return;
        }

        fetch(`/api/members/${memberId}/loans`)
            .then(response => response.json())
            .then(data => {
                loanSelect.innerHTML = '<option value="">Select Loan...</option>';
                if (data.success && data.data) {
                    data.data.forEach(loan => {
                        const option = document.createElement('option');
                        option.value = loan.id;
                        option.textContent = `${loan.loan_number} - UGX ${loan.outstanding_balance.toLocaleString()}`;
                        loanSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading loans:', error);
            });
    }
});
</script>
