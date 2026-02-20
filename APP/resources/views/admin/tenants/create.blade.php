@extends('admin.layouts.app')

@section('title', 'Add New SACCO')

@section('content')
<div class="container-fluid">

    {{-- Page header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Add New SACCO</h1>
            <p class="text-muted mb-0">Register a new tenant on the platform</p>
        </div>
        <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
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

    <form method="POST" action="{{ route('admin.tenants.store') }}" novalidate>
        @csrf

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
                                       value="{{ old('sacco_name') }}" placeholder="e.g. Kampala Savings & Credit Co-operative" required>
                                @error('sacco_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">SACCO Code <small class="text-muted fw-normal">(auto-generated)</small></label>
                                <input type="text" name="sacco_code" class="form-control @error('sacco_code') is-invalid @enderror"
                                       value="{{ old('sacco_code') }}" placeholder="e.g. KSC-001">
                                @error('sacco_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" placeholder="admin@sacco.ug" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone') }}" placeholder="+256 700 000000" required>
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Physical Address</label>
                                <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                          rows="2" placeholder="Plot 12, Kampala Road, Kampala">{{ old('address') }}</textarea>
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
                                <label class="form-label fw-semibold">Country <span class="text-danger">*</span></label>
                                <input type="text" name="country" class="form-control @error('country') is-invalid @enderror"
                                       value="{{ old('country', 'Uganda') }}" required>
                                @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Currency <span class="text-danger">*</span></label>
                                <input type="text" name="currency" class="form-control @error('currency') is-invalid @enderror"
                                       value="{{ old('currency', 'UGX') }}" placeholder="UGX" required maxlength="10">
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
                                        <option value="{{ $val }}" {{ old('subscription_plan','standard') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('subscription_plan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" id="statusSelect" class="form-select @error('status') is-invalid @enderror" required
                                        onchange="toggleTrialDate(this.value)">
                                    @foreach(['trial'=>'Trial','active'=>'Active','suspended'=>'Suspended','inactive'=>'Inactive'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('status','trial') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4" id="trialDateWrapper" style="{{ old('status','trial') !== 'trial' ? 'display:none' : '' }}">
                                <label class="form-label fw-semibold">Trial Ends At</label>
                                <input type="date" name="trial_ends_at" class="form-control @error('trial_ends_at') is-invalid @enderror"
                                       value="{{ old('trial_ends_at', now()->addDays(30)->format('Y-m-d')) }}">
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
                                       value="{{ old('max_members', 500) }}" min="1" required>
                                @error('max_members')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Max Staff <span class="text-danger">*</span></label>
                                <input type="number" name="max_staff" class="form-control @error('max_staff') is-invalid @enderror"
                                       value="{{ old('max_staff', 20) }}" min="1" required>
                                @error('max_staff')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Max Active Loans <span class="text-danger">*</span></label>
                                <input type="number" name="max_loans" class="form-control @error('max_loans') is-invalid @enderror"
                                       value="{{ old('max_loans', 200) }}" min="1" required>
                                @error('max_loans')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Max Loan Amount</label>
                                <input type="number" name="max_loan_amount" class="form-control @error('max_loan_amount') is-invalid @enderror"
                                       value="{{ old('max_loan_amount') }}" min="0" placeholder="Unlimited">
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
                                       value="{{ old('owner_name') }}" placeholder="Full name">
                                @error('owner_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="owner_email" class="form-control @error('owner_email') is-invalid @enderror"
                                       value="{{ old('owner_email') }}" placeholder="owner@example.com">
                                @error('owner_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="owner_phone" class="form-control @error('owner_phone') is-invalid @enderror"
                                       value="{{ old('owner_phone') }}" placeholder="+256 ...">
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
                                       value="{{ old('logo_url') }}" placeholder="https://..."
                                       oninput="previewLogo(this.value)">
                                @error('logo_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div id="logoPreview" class="mt-2 {{ old('logo_url') ? '' : 'd-none' }}">
                                    <img src="{{ old('logo_url', '') }}" alt="Logo preview"
                                         style="max-height:60px;max-width:120px;object-fit:contain;border:1px solid #dee2e6;border-radius:4px;padding:4px;background:#f8f9fa;">
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Primary Colour</label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" name="primary_color_picker" class="form-control form-control-color"
                                               value="{{ old('primary_color', '#3399CC') }}"
                                               oninput="document.getElementById('primary_color').value=this.value;updateColorSwatch('primary',this.value)">
                                        <input type="text" name="primary_color" id="primary_color"
                                               class="form-control @error('primary_color') is-invalid @enderror"
                                               value="{{ old('primary_color', '#3399CC') }}" placeholder="#3399CC"
                                               oninput="syncColorPicker('primary_color_picker',this.value)">
                                    </div>
                                    @error('primary_color')<div class="text-danger" style="font-size:.8rem;">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Secondary Colour</label>
                                    <div class="input-group input-group-sm">
                                        <input type="color" name="secondary_color_picker" class="form-control form-control-color"
                                               value="{{ old('secondary_color', '#2E86AB') }}"
                                               oninput="document.getElementById('secondary_color').value=this.value;updateColorSwatch('secondary',this.value)">
                                        <input type="text" name="secondary_color" id="secondary_color"
                                               class="form-control @error('secondary_color') is-invalid @enderror"
                                               value="{{ old('secondary_color', '#2E86AB') }}" placeholder="#2E86AB"
                                               oninput="syncColorPicker('secondary_color_picker',this.value)">
                                    </div>
                                    @error('secondary_color')<div class="text-danger" style="font-size:.8rem;">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            {{-- Colour preview swatches --}}
                            <div class="d-flex gap-2 mt-1">
                                <div id="swatch_primary" class="rounded flex-fill py-2 text-white text-center" style="font-size:.75rem;background:{{ old('primary_color','#3399CC') }};">Primary</div>
                                <div id="swatch_secondary" class="rounded flex-fill py-2 text-white text-center" style="font-size:.75rem;background:{{ old('secondary_color','#2E86AB') }};">Secondary</div>
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
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Internal notes about this SACCO">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-1"></i> Create SACCO
                    </button>
                    <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
