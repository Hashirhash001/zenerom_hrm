@extends('layouts.app')

@section('content')
@php
    $sortColumn = request('sort_by', 'id');
    $sortDirection = request('sort_direction', 'asc');
    $year = request('year', date('Y'));
    $month = request('month', '');
    $search = request('search', '');

    function formatDates($dates) {
        if (!is_array($dates) || empty($dates)) {
            return 'None';
        }
        return implode(', ', array_map(function($date) {
            return \Carbon\Carbon::parse($date)->format('d M Y');
        }, $dates));
    }

    function formatRoles($roleIds) {
        $roleIds = is_array($roleIds) ? $roleIds : (json_decode($roleIds, true) ?? []);
        if (empty($roleIds)) {
            return 'None';
        }
        $roleNames = \App\Models\Role::whereIn('id', $roleIds)->pluck('name')->toArray();
        return implode(', ', $roleNames) ?: 'None';
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
            <h1 class="text-2xl font-bold text-gray-800">Public Holidays <span class="badge bg-success text-white px-2 py-1 rounded">{{ $holidays->count() }}</span></h1>
        </div>
        <div class="flex items-center space-x-3">
            <button id="toggleFiltersBtn" class="btn btn-secondary flex items-center space-x-2">
                <i class="fas fa-filter"></i>
                <span>Toggle Filters</span>
            </button>
            <button class="btn btn-primary flex items-center space-x-2" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
                <i class="fas fa-plus mr-1"></i>
                <span>Add Holiday</span>
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div id="filtersContainer" class="bg-white shadow-md rounded-lg p-4 mb-6" style="min-height: 100px; display: none;">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end">
            <div>
                <label for="yearFilter" class="block text-xs font-medium text-gray-700">Year</label>
                <select name="year" id="yearFilter" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2">
                    <option value="">All Years</option>
                    @for($y = 2024; $y <= 2050; $y++)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label for="monthFilter" class="block text-xs font-medium text-gray-700">Month</label>
                <select name="month" id="monthFilter" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2">
                    <option value="">All Months</option>
                    @foreach([
                        '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
                        '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
                        '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
                    ] as $value => $name)
                        <option value="{{ $value }}" {{ ($value == $month) ?: (date('m') == $value ? 'selected' : '') }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="searchFilter" class="block text-xs font-medium text-gray-700">Search by Name</label>
                <input type="text" name="search" id="searchFilter" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2" value="{{ $search }}" placeholder="Enter holiday name">
            </div>
            <div>
                <a href="{{ route('public-holidays.index') }}" class="btn btn-secondary text-xs px-4 py-2 text-center">Clear</a>
            </div>
        </div>
    </div>

    <!-- Holidays Table Container -->
    <div id="holidaysContainer" class="relative">
        <!-- Loading Overlay -->
        <div id="loadingOverlay" style="display: none;">
            <div class="spinner-border text-indigo-600" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        @if($holidays->count() > 0)
            <div class="bg-white rounded-lg shadow-md overflow-x-auto">
                <table class="table table-bordered w-full text-xs mb-0" id="holidaysTable">
                    <thead class="bg-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="id">ID {{ $sortColumn == 'id' ? ($sortDirection == 'asc' ? '↑' : '↓') : '' }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="name">Name {{ $sortColumn == 'name' ? ($sortDirection == 'asc' ? '↑' : '↓') : '' }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider">Dates</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="year">Year {{ $sortColumn == 'year' ? ($sortDirection == 'asc' ? '↑' : '↓') : '' }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider">Roles</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider" style="min-width: 100px; text-align: center;">Operations</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($holidays as $holiday)
                            <tr id="holiday_{{ $holiday->id }}" class="hover:bg-gray-50 transition duration-150" data-holiday-id="{{ $holiday->id }}">
                                <td class="px-4 py-2 whitespace-nowrap">{{ $holiday->id }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $holiday->name }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ formatDates($holiday->dates) }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $holiday->year }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">{{ formatRoles($holiday->role_ids) }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1 justify-content-center">
                                        <button class="btn btn-sm btn-primary editHolidayBtn" data-bs-toggle="modal" data-bs-target="#editHolidayModal" data-id="{{ $holiday->id }}" data-name="{{ $holiday->name }}" data-year="{{ $holiday->year }}" data-dates='@json($holiday->dates)' data-role_ids='@json($holiday->role_ids)' title="Edit"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger deleteHolidayBtn" data-id="{{ $holiday->id }}" title="Delete"><i class="fas fa-trash"></i></button>
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
                    $pagination = $holidays->appends(request()->query());
                @endphp
                @include('public_holidays._pagination', ['pagination' => $pagination])
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs">
                No holidays found for the selected filters.
            </div>
        @endif
    </div>

    <!-- Add Holiday Modal -->
    <div class="modal fade" id="addHolidayModal" tabindex="-1" aria-labelledby="addHolidayModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-lg">
                <div class="modal-header border-b border-gray-200">
                    <h5 class="modal-title text-lg font-semibold" id="addHolidayModalLabel">Add Public Holiday</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addHolidayForm">
                    @csrf
                    <div class="modal-body p-5">
                        <div class="mb-3">
                            <label for="add_name" class="form-label text-sm font-medium text-gray-700">Holiday Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control w-full border-gray-300 rounded-md shadow-sm text-sm p-2" id="add_name" name="name" placeholder="Enter holiday name" required>
                            <div id="add_name_error" class="invalid-feedback text-red-600 text-xs"></div>
                        </div>
                        <div class="mb-3">
                            <label for="add_dates" class="form-label text-sm font-medium text-gray-700">Dates <span class="text-danger">*</span></label>
                            <input type="text" class="form-control w-full border-gray-300 rounded-md shadow-sm text-sm p-2" id="add_dates" placeholder="Select dates" required>
                            <div id="add_dates_error" class="invalid-feedback text-red-600 text-xs"></div>
                        </div>
                        <div class="mb-3">
                            <label for="add_year" class="form-label text-sm font-medium text-gray-700">Year <span class="text-danger">*</span></label>
                            <select class="form-control w-full border-gray-300 rounded-md shadow-sm text-sm p-2" id="add_year" name="year" required>
                                <option value="" disabled selected>Select year</option>
                                @for($y = 2024; $y <= 2050; $y++)
                                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                            <div id="add_year_error" class="invalid-feedback text-red-600 text-xs"></div>
                        </div>
                        <div class="mb-3">
                            <label for="add_role_ids" class="form-label text-sm font-medium text-gray-700">Roles <span class="text-danger">*</span></label>
                            <select class="form-control select2 w-full border-gray-300 rounded-md shadow-sm text-sm p-2" id="add_role_ids" name="role_ids[]" multiple required>
                                <option value="all">Select All</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <div id="add_role_ids_error" class="invalid-feedback text-red-600 text-xs"></div>
                        </div>
                    </div>
                    <div class="modal-footer border-t border-gray-200">
                        <button type="button" class="btn btn-secondary text-xs px-4 py-2" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary text-xs px-4 py-2">Save Holiday</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Holiday Modal -->
    <div class="modal fade" id="editHolidayModal" tabindex="-1" aria-labelledby="editHolidayModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-lg">
                <div class="modal-header border-b border-gray-200">
                    <h5 class="modal-title text-lg font-semibold" id="editHolidayModalLabel">Edit Public Holiday</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editHolidayForm">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body p-5">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label text-sm font-medium text-gray-700">Holiday Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control w-full border-gray-300 rounded-md shadow-sm text-sm p-2" id="edit_name" name="name" placeholder="Enter holiday name" required>
                            <div id="edit_name_error" class="invalid-feedback text-red-600 text-xs"></div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_dates" class="form-label text-sm font-medium text-gray-700">Dates <span class="text-danger">*</span></label>
                            <input type="text" class="form-control w-full border-gray-300 rounded-md shadow-sm text-sm p-2" id="edit_dates" placeholder="Select dates" required>
                            <div id="edit_dates_error" class="invalid-feedback text-red-600 text-xs"></div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_year" class="form-label text-sm font-medium text-gray-700">Year <span class="text-danger">*</span></label>
                            <select class="form-control w-full border-gray-300 rounded-md shadow-sm text-sm p-2" id="edit_year" name="year" required>
                                <option value="" disabled selected>Select year</option>
                                @for($y = 2024; $y <= 2050; $y++)
                                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                            <div id="edit_year_error" class="invalid-feedback text-red-600 text-xs"></div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_role_ids" class="form-label text-sm font-medium text-gray-700">Roles <span class="text-danger">*</span></label>
                            <select class="form-control select2 w-full border-gray-300 rounded-md shadow-sm text-sm p-2" id="edit_role_ids" name="role_ids[]" multiple required>
                                <option value="all">Select All</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <div id="edit_role_ids_error" class="invalid-feedback text-red-600 text-xs"></div>
                        </div>
                    </div>
                    <div class="modal-footer border-t border-gray-200">
                        <button type="button" class="btn btn-secondary text-xs px-4 py-2" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary text-xs px-4 py-2">Update Holiday</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://momentjs.com/downloads/moment.js"></script>
    <script src="https://momentjs.com/downloads/moment-timezone-with-data.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        // Debug: Log when scripts are loaded
        console.log('jQuery loaded:', typeof $ !== 'undefined');
        console.log('Flatpickr loaded:', typeof flatpickr !== 'undefined');
        console.log('jQuery Flatpickr plugin:', typeof $.fn.flatpickr);
        console.log('Bootstrap loaded:', typeof bootstrap !== 'undefined');
        console.log('Select2 loaded:', typeof $.fn.select2 !== 'undefined');
        console.log('Moment with timezone loaded:', typeof moment.tz !== 'undefined');

        $(document).ready(function() {
            let currentSortColumn = @json($sortColumn);
            let currentSortDirection = @json($sortDirection);

            // Initialize Bootstrap modals
            let modals = {
                addHolidayModal: new bootstrap.Modal(document.getElementById('addHolidayModal'), { backdrop: true }),
                editHolidayModal: new bootstrap.Modal(document.getElementById('editHolidayModal'), { backdrop: true })
            };

            // Function to initialize Flatpickr with retry mechanism
            function initializeFlatpickr(attempts = 5, delay = 100) {
                console.log('Attempting Flatpickr initialization, attempt:', attempts);
                console.log('flatpickr:', typeof flatpickr);
                console.log('$.fn.flatpickr:', typeof $.fn.flatpickr);
                if (typeof flatpickr !== 'undefined') {
                    flatpickr('#add_dates', {
                        mode: 'multiple',
                        dateFormat: 'Y-m-d',
                        allowInput: true,
                        maxDate: '2050-12-31',
                        minDate: '2020-01-01',
                        onChange: function(selectedDates, dateStr, instance) {
                            let $form = $(instance.element).closest('form');
                            $form.find('[name="dates[]"]').remove();
                            selectedDates.forEach(date => {
                                // Format date in Asia/Kolkata timezone
                                let formattedDate = moment.tz(date, 'Asia/Kolkata').format('YYYY-MM-DD');
                                $form.append(`<input type="hidden" name="dates[]" value="${formattedDate}">`);
                            });
                            instance.element.value = dateStr;
                        }
                    });
                    console.log('Flatpickr initialized for #add_dates');
                    return true;
                } else if (attempts > 0) {
                    console.warn(`Flatpickr not loaded, retrying... (${attempts} attempts left)`);
                    setTimeout(() => initializeFlatpickr(attempts - 1, delay * 2), delay);
                    return false;
                } else {
                    console.error('Flatpickr failed to load after retries');
                    $('#add_dates, #edit_dates').attr('type', 'text').val('Enter dates (YYYY-MM-DD, comma-separated)');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Date picker failed to load. Please enter dates manually (YYYY-MM-DD, comma-separated).',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                        }
                    });
                    return false;
                }
            }

            // Initialize Select2
            $('.select2').select2({
                placeholder: 'Select roles',
                allowClear: true,
                width: '100%',
                dropdownCssClass: 'select2-no-overflow'
            }).on('select2:select.holidayApp', function(e) {
                if (e.params.data.id === 'all') {
                    let $select = $(this);
                    $select.find('option').not('[value="all"]').prop('selected', true);
                    $select.trigger('change');
                }
            });

            // Initialize Flatpickr with retry
            initializeFlatpickr();

            // Toggle Filters
            $('#toggleFiltersBtn').on('click.holidayApp', function() {
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

            // Function to format dates
            function formatDates(dates) {
                if (!Array.isArray(dates) || dates.length === 0) return 'None';
                return dates.map(date => moment(date).format('DD MMM YYYY')).join(', ');
            }

            // Function to format roles
            function formatRoles(roleIds) {
                if (!Array.isArray(roleIds) || roleIds.length === 0) return 'None';
                return roleIds.map(id => {
                    let option = $('#add_role_ids option[value="' + id + '"]').text();
                    return option || id;
                }).join(', ');
            }

            // Function to update a single holiday row
            function updateHolidayRow(holiday) {
                const operations = `
                    <div class="flex flex-wrap gap-1 justify-content-center">
                        <button class="btn btn-sm btn-primary editHolidayBtn" data-bs-toggle="modal" data-bs-target="#editHolidayModal" data-id="${holiday.id}" data-name="${holiday.name}" data-year="${holiday.year}" data-dates='${JSON.stringify(holiday.dates)}' data-role_ids='${JSON.stringify(holiday.role_ids)}' title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger deleteHolidayBtn" data-id="${holiday.id}" title="Delete"><i class="fas fa-trash"></i></button>
                    </div>
                `;
                const rowHtml = `
                    <tr id="holiday_${holiday.id}" class="hover:bg-gray-50 transition duration-150" data-holiday-id="${holiday.id}">
                        <td class="px-4 py-2 whitespace-nowrap">${holiday.id}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${holiday.name}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${formatDates(holiday.dates)}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${holiday.year}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${formatRoles(holiday.role_ids)}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${operations}</td>
                    </tr>
                `;
                $(`#holiday_${holiday.id}`).replaceWith(rowHtml);
            }

            // Function to render holidays table
            function renderHolidaysTable(holidays, total) {
                let tableHtml = `
                    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
                        <table class="table table-bordered w-full text-xs mb-0" id="holidaysTable">
                            <thead class="bg-gray-500">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="id">ID ${currentSortColumn === 'id' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="name">Name ${currentSortColumn === 'name' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider">Dates</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="year">Year ${currentSortColumn === 'year' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider">Roles</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider" style="min-width: 100px; text-align: center;">Operations</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                `;
                if (holidays.length === 0) {
                    tableHtml += `
                        <tr>
                            <td colspan="6" class="px-4 py-2 text-center text-gray-500 text-xs">
                                No holidays found for the selected filters.
                            </td>
                        </tr>
                    `;
                } else {
                    holidays.forEach(holiday => {
                        const operations = `
                            <div class="flex flex-wrap gap-1 justify-content-center">
                                <button class="btn btn-sm btn-primary editHolidayBtn" data-bs-toggle="modal" data-bs-target="#editHolidayModal" data-id="${holiday.id}" data-name="${holiday.name}" data-year="${holiday.year}" data-dates='${JSON.stringify(holiday.dates)}' data-role_ids='${JSON.stringify(holiday.role_ids)}' title="Edit"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger deleteHolidayBtn" data-id="${holiday.id}" title="Delete"><i class="fas fa-trash"></i></button>
                            </div>
                        `;
                        tableHtml += `
                            <tr id="holiday_${holiday.id}" class="hover:bg-gray-50 transition duration-150" data-holiday-id="${holiday.id}">
                                <td class="px-4 py-2 whitespace-nowrap">${holiday.id}</td>
                                <td class="px-4 py-2 whitespace-nowrap">${holiday.name}</td>
                                <td class="px-4 py-2 whitespace-nowrap">${formatDates(holiday.dates)}</td>
                                <td class="px-4 py-2 whitespace-nowrap">${holiday.year}</td>
                                <td class="px-4 py-2 whitespace-nowrap">${formatRoles(holiday.role_ids)}</td>
                                <td class="px-2 py-1 whitespace-nowrap">${operations}</td>
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

            // Function to load holidays with filters, sort, and pagination
            function loadHolidays(page = 1) {
                const params = {
                    year: $('#yearFilter').val(),
                    month: $('#monthFilter').val(),
                    search: $('#searchFilter').val(),
                    sort_by: currentSortColumn,
                    sort_direction: currentSortDirection,
                    page: page,
                    ajax: true
                };

                $('#loadingOverlay').show();
                $('#holidaysContainer').hide();

                $.ajax({
                    url: "{{ route('public-holidays.index') }}",
                    type: "GET",
                    data: params,
                    success: function(response) {
                        if (response.success && Array.isArray(response.holidays)) {
                            const html = renderHolidaysTable(response.holidays, response.pagination.total);
                            const pagination = renderPagination(response.pagination);
                            $('#holidaysContainer').html(html + '<div id="paginationContainer">' + pagination + '</div>');
                            $('.page-link').off('click.holidayApp').on('click.holidayApp', function(e) {
                                e.preventDefault();
                                const page = $(this).data('page');
                                if (page) loadHolidays(page);
                            });
                            $('.sortable').off('click.holidayApp').on('click.holidayApp', function() {
                                const column = $(this).data('column');
                                const newDirection = (currentSortColumn === column && currentSortDirection === 'asc') ? 'desc' : 'asc';
                                currentSortColumn = column;
                                currentSortDirection = newDirection;
                                loadHolidays(1);
                            });
                            // Update header badge
                            $('h1 .badge').text(response.pagination.total);
                        } else {
                            $('#holidaysContainer').html(`
                                <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs">
                                    No holidays found for the selected filters.
                                </div>
                            `);
                            $('h1 .badge').text('0');
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'No holidays found or invalid data.',
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'rounded-lg',
                                    confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                                }
                            });
                        }
                        $('#loadingOverlay').hide();
                        $('#holidaysContainer').show();
                    },
                    error: function(xhr) {
                        console.error("Holidays Load Error:", xhr.responseText);
                        $('#holidaysContainer').html(`
                            <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs">
                                No holidays found for the selected filters.
                            </div>
                        `);
                        $('h1 .badge').text('0');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error loading holidays: ' + (xhr.responseJSON?.message || 'Server error'),
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'rounded-lg',
                                confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                            }
                        });
                        $('#loadingOverlay').hide();
                        $('#holidaysContainer').show();
                    }
                });
            }

            // Filter change events
            $('#yearFilter, #monthFilter').on('change.holidayApp', function() {
                loadHolidays(1);
            });

            // Search filter with debounce
            let searchTimeout;
            $('#searchFilter').on('input.holidayApp', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    loadHolidays(1);
                }, 500);
            });

            // Function to show popup message
            function showPopup(message, icon = 'error') {
                Swal.fire({
                    icon: icon,
                    title: icon.charAt(0).toUpperCase() + icon.slice(1),
                    text: message,
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

            // Add holiday form submission
            $('#addHolidayForm').on('submit.holidayApp', function(e) {
                e.preventDefault();
                if (validateForm('addHolidayForm')) {
                    let formData = new FormData(this);
                    if ($('#add_role_ids').val().includes('all')) {
                        formData.delete('role_ids[]');
                        $('#add_role_ids option').not('[value="all"]').each(function() {
                            formData.append('role_ids[]', $(this).val());
                        });
                    }
                    const $submitButton = $(this).find('button[type="submit"]');
                    $submitButton.prop('disabled', true).text('Saving...');
                    $.ajax({
                        url: "{{ route('public-holidays.store') }}",
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
                                    modals.addHolidayModal.hide();
                                    $submitButton.prop('disabled', false).text('Save Holiday');
                                    loadHolidays();
                                });
                            } else {
                                $submitButton.prop('disabled', false).text('Save Holiday');
                                showPopup('Error creating holiday: ' + response.message);
                            }
                        },
                        error: function(xhr) {
                            $submitButton.prop('disabled', false).text('Save Holiday');
                            let message = xhr.responseJSON?.message || 'Error creating holiday.';
                            if (xhr.responseJSON?.errors) {
                                for (let field in xhr.responseJSON.errors) {
                                    $(`#add_${field}_error`)
                                        .text(xhr.responseJSON.errors[field][0])
                                        .show();
                                    $(`#add_${field}`).addClass('is-invalid');
                                }
                            } else {
                                showPopup(message);
                            }
                        }
                    });
                }
            });

            // Edit holiday form population
            $(document).on('click.holidayApp', '.editHolidayBtn', function(e) {
                e.preventDefault();
                console.log('Edit button clicked:', $(this).data());
                let id = $(this).data('id');
                let name = $(this).data('name');
                let year = $(this).data('year');
                let dates = $(this).data('dates');
                let role_ids = $(this).data('role_ids');

                // Ensure dates is an array
                if (!Array.isArray(dates)) {
                    try {
                        dates = JSON.parse(dates || '[]');
                    } catch (e) {
                        console.error('Error parsing dates:', e);
                        dates = [];
                    }
                }

                $('#edit_id').val(id);
                $('#edit_name').val(name);
                $('#edit_year').val(year);
                $('#edit_role_ids').val(role_ids).trigger('change');

                if (typeof flatpickr !== 'undefined') {
                    // Destroy existing Flatpickr instance if any
                    let existingInstance = $('#edit_dates').data('flatpickr');
                    if (existingInstance) {
                        existingInstance.destroy();
                    }
                    let fp = flatpickr('#edit_dates', {
                        mode: 'multiple',
                        dateFormat: 'Y-m-d',
                        defaultDate: dates.length ? dates : null,
                        allowInput: true,
                        maxDate: '2050-12-31',
                        minDate: '2020-01-01',
                        onChange: function(selectedDates, dateStr, instance) {
                            let $form = $(instance.element).closest('form');
                            $form.find('[name="dates[]"]').remove();
                            selectedDates.forEach(date => {
                                // Format date in Asia/Kolkata timezone
                                let formattedDate = moment.tz(date, 'Asia/Kolkata').format('YYYY-MM-DD');
                                $form.append(`<input type="hidden" name="dates[]" value="${formattedDate}">`);
                            });
                            instance.element.value = dateStr;
                        }
                    });
                    $('#edit_dates').data('flatpickr', fp);
                    console.log('Flatpickr initialized for #edit_dates with dates:', dates);

                    // Immediately populate hidden inputs with existing dates
                    let $form = $('#editHolidayForm');
                    $form.find('[name="dates[]"]').remove();
                    if (dates.length) {
                        dates.forEach(date => {
                            // Format existing dates in Asia/Kolkata timezone
                            let formattedDate = moment.tz(date, 'Asia/Kolkata').format('YYYY-MM-DD');
                            $form.append(`<input type="hidden" name="dates[]" value="${formattedDate}">`);
                        });
                    }
                } else {
                    console.error('Flatpickr is not loaded for edit modal');
                    $('#edit_dates').val(dates.join(', '));
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Date picker failed to load. Please enter dates manually (YYYY-MM-DD, comma-separated).',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                        }
                    });
                }
            });

            // Edit holiday form submission
            $(document).on('submit.holidayApp', '#editHolidayForm', function(e) {
                e.preventDefault();
                console.log('Edit holiday form submitted');
                if (!validateForm('editHolidayForm')) {
                    console.log('Validation failed for editHolidayForm');
                    showPopup('Please fill all required fields correctly.', 'error');
                    return;
                }

                let formData = new FormData(this);
                let id = $('#edit_id').val();
                console.log('Form data:', Object.fromEntries(formData));

                // Handle "Select All" for roles
                if ($('#edit_role_ids').val() && $('#edit_role_ids').val().includes('all')) {
                    formData.delete('role_ids[]');
                    $('#edit_role_ids option').not('[value="all"]').each(function() {
                        formData.append('role_ids[]', $(this).val());
                    });
                }

                // Ensure _method is set to PATCH
                formData.append('_method', 'PATCH');

                const $submitButton = $(this).find('button[type="submit"]');
                $submitButton.prop('disabled', true).text('Updating...');

                $.ajax({
                    url: "{{ route('public-holidays.update', ':id') }}".replace(':id', id),
                    type: "POST",
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('#editHolidayForm input[name="_token"]').val()
                    },
                    contentType: false,
                    processData: false,
                    timeout: 10000,
                    success: function(response) {
                        console.log('Update success:', response);
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
                                modals.editHolidayModal.hide();
                                $submitButton.prop('disabled', false).text('Update Holiday');
                                $.ajax({
                                    url: "{{ url('public-holidays') }}/" + id,
                                    type: "GET",
                                    success: function(detailResponse) {
                                        if (detailResponse.success) {
                                            updateHolidayRow({
                                                id: detailResponse.data.id,
                                                name: detailResponse.data.name,
                                                dates: detailResponse.data.dates,
                                                year: detailResponse.data.year,
                                                role_ids: detailResponse.data.role_ids
                                            });
                                            loadHolidays();
                                        }
                                    },
                                    error: function(xhr) {
                                        console.error("Fetch Updated Holiday Error:", xhr.responseText);
                                        showPopup("Error fetching updated holiday: " + (xhr.responseJSON?.message || "Server error"));
                                    }
                                });
                            });
                        } else {
                            $submitButton.prop('disabled', false).text('Update Holiday');
                            showPopup('Error updating holiday: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        console.error('Update error:', xhr.responseText);
                        $submitButton.prop('disabled', false).text('Update Holiday');
                        let message = xhr.responseJSON?.message || 'Error updating holiday.';
                        if (xhr.responseJSON?.errors) {
                            for (let field in xhr.responseJSON.errors) {
                                $(`#edit_${field}_error`)
                                    .text(xhr.responseJSON.errors[field][0])
                                    .show();
                                $(`#edit_${field}`).addClass('is-invalid');
                            }
                        } else {
                            showPopup(message);
                        }
                    }
                });
            });

            // Delete holiday
            $(document).on('click.holidayApp', '.deleteHolidayBtn', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                if (!id || isNaN(id)) {
                    showPopup('Invalid holiday ID.');
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
                            url: "{{ url('public-holidays') }}/" + id,
                            type: "DELETE",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('#addHolidayForm input[name="_token"]').val()
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
                                        loadHolidays();
                                    });
                                } else {
                                    showPopup("Error deleting holiday: " + response.message);
                                }
                            },
                            error: function(xhr) {
                                console.error("Delete Holiday Error:", xhr.responseText);
                                showPopup("Error deleting holiday: " + (xhr.responseJSON?.message || "Server error"));
                            }
                        });
                    }
                });
            });

            // Validate form
            function validateForm(formId) {
                let isValid = true;
                $(`#${formId} .invalid-feedback`).hide();
                $(`#${formId} .form-control`).removeClass('is-invalid');

                if (!$(`#${formId} [name="name"]`).val()) {
                    $(`#${formId} [name="name"]`).addClass('is-invalid');
                    $(`#${formId} [name="name"]`).next('.invalid-feedback').text('Please enter a holiday name.').show();
                    isValid = false;
                }
                if (!$(`#${formId} [name="dates[]"]`).length) {
                    $(`#${formId} #${formId}_dates`).addClass('is-invalid');
                    $(`#${formId} #${formId}_dates`).next('.invalid-feedback').text('Please select at least one date.').show();
                    isValid = false;
                }
                if (!$(`#${formId} [name="year"]`).val()) {
                    $(`#${formId} [name="year"]`).addClass('is-invalid');
                    $(`#${formId} [name="year"]`).next('.invalid-feedback').text('Please select a valid year (2020-2050).').show();
                    isValid = false;
                }
                if (!$(`#${formId} [name="role_ids[]"]`).val()) {
                    $(`#${formId} [name="role_ids[]"]`).addClass('is-invalid');
                    $(`#${formId} [name="role_ids[]"]`).next('.invalid-feedback').text('Please select at least one role.').show();
                    isValid = false;
                }

                return isValid;
            }

            // Initial load
            loadHolidays();
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
        .form-control, . the {
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
        #holidaysContainer {
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
        .select2-container .select2-selection--multiple {
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
