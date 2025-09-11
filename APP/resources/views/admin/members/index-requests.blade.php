@extends('admin.layouts.app')

@section('title', 'Membership Requests')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-title">Membership Requests</h1>
                    <span
                        class="role-badge badge">{{ Str::headline(str_replace('_', ' ', auth()->user()->role ?? 'admin')) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.members.requests') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Profile Type</label>
                        <select class="form-select" name="profile_type">
                            <option value="">All Types</option>
                            <option value="individual" {{ request('profile_type') == 'individual' ? 'selected' : '' }}>Individual
                            </option>
                            <option value="vsla" {{ request('profile_type') == 'vsla' ? 'selected' : '' }}>VSLA</option>
                            <option value="mfi" {{ request('profile_type') == 'mfi' ? 'selected' : '' }}>MFI</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="stage">
                            <option value="">All Statuses</option>
                            <option value="level1" {{ request('stage') == 'level1' ? 'selected' : '' }}>Waiting Level 1</option>
                            <option value="level2" {{ request('stage') == 'level2' ? 'selected' : '' }}>Waiting Level 2</option>
                            <option value="level3" {{ request('stage') == 'level3' ? 'selected' : '' }}>Waiting Level 3</option>
                            <option value="approved" {{ request('stage') == 'approved' ? 'selected' : '' }}>Approved</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                            placeholder="Search by name, email, or ID">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100">Apply Filters</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Pending Approval Requests</span>
            <span class="badge bg-primary">{{ $members->total() }} requests</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Profile Type</th>
                            <th>Submitted On</th>
                            <th>Approval Progress</th>
                            <th>Current Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $member)
                            @php $ms = $member->membership; @endphp
                            <tr>
                                <td>#REQ-{{ $ms?->id }}</td>
                                <td>{{ $member->name }}</td>
                                <td>
                                    @php $pt = $ms?->profile_type ? class_basename($ms->profile_type) : null; @endphp
                                    @if ($pt === 'IndividualProfile')
                                        <span class="profile-badge badge-individual">Individual</span>
                                    @elseif($pt === 'VslaProfile')
                                        <span class="profile-badge badge-vsla">VSLA</span>
                                    @elseif($pt === 'MfiProfile')
                                        <span class="profile-badge badge-mfi">MFI</span>
                                    @else
                                        <span class="badge bg-secondary">Unknown</span>
                                    @endif
                                </td>
                                <td>{{ $member->created_at->format('M d, Y') }}</td>
                                <td>
                                    @php
                                        $progress = 0;
                                        if ($ms?->approved_by_level_1) {
                                            $progress = 33;
                                        }
                                        if ($ms?->approved_by_level_2) {
                                            $progress = 66;
                                        }
                                        if ($ms?->approved_by_level_3) {
                                            $progress = 100;
                                        }
                                    @endphp
                                    <div class="progress approval-progress">
                                        <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
                                    </div>
                                    <small class="text-muted">
                                        @if ($progress == 0)
                                            No approvals yet
                                        @elseif($progress == 33)
                                            Level 1/3 approved
                                        @elseif($progress == 66)
                                            Level 2/3 approved
                                        @else
                                            Fully approved
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    @if ($ms?->approval_status === 'approved')
                                        <span class="status-badge status-approved">Approved</span>
                                    @elseif(!$ms?->approved_by_level_1)
                                        <span class="status-badge status-pending">Waiting Level 1</span>
                                    @elseif(!$ms?->approved_by_level_2)
                                        <span class="status-badge status-pending">Waiting Level 2</span>
                                    @elseif(!$ms?->approved_by_level_3)
                                        <span class="status-badge status-pending">Waiting Level 3</span>
                                    @else
                                        <span class="status-badge status-approved">Approved</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary action-btn me-1 view-request"
                                        data-member-id="{{ $member->id }}">
                                        <i class="fas fa-eye me-1"></i> View
                                    </button>
                                    @if ($ms && $ms->approval_status === 'pending')
                                        @can('approve_level_1', $ms)
                                            <button class="btn btn-sm btn-success action-btn approve-btn" data-level="1"
                                                data-membership-id="{{ $ms->id }}"
                                                {{ $ms->approved_by_level_1 ? 'disabled' : '' }}>
                                                <i class="fas fa-check me-1"></i>
                                                {{ $ms->approved_by_level_1 ? 'Approved' : 'Approve' }}
                                            </button>
                                        @endcan
                                        @can('approve_level_2', $ms)
                                            <button class="btn btn-sm btn-success action-btn approve-btn" data-level="2"
                                                data-membership-id="{{ $ms->id }}"
                                                {{ $ms->approved_by_level_1 ? '' : 'disabled' }}>
                                                <i class="fas fa-{{ $ms->approved_by_level_1 ? 'check' : 'clock' }} me-1"></i>
                                                {{ $ms->approved_by_level_1 ? 'Approve' : 'Waiting for Level 1' }}
                                            </button>
                                        @endcan
                                        @can('approve_level_3', $ms)
                                            <button class="btn btn-sm btn-success action-btn approve-btn" data-level="3"
                                                data-membership-id="{{ $ms->id }}"
                                                {{ $ms->approved_by_level_2 ? '' : 'disabled' }}>
                                                <i class="fas fa-{{ $ms->approved_by_level_2 ? 'check' : 'clock' }} me-1"></i>
                                                {{ $ms->approved_by_level_2 ? 'Approve' : 'Waiting for Level 2' }}
                                            </button>
                                        @endcan
                                    @else
                                        <button class="btn btn-sm btn-secondary action-btn" disabled>
                                            <i class="fas fa-check-double me-1"></i> Completed
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No requests found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div><small class="text-muted">Showing {{ $members->firstItem() }} to {{ $members->lastItem() }} of
                        {{ $members->total() }} results</small></div>
                <div>{{ $members->appends(request()->query())->links() }}</div>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>

    <!-- Detail Modal (AJAX content) -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // View details in modal
            $(document).on('click', '.view-request', function() {
                const memberId = $(this).data('member-id');
                const $modal = $('#detailModal');
                const $content = $modal.find('.modal-content');
                $content.html(
                    '<div class="p-5 text-center"><div class="spinner-border" role="status"></div><div class="mt-2">Loading...</div></div>'
                    );
                $modal.modal('show');
                $.get(`{{ url('admin/members/requests/') }}/${memberId}/modal`)
                    .done(html => $content.html(html))
                    .fail(() => $content.html(
                        '<div class="p-4"><div class="alert alert-danger mb-0">Failed to load details</div></div>'
                        ));
            });

            // Approvals
            $(document).on('click', '.approve-btn', function() {
                const membershipId = $(this).data('membership-id');
                const level = $(this).data('level');
                const $btn = $(this);
                if (confirm(`Approve this membership at level ${level}?`)) {
                    $.ajax({
                        url: `/admin/memberships/${membershipId}/approve-level-${level}`,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        beforeSend: function() {
                            $btn.prop('disabled', true).html(
                                '<i class="bi bi-clock"></i> Processing...');
                        },
                        success: function() {
                            location.reload();
                        },
                        error: function(xhr) {
                            alert('Error: ' + (xhr.responseJSON?.message ||
                                'Something went wrong'));
                            $btn.prop('disabled', false);
                        }
                    });
                }
            });
        });
    </script>
@endpush
