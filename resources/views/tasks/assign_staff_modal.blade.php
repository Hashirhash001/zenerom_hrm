<div class="modal-header">
    <h5 class="modal-title" id="assignStaffModalLabel">Assign Staff to Task</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <form id="assignStaffForm">
        @csrf
        <!-- Hidden field to store the current task id -->
        <input type="hidden" name="task_id" value="">
        <!-- Frequency Type -->
        <div class="mb-3">
            <label for="assignStaffFrequency" class="form-label">Frequency</label>
            <select class="form-control" id="assignStaffFrequency" name="frequency" required>
                <option value="">Select Frequency</option>
                <option value="One-time">One-time</option>
                <option value="Daily">Daily</option>
                <option value="Once in a week">Once in a week</option>
                <option value="2 in a week">2 in a week</option>
                <option value="3 in a week">3 in a week</option>
                <option value="4 in a week">4 in a week</option>
                <option value="Monthly">Monthly</option>
                <option value="2 in Month">2 in Month</option>
                <option value="3 in Month">3 in Month</option>
                <option value="4 in Month">4 in Month</option>
            </select>
        </div>
        <!-- Conditional Fields for One-time -->
        <div class="mb-3 d-none" id="oneTimeFields">
            <label class="form-label">End Date</label>
            <input type="date" class="form-control" name="end_date">
        </div>
        <!-- Conditional Fields for Daily or Weekly -->
        <div class="mb-3 d-none" id="dailyWeeklyFields">
            <label class="form-label">Start Date</label>
            <input type="date" class="form-control" name="start_date">
        </div>
        <!-- Conditional Fields for Weekly Frequencies -->
        <div class="mb-3 d-none" id="weeklyFields">
            <label class="form-label">Select Days</label>
            <div>
                @php
                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                @endphp
                @foreach($days as $day)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="selected_days[]" value="{{ $day }}" id="assignStaffDay{{ $day }}">
                        <label class="form-check-label" for="assignStaffDay{{ $day }}">{{ $day }}</label>
                    </div>
                @endforeach
            </div>
        </div>
        <!-- Conditional Fields for Monthly Frequencies -->
        <div class="mb-3 d-none" id="monthlyFields">
            <label class="form-label">Select Dates of the Month</label>
            <div>
                @for($i = 1; $i <= 31; $i++)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="selected_dates[]" value="{{ $i }}" id="assignStaffDate{{ $i }}">
                        <label class="form-check-label" for="assignStaffDate{{ $i }}">{{ $i }}</label>
                    </div>
                @endfor
            </div>
        </div>
        <!-- Staff List -->
        <div class="mb-3">
            <label class="form-label">Select Staff</label>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Select</th>
                        <th>Staff Name</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staffs as $staff)
                        <tr>
                            <td>
                                <input type="checkbox" name="staff_ids[]" value="{{ $staff->id }}">
                            </td>
                            <td>{{ $staff->first_name }} {{ $staff->middle_name }} {{ $staff->last_name }}</td>
                            <td>{{ $staff->employee_id }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary" id="assignStaffBtn">Assign Staff</button>
</div>
