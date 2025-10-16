@extends('admin.layouts.app')

@section('title', 'Edit Savings Product - ' . $product->name)

@push('styles')
    <style>
        .page-header-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
                    <i class="bi bi-pencil-square"></i> Edit Savings Product
                </h1>
                <p class="mb-0 opacity-75">Modify settings for: <strong>{{ $product->name }}</strong> ({{ $product->code }})
                </p>
            </div>
            <a href="{{ route('admin.savings.products') }}" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Back to Products
            </a>
        </div>
    </div>

    <!-- Form Content -->


    {{-- Product Form for Edit --}}
    <form action="{{ route('admin.savings.products.update', $product->id) }}" method="POST" id="productForm">
        @csrf
        @method('PUT')

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
                                    id="name" name="name" value="{{ old('name', $product->name) }}"
                                    placeholder="e.g., Premium Savings Account" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label">Product Code <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror"
                                    id="code" name="code" value="{{ old('code', $product->code) }}"
                                    placeholder="e.g., SAV001" required>
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
                                    <option value="wallet" {{ old('type', $product->type) == 'wallet' ? 'selected' : '' }}>
                                        Wallet</option>
                                    <option value="compulsory"
                                        {{ old('type', $product->type) == 'compulsory' ? 'selected' : '' }}>Compulsory
                                    </option>
                                    <option value="voluntary"
                                        {{ old('type', $product->type) == 'voluntary' ? 'selected' : '' }}>Voluntary
                                    </option>
                                    <option value="fixed_deposit"
                                        {{ old('type', $product->type) == 'fixed_deposit' ? 'selected' : '' }}>Fixed
                                        Deposit</option>
                                    <option value="special"
                                        {{ old('type', $product->type) == 'special' ? 'selected' : '' }}>Special</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="is_active" class="form-label">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        <strong>Active</strong> (Product is available for new accounts)
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="3" placeholder="Detailed description of the savings product...">{{ old('description', $product->description) }}</textarea>
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
                                id="minimum_balance" name="minimum_balance"
                                value="{{ old('minimum_balance', $product->minimum_balance) }}" min="0"
                                step="0.01" required>
                            @error('minimum_balance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="maximum_balance" class="form-label">Maximum Balance (UGX)</label>
                            <input type="number" class="form-control @error('maximum_balance') is-invalid @enderror"
                                id="maximum_balance" name="maximum_balance"
                                value="{{ old('maximum_balance', $product->maximum_balance) }}" min="0"
                                step="0.01">
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
                                value="{{ old('minimum_monthly_contribution', $product->minimum_monthly_contribution) }}"
                                min="0" step="0.01">
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
                                id="interest_rate" name="interest_rate"
                                value="{{ old('interest_rate', $product->interest_rate) }}" min="0"
                                max="100" step="0.01" required>
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
                                <option value="simple"
                                    {{ old('interest_calculation', $product->interest_calculation) == 'simple' ? 'selected' : '' }}>
                                    Simple Interest</option>
                                <option value="compound"
                                    {{ old('interest_calculation', $product->interest_calculation) == 'compound' ? 'selected' : '' }}>
                                    Compound Interest</option>
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
                                    {{ old('interest_payment_frequency', $product->interest_payment_frequency) == 'daily' ? 'selected' : '' }}>
                                    Daily</option>
                                <option value="weekly"
                                    {{ old('interest_payment_frequency', $product->interest_payment_frequency) == 'weekly' ? 'selected' : '' }}>
                                    Weekly</option>
                                <option value="monthly"
                                    {{ old('interest_payment_frequency', $product->interest_payment_frequency) == 'monthly' ? 'selected' : '' }}>
                                    Monthly</option>
                                <option value="quarterly"
                                    {{ old('interest_payment_frequency', $product->interest_payment_frequency) == 'quarterly' ? 'selected' : '' }}>
                                    Quarterly</option>
                                <option value="annually"
                                    {{ old('interest_payment_frequency', $product->interest_payment_frequency) == 'annually' ? 'selected' : '' }}>
                                    Annually</option>
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
                                id="withdrawal_fee" name="withdrawal_fee"
                                value="{{ old('withdrawal_fee', $product->withdrawal_fee) }}" min="0"
                                step="0.01" required>
                            @error('withdrawal_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="allow_partial_withdrawals" class="form-label">Withdrawal Options</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="allow_partial_withdrawals"
                                    name="allow_partial_withdrawals" value="1"
                                    {{ old('allow_partial_withdrawals', $product->allow_partial_withdrawals) ? 'checked' : '' }}>
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
                                value="{{ old('minimum_notice_days', $product->minimum_notice_days) }}" min="0">
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
                                value="{{ old('maturity_period_months', $product->maturity_period_months) }}"
                                min="0">
                            @error('maturity_period_months')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">For fixed-term products (leave empty if not applicable)</small>
                        </div>

                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> <strong>Note:</strong> Fields marked with <span
                                class="text-danger">*</span> are required.
                        </div>

                        @if ($product->accounts()->count() > 0)
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="bi bi-exclamation-triangle"></i> <strong>Warning:</strong> This product has
                                {{ $product->accounts()->count() }} active account(s). Changes may affect existing
                                accounts.
                            </div>
                        @endif
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
                        <div>
                            @if ($product->accounts()->count() == 0)
                                <button type="button" class="btn btn-danger me-2" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal">
                                    <i class="bi bi-trash"></i> Delete Product
                                </button>
                            @endif
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save"></i> Update Product
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Delete Confirmation Modal -->
    @if ($product->accounts()->count() == 0)
        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="bi bi-trash"></i> Delete Product</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this savings product?</p>
                        <p><strong>{{ $product->name }} ({{ $product->code }})</strong></p>
                        <p class="text-danger">This action cannot be undone!</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form action="{{ route('admin.savings.products.delete', $product->id) }}" method="POST"
                            style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Yes, Delete It
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
