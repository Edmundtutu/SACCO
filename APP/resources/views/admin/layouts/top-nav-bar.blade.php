<!-- Top Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm border-bottom">
    <div class="container-fluid">
        <!-- Sidebar Toggle Button (mobile) -->
        <button type="button" id="sidebarToggle" class="btn btn-outline-primary d-lg-none me-3">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Desktop Sidebar Toggle -->
        <button type="button" id="sidebarCollapse" class="btn btn-outline-primary d-none d-lg-inline-block me-3">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Page Title -->
        <div class="navbar-brand mb-0 h1 d-none d-md-block">
            <span class="text-muted">@yield('title', 'Admin Panel')</span>
        </div>

        <!-- Right Side Actions -->
        <div class="ms-auto d-flex align-items-center gap-2">

            {{-- ─── Tenant Switcher — super admin only ─── --}}
            @if(auth()->check() && auth()->user()->isSuperAdmin())
            @php
                $activeTenantNav = tenant();
                $allTenants = \App\Models\Tenant::orderBy('sacco_name')->get();
            @endphp
            <div class="dropdown" id="tenantSwitcherDropdown">
                <button class="btn btn-sm d-flex align-items-center gap-2 {{ $activeTenantNav ? 'tenant-chip' : 'btn-outline-secondary' }}"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false"
                        style="max-width:260px;">
                    @if($activeTenantNav)
                        @php $logo = $activeTenantNav->getSetting('logo_url'); @endphp
                        @if($logo)
                            <img src="{{ $logo }}" alt="" style="height:22px;width:22px;object-fit:contain;border-radius:3px;background:#fff;">
                        @else
                            <i class="fas fa-building"></i>
                        @endif
                        <span class="text-truncate" style="max-width:160px;">{{ $activeTenantNav->sacco_name }}</span>
                        <span class="badge bg-success rounded-pill" style="font-size:.6rem;">{{ strtoupper($activeTenantNav->status) }}</span>
                    @else
                        <i class="fas fa-building"></i>
                        <span>Select SACCO</span>
                    @endif
                    <i class="fas fa-chevron-down ms-1" style="font-size:.65rem;opacity:.6;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width:280px;max-height:400px;overflow-y:auto;">
                    <li class="px-3 py-2">
                        <input type="text" class="form-control form-control-sm" id="saccoSearchInput"
                               placeholder="Search SACCOs..." oninput="filterSaccoList(this.value)">
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <button class="dropdown-item d-flex align-items-center gap-2 {{ !$activeTenantNav ? 'active' : '' }}"
                                onclick="switchTenant(null)">
                            <i class="fas fa-th-large text-muted"></i>
                            <span>Platform Overview</span>
                            <small class="ms-auto text-muted">all SACCOs</small>
                        </button>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <div id="saccoListItems">
                        @foreach($allTenants as $t)
                        @php $tLogo = $t->getSetting('logo_url'); $tActive = ($activeTenantNav && $activeTenantNav->id == $t->id); @endphp
                        <li data-sacco-name="{{ strtolower($t->sacco_name) }}">
                            <button class="dropdown-item d-flex align-items-center gap-2 {{ $tActive ? 'active' : '' }}"
                                    onclick="switchTenant({{ $t->id }})">
                                @if($tLogo)
                                    <img src="{{ $tLogo }}" alt="" style="height:20px;width:20px;object-fit:contain;border-radius:3px;background:#f0f4f8;">
                                @else
                                    <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:3px;background:rgba(51,153,204,.15);">
                                        <i class="fas fa-building" style="font-size:.6rem;color:#3399CC;"></i>
                                    </span>
                                @endif
                                <span class="text-truncate" style="max-width:160px;">{{ $t->sacco_name }}</span>
                                @php $badgeColor = match($t->status) { 'active'=>'success','trial'=>'info','suspended'=>'danger', default=>'secondary' }; @endphp
                                <span class="badge bg-{{ $badgeColor }} ms-auto" style="font-size:.6rem;">{{ $t->status }}</span>
                            </button>
                        </li>
                        @endforeach
                    </div>
                    @if($allTenants->isEmpty())
                    <li><span class="dropdown-item text-muted">No SACCOs registered yet.</span></li>
                    @endif
                </ul>
            </div>
            @endif

            <!-- Notifications (Optional) -->
            <div class="dropdown me-3">
                <button class="btn btn-outline-secondary btn-sm position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                        3
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                    <li class="dropdown-header">
                        <i class="fas fa-bell me-2"></i>Notifications
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-user-plus text-success"></i>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <div class="fw-bold">New Member Registration</div>
                                    <small class="text-muted">John Doe has registered</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-hand-holding-usd text-warning"></i>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <div class="fw-bold">Loan Application</div>
                                    <small class="text-muted">New loan application pending</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-center" href="#">
                            <small>View all notifications</small>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- User Dropdown -->
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown">
                    <div class="avatar-sm me-2">
                        <div class="avatar-title bg-primary text-white rounded-circle">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-header">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-2">
                                <div class="avatar-title bg-primary text-white rounded-circle">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div>
                                <div class="fw-bold">{{ Auth::user()->name }}</div>
                                <small class="text-muted">{{ Auth::user()->email }}</small>
                            </div>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-user me-2"></i> Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-cog me-2"></i> Settings
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-question-circle me-2"></i> Help & Support
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('admin.logout') }}" method="POST" class="d-inline w-100">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

{{-- Tenant Switcher JavaScript --}}
@if(auth()->check() && auth()->user()->isSuperAdmin())
<script>
function switchTenant(tenantId) {
    fetch('/admin/tenants/switch', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ tenant_id: tenantId })
    })
    .then(r => r.json())
    .then(data => { if (data.success) window.location.reload(); })
    .catch(err => console.error('Tenant switch error:', err));
}

function clearTenant() {
    switchTenant(null);
}

function filterSaccoList(query) {
    const q = query.toLowerCase().trim();
    document.querySelectorAll('#saccoListItems li[data-sacco-name]').forEach(function(li) {
        li.style.display = (!q || li.dataset.saccoName.includes(q)) ? '' : 'none';
    });
}
</script>
@endif
