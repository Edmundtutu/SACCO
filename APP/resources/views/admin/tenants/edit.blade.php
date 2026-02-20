@extends('admin.layouts.app')

@section('title', 'Edit SACCO — ' . $tenant->sacco_name)

@section('content')
<div class="container-fluid">

    {{-- Page header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit SACCO</h1>
            <p class="text-muted mb-0">{{ $tenant->sacco_name }} &middot; <span class="text-muted">{{ $tenant->sacco_code }}</span></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.tenants.show', $tenant) }}" class="btn btn-outline-secondary">
                <i class="fas fa-eye me-1"></i> View
            </a>
            <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Please correct the following errors:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @php
        $logoUrl       = $tenant->getSetting('logo_url', '');
        $primaryColor  = $tenant->getSetting('primary_color', '#3399CC');
        $secondaryColor = $tenant->getSetting('secondary_color', '#2E86AB');
    @endphp

    <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}" novalidate>
        @csrf
        @method('PUT')

        <div class="row g-4">

            {{-- ── LEFT COLUMN ────────────────────────────────────────── --}}
            <div class="col-lg-8">

                {{-- SACCO Identity --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-building me-2"></i>SACCO Identity
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">SACCO Name <span class="text-danger">*</span></label>
                                <input type="text" name="sacco_name" class="form-control @error('sacco_name') is-invalid @enderror"
                                       value="{{ old('sacco_name', $tenant->sacco_name) }}" required>
                                @error('sacco_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">SACCO Code</label>
                                <input type="text" class="form-control bg-light" value="{{ $tenant->sacco_code }}" readonly
                                       title="SACCO code cannot be changed after creation">
                                <small class="text-muted">Auto-assigned, read-only</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $tenant->email) }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $tenant->phone) }}" required>
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Physical Address</label>
                                <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                          rows="2">{{ old('address', $tenant->address) }}</textarea>
                                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Location & Currency --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-globe me-2"></i>Location &amp; Currency
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Country</label>
                                <input type="text" name="country" class="form-control @error('country') is-invalid @enderror"
                                       value="{{ old('country', $tenant->country) }}">
                                @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Currency</label>
                                <input type="text" name="currency" class="form-control @error('currency') is-invalid @enderror"
                                       value="{{ old('currency', $tenant->currency) }}" maxlength="10">
                                @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Subscription --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-tags me-2"></i>Subscription
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Plan <span class="text-danger">*</span></label>
                                <select name="subscription_plan" class="form-select @error('subscription_plan') is-invalid @enderror" required>
                                    @foreach(['basic'=>'Basic','standard'=>'Standard','premium'=>'Premium','enterprise'=>'Enterprise'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('subscription_plan', $tenant->subscription_plan) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('subscription_plan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required
                                        onchange="toggleTrialDate(this.value)">
                                    @foreach(['trial'=>'Trial','active'=>'Active','suspended'=>'Suspended','inactive'=>'Inactive'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('status', $tenant->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4" id="trialDateWrapper"
                                 style="{{ old('status', $tenant->status) !== 'trial' ? 'display:none' : '' }}">
                                <label class="form-label fw-semibold">Trial Ends At</label>
                                <input type="date" name="trial_ends_at" class="form-control @error('trial_ends_at') is-invalid @enderror"
                                       value="{{ old('trial_ends_at', optional($tenant->trial_ends_at)->format('Y-m-d')) }}">
                                @error('trial_ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- end left col --}}

            {{-- ── RIGHT COLUMN ───────────────────────────────────────── --}}
            <div class="col-lg-4">

                {{-- Limits --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-sliders-h me-2"></i>Limits
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Max Members <span class="text-danger">*</span></label>
                                <input type="number" name="max_members" class="form-control @error('max_members') is-invalid @enderror"
                                       value="{{ old('max_members', $tenant->max_members) }}" min="1" required>
                                @error('max_members')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Max Staff <span class="text-danger">*</span></label>
                                <input type="number" name="max_staff" class="form-control @error('max_staff') is-invalid @enderror"
                                       value="{{ old('max_staff', $tenant->max_staff) }}" min="1" required>
                                @error('max_staff')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Max Active Loans <span class="text-danger">*</span></label>
                                <input type="number" name="max_loans" class="form-control @error('max_loans') is-invalid @enderror"
                                       value="{{ old('max_loans', $tenant->max_loans) }}" min="1" required>
                                @error('max_loans')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Max Loan Amount</label>
                                <input type="number" name="max_loan_amount" class="form-control @error('max_loan_amount') is-invalid @enderror"
                                       value="{{ old('max_loan_amount', $tenant->max_loan_amount) }}" min="0" placeholder="Unlimited">
                                @error('max_loan_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Owner Contact --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-user-tie me-2"></i>Owner / Primary Contact
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <label class="form-label fw-semibold">Name</label>
                                <input type="text" name="owner_name" class="form-control @error('owner_name') is-invalid @enderror"
                                       value="{{ old('owner_name', $tenant->owner_name) }}" placeholder="Full name">
                                @error('owner_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="owner_email" class="form-control @error('owner_email') is-invalid @enderror"
                                       value="{{ old('owner_email', $tenant->owner_email) }}" placeholder="owner@example.com">
                                @error('owner_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="owner_phone" class="form-control @error('owner_phone') is-invalid @enderror"
                                       value="{{ old('owner_phone', $tenant->owner_phone) }}" placeholder="+256 ...">
                                @error('owner_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Branding --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-palette me-2"></i>Branding
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <label class="form-label fw-semibold">Logo URL</label>
                                <input type="url" name="logo_url" id="logoUrlInput"
                                       class="form-control @error('logo_url') is-invalid @enderror"
                                       value="{{ old('logo_url', $logoUrl) }}"
                                       placeholder="https://..."
                                       oninput="previewLogo(this.value)">
                                @error('logo_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div id="logoPreview" class="{{ $logoUrl ? '' : 'd-none' }} mt-2">
                                    <img src="{{ $logoUrl }}" alt="Logo preview"
                                         style="max-height:60px;max-width:120px;object-fit:contain;border:1px solid #dee2e6;border-radius:4px;padding:4px;background:#f8f9fa;">
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Primary Colour</label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" name="primary_color_picker" class="form-control form-control-color"
                                               value="{{ old('primary_color', $primaryColor) }}"
                                               oninput="document.getElementById('primary_color').value=this.value;updateColorSwatch('primary',this.value)">
                                        <input type="text" name="primary_color" id="primary_color"
                                               class="form-control @error('primary_color') is-invalid @enderror"
                                               value="{{ old('primary_color', $primaryColor) }}" placeholder="#3399CC"
                                               oninput="syncColorPicker('primary_color_picker',this.value)">
                                    </div>
                                    @error('primary_color')<div class="text-danger" style="font-size:.8rem;">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Secondary Colour</label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" name="secondary_color_picker" class="form-control form-control-color"
                                               value="{{ old('secondary_color', $secondaryColor) }}"
                                               oninput="document.getElementById('secondary_color').value=this.value;updateColorSwatch('secondary',this.value)">
                                        <input type="text" name="secondary_color" id="secondary_color"
                                               class="form-control @error('secondary_color') is-invalid @enderror"
                                               value="{{ old('secondary_color', $secondaryColor) }}" placeholder="#2E86AB"
                                               oninput="syncColorPicker('secondary_color_picker',this.value)">
                                    </div>
                                    @error('secondary_color')<div class="text-danger" style="font-size:.8rem;">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            {{-- Colour swatches --}}
                            <div class="d-flex gap-2 mt-1">
                                <div id="swatch_primary" class="rounded flex-fill py-2 text-white text-center"
                                     style="font-size:.75rem;background:{{ old('primary_color', $primaryColor) }};">Primary</div>
                                <div id="swatch_secondary" class="rounded flex-fill py-2 text-white text-center"
                                     style="font-size:.75rem;background:{{ old('secondary_color', $secondaryColor) }};">Secondary</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-sticky-note me-2"></i>Notes</h6>
                    </div>
                    <div class="card-body">
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $tenant->notes) }}</textarea>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.tenants.show', $tenant) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>

            </div>{{-- end right col --}}
        </div>{{-- end row --}}
    </form>
</div>
@endsection

@push('scripts')
<script>
function toggleTrialDate(status) {
    document.getElementById('trialDateWrapper').style.display = status === 'trial' ? '' : 'none';
}

function previewLogo(url) {
    const wrap = document.getElementById('logoPreview');
    const img  = wrap.querySelector('img');
    if (url && url.startsWith('http')) {
        img.src = url;
        wrap.classList.remove('d-none');
    } else {
        wrap.classList.add('d-none');
    }
}

function syncColorPicker(pickerId, hexValue) {
    if (/^#[0-9a-fA-F]{6}$/.test(hexValue)) {
        document.querySelector('[name="' + pickerId + '"]').value = hexValue;
    }
}

function updateColorSwatch(name, hex) {
    document.getElementById('swatch_' + name).style.background = hex;
}
</script>
@endpush
