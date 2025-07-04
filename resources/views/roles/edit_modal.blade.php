<form id="editRoleForm" enctype="multipart/form-data">
    @csrf
    @method('PATCH')
    <input type="hidden" name="role_id" value="{{ $role->id }}">
    
    <div class="modal-header">
        <h5 class="modal-title" id="editRoleModalLabel">Edit Role</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    
    <div class="modal-body">
        <div class="mb-3">
            <label for="edit_role_name" class="form-label">Role Name</label>
            <input type="text" class="form-control" id="edit_role_name" name="name" value="{{ $role->name }}" required>
        </div>
        <div class="mb-3">
            <label for="edit_role_description" class="form-label">Description</label>
            <textarea class="form-control" id="edit_role_description" name="description">{{ $role->description }}</textarea>
        </div>
    </div>
    
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="updateRoleBtn">Update Role</button>
    </div>
</form>
