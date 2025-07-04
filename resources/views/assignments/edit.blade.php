<div class="modal-header">
    <h5 class="modal-title" id="editAssignmentModalLabel">Edit Assignment</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="editAssignmentForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="assignment_id" value="{{ $assignment->id }}">
        
        <div class="mb-3">
            <label for="assignmentDate" class="form-label">Assignment Date</label>
            <input type="date" class="form-control" id="assignmentDate" name="date" 
                   value="{{ \Carbon\Carbon::parse($assignment->date)->toDateString() }}" required>
        </div>
        
        <div class="mb-3">
            <label for="assignmentStatus" class="form-label">Status</label>
            <select class="form-control" id="assignmentStatus" name="status">
                <option value="0" {{ $assignment->status == 0 ? 'selected' : '' }}>Pending</option>
                <option value="1" {{ $assignment->status == 1 ? 'selected' : '' }}>Completed</option>
            </select>
        </div>
        
        <!-- New Staff Select Field -->
        <div class="mb-3">
            <label for="assignmentStaff" class="form-label">Staff</label>
            <select class="form-control" id="assignmentStaff" name="staff_id" required>
                @foreach($staffs as $staff)
                    <option value="{{ $staff->id }}" {{ $assignment->staff_id == $staff->id ? 'selected' : '' }}>
                        {{ $staff->first_name }} {{ $staff->last_name }} ({{ $staff->employee_id }})
                    </option>
                @endforeach
            </select>
        </div>
        
        <button type="button" class="btn btn-primary" id="saveAssignmentBtn">Save</button>
    </form>
</div>
