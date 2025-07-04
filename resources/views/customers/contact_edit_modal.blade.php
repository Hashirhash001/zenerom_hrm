<div class="modal-header">
    <h5 class="modal-title" id="editContactModalLabel">Edit Contact</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="editContactForm">
        @csrf
        @method('PATCH')
        <input type="hidden" name="id" value="{{ $customerContact->id }}">
        <div class="mb-3">
            <label for="contact_name" class="form-label">Contact Name</label>
            <input type="text" class="form-control" id="contact_name" name="contact_name" value="{{ $customerContact->contact_name }}" required>
        </div>
        <div class="mb-3">
            <label for="contact_email" class="form-label">Contact Email</label>
            <input type="email" class="form-control" id="contact_email" name="contact_email" value="{{ $customerContact->contact_email }}">
        </div>
        <div class="mb-3">
            <label for="contact_phone" class="form-label">Contact Phone</label>
            <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="{{ $customerContact->contact_phone }}">
        </div>
        <button type="button" class="btn btn-primary" id="updateContactBtn">Update Contact</button>
    </form>
</div>
