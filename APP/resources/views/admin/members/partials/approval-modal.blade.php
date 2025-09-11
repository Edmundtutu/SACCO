<div class="modal-header">
    <h5 class="modal-title">
        Membership Request • #{{ $member->membership->id ?? 'N/A' }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
<div class="modal-body">
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <div>
            <strong>{{ $member->name }}</strong><br>
            <small class="text-muted">{{ $member->email }}</small>
        </div>
        <div>
            @php $profileType = $member->membership?->profile_type ? class_basename($member->membership->profile_type) : null; @endphp
            @switch($profileType)
                @case('IndividualProfile')
                    <span class="badge bg-primary">Individual</span>
                    @break
                @case('VslaProfile')
                    <span class="badge bg-info">VSLA</span>
                    @break
                @case('MfiProfile')
                    <span class="badge bg-warning text-dark">MFI</span>
                    @break
                @default
                    <span class="badge bg-secondary">Unknown</span>
            @endswitch
        </div>
    </div>

    @if($member->membership)
        @php $ms = $member->membership; @endphp
        <div class="mb-3">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge {{ $ms->approved_by_level_1 ? 'bg-success' : 'bg-light text-dark' }}">
                    <i class="bi {{ $ms->approved_by_level_1 ? 'bi-check-circle-fill' : 'bi-circle' }}"></i> Level 1
                </span>
                <span class="badge {{ $ms->approved_by_level_2 ? 'bg-success' : 'bg-light text-dark' }}">
                    <i class="bi {{ $ms->approved_by_level_2 ? 'bi-check-circle-fill' : 'bi-circle' }}"></i> Level 2
                </span>
                <span class="badge {{ $ms->approved_by_level_3 ? 'bg-success' : 'bg-light text-dark' }}">
                    <i class="bi {{ $ms->approved_by_level_3 ? 'bi-check-circle-fill' : 'bi-circle' }}"></i> Level 3
                </span>
            </div>
            @php
                $progress = 0;
                if ($ms->approved_by_level_1) $progress = 33;
                if ($ms->approved_by_level_2) $progress = 66;
                if ($ms->approved_by_level_3) $progress = 100;
            @endphp
            <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <small class="text-muted">{{ $progress == 0 ? 'Waiting for Level 1' : ($progress == 33 ? 'Waiting for Level 2' : ($progress == 66 ? 'Waiting for Level 3' : 'Fully approved')) }}</small>
        </div>
    @endif

    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profileTab" type="button" role="tab">Profile</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#kycTab" type="button" role="tab">KYC Documents</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#approvalTab" type="button" role="tab">Approval</button>
        </li>
    </ul>

    <div class="tab-content p-3">
        <div class="tab-pane fade show active" id="profileTab" role="tabpanel">
            @if($member->membership && $member->membership->profile)
                @php $profile = $member->membership->profile; @endphp
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded p-2">
                            <div class="small text-muted">Name</div>
                            <div>{{ $member->name }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-2">
                            <div class="small text-muted">Email</div>
                            <div>{{ $member->email }}</div>
                        </div>
                    </div>

                    @if($profile instanceof App\Models\Membership\IndividualProfile)
                        <div class="col-md-6">
                            <div class="border rounded p-2">
                                <div class="small text-muted">Phone</div>
                                <div>{{ $profile->phone ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-2">
                                <div class="small text-muted">National ID</div>
                                <div>{{ $profile->national_id ?? 'N/A' }}</div>
                            </div>
                        </div>
                    @elseif($profile instanceof App\Models\Membership\VslaProfile)
                        <div class="col-md-6">
                            <div class="border rounded p-2">
                                <div class="small text-muted">Village</div>
                                <div>{{ $profile->village ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-2">
                                <div class="small text-muted">District</div>
                                <div>{{ $profile->district ?? 'N/A' }}</div>
                            </div>
                        </div>
                    @elseif($profile instanceof App\Models\Membership\MfiProfile)
                        <div class="col-md-6">
                            <div class="border rounded p-2">
                                <div class="small text-muted">Contact Person</div>
                                <div>{{ $profile->contact_person ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-2">
                                <div class="small text-muted">Contact Number</div>
                                <div>{{ $profile->contact_number ?? 'N/A' }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="alert alert-warning">No profile data available.</div>
            @endif
        </div>

        <div class="tab-pane fade" id="kycTab" role="tabpanel">
            <div class="alert alert-info mb-0">KYC documents preview is not yet uploaded in this demo.</div>
        </div>

        <div class="tab-pane fade" id="approvalTab" role="tabpanel">
            @if($member->membership)
                @php $membership = $member->membership; @endphp
                <div class="vstack gap-2">
                    <div class="border-start ps-3 {{ $membership->approved_by_level_1 ? 'border-success' : 'border-2' }}">
                        <div class="d-flex justify-content-between">
                            <strong>Level 1</strong>
                            <span class="badge {{ $membership->approved_by_level_1 ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $membership->approved_by_level_1 ? 'Approved' : 'Pending' }}
                            </span>
                        </div>
                        <small class="text-muted">
                            @if($membership->approved_at_level_1)
                                {{ $membership->approved_at_level_1->format('M d, Y H:i') }}
                            @else
                                Waiting for Staff Level 1
                            @endif
                        </small>
                    </div>
                    <div class="border-start ps-3 {{ $membership->approved_by_level_2 ? 'border-success' : 'border-2' }}">
                        <div class="d-flex justify-content-between">
                            <strong>Level 2</strong>
                            <span class="badge {{ $membership->approved_by_level_2 ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $membership->approved_by_level_2 ? 'Approved' : 'Pending' }}
                            </span>
                        </div>
                        <small class="text-muted">
                            @if($membership->approved_at_level_2)
                                {{ $membership->approved_at_level_2->format('M d, Y H:i') }}
                            @else
                                {{ $membership->approved_by_level_1 ? 'Waiting for Staff Level 2' : 'Waiting for Level 1 first' }}
                            @endif
                        </small>
                    </div>
                    <div class="border-start ps-3 {{ $membership->approved_by_level_3 ? 'border-success' : 'border-2' }}">
                        <div class="d-flex justify-content-between">
                            <strong>Level 3</strong>
                            <span class="badge {{ $membership->approved_by_level_3 ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $membership->approved_by_level_3 ? 'Approved' : 'Pending' }}
                            </span>
                        </div>
                        <small class="text-muted">
                            @if($membership->approved_at_level_3)
                                {{ $membership->approved_at_level_3->format('M d, Y H:i') }}
                            @else
                                {{ $membership->approved_by_level_2 ? 'Waiting for Staff Level 3' : 'Waiting for Level 2 first' }}
                            @endif
                        </small>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-3">
                    @can('approve_level_1', $membership)
                        @if(!$membership->approved_by_level_1)
                            <button class="btn btn-success approve-btn" data-level="1" data-membership-id="{{ $membership->id }}">
                                <i class="bi bi-check-circle"></i> Approve Level 1
                            </button>
                        @endif
                    @endcan

                    @can('approve_level_2', $membership)
                        @if(!$membership->approved_by_level_2)
                            <button class="btn btn-success approve-btn" data-level="2" data-membership-id="{{ $membership->id }}" {{ $membership->approved_by_level_1 ? '' : 'disabled' }}>
                                <i class="bi bi-check-circle"></i> Approve Level 2
                            </button>
                        @endif
                    @endcan

                    @can('approve_level_3', $membership)
                        @if(!$membership->approved_by_level_3)
                            <button class="btn btn-success approve-btn" data-level="3" data-membership-id="{{ $membership->id }}" {{ $membership->approved_by_level_2 ? '' : 'disabled' }}>
                                <i class="bi bi-check-circle"></i> Final Approval & Activate
                            </button>
                        @endif
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>
