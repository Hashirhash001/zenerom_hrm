<div class="modal-header">
    <h5 class="modal-title" id="addProjectServiceStaffModalLabel">Add Staff to Service</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="addProjectServiceStaffForm">
    @csrf
    <!-- Hidden field for the project ID -->
    <input type="hidden" name="project_id" id="project_id" value="{{ $project->id }}">
    <!-- Dropdown for selecting the project service -->
    <div class="mb-3">
        <label for="staff_project_service_id" class="form-label">Service</label>
        <select class="form-control" id="staff_project_service_id" name="project_service_id" required>
            <option value="">Select Service</option>
            @foreach($project->projectServices as $ps)
                <option value="{{ $ps->id }}">{{ $ps->service->name ?? 'Service' }}</option>
            @endforeach
        </select>
    </div>
    <!-- Dropdown for selecting an employee -->
    <div class="mb-3">
        <label for="employee_id" class="form-label">Select Staff</label>
        <select class="form-control" id="user_id" name="user_id" required>
            <option value="">Select Staff</option>
            @foreach($employees as $employee)
                <option value="{{ $employee->id }}">
                    {{ $employee->employee_id }} - {{ $employee->first_name }} {{ $employee->last_name }}
                </option>
            @endforeach
        </select>
    </div>
    <!-- Assignment Status -->
    <div class="mb-3">
        <label for="assignment_status" class="form-label">Status</label>
        <select class="form-control" id="assignment_status" name="status" required>
            <option value="active" selected>Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
    <button type="button" class="btn btn-primary" id="saveProjectServiceStaffBtn">Save Staff Assignment</button>
</form>

</div>
