@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
            <p class="text-muted">SACCO Management System Overview</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Members
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\User::where('role', 'member')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Savings
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                UGX {{ number_format(\App\Models\Account::sum('balance')) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-piggy-bank fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Loans
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Loan::where('status', 'active')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Transactions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Transaction::where('status', 'pending')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Approvals Alert -->
    @php
        $pendingTransactions = \App\Models\Transaction::where('status', 'pending')->count();
        $pendingLoans = \App\Models\Loan::where('status', 'pending')->count();
        $pendingMembers = \App\Models\User::where('role', 'member')->where('status', 'pending')->count();
    @endphp

    @if($pendingTransactions > 0 || $pendingLoans > 0 || $pendingMembers > 0)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Attention!</strong> You have pending approvals:
        @if($pendingTransactions > 0)
            <a href="{{ route('admin.transactions.index') }}" class="alert-link">{{ $pendingTransactions }} transactions</a>
        @endif
        @if($pendingLoans > 0)
            <a href="{{ route('admin.loans.index') }}" class="alert-link">{{ $pendingLoans }} loans</a>
        @endif
        @if($pendingMembers > 0)
            <a href="{{ route('admin.members.requests') }}" class="alert-link">{{ $pendingMembers }} members</a>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Recent Transactions -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Transactions</h6>
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Transaction #</th>
                                    <th>Member</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(\App\Models\Transaction::with('member')->orderBy('created_at', 'desc')->limit(10)->get() as $transaction)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.transactions.show', $transaction->id) }}" class="text-decoration-none">
                                            {{ $transaction->transaction_number }}
                                        </a>
                                    </td>
                                    <td>{{ $transaction->member->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $transaction->type == 'deposit' ? 'success' : ($transaction->type == 'withdrawal' ? 'warning' : 'info') }}">
                                            {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                        </span>
                                    </td>
                                    <td class="text-end">UGX {{ number_format($transaction->amount) }}</td>
                                    <td>
                                        @switch($transaction->status)
                                            @case('pending')
                                                <span class="badge badge-warning">Pending</span>
                                                @break
                                            @case('completed')
                                                <span class="badge badge-success">Completed</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge badge-danger">Rejected</span>
                                                @break
                                            @default
                                                <span class="badge badge-light">{{ ucfirst($transaction->status) }}</span>
                                        @endswitch
                                    </td>
                                    <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No transactions found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Stats -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-exchange-alt"></i> Process Transaction
                        </a>
                        <a href="{{ route('admin.loans.index') }}" class="btn btn-outline-success">
                            <i class="fas fa-hand-holding-usd"></i> Review Loans
                        </a>
                        <a href="{{ route('admin.members.requests') }}" class="btn btn-outline-warning">
                            <i class="fas fa-user-plus"></i> Approve Members
                        </a>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-info">
                            <i class="fas fa-chart-bar"></i> Generate Reports
                        </a>
                        <a href="{{ route('admin.transactions.general-ledger') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-book"></i> General Ledger
                        </a>
                    </div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Financial Summary</h6>
                </div>
                <div class="card-body">
                    @php
                        $totalSavings = \App\Models\Account::sum('balance');
                        $totalLoans = \App\Models\Loan::where('status', 'active')->sum('outstanding_balance');
                        $totalShares = \App\Models\Share::sum('total_value');
                        $todayTransactions = \App\Models\Transaction::whereDate('created_at', today())->sum('amount');
                    @endphp
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Total Savings:</span>
                            <span class="font-weight-bold">UGX {{ number_format($totalSavings) }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Outstanding Loans:</span>
                            <span class="font-weight-bold text-danger">UGX {{ number_format($totalLoans) }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Share Capital:</span>
                            <span class="font-weight-bold text-info">UGX {{ number_format($totalShares) }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Today's Transactions:</span>
                            <span class="font-weight-bold text-success">UGX {{ number_format($todayTransactions) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Status -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Database:</span>
                            <span class="badge badge-success">Online</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">API Status:</span>
                            <span class="badge badge-success">Active</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Last Backup:</span>
                            <span class="text-muted">{{ now()->subDays(1)->format('M d, Y') }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Active Users:</span>
                            {{-- TODO: to a column on the users table to track the last login time--}}
                            {{-- <span class="font-weight-bold">{{ \App\Models\User::where('last_login_at', '>', now()->subHours(1))->count() }}</span> --}} 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Transaction Volume (Last 7 Days)</h6>
                </div>
                <div class="card-body">
                    <canvas id="transactionChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Member Growth</h6>
                </div>
                <div class="card-body">
                    <canvas id="memberChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function refreshDashboard() {
    location.reload();
}

// Transaction Chart
const transactionCtx = document.getElementById('transactionChart').getContext('2d');
const transactionChart = new Chart(transactionCtx, {
    type: 'line',
    data: {
        labels: [
            @for($i = 6; $i >= 0; $i--)
                '{{ now()->subDays($i)->format("M d") }}',
            @endfor
        ],
        datasets: [{
            label: 'Transactions',
            data: [
                @for($i = 6; $i >= 0; $i--)
                    {{ \App\Models\Transaction::whereDate('created_at', now()->subDays($i))->count() }},
                @endfor
            ],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Member Growth Chart
const memberCtx = document.getElementById('memberChart').getContext('2d');
const memberChart = new Chart(memberCtx, {
    type: 'bar',
    data: {
        labels: [
            @for($i = 5; $i >= 0; $i--)
                '{{ now()->subMonths($i)->format("M Y") }}',
            @endfor
        ],
        datasets: [{
            label: 'New Members',
            data: [
                @for($i = 5; $i >= 0; $i--)
                    {{ \App\Models\User::where('role', 'member')->whereMonth('created_at', now()->subMonths($i)->month)->whereYear('created_at', now()->subMonths($i)->year)->count() }},
                @endfor
            ],
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endpush