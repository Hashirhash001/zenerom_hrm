<div class="modal-header border-b border-gray-200">
    <h5 class="modal-title text-lg font-semibold" id="addReminderModalLabel">Add New Reminder</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-5">
    <form id="addReminderForm" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="project_id" class="form-label text-sm font-medium text-gray-700">Project <span class="text-danger">*</span></label>
            <select class="form-control select2 w-full border-gray-300 rounded-md shadow-sm text-sm p-2" id="project_id" name="project_id" required>
                <option value="" disabled selected>Select Project</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </select>
            <div id="project_id_error" class="invalid-feedback text-red-600 text-xs"></div>
        </div>
        <div class="mb-3">
            <label for="service_ids" class="form-label text-sm font-medium text-gray-700">Services (Optional)</label>
            <select class="form-control select2 w-full border-gray-300 rounded-md shadow-sm text-sm p-2" id="service_ids" name="service_ids[]" multiple>
                <option value="">Select Services</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                @endforeach
            </select>
            <div id="service_ids_error" class="invalid-feedback text-red-600 text-xs"></div>
        </div>
        <div class="mb-3">
            <label for="title" class="form-label text-sm font-medium text-gray-700">Reminder Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control w-full border-gray-300 rounded-md shadow-sm text-sm p-2" id="title" name="title" required>
            <div id="title_error" class="invalid-feedback text-red-600 text-xs"></div>
        </div>
        <div class="mb-3">
            <label for="message" class="form-label text-sm font-medium text-gray-700">Message (Optional)</label>
            <textarea class="form-control w-full border-gray-300 rounded-md shadow-sm text-sm p-2" id="message" name="message"></textarea>
            <div id="message_error" class="invalid-feedback text-red-600 text-xs"></div>
        </div>
        <div class="mb-3">
            <label for="type" class="form-label text-sm font-medium text-gray-700">Reminder Type <span class="text-danger">*</span></label>
            <select class="form-control w-full border-gray-300 rounded-md shadow-sm text-sm p-2" id="type" name="type" required>
                <option value="" disabled selected>Select Type</option>
                <option value="report_submission">Report Submission</option>
                <option value="meeting">Meeting</option>
                <option value="follow_up">Follow Up</option>
                <option value="other">Other</option>
            </select>
            <div id="type_error" class="invalid-feedback text-red-600 text-xs"></div>
        </div>
        <div class="mb-3">
            <label for="frequency" class="form-label text-sm font-medium text-gray-700">Frequency <span class="text-danger">*</span></label>
            <select class="form-control w-full border-gray-300 rounded-md shadow-sm text-sm p-2" id="frequency" name="frequency" required>
                <option value="" disabled selected>Select Frequency</option>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="one_time">One-Time</option>
            </select>
            <div id="frequency_error" class="invalid-feedback text-red-600 text-xs"></div>
        </div>
        <div class="mb-3">
            <label for="time_of_day" class="form-label text-sm font-medium text-gray-700">Time of Day <span class="text-danger">*</span></label>
            <input type="time" class="form-control w-full border-gray-300 rounded-md shadow-sm text-sm p-2" id="time_of_day" name="time_of_day" value="09:00" required>
            <div id="time_of_day_error" class="invalid-feedback text-red-600 text-xs"></div>
        </div>
        <div class="mb-3" id="days_of_week_field" style="display: none;">
            <label class="form-label text-sm font-medium text-gray-700">Days of Week <span class="text-danger">*</span></label>
            <div class="form-group">
                @foreach([1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'] as $value => $label)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="day{{ $value }}" name="days_of_week[]" value="{{ $value }}">
                        <label class="form-check-label text-sm text-gray-700" for="day{{ $value }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
            <div id="days_of_week_error" class="invalid-feedback text-red-600 text-xs"></div>
        </div>
        <div class="mb-3" id="day_of_month_field" style="display: none;">
            <label for="day_of_month" class="form-label text-sm font-medium text-gray-700">Day of Month <span class="text-danger">*</span></label>
            <input type="number" class="form-control w-full border-gray-300 rounded-md shadow-sm text-sm p-2" id="day_of_month" name="day_of_month" min="1" max="31">
            <div id="day_of_month_error" class="invalid-feedback text-red-600 text-xs"></div>
        </div>
        <div class="mb-3" id="specific_date_field" style="display: none;">
            <label for="specific_date" class="form-label text-sm font-medium text-gray-700">Specific Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control w-full border-gray-300 rounded-md shadow-sm text-sm p-2" id="specific_date" name="specific_date" min="2020-01-01" max="2050-12-31">
            <div id="specific_date_error" class="invalid-feedback text-red-600 text-xs"></div>
        </div>
        <div class="mb-3">
            <div class="form-check">
                <input type="hidden" name="email_notifications" value="0">
                <input type="checkbox" class="form-check-input" id="email_notifications" name="email_notifications" value="1">
                <label class="form-check-label text-sm text-gray-700" for="email_notifications">Send Email Notifications</label>
            </div>
            <div class="form-check">
                <input type="hidden" name="push_notifications" value="0">
                <input type="checkbox" class="form-check-input" id="push_notifications" name="push_notifications" value="1" checked>
                <label class="form-check-label text-sm text-gray-700" for="push_notifications">Send Push Notifications</label>
            </div>
            <div class="form-check">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" class="form-check-input" id="active" name="active" value="1" checked>
                <label class="form-check-label text-sm text-gray-700" for="active">Active</label>
            </div>
        </div>
        <div class="modal-footer border-t border-gray-200">
            <button type="button" class="btn btn-secondary text-xs px-4 py-2" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary text-xs px-4 py-2">Save Reminder</button>
        </div>
    </form>
</div>
