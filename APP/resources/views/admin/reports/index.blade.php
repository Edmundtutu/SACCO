@extends('admin.layouts.app')

@section('title', 'Reports')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">Reports</h1>
                <p class="text-muted">Generate comprehensive reports for management and compliance</p>
            </div>
        </div>
    </div>
</div>

<!-- Report Categories -->
<div class="row">
    <!-- Member Reports -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people"></i> Member Reports</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Generate reports related to member registration, status, and activities.</p>
                
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.reports.members') }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Member List Report</h6>
                                <small class="text-muted">Complete list of all members with their details and status</small>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                    
                    <a href="{{ route('admin.reports.members', ['status' => 'pending']) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Pending Members</h6>
                                <small class="text-muted">List of members awaiting approval</small>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                    
                    <a href="{{ route('admin.reports.members', ['new_members' => true]) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">New Member Registrations</h6>
                                <small class="text-muted">Members registered within a specific period</small>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Savings Reports -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-piggy-bank"></i> Savings Reports</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Reports on savings accounts, deposits, withdrawals, and balances.</p>
                
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.reports.savings') }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Savings Summary</h6>
                                <small class="text-muted">Overview of all savings accounts and balances</small>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                    
                    <a href="{{ route('admin.savings.transactions') }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Transaction Report</h6>
                                <small class="text-muted">Detailed list of all savings transactions</small>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                    
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Member Statements</h6>
                                <small class="text-muted">Individual member account statements</small>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Loan Reports -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-currency-dollar"></i> Loan Reports</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Comprehensive loan portfolio and repayment reports.</p>
                
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.reports.loans') }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Loan Portfolio</h6>
                                <small class="text-muted">Complete overview of all loans and their status</small>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                    
                    <a href="{{ route('admin.loans.applications') }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Pending Applications</h6>
                                <small class="text-muted">Loan applications awaiting approval</small>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                    
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Repayment Schedule</h6>
                                <small class="text-muted">Upcoming and overdue loan repayments</small>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Reports -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Financial Reports</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Financial statements and regulatory reports for management.</p>
                
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.reports.financial') }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Financial Summary</h6>
                                <small class="text-muted">Key financial metrics and ratios</small>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                    
                    <a href="{{ route('admin.reports.trial-balance') }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Trial Balance</h6>
                                <small class="text-muted">Statement of all account balances</small>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                    
                    <a href="{{ route('admin.reports.balance-sheet') }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Balance Sheet</h6>
                                <small class="text-muted">Statement of financial position</small>
                            </div>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Report Generation -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning-charge"></i> Quick Report Generation</h5>
            </div>
            <div class="card-body">
                <form class="row g-3">
                    <div class="col-md-3">
                        <label for="report_type" class="form-label">Report Type</label>
                        <select class="form-select" id="report_type" name="report_type">
                            <option value="">Select Report Type</option>
                            <option value="members">Member Report</option>
                            <option value="savings">Savings Report</option>
                            <option value="loans">Loan Report</option>
                            <option value="financial">Financial Report</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="format" class="form-label">Format</label>
                        <select class="form-select" id="format" name="format">
                            <option value="web">View Online</option>
                            <option value="pdf">Download PDF</option>
                            <option value="excel">Export Excel</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-file-earmark-text"></i> Generate Report
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-2" onclick="this.form.reset()">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle quick report generation
    $('form').on('submit', function(e) {
        e.preventDefault();
        
        const reportType = $('#report_type').val();
        const dateFrom = $('#date_from').val();
        const dateTo = $('#date_to').val();
        const format = $('#format').val();
        
        if (!reportType) {
            alert('Please select a report type');
            return;
        }
        
        // Build URL based on report type
        let url = '';
        switch(reportType) {
            case 'members':
                url = '{{ route("admin.reports.members") }}';
                break;
            case 'savings':
                url = '{{ route("admin.reports.savings") }}';
                break;
            case 'loans':
                url = '{{ route("admin.reports.loans") }}';
                break;
            case 'financial':
                url = '{{ route("admin.reports.financial") }}';
                break;
        }
        
        // Add parameters
        const params = new URLSearchParams();
        if (dateFrom) params.append('date_from', dateFrom);
        if (dateTo) params.append('date_to', dateTo);
        if (format) params.append('format', format);
        
        if (params.toString()) {
            url += '?' + params.toString();
        }
        
        // Navigate to report
        window.open(url, '_blank');
    });
});
</script>
@endpush