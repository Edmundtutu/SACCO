@extends('admin.layouts.app')

@section('title', 'Shares Management')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">Shares Management</h1>
                <p class="text-muted">Manage member shares, purchases, and dividend declarations</p>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number">{{ number_format($stats['total_shares']) }}</div>
                    <div class="stats-label">Total Shares</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-graph-up"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card text-white" style="background: linear-gradient(135deg, #28a745, #20c997);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number">UGX {{ number_format($stats['total_value'], 2) }}</div>
                    <div class="stats-label">Total Value</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card text-white" style="background: linear-gradient(135deg, #fd7e14, #e83e8c);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number">{{ number_format($stats['total_members']) }}</div>
                    <div class="stats-label">Shareholders</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card text-white" style="background: linear-gradient(135deg, #6f42c1, #e83e8c);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-number">{{ $stats['recent_purchases']->count() }}</div>
                    <div class="stats-label">Recent Purchases</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-cart-plus"></i>
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
                        <a href="{{ route('admin.shares.purchases') }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
                            <i class="bi bi-list"></i> View All Purchases
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.shares.dividends') }}" class="btn btn-outline-success btn-sm w-100 mb-2">
                            <i class="bi bi-percent"></i> Manage Dividends
                        </a>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-warning btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#declareDividendModal">
                            <i class="bi bi-plus-circle"></i> Declare Dividend
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="#" class="btn btn-outline-info btn-sm w-100 mb-2">
                            <i class="bi bi-file-earmark-text"></i> Share Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Share Purchases -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Share Purchases</h5>
            </div>
            <div class="card-body">
                @if($stats['recent_purchases']->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Member</th>
                                <th>Shares</th>
                                <th>Amount</th>
                                <th>Price per Share</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['recent_purchases'] as $purchase)
                            <tr>
                                <td>{{ $purchase->created_at->format('M d, Y') }}</td>
                                <td>
                                    <strong>{{ $purchase->user->name }}</strong><br>
                                    <small class="text-muted">{{ $purchase->user->member_number ?? $purchase->user->email }}</small>
                                </td>
                                <td>{{ number_format($purchase->shares_count) }}</td>
                                <td>UGX {{ number_format($purchase->amount, 2) }}</td>
                                <td>UGX {{ number_format($purchase->shares_count > 0 ? $purchase->amount / $purchase->shares_count : 0, 2) }}</td>
                                <td>
                                    @switch($purchase->status)
                                        @case('approved')
                                            <span class="badge bg-success">Approved</span>
                                            @break
                                        @case('pending')
                                            <span class="badge bg-warning">Pending</span>
                                            @break
                                        @case('rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark">{{ ucfirst($purchase->status) }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    @if($purchase->status == 'pending')
                                    <form action="{{ route('admin.shares.purchases.approve', $purchase->id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-outline-success btn-sm" 
                                                title="Approve" 
                                                onclick="return confirm('Approve this share purchase?')">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('admin.shares.purchases') }}" class="btn btn-primary">
                        View All Share Purchases
                    </a>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-graph-up display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No recent share purchases</h4>
                    <p class="text-muted">Recent share transactions will appear here.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Declare Dividend Modal -->
<div class="modal fade" id="declareDividendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Declare Dividend</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.shares.dividends.declare') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="year" class="form-label">Year</label>
                        <select class="form-select" id="year" name="year" required>
                            @for($i = date('Y'); $i >= 2020; $i--)
                            <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="rate" class="form-label">Dividend Rate (%)</label>
                        <input type="number" class="form-control" id="rate" name="rate" 
                               min="0" max="100" step="0.01" required 
                               placeholder="e.g., 10.5">
                        <div class="form-text">Enter the dividend rate as a percentage (e.g., 10.5 for 10.5%)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="total_amount" class="form-label">Total Dividend Amount (UGX)</label>
                        <input type="number" class="form-control" id="total_amount" name="total_amount" 
                               min="0" step="0.01" required 
                               placeholder="Total amount to be distributed">
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Note:</strong> This will calculate and create dividend payments for all shareholders based on their share holdings.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" onclick="return confirm('This will declare dividends for all shareholders. Are you sure?')">
                        <i class="bi bi-percent"></i> Declare Dividend
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection