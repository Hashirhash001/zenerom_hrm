<div class="modal-header">
    <h5 class="modal-title" id="editServiceModalLabel">Edit Service</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="editServiceForm">
        @csrf
        @method('PATCH')
        <input type="hidden" name="id" value="{{ $service->id }}">
        <div class="mb-3">
            <label for="edit_name" class="form-label">Service Name</label>
            <input type="text" class="form-control" id="edit_name" name="name" value="{{ $service->name }}" required>
        </div>
        <div class="mb-3">
            <label for="edit_details" class="form-label">Details</label>
            <textarea class="form-control" id="edit_details" name="details" required>{{ $service->details }}</textarea>
        </div>
        <div class="mb-3">
            <label for="edit_department_id" class="form-label">Department</label>
            <select class="form-control" id="edit_department_id" name="department_id" required>
                <option value="">Select Department</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ $service->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="edit_status" class="form-label">Status</label>
            <select class="form-control" id="edit_status" name="status">
                <option value="1" {{ $service->status == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ $service->status == 0 ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <button type="button" class="btn btn-primary" id="updateServiceBtn">Update Service</button>
    </form>
</div>
