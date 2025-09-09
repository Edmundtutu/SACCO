<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label for="village" class="form-label">Village <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('village') is-invalid @enderror" 
                   id="village" name="village" value="{{ old('village') }}" required>
            @error('village')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="sub_county" class="form-label">Sub County <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('sub_county') is-invalid @enderror" 
                   id="sub_county" name="sub_county" value="{{ old('sub_county') }}" required>
            @error('sub_county')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="district" class="form-label">District <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('district') is-invalid @enderror" 
                   id="district" name="district" value="{{ old('district') }}" required>
            @error('district')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="membership_count" class="form-label">Number of Members <span class="text-danger">*</span></label>
    <input type="number" class="form-control @error('membership_count') is-invalid @enderror" 
           id="membership_count" name="membership_count" value="{{ old('membership_count') }}" min="1" required>
    @error('membership_count')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<h6 class="mt-4 mb-3">Executive Contacts</h6>
<div id="executive-contacts">
    <div class="executive-contact-item mb-3">
        <div class="row">
            <div class="col-md-4">
                <input type="text" class="form-control" name="executive_contacts[0][name]" 
                       placeholder="Name" value="{{ old('executive_contacts.0.name') }}">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" name="executive_contacts[0][position]" 
                       placeholder="Position" value="{{ old('executive_contacts.0.position') }}">
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" name="executive_contacts[0][phone]" 
                       placeholder="Phone" value="{{ old('executive_contacts.0.phone') }}">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-success btn-sm add-contact">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<h6 class="mt-4 mb-3">Required Documents</h6>
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="registration_certificate" class="form-label">Registration Certificate <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('registration_certificate') is-invalid @enderror" 
                   id="registration_certificate" name="registration_certificate" 
                   value="{{ old('registration_certificate') }}" 
                   placeholder="Certificate number or reference" required>
            @error('registration_certificate')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="constitution_copy" class="form-label">Constitution Copy <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('constitution_copy') is-invalid @enderror" 
                   id="constitution_copy" name="constitution_copy" 
                   value="{{ old('constitution_copy') }}" 
                   placeholder="Document reference" required>
            @error('constitution_copy')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="resolution_minutes" class="form-label">Resolution Minutes <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('resolution_minutes') is-invalid @enderror" 
                   id="resolution_minutes" name="resolution_minutes" 
                   value="{{ old('resolution_minutes') }}" 
                   placeholder="Minutes reference" required>
            @error('resolution_minutes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<h6 class="mt-4 mb-3">Recommendations</h6>
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="recommendation_lc1" class="form-label">LC1 Recommendation <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('recommendation_lc1') is-invalid @enderror" 
                   id="recommendation_lc1" name="recommendation_lc1" 
                   value="{{ old('recommendation_lc1') }}" 
                   placeholder="LC1 recommendation reference" required>
            @error('recommendation_lc1')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="recommendation_cdo" class="form-label">CDO Recommendation <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('recommendation_cdo') is-invalid @enderror" 
                   id="recommendation_cdo" name="recommendation_cdo" 
                   value="{{ old('recommendation_cdo') }}" 
                   placeholder="CDO recommendation reference" required>
            @error('recommendation_cdo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let contactIndex = 1;
    
    $(document).on('click', '.add-contact', function() {
        const newContact = `
            <div class="executive-contact-item mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="executive_contacts[${contactIndex}][name]" placeholder="Name">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="executive_contacts[${contactIndex}][position]" placeholder="Position">
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="executive_contacts[${contactIndex}][phone]" placeholder="Phone">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-contact">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#executive-contacts').append(newContact);
        contactIndex++;
    });
    
    $(document).on('click', '.remove-contact', function() {
        $(this).closest('.executive-contact-item').remove();
    });
});
</script>
@endpush