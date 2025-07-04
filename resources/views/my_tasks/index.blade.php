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
                            <h3 class="nk-block-title page-title">My Tasks <span class="badge bg-success">{{ $tdtaskcnt }}</span></h3>
                        </div>
                        <div class="nk-block-head-content">
                            <div class="toggle-wrap nk-block-tools-toggle">
                                <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu">
                                    <em class="icon ni ni-menu-alt-r"></em>
                                </a>
                                <div class="toggle-expand-content" data-content="pageMenu">
                                    <ul class="nk-block-tools g-3">
                                        <li>
                                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                                                <em class="icon ni ni-plus"></em><span>Add New Task</span>
                                            </button>
                                        </li>
                                        <li>
                                            <button class="btn btn-secondary" id="toggleTasksBtn">Show All Tasks</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Header -->

                <!-- Tasks List Section -->
                <div class="card card-bordered">
                    <div class="card-inner">
                        <div id="tasksTableWrapper" @if (!$tasks->count()) style="display: none;" @endif>
                            <table class="table table-bordered table-striped" id="tasksTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Project</th>
                                        <th>Service</th>
                                        <th>Created by</th>
                                        <th>Operations</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tasks as $task)
                                        <tr>
                                            <td>
                                                {{ $task->id }}
                                                @if ($task->tdtask == 1)
                                                    @if ($task->all_assigned_updated)
                                                        <span class="badge bg-warning">Updated</span>
                                                    @else
                                                        <span class="badge bg-success">Task Today</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-gray">No Tasks</span>
                                                @endif
                                            </td>
                                            <td>{{ $task->title }}</td>
                                            <td>{{ $task->description }}</td>
                                            <td>
                                                @if ($task->date)
                                                    {{ \Carbon\Carbon::parse($task->date)->format('d M Y') }}
                                                @else
                                                    {{ $task->created_at->format('d M Y') }}
                                                @endif
                                            </td>
                                            <td>{{ ucfirst($task->status) }}</td>
                                            <td>{{ optional($task->project)->name }}</td>
                                            <td>{{ $task->service ? $task->service->name : 'Uncategorized' }}</td>
                                            <td>{{ optional($task->creator)->first_name }} {{ optional($task->creator)->middle_name }} {{ optional($task->creator)->last_name }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-primary assignStaffBtn" data-task="{{ $task->id }}">Self Assign</button>
                                                <button class="btn btn-warning btn-sm editTaskBtn" data-id="{{ $task->id }}">Edit</button>
                                                <button class="btn btn-danger btn-sm deleteTaskBtn" data-id="{{ $task->id }}">Delete</button>
                                                <button class="btn btn-sm btn-secondary viewDetailsBtn" onclick="window.location.href='{{ route('my-tasks.details', $task->id) }}'">View Details</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p id="noTasksMessage" @if ($tasks->count()) style="display: none;" @endif>No tasks assigned to you today.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add New Task Modal -->
    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                @include('my_tasks.create_modal')
            </div>
        </div>
    </div>

    <!-- Modal for Assign Staff -->
    <div class="modal fade" id="assignStaffModal" tabindex="-1" aria-labelledby="assignStaffModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                @include('my_tasks.assign_staff_modal')
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
</div>

<!-- jQuery -->
<script src="{{ asset('assets1/jquery.min.js') }}"></script>
<!-- Moment.js -->
<script src="https://momentjs.com/downloads/moment.js"></script>
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<!-- Bootstrap JS (for modals) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

<script>
    let table;
    let showingTodayTasks = true;

    $(document).ready(function() {
        // Initialize DataTable
        function initializeDataTable() {
            if ($.fn.DataTable.isDataTable('#tasksTable')) {
                $('#tasksTable').DataTable().destroy();
            }
            table = $('#tasksTable').DataTable({
                dom: "<'row mb-3'<'col-sm-6'l><'col-sm-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row mt-3'<'col-sm-5'i><'col-sm-7'p>>",
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                autoWidth: false,
                responsive: true,
                order: [[3, 'desc']],
                columnDefs: [
                    { type: 'date', targets: 3 }
                ]
            });
        }

        // Only initialize DataTable if tasks exist on server-side render
        @if ($tasks->count())
            initializeDataTable();
        @endif

        // Function to load all tasks
        function loadAllTasks() {
            $.ajax({
                url: "{{ route('my_tasks.index') }}",
                type: "GET",
                data: { ajax: true },
                success: function(response) {
                    console.log("All Tasks Response:", response);
                    $('#noTasksMessage').hide();
                    $('#tasksTableWrapper').show();
                    if (response.success && Array.isArray(response.tasks)) {
                        if (response.tasks.length === 0) {
                            $('#tasksTableWrapper').hide();
                            $('#noTasksMessage').text('No tasks assigned to you.').show();
                        } else {
                            initializeDataTable();
                            table.clear();
                            $.each(response.tasks, function(index, task) {
                                var badge = task.tdtask == 1
                                    ? (task.all_assigned_updated
                                        ? '<span class="badge bg-warning">Updated</span>'
                                        : '<span class="badge bg-success">Task Today</span>')
                                    : '<span class="badge bg-gray">No Tasks</span>';

                                table.row.add({
                                    "0": task.id + ' ' + badge,
                                    "1": task.title || '',
                                    "2": task.description || '',
                                    "3": task.date ? moment(task.date).format('DD MMM YYYY') : moment(task.created_at).format('DD MMM YYYY'),
                                    "4": task.status ? task.status.charAt(0).toUpperCase() + task.status.slice(1) : 'No Status',
                                    "5": task.project ? task.project.name : '',
                                    "6": task.service ? task.service.name : 'Uncategorized',
                                    "7": task.creator ? (task.creator.first_name + ' ' + (task.creator.middle_name || '') + ' ' + task.creator.last_name) : 'Unknown Creator',
                                    "8": '<button class="btn btn-sm btn-primary assignStaffBtn" data-task="' + task.id + '">Self Assign</button>' +
                                         '<button class="btn btn-warning btn-sm editTaskBtn" data-id="' + task.id + '">Edit</button>' +
                                         '<button class="btn btn-danger btn-sm deleteTaskBtn" data-id="' + task.id + '">Delete</button>' +
                                         '<button class="btn btn-sm btn-secondary viewDetailsBtn" onclick="window.location.href=\'/my-tasks/' + task.id + '/details\'">View Details</button>'
                                });
                            });
                            table.draw();
                        }
                        $('#toggleTasksBtn').text('Show Today\'s Tasks');
                        showingTodayTasks = false;
                    } else {
                        $('#tasksTableWrapper').hide();
                        $('#noTasksMessage').text('No tasks assigned to you.').show();
                        console.error("Invalid tasks data:", response);
                        alert("No tasks found or invalid data.");
                    }
                },
                error: function(xhr) {
                    console.error("AJAX Error (All Tasks):", xhr.responseText);
                    $('#tasksTableWrapper').hide();
                    $('#noTasksMessage').text('No tasks assigned to you.').show();
                    alert("Error fetching all tasks.");
                }
            });
        }

        // Function to load today's tasks
        function loadTodayTasks() {
            $.ajax({
                url: "{{ route('my_tasks.today') }}",
                type: "GET",
                success: function(response) {
                    console.log("Today's Tasks Response:", response);
                    $('#noTasksMessage').hide();
                    $('#tasksTableWrapper').show();
                    if (response.success && Array.isArray(response.tasks)) {
                        if (response.tasks.length === 0) {
                            $('#tasksTableWrapper').hide();
                            $('#noTasksMessage').text('No tasks assigned to you today.').show();
                        } else {
                            initializeDataTable();
                            table.clear();
                            $.each(response.tasks, function(index, task) {
                                var badge = task.tdtask == 1
                                    ? (task.all_assigned_updated
                                        ? '<span class="badge bg-warning">Updated</span>'
                                        : '<span class="badge bg-success">Task Today</span>')
                                    : '<span class="badge bg-gray">No Tasks</span>';

                                table.row.add({
                                    "0": task.id + ' ' + badge,
                                    "1": task.title || '',
                                    "2": task.description || '',
                                    "3": task.date ? moment(task.date).format('DD MMM YYYY') : moment(task.created_at).format('DD MMM YYYY'),
                                    "4": task.status ? task.status.charAt(0).toUpperCase() + task.status.slice(1) : 'No Status',
                                    "5": task.project ? task.project.name : '',
                                    "6": task.service ? task.service.name : 'Uncategorized',
                                    "7": task.creator ? (task.creator.first_name + ' ' + (task.creator.middle_name || '') + ' ' + task.creator.last_name) : 'Unknown Creator',
                                    "8": '<button class="btn btn-sm btn-primary assignStaffBtn" data-task="' + task.id + '">Self Assign</button>' +
                                         '<button class="btn btn-warning btn-sm editTaskBtn" data-id="' + task.id + '">Edit</button>' +
                                         '<button class="btn btn-danger btn-sm deleteTaskBtn" data-id="' + task.id + '">Delete</button>' +
                                         '<button class="btn btn-sm btn-secondary viewDetailsBtn" onclick="window.location.href=\'/my-tasks/' + task.id + '/details\'">View Details</button>'
                                });
                            });
                            table.draw();
                        }
                        $('#toggleTasksBtn').text('Show All Tasks');
                        showingTodayTasks = true;
                    } else {
                        $('#tasksTableWrapper').hide();
                        $('#noTasksMessage').text('No tasks assigned to you today.').show();
                        console.error("Invalid tasks data:", response);
                        alert("No tasks found or invalid data.");
                    }
                },
                error: function(xhr) {
                    console.error("AJAX Error (Today's Tasks):", xhr.responseText);
                    $('#tasksTableWrapper').hide();
                    $('#noTasksMessage').text('No tasks assigned to you today.').show();
                    alert("Error fetching today's tasks.");
                }
            });
        }

        // Toggle between All Tasks and Today's Tasks
        $('#toggleTasksBtn').on('click', function() {
            if (showingTodayTasks) {
                loadAllTasks();
            } else {
                loadTodayTasks();
            }
        });

        // Initial load (today's tasks)
        loadTodayTasks();

        // Assign Staff button handler
        $(document).on('click', '.assignStaffBtn', function(e) {
            e.preventDefault();
            var taskId = $(this).attr('data-task');
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
                alert('Please select a frequency for assignment.');
                return;
            }
            if (frequency === 'One-time' && !$('#assignStaffForm #oneTimeFields input[name="end_date"]').val()) {
                alert('Please select an end date for One-time frequency.');
                return;
            }
            if ((frequency === 'Daily' || frequency.includes('week') || frequency.includes('Month')) && !$('#assignStaffForm #dailyWeeklyFields input[name="start_date"]').val()) {
                alert('Please select a start date.');
                return;
            }
            if (frequency.includes('week') && !$('#assignStaffForm input[name="selected_days[]"]:checked').length) {
                alert('Please select at least one day for weekly frequency.');
                return;
            }
            if (frequency.includes('Month') && !$('#assignStaffForm input[name="selected_dates[]"]:checked').length) {
                alert('Please select at least one date for monthly frequency.');
                return;
            }

            var formData = new FormData(form[0]);
            for (var pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            $.ajax({
                url: "/tasks/" + $('#assignStaffForm input[name="task_id"]').val() + "/assign-staff",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('#assignStaffBtn').blur();
                        $('#assignStaffModal').modal('hide');
                        setTimeout(() => {
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css('padding-right', '');
                            $('.modal').removeAttr('aria-hidden').removeAttr('aria-modal').css('display', 'none');
                        }, 300);
                        if (showingTodayTasks) {
                            loadTodayTasks();
                        } else {
                            loadAllTasks();
                        }
                    } else {
                        alert("Error assigning staff: " + (response.message || "Unknown error"));
                        $('#assignStaffBtn').blur();
                        $('#assignStaffModal').modal('hide');
                        setTimeout(() => {
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css('padding-right', '');
                            $('.modal').removeAttr('aria-hidden').removeAttr('aria-modal').css('display', 'none');
                        }, 300);
                    }
                },
                error: function(xhr) {
                    console.error("Assign Staff Error:", xhr.responseText);
                    alert("Error assigning staff: " + (xhr.responseJSON?.message || "Server error"));
                    $('#assignStaffBtn').blur();
                    $('#assignStaffModal').modal('hide');
                    setTimeout(() => {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('padding-right', '');
                        $('.modal').removeAttr('aria-hidden').removeAttr('aria-modal').css('display', 'none');
                    }, 300);
                }
            });
        });

        // Save Task form submission
        $(document).on('click', '#saveTaskBtn', function(e) {
            e.preventDefault();
            var form = $('#addTaskForm');
            var assignSelf = $('#assignSelf').is(':checked');
            var frequency = $('#addTaskFrequency').val();

            // Client-side validation
            if (assignSelf && !frequency) {
                alert('Please select a frequency for self-assignment.');
                return;
            }
            if (assignSelf && frequency === 'One-time' && !$('#addTaskForm #oneTimeFields input[name="end_date"]').val()) {
                alert('Please select an end date for One-time frequency.');
                return;
            }
            if (assignSelf && (frequency === 'Daily' || frequency.includes('week') || frequency.includes('Month')) && !$('#addTaskForm #dailyWeeklyFields input[name="start_date"]').val()) {
                alert('Please select a start date.');
                return;
            }
            if (assignSelf && frequency.includes('week') && !$('#addTaskForm input[name="selected_days[]"]:checked').length) {
                alert('Please select at least one day for weekly frequency.');
                return;
            }
            if (assignSelf && frequency.includes('Month') && !$('#addTaskForm input[name="selected_dates[]"]:checked').length) {
                alert('Please select at least one date for monthly frequency.');
                return;
            }

            var formData = new FormData(form[0]);
            for (var pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            $.ajax({
                url: "{{ route('my_tasks.store') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('#saveTaskBtn').blur();
                        $('#addTaskModal').modal('hide');
                        setTimeout(() => {
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css('padding-right', '');
                            $('.modal').removeAttr('aria-hidden').removeAttr('aria-modal').css('display', 'none');
                        }, 300);
                        if (showingTodayTasks) {
                            loadTodayTasks();
                        } else {
                            loadAllTasks();
                        }
                    } else {
                        alert("Error saving task: " + (response.message || "Unknown error"));
                        $('#saveTaskBtn').blur();
                        $('#addTaskModal').modal('hide');
                        setTimeout(() => {
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css('padding-right', '');
                            $('.modal').removeAttr('aria-hidden').removeAttr('aria-modal').css('display', 'none');
                        }, 300);
                    }
                },
                error: function(xhr) {
                    console.error("Error saving task:", xhr.responseText);
                    alert("Error saving task: " + (xhr.responseJSON?.message || "Server error"));
                    $('#saveTaskBtn').blur();
                    $('#addTaskModal').modal('hide');
                    setTimeout(() => {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('padding-right', '');
                        $('.modal').removeAttr('aria-hidden').removeAttr('aria-modal').css('display', 'none');
                    }, 300);
                }
            });
        });

        // Load Edit Task form via AJAX
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
                    alert("Error loading edit form.");
                }
            });
        }

        // Update Task via AJAX
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
                        alert(response.message);
                        $('#editTaskModal').modal('hide');
                        setTimeout(() => {
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css('padding-right', '');
                            $('.modal').removeAttr('aria-hidden').removeAttr('aria-modal').css('display', 'none');
                        }, 300);
                        if (showingTodayTasks) {
                            loadTodayTasks();
                        } else {
                            loadAllTasks();
                        }
                    } else {
                        alert("Error updating task: " + (response.message || "Unknown error"));
                    }
                },
                error: function(xhr) {
                    console.error("Update Task Error:", xhr.responseText);
                    alert("Error updating task.");
                }
            });
        }

        // Delete Task via AJAX
        function deleteTask(id) {
            if (confirm('Are you sure you want to delete this task?')) {
                $.ajax({
                    url: "{{ url('tasks') }}/" + id,
                    type: "DELETE",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            if (showingTodayTasks) {
                                loadTodayTasks();
                            } else {
                                loadAllTasks();
                            }
                        } else {
                            alert("Error deleting task: " + (response.message || "Unknown error"));
                        }
                    },
                    error: function(xhr) {
                        console.error("Delete Task Error:", xhr.responseText);
                        alert("Error deleting task.");
                    }
                });
            }
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

        // Frequency type handler for assignStaffForm (attached when modal is shown)
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
            // Trigger change to initialize fields if frequency is pre-selected
            $('#assignStaffFrequency').trigger('change');
        });

        // Show/hide frequency fields based on assign_self checkbox
        $(document).on('change', '#assignSelf', function() {
            if ($(this).is(':checked')) {
                $('#addTaskForm #frequencyFields').show();
            } else {
                $('#addTaskForm #frequencyFields').hide();
                $('#addTaskForm #oneTimeFields, #addTaskForm #dailyWeeklyFields, #addTaskForm #weeklyFields, #addTaskForm #monthlyFields').addClass('d-none');
            }
        });
    });
</script>
<style type="text/css">
    .dataTables_filter {
        margin-bottom: 15px;
    }
    .dataTables_info,
    .dataTables_paginate {
        padding-top: 10px;
    }
    .table.dataTable {
        margin-top: 15px;
    }
    #noTasksMessage {
        display: none;
        margin-top: 15px;
        font-size: 16px;
        color: #555;
    }
</style>
@endsection
