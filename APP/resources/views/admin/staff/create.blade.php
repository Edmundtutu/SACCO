@extends('admin.layouts.app')

@section('title', 'Add Staff Member')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="bi bi-person-plus me-2"></i>Add Staff Member</h1>
            <p class="text-muted mb-0">Create a new admin or staff account for this SACCO.</p>
        </div>
        <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Staff
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-circle me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.staff.store') }}" novalidate>
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">

                {{-- Personal Info --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="bi bi-person me-2"></i>Personal Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Credentials --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="bi bi-key me-2"></i>Login Credentials
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       autocomplete="new-password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation"
                                       class="form-control" autocomplete="new-password" required>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">

                {{-- Role & Access --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="bi bi-shield me-2"></i>Role & Access
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="">— Select role —</option>
                                <option value="staff_level_1" {{ old('role') === 'staff_level_1' ? 'selected' : '' }}>
                                    Staff Level 1
                                </option>
                                <option value="staff_level_2" {{ old('role') === 'staff_level_2' ? 'selected' : '' }}>
                                    Staff Level 2
                                </option>
                                <option value="staff_level_3" {{ old('role') === 'staff_level_3' ? 'selected' : '' }}>
                                    Staff Level 3
                                </option>
                                @if(auth()->user()->isSuperAdmin())
                                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>
                                        Admin
                                    </option>
                                @endif
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check-circle me-1"></i> Create Staff Member
                </button>

            </div>
        </div>
    </form>

</div>
@endsection
