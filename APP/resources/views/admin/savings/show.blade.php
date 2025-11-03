@extends('admin.layouts.app')

@section('title', 'Account Details - ' . $account->account_number)

@push('styles')
<style>
    .account-header {
        background: linear-gradient(135deg, #2980b9 0%, #1a3a6e 100%);
        border-radius: 15px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
    }

    .member-profile-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .member-avatar-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2980b9, #1a3a6e);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 2.5rem;
        margin: 0 auto 1rem;
    }

    .balance-display {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        border-radius: 15px;
        padding: 2rem;
        color: white;
        text-align: center;
    }

    .balance-amount {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 0;
    }

    .info-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .info-row {
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .transaction-item {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border-left: 4px solid;
        transition: all 0.3s;
    }

    .transaction-item:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .transaction-item.deposit {
        border-left-color: #28a745;
    }

    .transaction-item.withdrawal {
        border-left-color: #dc3545;
    }

    .transaction-item.transfer {
        border-left-color: #17a2b8;
    }

    .action-buttons .btn {
        margin: 0.25rem;
    }

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e0e0e0;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -24px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #667eea;
        border: 3px solid white;
        box-shadow: 0 0 0 2px #667eea;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-box {
        background: white;
        border-radius: 10px;
        padding: 1.25rem;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .stat-value {
        font-size: 1.8rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: #6c757d;
        font-size: 0.9rem;
    }
</style>
@endpush

@section('content')
<!-- Account Header -->
<div class="account-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center mb-3">
                <a href="{{ route('admin.savings.accounts') }}" class="btn btn-light btn-sm me-3">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <div>
                    <h1 class="mb-1">
                        <i class="bi bi-bank2"></i> {{ $account->account_number }}
                    </h1>
                    <p class="mb-0 opacity-75">{{ $account->accountable->savingsProduct->name ?? 'Savings Account' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            <span class="badge bg-{{ $account->status == 'active' ? 'success' : ($account->status == 'inactive' ? 'danger' : 'warning') }} fs-6 px-3 py-2">
                {{ ucfirst($account->status) }}
            </span>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column -->
    <div class="col-lg-4 mb-4">
        <!-- Member Profile Card -->
        <div class="member-profile-card">
            <div class="member-avatar-large">
                {{ strtoupper(substr($account->member->name ?? 'N', 0, 1)) }}
            </div>
            <div class="text-center mb-3">
                <h4 class="mb-1">{{ $account->member->name ?? 'N/A' }}</h4>
                <p class="text-muted mb-2">
                    <i class="bi bi-person-badge"></i> {{ $account->member->member_number ?? 'N/A' }}
                </p>
                <p class="text-muted small mb-0">
                    <i class="bi bi-envelope"></i> {{ $account->member->email ?? 'N/A' }}<br>
                    <i class="bi bi-telephone"></i> {{ $account->member->phone ?? 'N/A' }}
                </p>
            </div>
            <div class="d-grid">
                <a href="{{ route('admin.members.show', $account->member->id) }}" class="btn btn-outline-primary">
                    <i class="bi bi-person"></i> View Member Profile
                </a>
            </div>
        </div>

        <!-- Balance Display -->
        <div class="balance-display mt-3">
            <p class="mb-2 opacity-75">Current Balance</p>
            <h2 class="balance-amount">UGX {{ number_format($account->balance, 0) }}</h2>
            <small class="opacity-75">As of {{ now()->format('M d, Y H:i') }}</small>
        </div>

        <!-- Quick Actions -->
        <div class="info-card mt-3">
            <h6 class="mb-3"><i class="bi bi-lightning-charge"></i> Quick Actions</h6>
            <div class="action-buttons d-grid gap-2">
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#depositModal">
                    <i class="bi bi-plus-circle"></i> Deposit
                </button>
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#withdrawalModal">
                    <i class="bi bi-dash-circle"></i> Withdrawal
                </button>
                <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#transferModal">
                    <i class="bi bi-arrow-left-right"></i> Transfer
                </button>
                <button class="btn btn-secondary btn-sm" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print Statement
                </button>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-8">
        <!-- Account Statistics -->
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-value text-success">{{ $account->transactions->where('type', 'deposit')->count() }}</div>
                <div class="stat-label">Total Deposits</div>
            </div>
            <div class="stat-box">
                <div class="stat-value text-danger">{{ $account->transactions->where('type', 'withdrawal')->count() }}</div>
                <div class="stat-label">Total Withdrawals</div>
            </div>
            <div class="stat-box">
                <div class="stat-value text-primary">{{ $account->transactions->count() }}</div>
                <div class="stat-label">All Transactions</div>
            </div>
        </div>

        <!-- Account Information -->
        <div class="info-card">
            <h5 class="mb-3"><i class="bi bi-info-circle"></i> Account Information</h5>
            <div class="info-row">
                <div class="row">
                    <div class="col-6"><strong>Account Number:</strong></div>
                    <div class="col-6 text-end">{{ $account->account_number }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="row">
                    <div class="col-6 text-nowrap"><strong>Account Type:</strong></div>
                <div class="col-6 text-end">{{ ucfirst($account->getAccountTypeAttribute()) }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="row">
                    <div class="col-6"><strong>Savings Product:</strong></div>
                    <div class="col-6 text-nowrap">{{ $account->accountable->savingsProduct->name ?? 'N/A' }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="row">
                    <div class="col-6"><strong>Product Code:</strong></div>
                    <div class="col-6 text-end">{{ $account->accountable->savingsProduct->code ?? 'N/A' }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="row">
                    <div class="col-6"><strong>Interest Rate:</strong></div>
                    <div class="col-6 text-end text-success fw-bold">{{ number_format($account->accountable->savingsProduct->interest_rate ?? 0, 2) }}%</div>
                </div>
            </div>
            <div class="info-row">
                <div class="row">
                    <div class="col-6"><strong>Opened Date:</strong></div>
                    <div class="col-6 text-end">{{ $account->created_at->format('M d, Y') }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="row">
                    <div class="col-6"><strong>Last Activity:</strong></div>
                    <div class="col-6 text-end">{{ $account->updated_at->diffForHumans() }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="row">
                    <div class="col-6"><strong>Status:</strong></div>
                    <div class="col-6 text-end">
                        <span class="badge bg-{{ $account->status == 'active' ? 'success' : ($account->status == 'inactive' ? 'danger' : 'warning') }}">
                            {{ ucfirst($account->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="info-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Transaction History</h5>
                <a href="{{ route('admin.savings.transactions') }}?account={{ $account->id }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>

            @if($account->transactions->isEmpty())
            <div class="text-center py-4">
                <i class="bi bi-inbox display-4 text-muted"></i>
                <p class="text-muted mt-2">No transactions yet</p>
            </div>
            @else
            <div class="timeline">
                @foreach($account->transactions->take(10) as $transaction)
                <div class="timeline-item">
                    <div class="transaction-item {{ $transaction->type }}">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        @if($transaction->type == 'deposit')
                                            <i class="bi bi-arrow-down-circle text-success fs-3"></i>
                                        @elseif($transaction->type == 'withdrawal')
                                            <i class="bi bi-arrow-up-circle text-danger fs-3"></i>
                                        @else
                                            <i class="bi bi-arrow-left-right text-info fs-3"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <h6 class="mb-1">{{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</h6>
                                        <p class="text-muted small mb-0">{{ $transaction->description ?? 'No description' }}</p>
                                        <small class="text-muted">{{ $transaction->created_at->format('M d, Y H:i') }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <h5 class="mb-1 {{ $transaction->type == 'deposit' ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->type == 'deposit' ? '+' : '-' }}UGX {{ number_format($transaction->amount, 0) }}
                                </h5>
                                <span class="badge bg-{{ $transaction->status == 'completed' ? 'success' : ($transaction->status == 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($account->transactions->count() > 10)
            <div class="text-center mt-3">
                <a href="{{ route('admin.savings.transactions') }}?account={{ $account->id }}" class="btn btn-outline-primary">
                    View All {{ $account->transactions->count() }} Transactions
                </a>
            </div>
            @endif
            @endif
        </div>
    </div>
</div>

<!-- Deposit Modal -->
<div class="modal fade" id="depositModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Make Deposit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.savings.manual-transaction') }}" method="POST">
                @csrf
                <input type="hidden" name="account_id" value="{{ $account->id }}">
                <input type="hidden" name="type" value="deposit">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount (UGX)</label>
                        <input type="number" name="amount" class="form-control" min="1" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Process Deposit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Withdrawal Modal -->
<div class="modal fade" id="withdrawalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-dash-circle"></i> Make Withdrawal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.savings.manual-transaction') }}" method="POST">
                @csrf
                <input type="hidden" name="account_id" value="{{ $account->id }}">
                <input type="hidden" name="type" value="withdrawal">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Available Balance: <strong>UGX {{ number_format($account->balance, 0) }}</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (UGX)</label>
                        <input type="number" name="amount" class="form-control" min="1" max="{{ $account->balance }}" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Process Withdrawal</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
