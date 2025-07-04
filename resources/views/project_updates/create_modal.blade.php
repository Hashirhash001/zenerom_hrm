<div class="modal-header">
    <h5 class="modal-title" id="addUpdateModalLabel">Add New Update</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="addUpdateForm">
        @csrf
        <input type="hidden" name="project_id" value="{{ $project->id }}">
        <!-- Optionally, select a service if needed -->
        <div class="mb-3">
            <label for="project_service_id" class="form-label">Service</label>
            <select class="form-control" id="project_service_id" name="project_service_id">
                <option value="">Select Service</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="update_title" class="form-label">Title</label>
            <input type="text" class="form-control" id="update_title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="update_note" class="form-label">Note</label>
            <textarea class="form-control summernote" id="update_note" name="note"></textarea>
        </div>
        <div class="mb-3">
            <label for="update_date" class="form-label">Date</label>
            <input type="date" class="form-control" id="date" name="date">
        </div>
        <div class="mb-3">
            <label for="assigned_to" class="form-label">Assign To</label>
            <select class="form-control" id="assigned_to" name="assigned_to">
                <option value="">Select Employee</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_id }})</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="update_status" class="form-label">Status</label>
            <select class="form-control" id="update_status" name="status" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="update_received_status" class="form-label">Received Status</label>
            <select class="form-control" id="update_received_status" name="received_status" required>
                <option value="pending">Pending</option>
                <option value="received">Received</option>
            </select>
        </div>
        <button type="button" class="btn btn-primary" id="saveUpdateBtn">Save Update</button>
    </form>
</div>
