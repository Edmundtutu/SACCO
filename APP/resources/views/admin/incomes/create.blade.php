@extends('admin.layouts.app')

@section('title', 'Record Income')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            @foreach($breadcrumbs as $crumb)
                @if($loop->last)
                    <li class="breadcrumb-item active">{{ $crumb['text'] }}</li>
                @else
                    <li class="breadcrumb-item"><a href="{{ $crumb['url'] }}">{{ $crumb['text'] }}</a></li>
                @endif
            @endforeach
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center gap-2">
                    <i class="fas fa-hand-holding-usd text-success"></i>
                    <h6 class="m-0 fw-bold text-primary">Record Non-Loan Income</h6>
                </div>
                <div class="card-body">

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.incomes.store') }}">
                        @csrf

                        {{-- Category --}}
                        <div class="mb-3">
                            <label for="category" class="form-label fw-semibold">
                                Income Category <span class="text-danger">*</span>
                            </label>
                            <select name="category" id="category"
                                    class="form-select @error('category') is-invalid @enderror" required>
                                <option value="">— Select Category —</option>
                                @foreach($categories as $key => $cat)
                                    <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>
                                        {{ $cat['name'] }} (GL: {{ $cat['code'] }})
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Amount --}}
                        <div class="mb-3">
                            <label for="amount" class="form-label fw-semibold">
                                Amount (UGX) <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="amount" id="amount" step="0.01" min="1"
                                   class="form-control @error('amount') is-invalid @enderror"
                                   value="{{ old('amount') }}" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Payment Method --}}
                        <div class="mb-3">
                            <label for="payment_method" class="form-label fw-semibold">
                                Payment Method <span class="text-danger">*</span>
                            </label>
                            <select name="payment_method" id="payment_method"
                                    class="form-select @error('payment_method') is-invalid @enderror" required>
                                @foreach($paymentMethods as $key => $label)
                                    <option value="{{ $key }}" {{ old('payment_method') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Payer Member --}}
                        <div class="mb-3">
                            <label for="payer_member_id" class="form-label fw-semibold">
                                Payer Member <small class="text-muted">(optional)</small>
                            </label>
                            <select name="payer_member_id" id="payer_member_id"
                                    class="form-select @error('payer_member_id') is-invalid @enderror">
                                <option value="">— Not member-linked —</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}"
                                        {{ old('payer_member_id') == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }} ({{ $member->membership?->member_number ?? $member->id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('payer_member_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Payment Reference --}}
                        <div class="mb-3">
                            <label for="payment_reference" class="form-label fw-semibold">
                                Payment Reference <small class="text-muted">(optional)</small>
                            </label>
                            <input type="text" name="payment_reference" id="payment_reference"
                                   class="form-control @error('payment_reference') is-invalid @enderror"
                                   value="{{ old('payment_reference') }}" maxlength="120">
                            @error('payment_reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">
                                Description / Notes <small class="text-muted">(optional)</small>
                            </label>
                            <textarea name="description" id="description" rows="3"
                                      class="form-control @error('description') is-invalid @enderror"
                                      maxlength="500">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Record Income
                            </button>
                            <a href="{{ route('admin.incomes.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>

                </div>
            </div>

            <div class="alert alert-info d-flex align-items-start gap-2">
                <i class="fas fa-info-circle mt-1"></i>
                <div>
                    <strong>Accounting Impact:</strong> Recording income will
                    <em>debit</em> the payment account (Cash / Bank / Mobile Money) and
                    <em>credit</em> the selected income GL account.
                    A receipt is generated automatically.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
