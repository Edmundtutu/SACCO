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
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-plus"></i> Add Member
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.members.create', ['type' => 'individual']) }}">
                        <i class="bi bi-person"></i> Individual Member
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.members.create', ['type' => 'vsla']) }}">
                        <i class="bi bi-people"></i> VSLA Group
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.members.create', ['type' => 'mfi']) }}">
                        <i class="bi bi-building"></i> MFI Institution
                    </a></li>
                </ul>
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
                        <div class="col-md-2">
                            <label for="status" class="form-label">User Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="approval_status" class="form-label">Approval Status</label>
                            <select class="form-select" id="approval_status" name="approval_status">
                                <option value="">All</option>
                                <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('approval_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
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
                                <th>Type</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Approval</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $member)
                            <tr>
                                <td>
                                    <strong>{{ $member->membership->id ?? 'N/A' }}</strong>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $member->name }}</strong>
                                        <br><small class="text-muted">{{ $member->email }}</small>
                                    </div>
                                </td>
                                <td>
                                    @if($member->membership && $member->membership->profile)
                                        @php
                                            $profileType = class_basename($member->membership->profile_type);
                                        @endphp
                                        @switch($profileType)
                                            @case('IndividualProfile')
                                                <span class="badge bg-primary">Individual</span>
                                                @break
                                            @case('VslaProfile')
                                                <span class="badge bg-info">VSLA</span>
                                                @break
                                            @case('MfiProfile')
                                                <span class="badge bg-warning">MFI</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $profileType }}</span>
                                        @endswitch
                                    @else
                                        <span class="badge bg-light text-dark">No Profile</span>
                                    @endif
                                </td>
                                <td>
                                    @if($member->membership && $member->membership->profile)
                                        @if($member->membership->profile instanceof App\Models\Membership\IndividualProfile)
                                            {{ $member->membership->profile->phone ?? $member->email }}
                                            @if($member->membership->profile->national_id)
                                                <br><small class="text-muted">ID: {{ $member->membership->profile->national_id }}</small>
                                            @endif
                                        @elseif($member->membership->profile instanceof App\Models\Membership\VslaProfile)
                                            {{ $member->membership->profile->village ?? 'N/A' }}
                                            <br><small class="text-muted">{{ $member->membership->profile->district ?? 'N/A' }}</small>
                                        @elseif($member->membership->profile instanceof App\Models\Membership\MfiProfile)
                                            {{ $member->membership->profile->contact_number ?? 'N/A' }}
                                            <br><small class="text-muted">{{ $member->membership->profile->contact_person ?? 'N/A' }}</small>
                                        @endif
                                    @else
                                        {{ $member->email }}
                                    @endif
                                </td>
                                <td>
                                    @switch($member->status)
                                        @case('active')
                                            <span class="badge bg-success">Active</span>
                                            @break
                                        @case('pending_approval')
                                            <span class="badge bg-warning">Pending</span>
                                            @break
                                        @case('suspended')
                                            <span class="badge bg-danger">Suspended</span>
                                            @break
                                        @case('inactive')
                                            <span class="badge bg-secondary">Inactive</span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark">{{ str_replace('_', ' ', ucfirst($member->status)) }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    @if($member->membership)
                                        @switch($member->membership->approval_status)
                                            @case('approved')
                                                <span class="badge bg-success">Approved</span>
                                                @break
                                            @case('pending')
                                                <span class="badge bg-warning">Pending</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($member->membership->approval_status) }}</span>
                                        @endswitch
                                    @else
                                        <span class="badge bg-light text-dark">No Membership</span>
                                    @endif
                                </td>
                                <td>{{ $member->created_at->format('M d, Y') }}</td>
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
                                        
                                        @if($member->membership && $member->membership->approval_status == 'pending')
                                            @php
                                                $currentUser = auth()->user();
                                                $membership = $member->membership;
                                            @endphp
                                            
                                            @if($currentUser->role == 'staff_level_1' && !$membership->approved_by_level_1)
                                                <button type="button" 
                                                        class="btn btn-outline-success btn-sm approve-btn" 
                                                        data-membership-id="{{ $membership->id }}"
                                                        data-level="1"
                                                        title="Level 1 Approval">
                                                    <i class="bi bi-check-circle"></i> L1
                                                </button>
                                            @elseif($currentUser->role == 'staff_level_2' && $membership->approved_by_level_1 && !$membership->approved_by_level_2)
                                                <button type="button" 
                                                        class="btn btn-outline-success btn-sm approve-btn" 
                                                        data-membership-id="{{ $membership->id }}"
                                                        data-level="2"
                                                        title="Level 2 Approval">
                                                    <i class="bi bi-check-circle"></i> L2
                                                </button>
                                            @elseif($currentUser->role == 'staff_level_3' && $membership->approved_by_level_2 && !$membership->approved_by_level_3)
                                                <button type="button" 
                                                        class="btn btn-outline-success btn-sm approve-btn" 
                                                        data-membership-id="{{ $membership->id }}"
                                                        data-level="3"
                                                        title="Level 3 Approval & Activate">
                                                    <i class="bi bi-check-circle"></i> L3
                                                </button>
                                            @endif
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

@push('scripts')
<script>
$(document).ready(function() {
    $('.approve-btn').click(function() {
        const membershipId = $(this).data('membership-id');
        const level = $(this).data('level');
        const button = $(this);
        
        if (confirm(`Approve this membership at level ${level}?`)) {
            $.ajax({
                url: `/admin/memberships/${membershipId}/approve-level-${level}`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    button.prop('disabled', true).html('<i class="bi bi-clock"></i> Processing...');
                },
                success: function(response) {
                    alert(response.message);
                    location.reload();
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
                    button.prop('disabled', false).html(`<i class="bi bi-check-circle"></i> L${level}`);
                }
            });
        }
    });
});
</script>
@endpush
@endsection