<div class="modal-header">
    <h5 class="modal-title" id="addLeaveRequestModalLabel">Add Leave Request</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="addLeaveRequestForm">
        @csrf
        <div class="form-group">
            <label for="leave_type">Leave Type</label>
            <select class="form-control" name="leave_type" id="leave_type" required>
                <option value="">Select Leave Type</option>
                <option value="Annual">Annual Leave</option>
                <option value="Sick">Sick Leave</option>
                <option value="Maternity">Maternity Leave</option>
                <option value="Unpaid">Unpaid Leave</option>
            </select>
        </div>
        <div class="form-group">
            <label for="start_date">Start Date</label>
            <input type="date" class="form-control" name="start_date" id="start_date" required>
        </div>
        <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="date" class="form-control" name="end_date" id="end_date" required>
        </div>
        <div class="form-group">
            <label for="reason">Reason (Optional)</label>
            <textarea class="form-control" name="reason" id="reason" rows="4"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Request</button>
    </form>
</div>
