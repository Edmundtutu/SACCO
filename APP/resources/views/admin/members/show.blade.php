@extends('admin.layouts.app')

@section('title', 'Member Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">{{ $member->name }}</h1>
                <p class="text-muted">
                    Member #{{ $member->membership->id ?? 'N/A' }} • 
                    @if($member->membership && $member->membership->profile)
                        {{ class_basename($member->membership->profile_type) }} • 
                    @endif
                    Joined {{ $member->created_at->format('M d, Y') }}
                </p>
            </div>
            <div>
                <a href="{{ route('admin.members.edit', $member->id) }}" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Edit Member
                </a>
                <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Member Status and Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5>
                            User Status: 
                            @switch($member->status)
                                @case('active')
                                    <span class="badge bg-success">Active</span>
                                    @break
                                @case('pending_approval')
                                    <span class="badge bg-warning">Pending Approval</span>
                                    @break
                                @case('suspended')
                                    <span class="badge bg-danger">Suspended</span>
                                    @break
                                @case('inactive')
                                    <span class="badge bg-secondary">Inactive</span>
                                    @break
                            @endswitch
                        </h5>
                        @if($member->membership)
                            <h6 class="mt-2">
                                Membership Approval: 
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
                                @endswitch
                            </h6>
                        @endif
                        @if($member->account_verified_at)
                        <small class="text-muted">Account verified on {{ $member->account_verified_at->format('M d, Y') }}</small>
                        @endif
                        
                        @if($member->membership)
                            <div class="mt-3">
                                <h6>Approval Progress:</h6>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge {{ $member->membership->approved_by_level_1 ? 'bg-success' : 'bg-light text-dark' }}">
                                        <i class="bi {{ $member->membership->approved_by_level_1 ? 'bi-check-circle-fill' : 'bi-circle' }}"></i> L1
                                    </span>
                                    <span class="badge {{ $member->membership->approved_by_level_2 ? 'bg-success' : 'bg-light text-dark' }}">
                                        <i class="bi {{ $member->membership->approved_by_level_2 ? 'bi-check-circle-fill' : 'bi-circle' }}"></i> L2
                                    </span>
                                    <span class="badge {{ $member->membership->approved_by_level_3 ? 'bg-success' : 'bg-light text-dark' }}">
                                        <i class="bi {{ $member->membership->approved_by_level_3 ? 'bi-check-circle-fill' : 'bi-circle' }}"></i> L3
                                    </span>
                                </div>
                                @if($member->membership->approval_status == 'pending')
                                <div class="progress" style="height: 20px;">
                                    @php
                                        $progress = 0;
                                        if ($member->membership->approved_by_level_1) $progress = 33;
                                        if ($member->membership->approved_by_level_2) $progress = 66;
                                        if ($member->membership->approved_by_level_3) $progress = 100;
                                    @endphp
                                    <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                        Level {{ $progress == 0 ? '1' : ($progress == 33 ? '2' : ($progress == 66 ? '3' : 'Complete')) }}
                                    </div>
                                </div>
                                @endif
                                <small class="text-muted">
                                    @if($member->membership->approved_by_level_1)
                                        ✓ Level 1 approved {{ $member->membership->approved_at_level_1->format('M d, Y') }}
                                    @endif
                                    @if($member->membership->approved_by_level_2)
                                        <br>✓ Level 2 approved {{ $member->membership->approved_at_level_2->format('M d, Y') }}
                                    @endif
                                    @if($member->membership->approved_by_level_3)
                                        <br>✓ Level 3 approved {{ $member->membership->approved_at_level_3->format('M d, Y') }}
                                    @endif
                                </small>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6 text-end">
                        @php $membership = $member->membership; @endphp
                        @if($membership && $membership->approval_status == 'pending')
                            @can('approve_level_1', $membership)
                                @if(!$membership->approved_by_level_1)
                                    <button type="button" class="btn btn-success approve-btn" 
                                            data-membership-id="{{ $membership->id }}" data-level="1" title="Approve Level 1">
                                        <i class="bi bi-check-circle"></i> Approve Level 1
                                    </button>
                                @endif
                            @endcan

                            @can('approve_level_2', $membership)
                                @if(!$membership->approved_by_level_2)
                                    @if($membership->approved_by_level_1)
                                        <button type="button" class="btn btn-success approve-btn" 
                                                data-membership-id="{{ $membership->id }}" data-level="2" title="Approve Level 2">
                                            <i class="bi bi-check-circle"></i> Approve Level 2
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-outline-secondary" disabled title="Waiting for Level 1 approval">
                                            <i class="bi bi-hourglass"></i> Approve Level 2
                                        </button>
                                    @endif
                                @endif
                            @endcan

                            @can('approve_level_3', $membership)
                                @if(!$membership->approved_by_level_3)
                                    @if($membership->approved_by_level_2)
                                        <button type="button" class="btn btn-success approve-btn" 
                                                data-membership-id="{{ $membership->id }}" data-level="3" title="Final Approval & Activate">
                                            <i class="bi bi-check-circle"></i> Final Approval & Activate
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-outline-secondary" disabled title="Waiting for Level 2 approval">
                                            <i class="bi bi-hourglass"></i> Final Approval
                                        </button>
                                    @endif
                                @endif
                            @endcan
                        @endif
                        
                        @if($member->status == 'active')
                        <form action="{{ route('admin.members.suspend', $member->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning" onclick="return confirm('Suspend this member?')">
                                <i class="bi bi-pause-circle"></i> Suspend
                            </button>
                        </form>
                        @endif
                        
                        @if($member->status == 'suspended')
                        <form action="{{ route('admin.members.activate', $member->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success" onclick="return confirm('Activate this member?')">
                                <i class="bi bi-play-circle"></i> Activate
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Member Information -->
<div class="row">
    <div class="col-lg-8">
        <!-- Profile Information -->
        @if($member->membership && $member->membership->profile)
            @php $profile = $member->membership->profile; @endphp
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        @if($profile instanceof App\Models\Membership\IndividualProfile)
                            <i class="bi bi-person"></i> Individual Profile Information
                        @elseif($profile instanceof App\Models\Membership\VslaProfile)
                            <i class="bi bi-people"></i> VSLA Group Information
                        @elseif($profile instanceof App\Models\Membership\MfiProfile)
                            <i class="bi bi-building"></i> MFI Institution Information
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Full Name:</strong></td>
                                    <td>{{ $member->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $member->email }}</td>
                                </tr>
                                
                                @if($profile instanceof App\Models\Membership\IndividualProfile)
                                    <tr>
                                        <td><strong>Phone:</strong></td>
                                        <td>{{ $profile->phone ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>National ID:</strong></td>
                                        <td>{{ $profile->national_id ?? 'N/A' }}</td>
                                    </tr>
                                @elseif($profile instanceof App\Models\Membership\VslaProfile)
                                    <tr>
                                        <td><strong>Village:</strong></td>
                                        <td>{{ $profile->village ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Sub County:</strong></td>
                                        <td>{{ $profile->sub_county ?? 'N/A' }}</td>
                                    </tr>
                                @elseif($profile instanceof App\Models\Membership\MfiProfile)
                                    <tr>
                                        <td><strong>Contact Person:</strong></td>
                                        <td>{{ $profile->contact_person ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Contact Number:</strong></td>
                                        <td>{{ $profile->contact_number ?? 'N/A' }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                @if($profile instanceof App\Models\Membership\IndividualProfile)
                                    <tr>
                                        <td><strong>Date of Birth:</strong></td>
                                        <td>{{ $profile->date_of_birth ? Carbon\Carbon::parse($profile->date_of_birth)->format('M d, Y') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Gender:</strong></td>
                                        <td>{{ $profile->gender ? ucfirst($profile->gender) : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Occupation:</strong></td>
                                        <td>{{ $profile->occupation ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Monthly Income:</strong></td>
                                        <td>{{ $profile->monthly_income ? 'UGX ' . number_format($profile->monthly_income, 2) : 'N/A' }}</td>
                                    </tr>
                                @elseif($profile instanceof App\Models\Membership\VslaProfile)
                                    <tr>
                                        <td><strong>District:</strong></td>
                                        <td>{{ $profile->district ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Membership Count:</strong></td>
                                        <td>{{ $profile->membership_count ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Registration Certificate:</strong></td>
                                        <td>{{ $profile->registration_certificate ?? 'N/A' }}</td>
                                    </tr>
                                @elseif($profile instanceof App\Models\Membership\MfiProfile)
                                    <tr>
                                        <td><strong>Membership Count:</strong></td>
                                        <td>{{ $profile->membership_count ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Registration Certificate:</strong></td>
                                        <td>{{ $profile->registration_certificate ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Operating License:</strong></td>
                                        <td>{{ $profile->operating_license ?? 'N/A' }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    
                    @if($profile->address ?? false)
                    <div class="row">
                        <div class="col-12">
                            <strong>Address:</strong><br>
                            {{ $profile->address }}
                        </div>
                    </div>
                    @endif
                    
                    @if($profile instanceof App\Models\Membership\IndividualProfile && ($profile->next_of_kin_name || $profile->emergency_contact_name))
                        <hr>
                        <h6>Additional Information</h6>
                        <div class="row">
                            @if($profile->next_of_kin_name)
                            <div class="col-md-6">
                                <strong>Next of Kin:</strong><br>
                                {{ $profile->next_of_kin_name }} ({{ $profile->next_of_kin_relationship }})<br>
                                <small class="text-muted">{{ $profile->next_of_kin_phone }}</small>
                            </div>
                            @endif
                            @if($profile->emergency_contact_name)
                            <div class="col-md-6">
                                <strong>Emergency Contact:</strong><br>
                                {{ $profile->emergency_contact_name }}<br>
                                <small class="text-muted">{{ $profile->emergency_contact_phone }}</small>
                            </div>
                            @endif
                        </div>
                    @endif
                    
                    @if($profile instanceof App\Models\Membership\VslaProfile && $profile->executive_contacts)
                        <hr>
                        <h6>Executive Contacts</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Position</th>
                                        <th>Phone</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($profile->executive_contacts as $contact)
                                    <tr>
                                        <td>{{ $contact['name'] ?? 'N/A' }}</td>
                                        <td>{{ $contact['position'] ?? 'N/A' }}</td>
                                        <td>{{ $contact['phone'] ?? 'N/A' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    
                    @if($profile instanceof App\Models\Membership\MfiProfile && $profile->board_members)
                        <hr>
                        <h6>Board Members</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Position</th>
                                        <th>Phone</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($profile->board_members as $member)
                                    <tr>
                                        <td>{{ $member['name'] ?? 'N/A' }}</td>
                                        <td>{{ $member['position'] ?? 'N/A' }}</td>
                                        <td>{{ $member['phone'] ?? 'N/A' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <!-- Basic User Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person"></i> User Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Full Name:</strong></td>
                                    <td>{{ $member->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $member->email }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        This member has no associated profile. Please contact system administrator.
                    </div>
                </div>
            </div>
        @endif

        <!-- Accounts -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-piggy-bank"></i>Accounts</h5>
            </div>
            <div class="card-body">
                @if($member->accounts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Account Number</th>
                                <th>Type</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Opened</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($member->accounts as $account)
                            <tr>
                                <td>{{ $account->account_number }}</td>
                                <td>{{ ucfirst($account->account_type) }}</td>
                                <td>UGX {{ number_format($account->balance, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $account->status == 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($account->status) }}
                                    </span>
                                </td>
                                <td>{{ $account->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted">No accounts found.</p>
                @endif
            </div>
        </div>

        <!-- Loans -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-currency-dollar"></i> Loans</h5>
            </div>
            <div class="card-body">
                @if($member->loans->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Loan Number</th>
                                <th>Principal</th>
                                <th>Outstanding</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($member->loans as $loan)
                            <tr>
                                <td>{{ $loan->loan_number }}</td>
                                <td>UGX {{ number_format($loan->principal_amount, 2) }}</td>
                                <td>UGX {{ number_format($loan->outstanding_balance ?? $loan->principal_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $loan->status == 'active' ? 'success' : ($loan->status == 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                </td>
                                <td>{{ $loan->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted">No loans found.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Account Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Account Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-12 mb-3">
                        <h4 class="text-primary">UGX {{ number_format($member->accounts->sum('balance'), 2) }}</h4>
                        <small class="text-muted">Total Savings</small>
                    </div>
                    <div class="col-6">
                        <h5>{{ $member->accounts->count() }}</h5>
                        <small class="text-muted">Accounts</small>
                    </div>
                    <div class="col-6">
                        <h5>{{ $member->loans->count() }}</h5>
                        <small class="text-muted">Loans</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shares -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Shares</h5>
            </div>
            <div class="card-body">
                @if($member->shares->count() > 0)
                <div class="row text-center">
                    <div class="col-12 mb-2">
                        <h4 class="text-success">{{ $member->shares->sum('shares_count') }}</h4>
                        <small class="text-muted">Total Shares</small>
                    </div>
                    <div class="col-12">
                        <h5>UGX {{ number_format($member->shares->sum('amount'), 2) }}</h5>
                        <small class="text-muted">Share Value</small>
                    </div>
                </div>
                @else
                <p class="text-muted text-center">No shares purchased.</p>
                @endif
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Activity</h5>
            </div>
            <div class="card-body">
                @php
                    $recentTransactions = $member->accounts->flatMap(function($account) {
                        return $account->transactions->take(5);
                    })->sortByDesc('created_at')->take(5);
                @endphp
                
                @if($recentTransactions->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($recentTransactions as $transaction)
                    <div class="list-group-item px-0 py-2">
                        <div class="d-flex justify-content-between">
                            <div>
                                <small class="fw-bold">{{ ucfirst($transaction->type) }}</small><br>
                                <small class="text-muted">{{ $transaction->created_at->format('M d, Y') }}</small>
                            </div>
                            <div class="text-end">
                                <small class="fw-bold text-{{ $transaction->type == 'deposit' ? 'success' : 'warning' }}">
                                    {{ $transaction->type == 'deposit'|| $transaction->type == 'wallet_topup' ? '+' : '-' }}UGX {{ number_format($transaction->amount, 2) }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted text-center">No recent activity.</p>
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
                    button.prop('disabled', false).html(`<i class="bi bi-check-circle"></i> Approve Level ${level}`);
                }
            });
        }
    });
});
</script>
@endpush
@endsection