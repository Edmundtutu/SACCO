@extends('admin.layouts.app')

@section('title', 'Loan Repayments')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Loan Repayments</h1>
            <p class="text-muted">Loan #{{ $loan->loan_number ?? 'L' . str_pad($loan->id, 6, '0', STR_PAD_LEFT) }} - {{ $loan->member->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.loans.show', $loan->id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Loan
            </a>
            @if($loan->status == 'active')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRepaymentModal">
                <i class="bi bi-plus-circle"></i> Add Repayment
            </button>
            @endif
        </div>
    </div>

    <!-- Loan Summary -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">UGX {{ number_format($loan->principal_amount, 0) }}</div>
                        <div class="stats-label">Principal Amount</div>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card text-white" style="background: linear-gradient(135deg, #28a745, #20c997);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">UGX {{ number_format($loan->repayments->sum('amount'), 0) }}</div>
                        <div class="stats-label">Total Repaid</div>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card text-white" style="background: linear-gradient(135deg, #dc3545, #fd7e14);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">UGX {{ number_format($loan->outstanding_balance ?? $loan->principal_amount, 0) }}</div>
                        <div class="stats-label">Outstanding</div>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card text-white" style="background: linear-gradient(135deg, #6f42c1, #e83e8c);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number">{{ $loan->repayments->count() }}</div>
                        <div class="stats-label">Repayments</div>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-list-ul"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Repayments Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-credit-card"></i> Repayment History 
                <span class="badge bg-primary ms-2">{{ $loan->repayments->count() }} payments</span>
            </h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" onclick="refreshRepayments()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
                <button class="btn btn-sm btn-outline-primary" onclick="printRepayments()">
                    <i class="bi bi-printer"></i> Print
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($loan->repayments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Payment Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Recorded By</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loan->repayments as $repayment)
                        <tr>
                            <td>
                                <div>
                                    <strong class="text-nowrap">{{ $repayment->payment_date ? $repayment->payment_date->format('M d, Y') : 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $repayment->payment_date ? $repayment->payment_date->format('H:i') : 'N/A' }}</small>
                                </div>
                            </td>
                            <td class="text-end">
                                <strong class="text-success">UGX {{ number_format($repayment->amount, 2) }}</strong>
                            </td>
                            <td>
                                @switch($repayment->payment_method)
                                    @case('cash')
                                        <span class="badge-icon bg-success">
                                            <i class="bi bi-cash me-1"></i>Cash
                                        </span>
                                        @break
                                    @case('bank_transfer')
                                        <span class="badge-icon bg-primary">
                                            <i class="bi bi-bank me-1"></i>Bank Transfer
                                        </span>
                                        @break
                                    @case('mobile_money')
                                        <span class="badge-icon bg-info">
                                            <i class="bi bi-phone me-1"></i>Mobile Money
                                        </span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $repayment->payment_method)) }}</span>
                                @endswitch
                            </td>
                            <td>
                                <span class="font-monospace">{{ $repayment->reference ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <div>
                                    <strong class="text-nowrap">{{ $repayment->recordedBy->name ?? 'System' }}</strong><br>
                                    <small class="text-muted">{{ $repayment->created_at ? $repayment->created_at->format('M d, Y H:i') : 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;" title="{{ $repayment->notes }}">
                                    {{ $repayment->notes ?? 'No notes' }}
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button" 
                                            class="btn btn-outline-primary btn-sm" 
                                            title="View Details" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#repaymentModal{{ $repayment->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="font-weight-bold">
                            <td colspan="1">
                                <i class="bi bi-calculator me-2"></i>Total
                            </td>
                            <td class="text-end">
                                <strong class="text-success">UGX {{ number_format($loan->repayments->sum('amount'), 2) }}</strong>
                            </td>
                            <td colspan="5"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-credit-card display-1 text-muted"></i>
                <h4 class="text-muted mt-3">No repayments found</h4>
                <p class="text-muted">No payments have been recorded for this loan yet.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Payment Summary Chart -->
    @if($loan->repayments->count() > 0)
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-pie-chart"></i> Payment Methods
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="paymentMethodChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-graph-up"></i> Payment Timeline
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="paymentTimelineChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Add Repayment Modal -->
@if($loan->status == 'active')
<div class="modal fade" id="addRepaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Repayment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.loans.add-repayment', $loan->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount *</label>
                        <input type="number" class="form-control" id="amount" name="amount" 
                               step="0.01" min="1" max="{{ $loan->outstanding_balance ?? $loan->principal_amount }}" required>
                        <div class="form-text">Maximum: UGX {{ number_format($loan->outstanding_balance ?? $loan->principal_amount, 2) }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Payment Date *</label>
                        <input type="date" class="form-control" id="payment_date" name="payment_date" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method *</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="reference" class="form-label">Reference Number</label>
                        <input type="text" class="form-control" id="reference" name="reference">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Record Repayment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Repayment Detail Modals -->
@foreach($loan->repayments as $repayment)
<div class="modal fade" id="repaymentModal{{ $repayment->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Repayment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless">
                    <tr>
                        <td class="font-weight-bold">Payment Date:</td>
                        <td>{{ $repayment->payment_date ? $repayment->payment_date->format('M d, Y H:i') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Amount:</td>
                        <td class="font-weight-bold text-success">UGX {{ number_format($repayment->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Method:</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $repayment->payment_method)) }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Reference:</td>
                        <td>{{ $repayment->reference ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Recorded By:</td>
                        <td>{{ $repayment->recordedBy->name ?? 'System' }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Recorded At:</td>
                        <td>{{ $repayment->created_at ? $repayment->created_at->format('M d, Y H:i') : 'N/A' }}</td>
                    </tr>
                    @if($repayment->notes)
                    <tr>
                        <td class="font-weight-bold">Notes:</td>
                        <td>{{ $repayment->notes }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function refreshRepayments() {
    location.reload();
}

function printRepayments() {
    window.print();
}

// Load loan transaction history from API
function loadLoanTransactionHistory() {
    fetch(`{{ route('admin.loans.history', $loan->id) }}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Loan Transaction History:', data.data);
                // Update the page with API transaction data
                updateTransactionHistory(data.data);
            }
        })
        .catch(error => {
            console.error('Error loading loan transaction history:', error);
        });
}

// Update transaction history display
function updateTransactionHistory(transactions) {
    // This function can be used to update the transaction history
    // with data from the API instead of the database
    console.log('Updating transaction history with API data:', transactions);
}

// Load data when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadLoanTransactionHistory();
});

// Payment Method Chart
@if($loan->repayments->count() > 0)
const paymentMethodCtx = document.getElementById('paymentMethodChart').getContext('2d');
const paymentMethodData = @json($loan->repayments->groupBy('payment_method')->map->sum('amount'));

new Chart(paymentMethodCtx, {
    type: 'doughnut',
    data: {
        labels: Object.keys(paymentMethodData).map(method => method.replace('_', ' ').toUpperCase()),
        datasets: [{
            data: Object.values(paymentMethodData),
            backgroundColor: [
                '#28a745',
                '#007bff',
                '#17a2b8',
                '#ffc107',
                '#dc3545'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Payment Timeline Chart
const timelineCtx = document.getElementById('paymentTimelineChart').getContext('2d');
const timelineData = @json($loan->repayments->filter(function($item) { return $item->payment_date !== null; })->sortBy('payment_date')->groupBy(function($item) { return $item->payment_date->format('M Y'); })->map->sum('amount'));

new Chart(timelineCtx, {
    type: 'line',
    data: {
        labels: Object.keys(timelineData),
        datasets: [{
            label: 'Monthly Payments',
            data: Object.values(timelineData),
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
@endif
</script>
@endpush
