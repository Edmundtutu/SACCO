@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <h1 class="page-title">Dashboard</h1>
        <p class="text-muted">Welcome to the SACCO Management System Admin Panel</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number">{{ number_format($stats['total_members']) }}</div>
                    <div class="stats-label">Total Members</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card text-white" style="background: linear-gradient(135deg, #28a745, #20c997);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number">UGX{{ number_format($stats['total_savings'], 2) }}</div>
                    <div class="stats-label">Total Savings</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-piggy-bank"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card text-white" style="background: linear-gradient(135deg, #fd7e14, #e83e8c);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number">UGX{{ number_format($stats['total_loans'], 2) }}</div>
                    <div class="stats-label">Total Loans</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stats-card text-white" style="background: linear-gradient(135deg, #6f42c1, #e83e8c);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number">UGX{{ number_format($stats['total_shares'], 2) }}</div>
                    <div class="stats-label">Total Shares</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-graph-up"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <h6>Members</h6>
                        <a href="{{ route('admin.members.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-people"></i> View All Members
                        </a>
                        @if($stats['pending_members'] > 0)
                        <a href="{{ route('admin.members.index', ['status' => 'pending']) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-clock"></i> Pending ({{ $stats['pending_members'] }})
                        </a>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <h6>Savings</h6>
                        <a href="{{ route('admin.savings.index') }}" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-piggy-bank"></i> Manage Savings
                        </a>
                    </div>
                    <div class="col-md-3">
                        <h6>Loans</h6>
                        <a href="{{ route('admin.loans.index') }}" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-currency-dollar"></i> Manage Loans
                        </a>
                        @if($stats['pending_loans'] > 0)
                        <a href="{{ route('admin.loans.index', ['status' => 'pending']) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-clock"></i> Pending ({{ $stats['pending_loans'] }})
                        </a>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <h6>Reports</h6>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-file-earmark-text"></i> Generate Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Transactions</h5>
            </div>
            <div class="card-body">
                @if($stats['recent_transactions']->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Member</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['recent_transactions'] as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                <td>{{ $transaction->account->user->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $transaction->transaction_type == 'deposit' ? 'success' : 'warning' }}">
                                        {{ ucfirst($transaction->transaction_type) }}
                                    </span>
                                </td>
                                <td>UGX{{ number_format($transaction->amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $transaction->status == 'completed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center">No recent transactions found.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-currency-dollar"></i> Recent Loan Applications</h5>
            </div>
            <div class="card-body">
                @if($stats['recent_loans']->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($stats['recent_loans'] as $loan)
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $loan->member->name }}</h6>
                                <p class="mb-1 text-muted small">UGX{{ number_format($loan->principal_amount, 2) }}</p>
                                <small class="text-muted">{{ $loan->created_at->format('M d, Y') }}</small>
                            </div>
                            <span class="badge bg-{{ $loan->status == 'pending' ? 'warning' : ($loan->status == 'approved' ? 'success' : 'danger') }}">
                                {{ ucfirst($loan->status) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted text-center">No recent loan applications.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row mt-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Monthly Savings Growth</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="savingsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Member Status Distribution</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="memberStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Sample chart data - replace with actual data from controller
const savingsCtx = document.getElementById('savingsChart').getContext('2d');
new Chart(savingsCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Savings Growth',
            data: [12000, 19000, 15000, 25000, 32000, 30000],
            borderColor: '#3399CC',
            backgroundColor: 'rgba(51, 153, 204, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'UGX' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

const memberStatusCtx = document.getElementById('memberStatusChart').getContext('2d');
new Chart(memberStatusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Active', 'Pending', 'Suspended'],
        datasets: [{
            data: [{{ $stats['active_members'] }}, {{ $stats['pending_members'] }}, {{ $stats['total_members'] - $stats['active_members'] - $stats['pending_members'] }}],
            backgroundColor: ['#28a745', '#ffc107', '#dc3545']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>
@endpush
