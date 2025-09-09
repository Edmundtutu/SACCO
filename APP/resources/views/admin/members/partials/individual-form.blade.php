<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                   id="phone" name="phone" value="{{ old('phone') }}" required>
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="national_id" class="form-label">National ID <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('national_id') is-invalid @enderror" 
                   id="national_id" name="national_id" value="{{ old('national_id') }}" required>
            @error('national_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                   id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
            @error('date_of_birth')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                <option value="">Select Gender</option>
                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
            </select>
            @error('gender')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
    <textarea class="form-control @error('address') is-invalid @enderror" 
              id="address" name="address" rows="3" required>{{ old('address') }}</textarea>
    @error('address')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="occupation" class="form-label">Occupation</label>
            <input type="text" class="form-control @error('occupation') is-invalid @enderror" 
                   id="occupation" name="occupation" value="{{ old('occupation') }}">
            @error('occupation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="monthly_income" class="form-label">Monthly Income (UGX)</label>
            <input type="number" class="form-control @error('monthly_income') is-invalid @enderror" 
                   id="monthly_income" name="monthly_income" value="{{ old('monthly_income') }}" min="0" step="0.01">
            @error('monthly_income')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

@if($potentialReferees->count() > 0)
<div class="mb-3">
    <label for="referee" class="form-label">Referee (Optional)</label>
    <select class="form-select @error('referee') is-invalid @enderror" id="referee" name="referee">
        <option value="">Select a referee</option>
        @foreach($potentialReferees as $referee)
            <option value="{{ $referee->id }}" {{ old('referee') == $referee->id ? 'selected' : '' }}>
                {{ $referee->name }} ({{ $referee->email }})
            </option>
        @endforeach
    </select>
    @error('referee')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
@endif

<h6 class="mt-4 mb-3">Next of Kin Information</h6>
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="next_of_kin_name" class="form-label">Next of Kin Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('next_of_kin_name') is-invalid @enderror" 
                   id="next_of_kin_name" name="next_of_kin_name" value="{{ old('next_of_kin_name') }}" required>
            @error('next_of_kin_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="next_of_kin_relationship" class="form-label">Relationship <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('next_of_kin_relationship') is-invalid @enderror" 
                   id="next_of_kin_relationship" name="next_of_kin_relationship" value="{{ old('next_of_kin_relationship') }}" required>
            @error('next_of_kin_relationship')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="next_of_kin_phone" class="form-label">Next of Kin Phone <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('next_of_kin_phone') is-invalid @enderror" 
                   id="next_of_kin_phone" name="next_of_kin_phone" value="{{ old('next_of_kin_phone') }}" required>
            @error('next_of_kin_phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="next_of_kin_address" class="form-label">Next of Kin Address <span class="text-danger">*</span></label>
            <textarea class="form-control @error('next_of_kin_address') is-invalid @enderror" 
                      id="next_of_kin_address" name="next_of_kin_address" rows="2" required>{{ old('next_of_kin_address') }}</textarea>
            @error('next_of_kin_address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<h6 class="mt-4 mb-3">Emergency Contact (Optional)</h6>
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
            <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                   id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}">
            @error('emergency_contact_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone</label>
            <input type="text" class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                   id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}">
            @error('emergency_contact_phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<h6 class="mt-4 mb-3">Employment & Banking (Optional)</h6>
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="employer_name" class="form-label">Employer Name</label>
            <input type="text" class="form-control @error('employer_name') is-invalid @enderror" 
                   id="employer_name" name="employer_name" value="{{ old('employer_name') }}">
            @error('employer_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="bank_name" class="form-label">Bank Name</label>
            <input type="text" class="form-control @error('bank_name') is-invalid @enderror" 
                   id="bank_name" name="bank_name" value="{{ old('bank_name') }}">
            @error('bank_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="bank_account_number" class="form-label">Bank Account Number</label>
    <input type="text" class="form-control @error('bank_account_number') is-invalid @enderror" 
           id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number') }}">
    @error('bank_account_number')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="additional_notes" class="form-label">Additional Notes</label>
    <textarea class="form-control @error('additional_notes') is-invalid @enderror" 
              id="additional_notes" name="additional_notes" rows="3">{{ old('additional_notes') }}</textarea>
    @error('additional_notes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>