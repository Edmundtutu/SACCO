@extends('admin.layouts.app')

@section('title', 'SACCO Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-building"></i> SACCO Management</h1>
        <a href="{{ route('admin.tenants.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New SACCO
        </a>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total SACCOs</h5>
                    <h2 class="mb-0">{{ $stats['total'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Active</h5>
                    <h2 class="mb-0 text-success">{{ $stats['active'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">On Trial</h5>
                    <h2 class="mb-0 text-warning">{{ $stats['trial'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Suspended</h5>
                    <h2 class="mb-0 text-danger">{{ $stats['suspended'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Tenants Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">All SACCOs</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped datatable">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Members</th>
                        <th>Loans</th>
                        <th>Plan</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tenants as $tenant)
                    <tr>
                        <td><code>{{ $tenant->sacco_code }}</code></td>
                        <td>{{ $tenant->sacco_name }}</td>
                        <td>
                            @if($tenant->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif($tenant->status === 'trial')
                                <span class="badge bg-warning">Trial</span>
                            @elseif($tenant->status === 'suspended')
                                <span class="badge bg-danger">Suspended</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $tenant->users_count }} / {{ $tenant->max_members }}</td>
                        <td>{{ $tenant->loans_count }} / {{ $tenant->max_loans }}</td>
                        <td>{{ ucfirst($tenant->subscription_plan) }}</td>
                        <td>{{ $tenant->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('admin.tenants.show', $tenant) }}" class="btn btn-sm btn-info" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.tenants.edit', $tenant) }}" class="btn btn-sm btn-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
