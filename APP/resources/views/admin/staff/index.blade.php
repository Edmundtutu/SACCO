@extends('admin.layouts.app')

@section('title', 'Staff Management')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0"><i class="bi bi-people me-2"></i>Staff Management</h1>
                <p class="text-muted mb-0">Manage admin and staff roles within your SACCO.</p>
            </div>
            @if (auth()->user()->role === 'admin' || auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i> Add New Staff
                </a>
            @endif
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4" id="staffTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="current-tab" data-bs-toggle="tab" data-bs-target="#current"
                    type="button" role="tab">
                    <i class="bi bi-person-badge me-1"></i>Current Staff
                    <span class="badge bg-secondary ms-1">{{ $staff->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="promote-tab" data-bs-toggle="tab" data-bs-target="#promote" type="button"
                    role="tab">
                    <i class="bi bi-arrow-up-circle me-1"></i>Promote a Member
                </button>
            </li>
        </ul>

        <div class="tab-content">

            {{-- ── TAB 1: Current Staff ─────────────────────────────────────── --}}
            <div class="tab-pane fade show active" id="current" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        @if ($staff->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-2"></i>
                                No staff members yet.
                                <a href="{{ route('admin.staff.create') }}">Add the first one.</a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>

                                            <th>Role</th>
                                            <th>Status</th>
                                            @if (auth()->user()->role === 'admin' || auth()->user()->isSuperAdmin())
                                                <th class="text-end">Actions</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($staff as $member)
                                            <tr>
                                                <td class="fw-semibold">
                                                    {{ $member->name }}
                                                </td>
                                                <td class="text-muted small">{{ $member->email }}</td>
                                                <td>
                                                    @php
                                                        $roleColors = [
                                                            'admin' => 'danger',
                                                            'staff_level_1' => 'primary',
                                                            'staff_level_2' => 'info',
                                                            'staff_level_3' => 'secondary',
                                                        ];
                                                        $c = $roleColors[$member->role] ?? 'secondary';
                                                    @endphp
                                                    <span
                                                        class="badge bg-{{ $c }} bg-opacity-15 text-{{ $c }}">
                                                        {{ str_replace('_', ' ', ucfirst($member->role)) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($member->status === 'active')
                                                        <span
                                                            class="badge bg-success bg-opacity-15 text-success">Active</span>
                                                    @elseif($member->status === 'suspended')
                                                        <span
                                                            class="badge bg-warning bg-opacity-15 text-warning">Suspended</span>
                                                    @else
                                                        <span class="badge bg-secondary bg-opacity-15 text-secondary">
                                                            {{ ucfirst($member->status) }}
                                                        </span>
                                                    @endif
                                                </td>
                                                @if (auth()->user()->role === 'admin' || auth()->user()->isSuperAdmin())
                                                    <td class="text-end">
                                                        <a href="{{ route('admin.staff.edit', $member) }}"
                                                            class="btn btn-sm btn-outline-primary me-1">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        @if ($member->id !== auth()->id())
                                                            <form method="POST"
                                                                action="{{ route('admin.staff.demote', $member) }}"
                                                                class="d-inline"
                                                                onsubmit="return confirm('Demote {{ $member->name }} to member?')">
                                                                @csrf @method('PATCH')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                    title="Demote to member">
                                                                    <i class="bi bi-arrow-down-circle"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── TAB 2: Promote a Member ─────────────────────────────────── --}}
            <div class="tab-pane fade" id="promote" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <div class="row g-2 align-items-center">
                            <div class="col">
                                <h6 class="mb-0">Promote a Member to Staff</h6>
                            </div>
                            <div class="col-auto">
                                <input type="text" class="form-control form-control-sm" id="memberSearch"
                                    placeholder="Search members…">
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @php
                            $members = \App\Models\User::where('role', 'member')->orderBy('name')->get();
                        @endphp

                        @if ($members->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                                No regular members found in this SACCO.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="membersTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>

                                            <th>Member Since</th>
                                            @if (auth()->user()->role === 'admin' || auth()->user()->isSuperAdmin())
                                                <th>Promote To</th>
                                                <th></th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($members as $member)
                                            <tr class="member-row">
                                                <td class="fw-semibold member-name">
                                                    {{ $member->name }}
                                                </td>
                                                <td class="text-muted small">{{ $member->email }}</td>
                                                <td class="text-muted small">
                                                    {{ $member->created_at->format('M Y') }}
                                                </td>
                                                @if (auth()->user()->role === 'admin' || auth()->user()->isSuperAdmin())
                                                    <td>
                                                        <form method="POST"
                                                            action="{{ route('admin.staff.promote', $member) }}"
                                                            class="d-flex gap-2 align-items-center">
                                                            @csrf @method('PATCH')
                                                            <select name="role" class="form-select form-select-sm"
                                                                style="width: auto; min-width: 160px;">
                                                                <option value="staff_level_1">Staff Level 1</option>
                                                                <option value="staff_level_2">Staff Level 2</option>
                                                                <option value="staff_level_3">Staff Level 3</option>
                                                                @if (auth()->user()->isSuperAdmin())
                                                                    <option value="admin">Admin</option>
                                                                @endif
                                                            </select>
                                                            <button type="submit" class="btn btn-sm btn-success">
                                                                <i class="bi bi-arrow-up-circle me-1"></i>Promote
                                                            </button>
                                                        </form>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>{{-- /tab-content --}}
    </div>

    @push('scripts')
        <script>
            // Live member search filter
            document.getElementById('memberSearch')?.addEventListener('input', function() {
                const q = this.value.toLowerCase();
                document.querySelectorAll('#membersTable .member-row').forEach(function(row) {
                    const name = row.querySelector('.member-name')?.textContent.toLowerCase() ?? '';
                    row.style.display = name.includes(q) ? '' : 'none';
                });
            });
        </script>
    @endpush

@endsection
