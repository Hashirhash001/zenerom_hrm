@extends('layouts.app')

@section('content')
<div class="nk-content">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <!-- Header Section -->
                <div class="nk-block-head nk-block-head-sm">
                    <div class="nk-block-between">
                        <div class="nk-block-head-content">
                            <h3 class="nk-block-title page-title">Tasks</h3>
                            <div class="nk-block-des text-soft">
                                <p>You have total {{ $tasks->count() }} Tasks.</p>
                            </div>
                        </div>

                        <div class="nk-block-head-content">
                            <div class="toggle-wrap nk-block-tools-toggle">
                                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu">
                                    <em class="icon ni ni-menu-alt-r"></em>
                                </a>
                                <div class="toggle-expand-content" data-content="pageMenu">
                                    <ul class="nk-block-tools g-3">
                                        <li>
                                            <div>
                                                <input type="hidden" class="form-control" id="taskSearch" placeholder="Search Tasks">
                                            </div>
                                        </li>
                                        <li class="nk-block-tools-opt d-none d-sm-block">
                                            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                                                <em class="icon ni ni-plus"></em><span>Add Task</span>
                                            </a>
                                        </li>
                                        <li class="nk-block-tools-opt d-block d-sm-none">
                                            <a href="#" class="btn btn-icon btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                                                <em class="icon ni ni-plus"></em>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div><!-- .toggle-wrap -->
                        </div>
                    </div>
                </div>
                <!-- End Header -->
                <div class="col-md-12">
                    <form method="GET" action="{{ route('tasks.index') }}" class="row g-3 mb-4">
                        <div class="col-md-2">
                            <label for="created_start" class="form-label">Created - Start Date</label>
                            <input type="date" name="created_start" id="created_start" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="created_end" class="form-label">Created - End Date</label>
                            <input type="date" name="created_end" id="created_end" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="deadline_start" class="form-label">Deadline - Start Date</label>
                            <input type="date" name="deadline_start" id="deadline_start" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="deadline_end" class="form-label">Deadline - End Date</label>
                            <input type="date" name="deadline_end" id="deadline_end" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="assigned_by" class="form-label">Created By</label>
                            <select name="assigned_by" id="assigned_by" class="form-control">
                                <option value="">All</option>
                                @foreach($staffs as $staff)
                                    <option value="{{ $staff->id }}" {{ request('assigned_by') == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->first_name }} {{ $staff->middle_name }} {{ $staff->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="assigned_staff" class="form-label">Assigned to</label>
                            <select name="assigned_staff" id="assigned_staff" class="form-control">
                                <option value="">All</option>
                                @foreach($staffs as $staff)
                                    <option value="{{ $staff->id }}" {{ request('assigned_staff') == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->first_name }} {{ $staff->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="project_id" class="form-label">Project</label>
                            <select name="project_id" id="project_id" class="form-control">
                                <option value="">All</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="service_id" class="form-label">Service</label>
                            <select name="service_id" id="service_id" class="form-control">
                                <option value="">All</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                        {{ $service->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 align-self-end">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="{{ route('tasks.index') }}" class="btn btn-secondary">Clear</a>
                        </div>
                    </form>
                </div>

                <!-- Tasks List Container -->
                <div id="tasksContainer">
                    @include('tasks._list', ['tasks' => $tasks])
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Adding New Task -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            @include('tasks.create_modal')
        </div>
    </div>
</div>

<!-- Modal for Editing Task -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- The edit form will be loaded via AJAX -->
        </div>
    </div>
</div>

<!-- Popup Modal for messages -->
<div class="modal fade" id="popupMessageModal" tabindex="-1" aria-labelledby="popupMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="popupMessageModalLabel">Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Message content will be injected here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Assign Staff Modal -->
<div class="modal fade" id="assignStaffModal" tabindex="-1" aria-labelledby="assignStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            @include('tasks.assign_staff_modal')
        </div>
    </div>
</div>

<!-- jQuery and jQuery UI -->
<script src="{{ asset('assets1/jquery.min.js') }}"></script>
<script src="{{ asset('assets1/jquery-ui/jquery-ui.js') }}"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    // Frequency type handler for addTaskForm
    $(document).on('change', '#addTaskFrequency', function() {
        var freq = $(this).val();
        $('#addTaskForm #oneTimeFields, #addTaskForm #dailyWeeklyFields, #addTaskForm #weeklyFields, #addTaskForm #monthlyFields').addClass('d-none');
        if (freq === 'One-time') {
            $('#addTaskForm #oneTimeFields').removeClass('d-none');
        } else if (freq === 'Daily') {
            $('#addTaskForm #dailyWeeklyFields').removeClass('d-none');
        } else if (freq.indexOf('week') !== -1) {
            $('#addTaskForm #dailyWeeklyFields, #addTaskForm #weeklyFields').removeClass('d-none');
        } else if (freq.indexOf('Month') !== -1) {
            $('#addTaskForm #dailyWeeklyFields, #addTaskForm #monthlyFields').removeClass('d-none');
        }
    });

    // Frequency type handler for assignStaffForm
    $('#assignStaffModal').on('shown.bs.modal', function() {
        $('#assignStaffFrequency').off('change').on('change', function() {
            var freq = $(this).val();
            $('#assignStaffForm #oneTimeFields, #assignStaffForm #dailyWeeklyFields, #assignStaffForm #weeklyFields, #assignStaffForm #monthlyFields').addClass('d-none');
            if (freq === 'One-time') {
                $('#assignStaffForm #oneTimeFields').removeClass('d-none');
            } else if (freq === 'Daily') {
                $('#assignStaffForm #dailyWeeklyFields').removeClass('d-none');
            } else if (freq.indexOf('week') !== -1) {
                $('#assignStaffForm #dailyWeeklyFields, #assignStaffForm #weeklyFields').removeClass('d-none');
            } else if (freq.indexOf('Month') !== -1) {
                $('#assignStaffForm #dailyWeeklyFields, #assignStaffForm #monthlyFields').removeClass('d-none');
            }
        });
        // Trigger change to initialize fields
        $('#assignStaffFrequency').trigger('change');
    });

    // Assign Staff button handler
    $(document).on('click', '.assignStaffBtn', function(e) {
        e.preventDefault();
        var taskId = $(this).data('id');
        $('#assignStaffForm input[name="task_id"]').val(taskId);
        $('#assignStaffModal').modal('show');
    });

    // Assign Staff form submission
    $(document).on('click', '#assignStaffBtn', function(e) {
        e.preventDefault();
        var form = $('#assignStaffForm');
        var frequency = $('#assignStaffFrequency').val();

        // Client-side validation
        if (!frequency) {
            showPopup('Please select a frequency for assignment.');
            return;
        }
        if (frequency === 'One-time' && !$('#assignStaffForm #oneTimeFields input[name="end_date"]').val()) {
            showPopup('Please select an end date for One-time frequency.');
            return;
        }
        if ((frequency === 'Daily' || frequency.includes('week') || frequency.includes('Month')) && !$('#assignStaffForm #dailyWeeklyFields input[name="start_date"]').val()) {
            showPopup('Please select a start date.');
            return;
        }
        if (frequency.includes('week') && !$('#assignStaffForm input[name="selected_days[]"]:checked').length) {
            showPopup('Please select at least one day for weekly frequency.');
            return;
        }
        if (frequency.includes('Month') && !$('#assignStaffForm input[name="selected_dates[]"]:checked').length) {
            showPopup('Please select at least one date for monthly frequency.');
            return;
        }
        if (!$('#assignStaffForm input[name="staff_ids[]"]:checked').length) {
            showPopup('Please select at least one staff member.');
            return;
        }

        var formData = new FormData(form[0]);
        var taskId = formData.get('task_id');
        $.ajax({
            url: "/tasks/" + taskId + "/assign-staff",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    showPopup(response.message);
                    $('#assignStaffModal').modal('hide');
                    setTimeout(() => {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('padding-right', '');
                        $('.modal').removeAttr('aria-hidden').removeAttr('aria-modal').css('display', 'none');
                    }, 300);
                    location.reload();
                } else {
                    showPopup("Error assigning staff: " + (response.message || "Unknown error"));
                }
            },
            error: function(xhr) {
                console.error("Assign Staff Error:", xhr.responseText);
                showPopup("Error assigning staff: " + (xhr.responseJSON?.message || "Server error"));
            }
        });
    });

    // Save new Task via Ajax
    $(document).on('click', '#saveTaskBtn', function(e) {
        e.preventDefault();
        var form = $('#addTaskForm');
        var frequency = $('#addTaskFrequency').val();
        var assignStaff = $('#addTaskForm input[name="staff_ids[]"]:checked').length > 0;

        // Client-side validation
        if (assignStaff) {
            if (!frequency) {
                showPopup('Please select a frequency for assignment.');
                return;
            }
            if (frequency === 'One-time' && !$('#addTaskForm #oneTimeFields input[name="end_date"]').val()) {
                showPopup('Please select an end date for One-time frequency.');
                return;
            }
            if ((frequency === 'Daily' || frequency.includes('week') || frequency.includes('Month')) && !$('#addTaskForm #dailyWeeklyFields input[name="start_date"]').val()) {
                showPopup('Please select a start date.');
                return;
            }
            if (frequency.includes('week') && !$('#addTaskForm input[name="selected_days[]"]:checked').length) {
                showPopup('Please select at least one day for weekly frequency.');
                return;
            }
            if (frequency.includes('Month') && !$('#addTaskForm input[name="selected_dates[]"]:checked').length) {
                showPopup('Please select at least one date for monthly frequency.');
                return;
            }
        }

        var formData = new FormData(form[0]);
        $.ajax({
            url: "{{ route('tasks.store') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    showPopup(response.message);
                    $('#addTaskModal').modal('hide');
                    setTimeout(() => {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('padding-right', '');
                        $('.modal').removeAttr('aria-hidden').removeAttr('aria-modal').css('display', 'none');
                    }, 300);
                    location.reload();
                } else {
                    showPopup("Error saving task: " + (response.message || "Unknown error"));
                }
            },
            error: function(xhr) {
                console.error("Save Task Error:", xhr.responseText);
                showPopup("Error saving task: " + (xhr.responseJSON?.message || "Server error"));
            }
        });
    });

    // Load Edit Task form via Ajax
    function editTask(id) {
        $.ajax({
            url: "{{ url('tasks') }}/" + id + "/edit",
            type: "GET",
            success: function(response) {
                $('#editTaskModal .modal-content').html(response);
                $('#editTaskModal').modal('show');
            },
            error: function(xhr) {
                console.error("Edit Task Error:", xhr.responseText);
                showPopup("Error loading edit form.");
            }
        });
    }

    // Update Task via Ajax
    function updateTask() {
        var formData = new FormData($('#editTaskForm')[0]);
        var taskId = $('#editTaskForm input[name="id"]').val();
        $.ajax({
            url: "{{ url('tasks') }}/" + taskId,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    showPopup(response.message);
                    $('#editTaskModal').modal('hide');
                    setTimeout(() => {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('padding-right', '');
                        $('.modal').removeAttr('aria-hidden').removeAttr('aria-modal').css('display', 'none');
                    }, 300);
                    location.reload();
                } else {
                    showPopup("Error updating task: " + (response.message || "Unknown error"));
                }
            },
            error: function(xhr) {
                console.error("Update Task Error:", xhr.responseText);
                showPopup("Error updating task.");
            }
        });
    }

    // Delete Task via Ajax
    function deleteTask(id) {
        if (confirm('Are you sure you want to delete this task?')) {
            $.ajax({
                url: "{{ url('tasks') }}/" + id,
                type: "DELETE",
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    if (response.success) {
                        showPopup(response.message);
                        location.reload();
                    } else {
                        showPopup("Error deleting task: " + (response.message || "Unknown error"));
                    }
                },
                error: function(xhr) {
                    console.error("Delete Task Error:", xhr.responseText);
                    showPopup("Error deleting task: " + (xhr.responseJSON?.message || "Server error"));
                }
            });
        }
    }

    // Live search for tasks
    $('#taskSearch').on('keyup', function() {
        var query = $(this).val();
        $.ajax({
            url: "{{ route('tasks.search') }}",
            type: "GET",
            data: { query: query },
            success: function(response) {
                $('#tasksContainer').html(response.html);
            },
            error: function(xhr) {
                console.error("Task Search Error:", xhr.responseText);
                showPopup("Error searching tasks.");
            }
        });
    });

    // Utility: Show popup modal with a message
    function showPopup(message) {
        $('#popupMessageModal .modal-body').html("<p>" + message + "</p>");
        $('#popupMessageModal').modal('show');
    }

    // Bind events
    $(document).on('click', '.editTaskBtn', function(e) {
        var id = $(this).data('id');
        editTask(id);
    });

    $(document).on('click', '.deleteTaskBtn', function(e) {
        var id = $(this).data('id');
        deleteTask(id);
    });

    $(document).on('click', '#updateTaskBtn', function(e) {
        e.preventDefault();
        updateTask();
    });
});
</script>
@endsection
