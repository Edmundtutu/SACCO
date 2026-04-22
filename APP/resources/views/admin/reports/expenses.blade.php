@extends('admin.layouts.app')

@section('title', 'Expense Report')

@section('content')
<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Expense Report</h1>
            <p class="text-muted">Operational expenses by category</p>
        </div>
        <a href="{{ route('admin.expenses.create') }}" class="btn btn-danger btn-sm">
            <i class="fas fa-plus me-1"></i>Record Expense
        </a>
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

    <!-- Date/Category Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.expenses') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $key => $cat)
                                <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>
                                    {{ $cat['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm me-2">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.reports.expenses') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Category Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Grand Total</div>
                    <div class="h4 fw-bold text-danger">UGX {{ number_format($grandTotal, 2) }}</div>
                    <small class="text-muted">{{ $dateFrom }} to {{ $dateTo }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Total Records</div>
                    <div class="h4 fw-bold">{{ $expenses->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Breakdown -->
    @if($byCategory->count())
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">By Category</h6>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Category</th>
                        <th>GL Code</th>
                        <th class="text-end">Count</th>
                        <th class="text-end">Total (UGX)</th>
                        <th class="text-end">% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($byCategory as $cat => $data)
                    <tr>
                        <td>{{ $categories[$cat]['name'] ?? ucfirst($cat) }}</td>
                        <td><code>{{ $categories[$cat]['code'] ?? '—' }}</code></td>
                        <td class="text-end">{{ $data['count'] }}</td>
                        <td class="text-end fw-semibold text-danger">{{ number_format($data['total'], 2) }}</td>
                        <td class="text-end">
                            {{ $grandTotal > 0 ? number_format(($data['total'] / $grandTotal) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="3">Total</td>
                        <td class="text-end text-danger">{{ number_format($grandTotal, 2) }}</td>
                        <td class="text-end">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    <!-- Detailed List -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Expense Detail</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Receipt No.</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Description</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                        <tr>
                            <td>
                                <a href="{{ route('admin.expenses.show', $expense->id) }}">
                                    <code>{{ $expense->receipt_number }}</code>
                                </a>
                            </td>
                            <td>{{ $categories[$expense->category]['name'] ?? ucfirst($expense->category) }}</td>
                            <td class="text-danger fw-semibold">{{ number_format($expense->amount, 2) }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $expense->payment_method)) }}</td>
                            <td>{{ Str::limit($expense->description, 40) }}</td>
                            <td>{{ $expense->created_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No expense records in this period.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
