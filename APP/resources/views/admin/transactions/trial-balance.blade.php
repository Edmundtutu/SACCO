@extends('admin.layouts.app')

@section('title', 'Trial Balance')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Trial Balance</h1>
            <p class="text-muted">As of {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <form method="GET" action="{{ route('admin.transactions.trial-balance') }}" class="d-flex gap-2">
                <input type="date" name="date" value="{{ $date }}" class="form-control" style="width: auto;">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-calendar"></i> Update Date
                </button>
            </form>
            <button class="btn btn-outline-secondary" onclick="printTrialBalance()">
                <i class="fas fa-print"></i> Print
            </button>
            <button class="btn btn-outline-success" onclick="exportTrialBalance()">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>

    <!-- Trial Balance Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Trial Balance</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="trialBalanceTable">
                    <thead class="thead-light">
                        <tr>
                            <th rowspan="2" class="text-center align-middle">Account Code</th>
                            <th rowspan="2" class="text-center align-middle">Account Name</th>
                            <th rowspan="2" class="text-center align-middle">Account Type</th>
                            <th colspan="2" class="text-center">Balance</th>
                        </tr>
                        <tr>
                            <th class="text-center">Debit</th>
                            <th class="text-center">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalDebits = 0;
                            $totalCredits = 0;
                            $groupedAccounts = $trialBalance->groupBy('account_type');
                        @endphp

                        @foreach(['asset', 'liability', 'equity', 'income', 'expense'] as $accountType)
                            @if($groupedAccounts->has($accountType))
                                @if($loop->first)
                                    <tr class="table-primary">
                                        <td colspan="5" class="font-weight-bold text-center">
                                            {{ strtoupper($accountType) }}S
                                        </td>
                                    </tr>
                                @else
                                    <tr class="table-light">
                                        <td colspan="5" class="font-weight-bold text-center">
                                            {{ strtoupper($accountType) }}S
                                        </td>
                                    </tr>
                                @endif

                                @foreach($groupedAccounts[$accountType] as $account)
                                    @php
                                        $debitBalance = $account['debit_balance'] ?? 0;
                                        $creditBalance = $account['credit_balance'] ?? 0;
                                        $totalDebits += $debitBalance;
                                        $totalCredits += $creditBalance;
                                    @endphp
                                    <tr>
                                        <td class="font-monospace">{{ $account['account_code'] }}</td>
                                        <td>{{ $account['account_name'] }}</td>
                                        <td>
                                            <span class="badge badge-{{ $accountType == 'asset' ? 'primary' : ($accountType == 'liability' ? 'warning' : ($accountType == 'equity' ? 'success' : ($accountType == 'income' ? 'info' : 'secondary'))) }}">
                                                {{ ucfirst($accountType) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            @if($debitBalance > 0)
                                                <span class="text-success font-weight-bold">
                                                    UGX {{ number_format($debitBalance, 2) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($creditBalance > 0)
                                                <span class="text-danger font-weight-bold">
                                                    UGX {{ number_format($creditBalance, 2) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <td colspan="3" class="font-weight-bold text-center">TOTAL</td>
                            <td class="text-end font-weight-bold">
                                <span class="text-success">
                                    UGX {{ number_format($totalDebits, 2) }}
                                </span>
                            </td>
                            <td class="text-end font-weight-bold">
                                <span class="text-danger">
                                    UGX {{ number_format($totalCredits, 2) }}
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Balance Verification -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Balance Verification</h6>
                </div>
                <div class="card-body">
                    @php
                        $isBalanced = abs($totalDebits - $totalCredits) < 0.01;
                    @endphp
                    
                    <div class="text-center">
                        @if($isBalanced)
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle fa-3x mb-3"></i>
                                <h4>Trial Balance is Balanced</h4>
                                <p class="mb-0">Total debits equal total credits</p>
                            </div>
                        @else
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                <h4>Trial Balance is Out of Balance</h4>
                                <p class="mb-0">Difference: UGX {{ number_format(abs($totalDebits - $totalCredits), 2) }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Account Type Summary</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Account Type</th>
                                    <th class="text-end">Total Debits</th>
                                    <th class="text-end">Total Credits</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $typeSummary = $groupedAccounts->map(function($accounts) {
                                        return [
                                            'debits' => collect($accounts)->sum('debit_balance'),
                                            'credits' => collect($accounts)->sum('credit_balance')
                                        ];
                                    });
                                @endphp
                                @foreach($typeSummary as $type => $summary)
                                <tr>
                                    <td>
                                        <span class="badge badge-{{ $type == 'asset' ? 'primary' : ($type == 'liability' ? 'warning' : ($type == 'equity' ? 'success' : ($type == 'income' ? 'info' : 'secondary'))) }}">
                                            {{ ucfirst($type) }}s
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-success">
                                            UGX {{ number_format($summary['debits'], 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-danger">
                                            UGX {{ number_format($summary['credits'], 2) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Ratios -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Financial Position Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @php
                            $assets = $groupedAccounts['asset'] ?? collect();
                            $liabilities = $groupedAccounts['liability'] ?? collect();
                            $equity = $groupedAccounts['equity'] ?? collect();
                            $income = $groupedAccounts['income'] ?? collect();
                            $expenses = $groupedAccounts['expense'] ?? collect();

                            $totalAssets = $assets->sum('debit_balance') - $assets->sum('credit_balance');
                            $totalLiabilities = $liabilities->sum('credit_balance') - $liabilities->sum('debit_balance');
                            $totalEquity = $equity->sum('credit_balance') - $equity->sum('debit_balance');
                            $totalIncome = $income->sum('credit_balance') - $income->sum('debit_balance');
                            $totalExpenses = $expenses->sum('debit_balance') - $expenses->sum('credit_balance');
                            $netIncome = $totalIncome - $totalExpenses;
                        @endphp

                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-primary">Total Assets</h6>
                                <h4 class="font-weight-bold text-primary">
                                    UGX {{ number_format($totalAssets, 2) }}
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-warning">Total Liabilities</h6>
                                <h4 class="font-weight-bold text-warning">
                                    UGX {{ number_format($totalLiabilities, 2) }}
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-success">Total Equity</h6>
                                <h4 class="font-weight-bold text-success">
                                    UGX {{ number_format($totalEquity, 2) }}
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-info">Net Income</h6>
                                <h4 class="font-weight-bold {{ $netIncome >= 0 ? 'text-success' : 'text-danger' }}">
                                    UGX {{ number_format($netIncome, 2) }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function printTrialBalance() {
    window.print();
}

function exportTrialBalance() {
    // Create CSV content
    let csvContent = "Account Code,Account Name,Account Type,Debit Balance,Credit Balance\n";
    
    // Add data rows
    const table = document.getElementById('trialBalanceTable');
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length === 5) {
            const accountCode = cells[0].textContent.trim();
            const accountName = cells[1].textContent.trim();
            const accountType = cells[2].textContent.trim();
            const debitBalance = cells[3].textContent.trim().replace('UGX ', '').replace(',', '') || '0';
            const creditBalance = cells[4].textContent.trim().replace('UGX ', '').replace(',', '') || '0';
            
            csvContent += `"${accountCode}","${accountName}","${accountType}","${debitBalance}","${creditBalance}"\n`;
        }
    });
    
    // Download CSV
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'trial_balance_{{ $date }}.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

// Print styles
const printStyles = `
    @media print {
        .btn, .card-header, .navbar, .sidebar { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        .table { font-size: 12px; }
        .container-fluid { padding: 0 !important; }
    }
`;

const styleSheet = document.createElement("style");
styleSheet.type = "text/css";
styleSheet.innerText = printStyles;
document.head.appendChild(styleSheet);
</script>
@endpush
