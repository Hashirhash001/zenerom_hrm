<div class="modal-header">
    <h5 class="modal-title" id="editProjectModalLabel">Edit Project</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="editProjectForm" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <input type="hidden" name="id" value="{{ $project->id }}">
        <div class="mb-3">
            <label for="customer_id" class="form-label">Client</label>
            <select class="form-control" id="customer_id" name="customer_id" required>
                <option value="">Select Client</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ $project->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="edit_name" class="form-label">Project Name</label>
            <input type="text" class="form-control" id="edit_name" name="name" value="{{ $project->name }}" required>
        </div>
        <div class="mb-3">
            <label for="edit_requirements" class="form-label">Requirements</label>
            <textarea class="form-control" id="edit_requirements" name="requirements">{{ $project->requirements }}</textarea>
        </div>
        <div class="mb-3">
            <label for="edit_status" class="form-label">Status</label>
            <select class="form-control" id="edit_status" name="status" required>
                <option value="active" {{ $project->status == 'active' ? 'selected' : '' }}>Active</option>
                <option value="completed" {{ $project->status == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="delayed" {{ $project->status == 'delayed' ? 'selected' : '' }}>Delayed</option>
                <option value="canceled" {{ $project->status == 'canceled' ? 'selected' : '' }}>Canceled</option>
                <option value="hold" {{ $project->status == 'hold' ? 'selected' : '' }}>Hold</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="edit_onboarded_time" class="form-label">Onboarded Time</label>
            <input type="datetime-local" class="form-control" id="edit_onboarded_time" name="onboarded_time" value="{{ $project->onboarded_time ? \Carbon\Carbon::parse($project->onboarded_time)->format('Y-m-d\TH:i') : '' }}">
        </div>
        <div class="mb-3">
            <label for="edit_payment_status" class="form-label">Payment Status</label>
            <select class="form-control" id="edit_payment_status" name="payment_status" required>
                <option value="pending" {{ $project->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="paid" {{ $project->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="unpaid" {{ $project->payment_status == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="edit_payment_type" class="form-label">Payment Type</label>
            <select class="form-control" id="edit_payment_type" name="payment_type" required>
                <option value="one_time" {{ $project->payment_type == 'one_time' ? 'selected' : '' }}>One-Time</option>
                <option value="monthly" {{ $project->payment_type == 'monthly' ? 'selected' : '' }}>Monthly</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="edit_project_owner_id" class="form-label">Project Owner (Assigned Team Head)</label>
            <select class="form-control" id="edit_project_owner_id" name="project_owner_id" required>
                <option value="">Select Project Owner</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ $project->project_owner_id == $employee->id ? 'selected' : '' }}>
                        {{ $employee->employee_id }} - {{ $employee->first_name }} {{ $employee->last_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="edit_location" class="form-label">Project Location</label>
            <select class="form-control" id="edit_location" name="location" required>
                <option value="">Select Location</option>
                <option value="UAE" {{ $project->location == 'UAE' ? 'selected' : '' }}>UAE</option>
                <option value="India" {{ $project->location == 'India' ? 'selected' : '' }}>India</option>
                <option value="UK" {{ $project->location == 'UK' ? 'selected' : '' }}>UK</option>
            </select>
        </div>
        {{-- <div class="mb-3">
            <label for="edit_report_date" class="form-label">Report Date</label>
            <input type="date" class="form-control" id="edit_report_date" name="report_date" value="{{ $project->report_date ? \Carbon\Carbon::parse($project->report_date)->format('Y-m-d') : '' }}" onclick="this.showPicker()">
        </div> --}}
        <button type="button" class="btn btn-primary" id="updateProjectBtn">Update Project</button>
    </form>
</div>
