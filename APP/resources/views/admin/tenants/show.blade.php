@extends('admin.layouts.app')

@section('title', $tenant->sacco_name . ' — SACCO Detail')

@section('content')
@php
    $logoUrl        = $tenant->getSetting('logo_url', '');
    $primaryColor   = $tenant->getSetting('primary_color', '#3399CC');
    $secondaryColor = $tenant->getSetting('secondary_color', '#2E86AB');
    $statusColors   = ['active' => 'success', 'trial' => 'info', 'suspended' => 'danger', 'inactive' => 'secondary'];
    $statusColor    = $statusColors[$tenant->status] ?? 'secondary';
    $planColors     = ['basic' => 'secondary', 'standard' => 'primary', 'premium' => 'warning', 'enterprise' => 'dark'];
    $planColor      = $planColors[$tenant->subscription_plan] ?? 'secondary';
@endphp

<div class="container-fluid">

    {{-- ── Hero Header ──────────────────────────────────────────────── --}}
    <div class="card shadow-sm mb-4 border-0"
         style="background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);">
        <div class="card-body py-4 px-4">
            <div class="d-flex align-items-start gap-4 flex-wrap">
                {{-- Logo / icon --}}
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $tenant->sacco_name }}"
                         style="width:80px;height:80px;object-fit:contain;border-radius:12px;background:rgba(255,255,255,.9);padding:8px;flex-shrink:0;">
                @else
                    <div style="width:80px;height:80px;border-radius:12px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-building fa-2x text-white"></i>
                    </div>
                @endif

                {{-- Name, code, badges --}}
                <div class="flex-grow-1 min-w-0">
                    <h2 class="text-white mb-1 fw-bold">{{ $tenant->sacco_name }}</h2>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <code class="bg-white bg-opacity-25 text-white px-2 py-0 rounded" style="font-size:.8rem;">{{ $tenant->sacco_code }}</code>
                        <span class="badge bg-{{ $statusColor }}">{{ ucfirst($tenant->status) }}</span>
                        <span class="badge bg-{{ $planColor }}">{{ ucfirst($tenant->subscription_plan) }}</span>
                        @if($tenant->trial_ends_at)
                            <span class="badge bg-white text-dark" style="font-size:.7rem;">
                                Trial ends {{ $tenant->trial_ends_at->format('M d, Y') }}
                            </span>
                        @endif
                    </div>
                    <p class="text-white opacity-75 mt-1 mb-0 small">
                        <i class="fas fa-map-marker-alt me-1"></i>{{ $tenant->address ?? $tenant->country }}
                        &ensp;&bull;&ensp;
                        <i class="fas fa-envelope me-1"></i>{{ $tenant->email }}
                        &ensp;&bull;&ensp;
                        <i class="fas fa-phone me-1"></i>{{ $tenant->phone }}
                    </p>
                </div>

                {{-- Action buttons --}}
                <div class="d-flex gap-2 flex-shrink-0 flex-wrap">
                    <button type="button" class="btn btn-light btn-sm" onclick="heroSwitch({{ $tenant->id }})">
                        <i class="fas fa-sign-in-alt me-1"></i> Switch to SACCO
                    </button>
                    <a href="{{ route('admin.tenants.edit', $tenant) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">

        {{-- ── Left column ────────────────────────────────────────────── --}}
        <div class="col-lg-8">

            {{-- Stats row --}}
            <div class="row g-3 mb-4">
                <div class="col-sm-4">
                    <div class="card shadow-sm h-100 text-center py-3">
                        <div class="h3 fw-bold text-primary mb-0">{{ number_format($tenant->users_count) }}</div>
                        <div class="text-muted small">Total Users</div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card shadow-sm h-100 text-center py-3">
                        <div class="h3 fw-bold text-success mb-0">{{ number_format($tenant->loans_count) }}</div>
                        <div class="text-muted small">Loans</div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card shadow-sm h-100 text-center py-3">
                        <div class="h3 fw-bold text-info mb-0">{{ number_format($tenant->transactions_count) }}</div>
                        <div class="text-muted small">Transactions</div>
                    </div>
                </div>
            </div>

            {{-- Contact & Location --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-address-card me-2"></i>Contact &amp; Location</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Email</div>
                            <div>{{ $tenant->email }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Phone</div>
                            <div>{{ $tenant->phone }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Country</div>
                            <div>{{ $tenant->country }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Currency</div>
                            <div>{{ $tenant->currency }}</div>
                        </div>
                        @if($tenant->address)
                        <div class="col-12">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Address</div>
                            <div>{{ $tenant->address }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Subscription details --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-tags me-2"></i>Subscription</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Plan</div>
                            <span class="badge bg-{{ $planColor }} fs-6">{{ ucfirst($tenant->subscription_plan) }}</span>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Status</div>
                            <span class="badge bg-{{ $statusColor }} fs-6">{{ ucfirst($tenant->status) }}</span>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Registered</div>
                            <div>{{ $tenant->created_at->format('M d, Y') }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Trial Ends</div>
                            <div>{{ $tenant->trial_ends_at ? $tenant->trial_ends_at->format('M d, Y') : '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Limits & Usage --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-sliders-h me-2"></i>Limits &amp; Usage</h6>
                </div>
                <div class="card-body">
                    @php
                        $memberPct = $tenant->max_members > 0 ? min(100, round(($tenant->users_count / $tenant->max_members) * 100)) : 0;
                        $loanPct   = $tenant->max_loans   > 0 ? min(100, round(($tenant->loans_count   / $tenant->max_loans)   * 100)) : 0;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-semibold">Members</span>
                            <span class="text-muted small">{{ number_format($tenant->users_count) }} / {{ number_format($tenant->max_members) }}</span>
                        </div>
                        <div class="progress" style="height:8px;">
                            <div class="progress-bar bg-{{ $memberPct >= 90 ? 'danger' : ($memberPct >= 70 ? 'warning' : 'primary') }}"
                                 style="width:{{ $memberPct }}%;" role="progressbar"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-semibold">Loans</span>
                            <span class="text-muted small">{{ number_format($tenant->loans_count) }} / {{ number_format($tenant->max_loans) }}</span>
                        </div>
                        <div class="progress" style="height:8px;">
                            <div class="progress-bar bg-{{ $loanPct >= 90 ? 'danger' : ($loanPct >= 70 ? 'warning' : 'success') }}"
                                 style="width:{{ $loanPct }}%;" role="progressbar"></div>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Max Staff</div>
                            <div>{{ number_format($tenant->max_staff) }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Max Loan Amount</div>
                            <div>{{ $tenant->max_loan_amount ? number_format($tenant->max_loan_amount) . ' ' . $tenant->currency : 'Unlimited' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Transactions</div>
                            <div>{{ number_format($tenant->transactions_count) }}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- end left col --}}

        {{-- ── Right column ───────────────────────────────────────────── --}}
        <div class="col-lg-4">

            {{-- Owner / Contact --}}
            @if($tenant->owner_name || $tenant->owner_email || $tenant->owner_phone)
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-tie me-2"></i>Owner / Contact</h6>
                </div>
                <div class="card-body">
                    @if($tenant->owner_name)
                    <div class="mb-2">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Name</div>
                        <div>{{ $tenant->owner_name }}</div>
                    </div>
                    @endif
                    @if($tenant->owner_email)
                    <div class="mb-2">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Email</div>
                        <div>{{ $tenant->owner_email }}</div>
                    </div>
                    @endif
                    @if($tenant->owner_phone)
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Phone</div>
                        <div>{{ $tenant->owner_phone }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Branding preview --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-palette me-2"></i>Branding</h6>
                </div>
                <div class="card-body">
                    @if($logoUrl)
                    <div class="mb-3 text-center">
                        <img src="{{ $logoUrl }}" alt="{{ $tenant->sacco_name }} logo"
                             style="max-height:80px;max-width:160px;object-fit:contain;border:1px solid #dee2e6;border-radius:6px;padding:8px;background:#f8f9fa;">
                    </div>
                    @else
                    <div class="mb-3 text-center text-muted small">No logo configured</div>
                    @endif

                    <div class="d-flex gap-2">
                        <div class="flex-fill rounded py-3 text-center text-white fw-semibold"
                             style="background:{{ $primaryColor }};font-size:.75rem;">
                            <div style="opacity:.7;font-size:.65rem;text-transform:uppercase;">Primary</div>
                            {{ $primaryColor }}
                        </div>
                        <div class="flex-fill rounded py-3 text-center text-white fw-semibold"
                             style="background:{{ $secondaryColor }};font-size:.75rem;">
                            <div style="opacity:.7;font-size:.65rem;text-transform:uppercase;">Secondary</div>
                            {{ $secondaryColor }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            @if($tenant->notes)
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-sticky-note me-2"></i>Notes</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0" style="white-space:pre-line;">{{ $tenant->notes }}</p>
                </div>
            </div>
            @endif

            {{-- Meta info --}}
            <div class="card shadow-sm mb-4 border-0 bg-light">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Created</span>
                        <span class="small">{{ $tenant->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Last Updated</span>
                        <span class="small">{{ $tenant->updated_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Slug</span>
                        <code class="small">{{ $tenant->slug }}</code>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-grid gap-2">
                <a href="{{ route('admin.tenants.edit', $tenant) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit SACCO
                </a>
                <button type="button" class="btn btn-outline-primary w-100" onclick="heroSwitch({{ $tenant->id }})">
                    <i class="fas fa-sign-in-alt me-1"></i> Switch to This SACCO
                </button>
                <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>

        </div>{{-- end right col --}}
    </div>{{-- end row --}}
</div>
@endsection

@push('scripts')
<script>
function heroSwitch(tenantId) {
    fetch('{{ route("admin.tenants.switch") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ tenant_id: tenantId })
    })
    .then(r => r.json())
    .then(data => { if (data.success) window.location.href = '{{ route("admin.dashboard") }}'; })
    .catch(err => console.error('Switch error:', err));
}
</script>
@endpush
