@extends('admin.layouts.app')

@section('title', 'Create ' . ucfirst($memberType) . ' Member')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">Create {{ ucfirst($memberType) }} Member</h1>
                <p class="text-muted">Add a new {{ $memberType }} member to the SACCO</p>
            </div>
            <div>
                <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('admin.members.store') }}" method="POST">
    @csrf
    <input type="hidden" name="member_type" value="{{ $memberType }}">
    
    <div class="row">
        <div class="col-lg-8">
            <!-- User Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-circle"></i> User Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        @switch($memberType)
                            @case('individual')
                                <i class="bi bi-person"></i> Individual Profile Information
                                @break
                            @case('vsla')
                                <i class="bi bi-people"></i> VSLA Group Information
                                @break
                            @case('mfi')
                                <i class="bi bi-building"></i> MFI Institution Information
                                @break
                        @endswitch
                    </h5>
                </div>
                <div class="card-body">
                    @if($memberType === 'individual')
                        @include('admin.members.partials.individual-form')
                    @elseif($memberType === 'vsla')
                        @include('admin.members.partials.vsla-form')
                    @elseif($memberType === 'mfi')
                        @include('admin.members.partials.mfi-form')
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Create Member
                        </button>
                        <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                    
                    <hr>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Note:</strong> The member will be created with "Pending Approval" status and will require approval from authorized staff members.
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection