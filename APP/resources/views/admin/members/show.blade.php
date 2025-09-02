@extends('admin.layouts.app')

@section('title', 'Member Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">{{ $member->name }}</h1>
                <p class="text-muted">Member #{{ $member->member_number ?? 'N/A' }} • Joined {{ $member->created_at->format('M d, Y') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.members.edit', $member->id) }}" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Edit Member
                </a>
                <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Member Status and Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5>
                            Status: 
                            @switch($member->status)
                                @case('active')
                                    <span class="badge bg-success">Active</span>
                                    @break
                                @case('pending')
                                    <span class="badge bg-warning">Pending Approval</span>
                                    @break
                                @case('suspended')
                                    <span class="badge bg-danger">Suspended</span>
                                    @break
                                @case('inactive')
                                    <span class="badge bg-secondary">Inactive</span>
                                    @break
                            @endswitch
                        </h5>
                        @if($member->approved_at)
                        <small class="text-muted">Approved on {{ $member->approved_at->format('M d, Y') }}</small>
                        @endif
                    </div>
                    <div class="col-md-6 text-end">
                        @if($member->status == 'pending')
                        <form action="{{ route('admin.members.approve', $member->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Approve this member?')">
                                <i class="bi bi-check-circle"></i> Approve Member
                            </button>
                        </form>
                        @elseif($member->status == 'active')
                        <form action="{{ route('admin.members.suspend', $member->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning" onclick="return confirm('Suspend this member?')">
                                <i class="bi bi-pause-circle"></i> Suspend
                            </button>
                        </form>
                        @elseif($member->status == 'suspended')
                        <form action="{{ route('admin.members.activate', $member->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Activate this member?')">
                                <i class="bi bi-play-circle"></i> Activate
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Member Information -->
<div class="row">
    <div class="col-lg-8">
        <!-- Personal Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person"></i> Personal Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Full Name:</strong></td>
                                <td>{{ $member->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $member->email }}</td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td>{{ $member->phone ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>National ID:</strong></td>
                                <td>{{ $member->national_id ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Date of Birth:</strong></td>
                                <td>{{ $member->date_of_birth ? Carbon\Carbon::parse($member->date_of_birth)->format('M d, Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Gender:</strong></td>
                                <td>{{ $member->gender ? ucfirst($member->gender) : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Occupation:</strong></td>
                                <td>{{ $member->occupation ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Monthly Income:</strong></td>
                                <td>{{ $member->monthly_income ? 'UGX ' . number_format($member->monthly_income, 2) : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                @if($member->address)
                <div class="row">
                    <div class="col-12">
                        <strong>Address:</strong><br>
                        {{ $member->address }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Accounts -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-piggy-bank"></i> Savings Accounts</h5>
            </div>
            <div class="card-body">
                @if($member->accounts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Account Number</th>
                                <th>Type</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Opened</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($member->accounts as $account)
                            <tr>
                                <td>{{ $account->account_number }}</td>
                                <td>{{ ucfirst($account->account_type) }}</td>
                                <td>UGX {{ number_format($account->balance, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $account->status == 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($account->status) }}
                                    </span>
                                </td>
                                <td>{{ $account->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted">No accounts found.</p>
                @endif
            </div>
        </div>

        <!-- Loans -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-currency-dollar"></i> Loans</h5>
            </div>
            <div class="card-body">
                @if($member->loans->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Loan Number</th>
                                <th>Principal</th>
                                <th>Outstanding</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($member->loans as $loan)
                            <tr>
                                <td>{{ $loan->loan_number }}</td>
                                <td>UGX {{ number_format($loan->principal_amount, 2) }}</td>
                                <td>UGX {{ number_format($loan->outstanding_balance ?? $loan->principal_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $loan->status == 'active' ? 'success' : ($loan->status == 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                </td>
                                <td>{{ $loan->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted">No loans found.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Account Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Account Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-12 mb-3">
                        <h4 class="text-primary">UGX {{ number_format($member->accounts->sum('balance'), 2) }}</h4>
                        <small class="text-muted">Total Savings</small>
                    </div>
                    <div class="col-6">
                        <h5>{{ $member->accounts->count() }}</h5>
                        <small class="text-muted">Accounts</small>
                    </div>
                    <div class="col-6">
                        <h5>{{ $member->loans->count() }}</h5>
                        <small class="text-muted">Loans</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shares -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Shares</h5>
            </div>
            <div class="card-body">
                @if($member->shares->count() > 0)
                <div class="row text-center">
                    <div class="col-12 mb-2">
                        <h4 class="text-success">{{ $member->shares->sum('shares_count') }}</h4>
                        <small class="text-muted">Total Shares</small>
                    </div>
                    <div class="col-12">
                        <h5>UGX {{ number_format($member->shares->sum('amount'), 2) }}</h5>
                        <small class="text-muted">Share Value</small>
                    </div>
                </div>
                @else
                <p class="text-muted text-center">No shares purchased.</p>
                @endif
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Activity</h5>
            </div>
            <div class="card-body">
                @php
                    $recentTransactions = $member->accounts->flatMap(function($account) {
                        return $account->transactions->take(5);
                    })->sortByDesc('created_at')->take(5);
                @endphp
                
                @if($recentTransactions->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($recentTransactions as $transaction)
                    <div class="list-group-item px-0 py-2">
                        <div class="d-flex justify-content-between">
                            <div>
                                <small class="fw-bold">{{ ucfirst($transaction->transaction_type) }}</small><br>
                                <small class="text-muted">{{ $transaction->created_at->format('M d, Y') }}</small>
                            </div>
                            <div class="text-end">
                                <small class="fw-bold text-{{ $transaction->transaction_type == 'deposit' ? 'success' : 'warning' }}">
                                    {{ $transaction->transaction_type == 'deposit' ? '+' : '-' }}UGX {{ number_format($transaction->amount, 2) }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted text-center">No recent activity.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection