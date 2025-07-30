@extends('layouts.app')

@section('content')
@php
    // Convert session privileges into a collection for easier access
    $taskPrivileges = collect(session('user_privileges'));
    $isAdminOrAuthorized = !in_array(Auth::user()->role_id, [2, 7]) || ($taskPrivileges->has(13) && $taskPrivileges->get(13)->can_edit);
@endphp
<div class="container px-6 py-6" style="margin-top: 70px !important;">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800">My Tasks <span class="badge bg-success text-white px-2 py-1 rounded">{{ $tdtaskcnt }}</span></h1>
        <div class="flex space-x-3">
            <button class="btn btn-primary flex items-center" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                <i class="fas fa-plus mr-1"></i> Add New Task
            </button>
            <button class="btn btn-secondary flex items-center" id="toggleTasksBtn">
                <i class="fas fa-list mr-1"></i> Show All Tasks
            </button>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="mb-4 bg-white p-4 rounded-lg shadow-md">
        <form id="taskFilters" class="row g-3">
            <div class="col-md-3">
                <label for="filterProject" class="block text-xs font-medium text-gray-700">Project</label>
                <select class="form-select mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-1" id="filterProject" name="project_id">
                    <option value="">All Projects</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" @if ($projectId == $project->id) selected @endif>{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="filterService" class="block text-xs font-medium text-gray-700">Service</label>
                <select class="form-select mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-1" id="filterService" name="service_id">
                    <option value="">All Services</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" @if ($serviceId == $service->id) selected @endif>{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="filterStatus" class="block text-xs font-medium text-gray-700">Status</label>
                <select class="form-select mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-1" id="filterStatus" name="status">
                    <option value="all" @if ($status == 'all' || !$status) selected @endif>All Statuses</option>
                    <option value="pending" @if ($status == 'pending') selected @endif>Pending</option>
                    <option value="in_progress" @if ($status == 'in_progress') selected @endif>In Progress</option>
                    <option value="completed" @if ($status == 'completed') selected @endif>Completed</option>
                    <option value="hold" @if ($status == 'hold') selected @endif>Hold</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filterSearch" class="block text-xs font-medium text-gray-700">Search</label>
                <input type="text" class="form-control mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-1" id="filterSearch" name="search" value="{{ $search ?? '' }}" placeholder="Search tasks...">
            </div>
            {{-- <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="w-full bg-indigo-600 text-white py-1 px-2 rounded-md hover:bg-indigo-700 transition duration-300 ease-in-out transform hover:-translate-y-1">
                    <i class="fas fa-filter w-4 h-4 inline-block mr-1"></i>
                </button>
            </div> --}}
        </form>
    </div>

    <!-- Tasks List Section -->
    <div id="tasksTableContainer" style="display: none;">
        <!-- Loading Overlay -->
        <div id="loadingOverlay" style="display: flex;">
            <div class="spinner-border text-indigo-600" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        @if($tasks->count() > 0)
        <div class="bg-white rounded-lg shadow-md overflow-x-auto">
            <table class="table table-bordered w-full text-xs" id="tasksTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="id">ID</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="title">Title</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="description">Description</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="deadline">Deadline</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="status">Status</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="project_id">Project</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="service_id">Service</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="created_by">Created by</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider" style="min-width: 240px; text-align: center;">Operations</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($tasks as $task)
                        <tr id="task_{{ $task->id }}" class="hover:bg-gray-50 transition duration-150" data-task-id="{{ $task->id }}">
                            <td class="px-4 py-2 whitespace-nowrap">
                                {{ $task->id }}
                                @if ($task->tdtask == 1)
                                    @if ($task->all_assigned_updated)
                                        <span class="badge bg-yellow-400 text-white px-2 py-1 rounded ms-1">Updated</span>
                                    @else
                                        <span class="badge bg-success text-white px-2 py-1 rounded ms-1">Task Today</span>
                                    @endif
                                @else
                                    <span class="badge bg-gray-400 text-white px-2 py-1 rounded ms-1">No Tasks</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $task->title }}</td>
                            <td class="px-4 py-2" style="min-width: 300px;">{{ $task->description }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                @if ($task->deadline)
                                    {{ \Carbon\Carbon::parse($task->deadline)->format('d M Y') }}
                                @else
                                    {{ 'N/A' }}
                                @endif
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap status">
                                <span class="badge
                                    @if ($task->status == 'pending') bg-blue-500
                                    @elseif ($task->status == 'in_progress') bg-yellow-500
                                    @elseif ($task->status == 'completed') bg-green-500
                                    @elseif ($task->status == 'hold') bg-red-500
                                    @endif text-white px-2 py-1 rounded">
                                    {{ str_replace('_', ' ', ucfirst($task->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ optional($task->project)->name }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $task->service ? $task->service->name : 'Uncategorized' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ optional($task->creator)->first_name }} {{ optional($task->creator)->middle_name }} {{ optional($task->creator)->last_name }}</td>
                            <td class="px-4 py-2 whitespace-nowrap" style="min-width: 240px">
                                <div class="flex flex-wrap gap-1 justify-content-center">
                                    <button class="btn btn-sm btn-primary assignStaffBtn" data-task="{{ $task->id }}" title="Self Assign">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                    @if ($isAdminOrAuthorized)
                                        <button class="btn btn-sm btn-warning editTaskBtn" data-id="{{ $task->id }}" title="Edit">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger deleteTaskBtn" data-id="{{ $task->id }}" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-info changeStatusBtn" data-id="{{ $task->id }}" data-bs-toggle="modal" data-bs-target="#changeStatusModal" title="Change Status">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    @endif
                                    <button class="btn btn-sm btn-secondary viewDetailsBtn" onclick="window.location.href='{{ route('my-tasks.details', $task->id) }}'" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <!-- Pagination Links -->
            <div class="mt-4">
                {{ $tasks->appends(['project_id' => $projectId, 'service_id' => $serviceId, 'status' => $status, 'search' => $search, 'sort_column' => $sortColumn, 'sort_direction' => $sortDirection])->links() }}
            </div>
        </div>
        @else
        <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs" style="margin-top: 0;">
            No tasks assigned to you for the selected criteria.
        </div>
        @endif
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

    <!-- Modal for Changing Task Status -->
    <div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-lg font-semibold text-gray-800" id="changeStatusModalLabel">Change Task Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="changeStatusForm">
                        @csrf
                        <input type="hidden" name="task_id" id="changeStatusTaskId">
                        <div class="mb-3">
                            <label for="taskStatus" class="form-label font-medium text-gray-600">Status</label>
                            <select class="form-control form-select" id="taskStatus" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="hold">Hold</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="updateStatusBtn">Update Status</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery (CDN with local fallback) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>window.jQuery || document.write('<script src="{{ asset('assets1/jquery.min.js') }}"><\/script>')</script>
<!-- Moment.js -->
<script src="https://momentjs.com/downloads/moment.js"></script>
<!-- DataTables CSS (CDN) -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
<!-- DataTables JS (CDN) -->
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Bootstrap JS (for modal compatibility) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
<!-- Font Awesome for modern icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<!-- Tailwind CSS CDN for modern styling -->
<script src="https://cdn.tailwindcss.com"></script>

<script>
    let isAdminOrAuthorized = @json($isAdminOrAuthorized);
    let table;
    let showingTodayTasks = true;
    let currentSortColumn = @json($sortColumn ?? 'created_at');
    let currentSortDirection = @json($sortDirection ?? 'desc');

    // Function to format date
    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        return moment(dateStr).format('DD MMM YYYY');
    }

    $(document).ready(function() {
        // Initialize Bootstrap modals
        let modals = {
            addTaskModal: new bootstrap.Modal(document.getElementById('addTaskModal'), { backdrop: true }),
            assignStaffModal: new bootstrap.Modal(document.getElementById('assignStaffModal'), { backdrop: true }),
            editTaskModal: new bootstrap.Modal(document.getElementById('editTaskModal'), { backdrop: true }),
            changeStatusModal: new bootstrap.Modal(document.getElementById('changeStatusModal'), { backdrop: true })
        };

        // Initialize Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function(tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize DataTable
        function initializeDataTable() {
            if ($.fn.DataTable.isDataTable('#tasksTable')) {
                $('#tasksTable').DataTable().destroy();
            }
            table = $('#tasksTable').DataTable({
                dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                paging: false, // Disable client-side pagination
                searching: false, // Disable client-side searching
                ordering: false, // Disable client-side sorting
                info: false,
                autoWidth: false,
                responsive: false,
                language: {
                    emptyTable: "No tasks assigned to you for the selected criteria."
                },
                initComplete: function() {
                    // Ensure loader is hidden after DataTable initialization
                    $('#loadingOverlay').hide();
                    $('#tasksTableContainer').show();
                }
            });
        }

        @if($tasks->count())
            initializeDataTable();
        @else
            $('#tasksTableContainer').show();
            $('#loadingOverlay').hide();
        @endif

        // Reset modal content and reinitialize modals on close
        function resetModal(modalId, modalInstance) {
            const modalElement = document.getElementById(modalId);
            if (modalId === 'editTaskModal') {
                $(`#${modalId} .modal-content`).empty();
            } else {
                $(`#${modalId} form`).trigger('reset');
            }
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
            if (modalElement) {
                modals[modalId] = new bootstrap.Modal(modalElement, { backdrop: true });
            }
        }

        $('#addTaskModal, #assignStaffModal, #editTaskModal, #changeStatusModal').on('hidden.bs.modal', function() {
            const modalId = $(this).attr('id');
            resetModal(modalId, modals[modalId]);
        });

        // Function to get filter parameters
        function getFilterParams() {
            return {
                project_id: $('#filterProject').val(),
                service_id: $('#filterService').val(),
                status: $('#filterStatus').val(),
                search: $('#filterSearch').val(),
                length: $('#tasksTable_length select').val() || 10,
                sort_column: currentSortColumn,
                sort_direction: currentSortDirection
            };
        }

        // Function to render table rows
        function renderTableRows(tasks) {
            let tableHtml = `
                <div class="bg-white rounded-lg shadow-md overflow-x-auto">
                    <table class="table table-bordered w-full text-xs" id="tasksTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="id">ID ${currentSortColumn === 'id' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="title">Title ${currentSortColumn === 'title' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="description">Description ${currentSortColumn === 'description' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="deadline">Deadline ${currentSortColumn === 'deadline' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="status">Status ${currentSortColumn === 'status' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="project_id">Project ${currentSortColumn === 'project_id' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="service_id">Service ${currentSortColumn === 'service_id' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="created_by">Created by ${currentSortColumn === 'created_by' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider" style="min-width: 240px; text-align: center;">Operations</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
            `;
            tasks.forEach(task => {
                const badge = task.tdtask == 1
                    ? (task.all_assigned_updated
                        ? '<span class="badge badge-updated px-2 py-1 rounded ms-1">Updated</span>'
                        : '<span class="badge badge-task-today px-2 py-1 rounded ms-1">Task Today</span>')
                    : '<span class="badge badge-no-tasks px-2 py-1 rounded ms-1">No Tasks</span>';
                let operations = `
                    <div class="flex flex-wrap gap-1 justify-content-center">
                        <button class="btn btn-sm btn-primary assignStaffBtn" data-task="${task.id}" title="Self Assign"><i class="fas fa-user-plus"></i></button>
                `;
                if (isAdminOrAuthorized) {
                    operations += `
                        <button class="btn btn-sm btn-warning editTaskBtn" data-id="${task.id}" title="Edit"><i class="fas fa-pencil-alt"></i></button>
                        <button class="btn btn-sm btn-danger deleteTaskBtn" data-id="${task.id}" title="Delete"><i class="fas fa-trash-alt"></i></button>
                    `;
                } else {
                    operations += `
                        <button class="btn btn-sm btn-info changeStatusBtn" data-id="${task.id}" data-bs-toggle="modal" data-bs-target="#changeStatusModal" title="Change Status"><i class="fas fa-sync-alt"></i></button>
                    `;
                }
                operations += `
                    <button class="btn btn-sm btn-secondary viewDetailsBtn" onclick="window.location.href='/my-tasks/${task.id}/details'" title="View Details"><i class="fas fa-eye"></i></button>
                    </div>
                `;
                tableHtml += `
                    <tr id="task_${task.id}" class="hover:bg-gray-50 transition duration-150" data-task-id="${task.id}">
                        <td class="px-4 py-2 whitespace-nowrap">${task.id} ${badge}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${task.title || ''}</td>
                        <td class="px-4 py-2" style="min-width: 300px;">${task.description || ''}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${formatDate(task.deadline)}</td>
                        <td class="px-4 py-2 whitespace-nowrap status">
                            <span class="badge ${
                                task.status === 'pending' ? 'badge-pending' :
                                task.status === 'in_progress' ? 'badge-in-progress' :
                                task.status === 'completed' ? 'badge-completed' :
                                task.status === 'hold' ? 'badge-hold' : 'badge-no-tasks'
                            } px-2 py-1 rounded">${task.status ? task.status.replace('_', ' ').split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ') : 'No Status'}</span>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">${task.project ? task.project.name : ''}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${task.service ? task.service.name : 'Uncategorized'}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${task.creator ? (task.creator.first_name + ' ' + (task.creator.middle_name || '') + ' ' + task.creator.last_name) : 'Unknown Creator'}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${operations}</td>
                    </tr>
                `;
            });
            tableHtml += `
                        </tbody>
                    </table>
                </div>
            `;
            return tableHtml;
        }

        // Function to render pagination links
        function renderPagination(pagination) {
            let linksHtml = '<div class="mt-4"><nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
            if (pagination.current_page > 1) {
                linksHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a></li>`;
            } else {
                linksHtml += `<li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>`;
            }
            for (let i = 1; i <= pagination.last_page; i++) {
                linksHtml += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            }
            if (pagination.current_page < pagination.last_page) {
                linksHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next</a></li>`;
            } else {
                linksHtml += `<li class="page-item disabled"><a class="page-link" href="#">Next</a></li>`;
            }
            linksHtml += '</ul></nav></div>';
            return linksHtml;
        }

        // Function to load all tasks
        function loadAllTasks(page = 1) {
            let params = getFilterParams();
            params.page = page;
            params.ajax = true;
            $('#loadingOverlay').show();
            $('#tasksTableContainer').hide();
            $.ajax({
                url: "{{ route('my_tasks.index') }}",
                type: "GET",
                data: params,
                success: function(response) {
                    console.log("All Tasks Response:", response);
                    if (response.success && Array.isArray(response.tasks)) {
                        isAdminOrAuthorized = response.isAdminOrAuthorized || false;
                        if (response.tasks.length === 0) {
                            $('#tasksTableContainer').html(`
                                <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs" style="margin-top: 0;">
                                    No tasks assigned to you for the selected criteria.
                                </div>
                            `);
                            table?.destroy();
                            table = null;
                        } else {
                            let tableHtml = renderTableRows(response.tasks);
                            tableHtml += renderPagination(response.pagination);
                            $('#tasksTableContainer').html(tableHtml);
                            initializeDataTable();
                            // Bind pagination click events
                            $('.page-link').on('click', function(e) {
                                e.preventDefault();
                                let page = $(this).data('page');
                                if (page) loadAllTasks(page);
                            });
                        }
                        $('#toggleTasksBtn').html('<i class="fas fa-list mr-1"></i> Show Today\'s Tasks');
                        showingTodayTasks = false;
                    } else {
                        $('#tasksTableContainer').html(`
                            <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs" style="margin-top: 0;">
                                No tasks assigned to you for the selected criteria.
                            </div>
                        `);
                        table?.destroy();
                        table = null;
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
                    $('#tasksTableContainer').show();
                },
                error: function(xhr) {
                    console.error("AJAX Error (All Tasks):", xhr.responseText);
                    $('#tasksTableContainer').html(`
                        <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs" style="margin-top: 0;">
                            No tasks assigned to you for the selected criteria.
                        </div>
                    `);
                    table?.destroy();
                    table = null;
                    $('#loadingOverlay').hide();
                    $('#tasksTableContainer').show();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error fetching all tasks: ' + (xhr.responseJSON?.message || 'Server error'),
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                        }
                    });
                }
            });
        }

        // Function to load today's tasks
        function loadTodayTasks(page = 1) {
            let params = getFilterParams();
            params.page = page;
            $('#loadingOverlay').show();
            $('#tasksTableContainer').hide();
            $.ajax({
                url: "{{ route('my_tasks.today') }}",
                type: "GET",
                data: params,
                success: function(response) {
                    console.log("Today's Tasks Response:", response);
                    if (response.success && Array.isArray(response.tasks)) {
                        if (response.tasks.length === 0) {
                            $('#tasksTableContainer').html(`
                                <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs" style="margin-top: 0;">
                                    No tasks assigned to you for the selected criteria.
                                </div>
                            `);
                            table?.destroy();
                            table = null;
                        } else {
                            let tableHtml = renderTableRows(response.tasks);
                            tableHtml += renderPagination(response.pagination);
                            $('#tasksTableContainer').html(tableHtml);
                            initializeDataTable();
                            // Bind pagination click events
                            $('.page-link').on('click', function(e) {
                                e.preventDefault();
                                let page = $(this).data('page');
                                if (page) loadTodayTasks(page);
                            });
                        }
                        $('#toggleTasksBtn').html('<i class="fas fa-list mr-1"></i> Show All Tasks');
                        showingTodayTasks = true;
                    } else {
                        $('#tasksTableContainer').html(`
                            <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs" style="margin-top: 0;">
                                No tasks assigned to you for the selected criteria.
                            </div>
                        `);
                        table?.destroy();
                        table = null;
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
                    $('#tasksTableContainer').show();
                },
                error: function(xhr) {
                    console.error("AJAX Error (Today's Tasks):", xhr.responseText);
                    $('#tasksTableContainer').html(`
                        <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs" style="margin-top: 0;">
                            No tasks assigned to you for the selected criteria.
                        </div>
                    `);
                    table?.destroy();
                    table = null;
                    $('#loadingOverlay').hide();
                    $('#tasksTableContainer').show();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error fetching today\'s tasks: ' + (xhr.responseJSON?.message || 'Server error'),
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                        }
                    });
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

        // Filter form submission
        $('#taskFilters').on('submit', function(e) {
            e.preventDefault();
            if (showingTodayTasks) {
                loadTodayTasks();
            } else {
                loadAllTasks();
            }
        });

        // Filter change handler
        $('#filterProject, #filterService, #filterStatus, #filterSearch').on('change keyup', function() {
            if (showingTodayTasks) {
                loadTodayTasks();
            } else {
                loadAllTasks();
            }
        });

        // Sorting handler
        $(document).on('click', '.sortable', function() {
            const column = $(this).data('column');
            if (currentSortColumn === column) {
                currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                currentSortColumn = column;
                currentSortDirection = 'asc';
            }
            if (showingTodayTasks) {
                loadTodayTasks();
            } else {
                loadAllTasks();
            }
        });

        // Initial load (today's tasks)
        loadTodayTasks();

        // Assign Staff button handler
        $(document).on('click', '.assignStaffBtn', function(e) {
            e.preventDefault();
            var taskId = $(this).attr('data-task');
            $('#assignStaffForm input[name="task_id"]').val(taskId);
            modals.assignStaffModal.show();
        });

        // Assign Staff form submission
        $(document).on('click', '#assignStaffBtn', function(e) {
            e.preventDefault();
            $(this).prop('disabled', true);
            var form = $('#assignStaffForm');
            var frequency = $('#assignStaffFrequency').val();

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
            if (frequency === 'One-time' && !$('#assignStaffForm #oneTimeFields input[name="end_date"]').val()) {
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
            if ((frequency === 'Daily' || frequency.includes('week') || frequency.includes('Month')) && !$('#assignStaffForm #dailyWeeklyFields input[name="start_date"]').val()) {
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
            if (frequency.includes('week') && !$('#assignStaffForm input[name="selected_days[]"]:checked').length) {
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
            if (frequency.includes('Month') && !$('#assignStaffForm input[name="selected_dates[]"]:checked').length) {
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

            var formData = new FormData(form[0]);
            $.ajax({
                url: "/tasks/" + $('#assignStaffForm input[name="task_id"]').val() + "/assign-staff",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Staff assigned successfully.',
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'rounded-lg',
                                confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                            }
                        });
                        modals.assignStaffModal.hide();
                        if (showingTodayTasks) {
                            loadTodayTasks();
                        } else {
                            loadAllTasks();
                        }
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
                    $('#tasksTableContainer').show();
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
                        $('#tasksTableContainer').show();
                    }
                });
            });

            // Save Task form submission
            $(document).on('click', '#saveTaskBtn', function(e) {
                e.preventDefault();
                $(this).prop('disabled', true);
                var form = $('#addTaskForm');
                var assignSelf = $('#assignSelf').is(':checked');
                var frequency = $('#addTaskFrequency').val();

                if (assignSelf && !frequency) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Please select a frequency for self-assignment.',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                        }
                    });
                    $(this).prop('disabled', false);
                    return;
                }
                if (assignSelf && frequency === 'One-time' && !$('#addTaskForm #oneTimeFields input[name="end_date"]').val()) {
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
                if (assignSelf && (frequency === 'Daily' || frequency.includes('week') || frequency.includes('Month')) && !$('#addTaskForm #dailyWeeklyFields input[name="start_date"]').val()) {
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
                if (assignSelf && frequency.includes('week') && !$('#addTaskForm input[name="selected_days[]"]:checked').length) {
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
                if (assignSelf && frequency.includes('Month') && !$('#addTaskForm input[name="selected_dates[]"]:checked').length) {
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

                var formData = new FormData(form[0]);
                $.ajax({
                    url: "{{ route('my_tasks.store') }}",
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
                            });
                            modals.addTaskModal.hide();
                            if (showingTodayTasks) {
                                loadTodayTasks();
                            } else {
                                loadAllTasks();
                            }
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
                        $('#tasksTableContainer').show();
                    },
                    error: function(xhr) {
                        console.error("Error saving task:", xhr.responseText);
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
                        $('#tasksTableContainer').show();
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
                        modals.editTaskModal.show();
                        $('#loadingOverlay').hide();
                        $('#tasksTableContainer').show();
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
                        $('#tasksTableContainer').show();
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
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'rounded-lg',
                                    confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                            }
                        });
                        modals.editTaskModal.hide();
                        if (showingTodayTasks) {
                            loadTodayTasks();
                        } else {
                            loadAllTasks();
                        }
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
                    $('#tasksTableContainer').show();
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
                    $('#tasksTableContainer').show();
                }
            });
        }

        // Delete Task via AJAX
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
                        url: "{{ url('tasks') }}/" + id,
                        type: "DELETE",
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: response.message,
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        popup: 'rounded-lg',
                                        confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                                    }
                                });
                                if (showingTodayTasks) {
                                    loadTodayTasks();
                                } else {
                                    loadAllTasks();
                                }
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
                            $('#tasksTableContainer').show();
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
                            $('#tasksTableContainer').show();
                        }
                    });
                }
            });
        }

        // Change Task Status
        $(document).on('click', '.changeStatusBtn', function(e) {
            e.preventDefault();
            var taskId = $(this).data('id');
            $('#changeStatusForm input[name="task_id"]').val(taskId);
            modals.changeStatusModal.show();
        });

        // Update Status form submission
        $(document).on('click', '#updateStatusBtn', function(e) {
            e.preventDefault();
            $(this).prop('disabled', true);
            var form = $('#changeStatusForm');
            var formData = new FormData(form[0]);
            var taskId = $('#changeStatusForm input[name="task_id"]').val();

            $.ajax({
                url: "{{ url('my-tasks') }}/" + taskId + "/status",
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
                        });
                        modals.changeStatusModal.hide();
                        if (showingTodayTasks) {
                            loadTodayTasks();
                        } else {
                            loadAllTasks();
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error updating task status: ' + (response.message || 'Unknown error'),
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'rounded-lg',
                                confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                            }
                        });
                    }
                    $('#updateStatusBtn').prop('disabled', false);
                    $('#loadingOverlay').hide();
                    $('#tasksTableContainer').show();
                },
                error: function(xhr) {
                    console.error("Update Status Error:", xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error updating task status: ' + (xhr.responseJSON?.message || 'Server error'),
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                        }
                    });
                    $('#updateStatusBtn').prop('disabled', false);
                    $('#loadingOverlay').hide();
                    $('#tasksTableContainer').show();
                }
            });
        });

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
            $(this).prop('disabled', true);
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
            $('#assignStaffFrequency').trigger('change');
        });

        // Trigger frequency change on modal show to set initial state
        $('#addTaskModal').on('shown.bs.modal', function() {
            $('#addTaskFrequency').trigger('change');
        });
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
        padding: 0.5rem 0.75rem;
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
    .min-w-\[160px\] {
        min-width: 160px;
    }

    .badge {
        line-height: 1 !important;
        font-weight: 400;
    }

    .form-control, .dual-listbox .dual-listbox__search, div.dataTables_wrapper div.dataTables_filter input
    {
        min-height: auto;
    }

    #tasksTableContainer {
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

    /* Badge styling for consistent width and lighter colors */
    .badge {
        display: inline-block;
        width: 100px; /* Fixed width for all badges */
        text-align: center;
        font-size: 0.75rem; /* Ensure text fits within fixed width */
        border: none;
    }

    /* Lighter badge colors */
    .badge-pending {
        background-color: #60a5fa; /* Light blue */
        color: #ffffff;
    }

    .badge-in-progress {
        background-color: #fef08a; /* Light yellow */
        color: #1f2937; /* Dark text for contrast */
    }

    .badge-completed {
        background-color: #86efac; /* Light green */
        color: #1f2937; /* Dark text for contrast */
    }

    .badge-hold {
        background-color: #f87171; /* Light red */
        color: #ffffff;
    }

    .badge-task-today {
        background-color: #34d399; /* Light green for Task Today */
        color: #ffffff;
    }

    .badge-updated {
        background-color: #facc15; /* Light yellow for Updated */
        color: #1f2937; /* Dark text for contrast */
    }

    .badge-no-tasks {
        background-color: #d1d5db; /* Light gray */
        color: #1f2937; /* Dark text for contrast */
    }

    /* Pointer cursor on sortable headers */
    th.sortable {
        cursor: pointer;
    }

    /* Equal width for Title and Description columns */
    th[data-column="title"],
    th[data-column="description"],
    td:nth-child(2), /* Title column */
    td:nth-child(3) /* Description column */ {
        width: 200px; /* Adjust as needed for your layout */
    }

    .bg-gray-500 {
        background-color: #d1dae5 !important;
    }
</style>
@endsection

@push('modals')
    @include('my_tasks.create_modal')
    @include('my_tasks.assign_staff_modal')
@endpush
