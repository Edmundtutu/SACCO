<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="d-flex align-items-center">
            <div>
                <h4 class="text-white mb-0">SACCO Admin</h4>
                <small class="text-light opacity-75">Accountable Value Suite</small>
            </div>
        </div>
        <button class="sidebar-toggle d-lg-none" id="sidebarToggle">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <ul class="list-unstyled components">
        <!-- Dashboard -->
        <li class="{{request()->routeIs('admin.dashboard') ? 'active' : ''}}">
            <a href="{{route('admin.dashboard')}}" data-tooltip="Dashboard">
                <i class="fas fa-tachometer-alt"></i> 
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Members -->
        <li class="has-submenu {{request()->routeIs('admin.members.*') ? 'active' : ''}}">
            <a href="#" class="submenu-toggle" data-tooltip="Members">
                <i class="fas fa-users"></i> 
                <span>Members</span>
            </a>
            <ul class="submenu list-unstyled">
                <li class="{{request()->routeIs('admin.members.index') ? 'active' : ''}}">
                    <a href="{{route('admin.members.index')}}">
                        <i class="fas fa-list"></i> All Members
                    </a>
                </li>
                <li class="{{request()->routeIs('admin.members.create') ? 'active' : ''}}">
                    <a href="{{route('admin.members.create')}}">
                        <i class="fas fa-user-plus"></i> Add New Member
                    </a>
                </li>
                <li class="{{request()->routeIs('admin.members.requests') ? 'active' : ''}}">
                    <a href="{{route('admin.members.requests')}}">
                        <i class="fas fa-user-clock"></i> Membership Requests
                    </a>
                </li>
            </ul>
        </li>

        <!-- Savings -->
        <li class="has-submenu {{request()->routeIs('admin.savings.*') ? 'active' : ''}}">
            <a href="#" class="submenu-toggle" data-tooltip="Savings">
                <i class="fas fa-piggy-bank"></i> 
                <span>Savings</span>
            </a>
            <ul class="submenu list-unstyled">
                <li class="{{request()->routeIs('admin.savings.index') ? 'active' : ''}}">
                    <a href="{{route('admin.savings.index')}}">
                        <i class="fas fa-chart-line"></i> Savings Overview
                    </a>
                </li>
                <li class="{{request()->routeIs('admin.savings.accounts') ? 'active' : ''}}">
                    <a href="{{route('admin.savings.accounts')}}">
                        <i class="fas fa-wallet"></i> Savings Accounts
                    </a>
                </li>
                <li class="{{request()->routeIs('admin.savings.transactions') ? 'active' : ''}}">
                    <a href="{{route('admin.savings.transactions')}}">
                        <i class="fas fa-exchange-alt"></i> Transactions
                    </a>
                </li>
                <li class="{{request()->routeIs('admin.savings.products') ? 'active' : ''}}">
                    <a href="{{route('admin.savings.products')}}">
                        <i class="fas fa-cogs"></i> Products
                    </a>
                </li>
            </ul>
        </li>

        <!-- Loans -->
        <li class="has-submenu {{request()->routeIs('admin.loans.*') || request()->routeIs('admin.loan-products.*') ? 'active' : ''}}">
            <a href="#" class="submenu-toggle" data-tooltip="Loans">
                <i class="fas fa-hand-holding-usd"></i> 
                <span>Loans</span>
            </a>
            <ul class="submenu list-unstyled">
                <li class="{{request()->routeIs('admin.loans.index') ? 'active' : ''}}">
                    <a href="{{route('admin.loans.index')}}">
                        <i class="fas fa-list"></i> All Loans
                    </a>
                </li>
                <li class="{{request()->routeIs('admin.loans.applications') ? 'active' : ''}}">
                    <a href="{{route('admin.loans.applications')}}">
                        <i class="fas fa-file-alt"></i> Applications
                    </a>
                </li>
                <li class="{{request()->routeIs('admin.loans.create') ? 'active' : ''}}">
                    <a href="{{route('admin.loans.create')}}">
                        <i class="fas fa-plus-circle"></i> Create Loan
                    </a>
                </li>
                <li class="{{request()->routeIs('admin.loans.products') ? 'active' : ''}}">
                    <a href="{{route('admin.loans.products')}}">
                        <i class="fas fa-cogs"></i> Loan Products
                    </a>
                </li>
            </ul>
        </li>

        <!-- Shares -->
        <li class="has-submenu {{request()->routeIs('admin.shares.*') ? 'active' : ''}}">
            <a href="#" class="submenu-toggle" data-tooltip="Shares">
                <i class="fas fa-chart-pie"></i> 
                <span>Shares</span>
            </a>
            <ul class="submenu list-unstyled">
                <li class="{{request()->routeIs('admin.shares.index') ? 'active' : ''}}">
                    <a href="{{route('admin.shares.index')}}">
                        <i class="fas fa-chart-pie"></i> Share Overview
                    </a>
                </li>
                <li class="{{request()->routeIs('admin.shares.purchases') ? 'active' : ''}}">
                    <a href="{{route('admin.shares.purchases')}}">
                        <i class="fas fa-shopping-cart"></i> Share Purchases
                    </a>
                </li>
                <li class="{{request()->routeIs('admin.shares.dividends') ? 'active' : ''}}">
                    <a href="{{route('admin.shares.dividends')}}">
                        <i class="fas fa-gift"></i> Dividends
                    </a>
                </li>
            </ul>
        </li>

        <!-- Transactions -->
        <li class="has-submenu {{request()->routeIs('admin.transactions.*') ? 'active' : ''}}">
            <a href="#" class="submenu-toggle" data-tooltip="Transactions">
                <i class="fas fa-exchange-alt"></i> 
                <span>Transactions</span>
            </a>
            <ul class="submenu list-unstyled">
                <li class="{{request()->routeIs('admin.transactions.index') ? 'active' : ''}}">
                    <a href="{{route('admin.transactions.index')}}">
                        <i class="fas fa-list"></i> All Transactions
                    </a>
                </li>
                <li class="{{request()->routeIs('admin.transactions.general-ledger') ? 'active' : ''}}">
                    <a href="{{route('admin.transactions.general-ledger')}}">
                        <i class="fas fa-book"></i> General Ledger
                    </a>
                </li>
                <li class="{{request()->routeIs('admin.transactions.trial-balance') ? 'active' : ''}}">
                    <a href="{{route('admin.transactions.trial-balance')}}">
                        <i class="fas fa-balance-scale"></i> Trial Balance
                    </a>
                </li>
            </ul>
        </li>

        <!-- Reports -->
        <li class="has-submenu {{request()->routeIs('admin.reports.*') ? 'active' : ''}}">
            <a href="#" class="submenu-toggle" data-tooltip="Reports">
                <i class="fas fa-chart-bar"></i> 
                <span>Reports</span>
            </a>
            <ul class="submenu list-unstyled">
                <li class="{{request()->routeIs('admin.reports.index') ? 'active' : ''}}">
                    <a href="{{route('admin.reports.index')}}">
                        <i class="fas fa-chart-line"></i> General Reports
                    </a>
                </li>
                <li class="{{request()->routeIs('admin.reports.members') ? 'active' : ''}}">
                    <a href="{{route('admin.reports.members')}}">
                        <i class="fas fa-users"></i> Member Reports
                    </a>
                </li>
                <li class="{{request()->routeIs('admin.reports.savings') ? 'active' : ''}}">
                    <a href="{{route('admin.reports.savings')}}">
                        <i class="fas fa-piggy-bank"></i> Savings Reports
                    </a>
                </li>
                <li class="{{request()->routeIs('admin.reports.loans') ? 'active' : ''}}">
                    <a href="{{route('admin.reports.loans')}}">
                        <i class="fas fa-hand-holding-usd"></i> Loan Reports
                    </a>
                </li>
                <li class="{{request()->routeIs('admin.reports.financial') ? 'active' : ''}}">
                    <a href="{{route('admin.reports.financial')}}">
                        <i class="fas fa-calculator"></i> Financial Reports
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</nav>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarCollapse = document.getElementById('sidebarCollapse');
        const menuItems = document.querySelectorAll('.has-submenu');
        const submenuToggles = document.querySelectorAll('.submenu-toggle');

        // Mobile sidebar toggle (hamburger menu)
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                document.body.classList.toggle('sidebar-open');
            });
        }

        // Desktop sidebar collapse/expand
        if (sidebarCollapse) {
            sidebarCollapse.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                document.body.classList.toggle('sidebar-collapsed');
                
                // Update button icon
                const icon = this.querySelector('i');
                if (sidebar.classList.contains('collapsed')) {
                    icon.className = 'fas fa-bars';
                } else {
                    icon.className = 'fas fa-bars';
                }
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 992) {
                if (!sidebar.contains(e.target) && 
                    !e.target.closest('#sidebarToggle') && 
                    !e.target.closest('.navbar-toggler')) {
                    sidebar.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                }
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }
        });

        // Enhanced submenu toggle functionality
        submenuToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const parentItem = this.closest('.has-submenu');
                const submenu = parentItem.querySelector('.submenu');
                const arrow = this.querySelector('.submenu-arrow');
                
                // Close other open submenus
                menuItems.forEach(otherItem => {
                    if (otherItem !== parentItem) {
                        otherItem.classList.remove('active');
                        const otherArrow = otherItem.querySelector('.submenu-arrow');
                        if (otherArrow) {
                            otherArrow.style.transform = 'rotate(0deg)';
                        }
                    }
                });

                // Toggle current submenu
                parentItem.classList.toggle('active');
                
                // Rotate arrow
                if (arrow) {
                    if (parentItem.classList.contains('active')) {
                        arrow.style.transform = 'rotate(180deg)';
                    } else {
                        arrow.style.transform = 'rotate(0deg)';
                    }
                }
            });
        });

        // Auto-expand submenu if current page is in it
        menuItems.forEach(item => {
            const activeSubmenuItem = item.querySelector('.submenu .active');
            if (activeSubmenuItem) {
                item.classList.add('active');
                const arrow = item.querySelector('.submenu-arrow');
                if (arrow) {
                    arrow.style.transform = 'rotate(180deg)';
                }
            }
        });

        // Close submenus when clicking on submenu links (mobile)
        document.querySelectorAll('.submenu a').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    setTimeout(() => {
                        sidebar.classList.remove('show');
                        document.body.classList.remove('sidebar-open');
                    }, 150);
                }
            });
        });

        // Keyboard navigation support
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && window.innerWidth < 992) {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }
        });
    });
</script>

