@extends('admin.layouts.app')

@section('title', 'Members Management')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">Members Management</h1>
                <p class="text-muted">Manage member registrations, profiles, and account status</p>
            </div>
            <div>
                <a href="#" class="btn btn-primary">
                    <i class="bi bi-person-plus"></i> Add Member
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.members.index') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search Members</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Name, email, member number, phone...">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Search
                            </button>
                            <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Members Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-people"></i> Members List 
                    <span class="badge bg-primary ms-2">{{ $members->total() }} total</span>
                </h5>
            </div>
            <div class="card-body">
                @if($members->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Member #</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Accounts</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $member)
                            <tr>
                                <td>
                                    <strong>{{ $member->member_number ?? 'N/A' }}</strong>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $member->name }}</strong>
                                        @if($member->national_id)
                                        <br><small class="text-muted">ID: {{ $member->national_id }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $member->email }}</td>
                                <td>{{ $member->phone ?? 'N/A' }}</td>
                                <td>
                                    @switch($member->status)
                                        @case('active')
                                            <span class="badge bg-success">Active</span>
                                            @break
                                        @case('pending')
                                            <span class="badge bg-warning">Pending</span>
                                            @break
                                        @case('suspended')
                                            <span class="badge bg-danger">Suspended</span>
                                            @break
                                        @case('inactive')
                                            <span class="badge bg-secondary">Inactive</span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark">{{ ucfirst($member->status) }}</span>
                                    @endswitch
                                </td>
                                <td>{{ $member->created_at->format('M d, Y') }}</td>
                                <td>
                                    <small class="text-muted">
                                        {{ $member->accounts->count() }} account(s)<br>
                                        Balance: KSh {{ number_format($member->accounts->sum('balance'), 2) }}
                                    </small>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.members.show', $member->id) }}" 
                                           class="btn btn-outline-primary btn-sm" 
                                           title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.members.edit', $member->id) }}" 
                                           class="btn btn-outline-secondary btn-sm" 
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        @if($member->status == 'pending')
                                        <form action="{{ route('admin.members.approve', $member->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-outline-success btn-sm" 
                                                    title="Approve" 
                                                    onclick="return confirm('Approve this member?')">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>
                                        @endif
                                        
                                        @if($member->status == 'active')
                                        <form action="{{ route('admin.members.suspend', $member->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-outline-warning btn-sm" 
                                                    title="Suspend" 
                                                    onclick="return confirm('Suspend this member?')">
                                                <i class="bi bi-pause-circle"></i>
                                            </button>
                                        </form>
                                        @endif
                                        
                                        @if($member->status == 'suspended')
                                        <form action="{{ route('admin.members.activate', $member->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-outline-success btn-sm" 
                                                    title="Activate" 
                                                    onclick="return confirm('Activate this member?')">
                                                <i class="bi bi-play-circle"></i>
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
                
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <small class="text-muted">
                            Showing {{ $members->firstItem() }} to {{ $members->lastItem() }} of {{ $members->total() }} results
                        </small>
                    </div>
                    <div>
                        {{ $members->appends(request()->query())->links() }}
                    </div>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-people display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No members found</h4>
                    <p class="text-muted">Try adjusting your search criteria or add new members.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection