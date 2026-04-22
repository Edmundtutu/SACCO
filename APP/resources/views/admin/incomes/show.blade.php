@extends('admin.layouts.app')

@section('title', 'Income Details')

@section('content')
<div class="container-fluid">

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

    <div class="row">
        <!-- Income Info -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-success">
                        <i class="fas fa-hand-holding-usd me-2"></i>Income Record
                    </h6>
                    <a href="{{ route('admin.incomes.receipt', $income->id) }}"
                       class="btn btn-sm btn-outline-secondary" target="_blank">
                        <i class="fas fa-print me-1"></i>Print Receipt
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th class="text-muted" style="width:40%">Receipt No.</th>
                                <td><code class="text-success">{{ $income->receipt_number }}</code></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Category</th>
                                @php $cats = config('financial.income_categories', []); @endphp
                                <td>{{ $cats[$income->category]['name'] ?? ucfirst($income->category) }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">GL Account</th>
                                <td>{{ $income->gl_account_code }} — {{ $income->gl_account_name }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Amount</th>
                                <td class="fw-bold text-success fs-5">UGX {{ number_format($income->amount, 2) }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Payment Method</th>
                                <td>{{ ucwords(str_replace('_', ' ', $income->payment_method)) }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Payment Reference</th>
                                <td>{{ $income->payment_reference ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Description</th>
                                <td>{{ $income->description ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Payer Member</th>
                                <td>{{ $income->payerMember?->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Recorded By</th>
                                <td>{{ $income->recordedBy?->name ?? 'System' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Date</th>
                                <td>{{ $income->created_at->format('d M Y H:i:s') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Transaction & GL -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-exchange-alt me-2"></i>Transaction Details
                    </h6>
                </div>
                <div class="card-body">
                    @if($income->transaction)
                    <p class="mb-1">
                        <strong>Transaction #:</strong>
                        <a href="{{ route('admin.transactions.show', $income->transaction->id) }}">
                            {{ $income->transaction->transaction_number }}
                        </a>
                    </p>
                    <p class="mb-1">
                        <strong>Status:</strong>
                        <span class="badge bg-{{ $income->transaction->status === 'completed' ? 'success' : 'warning' }}">
                            {{ ucfirst($income->transaction->status) }}
                        </span>
                    </p>
                    <p class="mb-3">
                        <strong>Type:</strong> {{ ucfirst($income->transaction->type) }}
                    </p>

                    @if($income->transaction->generalLedgerEntries->count())
                    <h6 class="fw-bold mb-2">GL Entries</h6>
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Account</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($income->transaction->generalLedgerEntries as $entry)
                            <tr>
                                <td>
                                    <span class="text-muted small">{{ $entry->account_code }}</span><br>
                                    {{ $entry->account_name }}
                                </td>
                                <td class="text-end">
                                    {{ $entry->debit_amount > 0 ? number_format($entry->debit_amount, 2) : '—' }}
                                </td>
                                <td class="text-end">
                                    {{ $entry->credit_amount > 0 ? number_format($entry->credit_amount, 2) : '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif

                    @else
                        <p class="text-muted">No transaction linked.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-2">
        <a href="{{ route('admin.incomes.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Income
        </a>
    </div>

</div>
@endsection
