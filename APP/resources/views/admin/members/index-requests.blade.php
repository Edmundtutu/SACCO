<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Approval System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color:#3498db; --secondary-color:#2c3e50; --success-color:#2ecc71; --warning-color:#f39c12; --danger-color:#e74c3c; --light-bg:#f8f9fa; }
        body { background-color:#f5f7f9; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-brand{ font-weight:700; color:var(--secondary-color)!important; }
        .card{ border-radius:10px; box-shadow:0 4px 6px rgba(0,0,0,.1); margin-bottom:20px; border:none; }
        .card-header{ background-color:white; border-bottom:2px solid #eaeaea; font-weight:600; padding:15px 20px; border-radius:10px 10px 0 0 !important; }
        .profile-badge{ padding:5px 10px; border-radius:20px; font-size:12px; font-weight:600; }
        .badge-individual{ background-color:#e1f0ff; color:#0d6efd; }
        .badge-vsla{ background-color:#e0f7fa; color:#0097a7; }
        .badge-mfi{ background-color:#fce4ec; color:#d81b60; }
        .approval-progress{ height:8px; border-radius:4px; }
        .approval-item{ border-left:3px solid #eaeaea; padding:10px 15px; margin-bottom:10px; background:white; border-radius:5px; }
        .approved-item{ border-left-color:var(--success-color); background-color:#f0fff4; }
        .action-btn{ padding:5px 15px; border-radius:5px; font-weight:500; font-size:14px; }
        .profile-detail-card{ border:1px solid #e0e0e0; border-radius:8px; padding:15px; margin-bottom:15px; background:white; }
        .detail-item{ display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f0f0f0; }
        .detail-item:last-child{ border-bottom:none; }
        .status-badge{ padding:5px 10px; border-radius:15px; font-size:12px; font-weight:600; }
        .status-pending{ background-color:#fff3cd; color:#856404; }
        .status-approved{ background-color:#d4edda; color:#155724; }
        .check-item{ display:flex; align-items:center; margin-bottom:10px; }
        .check-item input{ margin-right:10px; }
        .nav-tabs .nav-link.active{ font-weight:600; border-bottom:3px solid var(--primary-color); }
        .role-badge{ background-color:var(--secondary-color); color:white; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Membership Approval System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-user-cog me-1"></i> Admin Panel</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i> {{ auth()->user()->name ?? 'Admin' }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Profile</a></li>
                            <li><a class="dropdown-item" href="#">Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Membership Approval Requests</h2>
                    <span class="role-badge badge">{{ Str::headline(str_replace('_',' ', auth()->user()->role ?? 'admin')) }}</span>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.members.requests') }}">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Profile Type</label>
                                    <select class="form-select" name="profile_type">
                                        <option value="">All Types</option>
                                        <option value="individual" {{ request('profile_type')=='individual'?'selected':'' }}>Individual</option>
                                        <option value="vsla" {{ request('profile_type')=='vsla'?'selected':'' }}>VSLA</option>
                                        <option value="mfi" {{ request('profile_type')=='mfi'?'selected':'' }}>MFI</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="stage">
                                        <option value="">All Statuses</option>
                                        <option value="level1" {{ request('stage')=='level1'?'selected':'' }}>Waiting Level 1</option>
                                        <option value="level2" {{ request('stage')=='level2'?'selected':'' }}>Waiting Level 2</option>
                                        <option value="level3" {{ request('stage')=='level3'?'selected':'' }}>Waiting Level 3</option>
                                        <option value="approved" {{ request('stage')=='approved'?'selected':'' }}>Approved</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Search</label>
                                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or ID">
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
                                                @if($pt==='IndividualProfile')
                                                    <span class="profile-badge badge-individual">Individual</span>
                                                @elseif($pt==='VslaProfile')
                                                    <span class="profile-badge badge-vsla">VSLA</span>
                                                @elseif($pt==='MfiProfile')
                                                    <span class="profile-badge badge-mfi">MFI</span>
                                                @else
                                                    <span class="badge bg-secondary">Unknown</span>
                                                @endif
                                            </td>
                                            <td>{{ $member->created_at->format('M d, Y') }}</td>
                                            <td>
                                                @php $progress=0; if($ms?->approved_by_level_1) $progress=33; if($ms?->approved_by_level_2) $progress=66; if($ms?->approved_by_level_3) $progress=100; @endphp
                                                <div class="progress approval-progress"><div class="progress-bar bg-success" style="width: {{ $progress }}%"></div></div>
                                                <small class="text-muted">
                                                    @if($progress==0) No approvals yet @elseif($progress==33) Level 1/3 approved @elseif($progress==66) Level 2/3 approved @else Fully approved @endif
                                                </small>
                                            </td>
                                            <td>
                                                @if($ms?->approval_status==='approved')
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
                                                <button class="btn btn-sm btn-outline-primary action-btn me-1 view-request" data-member-id="{{ $member->id }}">
                                                    <i class="fas fa-eye me-1"></i> View
                                                </button>
                                                @if($ms && $ms->approval_status==='pending')
                                                    @can('approve_level_1', $ms)
                                                        <button class="btn btn-sm btn-success action-btn approve-btn" data-level="1" data-membership-id="{{ $ms->id }}" {{ $ms->approved_by_level_1 ? 'disabled' : '' }}>
                                                            <i class="fas fa-check me-1"></i> {{ $ms->approved_by_level_1 ? 'Approved' : 'Approve' }}
                                                        </button>
                                                    @endcan
                                                    @can('approve_level_2', $ms)
                                                        <button class="btn btn-sm btn-success action-btn approve-btn" data-level="2" data-membership-id="{{ $ms->id }}" {{ $ms->approved_by_level_1 ? '' : 'disabled' }}>
                                                            <i class="fas fa-{{ $ms->approved_by_level_1 ? 'check' : 'clock' }} me-1"></i> {{ $ms->approved_by_level_1 ? 'Approve' : 'Waiting for Level 1' }}
                                                        </button>
                                                    @endcan
                                                    @can('approve_level_3', $ms)
                                                        <button class="btn btn-sm btn-success action-btn approve-btn" data-level="3" data-membership-id="{{ $ms->id }}" {{ $ms->approved_by_level_2 ? '' : 'disabled' }}>
                                                            <i class="fas fa-{{ $ms->approved_by_level_2 ? 'check' : 'clock' }} me-1"></i> {{ $ms->approved_by_level_2 ? 'Approve' : 'Waiting for Level 2' }}
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
                                        <tr><td colspan="7" class="text-center text-muted py-4">No requests found</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div><small class="text-muted">Showing {{ $members->firstItem() }} to {{ $members->lastItem() }} of {{ $members->total() }} results</small></div>
                            <div>{{ $members->appends(request()->query())->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const role = '{{ auth()->user()->role ?? 'admin' }}';
            if (role === 'admin') {
                document.querySelectorAll('.btn-success').forEach(btn => {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-eye me-1"></i> View Only';
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-secondary');
                });
            }

            document.addEventListener('click', function(e) {
                const viewBtn = e.target.closest('.view-request');
                if (viewBtn) {
                    const memberId = viewBtn.getAttribute('data-member-id');
                    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
                    const modalContent = document.querySelector('#detailModal .modal-content');
                    modalContent.innerHTML = '<div class="p-5 text-center"><div class="spinner-border" role="status"></div><div class="mt-2">Loading...</div></div>';
                    modal.show();
                    fetch(`{{ route('admin.members.requests.modal', '') }}/${memberId}`)
                        .then(r=>r.text())
                        .then(html=>{ modalContent.innerHTML = html; })
                        .catch(()=>{ modalContent.innerHTML = '<div class="p-4"><div class="alert alert-danger mb-0">Failed to load details</div></div>'; });
                }

                const approveBtn = e.target.closest('.approve-btn');
                if (approveBtn) {
                    const membershipId = approveBtn.getAttribute('data-membership-id');
                    const level = approveBtn.getAttribute('data-level');
                    if (confirm(`Approve this membership at level ${level}?`)) {
                        approveBtn.disabled = true; approveBtn.innerHTML = '<i class="fas fa-clock me-1"></i> Processing...';
                        fetch(`/admin/memberships/${membershipId}/approve-level-${level}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
                            .then(r=>{ if(!r.ok) throw new Error(); return r.json(); })
                            .then(()=>{ location.reload(); })
                            .catch(()=>{ approveBtn.disabled=false; });
                    }
                }
            });

            const checkboxes = document.querySelectorAll('.form-check-input');
            function updateApprovalProgress() {
                const total = checkboxes.length;
                const checked = document.querySelectorAll('.form-check-input:checked').length;
                const progress = (checked / total) * 100;
                const bar = document.querySelector('#approval .progress-bar');
                if (bar) bar.style.width = `${progress}%`;
            }
            checkboxes.forEach(cb => cb.addEventListener('change', updateApprovalProgress));
        });
    </script>
</body>
</html>
