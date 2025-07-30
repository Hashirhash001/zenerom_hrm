@extends('layouts.app')

@section('content')
@php
    // Convert session privileges into a collection for easier access
    $taskPrivileges = collect(session('user_privileges'));
    $isAdminOrAuthorized = !in_array(Auth::user()->role_id, [2, 7]) || ($taskPrivileges->has(13) && $taskPrivileges->get(13)->can_edit);
    $sortColumn = request('sort_by', 'created_at');
    $sortDirection = request('sort_direction', 'desc');
@endphp
<div class="container px-6 py-6" style="margin-top: 70px !important;">
    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">All Tasks <span class="badge bg-success text-white px-2 py-1 rounded">{{ $tasks->total() }}</span></h1>
        </div>
        <div class="flex items-center space-x-3">
            <button id="toggleFiltersBtn" class="btn btn-secondary flex items-center space-x-2">
                <i class="fas fa-filter"></i>
                <span>Toggle Filters</span>
            </button>
            <button class="btn btn-primary flex items-center space-x-2" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                <i class="fas fa-plus mr-1"></i>
                <span>Add Task</span>
            </button>
        </div>
    </div>

    <!-- Search and Filters -->
    <div id="filtersContainer" class="bg-white shadow-md rounded-lg p-4 mb-6" style="min-height: 240px; display: none;">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            <div>
                <label for="taskSearch" class="block text-xs font-medium text-gray-700">Search Tasks</label>
                <input type="text" id="taskSearch" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2" placeholder="Search" value="{{ request('query') }}">
            </div>
            <div>
                <label for="sort_by" class="block text-xs font-medium text-gray-700">Sort By</label>
                <select id="sort_by" name="sort_by" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2">
                    <option value="id|asc" {{ $sortColumn == 'id' && $sortDirection == 'asc' ? 'selected' : '' }}>ID (Asc)</option>
                    <option value="id|desc" {{ $sortColumn == 'id' && $sortDirection == 'desc' ? 'selected' : '' }}>ID (Desc)</option>
                    <option value="title|asc" {{ $sortColumn == 'title' && $sortDirection == 'asc' ? 'selected' : '' }}>Title (A-Z)</option>
                    <option value="title|desc" {{ $sortColumn == 'title' && $sortDirection == 'desc' ? 'selected' : '' }}>Title (Z-A)</option>
                    <option value="description|asc" {{ $sortColumn == 'description' && $sortDirection == 'asc' ? 'selected' : '' }}>Description (A-Z)</option>
                    <option value="description|desc" {{ $sortColumn == 'description' && $sortDirection == 'desc' ? 'selected' : '' }}>Description (Z-A)</option>
                    <option value="deadline|asc" {{ $sortColumn == 'deadline' && $sortDirection == 'asc' ? 'selected' : '' }}>Deadline (Earliest)</option>
                    <option value="deadline|desc" {{ $sortColumn == 'deadline' && $sortDirection == 'desc' ? 'selected' : '' }}>Deadline (Latest)</option>
                    <option value="status|asc" {{ $sortColumn == 'status' && $sortDirection == 'asc' ? 'selected' : '' }}>Status (A-Z)</option>
                    <option value="status|desc" {{ $sortColumn == 'status' && $sortDirection == 'desc' ? 'selected' : '' }}>Status (Z-A)</option>
                    <option value="project_id|asc" {{ $sortColumn == 'project_id' && $sortDirection == 'asc' ? 'selected' : '' }}>Project (A-Z)</option>
                    <option value="project_id|desc" {{ $sortColumn == 'project_id' && $sortDirection == 'desc' ? 'selected' : '' }}>Project (Z-A)</option>
                    <option value="service_id|asc" {{ $sortColumn == 'service_id' && $sortDirection == 'asc' ? 'selected' : '' }}>Service (A-Z)</option>
                    <option value="service_id|desc" {{ $sortColumn == 'service_id' && $sortDirection == 'desc' ? 'selected' : '' }}>Service (Z-A)</option>
                    <option value="created_by|asc" {{ $sortColumn == 'created_by' && $sortDirection == 'asc' ? 'selected' : '' }}>Created By (A-Z)</option>
                    <option value="created_by|desc" {{ $sortColumn == 'created_by' && $sortDirection == 'desc' ? 'selected' : '' }}>Created By (Z-A)</option>
                </select>
            </div>
            <div>
                <label for="created_start" class="block text-xs font-medium text-gray-700">Created Start</label>
                <input type="date" name="created_start" id="created_start" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2" value="{{ request('created_start') }}">
            </div>
            <div>
                <label for="created_end" class="block text-xs font-medium text-gray-700">Created End</label>
                <input type="date" name="created_end" id="created_end" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2" value="{{ request('created_end') }}">
            </div>
            <div>
                <label for="deadline_start" class="block text-xs font-medium text-gray-700">Deadline Start</label>
                <input type="date" name="deadline_start" id="deadline_start" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2" value="{{ request('deadline_start') }}">
            </div>
            <div>
                <label for="deadline_end" class="block text-xs font-medium text-gray-700">Deadline End</label>
                <input type="date" name="deadline_end" id="deadline_end" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2" value="{{ request('deadline_end') }}">
            </div>
            <div>
                <label for="assigned_by" class="block text-xs font-medium text-gray-700">Created By</label>
                <select name="assigned_by" id="assigned_by" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2">
                    <option value="">All</option>
                    @foreach($staffs as $staff)
                        <option value="{{ $staff->id }}" {{ request('assigned_by') == $staff->id ? 'selected' : '' }}>
                            {{ $staff->first_name }} {{ $staff->middle_name }} {{ $staff->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="assigned_staff" class="block text-xs font-medium text-gray-700">Assigned To</label>
                <select name="assigned_staff" id="assigned_staff" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2">
                    <option value="">All</option>
                    @foreach($staffs as $staff)
                        <option value="{{ $staff->id }}" {{ request('assigned_staff') == $staff->id ? 'selected' : '' }}>
                            {{ $staff->first_name }} {{ $staff->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="project_id" class="block text-xs font-medium text-gray-700">Project</label>
                <select name="project_id" id="project_id" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2">
                    <option value="">All</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="service_id" class="block text-xs font-medium text-gray-700">Service</label>
                <select name="service_id" id="service_id" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2">
                    <option value="">All</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                            {{ $service->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-xs font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="hold" {{ request('status') == 'hold' ? 'selected' : '' }}>Hold</option>
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <a href="{{ route('tasks.index') }}" class="btn btn-secondary text-xs px-4 py-2">Clear</a>
            </div>
        </div>
    </div>

    <!-- Tasks Table Container -->
    <div id="tasksContainer" class="relative">
        <!-- Loading Overlay -->
        <div id="loadingOverlay" style="display: none;">
            <div class="spinner-border text-indigo-600" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        @if($tasks->count() > 0)
            <div class="bg-white rounded-lg shadow-md overflow-x-auto">
                @include('tasks._list', ['tasks' => $tasks])
            </div>
            <!-- Pagination -->
            <div id="paginationContainer" class="mt-4">
                @php
                    $pagination = $tasks->appends(request()->query());
                @endphp
                @include('tasks._pagination', ['pagination' => $pagination])
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs">
                No tasks found for the selected criteria.
            </div>
        @endif
    </div>

    <!-- Add Task Modal -->
    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTaskModalLabel">Add New Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
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
                        <!-- Frequency Type -->
                        <div class="mb-3">
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
                        <div class="mb-3 hidden" id="addTaskOneTimeFields">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date">
                        </div>
                        <!-- Conditional Fields for Daily or Weekly -->
                        <div class="mb-3 hidden" id="addTaskDailyWeeklyFields">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date">
                        </div>
                        <!-- Conditional Fields for Weekly Frequencies -->
                        <div class="mb-3 hidden" id="addTaskWeeklyFields">
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
                        <div class="mb-3 hidden" id="addTaskMonthlyFields">
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveTaskBtn">Save Task</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Task Modal -->
    <div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- Edit form loaded via AJAX -->
            </div>
        </div>
    </div>

    <!-- Assign Staff Modal -->
    <div class="modal fade" id="assignStaffModal" tabindex="-1" aria-labelledby="assignStaffModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
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
                                <option value="One-time" selected>One-time</option>
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
                        <div class="mb-3 hidden" id="assignStaffOneTimeFields">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" value="{{ session('task_end_date', \Carbon\Carbon::today()->format('Y-m-d')) }}" onclick="this.showPicker()">
                        </div>
                        <!-- Conditional Fields for Daily or Weekly -->
                        <div class="mb-3 hidden" id="assignStaffDailyWeeklyFields">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" value="{{ session('task_end_date', \Carbon\Carbon::today()->format('Y-m-d')) }}" onclick="this.showPicker()">
                        </div>
                        <!-- Conditional Fields for Weekly Frequencies -->
                        <div class="mb-3 hidden" id="assignStaffWeeklyFields">
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
                        <div class="mb-3 hidden" id="assignStaffMonthlyFields">
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
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://momentjs.com/downloads/moment.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>

<script>
$(document).ready(function() {
    let currentSortColumn = @json($sortColumn);
    let currentSortDirection = @json($sortDirection);

    // Initialize Bootstrap modals
    let modals = {
        addTaskModal: new bootstrap.Modal(document.getElementById('addTaskModal'), { backdrop: true }),
        assignStaffModal: new bootstrap.Modal(document.getElementById('assignStaffModal'), { backdrop: true }),
        editTaskModal: new bootstrap.Modal(document.getElementById('editTaskModal'), { backdrop: true })
    };

    // Toggle Filters
    $('#toggleFiltersBtn').on('click', function() {
        const $filtersContainer = $('#filtersContainer');
        const isVisible = $filtersContainer.is(':visible');
        if (isVisible) {
            $filtersContainer.removeClass('opacity-100 scale-y-100').addClass('opacity-0 scale-y-0');
            setTimeout(() => {
                $filtersContainer.hide();
                $('#toggleFiltersBtn span').text('Show Filters');
            }, 0); // Match duration of transition
        } else {
            $filtersContainer.show().removeClass('opacity-0 scale-y-0').addClass('opacity-100 scale-y-100');
            $('#toggleFiltersBtn span').text('Hide Filters');
        }
    });

    // Function to format date
    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        return moment(dateStr).format('DD MMM YYYY');
    }

    // Function to render task table
    function renderTaskTable(tasks) {
        let tableHtml = `
            <div class="bg-white rounded-lg shadow-md overflow-x-auto">
                <table class="table table-bordered w-full text-xs mb-0" id="tasksTable">
                    <thead class="bg-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="id">ID ${currentSortColumn === 'id' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="title">Title ${currentSortColumn === 'title' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="description">Description ${currentSortColumn === 'description' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="deadline">Deadline ${currentSortColumn === 'deadline' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="status">Status ${currentSortColumn === 'status' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="project_id">Project ${currentSortColumn === 'project_id' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="service_id">Service ${currentSortColumn === 'service_id' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="created_by">Created By ${currentSortColumn === 'created_by' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider" style="min-width: 240px; text-align: center;">Operations</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
        `;
        if (tasks.length === 0) {
            tableHtml += `
                <tr>
                    <td colspan="9" class="px-4 py-2 text-center text-gray-500 text-xs">
                        No tasks found for the selected criteria.
                    </td>
                </tr>
            `;
        } else {
            tasks.forEach(task => {
                const badge = task.tdtask == 1
                    ? (task.all_assigned_updated
                        ? '<span class="badge badge-updated px-2 py-1 rounded ms-1">Updated</span>'
                        : '<span class="badge badge-task-today px-2 py-1 rounded ms-1">Task Today</span>')
                    : '<span class="badge badge-no-tasks px-2 py-1 rounded ms-1">No Tasks</span>';
                let operations = `
                    <div class="flex flex-wrap gap-1 justify-content-center">
                        <button class="btn btn-sm btn-primary assignStaffBtn" data-id="${task.id}" title="Assign Staff"><i class="fas fa-user-plus"></i></button>
                        <button class="btn btn-sm btn-secondary viewDetailsBtn" onclick="window.location.href='/tasks/${task.id}/details'" title="View Details"><i class="fas fa-eye"></i></button>
                `;
                if (@json($isAdminOrAuthorized)) {
                    operations += `
                        <button class="btn btn-sm btn-warning editTaskBtn" data-id="${task.id}" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                        <button class="btn btn-sm btn-danger deleteTaskBtn" data-id="${task.id}" title="Delete"><i class="fas fa-trash-alt"></i></button>
                    `;
                }
                operations += `</div>`;
                tableHtml += `
                    <tr id="task_${task.id}" class="hover:bg-gray-50 transition duration-150" data-task-id="${task.id}">
                        <td class="px-4 py-2 whitespace-nowrap">${task.id} ${badge}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${task.title || 'No Title'}</td>
                        <td class="px-4 py-2" style="min-width: 300px;">${task.description || 'No description available'}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${formatDate(task.deadline)}</td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <span class="badge ${
                                task.status === 'pending' ? 'badge-pending' :
                                task.status === 'in_progress' ? 'badge-in-progress' :
                                task.status === 'completed' ? 'badge-completed' :
                                task.status === 'hold' ? 'badge-hold' : 'badge-no-tasks'
                            } px-2 py-1 rounded">${task.status ? task.status.replace('_', ' ').split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ') : 'No Status'}</span>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">${task.project ? task.project.name : 'N/A'}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${task.service ? task.service.name : 'Uncategorized'}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${task.creator ? (task.creator.first_name + ' ' + (task.creator.middle_name || '') + ' ' + task.creator.last_name) : 'Unknown'}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${operations}</td>
                    </tr>
                `;
            });
        }
        tableHtml += `
                    </tbody>
                </table>
            </div>
        `;
        return tableHtml;
    }

    // Function to render pagination links with limited page numbers
    function renderPagination(pagination) {
        let linksHtml = '<div class="mt-4 mb-4"><nav aria-label="Page navigation"><ul class="pagination flex flex-wrap justify-center gap-1">';
        const currentPage = pagination.current_page;
        const lastPage = pagination.last_page;
        const delta = 1; // Show 1 page before and after current page

        // Previous button
        if (currentPage > 1) {
            linksHtml += `<li class="page-item"><a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="#" data-page="${currentPage - 1}">Previous</a></li>`;
        } else {
            linksHtml += `<li class="page-item disabled"><a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="#">Previous</a></li>`;
        }

        // Page numbers
        const pagesToShow = [];
        pagesToShow.push(1); // Always show first page
        if (lastPage > 1) {
            // Add ellipsis if there's a gap after first page
            if (currentPage - delta > 2) {
                pagesToShow.push('...');
            }
            // Add pages around current page
            for (let i = Math.max(2, currentPage - delta); i <= Math.min(lastPage - 1, currentPage + delta); i++) {
                pagesToShow.push(i);
            }
            // Add ellipsis if there's a gap before last page
            if (currentPage + delta < lastPage - 1) {
                pagesToShow.push('...');
            }
            // Always show last page if more than one page
            pagesToShow.push(lastPage);
        }

        pagesToShow.forEach(page => {
            if (page === '...') {
                linksHtml += `<li class="page-item disabled"><span class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm">...</span></li>`;
            } else {
                linksHtml += `<li class="page-item ${page === currentPage ? 'active' : ''}"><a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="#" data-page="${page}">${page}</a></li>`;
            }
        });

        // Next button
        if (currentPage < lastPage) {
            linksHtml += `<li class="page-item"><a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="#" data-page="${currentPage + 1}">Next</a></li>`;
        } else {
            linksHtml += `<li class="page-item disabled"><a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="#">Next</a></li>`;
        }

        linksHtml += '</ul></nav></div>';
        return linksHtml;
    }

    // Function to load tasks with filters, search, sort, and pagination
    function loadTasks(page = 1) {
        const query = $('#taskSearch').val();
        const sortBy = $('#sort_by').val().split('|');
        const params = {
            query: query,
            created_start: $('#created_start').val(),
            created_end: $('#created_end').val(),
            deadline_start: $('#deadline_start').val(),
            deadline_end: $('#deadline_end').val(),
            assigned_by: $('#assigned_by').val(),
            assigned_staff: $('#assigned_staff').val(),
            project_id: $('#project_id').val(),
            service_id: $('#service_id').val(),
            status: $('#status').val(),
            sort_by: sortBy[0],
            sort_direction: sortBy[1],
            page: page,
            ajax: true
        };

        $('#loadingOverlay').show();
        $('#tasksContainer').hide();

        $.ajax({
            url: "{{ route('tasks.index') }}",
            type: "GET",
            data: params,
            success: function(response) {
                if (response.success && Array.isArray(response.tasks)) {
                    const html = renderTaskTable(response.tasks);
                    const pagination = renderPagination(response.pagination);
                    $('#tasksContainer').html(html + '<div id="paginationContainer">' + pagination + '</div>');
                    // Bind pagination click events
                    $('.page-link').on('click', function(e) {
                        e.preventDefault();
                        const page = $(this).data('page');
                        if (page) loadTasks(page);
                    });
                    // Bind sortable column clicks
                    $('.sortable').on('click', function() {
                        const column = $(this).data('column');
                        const newDirection = (currentSortColumn === column && currentSortDirection === 'asc') ? 'desc' : 'asc';
                        currentSortColumn = column;
                        currentSortDirection = newDirection;
                        $('#sort_by').val(`${column}|${newDirection}`);
                        loadTasks(1);
                    });
                } else {
                    $('#tasksContainer').html(`
                        <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs">
                            No tasks found for the selected criteria.
                        </div>
                    `);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No tasks found or invalid data.',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                        }
                    });
                }
                $('#loadingOverlay').hide();
                $('#tasksContainer').show();
            },
            error: function(xhr) {
                console.error("Task Load Error:", xhr.responseText);
                $('#tasksContainer').html(`
                    <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs">
                        No tasks found for the selected criteria.
                    </div>
                `);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error loading tasks: ' + (xhr.responseJSON?.message || 'Server error'),
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'rounded-lg',
                        confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                    }
                });
                $('#loadingOverlay').hide();
                $('#tasksContainer').show();
            }
        });
    }

    // Live search with debounce
    let searchTimer;
    $('#taskSearch').on('keyup', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadTasks(1), 300);
    });

    // Filter change handler
    $('#created_start, #created_end, #deadline_start, #deadline_end, #assigned_by, #assigned_staff, #project_id, #service_id, #status').on('change', function() {
        loadTasks(1);
    });

    // Sort change via dropdown
    $('#sort_by').on('change', function() {
        const sortBy = $(this).val().split('|');
        currentSortColumn = sortBy[0];
        currentSortDirection = sortBy[1];
        loadTasks(1);
    });

    // Frequency type handler for addTaskForm
    function updateAddTaskFrequencyFields() {
        const freq = $('#addTaskFrequency').val();
        $('#addTaskOneTimeFields, #addTaskDailyWeeklyFields, #addTaskWeeklyFields, #addTaskMonthlyFields').addClass('hidden');
        if (freq === 'One-time') {
            $('#addTaskOneTimeFields').removeClass('hidden');
        } else if (freq === 'Daily') {
            $('#addTaskDailyWeeklyFields').removeClass('hidden');
        } else if (freq.includes('week')) {
            $('#addTaskDailyWeeklyFields, #addTaskWeeklyFields').removeClass('hidden');
        } else if (freq.includes('Month')) {
            $('#addTaskDailyWeeklyFields, #addTaskMonthlyFields').removeClass('hidden');
        }
    }

    $('#addTaskModal').on('shown.bs.modal', function() {
        $('#addTaskFrequency').off('change').on('change', updateAddTaskFrequencyFields);
        updateAddTaskFrequencyFields();
    });

    // Frequency type handler for assignStaffForm
    function updateAssignStaffFrequencyFields() {
        const freq = $('#assignStaffFrequency').val();
        $('#assignStaffOneTimeFields, #assignStaffDailyWeeklyFields, #assignStaffWeeklyFields, #assignStaffMonthlyFields').addClass('hidden');
        if (freq === 'One-time') {
            $('#assignStaffOneTimeFields').removeClass('hidden');
        } else if (freq === 'Daily') {
            $('#assignStaffDailyWeeklyFields').removeClass('hidden');
        } else if (freq.includes('week')) {
            $('#assignStaffDailyWeeklyFields, #assignStaffWeeklyFields').removeClass('hidden');
        } else if (freq.includes('Month')) {
            $('#assignStaffDailyWeeklyFields, #assignStaffMonthlyFields').removeClass('hidden');
        }
    }

    $('#assignStaffModal').on('shown.bs.modal', function() {
        $('#assignStaffFrequency').off('change').on('change', updateAssignStaffFrequencyFields);
        updateAssignStaffFrequencyFields();
    });

    // Assign Staff button handler
    $(document).on('click', '.assignStaffBtn', function(e) {
        e.preventDefault();
        const taskId = $(this).data('id');
        $('#assignStaffForm input[name="task_id"]').val(taskId);
        modals.assignStaffModal.show();
    });

    // Assign Staff form submission
    $(document).on('click', '#assignStaffBtn', function(e) {
        e.preventDefault();
        $(this).prop('disabled', true);
        const form = $('#assignStaffForm');
        const frequency = $('#assignStaffFrequency').val();

        // Client-side validation
        if (!frequency) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please select a frequency for assignment.',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'rounded-lg',
                    confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                }
            });
            $(this).prop('disabled', false);
            return;
        }
        if (frequency === 'One-time' && !$('#assignStaffOneTimeFields input[name="end_date"]').val()) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please select an end date for One-time frequency.',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'rounded-lg',
                    confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                }
            });
            $(this).prop('disabled', false);
            return;
        }
        if ((frequency === 'Daily' || frequency.includes('week') || frequency.includes('Month')) && !$('#assignStaffDailyWeeklyFields input[name="start_date"]').val()) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please select a start date.',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'rounded-lg',
                    confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                }
            });
            $(this).prop('disabled', false);
            return;
        }
        if (frequency.includes('week') && !$('#assignStaffWeeklyFields input[name="selected_days[]"]:checked').length) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please select at least one day for weekly frequency.',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'rounded-lg',
                    confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                }
            });
            $(this).prop('disabled', false);
            return;
        }
        if (frequency.includes('Month') && !$('#assignStaffMonthlyFields input[name="selected_dates[]"]:checked').length) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please select at least one date for monthly frequency.',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'rounded-lg',
                    confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                }
            });
            $(this).prop('disabled', false);
            return;
        }
        if (!$('#assignStaffForm input[name="staff_ids[]"]:checked').length) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please select at least one staff member.',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'rounded-lg',
                    confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                }
            });
            $(this).prop('disabled', false);
            return;
        }

        const formData = new FormData(form[0]);
        const taskId = formData.get('task_id');
        $.ajax({
            url: `/tasks/${taskId}/assign-staff`,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                        }
                    }).then(() => {
                        modals.assignStaffModal.hide();
                        loadTasks();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error assigning staff: ' + (response.message || 'Unknown error'),
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                        }
                    });
                }
                $('#assignStaffBtn').prop('disabled', false);
                $('#loadingOverlay').hide();
                $('#tasksContainer').show();
            },
            error: function(xhr) {
                console.error("Assign Staff Error:", xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error assigning staff: ' + (xhr.responseJSON?.message || 'Server error'),
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'rounded-lg',
                        confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                    }
                });
                $('#assignStaffBtn').prop('disabled', false);
                $('#loadingOverlay').hide();
                $('#tasksContainer').show();
            }
        });
    });

    // Save new Task via Ajax
    $(document).on('click', '#saveTaskBtn', function(e) {
        e.preventDefault();
        $(this).prop('disabled', true);
        const form = $('#addTaskForm');
        const frequency = $('#addTaskFrequency').val();
        const assignStaff = $('#addTaskForm input[name="staff_ids[]"]:checked').length > 0;

        // Client-side validation
        if (assignStaff) {
            if (!frequency) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please select a frequency for assignment.',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'rounded-lg',
                        confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                    }
                });
                $(this).prop('disabled', false);
                return;
            }
            if (frequency === 'One-time' && !$('#addTaskOneTimeFields input[name="end_date"]').val()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please select an end date for One-time frequency.',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'rounded-lg',
                        confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                    }
                });
                $(this).prop('disabled', false);
                return;
            }
            if ((frequency === 'Daily' || frequency.includes('week') || frequency.includes('Month')) && !$('#addTaskDailyWeeklyFields input[name="start_date"]').val()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please select a start date.',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'rounded-lg',
                        confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                    }
                });
                $(this).prop('disabled', false);
                return;
            }
            if (frequency.includes('week') && !$('#addTaskWeeklyFields input[name="selected_days[]"]:checked').length) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please select at least one day for weekly frequency.',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'rounded-lg',
                        confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                    }
                });
                $(this).prop('disabled', false);
                return;
            }
            if (frequency.includes('Month') && !$('#addTaskMonthlyFields input[name="selected_dates[]"]:checked').length) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please select at least one date for monthly frequency.',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'rounded-lg',
                        confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                    }
                });
                $(this).prop('disabled', false);
                return;
            }
        }

        const formData = new FormData(form[0]);
        $.ajax({
            url: "{{ route('tasks.store') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                        }
                    }).then(() => {
                        modals.addTaskModal.hide();
                        loadTasks();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error saving task: ' + (response.message || 'Unknown error'),
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                        }
                    });
                }
                $('#saveTaskBtn').prop('disabled', false);
                $('#loadingOverlay').hide();
                $('#tasksContainer').show();
            },
            error: function(xhr) {
                console.error("Save Task Error:", xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error saving task: ' + (xhr.responseJSON?.message || 'Server error'),
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'rounded-lg',
                        confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                    }
                });
                $('#saveTaskBtn').prop('disabled', false);
                $('#loadingOverlay').hide();
                $('#tasksContainer').show();
            }
        });
    });

    // Load Edit Task form via Ajax
    function editTask(id) {
        $.ajax({
            url: `/tasks/${id}/edit`,
            type: "GET",
            success: function(response) {
                $('#editTaskModal .modal-content').html(response);
                modals.editTaskModal.show();
                $('#loadingOverlay').hide();
                $('#tasksContainer').show();
            },
            error: function(xhr) {
                console.error("Edit Task Error:", xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error loading edit form.',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'rounded-lg',
                        confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                    }
                });
                $('#loadingOverlay').hide();
                $('#tasksContainer').show();
            }
        });
    }

    // Update Task via Ajax
    function updateTask() {
        const formData = new FormData($('#editTaskForm')[0]);
        const taskId = $('#editTaskForm input[name="id"]').val();
        $.ajax({
            url: `/tasks/${taskId}`,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                        }
                    }).then(() => {
                        modals.editTaskModal.hide();
                        loadTasks();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error updating task: ' + (response.message || 'Unknown error'),
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                        }
                    });
                }
                $('#updateTaskBtn').prop('disabled', false);
                $('#loadingOverlay').hide();
                $('#tasksContainer').show();
            },
            error: function(xhr) {
                console.error("Update Task Error:", xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error updating task: ' + (xhr.responseJSON?.message || 'Server error'),
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'rounded-lg',
                        confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                    }
                });
                $('#updateTaskBtn').prop('disabled', false);
                $('#loadingOverlay').hide();
                $('#tasksContainer').show();
            }
        });
    }

    // Delete Task via Ajax
    function deleteTask(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'This will delete the task and all its subtasks!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel',
            customClass: {
                popup: 'rounded-lg',
                confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700',
                cancelButton: 'bg-gray-300 text-gray-800 px-3 py-1.5 rounded-md hover:bg-gray-400'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/tasks/${id}`,
                    type: "DELETE",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'rounded-lg',
                                    confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                                }
                            }).then(() => {
                                loadTasks();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error deleting task: ' + (response.message || 'Unknown error'),
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'rounded-lg',
                                    confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                                }
                            });
                        }
                        $('#loadingOverlay').hide();
                        $('#tasksContainer').show();
                    },
                    error: function(xhr) {
                        console.error("Delete Task Error:", xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error deleting task: ' + (xhr.responseJSON?.message || 'Server error'),
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'rounded-lg',
                                confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                            }
                        });
                        $('#loadingOverlay').hide();
                        $('#tasksContainer').show();
                    }
                });
            }
        });
    }

    // Bind events
    $(document).on('click', '.editTaskBtn', function(e) {
        const id = $(this).data('id');
        editTask(id);
    });

    $(document).on('click', '.deleteTaskBtn', function(e) {
        const id = $(this).data('id');
        deleteTask(id);
    });

    $(document).on('click', '#updateTaskBtn', function(e) {
        e.preventDefault();
        $(this).prop('disabled', true);
        updateTask();
    });

    // Initial load
    loadTasks();
});
</script>

<style type="text/css">
    .btn-group .btn {
        margin-right: 2px;
        padding: 4px 8px;
        border-radius: 4px;
    }
    .btn-group .btn:last-child {
        margin-right: 0;
    }
    .modal-content {
        border-radius: 0.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .modal-header, .modal-footer {
        border-color: #e5e7eb;
    }
    .btn-close {
        background-size: 1rem;
    }
    .form-control, .form-select {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem !important;
        font-size: 0.875rem;
    }
    .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
    }
    .btn:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .btn:disabled {
        cursor: not-allowed;
    }
    #tasksContainer {
        position: relative;
    }
    #loadingOverlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(255, 255, 255, 0.75);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 50;
        width: 100%;
        height: 100%;
    }
    .badge {
        display: inline-block;
        width: 100px;
        text-align: center;
        font-size: 0.75rem;
        border: none;
        line-height: 1 !important;
        font-weight: 400;
    }
    .badge-pending {
        background-color: #60a5fa;
        color: #ffffff;
    }
    .badge-in-progress {
        background-color: #fef08a;
        color: #1f2937;
    }
    .badge-completed {
        background-color: #86efac;
        color: #1f2937;
    }
    .badge-hold {
        background-color: #f87171;
        color: #ffffff;
    }
    .badge-task-today {
        background-color: #34d399;
        color: #ffffff;
    }
    .badge-updated {
        background-color: #facc15;
        color: #1f2937;
    }
    .badge-no-tasks {
        background-color: #d1d5db;
        color: #1f2937;
    }
    .pagination .page-item.active .page-link {
        background-color: #4f46e5;
        border-color: #4f46e5;
        color: #ffffff;
    }
    .pagination .page-item.disabled .page-link {
        background-color: #e5e7eb;
        border-color: #e5e7eb;
        color: #6b7280;
        cursor: not-allowed;
    }
    .pagination .page-link {
        color: #4f46e5;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        margin: 0 0.125rem;
    }
    .pagination .page-link:hover:not(.disabled) {
        background-color: #e0e7ff;
        color: #312e81;
    }
    .sortable {
        cursor: pointer;
    }
    .sortable:hover {
        background-color: #f3f4f6;
    }
    th[data-column="title"],
    th[data-column="description"],
    td:nth-child(2),
    td:nth-child(3) {
        width: 200px;
    }
    @media (max-width: 640px) {
        .pagination .page-link {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
    }
    .bg-gray-500 {
        background-color: #d1dae5 !important;
    }

    #filtersContainer {
        transition: none;
        transform-origin: top;
    }
    #filtersContainer.opacity-0 {
        opacity: 0;
        transform: scaleY(0);
    }
    #filtersContainer.opacity-100 {
        opacity: 1;
        transform: scaleY(1);
    }
    @media (max-width: 640px) {
        #filtersContainer {
            padding: 1rem;
        }
        #filtersContainer .grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        #filtersContainer .min-w-[150px] {
            min-width: 100%;
        }
        .form-control, .form-select {
            width: 100%;
        }
    }
</style>
@endsection
