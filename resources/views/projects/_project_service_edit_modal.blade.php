<div class="modal-header">
    <h5 class="modal-title" id="editProjectServiceModalLabel">Edit Project Service</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    @isset($projectService)
    <form id="editProjectServiceForm">
        @csrf
        @method('PATCH')
        <input type="hidden" name="id" value="{{ $projectService->id }}">
        <input type="hidden" name="project_id" value="{{ $project->id }}">
        <div class="mb-3">
            <label for="edit_service_id" class="form-label">Service</label>
            <select class="form-control" id="edit_service_id" name="service_id" required>
                <option value="">Select Service</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" {{ $projectService->service_id == $service->id ? 'selected' : '' }}>
                        {{ $service->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="edit_assigned_to_staff" class="form-label">Assigned To Staff</label>
            <select class="form-control" id="edit_assigned_to_staff" name="assigned_to_staff">
                <option value="">Select Staff</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ $projectService->assigned_to_staff == $employee->id ? 'selected' : '' }}>
                        {{ $employee->employee_id }} - {{ $employee->first_name }} {{ $employee->last_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="edit_status" class="form-label">Status</label>
            <select class="form-control" id="edit_status" name="status" required>
                <option value="pending" {{ $projectService->status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="in_progress" {{ $projectService->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ $projectService->status == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="canceled" {{ $projectService->status == 'canceled' ? 'selected' : '' }}>Canceled</option>
                <option value="hold" {{ $projectService->status == 'hold' ? 'selected' : '' }}>Hold</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="edit_notes" class="form-label">Notes</label>
            <textarea class="form-control" id="edit_notes" name="notes">{{ $projectService->notes }}</textarea>
        </div>
        <button type="button" class="btn btn-primary" id="updateProjectServiceBtn">Update Service</button>
    </form>
    @else
        <p>Error: Project service data is not available.</p>
    @endisset
</div>
