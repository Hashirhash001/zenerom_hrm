<div class="modal-header">
    <h5 class="modal-title" id="addProjectModalLabel">Add New Project</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="addProjectForm" enctype="multipart/form-data">
        @csrf
          <div class="mb-3">
            <label for="customer_id" class="form-label">Client</label>
            <select class="form-control" id="customer_id" name="customer_id" required>
                <option value="">Select Client</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Project Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="requirements" class="form-label">Requirements</label>
            <textarea class="form-control" id="requirements" name="requirements"></textarea>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-control" id="status" name="status" required>
                <option value="active" selected>Active</option>
                <option value="completed">Completed</option>
                <option value="delayed">Delayed</option>
                <option value="canceled">Canceled</option>
                <option value="hold">Hold</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="onboarded_time" class="form-label">Onboarded Time</label>
            <input type="datetime-local" class="form-control" id="onboarded_time" name="onboarded_time">
        </div>
        <div class="mb-3">
            <label for="payment_status" class="form-label">Payment Status</label>
            <select class="form-control" id="payment_status" name="payment_status" required>
                <option value="pending" selected>Pending</option>
                <option value="paid">Paid</option>
                <option value="unpaid">Unpaid</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="payment_type" class="form-label">Payment Type</label>
            <select class="form-control" id="payment_type" name="payment_type" required>
                <option value="one_time" selected>One-Time</option>
                <option value="monthly">Monthly</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="project_owner_id" class="form-label">Project Owner (Assigned Team Head)</label>
           <select class="form-control" id="project_owner_id" name="project_owner_id" required>
            <option value="">Select Project Owner</option>
            @foreach($employees as $employee)
                <option value="{{ $employee->id }}">
                    {{ $employee->employee_id }} - {{ $employee->first_name }} {{ $employee->last_name }}
                </option>
            @endforeach
        </select>
        </div>
        <button type="button" class="btn btn-primary" id="saveProjectBtn">Save Project</button>
    </form>
</div>
