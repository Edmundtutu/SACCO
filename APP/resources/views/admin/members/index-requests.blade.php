@extends('admin.layouts.app')

@section('title', 'Membership Requests')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">Membership Requests</h1>
                <p class="text-muted">Review new membership applications and approve at your level</p>
            </div>
        </div>
    </div>
</div>
<!-- Search and Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.members.requests') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search Requests</label>
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
                            <a href="{{ route('admin.members.requests') }}" class="btn btn-outline-secondary">
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-people"></i> Membership Requests
                    <span class="badge bg-primary ms-2">{{ $members->total() }} total</span>
                </h5>
                <div class="small text-muted">
                    <i class="bi bi-info-circle"></i> Approvals require sequential verification: L1 → L2 → L3
                </div>
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
                                <th>Progress</th>
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
                                    <td>
                                        @php $ms = $member->membership; @endphp
                                        @if($ms)
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge {{ $ms->approved_by_level_1 ? 'bg-success' : 'bg-light text-dark' }}">
                                                    <i class="bi {{ $ms->approved_by_level_1 ? 'bi-check-circle-fill' : 'bi-circle' }}"></i> L1
                                                </span>
                                                <span class="badge {{ $ms->approved_by_level_2 ? 'bg-success' : 'bg-light text-dark' }}">
                                                    <i class="bi {{ $ms->approved_by_level_2 ? 'bi-check-circle-fill' : 'bi-circle' }}"></i> L2
                                                </span>
                                                <span class="badge {{ $ms->approved_by_level_3 ? 'bg-success' : 'bg-light text-dark' }}">
                                                    <i class="bi {{ $ms->approved_by_level_3 ? 'bi-check-circle-fill' : 'bi-circle' }}"></i> L3
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $member->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.members.show', $member->id) }}" class="btn btn-outline-primary btn-sm" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.members.edit', $member->id) }}" class="btn btn-outline-secondary btn-sm" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            @php $membership = $member->membership; @endphp
                                            @if($membership && $membership->approval_status == 'pending')
                                                {{-- Level 1 button --}}
                                                @can('approve_level_1', $membership)
                                                    @if(!$membership->approved_by_level_1)
                                                        <button type="button"
                                                                class="btn btn-outline-success btn-sm approve-btn"
                                                                data-membership-id="{{ $membership->id }}"
                                                                data-level="1"
                                                                title="Approve Level 1">
                                                            <i class="bi bi-check-circle"></i> L1
                                                        </button>
                                                    @endif
                                                @endcan

                                                {{-- Level 2 button (disabled reason if L1 not yet) --}}
                                                @if(!$membership->approved_by_level_2)
                                                    @can('approve_level_2', $membership)
                                                        @if($membership->approved_by_level_1)
                                                            <button type="button"
                                                                    class="btn btn-outline-success btn-sm approve-btn"
                                                                    data-membership-id="{{ $membership->id }}"
                                                                    data-level="2"
                                                                    title="Approve Level 2">
                                                                <i class="bi bi-check-circle"></i> L2
                                                            </button>
                                                        @else
                                                            <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Waiting for Level 1 approval">
                                                                <i class="bi bi-hourglass"></i> L2
                                                            </button>
                                                        @endif
                                                    @endcan
                                                @endif

                                                {{-- Level 3 button (disabled reason if L2 not yet) --}}
                                                @if(!$membership->approved_by_level_3)
                                                    @can('approve_level_3', $membership)
                                                        @if($membership->approved_by_level_2)
                                                            <button type="button"
                                                                    class="btn btn-outline-success btn-sm approve-btn"
                                                                    data-membership-id="{{ $membership->id }}"
                                                                    data-level="3"
                                                                    title="Approve Level 3 & Activate">
                                                                <i class="bi bi-check-circle"></i> L3
                                                            </button>
                                                        @else
                                                            <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Waiting for Level 2 approval">
                                                                <i class="bi bi-hourglass"></i> L3
                                                            </button>
                                                        @endif
                                                    @endcan
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
                            // On success, the record may move to next step or disappear if fully approved
                            location.reload();
                        },
                        error: function(xhr) {
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Something went wrong';
                            alert('Error: ' + msg);
                            button.prop('disabled', false);
                            const label = button.data('level');
                            button.html(`<i class="bi bi-check-circle"></i> L${label}`);
                        }
                    });
                }
            });
        });
    </script>
@endpush
@endsection
