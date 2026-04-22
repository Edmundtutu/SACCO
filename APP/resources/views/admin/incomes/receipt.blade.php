<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income Receipt — {{ $income->receipt_number }}</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #222; margin: 0; padding: 20px; }
        .receipt { max-width: 480px; margin: 0 auto; border: 1px solid #ccc; padding: 24px; border-radius: 6px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0 0 4px; font-size: 18px; color: #27ae60; }
        .header p { margin: 2px 0; font-size: 12px; color: #666; }
        .divider { border-top: 1px dashed #bbb; margin: 14px 0; }
        .row { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .row .label { color: #666; }
        .row .value { font-weight: 600; text-align: right; }
        .amount-row { font-size: 20px; color: #27ae60; font-weight: 700; }
        .footer { text-align: center; margin-top: 20px; font-size: 11px; color: #888; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
            .receipt { border: none; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            @php $tenant = tenant(); @endphp
            <h2>{{ $tenant?->sacco_name ?? 'SACCO' }}</h2>
            <p>INCOME RECEIPT</p>
            <p>{{ now()->format('d M Y, H:i') }}</p>
        </div>

        <div class="divider"></div>

        <div class="row">
            <span class="label">Receipt No.</span>
            <span class="value">{{ $income->receipt_number }}</span>
        </div>
        <div class="row">
            <span class="label">Transaction No.</span>
            <span class="value">{{ $income->transaction?->transaction_number ?? '—' }}</span>
        </div>
        <div class="row">
            <span class="label">Category</span>
            @php $cats = config('financial.income_categories', []); @endphp
            <span class="value">{{ $cats[$income->category]['name'] ?? ucfirst($income->category) }}</span>
        </div>
        <div class="row">
            <span class="label">GL Account</span>
            <span class="value">{{ $income->gl_account_code }}</span>
        </div>
        <div class="row">
            <span class="label">Payment Method</span>
            <span class="value">{{ ucwords(str_replace('_', ' ', $income->payment_method)) }}</span>
        </div>
        @if($income->payment_reference)
        <div class="row">
            <span class="label">Payment Reference</span>
            <span class="value">{{ $income->payment_reference }}</span>
        </div>
        @endif
        @if($income->payerMember)
        <div class="row">
            <span class="label">Received From</span>
            <span class="value">{{ $income->payerMember->name }}</span>
        </div>
        @endif
        @if($income->description)
        <div class="row">
            <span class="label">Description</span>
            <span class="value" style="max-width:240px;text-align:right;">{{ $income->description }}</span>
        </div>
        @endif
        <div class="row">
            <span class="label">Recorded By</span>
            <span class="value">{{ $income->recordedBy?->name ?? 'System' }}</span>
        </div>
        <div class="row">
            <span class="label">Date</span>
            <span class="value">{{ $income->created_at->format('d M Y H:i') }}</span>
        </div>

        <div class="divider"></div>

        <div class="row amount-row">
            <span>TOTAL RECEIVED</span>
            <span>UGX {{ number_format($income->amount, 2) }}</span>
        </div>

        <div class="divider"></div>
        <div class="footer">
            <p>This is a computer-generated receipt and does not require a signature.</p>
            <p>{{ $tenant?->sacco_name ?? 'SACCO' }} &mdash; Thank you</p>
        </div>
    </div>

    <div class="no-print" style="text-align:center;margin-top:20px;">
        <button onclick="window.print()" style="padding:8px 20px;cursor:pointer;">Print Receipt</button>
        <a href="{{ route('admin.incomes.show', $income->id) }}"
           style="margin-left:10px;text-decoration:none;">Back</a>
    </div>
</body>
</html>
