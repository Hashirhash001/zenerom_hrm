<div class="modal-header">
    <h5 class="modal-title" id="editCustomerModalLabel">Edit Customer</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="editCustomerForm">
        @csrf
        @method('PATCH')
        <input type="hidden" name="id" value="{{ $customer->id }}">
        <div class="mb-3">
            <label for="edit_name" class="form-label">Customer Name</label>
            <input type="text" class="form-control" id="edit_name" name="name" value="{{ $customer->name }}" required>
        </div>
        <div class="mb-3">
            <label for="edit_contact_info" class="form-label">Contact Information</label>
            <textarea class="form-control" id="edit_contact_info" name="contact_info">{{ $customer->contact_info }}</textarea>
        </div>
        <button type="button" class="btn btn-primary" id="updateCustomerBtn">Update Customer</button>
    </form>
</div>
