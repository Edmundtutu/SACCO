@php
    $sidebarTenant   = tenant();
    $sidebarUser     = auth()->user();
    $isSuperAdmin    = $sidebarUser?->isSuperAdmin();
    $tenantSelected  = (bool) $sidebarTenant;
    $lockNav         = $isSuperAdmin && !$tenantSelected;  // grey out tenant-specific items
@endphp

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    {{-- ───────────── Branding Header ───────────── --}}
    <div class="sidebar-header">
        <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
            @if($sidebarTenant)
                @php $logoUrl = $sidebarTenant->getSetting('logo_url'); @endphp
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $sidebarTenant->sacco_name }}"
                         style="max-height:38px;max-width:80px;object-fit:contain;border-radius:4px;background:#fff;padding:2px;">
                @else
                    <div style="width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-building text-white"></i>
                    </div>
                @endif
                <div class="min-w-0">
                    <div class="text-white fw-semibold text-truncate" style="font-size:.95rem;max-width:160px;">{{ $sidebarTenant->sacco_name }}</div>
                    <div class="d-flex align-items-center gap-1">
                        <small class="text-light opacity-75" style="font-size:.7rem;">{{ $sidebarTenant->sacco_code }}</small>
                        <span class="badge rounded-pill {{ $sidebarTenant->isActive() ? 'bg-success' : ($sidebarTenant->isOnTrial() ? 'bg-warning text-dark' : 'bg-danger') }}"
                              style="font-size:.6rem;padding:2px 5px;">{{ strtoupper($sidebarTenant->status) }}</span>
                    </div>
                </div>
            @else
                <div>
                    <h4 class="text-white mb-0" style="font-size:1rem;">SACCO Admin</h4>
                    <small class="text-light opacity-75">Accountable Value Suite</small>
                </div>
            @endif
        </div>
        <button class="sidebar-toggle d-lg-none" id="sidebarToggle">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <ul class="list-unstyled components">
        {{-- ─── Dashboard ─── --}}
        <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}" data-tooltip="Dashboard">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        {{-- ─── Administration ─── --}}
        <li class="sidebar-label"><span>Administration</span></li>
        @if($isSuperAdmin)
        <li class="{{ request()->routeIs('admin.tenants.*') ? 'active' : '' }}">
            <a href="{{ route('admin.tenants.index') }}" data-tooltip="SACCO Management">
                <i class="fas fa-building"></i>
                <span>SACCO Management</span>
            </a>
        </li>
        @endif

        @if(auth()->check() && (auth()->user()->role === 'admin' || auth()->user()->isSuperAdmin()))
        @if($lockNav)
        <li class="nav-disabled-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Select a SACCO first">
            <span class="nav-disabled-link"><i class="bi bi-people"></i> <span>Staff Management</span></span>
        </li>
        @else
        <li class="{{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
            <a href="{{ route('admin.staff.index') }}" data-tooltip="Staff Management">
                <i class="bi bi-people"></i> <span>Staff Management</span>
            </a>
        </li>
        @endif
        @endif

        @if($isSuperAdmin)
        <li class="sidebar-label"><span>{{ $tenantSelected ? strtoupper($sidebarTenant->sacco_name) : 'Tenant Context Required' }}</span></li>
        @endif

        {{-- ─── Operations (tenant-scoped) ─── --}}
        <li class="sidebar-label"><span>Operations</span></li>

        {{-- ─── Members ─── --}}
        @if($lockNav)
        <li class="nav-disabled-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Select a SACCO first">
            <span class="nav-disabled-link"><i class="fas fa-users"></i> <span>Members</span></span>
        </li>
        @else
        <li class="has-submenu {{ request()->routeIs('admin.members.*') ? 'active' : '' }}">
            <a href="#" class="submenu-toggle" data-tooltip="Members">
                <i class="fas fa-users"></i> <span>Members</span>
            </a>
            <ul class="submenu list-unstyled">
                <li class="{{ request()->routeIs('admin.members.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.members.index') }}"><i class="fas fa-list"></i> All Members</a>
                </li>
                <li class="{{ request()->routeIs('admin.members.create') ? 'active' : '' }}">
                    <a href="{{ route('admin.members.create') }}"><i class="fas fa-user-plus"></i> Add Member</a>
                </li>
                <li class="{{ request()->routeIs('admin.members.requests') ? 'active' : '' }}">
                    <a href="{{ route('admin.members.requests') }}"><i class="fas fa-user-clock"></i> Membership Requests</a>
                </li>
            </ul>
        </li>
        @endif

        {{-- ─── Savings ─── --}}
        @if($lockNav)
        <li class="nav-disabled-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Select a SACCO first">
            <span class="nav-disabled-link"><i class="fas fa-piggy-bank"></i> <span>Savings</span></span>
        </li>
        @else
        <li class="has-submenu {{ request()->routeIs('admin.savings.*') ? 'active' : '' }}">
            <a href="#" class="submenu-toggle" data-tooltip="Savings">
                <i class="fas fa-piggy-bank"></i> <span>Savings</span>
            </a>
            <ul class="submenu list-unstyled">
                <li class="{{ request()->routeIs('admin.savings.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.savings.index') }}"><i class="fas fa-chart-line"></i> Savings Overview</a>
                </li>
                <li class="{{ request()->routeIs('admin.savings.accounts') ? 'active' : '' }}">
                    <a href="{{ route('admin.savings.accounts') }}"><i class="fas fa-wallet"></i> Savings Accounts</a>
                </li>
                <li class="{{ request()->routeIs('admin.savings.transactions') ? 'active' : '' }}">
                    <a href="{{ route('admin.savings.transactions') }}"><i class="fas fa-exchange-alt"></i> Transactions</a>
                </li>
                <li class="{{ request()->routeIs('admin.savings.products') ? 'active' : '' }}">
                    <a href="{{ route('admin.savings.products') }}"><i class="fas fa-cogs"></i> Products</a>
                </li>
            </ul>
        </li>
        @endif

        {{-- ─── Loans ─── --}}
        @if($lockNav)
        <li class="nav-disabled-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Select a SACCO first">
            <span class="nav-disabled-link"><i class="fas fa-hand-holding-usd"></i> <span>Loans</span></span>
        </li>
        @else
        <li class="has-submenu {{ request()->routeIs('admin.loans.*') || request()->routeIs('admin.loan-products.*') ? 'active' : '' }}">
            <a href="#" class="submenu-toggle" data-tooltip="Loans">
                <i class="fas fa-hand-holding-usd"></i> <span>Loans</span>
            </a>
            <ul class="submenu list-unstyled">
                <li class="{{ request()->routeIs('admin.loans.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.loans.index') }}"><i class="fas fa-list"></i> All Loans</a>
                </li>
                <li class="{{ request()->routeIs('admin.loans.applications') ? 'active' : '' }}">
                    <a href="{{ route('admin.loans.applications') }}"><i class="fas fa-file-alt"></i> Applications</a>
                </li>
                <li class="{{ request()->routeIs('admin.loans.create') ? 'active' : '' }}">
                    <a href="{{ route('admin.loans.create') }}"><i class="fas fa-plus-circle"></i> Create Loan</a>
                </li>
                <li class="{{ request()->routeIs('admin.loans.products') ? 'active' : '' }}">
                    <a href="{{ route('admin.loans.products') }}"><i class="fas fa-cogs"></i> Loan Products</a>
                </li>
            </ul>
        </li>
        @endif

        {{-- ─── Shares ─── --}}
        @if($lockNav)
        <li class="nav-disabled-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Select a SACCO first">
            <span class="nav-disabled-link"><i class="fas fa-chart-pie"></i> <span>Shares</span></span>
        </li>
        @else
        <li class="has-submenu {{ request()->routeIs('admin.shares.*') ? 'active' : '' }}">
            <a href="#" class="submenu-toggle" data-tooltip="Shares">
                <i class="fas fa-chart-pie"></i> <span>Shares</span>
            </a>
            <ul class="submenu list-unstyled">
                <li class="{{ request()->routeIs('admin.shares.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.shares.index') }}"><i class="fas fa-chart-pie"></i> Share Overview</a>
                </li>
                <li class="{{ request()->routeIs('admin.shares.purchases') ? 'active' : '' }}">
                    <a href="{{ route('admin.shares.purchases') }}"><i class="fas fa-shopping-cart"></i> Share Purchases</a>
                </li>
                <li class="{{ request()->routeIs('admin.shares.dividends') ? 'active' : '' }}">
                    <a href="{{ route('admin.shares.dividends') }}"><i class="fas fa-gift"></i> Dividends</a>
                </li>
            </ul>
        </li>
        @endif

        {{-- ─── Accounting ─── --}}
        <li class="sidebar-label"><span>Accounting</span></li>
        @if($lockNav)
        <li class="nav-disabled-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Select a SACCO first">
            <span class="nav-disabled-link"><i class="fas fa-exchange-alt"></i> <span>Accounting Transactions</span></span>
        </li>
        @else
        <li class="has-submenu {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
            <a href="#" class="submenu-toggle" data-tooltip="Accounting Transactions">
                <i class="fas fa-exchange-alt"></i> <span>Accounting Transactions</span>
            </a>
            <ul class="submenu list-unstyled">
                <li class="{{ request()->routeIs('admin.transactions.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.transactions.index') }}"><i class="fas fa-list"></i> All Transactions</a>
                </li>
                <li class="{{ request()->routeIs('admin.transactions.general-ledger') ? 'active' : '' }}">
                    <a href="{{ route('admin.transactions.general-ledger') }}"><i class="fas fa-book"></i> General Ledger</a>
                </li>
                <li class="{{ request()->routeIs('admin.transactions.trial-balance') ? 'active' : '' }}">
                    <a href="{{ route('admin.transactions.trial-balance') }}"><i class="fas fa-balance-scale"></i> Trial Balance</a>
                </li>
            </ul>
        </li>
        @endif

        {{-- ─── Reports ─── --}}
        <li class="sidebar-label"><span>Reports</span></li>
        @if($lockNav)
        <li class="nav-disabled-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Select a SACCO first">
            <span class="nav-disabled-link"><i class="fas fa-chart-bar"></i> <span>Reports</span></span>
        </li>
        @else
        <li class="has-submenu {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            <a href="#" class="submenu-toggle" data-tooltip="Reports">
                <i class="fas fa-chart-bar"></i> <span>Reports</span>
            </a>
            <ul class="submenu list-unstyled">
                <li class="{{ request()->routeIs('admin.reports.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.reports.index') }}"><i class="fas fa-chart-line"></i> General Reports</a>
                </li>
                <li class="{{ request()->routeIs('admin.reports.members') ? 'active' : '' }}">
                    <a href="{{ route('admin.reports.members') }}"><i class="fas fa-users"></i> Member Reports</a>
                </li>
                <li class="{{ request()->routeIs('admin.reports.savings') ? 'active' : '' }}">
                    <a href="{{ route('admin.reports.savings') }}"><i class="fas fa-piggy-bank"></i> Savings Reports</a>
                </li>
                <li class="{{ request()->routeIs('admin.reports.loans') ? 'active' : '' }}">
                    <a href="{{ route('admin.reports.loans') }}"><i class="fas fa-hand-holding-usd"></i> Loan Reports</a>
                </li>
                <li class="{{ request()->routeIs('admin.reports.financial') ? 'active' : '' }}">
                    <a href="{{ route('admin.reports.financial') }}"><i class="fas fa-calculator"></i> Financial Reports</a>
                </li>
                @if(config('financial.enable_expense_transactions'))
                <li class="{{ request()->routeIs('admin.reports.expenses') ? 'active' : '' }}">
                    <a href="{{ route('admin.reports.expenses') }}"><i class="fas fa-file-invoice-dollar"></i> Expense Reports</a>
                </li>
                @endif
                @if(config('financial.enable_income_transactions'))
                <li class="{{ request()->routeIs('admin.reports.incomes') ? 'active' : '' }}">
                    <a href="{{ route('admin.reports.incomes') }}"><i class="fas fa-hand-holding-usd"></i> Income Reports</a>
                </li>
                @endif
                @if(config('financial.enable_expense_transactions') || config('financial.enable_income_transactions'))
                <li class="{{ request()->routeIs('admin.reports.profit-loss') ? 'active' : '' }}">
                    <a href="{{ route('admin.reports.profit-loss') }}"><i class="fas fa-balance-scale"></i> Profit & Loss</a>
                </li>
                @endif
            </ul>
        </li>
        @endif

        {{-- ─── Phase 2: Expenses ─── --}}
        @if(config('financial.enable_expense_transactions'))
        @if($lockNav)
        <li class="nav-disabled-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Select a SACCO first">
            <span class="nav-disabled-link"><i class="fas fa-file-invoice-dollar"></i> <span>Expenses</span></span>
        </li>
        @else
        <li class="has-submenu {{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}">
            <a href="#" class="submenu-toggle" data-tooltip="Expenses">
                <i class="fas fa-file-invoice-dollar"></i> <span>Expenses</span>
            </a>
            <ul class="submenu list-unstyled">
                <li class="{{ request()->routeIs('admin.expenses.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.expenses.index') }}"><i class="fas fa-list"></i> All Expenses</a>
                </li>
                <li class="{{ request()->routeIs('admin.expenses.create') ? 'active' : '' }}">
                    <a href="{{ route('admin.expenses.create') }}"><i class="fas fa-plus-circle"></i> Record Expense</a>
                </li>
            </ul>
        </li>
        @endif
        @endif

        {{-- ─── Phase 2: Income ─── --}}
        @if(config('financial.enable_income_transactions'))
        @if($lockNav)
        <li class="nav-disabled-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Select a SACCO first">
            <span class="nav-disabled-link"><i class="fas fa-hand-holding-usd"></i> <span>Income</span></span>
        </li>
        @else
        <li class="has-submenu {{ request()->routeIs('admin.incomes.*') ? 'active' : '' }}">
            <a href="#" class="submenu-toggle" data-tooltip="Income">
                <i class="fas fa-hand-holding-usd"></i> <span>Income</span>
            </a>
            <ul class="submenu list-unstyled">
                <li class="{{ request()->routeIs('admin.incomes.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.incomes.index') }}"><i class="fas fa-list"></i> All Income</a>
                </li>
                <li class="{{ request()->routeIs('admin.incomes.create') ? 'active' : '' }}">
                    <a href="{{ route('admin.incomes.create') }}"><i class="fas fa-plus-circle"></i> Record Income</a>
                </li>
            </ul>
        </li>
        @endif
        @endif

    </ul>
</nav>

<style>
/* Greyed-out nav items when in super-admin neutral mode */
.sidebar .nav-disabled-item {
    opacity: .42;
    cursor: not-allowed;
    pointer-events: none;
}
.sidebar .nav-disabled-link {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .75rem 1.25rem;
    color: rgba(255,255,255,.7);
    font-size: .9rem;
}
.sidebar .nav-disabled-link i { width: 1.2rem; text-align: center; }

/* Section label */
.sidebar .sidebar-label {
    padding: .4rem 1rem .1rem;
    pointer-events: none;
}
.sidebar .sidebar-label span {
    font-size: .65rem;
    font-weight: 600;
    letter-spacing: .08em;
    color: rgba(255,255,255,.5);
    text-transform: uppercase;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialise Bootstrap tooltips on disabled nav items
    document.querySelectorAll('.nav-disabled-item[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el, { trigger: 'hover' });
    });

    const sidebar        = document.getElementById('sidebar');
    const sidebarToggle  = document.getElementById('sidebarToggle');
    const sidebarCollapse = document.getElementById('sidebarCollapse');
    const submenuToggles = document.querySelectorAll('.submenu-toggle');
    const menuItems      = document.querySelectorAll('.has-submenu');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
        });
    }
    if (sidebarCollapse) {
        sidebarCollapse.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');
            document.body.classList.toggle('sidebar-collapsed');
        });
    }
    document.addEventListener('click', function (e) {
        if (window.innerWidth < 992 &&
            !sidebar.contains(e.target) &&
            !e.target.closest('#sidebarToggle') &&
            !e.target.closest('.navbar-toggler')) {
            sidebar.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        }
    });
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) {
            sidebar.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        }
    });
    submenuToggles.forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const parent = this.closest('.has-submenu');
            menuItems.forEach(function (item) {
                if (item !== parent) item.classList.remove('active');
            });
            parent.classList.toggle('active');
        });
    });
    // Keep parent open if a child route is active
    menuItems.forEach(function (item) {
        if (item.querySelector('.submenu .active')) item.classList.add('active');
    });
    document.querySelectorAll('.submenu a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth < 992) {
                setTimeout(function () {
                    sidebar.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                }, 150);
            }
        });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && window.innerWidth < 992) {
            sidebar.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        }
    });
});
</script>
