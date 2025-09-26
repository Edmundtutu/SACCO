@extends('admin.layouts.app')

@section('title', 'Loan Products')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Loan Products</h1>
            <p class="text-muted">Manage loan products and their terms</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.loans.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Loans
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProductModal">
                <i class="fas fa-plus"></i> Create Product
            </button>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Products
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $products->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cogs fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Products
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $products->where('is_active', true)->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Inactive Products
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $products->where('is_active', false)->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-pause-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Avg Interest Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $products->avg('interest_rate') ? number_format($products->avg('interest_rate'), 1) : 0 }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-cogs"></i> Loan Products
            </h6>
            <div class="d-flex gap-2">
                <span class="badge bg-primary">{{ $products->count() }} total</span>
                <button class="btn btn-sm btn-outline-primary" onclick="refreshProducts()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($products->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover" id="productsTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Product Name</th>
                            <th>Interest Rate</th>
                            <th>Min Amount</th>
                            <th>Max Amount</th>
                            <th>Min Period</th>
                            <th>Max Period</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>
                                <div>
                                    <strong class="text-nowrap">{{ $product->name }}</strong><br>
                                    <small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="badge-icon bg-info">{{ $product->interest_rate }}% p.a.</span>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong class="text-nowrap">UGX {{ number_format($product->minimum_amount, 0) }}</strong>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong class="text-nowrap">UGX {{ number_format($product->maximum_amount, 0) }}</strong>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="badge-icon bg-secondary">{{ $product->minimum_period_months }} months</span>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="badge-icon bg-secondary">{{ $product->maximum_period_months }} months</span>
                                </div>
                            </td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge-icon bg-success">Active</span>
                                @else
                                    <span class="badge-icon bg-warning">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <strong class="text-nowrap">{{ $product->created_at ? $product->created_at->format('M d, Y') : 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $product->created_at ? $product->created_at->format('H:i') : 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary" 
                                            title="Edit" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editProductModal{{ $product->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    @if($product->is_active)
                                    <form action="{{ route('admin.loan-products.deactivate', $product->id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-warning" 
                                                title="Deactivate" 
                                                onclick="return confirm('Deactivate this product?')">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                    </form>
                                    @else
                                    <form action="{{ route('admin.loan-products.activate', $product->id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-success" 
                                                title="Activate" 
                                                onclick="return confirm('Activate this product?')">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                <h5 class="mt-3 text-muted">No loan products found</h5>
                <p class="text-muted">Create your first loan product to get started.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProductModal">
                    <i class="fas fa-plus"></i> Create First Product
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Product Modal -->
<div class="modal fade" id="createProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Loan Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.loan-products.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Product Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="interest_rate" class="form-label">Interest Rate (% p.a.) *</label>
                                <input type="number" class="form-control" id="interest_rate" name="interest_rate" 
                                       step="0.01" min="0" max="100" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="min_amount" class="form-label">Minimum Amount *</label>
                                <input type="number" class="form-control" id="min_amount" name="min_amount" 
                                       min="1000" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_amount" class="form-label">Maximum Amount *</label>
                                <input type="number" class="form-control" id="max_amount" name="max_amount" 
                                       min="1000" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="min_period" class="form-label">Minimum Period (months) *</label>
                                <input type="number" class="form-control" id="min_period" name="min_period" 
                                       min="1" max="60" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_period" class="form-label">Maximum Period (months) *</label>
                                <input type="number" class="form-control" id="max_period" name="max_period" 
                                       min="1" max="60" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">
                                Active Product
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modals -->
@foreach($products as $product)
<div class="modal fade" id="editProductModal{{ $product->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Loan Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.loan-products.update', $product->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name{{ $product->id }}" class="form-label">Product Name *</label>
                                <input type="text" class="form-control" id="name{{ $product->id }}" name="name" 
                                       value="{{ $product->name }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="interest_rate{{ $product->id }}" class="form-label">Interest Rate (% p.a.) *</label>
                                <input type="number" class="form-control" id="interest_rate{{ $product->id }}" name="interest_rate" 
                                       step="0.01" min="0" max="100" value="{{ $product->interest_rate }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="min_amount{{ $product->id }}" class="form-label">Minimum Amount *</label>
                                <input type="number" class="form-control" id="min_amount{{ $product->id }}" name="min_amount" 
                                       min="1000" value="{{ $product->minimum_amount }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_amount{{ $product->id }}" class="form-label">Maximum Amount *</label>
                                <input type="number" class="form-control" id="max_amount{{ $product->id }}" name="max_amount" 
                                       min="1000" value="{{ $product->maximum_amount }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="min_period{{ $product->id }}" class="form-label">Minimum Period (months) *</label>
                                <input type="number" class="form-control" id="min_period{{ $product->id }}" name="min_period" 
                                       min="1" max="60" value="{{ $product->minimum_period_months }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_period{{ $product->id }}" class="form-label">Maximum Period (months) *</label>
                                <input type="number" class="form-control" id="max_period{{ $product->id }}" name="max_period" 
                                       min="1" max="60" value="{{ $product->maximum_period_months }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description{{ $product->id }}" class="form-label">Description</label>
                        <textarea class="form-control" id="description{{ $product->id }}" name="description" rows="3">{{ $product->description }}</textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active{{ $product->id }}" name="is_active" value="1" 
                                   {{ $product->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active{{ $product->id }}">
                                Active Product
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection

@push('scripts')
<script>
function refreshProducts() {
    location.reload();
}
</script>
@endpush
