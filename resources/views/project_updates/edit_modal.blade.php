<div class="modal-header">
    <h5 class="modal-title" id="editUpdateModalLabel">Edit Update</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    @isset($update)
    <form id="editUpdateForm">

        @csrf
        @method('PUT')
        <input type="hidden" name="update_id" value="{{ $update->id }}">
        <input type="hidden" name="project_id" value="{{ $project->id ?? $update->project_id }}">
        <!-- Service Selection -->
        <div class="mb-3">
            <label for="project_service_id_edit" class="form-label">Service</label>
            <select class="form-control" id="project_service_id_edit" name="project_service_id">
                <option value="">Select Service</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" {{ $update->project_service_id == $service->id ? 'selected' : '' }}>
                        {{ $service->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <!-- Title -->
        <div class="mb-3">
            <label for="update_title_edit" class="form-label">Title</label>
            <input type="text" class="form-control" id="update_title_edit" name="title" value="{{ $update->title }}" required>
        </div>
        <!-- Note -->
        <div class="mb-3">
            <label for="update_note_edit" class="form-label">Note</label>
            <textarea class="form-control summernote" id="update_note_edit" name="note">{{ $update->note }}</textarea>
        </div>
        <!-- Date -->
        <div class="mb-3">
            <label for="update_date_edit" class="form-label">Date</label>
            <input type="date" class="form-control" id="update_date_edit" name="date" value="{{ $update->date ? \Carbon\Carbon::parse($update->date)->format('Y-m-d') : '' }}">
        </div>
        <!-- Assign To -->
        <div class="mb-3">
            <label for="assigned_to_edit" class="form-label">Assign To</label>
            <select class="form-control" id="assigned_to_edit" name="assigned_to">
                <option value="">Select Employee</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ $update->assigned_to == $employee->id ? 'selected' : '' }}>
                        {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_id }})
                    </option>
                @endforeach
            </select>
        </div>
        <!-- Status -->
        <div class="mb-3">
            <label for="update_status_edit" class="form-label">Status</label>
            <select class="form-control" id="update_status_edit" name="status" required>
                <option value="active" {{ $update->status == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $update->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <!-- Received Status -->
        <div class="mb-3">
            <label for="update_received_status_edit" class="form-label">Received Status</label>
            <select class="form-control" id="update_received_status_edit" name="received_status" required>
                <option value="pending" {{ $update->received_status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="received" {{ $update->received_status == 'received' ? 'selected' : '' }}>Received</option>
            </select>
        </div>
        <button type="button" class="btn btn-primary" id="updateUpdateBtn">Update Update</button>
    </form>
     @else
        <p>Error:  data is not available.</p>
    @endisset
</div>



