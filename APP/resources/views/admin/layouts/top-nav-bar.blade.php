<!-- Top Navigation -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm border-bottom">
    <div class="container-fluid">
        <!-- Sidebar Toggle Button -->
        <button type="button" id="sidebarToggle" class="btn btn-outline-primary d-lg-none me-3">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Desktop Sidebar Toggle -->
        <button type="button" id="sidebarCollapse" class="btn btn-outline-primary d-none d-lg-inline-block me-3">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Page Title (Optional) -->
        <div class="navbar-brand mb-0 h1 d-none d-md-block">
            <span class="text-muted">@yield('title', 'Admin Panel')</span>
        </div>

        <!-- Right Side Actions -->
        <div class="ms-auto d-flex align-items-center">
            <!-- Tenant Switcher (Super Admin Only) -->
            @if(auth()->check() && auth()->user()->role === 'super_admin')
            <div class="me-3">
                <select id="tenant-switch" class="form-select form-select-sm" style="min-width: 200px;">
                    <option value="">-- All Tenants --</option>
                    @foreach(\App\Models\Tenant::withoutGlobalScope('tenant')->where('status', 'active')->get() as $t)
                        <option value="{{ $t->id }}" {{ (session('admin_tenant_id') == $t->id) ? 'selected' : '' }}>
                            {{ $t->sacco_name }} ({{ $t->sacco_code }})
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Current Tenant Indicator -->
            @if(tenant())
            <div class="alert alert-info mb-0 me-3 py-1 px-2 d-flex align-items-center" style="font-size: 0.875rem;">
                <i class="bi bi-building me-2"></i>
                <span>{{ tenant()->sacco_name }}</span>
                <button class="btn btn-sm btn-link text-decoration-none ms-2 p-0" onclick="clearTenant()" title="View All">
                    <i class="bi bi-x-circle"></i>
                </button>
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

{{-- Tenant Switcher JavaScript (Super Admin) --}}
@if(auth()->check() && auth()->user()->role === 'super_admin')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tenantSwitch = document.getElementById('tenant-switch');
    if (tenantSwitch) {
        tenantSwitch.addEventListener('change', function() {
            const tenantId = this.value;
            fetch('/admin/tenants/switch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ tenant_id: tenantId || null })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
});

function clearTenant() {
    fetch('/admin/tenants/switch', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ tenant_id: null })
    })
    .then(() => window.location.reload());
}
</script>
@endif
