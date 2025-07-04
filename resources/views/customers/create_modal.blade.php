<div class="modal-header">
    <h5 class="modal-title" id="addCustomerModalLabel">Add New Customer</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="addCustomerForm">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Customer Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="contact_info" class="form-label">Contact Information</label>
            <textarea class="form-control" id="contact_info" name="contact_info"></textarea>
        </div>
        <!-- Optionally, include fields to add initial contacts -->
        <button type="button" class="btn btn-primary" id="saveCustomerBtn">Save Customer</button>
    </form>
</div>
