@extends('admin.layouts.app')

@section('title', 'Edit Member')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title">Edit Member</h1>
                <p class="text-muted">Update member information and account status</p>
            </div>
            <div>
                <a href="{{ route('admin.members.show', $member->id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Member
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil"></i> Member Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.members.update', $member->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $member->name) }}" 
                                       required>
                                @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $member->email) }}" 
                                       required>
                                @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone', $member->phone) }}" 
                                       required>
                                @error('phone')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="national_id" class="form-label">National ID <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('national_id') is-invalid @enderror" 
                                       id="national_id" 
                                       name="national_id" 
                                       value="{{ old('national_id', $member->national_id) }}" 
                                       required>
                                @error('national_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                <input type="date" 
                                       class="form-control @error('date_of_birth') is-invalid @enderror" 
                                       id="date_of_birth" 
                                       name="date_of_birth" 
                                       value="{{ old('date_of_birth', $member->date_of_birth) }}">
                                @error('date_of_birth')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select @error('gender') is-invalid @enderror" 
                                        id="gender" 
                                        name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender', $member->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $member->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender', $member->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('gender')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" 
                                  name="address" 
                                  rows="3" 
                                  required>{{ old('address', $member->address) }}</textarea>
                        @error('address')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="occupation" class="form-label">Occupation <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('occupation') is-invalid @enderror" 
                                       id="occupation" 
                                       name="occupation" 
                                       value="{{ old('occupation', $member->occupation) }}" 
                                       required>
                                @error('occupation')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="monthly_income" class="form-label">Monthly Income (KSh) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('monthly_income') is-invalid @enderror" 
                                       id="monthly_income" 
                                       name="monthly_income" 
                                       value="{{ old('monthly_income', $member->monthly_income) }}" 
                                       min="0" 
                                       step="0.01" 
                                       required>
                                @error('monthly_income')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Account Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" 
                                name="status" 
                                required>
                            <option value="pending" {{ old('status', $member->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="active" {{ old('status', $member->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ old('status', $member->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="inactive" {{ old('status', $member->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                        <div class="form-text">
                            <strong>Warning:</strong> Changing status to "Active" will automatically approve the member if pending.
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.members.show', $member->id) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Current Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Current Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td><strong>Member Number:</strong></td>
                        <td>{{ $member->member_number ?? 'Not assigned' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Registration Date:</strong></td>
                        <td>{{ $member->created_at->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Current Status:</strong></td>
                        <td>
                            <span class="badge bg-{{ $member->status == 'active' ? 'success' : ($member->status == 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst($member->status) }}
                            </span>
                        </td>
                    </tr>
                    @if($member->approved_at)
                    <tr>
                        <td><strong>Approved Date:</strong></td>
                        <td>{{ $member->approved_at->format('M d, Y') }}</td>
                    </tr>
                    @endif
                </table>
                
                <hr>
                
                <h6>Account Summary</h6>
                <table class="table table-borderless table-sm">
                    <tr>
                        <td><strong>Savings Accounts:</strong></td>
                        <td>{{ $member->accounts->count() }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Balance:</strong></td>
                        <td>KSh {{ number_format($member->accounts->sum('balance'), 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Active Loans:</strong></td>
                        <td>{{ $member->loans->where('status', 'active')->count() }}</td>
                    </tr>
                    <tr>
                        <td><strong>Shares:</strong></td>
                        <td>{{ $member->shares->sum('shares_count') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection