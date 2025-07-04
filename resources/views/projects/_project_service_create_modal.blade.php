<div class="modal-header">
    <h5 class="modal-title" id="addProjectServiceModalLabel">Add Project Service</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="addProjectServiceForm">
        @csrf
        <input type="hidden" name="project_id" value="{{ $project->id }}">
        <div class="mb-3">
            <label for="service_id" class="form-label">Service</label>
            <select class="form-control" id="service_id" name="service_id" required>
                <option value="">Select Service</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="assigned_to_staff" class="form-label">Assigned To Staff</label>
            <select class="form-control" id="assigned_to_staff" name="assigned_to_staff">
                <option value="">Select Staff</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}">{{ $employee->employee_id }} - {{ $employee->first_name }} {{ $employee->last_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-control" id="status" name="status" required>
                <option value="pending" selected>Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="canceled">Canceled</option>
                <option value="hold">Hold</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes"></textarea>
        </div>
        <button type="button" class="btn btn-primary" id="saveProjectServiceBtn">Save Service</button>
    </form>
</div>
