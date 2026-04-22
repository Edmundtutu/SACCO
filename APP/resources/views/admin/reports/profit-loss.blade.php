@extends('admin.layouts.app')

@section('title', 'Profit & Loss Statement')

@section('content')
<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Profit & Loss Statement</h1>
            <p class="text-muted">Income vs Expenses — GL-derived</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-print me-1"></i>Print
        </button>
    </div>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            @foreach($breadcrumbs as $crumb)
                @if($loop->last)
                    <li class="breadcrumb-item active">{{ $crumb['text'] }}</li>
                @else
                    <li class="breadcrumb-item"><a href="{{ $crumb['url'] }}">{{ $crumb['text'] }}</a></li>
                @endif
            @endforeach
        </ol>
    </nav>

    <!-- Period Filter -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.profit-loss') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">From</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">To</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm me-2">
                            <i class="fas fa-search me-1"></i>Refresh
                        </button>
                        <a href="{{ route('admin.reports.profit-loss') }}" class="btn btn-outline-secondary btn-sm">YTD</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-success shadow py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Income</div>
                    <div class="h4 fw-bold text-success">UGX {{ number_format($totalIncome, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-danger shadow py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Expenses</div>
                    <div class="h4 fw-bold text-danger">UGX {{ number_format($totalExpenses, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-{{ $netProfit >= 0 ? 'primary' : 'warning' }} shadow py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-{{ $netProfit >= 0 ? 'primary' : 'warning' }} text-uppercase mb-1">
                        Net {{ $netProfit >= 0 ? 'Profit' : 'Loss' }}
                    </div>
                    <div class="h4 fw-bold text-{{ $netProfit >= 0 ? 'primary' : 'warning' }}">
                        UGX {{ number_format(abs($netProfit), 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Income Section -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success bg-opacity-10">
                    <h6 class="m-0 fw-bold text-success">
                        <i class="fas fa-arrow-up me-2"></i>INCOME
                    </h6>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            <tr>
                                <td>Loan Interest Income <code class="text-muted">(4001)</code></td>
                                <td class="text-end text-success fw-semibold">{{ number_format($loanInterestIncome, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Fee Income <code class="text-muted">(4002)</code></td>
                                <td class="text-end text-success fw-semibold">{{ number_format($feeIncome, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Penalty Income <code class="text-muted">(4003)</code></td>
                                <td class="text-end text-success fw-semibold">{{ number_format($penaltyIncome, 2) }}</td>
                            </tr>

                            @if($incomeByCategory->count())
                            <tr class="table-light">
                                <td colspan="2" class="fw-semibold">Non-Loan Income (Phase 2)</td>
                            </tr>
                            @foreach($incomeByCategory as $row)
                            <tr>
                                <td class="ps-4">
                                    {{ $incomeCategories[$row->category]['name'] ?? ucfirst($row->category) }}
                                    <code class="text-muted">({{ $incomeCategories[$row->category]['code'] ?? '—' }})</code>
                                </td>
                                <td class="text-end text-success fw-semibold">{{ number_format($row->total, 2) }}</td>
                            </tr>
                            @endforeach
                            @endif

                            <tr class="table-success fw-bold border-top">
                                <td>TOTAL INCOME</td>
                                <td class="text-end text-success fs-6">UGX {{ number_format($totalIncome, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Expenses Section -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-danger bg-opacity-10">
                    <h6 class="m-0 fw-bold text-danger">
                        <i class="fas fa-arrow-down me-2"></i>EXPENSES
                    </h6>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            <tr>
                                <td>Interest Expense on Savings <code class="text-muted">(5002)</code></td>
                                <td class="text-end text-danger fw-semibold">{{ number_format($interestExpense, 2) }}</td>
                            </tr>

                            @if($expenseByCategory->count())
                            <tr class="table-light">
                                <td colspan="2" class="fw-semibold">Operational Expenses (Phase 2)</td>
                            </tr>
                            @foreach($expenseByCategory as $row)
                            <tr>
                                <td class="ps-4">
                                    {{ $expenseCategories[$row->category]['name'] ?? ucfirst($row->category) }}
                                    <code class="text-muted">({{ $expenseCategories[$row->category]['code'] ?? '—' }})</code>
                                </td>
                                <td class="text-end text-danger fw-semibold">{{ number_format($row->total, 2) }}</td>
                            </tr>
                            @endforeach
                            @endif

                            <tr class="table-danger fw-bold border-top">
                                <td>TOTAL EXPENSES</td>
                                <td class="text-end text-danger fs-6">UGX {{ number_format($totalExpenses, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Net -->
            <div class="card shadow border-{{ $netProfit >= 0 ? 'success' : 'warning' }}">
                <div class="card-body text-center py-4">
                    <div class="text-uppercase text-muted small mb-1">
                        NET {{ $netProfit >= 0 ? 'PROFIT' : 'LOSS' }}
                    </div>
                    <div class="display-6 fw-bold text-{{ $netProfit >= 0 ? 'success' : 'danger' }}">
                        UGX {{ number_format(abs($netProfit), 2) }}
                    </div>
                    <small class="text-muted">{{ $dateFrom }} — {{ $dateTo }}</small>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
