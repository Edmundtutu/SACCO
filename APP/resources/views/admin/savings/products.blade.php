@extends('admin.layouts.app')

@section('title', 'Savings Products')

@push('styles')
<style>
    :root {
        --primary: #2c3e50;
        --secondary: #3498db;
        --success: #2ecc71;
        --warning: #f39c12;
        --info: #17a2b8;
        --light: #ecf0f1;
        --dark: #34495e;
    }

    .dashboard-header {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        padding: 2rem 0;
        border-radius: 15px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .product-card {
        border-radius: 15px;
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
        margin-bottom: 1.5rem;
        border: none;
        box-shadow: 0 4px 8px rgba(0,0,0,0.08);
        height: 100%;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    }

    .product-card.wallet-card {
        border: 2px solid var(--warning);
        background: linear-gradient(135deg, #fff9e6, #fff);
    }

    .product-type-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 0.8rem;
        padding: 5px 10px;
        border-radius: 20px;
        z-index: 1;
    }

    .wallet-highlight {
        background-color: rgba(255, 193, 7, 0.15);
        border-left: 4px solid var(--warning);
        padding: 15px;
        border-radius: 0 8px 8px 0;
        margin-bottom: 2rem;
    }

    .product-image {
        height: 120px;
        background-color: var(--light);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        color: var(--dark);
        font-size: 3rem;
    }

    .interest-rate {
        font-size: 1.8rem;
        font-weight: bold;
        color: var(--success);
    }

    .feature-list {
        list-style-type: none;
        padding-left: 0;
    }

    .feature-list li {
        padding: 5px 0;
        position: relative;
        padding-left: 25px;
    }

    .feature-list li:before {
        content: "✓";
        position: absolute;
        left: 0;
        color: var(--success);
        font-weight: bold;
    }

    .wallet-feature {
        color: var(--warning);
        font-weight: bold;
    }

    .wallet-badge {
        background-color: var(--warning);
        color: white;
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: bold;
    }

    .comparison-table th {
        background-color: var(--primary);
        color: white;
        vertical-align: middle;
    }

    .comparison-table td {
        vertical-align: middle;
    }

    .nav-pills .nav-link.active {
        background-color: var(--secondary);
        border-radius: 20px;
    }

    .stats-box {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 1rem;
    }

    .inactive-product {
        opacity: 0.6;
        position: relative;
    }

    .inactive-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(255, 255, 255, 0.95);
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: bold;
        color: #dc3545;
        z-index: 10;
    }
</style>
@endpush

@section('content')
<!-- Dashboard Header -->
<div class="dashboard-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-5 fw-bold mb-2">
                    <i class="bi bi-gear-fill me-2"></i>Savings Products
                </h1>
                <p class="lead mb-0">Comprehensive range of savings solutions for members</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('admin.savings.products.create') }}" class="btn btn-light btn-lg me-2">
                    <i class="bi bi-plus-circle"></i> Create New Product
                </a>
                <button class="btn btn-outline-light btn-lg" onclick="window.location.reload()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
        </div>
    </div>
</div>

@if($products->isEmpty())
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h4 class="text-muted mt-3">No Savings Products Found</h4>
                <p class="text-muted">There are no savings products configured yet.</p>
            </div>
        </div>
    </div>
</div>
@else

<!-- Wallet Product Highlight (if exists) -->
@php
    $walletProduct = $products->firstWhere('name', 'Wallet Account');
@endphp

@if($walletProduct)
<div class="row mb-4">
    <div class="col-12">
        <div class="wallet-highlight">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-1">
                        <i class="bi bi-wallet2 me-2"></i>{{ $walletProduct->name }}
                    </h3>
                    <p class="mb-0">{{ $walletProduct->description }}</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="wallet-badge me-2">SPECIAL PRODUCT</span>
                    @if($walletProduct->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Product Cards -->
<div class="row">
    @foreach($products as $product)
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card product-card {{ $product->type == 'wallet' ? 'wallet-card' : '' }} {{ !$product->is_active ? 'inactive-product' : '' }}">
            @if(!$product->is_active)
                <div class="inactive-overlay">INACTIVE</div>
            @endif
            <div class="card-body position-relative">
                <!-- Product Type Badge -->
                <span class="badge product-type-badge 
                    {{ $product->type == 'special' ? 'bg-warning' : '' }}
                    {{ $product->type == 'compulsory' ? 'bg-primary' : '' }}
                    {{ $product->type == 'voluntary' ? 'bg-success' : '' }}
                    {{ $product->type == 'fixed_deposit' ? 'bg-info' : '' }}
                    {{ !in_array($product->type, ['wallet', 'compulsory', 'voluntary', 'fixed_deposit']) ? 'bg-secondary' : '' }}">
                    {{ ucfirst(str_replace('_', ' ', $product->type)) }}
                </span>

                <!-- Product Icon -->
                <div class="product-image">
                    @switch($product->name)
                        @case('Wallet Account')
                            <i class="bi bi-wallet2"></i>
                            @break
                        @case('Compulsory Savings')
                            <i class="bi bi-piggy-bank"></i>
                            @break
                        @case('Voluntary Savings')
                            <i class="bi bi-hand-thumbs-up"></i>
                            @break
                        @case('Fixed Deposit')
                            <i class="bi bi-lock"></i>
                            @break
                        @default
                            <i class="bi bi-cash-stack"></i>
                    @endswitch
                </div>

                <!-- Product Name & Description -->
                <h4 class="card-title">{{ $product->name }}</h4>
                <p class="card-text text-muted">{{ $product->description }}</p>
                <p class="small text-muted mb-3">
                    <strong>Code:</strong> {{ $product->code }}
                </p>

                <!-- Key Metrics -->
                <div class="row mt-3">
                    <div class="col-6">
                        <small class="text-muted">Minimum Balance</small>
                        <p class="mb-0 fw-bold">UGX {{ number_format($product->minimum_balance, 0) }}</p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Interest Rate</small>
                        <p class="interest-rate mb-0">{{ number_format($product->interest_rate, 2) }}%</p>
                    </div>
                </div>

                @if($product->maximum_balance)
                <div class="row mt-2">
                    <div class="col-12">
                        <small class="text-muted">Maximum Balance</small>
                        <p class="mb-0 fw-bold">UGX {{ number_format($product->maximum_balance, 0) }}</p>
                    </div>
                </div>
                @endif

                <!-- Key Features -->
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Key Features:</h6>
                        <ul class="feature-list small">
                            @if($product->allow_partial_withdrawals)
                                <li class="{{ $product->type == 'wallet' ? 'wallet-feature' : '' }}">
                                    Partial withdrawals allowed
                                </li>
                            @else
                                <li>Full withdrawal only</li>
                            @endif
                            
                            @if($product->withdrawal_fee > 0)
                                <li>Withdrawal fee: UGX {{ number_format($product->withdrawal_fee, 0) }}</li>
                            @else
                                <li class="{{ $product->type == 'wallet' ? 'wallet-feature' : '' }}">
                                    No withdrawal fees
                                </li>
                            @endif

                            @if($product->minimum_monthly_contribution)
                                <li>Min. monthly: UGX {{ number_format($product->minimum_monthly_contribution, 0) }}</li>
                            @endif

                            @if($product->maturity_period_months)
                                <li>Maturity: {{ $product->maturity_period_months }} months</li>
                            @endif

                            @if($product->minimum_notice_days)
                                <li>Notice period: {{ $product->minimum_notice_days }} days</li>
                            @endif

                            <li>Interest: {{ ucfirst($product->interest_calculation ?? 'Simple') }}</li>
                        </ul>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-2 mt-3">
                    <button class="btn btn-outline-{{ $product->type == 'wallet' ? 'warning' : 'primary' }}" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#details-{{ $product->id }}">
                        <i class="bi bi-eye"></i> View Details
                    </button>
                    <div class="btn-group mt-2" role="group">
                        <a href="{{ route('admin.savings.products.edit', $product->id) }}" 
                           class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                        @if($product->accounts()->count() == 0)
                        <button type="button" class="btn btn-sm btn-danger" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteModal{{ $product->id }}">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                        @else
                        <button type="button" class="btn btn-sm btn-secondary" disabled 
                                title="Cannot delete product with active accounts">
                            <i class="bi bi-lock"></i> Protected
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Collapsible Details -->
                <div class="collapse mt-3" id="details-{{ $product->id }}">
                    <div class="card card-body bg-light">
                        <h6>Additional Information</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td><strong>Interest Payment:</strong></td>
                                <td>{{ ucfirst(str_replace('_', ' ', $product->interest_payment_frequency ?? 'N/A')) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Accounts:</strong></td>
                                <td>{{ $product->accounts->count() }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if($product->accounts()->count() == 0)
    <div class="modal fade" id="deleteModal{{ $product->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-trash"></i> Delete Product</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this savings product?</p>
                    <p><strong>{{ $product->name }} ({{ $product->code }})</strong></p>
                    <p class="text-danger mb-0">This action cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('admin.savings.products.delete', $product->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Yes, Delete It
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endforeach
</div>

<!-- Detailed Information Tabs -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-comparison-tab" data-bs-toggle="pill" 
                                data-bs-target="#pills-comparison" type="button" role="tab">
                            <i class="bi bi-bar-chart"></i> Product Comparison
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-statistics-tab" data-bs-toggle="pill" 
                                data-bs-target="#pills-statistics" type="button" role="tab">
                            <i class="bi bi-graph-up"></i> Statistics
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="pills-tabContent">
                    <!-- Product Comparison Tab -->
                    <div class="tab-pane fade show active" id="pills-comparison" role="tabpanel">
                        <h5><i class="bi bi-table"></i> Savings Products Comparison</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered comparison-table">
                                <thead>
                                    <tr>
                                        <th>Feature</th>
                                        @foreach($products->take(4) as $product)
                                            <th>{{ $product->name }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Product Code</strong></td>
                                        @foreach($products->take(4) as $product)
                                            <td>{{ $product->code }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td><strong>Type</strong></td>
                                        @foreach($products->take(4) as $product)
                                            <td>{{ ucfirst(str_replace('_', ' ', $product->type)) }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td><strong>Minimum Balance</strong></td>
                                        @foreach($products->take(4) as $product)
                                            <td>UGX {{ number_format($product->minimum_balance, 0) }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td><strong>Interest Rate</strong></td>
                                        @foreach($products->take(4) as $product)
                                            <td class="text-success fw-bold">{{ number_format($product->interest_rate, 2) }}%</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td><strong>Withdrawal Fee</strong></td>
                                        @foreach($products->take(4) as $product)
                                            <td>UGX {{ number_format($product->withdrawal_fee, 0) }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td><strong>Partial Withdrawals</strong></td>
                                        @foreach($products->take(4) as $product)
                                            <td class="text-center">
                                                @if($product->allow_partial_withdrawals)
                                                    <i class="bi bi-check-circle-fill text-success"></i>
                                                @else
                                                    <i class="bi bi-x-circle-fill text-danger"></i>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td><strong>Interest Calculation</strong></td>
                                        @foreach($products->take(4) as $product)
                                            <td>{{ ucfirst($product->interest_calculation ?? 'Simple') }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td><strong>Status</strong></td>
                                        @foreach($products->take(4) as $product)
                                            <td>
                                                @if($product->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        @if($products->count() > 4)
                            <p class="text-muted small mt-2">
                                <i class="bi bi-info-circle"></i> Showing first 4 products. Total products: {{ $products->count() }}
                            </p>
                        @endif
                    </div>

                    <!-- Statistics Tab -->
                    <div class="tab-pane fade" id="pills-statistics" role="tabpanel">
                        <h5><i class="bi bi-graph-up"></i> Product Statistics</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="stats-box">
                                    <h3 class="text-primary mb-2">{{ $products->count() }}</h3>
                                    <p class="text-muted mb-0">Total Products</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-box">
                                    <h3 class="text-success mb-2">{{ $products->where('is_active', true)->count() }}</h3>
                                    <p class="text-muted mb-0">Active Products</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-box">
                                    <h3 class="text-info mb-2">{{ number_format($products->avg('interest_rate'), 2) }}%</h3>
                                    <p class="text-muted mb-0">Avg Interest Rate</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-box">
                                    <h3 class="text-warning mb-2">{{ $products->sum(function($p) { return $p->accounts->count(); }) }}</h3>
                                    <p class="text-muted mb-0">Total Accounts</p>
                                </div>
                            </div>
                        </div>

                        <!-- Product Type Breakdown -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6>Products by Type</h6>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Product Type</th>
                                                <th>Count</th>
                                                <th>Active</th>
                                                <th>Total Accounts</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $groupedProducts = $products->groupBy('type');
                                            @endphp
                                            @foreach($groupedProducts as $type => $typeProducts)
                                            <tr>
                                                <td>
                                                    <i class="bi bi-{{ $type == 'wallet' ? 'wallet2' : ($type == 'compulsory' ? 'piggy-bank' : 'cash-stack') }}"></i>
                                                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                                                </td>
                                                <td>{{ $typeProducts->count() }}</td>
                                                <td>{{ $typeProducts->where('is_active', true)->count() }}</td>
                                                <td>{{ $typeProducts->sum(function($p) { return $p->accounts->count(); }) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
