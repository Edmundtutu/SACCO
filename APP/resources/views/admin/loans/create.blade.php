@extends('admin.layouts.app')

@section('title', 'Create Loan')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Create New Loan</h1>
            <p class="text-muted">Create a new loan application for a member</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.loans.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Loans
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-plus-circle"></i> Loan Application Form
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.loans.store') }}" method="POST" id="loanForm">
                        @csrf
                        
                        <!-- Member Selection -->
                        <div class="mb-4">
                            <h6 class="font-weight-bold text-primary mb-3">Member Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="member_id" class="form-label">Select Member *</label>
                                        <select class="form-select" id="member_id" name="member_id" required>
                                            <option value="">Choose a member...</option>
                                            @foreach($members as $member)
                                            <option value="{{ $member->id }}" 
                                                    data-name="{{ $member->name }}"
                                                    data-email="{{ $member->email }}"
                                                    data-phone="{{ $member->phone }}"
                                                    data-member-number="{{ $member->member_number }}">
                                                {{ $member->name }} ({{ $member->member_number ?? $member->email }})
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Member Details</label>
                                        <div id="memberDetails" class="p-3 bg-light rounded">
                                            <p class="text-muted mb-0">Select a member to view details</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Loan Product Selection -->
                        <div class="mb-4">
                            <h6 class="font-weight-bold text-primary mb-3">Loan Product</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="loan_product_id" class="form-label">Loan Product *</label>
                                        <select class="form-select" id="loan_product_id" name="loan_product_id" required>
                                            <option value="">Choose a loan product...</option>
                                            @foreach($products as $product)
                                            <option value="{{ $product->id }}"
                                                    data-interest-rate="{{ $product->interest_rate }}"
                                                    data-min-amount="{{ $product->min_amount }}"
                                                    data-max-amount="{{ $product->max_amount }}"
                                                    data-min-period="{{ $product->min_period }}"
                                                    data-max-period="{{ $product->max_period }}">
                                                {{ $product->name }} ({{ $product->interest_rate }}% p.a.)
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Product Details</label>
                                        <div id="productDetails" class="p-3 bg-light rounded">
                                            <p class="text-muted mb-0">Select a loan product to view details</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Loan Amount and Terms -->
                        <div class="mb-4">
                            <h6 class="font-weight-bold text-primary mb-3">Loan Terms</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="principal_amount" class="form-label">Principal Amount (UGX) *</label>
                                        <input type="number" class="form-control" id="principal_amount" name="principal_amount" 
                                               min="1000" step="1000" required>
                                        <div class="form-text">Minimum: UGX 1,000</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="repayment_period" class="form-label">Repayment Period (months) *</label>
                                        <input type="number" class="form-control" id="repayment_period" name="repayment_period" 
                                               min="1" max="60" required>
                                        <div class="form-text">Maximum: 60 months</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Loan Purpose -->
                        <div class="mb-4">
                            <h6 class="font-weight-bold text-primary mb-3">Loan Purpose</h6>
                            <div class="mb-3">
                                <label for="purpose" class="form-label">Purpose of Loan *</label>
                                <textarea class="form-control" id="purpose" name="purpose" rows="3" 
                                          placeholder="Describe the purpose of this loan..." required></textarea>
                            </div>
                        </div>

                        <!-- Guarantors -->
                        <div class="mb-4">
                            <h6 class="font-weight-bold text-primary mb-3">Guarantors</h6>
                            <div class="mb-3">
                                <label for="guarantors" class="form-label">Select Guarantors *</label>
                                <select class="form-select" id="guarantors" name="guarantors[]" multiple required>
                                    @foreach($members as $member)
                                    <option value="{{ $member->id }}">
                                        {{ $member->name }} ({{ $member->member_number ?? $member->email }})
                                    </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Select at least one guarantor. Hold Ctrl/Cmd to select multiple.</div>
                            </div>
                            <div id="guarantorDetails" class="mt-3"></div>
                        </div>

                        <!-- Loan Summary -->
                        <div class="mb-4">
                            <h6 class="font-weight-bold text-primary mb-3">Loan Summary</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless table-sm">
                                                <tr>
                                                    <td class="font-weight-bold">Principal Amount:</td>
                                                    <td id="summaryPrincipal">UGX 0</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">Interest Rate:</td>
                                                    <td id="summaryRate">0% p.a.</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">Repayment Period:</td>
                                                    <td id="summaryPeriod">0 months</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless table-sm">
                                                <tr>
                                                    <td class="font-weight-bold">Monthly Payment:</td>
                                                    <td id="summaryMonthly" class="text-primary font-weight-bold">UGX 0</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">Total Interest:</td>
                                                    <td id="summaryInterest">UGX 0</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">Total Amount:</td>
                                                    <td id="summaryTotal" class="text-success font-weight-bold">UGX 0</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.loans.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Create Loan Application
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Help Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-info-circle"></i> Help & Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Member Selection</h6>
                        <p class="text-muted small">Select an active member who is eligible for loans. The member must have completed their membership requirements.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Loan Product</h6>
                        <p class="text-muted small">Choose an appropriate loan product based on the member's needs and the loan amount requested.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Guarantors</h6>
                        <p class="text-muted small">At least one guarantor is required. Guarantors must be active members and will be responsible for the loan if the borrower defaults.</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Loan Terms</h6>
                        <p class="text-muted small">Ensure the loan amount and repayment period are within the limits set by the selected loan product.</p>
                    </div>
                </div>
            </div>

            <!-- Recent Loans -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-clock-history"></i> Recent Loans
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <i class="bi bi-currency-dollar display-4 text-muted"></i>
                        <p class="text-muted mt-2">Recent loan applications will appear here</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const memberSelect = document.getElementById('member_id');
    const memberDetails = document.getElementById('memberDetails');
    const productSelect = document.getElementById('loan_product_id');
    const productDetails = document.getElementById('productDetails');
    const principalAmount = document.getElementById('principal_amount');
    const repaymentPeriod = document.getElementById('repayment_period');
    const guarantorsSelect = document.getElementById('guarantors');
    const guarantorDetails = document.getElementById('guarantorDetails');

    // Member selection handler
    memberSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const name = selectedOption.dataset.name;
            const email = selectedOption.dataset.email;
            const phone = selectedOption.dataset.phone;
            const memberNumber = selectedOption.dataset.memberNumber;

            memberDetails.innerHTML = `
                <div class="row">
                    <div class="col-12">
                        <h6 class="font-weight-bold mb-2">${name}</h6>
                        <p class="mb-1"><strong>Member #:</strong> ${memberNumber || 'N/A'}</p>
                        <p class="mb-1"><strong>Email:</strong> ${email}</p>
                        <p class="mb-0"><strong>Phone:</strong> ${phone || 'N/A'}</p>
                    </div>
                </div>
            `;
        } else {
            memberDetails.innerHTML = '<p class="text-muted mb-0">Select a member to view details</p>';
        }
    });

    // Product selection handler
    productSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const interestRate = selectedOption.dataset.interestRate;
            const minAmount = selectedOption.dataset.minAmount;
            const maxAmount = selectedOption.dataset.maxAmount;
            const minPeriod = selectedOption.dataset.minPeriod;
            const maxPeriod = selectedOption.dataset.maxPeriod;

            productDetails.innerHTML = `
                <div class="row">
                    <div class="col-12">
                        <h6 class="font-weight-bold mb-2">Product Details</h6>
                        <p class="mb-1"><strong>Interest Rate:</strong> ${interestRate}% p.a.</p>
                        <p class="mb-1"><strong>Amount Range:</strong> UGX ${Number(minAmount).toLocaleString()} - UGX ${Number(maxAmount).toLocaleString()}</p>
                        <p class="mb-0"><strong>Period Range:</strong> ${minPeriod} - ${maxPeriod} months</p>
                    </div>
                </div>
            `;

            // Update form validation
            principalAmount.min = minAmount;
            principalAmount.max = maxAmount;
            repaymentPeriod.min = minPeriod;
            repaymentPeriod.max = maxPeriod;
        } else {
            productDetails.innerHTML = '<p class="text-muted mb-0">Select a loan product to view details</p>';
        }
    });

    // Guarantors selection handler
    guarantorsSelect.addEventListener('change', function() {
        const selectedGuarantors = Array.from(this.selectedOptions);
        if (selectedGuarantors.length > 0) {
            let guarantorList = '<h6 class="font-weight-bold mb-2">Selected Guarantors:</h6>';
            selectedGuarantors.forEach(option => {
                guarantorList += `<div class="badge bg-primary me-1 mb-1">${option.text}</div>`;
            });
            guarantorDetails.innerHTML = guarantorList;
        } else {
            guarantorDetails.innerHTML = '';
        }
    });

    // Calculate loan summary
    function calculateLoanSummary() {
        const principal = parseFloat(principalAmount.value) || 0;
        const period = parseInt(repaymentPeriod.value) || 0;
        const selectedProduct = productSelect.options[productSelect.selectedIndex];
        const interestRate = selectedProduct ? parseFloat(selectedProduct.dataset.interestRate) || 0 : 0;

        if (principal > 0 && period > 0 && interestRate > 0) {
            const monthlyRate = interestRate / 100 / 12;
            const monthlyPayment = principal * (monthlyRate * Math.pow(1 + monthlyRate, period)) / (Math.pow(1 + monthlyRate, period) - 1);
            const totalInterest = (monthlyPayment * period) - principal;
            const totalAmount = principal + totalInterest;

            document.getElementById('summaryPrincipal').textContent = `UGX ${principal.toLocaleString()}`;
            document.getElementById('summaryRate').textContent = `${interestRate}% p.a.`;
            document.getElementById('summaryPeriod').textContent = `${period} months`;
            document.getElementById('summaryMonthly').textContent = `UGX ${monthlyPayment.toLocaleString()}`;
            document.getElementById('summaryInterest').textContent = `UGX ${totalInterest.toLocaleString()}`;
            document.getElementById('summaryTotal').textContent = `UGX ${totalAmount.toLocaleString()}`;
        } else {
            document.getElementById('summaryPrincipal').textContent = 'UGX 0';
            document.getElementById('summaryRate').textContent = '0% p.a.';
            document.getElementById('summaryPeriod').textContent = '0 months';
            document.getElementById('summaryMonthly').textContent = 'UGX 0';
            document.getElementById('summaryInterest').textContent = 'UGX 0';
            document.getElementById('summaryTotal').textContent = 'UGX 0';
        }
    }

    // Add event listeners for calculation
    principalAmount.addEventListener('input', calculateLoanSummary);
    repaymentPeriod.addEventListener('input', calculateLoanSummary);
    productSelect.addEventListener('change', calculateLoanSummary);

    // Form validation
    document.getElementById('loanForm').addEventListener('submit', function(e) {
        const memberId = memberSelect.value;
        const productId = productSelect.value;
        const principal = principalAmount.value;
        const period = repaymentPeriod.value;
        const guarantors = Array.from(guarantorsSelect.selectedOptions);

        if (!memberId || !productId || !principal || !period || guarantors.length === 0) {
            e.preventDefault();
            alert('Please fill in all required fields and select at least one guarantor.');
            return false;
        }

        // Validate amount and period against product limits
        const selectedProduct = productSelect.options[productSelect.selectedIndex];
        const minAmount = parseFloat(selectedProduct.dataset.minAmount);
        const maxAmount = parseFloat(selectedProduct.dataset.maxAmount);
        const minPeriod = parseInt(selectedProduct.dataset.minPeriod);
        const maxPeriod = parseInt(selectedProduct.dataset.maxPeriod);

        if (parseFloat(principal) < minAmount || parseFloat(principal) > maxAmount) {
            e.preventDefault();
            alert(`Loan amount must be between UGX ${minAmount.toLocaleString()} and UGX ${maxAmount.toLocaleString()}`);
            return false;
        }

        if (parseInt(period) < minPeriod || parseInt(period) > maxPeriod) {
            e.preventDefault();
            alert(`Repayment period must be between ${minPeriod} and ${maxPeriod} months`);
            return false;
        }
    });
});
</script>
@endpush
