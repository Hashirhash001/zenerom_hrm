<div class="modal-header">
    <h5 class="modal-title" id="addTaskModalLabel">Add New Task</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    @isset($projects)
    <form id="addTaskForm">
        @csrf
        <!-- Project Selection -->
        <div class="mb-3">
            <label for="taskProject" class="form-label">Project</label>
            <select class="form-control" id="taskProject" name="project_id" required>
                <option value="">Select Project</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <!-- Service Selection -->
        <div class="mb-3">
            <label for="taskService" class="form-label">Service</label>
            <select class="form-control" id="taskService" name="service_id">
                <option value="">Select Service</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                @endforeach
            </select>
        </div>
        <!-- Title -->
        <div class="mb-3">
            <label for="taskTitle" class="form-label">Title</label>
            <input type="text" class="form-control" id="taskTitle" name="title" required>
        </div>
        <!-- Description -->
        <div class="mb-3">
            <label for="taskDescription" class="form-label">Description</label>
            <textarea class="form-control" id="taskDescription" name="description"></textarea>
        </div>
        <!-- Deadline -->
        <div class="mb-3">
            <label for="taskDeadline" class="form-label">Deadline</label>
            <input type="date" class="form-control" id="taskDeadline" name="deadline">
        </div>
        <!-- Status -->
        <div class="mb-3">
            <label for="taskStatus" class="form-label">Status</label>
            <select class="form-control" id="taskStatus" name="status" required>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="hold">Hold</option>
            </select>
        </div>
        <!-- Assign to Self -->
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="assignSelf" name="assign_self" value="1">
                <label class="form-check-label" for="assignSelf">Assign this task to myself</label>
            </div>
        </div>
        <!-- Frequency Type -->
        <div class="mb-3" id="frequencyFields" style="display: none;">
            <label for="addTaskFrequency" class="form-label">Frequency</label>
            <select class="form-control" id="addTaskFrequency" name="frequency">
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
                        <input class="form-check-input" type="checkbox" name="selected_days[]" value="{{ $day }}" id="addTaskDay{{ $day }}">
                        <label class="form-check-label" for="addTaskDay{{ $day }}">{{ $day }}</label>
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
                        <input class="form-check-input" type="checkbox" name="selected_dates[]" value="{{ $i }}" id="addTaskDate{{ $i }}">
                        <label class="form-check-label" for="addTaskDate{{ $i }}">{{ $i }}</label>
                    </div>
                @endfor
            </div>
        </div>
    </form>
    @else
        <p>Error: data is not available.</p>
    @endisset
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary" id="saveTaskBtn">Save Task</button>
</div>
