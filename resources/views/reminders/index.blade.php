@extends('layouts.app')

@section('content')
@php
    $sortColumn = request('sort_by', 'id');
    $sortDirection = request('sort_direction', 'asc');
    $search = request('search', '');
    $project_id = request('project_id', '');
    $service_ids = request('service_ids', []);
    $frequency = request('frequency', '');

    function formatProject($project) {
        return $project ? $project->name : 'None';
    }

    function formatServices($services) {
        if (!$services || $services->isEmpty()) return 'N/A';
        return $services->pluck('name')->implode(', ');
    }

    function formatDaysOfWeek($days) {
        if (!is_array($days) || empty($days)) return 'N/A';
        $daysMap = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
        return implode(', ', array_map(fn($day) => $daysMap[$day] ?? $day, $days));
    }

    function formatDate($date) {
        return $date ? \Carbon\Carbon::parse($date)->format('d M Y') : 'N/A';
    }
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

    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Reminders <span class="badge bg-success text-white px-2 py-1 rounded">{{ $reminders->count() }}</span></h1>
        </div>
        <div class="flex items-center space-x-3">
            <button id="toggleFiltersBtn" class="btn btn-secondary flex items-center space-x-2">
                <i class="fas fa-filter"></i>
                <span>Toggle Filters</span>
            </button>
            <button class="btn btn-primary flex items-center space-x-2" data-bs-toggle="modal" data-bs-target="#addReminderModal">
                <i class="fas fa-plus mr-1"></i>
                <span>Add Reminder</span>
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div id="filtersContainer" class="bg-white shadow-md rounded-lg p-4 mb-6 flex items-center space-x-4" style="min-height: 100px; display: none;">
        <div class="grid grid-cols-1 sm:grid-cols-4 flex-1">
            <div class="mr-3">
                <label for="projectFilter" class="block text-xs font-medium text-gray-700">Project</label>
                <select name="project_id" id="projectFilter" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2 select2">
                    <option value="">All Projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ $project->id == $project_id ? 'selected' : '' }}>{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mr-3">
                <label for="servicesFilter" class="block text-xs font-medium text-gray-700">Services</label>
                <select name="service_ids[]" id="servicesFilter" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2 select2" multiple>
                    <option value="">All Services</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ in_array($service->id, $service_ids) ? 'selected' : '' }}>{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mr-3">
                <label for="frequencyFilter" class="block text-xs font-medium text-gray-700">Frequency</label>
                <select name="frequency" id="frequencyFilter" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2">
                    <option value="">All Frequencies</option>
                    <option value="daily" {{ $frequency == 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ $frequency == 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ $frequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="one_time" {{ $frequency == 'one_time' ? 'selected' : '' }}>One Time</option>
                </select>
            </div>
            <div>
                <label for="searchFilter" class="block text-xs font-medium text-gray-700">Search by Title</label>
                <input type="text" name="search" id="searchFilter" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2" value="{{ $search }}" placeholder="Enter reminder title">
            </div>
        </div>
        <div class="flex items-center mt-2">
            <a href="{{ route('reminders.index') }}" class="text-gray-500 hover:text-gray-700" title="Clear Filters">
                <i class="fas fa-times-circle text-lg"></i>
            </a>
        </div>
    </div>

    <!-- Reminders Table Container -->
    <div id="remindersContainer" class="relative">
        <!-- Loading Overlay -->
        <div id="loadingOverlay" style="display: none;">
            <div class="spinner-border text-indigo-600" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        @if($reminders->count() > 0)
            <div class="bg-white rounded-lg shadow-md overflow-x-auto">
                <table class="table table-bordered w-full text-xs mb-0" id="remindersTable">
                    <thead class="bg-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="id">ID {{ $sortColumn == 'id' ? ($sortDirection == 'asc' ? '↑' : '↓') : '' }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="title">Title {{ $sortColumn == 'title' ? ($sortDirection == 'asc' ? '↑' : '↓') : '' }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider">Project</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider">Services</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="frequency">Frequency {{ $sortColumn == 'frequency' ? ($sortDirection == 'asc' ? '↑' : '↓') : '' }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider" style="min-width: 100px; text-align: center;">Operations</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($reminders as $reminder)
                            <tr id="reminder_{{ $reminder->id }}" class="hover:bg-gray-50 transition duration-150" data-reminder-id="{{ $reminder->id }}">
                                <td class="px-4 py-2 whitespace-nowrap">{{ $reminder->id }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $reminder->title }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ formatProject($reminder->project) }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ formatServices($reminder->services) }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ ucfirst($reminder->frequency) }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $reminder->time_of_day }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $reminder->active ? 'Active' : 'Inactive' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1 justify-content-center">
                                        <button class="btn btn-sm btn-primary editReminderBtn" data-bs-toggle="modal" data-bs-target="#editReminderModal" data-id="{{ $reminder->id }}" title="Edit"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger deleteReminderBtn" data-id="{{ $reminder->id }}" title="Delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div id="paginationContainer" class="mt-4">
                @php
                    $pagination = $reminders->appends(request()->query());
                @endphp
                @include('reminders._pagination', ['pagination' => $pagination])
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs">
                No reminders found for the selected filters.
            </div>
        @endif
    </div>

    <!-- Add Reminder Modal -->
    <div class="modal fade" id="addReminderModal" tabindex="-1" aria-labelledby="addReminderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-lg">
                @include('reminders.create_modal')
            </div>
        </div>
    </div>

    <!-- Edit Reminder Modal -->
    <div class="modal fade" id="editReminderModal" tabindex="-1" aria-labelledby="editReminderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-lg">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // Set up CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {
        let currentSortColumn = @json($sortColumn);
        let currentSortDirection = @json($sortDirection);

        // Initialize Bootstrap modals
        let modals = {
            addReminderModal: new bootstrap.Modal(document.getElementById('addReminderModal'), { backdrop: true }),
            editReminderModal: new bootstrap.Modal(document.getElementById('editReminderModal'), { backdrop: true })
        };

        // Initialize Select2 for filters and modals
        function initializeSelect2(modalId) {
            $(`#${modalId} .select2`).select2({
                placeholder: 'Select an option',
                allowClear: true,
                width: '100%',
                dropdownCssClass: 'select2-no-overflow'
            });
        }

        // Initialize Select2 for filters
        $('#projectFilter, #servicesFilter').select2({
            placeholder: 'Select an option',
            allowClear: true,
            width: '100%',
            dropdownCssClass: 'select2-no-overflow'
        });

        // Initialize frequency change handler for both modals
        function handleFrequencyChange(modalId) {
            const $frequency = $(`#${modalId} #frequency`);
            $frequency.off('change.reminderApp').on('change.reminderApp', function() {
                const frequency = $(this).val();
                $(`#${modalId} #days_of_week_field`).hide();
                $(`#${modalId} #day_of_month_field`).hide();
                $(`#${modalId} #specific_date_field`).hide();
                $(`#${modalId} #day_of_month`).prop('required', false);
                $(`#${modalId} #specific_date`).prop('required', false);

                if (frequency === 'weekly') {
                    $(`#${modalId} #days_of_week_field`).show();
                } else if (frequency === 'monthly') {
                    $(`#${modalId} #day_of_month_field`).show();
                    $(`#${modalId} #day_of_month`).prop('required', true);
                } else if (frequency === 'one_time') {
                    $(`#${modalId} #specific_date_field`).show();
                    $(`#${modalId} #specific_date`).prop('required', true);
                }
            }).trigger('change');
        }

        // Initialize for add modal
        initializeSelect2('addReminderModal');
        handleFrequencyChange('addReminderModal');

        // Toggle Filters
        $('#toggleFiltersBtn').on('click.reminderApp', function() {
            const $filtersContainer = $('#filtersContainer');
            const isVisible = $filtersContainer.is(':visible');
            if (isVisible) {
                $filtersContainer.removeClass('opacity-100 scale-y-100').addClass('opacity-0 scale-y-0');
                setTimeout(() => {
                    $filtersContainer.hide();
                    $('#toggleFiltersBtn span').text('Show Filters');
                }, 300);
            } else {
                $filtersContainer.show().removeClass('opacity-0 scale-y-0').addClass('opacity-100 scale-y-100');
                $('#toggleFiltersBtn span').text('Hide Filters');
            }
        });

        // Function to format reminder data
        function formatProject(project) {
            return project ? project.name : 'None';
        }

        function formatServices(services) {
            if (!services || services.length === 0) return 'N/A';
            return services.map(service => service.name).join(', ');
        }

        function formatDaysOfWeek(days) {
            if (!Array.isArray(days) || days.length === 0) return 'N/A';
            const daysMap = {1: 'Mon', 2: 'Tue', 3: 'Wed', 4: 'Thu', 5: 'Fri', 6: 'Sat', 7: 'Sun'};
            return days.map(day => daysMap[day] || day).join(', ');
        }

        function formatDate(date) {
            return date ? moment(date).format('DD MMM YYYY') : 'N/A';
        }

        // Function to update a single reminder row
        function updateReminderRow(reminder) {
            const operations = `
                <div class="flex flex-wrap gap-1 justify-content-center">
                    <button class="btn btn-sm btn-primary editReminderBtn" data-bs-toggle="modal" data-bs-target="#editReminderModal" data-id="${reminder.id}" title="Edit"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-danger deleteReminderBtn" data-id="${reminder.id}" title="Delete"><i class="fas fa-trash"></i></button>
                </div>
            `;
            const rowHtml = `
                <tr id="reminder_${reminder.id}" class="hover:bg-gray-50 transition duration-150" data-reminder-id="${reminder.id}">
                    <td class="px-4 py-2 whitespace-nowrap">${reminder.id}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${reminder.title}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${formatProject(reminder.project)}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${formatServices(reminder.services)}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${reminder.frequency.charAt(0).toUpperCase() + reminder.frequency.slice(1)}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${reminder.time_of_day}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${reminder.active ? 'Active' : 'Inactive'}</td>
                    <td class="px-4 py-2 whitespace-nowrap">${operations}</td>
                </tr>
            `;
            $(`#reminder_${reminder.id}`).replaceWith(rowHtml);
        }

        // Function to render reminders table
        function renderRemindersTable(reminders, total) {
            let tableHtml = `
                <div class="bg-white rounded-lg shadow-md overflow-x-auto">
                    <table class="table table-bordered w-full text-xs mb-0" id="remindersTable">
                        <thead class="bg-gray-500">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="id">ID ${currentSortColumn === 'id' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="title">Title ${currentSortColumn === 'title' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider">Project</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider">Services</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="frequency">Frequency ${currentSortColumn === 'frequency' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider" style="min-width: 100px; text-align: center;">Operations</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
            `;
            if (reminders.length === 0) {
                tableHtml += `
                    <tr>
                        <td colspan="8" class="px-4 py-2 text-center text-gray-500 text-xs">
                            No reminders found for the selected filters.
                        </td>
                    </tr>
                `;
            } else {
                reminders.forEach(reminder => {
                    const operations = `
                        <div class="flex flex-wrap gap-1 justify-content-center">
                            <button class="btn btn-sm btn-primary editReminderBtn" data-bs-toggle="modal" data-bs-target="#editReminderModal" data-id="${reminder.id}" title="Edit"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger deleteReminderBtn" data-id="${reminder.id}" title="Delete"><i class="fas fa-trash"></i></button>
                        </div>
                    `;
                    tableHtml += `
                        <tr id="reminder_${reminder.id}" class="hover:bg-gray-50 transition duration-150" data-reminder-id="${reminder.id}">
                            <td class="px-4 py-2 whitespace-nowrap">${reminder.id}</td>
                            <td class="px-4 py-2 whitespace-nowrap">${reminder.title}</td>
                            <td class="px-4 py-2 whitespace-nowrap">${formatProject(reminder.project)}</td>
                            <td class="px-4 py-2 whitespace-nowrap">${formatServices(reminder.services)}</td>
                            <td class="px-4 py-2 whitespace-nowrap">${reminder.frequency.charAt(0).toUpperCase() + reminder.frequency.slice(1)}</td>
                            <td class="px-4 py-2 whitespace-nowrap">${reminder.time_of_day}</td>
                            <td class="px-4 py-2 whitespace-nowrap">${reminder.active ? 'Active' : 'Inactive'}</td>
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

        // Function to render pagination
        function renderPagination(pagination) {
            let linksHtml = '<div class="mt-4 mb-4"><nav aria-label="Page navigation"><ul class="pagination flex flex-wrap justify-center gap-1">';
            const currentPage = pagination.current_page;
            const lastPage = pagination.last_page;
            const delta = 1;

            if (currentPage > 1) {
                linksHtml += `<li class="page-item"><a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="#" data-page="${currentPage - 1}">Previous</a></li>`;
            } else {
                linksHtml += `<li class="page-item disabled"><a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="#">Previous</a></li>`;
            }

            const pagesToShow = [];
            pagesToShow.push(1);
            if (lastPage > 1) {
                if (currentPage - delta > 2) {
                    pagesToShow.push('...');
                }
                for (let i = Math.max(2, currentPage - delta); i <= Math.min(lastPage - 1, currentPage + delta); i++) {
                    pagesToShow.push(i);
                }
                if (currentPage + delta < lastPage - 1) {
                    pagesToShow.push('...');
                }
                pagesToShow.push(lastPage);
            }

            pagesToShow.forEach(page => {
                if (page === '...') {
                    linksHtml += `<li class="page-item disabled"><span class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm">...</span></li>`;
                } else {
                    linksHtml += `<li class="page-item ${page === currentPage ? 'active' : ''}"><a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="#" data-page="${page}">${page}</a></li>`;
                }
            });

            if (currentPage < lastPage) {
                linksHtml += `<li class="page-item"><a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="#" data-page="${currentPage + 1}">Next</a></li>`;
            } else {
                linksHtml += `<li class="page-item disabled"><a class="page-link px-2 py-1 text-xs sm:px-3 sm:py-1.5 sm:text-sm" href="#">Next</a></li>`;
            }

            linksHtml += '</ul></nav></div>';
            return linksHtml;
        }

        // Function to load reminders with filters, sort, and pagination
        function loadReminders(page = 1) {
            const params = {
                project_id: $('#projectFilter').val(),
                service_ids: $('#servicesFilter').val() || [],
                frequency: $('#frequencyFilter').val(),
                search: $('#searchFilter').val(),
                sort_by: currentSortColumn,
                sort_direction: currentSortDirection,
                page: page,
                ajax: true
            };

            $('#loadingOverlay').show();
            $('#remindersContainer').hide();

            $.ajax({
                url: "{{ route('reminders.index') }}",
                type: "GET",
                data: params,
                success: function(response) {
                    if (response.success && Array.isArray(response.reminders)) {
                        const html = renderRemindersTable(response.reminders, response.pagination.total);
                        const pagination = renderPagination(response.pagination);
                        $('#remindersContainer').html(html + '<div id="paginationContainer">' + pagination + '</div>');
                        $('.page-link').off('click.reminderApp').on('click.reminderApp', function(e) {
                            e.preventDefault();
                            const page = $(this).data('page');
                            if (page) loadReminders(page);
                        });
                        $('.sortable').off('click.reminderApp').on('click.reminderApp', function() {
                            const column = $(this).data('column');
                            const newDirection = (currentSortColumn === column && currentSortDirection === 'asc') ? 'desc' : 'asc';
                            currentSortColumn = column;
                            currentSortDirection = newDirection;
                            loadReminders(1);
                        });
                        $('h1 .badge').text(response.pagination.total);
                    } else {
                        $('#remindersContainer').html(`
                            <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs">
                                No reminders found for the selected filters.
                            </div>
                        `);
                        $('h1 .badge').text('0');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'No reminders found or invalid data.',
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'rounded-lg',
                                confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                            }
                        });
                    }
                    $('#loadingOverlay').hide();
                    $('#remindersContainer').show();
                },
                error: function(xhr) {
                    console.error("Reminders Load Error:", xhr.responseText);
                    $('#remindersContainer').html(`
                        <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs">
                            No reminders found for the selected filters.
                        </div>
                    `);
                    $('h1 .badge').text('0');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error loading reminders: ' + (xhr.responseJSON?.message || 'Server error'),
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                        }
                    });
                    $('#loadingOverlay').hide();
                    $('#remindersContainer').show();
                }
            });
        }

        // Filter change events
        $('#projectFilter, #servicesFilter, #frequencyFilter').on('change.reminderApp', function() {
            loadReminders(1);
        });

        // Search filter with debounce
        let searchTimeout;
        $('#searchFilter').on('input.reminderApp', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadReminders(1);
            }, 500);
        });

        // Function to show popup message
        function showPopup(message, icon = 'error') {
            Swal.fire({
                icon: icon,
                title: icon.charAt(0).toUpperCase() + icon.slice(1),
                html: message,
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'rounded-lg',
                    confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                }
            });
        }

        // Function to clear modal backdrops
        function clearModalBackdrops() {
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
            $('body').css('padding-right', '');
            $('.modal').each(function() {
                $(this).removeClass('show').css('display', 'none');
            });
        }

        // Add reminder form submission
        $('#addReminderForm').on('submit.reminderApp', function(e) {
            e.preventDefault();
            if (validateForm('addReminderForm')) {
                let formData = new FormData(this);
                const $submitButton = $(this).find('button[type="submit"]');
                $submitButton.prop('disabled', true).text('Saving...');
                $.ajax({
                    url: "{{ route('reminders.store') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    timeout: 10000,
                    success: function(response) {
                        if (response.success) {
                            clearModalBackdrops();
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
                                modals.addReminderModal.hide();
                                $submitButton.prop('disabled', false).text('Save Reminder');
                                $('#addReminderForm')[0].reset();
                                $(`#addReminderModal .select2`).val(null).trigger('change');
                                loadReminders();
                            });
                        } else {
                            $submitButton.prop('disabled', false).text('Save Reminder');
                            showPopup('Error creating reminder: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        $submitButton.prop('disabled', false).text('Save Reminder');
                        let message = xhr.responseJSON?.message || 'Error creating reminder.';
                        if (xhr.responseJSON?.errors) {
                            for (let field in xhr.responseJSON.errors) {
                                $(`#addReminderForm #${field}_error`)
                                    .text(xhr.responseJSON.errors[field][0])
                                    .show();
                                $(`#addReminderForm #${field}`).addClass('is-invalid');
                            }
                        } else {
                            showPopup(message);
                        }
                    }
                });
            }
        });

        // Edit reminder form population
        $(document).on('click.reminderApp', '.editReminderBtn', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            $.ajax({
                url: "{{ url('reminders') }}/" + id + "/edit",
                type: "GET",
                success: function(response) {
                    $('#editReminderModal .modal-content').html(response);
                    initializeSelect2('editReminderModal');
                    handleFrequencyChange('editReminderModal');
                    modals.editReminderModal.show();
                },
                error: function(xhr) {
                    showPopup("Error loading edit form: " + (xhr.responseJSON?.message || "Server error"));
                }
            });
        });

        // Edit reminder form submission
        $(document).on('submit.reminderApp', '#editReminderForm', function(e) {
            e.preventDefault();
            if (validateForm('editReminderForm')) {
                let formData = new FormData(this);
                let id = $('#edit_id').val();
                formData.append('_method', 'PATCH');
                const $submitButton = $(this).find('button[type="submit"]');
                $submitButton.prop('disabled', true).text('Updating...');
                $.ajax({
                    url: "{{ route('reminders.update', ':id') }}".replace(':id', id),
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    timeout: 10000,
                    success: function(response) {
                        if (response.success) {
                            clearModalBackdrops();
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
                                modals.editReminderModal.hide();
                                $submitButton.prop('disabled', false).text('Update Reminder');
                                $.ajax({
                                    url: "{{ url('reminders') }}/" + id,
                                    type: "GET",
                                    success: function(detailResponse) {
                                        if (detailResponse.success) {
                                            updateReminderRow(detailResponse.data);
                                            loadReminders();
                                        }
                                    },
                                    error: function(xhr) {
                                        showPopup("Error fetching updated reminder: " + (xhr.responseJSON?.message || "Server error"));
                                    }
                                });
                            });
                        } else {
                            $submitButton.prop('disabled', false).text('Update Reminder');
                            showPopup('Error updating reminder: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        $submitButton.prop('disabled', false).text('Update Reminder');
                        let message = xhr.responseJSON?.message || 'Error updating reminder.';
                        if (xhr.responseJSON?.errors) {
                            for (let field in xhr.responseJSON.errors) {
                                $(`#editReminderForm #${field}_error`)
                                    .text(xhr.responseJSON.errors[field][0])
                                    .show();
                                $(`#editReminderForm #${field}`).addClass('is-invalid');
                            }
                        } else {
                            showPopup(message);
                        }
                    }
                });
            }
        });

        // Delete reminder
        $(document).on('click.reminderApp', '.deleteReminderBtn', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            if (!id || isNaN(id)) {
                showPopup('Invalid reminder ID.');
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                customClass: {
                    popup: 'rounded-lg',
                    confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('reminders') }}/" + id,
                        type: "DELETE",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Explicitly include CSRF token
                        },
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
                                    loadReminders();
                                });
                            } else {
                                showPopup("Error deleting reminder: " + response.message);
                            }
                        },
                        error: function(xhr) {
                            console.error("Delete Reminder Error:", {
                                status: xhr.status,
                                responseText: xhr.responseText,
                                responseJSON: xhr.responseJSON
                            });
                            let message = xhr.responseJSON?.message || "Server error";
                            if (xhr.status === 419) {
                                message = "CSRF token mismatch. Please refresh the page and try again.";
                            }
                            showPopup("Error deleting reminder: " + message);
                        }
                    });
                }
            });
        });

        // Validate form
        function validateForm(formId) {
            let isValid = true;
            $(`#${formId} .invalid-feedback`).hide();
            $(`#${formId} .form-control, #${formId} .select2`).removeClass('is-invalid');

            if (!$(`#${formId} #project_id`).val()) {
                $(`#${formId} #project_id`).addClass('is-invalid');
                $(`#${formId} #project_id_error`).text('Please select a project.').show();
                isValid = false;
            }
            if (!$(`#${formId} #title`).val()) {
                $(`#${formId} #title`).addClass('is-invalid');
                $(`#${formId} #title_error`).text('Please enter a reminder title.').show();
                isValid = false;
            }
            if (!$(`#${formId} #type`).val()) {
                $(`#${formId} #type`).addClass('is-invalid');
                $(`#${formId} #type_error`).text('Please select a reminder type.').show();
                isValid = false;
            }
            if (!$(`#${formId} #frequency`).val()) {
                $(`#${formId} #frequency`).addClass('is-invalid');
                $(`#${formId} #frequency_error`).text('Please select a frequency.').show();
                isValid = false;
            }
            if (!$(`#${formId} #time_of_day`).val()) {
                $(`#${formId} #time_of_day`).addClass('is-invalid');
                $(`#${formId} #time_of_day_error`).text('Please select a time.').show();
                isValid = false;
            }
            if ($(`#${formId} #frequency`).val() === 'weekly' && $(`#${formId} [name="days_of_week[]"]:checked`).length === 0) {
                $(`#${formId} #days_of_week_field`).find('.form-group').addClass('is-invalid');
                $(`#${formId} #days_of_week_error`).text('Please select at least one day of the week.').show();
                isValid = false;
            }
            if ($(`#${formId} #frequency`).val() === 'monthly' && !$(`#${formId} #day_of_month`).val()) {
                $(`#${formId} #day_of_month`).addClass('is-invalid');
                $(`#${formId} #day_of_month_error`).text('Please enter a valid day of the month (1-31).').show();
                isValid = false;
            }
            if ($(`#${formId} #frequency`).val() === 'one_time' && !$(`#${formId} #specific_date`).val()) {
                $(`#${formId} #specific_date`).addClass('is-invalid');
                $(`#${formId} #specific_date_error`).text('Please select a specific date.').show();
                isValid = false;
            }

            return isValid;
        }

        // Initial load
        loadReminders();
    });
</script>

<style>
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
    #remindersContainer {
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
    .bg-gray-500 {
        background-color: #d1dae5 !important;
    }
    #filtersContainer {
        transition: opacity 0.3s ease, transform 0.3s ease;
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
    .select2-container .select2-selection--multiple,
    .select2-container .select2-selection--single {
        min-height: 38px;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
    }
    .select2-container .select2-selection--multiple .select2-selection__choice {
        background-color: #4f46e5;
        color: white;
        border-radius: 0.25rem;
    }
    .select2-container .select2-selection--multiple .select2-selection__choice__remove {
        color: white;
    }
    .select2-no-overflow .select2-dropdown {
        max-height: 200px;
        overflow-y: auto;
        width: 100% !important;
        box-sizing: border-box;
    }
    .select2-container {
        width: 100% !important;
    }
    .select2-container .select2-selection--multiple .select2-selection__rendered {
        display: inline-flex !important;
    }
    @media (max-width: 640px) {
        #filtersContainer {
            padding: 1rem;
        }
        #filtersContainer .grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        .form-control, .form-select {
            width: 100%;
        }
        .pagination .page-link {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        .modal-body {
            max-height: 50vh;
            overflow-y: auto;
        }
    }
</style>
@endpush
@endsection
