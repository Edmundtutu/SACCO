@extends('admin.layouts.app')

@section('title', 'Savings Accounts')

@push('styles')
    <style>
        .account-card {
            border-radius: 4px;
            transition: all 0.3s;
            border-top: 4px solid;
            margin-bottom: 1rem;
        }

        .account-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .account-card.active {
            border-top-color: #28a745;
        }

        .account-card.inactive {
            border-top-color: #dc3545;
            opacity: 0.7;
        }

        .account-card.dormant {
            border-top-color: #ffc107;
        }

        .search-filter-section {
            background: linear-gradient(135deg, #2980b9 0%, #1a3a6e 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
        }

        .filter-badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            margin: 0.25rem;
            display: inline-block;
        }

        .account-balance {
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
        }

        .member-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2980b9, #1a3a6e);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .stats-mini-card {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .view-toggle {
            background: white;
            border-radius: 10px;
            padding: 0.5rem;
        }

        .view-toggle .btn {
            border-radius: 8px;
        }
    </style>
@endpush

@section('content')
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">
                        <i class="bi bi-bank2"></i> Savings Accounts
                    </h1>
                    <p class="text-muted">Manage and monitor member savings accounts</p>
                </div>
                <div class="view-toggle">
                    <button class="btn btn-sm btn-primary active" id="cardViewBtn">
                        <i class="bi bi-grid-3x3-gap"></i> Cards
                    </button>
                    <button class="btn btn-sm btn-outline-primary" id="tableViewBtn">
                        <i class="bi bi-table"></i> Table
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="search-filter-section">
        <form action="{{ route('admin.savings.accounts') }}" method="GET" id="filterForm">
            <div class="row align-items-end">
                <div class="col-md-5 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-search"></i> Search Accounts
                    </label>
                    <input type="text" name="search" class="form-control form-control-lg"
                        placeholder="Search by member name, email, account number..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-funnel"></i> Status Filter
                    </label>
                    <select name="status" class="form-select form-select-lg">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="dormant" {{ request('status') == 'dormant' ? 'selected' : '' }}>Dormant</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <button type="submit" class="btn btn-light btn-lg me-2">
                        <i class="bi bi-search"></i> Search
                    </button>
                    <a href="{{ route('admin.savings.accounts') }}" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </div>

            @if (request('search') || request('status'))
                <div class="mt-3">
                    <strong>Active Filters:</strong>
                    @if (request('search'))
                        <span class="filter-badge">
                            Search: "{{ request('search') }}"
                            <a href="{{ route('admin.savings.accounts', array_merge(request()->except('search'))) }}"
                                class="text-white ms-2">×</a>
                        </span>
                    @endif
                    @if (request('status'))
                        <span class="filter-badge">
                            Status: {{ ucfirst(request('status')) }}
                            <a href="{{ route('admin.savings.accounts', array_merge(request()->except('status'))) }}"
                                class="text-white ms-2">×</a>
                        </span>
                    @endif
                </div>
            @endif
        </form>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-mini-card">
                <h3 class="text-primary mb-2">{{ $accounts->total() }}</h3>
                <p class="text-muted mb-0">Total Accounts</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-mini-card">
                <h3 class="text-success mb-2">UGX {{ number_format($accounts->sum('balance'), 0) }}</h3>
                <p class="text-muted mb-0">Total Balance</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-mini-card">
                <h3 class="text-info mb-2">UGX {{ number_format($accounts->avg('balance'), 0) }}</h3>
                <p class="text-muted mb-0">Average Balance</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-mini-card">
                <h3 class="text-warning mb-2">{{ $accounts->where('status', 'active')->count() }}</h3>
                <p class="text-muted mb-0">Active Accounts</p>
            </div>
        </div>
    </div>

    @if ($accounts->isEmpty())
        <!-- Empty State -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <h4 class="text-muted mt-3">No Savings Accounts Found</h4>
                        <p class="text-muted">
                            @if (request('search') || request('status'))
                                Try adjusting your filters or search criteria.
                            @else
                                There are no savings accounts in the system yet.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Card View -->
        <div id="cardView">
            <div class="row">
                @foreach ($accounts as $account)
                    <div class="col-lg-6 col-xl-4 mb-3">
                        <div class="card account-card {{ strtolower($account->status) }}">
                            <div class="card-body">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="member-avatar me-3">
                                        {{ strtoupper(substr($account->member->name ?? 'N', 0, 1)) }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1">{{ $account->member->name ?? 'N/A' }}</h5>
                                        <p class="text-muted small mb-0">
                                            <i class="bi bi-person-badge"></i>
                                            {{ $account->member->member_number ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <span
                                        class="badge bg-{{ $account->status == 'active' ? 'success' : ($account->status == 'inactive' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($account->status) }}
                                    </span>
                                </div>

                                <div class="border-top border-bottom py-3 mb-3">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted d-block">Account Number</small>
                                            <strong>{{ $account->account_number }}</strong>
                                        </div>
                                        <div class="col-6 text-end">
                                            <small class="text-muted d-block">Product</small>
                                            <strong>{{ $account->accountable->savingsProduct->name ?? 'N/A' }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted d-block">Current Balance</small>
                                    <div class="account-balance">UGX {{ number_format($account->balance, 0) }}</div>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Opened</small>
                                        <small>{{ $account->created_at->format('M d, Y') }}</small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Last Updated</small>
                                        <small>{{ $account->updated_at->diffForHumans() }}</small>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <a href="{{ route('admin.savings.accounts.show', $account->id) }}"
                                        class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Table View (Hidden by default) -->
        <div id="tableView" style="display: none;">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Member</th>
                                    <th>Account Number</th>
                                    <th>Product</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Opened</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($accounts as $account)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="member-avatar me-2"
                                                    style="width: 35px; height: 35px; font-size: 0.9rem;">
                                                    {{ strtoupper(substr($account->member->name ?? 'N', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <strong class="d-block">{{ $account->member->name ?? 'N/A' }}</strong>
                                                    <small
                                                        class="text-muted">{{ $account->member->member_number ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><strong>{{ $account->account_number }}</strong></td>
                                        <td>{{ $account->savingsProduct->name ?? 'N/A' }}</td>
                                        <td>
                                            <strong class="text-success">UGX
                                                {{ number_format($account->balance, 0) }}</strong>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $account->status == 'active' ? 'success' : ($account->status == 'inactive' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($account->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $account->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.savings.accounts.show', $account->id) }}"
                                                class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
            <div class="pagination-info">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Showing {{ $accounts->firstItem() }} to {{ $accounts->lastItem() }} of {{ $accounts->total() }} results
                </small>
            </div>
            <div>
                {{ $accounts->appends(request()->query())->links('pagination.bootstrap-5') }}
            </div>
        </div>
    @endif

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Toggle between card and table view
            $('#cardViewBtn').on('click', function() {
                $(this).addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
                $('#tableViewBtn').removeClass('active').removeClass('btn-primary').addClass(
                    'btn-outline-primary');
                $('#cardView').show();
                $('#tableView').hide();
            });

            $('#tableViewBtn').on('click', function() {
                $(this).addClass('active').removeClass('btn-outline-primary').addClass('btn-primary');
                $('#cardViewBtn').removeClass('active').removeClass('btn-primary').addClass(
                    'btn-outline-primary');
                $('#cardView').hide();
                $('#tableView').show();
            });
        });
    </script>
@endpush
