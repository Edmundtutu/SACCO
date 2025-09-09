<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="contact_person" class="form-label">Contact Person</label>
            <input type="text" class="form-control @error('contact_person') is-invalid @enderror" 
                   id="contact_person" name="contact_person" value="{{ old('contact_person') }}">
            @error('contact_person')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="contact_number" class="form-label">Contact Number</label>
            <input type="text" class="form-control @error('contact_number') is-invalid @enderror" 
                   id="contact_number" name="contact_number" value="{{ old('contact_number') }}">
            @error('contact_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="address" class="form-label">Institution Address</label>
    <textarea class="form-control @error('address') is-invalid @enderror" 
              id="address" name="address" rows="3">{{ old('address') }}</textarea>
    @error('address')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="membership_count" class="form-label">Number of Members</label>
    <input type="number" class="form-control @error('membership_count') is-invalid @enderror" 
           id="membership_count" name="membership_count" value="{{ old('membership_count') }}" min="1">
    @error('membership_count')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<h6 class="mt-4 mb-3">Board Members</h6>
<div id="board-members">
    <div class="board-member-item mb-3">
        <div class="row">
            <div class="col-md-4">
                <input type="text" class="form-control" name="board_members[0][name]" 
                       placeholder="Name" value="{{ old('board_members.0.name') }}">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" name="board_members[0][position]" 
                       placeholder="Position" value="{{ old('board_members.0.position') }}">
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" name="board_members[0][phone]" 
                       placeholder="Phone" value="{{ old('board_members.0.phone') }}">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-success btn-sm add-board-member">
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
            <label for="registration_certificate" class="form-label">Registration Certificate</label>
            <input type="text" class="form-control @error('registration_certificate') is-invalid @enderror" 
                   id="registration_certificate" name="registration_certificate" 
                   value="{{ old('registration_certificate') }}" 
                   placeholder="Certificate number or reference">
            @error('registration_certificate')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="operating_license" class="form-label">Operating License</label>
            <input type="text" class="form-control @error('operating_license') is-invalid @enderror" 
                   id="operating_license" name="operating_license" 
                   value="{{ old('operating_license') }}" 
                   placeholder="License number or reference">
            @error('operating_license')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="bylaws_copy" class="form-label">Bylaws Copy</label>
            <input type="text" class="form-control @error('bylaws_copy') is-invalid @enderror" 
                   id="bylaws_copy" name="bylaws_copy" 
                   value="{{ old('bylaws_copy') }}" 
                   placeholder="Document reference">
            @error('bylaws_copy')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="resolution_minutes" class="form-label">Resolution Minutes</label>
            <input type="text" class="form-control @error('resolution_minutes') is-invalid @enderror" 
                   id="resolution_minutes" name="resolution_minutes" 
                   value="{{ old('resolution_minutes') }}" 
                   placeholder="Minutes reference">
            @error('resolution_minutes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let boardMemberIndex = 1;
    
    $(document).on('click', '.add-board-member', function() {
        const newMember = `
            <div class="board-member-item mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="board_members[${boardMemberIndex}][name]" placeholder="Name">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="board_members[${boardMemberIndex}][position]" placeholder="Position">
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="board_members[${boardMemberIndex}][phone]" placeholder="Phone">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-board-member">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#board-members').append(newMember);
        boardMemberIndex++;
    });
    
    $(document).on('click', '.remove-board-member', function() {
        $(this).closest('.board-member-item').remove();
    });
});
</script>
@endpush