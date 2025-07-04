<form id="editDepartmentForm" enctype="multipart/form-data">
    @csrf
    @method('PATCH')
    <!-- Hidden field to hold the department id -->
    <input type="hidden" name="department_id" value="{{ $department->id }}">
    
    <div class="modal-header">
        <h5 class="modal-title" id="editDepartmentModalLabel">Edit Department</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    
    <div class="modal-body">
        <div class="mb-3">
            <label for="edit_name" class="form-label">Department Name</label>
            <input type="text" class="form-control" id="edit_name" name="name" value="{{ $department->name }}" required>
        </div>
        <div class="mb-3">
            <label for="edit_description" class="form-label">Description</label>
            <textarea class="form-control" id="edit_description" name="description">{{ $department->description }}</textarea>
        </div>
        <div class="mb-3">
            <label for="edit_image" class="form-label">Department Image</label>
            <input type="file" class="form-control" id="edit_image" name="image">
        </div>
        <!-- Hidden field for status, if needed -->
        <input type="hidden" name="status" value="{{ $department->status }}">
    </div>
    
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <!-- Update button triggers the update function -->
        <button type="button" class="btn btn-primary" id="updateDepartmentBtn">Update Department</button>
    </div>
</form>
