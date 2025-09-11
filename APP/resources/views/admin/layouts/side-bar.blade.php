<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h4 class="text-white">SACCO Admin</h4>
    </div>

    <ul class="list-unstyled components">
        <li class="{{request()->routeIs('admin.dashboard.*') ? 'active' : ''}}">
            <a href="{{route('admin.dashboard')}}">
                <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
            </a>
        </li>
        <li class="has-submenu">
            <a href="#">
                <i class="bi bi-people"></i> <span>Members</span>
            </a>
            <ul class="submenu list-unstyled">
                <li class="{{request()->routeIs('admin.members.*') ? 'active' : ' ' }}">
                    <a href="{{route('admin.members.index')}}">
                        All Members
                    </a>
                </li>
                <li><a href="#">Add New Member</a></li>
                <li><a href="#">Member Categories</a></li>
                <li class="{{request()->routeIs('admin.members.requests') ? 'active' : ' ' }}">
                    <a href="{{route('admin.members.requests')}}">
                        Membership Requests
                    </a>
                </li>
            </ul>
        </li>
        <li class="has-submenu">
            <a href="#">
                <i class="bi bi-piggy-bank"></i> <span>Savings</span>
            </a>
            <ul class="submenu list-unstyled">
                <li class="{{request()->routeIs('admin.savings.*') ? 'active' : ' ' }}">
                    <a href="{{route('admin.savings.index')}}">
                        Savings Accounts
                    </a>
                </li>
                <li><a href="#">Deposits</a></li>
                <li><a href="#">Withdrawals</a></li>
                <li><a href="#">Savings Reports</a></li>
            </ul>
        </li>
        <li class="has-submenu">
            <a href="#">
                <i class="bi bi-currency-dollar"></i> <span>Loans</span>
            </a>
            <ul class="submenu list-unstyled">
                <li class="{{request()->routeIs('admin.loans.*') ? 'active' : ' ' }}">
                    <a href="{{route('admin.loans.index')}}">
                        Loan Applications
                    </a>
                </li>
                <li><a href="#">Active Loans</a></li>
                <li><a href="#">Loan Payments</a></li>
                <li><a href="#">Loan Products</a></li>
                <li><a href="#">Loan Reports</a></li>
            </ul>
        </li>
        <li class="has-submenu">
            <a href="#">
                <i class="bi bi-graph-up"></i> <span>Shares</span>
            </a>
            <ul class="submenu list-unstyled">
                <li class="{{request()->routeIs('admin.shares.*') ? 'active' : ' ' }}">
                    <a href="{{route('admin.shares.index')}}">
                        Share Accounts
                    </a>
                </li>
                <li><a href="#">Share Transfers</a></li>
                <li><a href="#">Dividends</a></li>
                <li><a href="#">Share Reports</a></li>
            </ul>
        </li>
        <li class="has-submenu">
            <a href="#">
                <i class="bi bi-file-earmark-text"></i> <span>Reports</span>
            </a>
            <ul class="submenu list-unstyled">
                <li class="{{request()->routeIs('admin.reports.*') ? 'active' : ' ' }}">
                    <a href="{{route('admin.reports.index')}}">General Reports
                    </a>
                </li>
                <li><a href="#">Financial Reports</a></li>
                <li><a href="#">Member Statements</a></li>
                <li><a href="#">Transaction Reports</a></li>
                <li><a href="#">Audit Trail</a></li>
            </ul>
        </li>
    </ul>
</nav>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle submenus
        const menuItems = document.querySelectorAll('.has-submenu');

        menuItems.forEach(item => {
            const link = item.querySelector('a');

            link.addEventListener('click', function(e) {
                e.preventDefault();

                // Close other open submenus
                menuItems.forEach(otherItem => {
                    if (otherItem !== item && otherItem.classList.contains('active')) {
                        otherItem.classList.remove('active');
                    }
                });

                // Toggle current submenu
                item.classList.toggle('active');
            });
        });

        // Close submenus when clicking elsewhere, but not when clicking submenu items
        document.addEventListener('click', function(e) {
            // Don't close if clicking on a submenu item
            if (e.target.closest('.submenu')) {
                return;
            }

            // Close if clicking outside of any has-submenu element
            if (!e.target.closest('.has-submenu')) {
                menuItems.forEach(item => {
                    item.classList.remove('active');
                });
            }
        });
    });
</script>
