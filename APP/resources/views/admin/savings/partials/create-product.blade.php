@extends('admin.layouts.app')

@section('title', 'Create Savings Product')

@push('styles')
<style>
    .page-header-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="page-header-card">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-2">
                <i class="bi bi-plus-circle"></i> Create New Savings Product
            </h1>
            <p class="mb-0 opacity-75">Define a new savings product with custom rules and interest rates</p>
        </div>
        <a href="{{ route('admin.savings.products') }}" class="btn btn-light">
            <i class="bi bi-arrow-left"></i> Back to Products
        </a>
    </div>
</div>

<!-- Form Content -->
<form action="{{ route('admin.savings.products.store') }}" method="POST" id="productForm">
    @csrf

    <div class="row">
        <!-- Basic Information -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Product Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                id="name" name="name" value="{{ old('name') }}"
                                placeholder="e.g., Premium Savings Account" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="code" class="form-label">Product Code <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror"
                                id="code" name="code" value="{{ old('code') }}" placeholder="e.g., SAV001"
                                required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Unique identifier for this product</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Product Type <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type"
                                name="type" required>
                                <option value="">Select Type</option>
                                <option value="wallet" {{ old('type') == 'wallet' ? 'selected' : '' }}>Wallet</option>
                                <option value="compulsory" {{ old('type') == 'compulsory' ? 'selected' : '' }}>
                                    Compulsory</option>
                                <option value="voluntary" {{ old('type') == 'voluntary' ? 'selected' : '' }}>Voluntary
                                </option>
                                <option value="fixed_deposit" {{ old('type') == 'fixed_deposit' ? 'selected' : '' }}>
                                    Fixed Deposit</option>
                                <option value="special" {{ old('type') == 'special' ? 'selected' : '' }}>Special
                                </option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                    value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active</strong> (Product is available for new accounts)
                                </label>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                rows="3" placeholder="Detailed description of the savings product...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Balance Requirements -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Balance Requirements</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="minimum_balance" class="form-label">Minimum Balance (UGX) <span
                                class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('minimum_balance') is-invalid @enderror"
                            id="minimum_balance" name="minimum_balance" value="{{ old('minimum_balance', 0) }}"
                            min="0" step="0.01" required>
                        @error('minimum_balance')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="maximum_balance" class="form-label">Maximum Balance (UGX)</label>
                        <input type="number" class="form-control @error('maximum_balance') is-invalid @enderror"
                            id="maximum_balance" name="maximum_balance" value="{{ old('maximum_balance') }}"
                            min="0" step="0.01">
                        @error('maximum_balance')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Leave empty for no limit</small>
                    </div>

                    <div class="mb-3">
                        <label for="minimum_monthly_contribution" class="form-label">Minimum Monthly Contribution
                            (UGX)</label>
                        <input type="number"
                            class="form-control @error('minimum_monthly_contribution') is-invalid @enderror"
                            id="minimum_monthly_contribution" name="minimum_monthly_contribution"
                            value="{{ old('minimum_monthly_contribution') }}" min="0" step="0.01">
                        @error('minimum_monthly_contribution')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">For regular contribution products</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interest Configuration -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-percent"></i> Interest Configuration</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="interest_rate" class="form-label">Interest Rate (%) <span
                                class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('interest_rate') is-invalid @enderror"
                            id="interest_rate" name="interest_rate" value="{{ old('interest_rate', 0) }}"
                            min="0" max="100" step="0.01" required>
                        @error('interest_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="interest_calculation" class="form-label">Interest Calculation Method <span
                                class="text-danger">*</span></label>
                        <select class="form-select @error('interest_calculation') is-invalid @enderror"
                            id="interest_calculation" name="interest_calculation" required>
                            <option value="">Select Method</option>
                            <option value="simple" {{ old('interest_calculation') == 'simple' ? 'selected' : '' }}>
                                Simple Interest</option>
                            <option value="compound"
                                {{ old('interest_calculation') == 'compound' ? 'selected' : '' }}>Compound Interest
                            </option>
                        </select>
                        @error('interest_calculation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="interest_payment_frequency" class="form-label">Interest Payment Frequency <span
                                class="text-danger">*</span></label>
                        <select class="form-select @error('interest_payment_frequency') is-invalid @enderror"
                            id="interest_payment_frequency" name="interest_payment_frequency" required>
                            <option value="">Select Frequency</option>
                            <option value="daily"
                                {{ old('interest_payment_frequency') == 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly"
                                {{ old('interest_payment_frequency') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly"
                                {{ old('interest_payment_frequency') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly"
                                {{ old('interest_payment_frequency') == 'quarterly' ? 'selected' : '' }}>Quarterly
                            </option>
                            <option value="annually"
                                {{ old('interest_payment_frequency') == 'annually' ? 'selected' : '' }}>Annually
                            </option>
                        </select>
                        @error('interest_payment_frequency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Withdrawal Rules -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="bi bi-arrow-up-circle"></i> Withdrawal Rules</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="withdrawal_fee" class="form-label">Withdrawal Fee (UGX) <span
                                class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('withdrawal_fee') is-invalid @enderror"
                            id="withdrawal_fee" name="withdrawal_fee" value="{{ old('withdrawal_fee', 0) }}"
                            min="0" step="0.01" required>
                        @error('withdrawal_fee')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="allow_partial_withdrawals" class="form-label">Withdrawal Options</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" id="allow_partial_withdrawals"
                                name="allow_partial_withdrawals" value="1"
                                {{ old('allow_partial_withdrawals', false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_partial_withdrawals">
                                <strong>Allow Partial Withdrawals</strong>
                            </label>
                        </div>
                        <small class="text-muted">If unchecked, only full account closure withdrawals are
                            allowed</small>
                    </div>

                    <div class="mb-3">
                        <label for="minimum_notice_days" class="form-label">Minimum Notice Days</label>
                        <input type="number" class="form-control @error('minimum_notice_days') is-invalid @enderror"
                            id="minimum_notice_days" name="minimum_notice_days"
                            value="{{ old('minimum_notice_days') }}" min="0">
                        @error('minimum_notice_days')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Number of days notice required before withdrawal</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Settings -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Additional Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="maturity_period_months" class="form-label">Maturity Period (Months)</label>
                        <input type="number"
                            class="form-control @error('maturity_period_months') is-invalid @enderror"
                            id="maturity_period_months" name="maturity_period_months"
                            value="{{ old('maturity_period_months') }}" min="0">
                        @error('maturity_period_months')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">For fixed-term products (leave empty if not applicable)</small>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> <strong>Note:</strong> Fields marked with <span
                            class="text-danger">*</span> are required.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.savings.products') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-save"></i> Create Product
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
    <script>
        $(document).ready(function() {
            // Validate maximum balance
            $('#minimum_balance, #maximum_balance').on('change', function() {
                const minBalance = parseFloat($('#minimum_balance').val()) || 0;
                const maxBalance = parseFloat($('#maximum_balance').val()) || 0;

                if (maxBalance > 0 && maxBalance < minBalance) {
                    alert('Maximum balance cannot be less than minimum balance');
                    $('#maximum_balance').val('');
                }
            });

            // Product type suggestions
            $('#type').on('change', function() {
                const type = $(this).val();
                if (!type) return;

                let suggestions = {};

                switch (type) {
                    case 'wallet':
                        suggestions = {
                            minimum_balance: 0,
                            interest_rate: 0,
                            withdrawal_fee: 0,
                            interest_calculation: 'simple',
                            interest_payment_frequency: 'monthly',
                            allow_partial_withdrawals: true
                        };
                        break;
                    case 'compulsory':
                        suggestions = {
                            minimum_balance: 50000,
                            interest_rate: 3.5,
                            withdrawal_fee: 2000,
                            interest_calculation: 'simple',
                            interest_payment_frequency: 'annually',
                            allow_partial_withdrawals: false
                        };
                        break;
                    case 'voluntary':
                        suggestions = {
                            minimum_balance: 10000,
                            interest_rate: 2.75,
                            withdrawal_fee: 1500,
                            interest_calculation: 'simple',
                            interest_payment_frequency: 'monthly',
                            allow_partial_withdrawals: true
                        };
                        break;
                    case 'fixed_deposit':
                        suggestions = {
                            minimum_balance: 500000,
                            interest_rate: 4.25,
                            withdrawal_fee: 10000,
                            interest_calculation: 'compound',
                            interest_payment_frequency: 'annually',
                            allow_partial_withdrawals: false,
                            maturity_period_months: 12,
                            minimum_notice_days: 30
                        };
                        break;
                    case 'special':
                        suggestions = {
                            minimum_balance: 100000,
                            interest_rate: 3.0,
                            withdrawal_fee: 2500,
                            interest_calculation: 'simple',
                            interest_payment_frequency: 'quarterly',
                            allow_partial_withdrawals: true
                        };
                        break;
                }

                // Apply suggestions only if fields are empty
                Object.keys(suggestions).forEach(key => {
                    const input = $(`[name="${key}"]`);
                    if (input.is(':checkbox')) {
                        input.prop('checked', suggestions[key]);
                    } else if (input.is('select')) {
                        if (!input.val()) {
                            input.val(suggestions[key]);
                        }
                    } else if (!input.val()) {
                        input.val(suggestions[key]);
                    }
                });

                // Show notification
                const notification = $(
                    '<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">').
                html('<i class="bi bi-check-circle"></i> <strong>Suggested values applied!</strong> Feel free to modify them as needed.' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>');
                $('#type').parent().append(notification);
                setTimeout(() => notification.fadeOut(() => notification.remove()), 3000);
            });

            // Form validation
            $('#productForm').on('submit', function(e) {
                const minBalance = parseFloat($('#minimum_balance').val()) || 0;
                const maxBalance = parseFloat($('#maximum_balance').val()) || 0;

                if (maxBalance > 0 && maxBalance < minBalance) {
                    e.preventDefault();
                    alert('Please fix the validation errors before submitting.');
                    return false;
                }
            });
        });
    </script>
@endpush


@endsection


