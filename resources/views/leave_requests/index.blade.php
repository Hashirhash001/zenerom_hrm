@extends('layouts.app')

@section('content')
@php
    // Convert session privileges into a collection for easier access
    $taskPrivileges = collect(session('user_privileges'));
    $isAdminOrAuthorized = in_array(Auth::user()->role_id, [1, 2, 3, 7]);
    $sortColumn = request('sort_by', 'created_at');
    $sortDirection = request('sort_direction', 'desc');

    // Helper function to format leave type
    function formatLeaveType($leaveType) {
        $leaveTypes = [
            'full_day' => 'Full Day',
            'half_day_first' => 'Half Day (First Half)',
            'half_day_second' => 'Half Day (Second Half)',
            'Sick' => 'Sick Leave',
            'Maternity' => 'Maternity Leave',
            'Unpaid' => 'Unpaid Leave',
            'Paid' => 'Paid Leave',
        ];
        return $leaveTypes[$leaveType] ?? ucfirst(str_replace('_', ' ', $leaveType));
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
            <h1 class="text-2xl font-bold text-gray-800">Leave Requests <span class="badge bg-success text-white px-2 py-1 rounded">{{ $leaveRequests->total() }}</span></h1>
        </div>
        <div class="flex items-center space-x-3">
            <button id="toggleFiltersBtn" class="btn btn-secondary flex items-center space-x-2">
                <i class="fas fa-filter"></i>
                <span>Toggle Filters</span>
            </button>
            <button class="btn btn-primary flex items-center space-x-2" data-bs-toggle="modal" data-bs-target="#addLeaveRequestModal">
                <i class="fas fa-plus mr-1"></i>
                <span>Add Leave Request</span>
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div id="filtersContainer" class="bg-white shadow-md rounded-lg p-4 mb-6" style="min-height: 170px; display: none;">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            <div>
                <label for="start_date" class="block text-xs font-medium text-gray-700">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2" value="{{ request('start_date') }}">
            </div>
            <div>
                <label for="end_date" class="block text-xs font-medium text-gray-700">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2" value="{{ request('end_date') }}">
            </div>
            <div>
                <label for="status" class="block text-xs font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2">
                    <option value="">All</option>
                    <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                    <option value="Submitted" {{ request('status') == 'Submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            @if($isAdminOrAuthorized)
                <div id="employeeFilterContainer">
                    <label for="user_id" class="block text-xs font-medium text-gray-700">Employee</label>
                    <select name="user_id" id="user_id" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2">
                        <option value="">All</option>
                    </select>
                </div>
            @endif
            <div>
                <label for="sort_by" class="block text-xs font-medium text-gray-700">Sort By</label>
                <select id="sort_by" name="sort_by" class="form-control w-full border-gray-300 rounded-md shadow-sm text-xs p-2">
                    <option value="id|asc" {{ $sortColumn == 'id' && $sortDirection == 'asc' ? 'selected' : '' }}>ID (Asc)</option>
                    <option value="id|desc" {{ $sortColumn == 'id' && $sortDirection == 'desc' ? 'selected' : '' }}>ID (Desc)</option>
                    <option value="leave_type|asc" {{ $sortColumn == 'leave_type' && $sortDirection == 'asc' ? 'selected' : '' }}>Leave Type (A-Z)</option>
                    <option value="leave_type|desc" {{ $sortColumn == 'leave_type' && $sortDirection == 'desc' ? 'selected' : '' }}>Leave Type (Z-A)</option>
                    <option value="start_date|asc" {{ $sortColumn == 'start_date' && $sortDirection == 'asc' ? 'selected' : '' }}>Start Date (Earliest)</option>
                    <option value="start_date|desc" {{ $sortColumn == 'start_date' && $sortDirection == 'desc' ? 'selected' : '' }}>Start Date (Latest)</option>
                    <option value="end_date|asc" {{ $sortColumn == 'end_date' && $sortDirection == 'asc' ? 'selected' : '' }}>End Date (Earliest)</option>
                    <option value="end_date|desc" {{ $sortColumn == 'end_date' && $sortDirection == 'desc' ? 'selected' : '' }}>End Date (Latest)</option>
                    <option value="team_lead_status|asc" {{ $sortColumn == 'team_lead_status' && $sortDirection == 'asc' ? 'selected' : '' }}>Team Lead Status (A-Z)</option>
                    <option value="team_lead_status|desc" {{ $sortColumn == 'team_lead_status' && $sortDirection == 'desc' ? 'selected' : '' }}>Team Lead Status (Z-A)</option>
                    <option value="hr_status|asc" {{ $sortColumn == 'hr_status' && $sortDirection == 'asc' ? 'selected' : '' }}>HR Status (A-Z)</option>
                    <option value="hr_status|desc" {{ $sortColumn == 'hr_status' && $sortDirection == 'desc' ? 'selected' : '' }}>HR Status (Z-A)</option>
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <a href="{{ route('leave_requests.index') }}" class="btn btn-secondary text-xs px-4 py-2">Clear</a>
            </div>
        </div>
    </div>

    <!-- Leave Requests Table Container -->
    <div id="leaveRequestsContainer" class="relative">
        <!-- Loading Overlay -->
        <div id="loadingOverlay" style="display: none;">
            <div class="spinner-border text-indigo-600" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        @if($leaveRequests->count() > 0)
            <div class="bg-white rounded-lg shadow-md overflow-x-auto">
                @include('leave_requests._list', ['leaveRequests' => $leaveRequests])
            </div>
            <!-- Pagination -->
            <div id="paginationContainer" class="mt-4">
                @php
                    $pagination = $leaveRequests->appends(request()->query());
                @endphp
                @include('leave_requests._pagination', ['pagination' => $pagination])
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs">
                No leave requests found for the selected criteria.
            </div>
        @endif
    </div>

    <!-- Add Leave Request Modal -->
    <div class="modal fade" id="addLeaveRequestModal" tabindex="-1" aria-labelledby="addLeaveRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                @include('leave_requests.create')
            </div>
        </div>
    </div>

    <!-- Approve Leave Modal -->
    <div class="modal fade" id="approveLeaveModal" tabindex="-1" aria-labelledby="approveLeaveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveLeaveModalLabel">Approve Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="approveLeaveForm">
                        @csrf
                        <input type="hidden" name="leave_request_id" id="approveLeaveRequestId">
                        <div class="mb-3">
                            <label for="approver_comments" class="form-label">Comments (Optional)</label>
                            <textarea class="form-control" name="approver_comments" id="approver_comments" rows="4"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-full">Approve Leave</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Leave Modal -->
    <div class="modal fade" id="rejectLeaveModal" tabindex="-1" aria-labelledby="rejectLeaveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectLeaveModalLabel">Reject Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="rejectLeaveForm">
                        @csrf
                        <input type="hidden" name="leave_request_id" id="rejectLeaveRequestId">
                        <div class="mb-3">
                            <label for="reject_comments" class="form-label">Comments (Required)</label>
                            <textarea class="form-control" name="approver_comments" id="reject_comments" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-full">Reject Leave</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Leave Modal -->
    <div class="modal fade" id="deleteLeaveModal" tabindex="-1" aria-labelledby="deleteLeaveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteLeaveModalLabel">Delete Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this leave request? This action cannot be undone.</p>
                    <form id="deleteLeaveForm">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="leave_request_id" id="deleteLeaveRequestId">
                        <button type="submit" class="btn btn-danger w-full">Delete Leave</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Leave Request Details Modal -->
    <div class="modal fade" id="viewLeaveRequestModal" tabindex="-1" aria-labelledby="viewLeaveRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewLeaveRequestModalLabel">Leave Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-5" style="min-height: 80vh; overflow-y: auto;">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Employee Information -->
                        <div>
                            <h6 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-3">Employee Information</h6>
                            <div class="mb-2">
                                <span class="font-medium text-gray-600">Employee:</span>
                                <span id="detailEmployee" class="ml-2"></span>
                            </div>
                            <div class="mb-2">
                                <span class="font-medium text-gray-600">Leave Type:</span>
                                <span id="detailLeaveType" class="ml-2"></span>
                            </div>
                            <div class="mb-2">
                                <span class="font-medium text-gray-600">Start Date:</span>
                                <span id="detailStartDate" class="ml-2"></span>
                            </div>
                            <div class="mb-2">
                                <span class="font-medium text-gray-600">End Date:</span>
                                <span id="detailEndDate" class="ml-2"></span>
                            </div>
                            <div class="mb-2">
                                <span class="font-medium text-gray-600">Duration:</span>
                                <span id="detailDuration" class="ml-2"></span>
                            </div>
                            <div class="mb-2">
                                <span class="font-medium text-gray-600">Reason:</span>
                                <span id="detailReason" class="ml-2"></span>
                            </div>
                        </div>
                        <!-- Approval Information -->
                        <div>
                            <h6 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-3">Approval Information</h6>
                            <div class="mb-2">
                                <span class="font-medium text-gray-600">Team Lead Status:</span>
                                <span id="detailTeamLeadStatus" class="ml-2"></span>
                            </div>
                            <div class="mb-2">
                                <span class="font-medium text-gray-600">HR Status:</span>
                                <span id="detailHrStatus" class="ml-2"></span>
                            </div>
                            <div class="mb-2">
                                <span class="font-medium text-gray-600">Team Lead Approver:</span>
                                <span id="detailTeamLeadApprover" class="ml-2"></span>
                            </div>
                            <div class="mb-2">
                                <span class="font-medium text-gray-600">Team Lead Comments:</span>
                                <span id="detailTeamLeadComments" class="ml-2"></span>
                            </div>
                            <div class="mb-2">
                                <span class="font-medium text-gray-600">HR Approver:</span>
                                <span id="detailHrApprover" class="ml-2"></span>
                            </div>
                            <div class="mb-2">
                                <span class="font-medium text-gray-600">HR Comments:</span>
                                <span id="detailHrComments" class="ml-2"></span>
                            </div>
                            <div class="mb-2">
                                <span class="font-medium text-gray-600">Team Lead Approved At:</span>
                                <span id="detailTeamLeadApprovedAt" class="ml-2"></span>
                            </div>
                            <div class="mb-2">
                                <span class="font-medium text-gray-600">HR Approved At:</span>
                                <span id="detailHrApprovedAt" class="ml-2"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Popup Modal for Messages -->
    <div class="modal fade" id="popupMessageModal" tabindex="-1" aria-labelledby="popupMessageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="popupMessageModalLabel">Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="popupMessageContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        addLeaveRequestModal: new bootstrap.Modal(document.getElementById('addLeaveRequestModal'), { backdrop: true }),
        approveLeaveModal: new bootstrap.Modal(document.getElementById('approveLeaveModal'), { backdrop: true }),
        rejectLeaveModal: new bootstrap.Modal(document.getElementById('rejectLeaveModal'), { backdrop: true }),
        deleteLeaveModal: new bootstrap.Modal(document.getElementById('deleteLeaveModal'), { backdrop: true }),
        viewLeaveRequestModal: new bootstrap.Modal(document.getElementById('viewLeaveRequestModal'), { backdrop: true }),
        popupMessageModal: new bootstrap.Modal(document.getElementById('popupMessageModal'), { backdrop: true })
    };

    // Function to toggle End Date field visibility
    function toggleEndDateField() {
        const leaveType = $('#leave_type').val();
        const $endDateContainer = $('#end_date_container');
        const $endDateInput = $('#end_date');
        if (leaveType === 'half_day_first' || leaveType === 'half_day_second') {
            $endDateContainer.hide();
            $endDateInput.val('').prop('disabled', true); // Clear and disable to prevent submission
        } else {
            $endDateContainer.show();
            $endDateInput.prop('disabled', false);
        }
    }

    // Toggle End Date field on leave_type change
    $('#leave_type').on('change', toggleEndDateField);

    // Initialize End Date field visibility on modal open
    $('#addLeaveRequestModal').on('shown.bs.modal', function() {
        toggleEndDateField();
    });

    // Clear End Date when form is reset
    $('#addLeaveRequestForm').on('reset', function() {
        $('#end_date').val('').prop('disabled', false);
        $('#end_date_container').show();
    });

    // Function to clear modal backdrops
    function clearModalBackdrops() {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
        $('body').css('padding-right', '');
        $('.modal').each(function() {
            $(this).removeClass('show').css('display', 'none');
        });
    }

    // Toggle Filters
    $('#toggleFiltersBtn').on('click', function() {
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
            loadEmployees();
        }
    });

    // Function to format date
    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        return moment(dateStr).format('DD MMM YYYY');
    }

    // Client-side leave type formatting
    function formatLeaveType(leaveType) {
        const leaveTypes = {
            'full_day': 'Full Day',
            'half_day_first': 'Half Day (First Half)',
            'half_day_second': 'Half Day (Second Half)',
            'Sick': 'Sick Leave',
            'Maternity': 'Maternity Leave',
            'Unpaid': 'Unpaid Leave',
            'Paid': 'Paid Leave'
        };
        return leaveTypes[leaveType] || leaveType.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }

    // Function to load employees for the filter dropdown
    function loadEmployees() {
        if (!@json($isAdminOrAuthorized)) return;
        $.ajax({
            url: "{{ route('list.employees') }}",
            type: "GET",
            success: function(response) {
                if (response.success && Array.isArray(response.employees)) {
                    let options = '<option value="">All</option>';
                    response.employees.forEach(employee => {
                        // Construct full name from first_name, middle_name, and last_name
                        let fullName = employee.first_name;
                        if (employee.middle_name) {
                            fullName += ' ' + employee.middle_name;
                        }
                        fullName += ' ' + employee.last_name;
                        options += `<option value="${employee.id}" ${employee.id == $('#user_id').val() ? 'selected' : ''}>
                            ${fullName} ${employee.employee_id ? '(' + employee.employee_id + ')' : ''}
                        </option>`;
                    });
                    $('#user_id').html(options);
                } else {
                    $('#user_id').html('<option value="">No employees found</option>');
                }
            },
            error: function(xhr) {
                console.error("Load Employees Error:", xhr.responseText);
                $('#user_id').html('<option value="">Error loading employees</option>');
            }
        });
    }

    // Function to update a single leave request row
    function updateLeaveRequestRow(request) {
        let operations = `
            <div class="flex flex-wrap gap-1 justify-content-center">
                <button class="btn btn-sm btn-secondary viewLeaveBtn" data-id="${request.id}" title="View Details"><i class="fas fa-eye"></i></button>
        `;
        const userRole = @json(Auth::user()->role_id);
        const userId = @json(Auth::user()->id);
        if ((userRole === 3 && request.team_lead_status === 'Submitted') || (userRole === 7 && request.hr_status === 'Submitted')) {
            operations += `
                <button class="btn btn-sm btn-primary approveLeaveBtn" data-id="${request.id}" title="Approve"><i class="fas fa-check"></i></button>
                <button class="btn btn-sm btn-danger rejectLeaveBtn" data-id="${request.id}" title="Reject"><i class="fas fa-times"></i></button>
            `;
        }
        if (userRole === 1 || request.user_id === userId) {
            operations += `
                <button class="btn btn-sm btn-danger deleteLeaveBtn" data-id="${request.id}" title="Delete"><i class="fas fa-trash"></i></button>
            `;
        }
        operations += `</div>`;
        const rowHtml = `
            <tr id="leaveRequest_${request.id}" class="hover:bg-gray-50 transition duration-150" data-leave-request-id="${request.id}">
                <td class="px-4 py-2 whitespace-nowrap">${request.id}</td>
                <td class="px-4 py-2 whitespace-nowrap">${request.employee || 'N/A'}</td>
                <td class="px-4 py-2 whitespace-nowrap">${formatLeaveType(request.leave_type)}</td>
                <td class="px-4 py-2 whitespace-nowrap">${formatDate(request.start_date)}</td>
                <td class="px-4 py-2 whitespace-nowrap">${formatDate(request.end_date)}</td>
                <td class="px-4 py-2 whitespace-nowrap">${request.duration ? request.duration + ' days' : 'N/A'}</td>
                <td class="px-4 py-2 whitespace-nowrap">
                    <span class="badge ${
                        request.team_lead_status === 'Draft' ? 'badge-draft' :
                        request.team_lead_status === 'Submitted' ? 'badge-pending' :
                        request.team_lead_status === 'Approved' ? 'badge-completed' :
                        request.team_lead_status === 'Rejected' ? 'badge-hold' : 'badge-no-tasks'
                    } px-2 py-1 rounded">${request.team_lead_status ? request.team_lead_status : 'Submitted'}</span>
                </td>
                <td class="px-4 py-2 whitespace-nowrap">
                    <span class="badge ${
                        request.hr_status === 'Draft' ? 'badge-draft' :
                        request.hr_status === 'Submitted' ? 'badge-pending' :
                        request.hr_status === 'Approved' ? 'badge-completed' :
                        request.hr_status === 'Rejected' ? 'badge-hold' : 'badge-no-tasks'
                    } px-2 py-1 rounded">${request.hr_status ? request.hr_status : 'Submitted'}</span>
                </td>
                <td class="px-4 py-2 whitespace-nowrap">${operations}</td>
            </tr>
        `;
        $(`#leaveRequest_${request.id}`).replaceWith(rowHtml);
    }

    // Function to render leave requests table
    function renderLeaveRequestTable(leaveRequests) {
        let tableHtml = `
            <div class="bg-white rounded-lg shadow-md overflow-x-auto">
                <table class="table table-bordered w-full text-xs mb-0" id="leaveRequestsTable">
                    <thead class="bg-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="id">ID ${currentSortColumn === 'id' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="user_id">Employee ${currentSortColumn === 'user_id' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="leave_type">Leave Type ${currentSortColumn === 'leave_type' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="start_date">Start Date ${currentSortColumn === 'start_date' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="end_date">End Date ${currentSortColumn === 'end_date' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="duration">Duration ${currentSortColumn === 'duration' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="team_lead_status">Team Lead Status ${currentSortColumn === 'team_lead_status' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider sortable" data-column="hr_status">HR Status ${currentSortColumn === 'hr_status' ? (currentSortDirection === 'asc' ? '↑' : '↓') : ''}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 bg-gray-500 uppercase tracking-wider" style="min-width: 240px; text-align: center;">Operations</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
        `;
        if (leaveRequests.length === 0) {
            tableHtml += `
                <tr>
                    <td colspan="9" class="px-4 py-2 text-center text-gray-500 text-xs">
                        No leave requests found for the selected criteria.
                    </td>
                </tr>
            `;
        } else {
            leaveRequests.forEach(request => {
                let operations = `
                    <div class="flex flex-wrap gap-1 justify-content-center">
                        <button class="btn btn-sm btn-secondary viewLeaveBtn" data-id="${request.id}" title="View Details"><i class="fas fa-eye"></i></button>
                `;
                const userRole = @json(Auth::user()->role_id);
                const userId = @json(Auth::user()->id);
                if ((userRole === 3 && request.team_lead_status === 'Submitted') || (userRole === 7 && request.hr_status === 'Submitted')) {
                    operations += `
                        <button class="btn btn-sm btn-primary approveLeaveBtn" data-id="${request.id}" title="Approve"><i class="fas fa-check"></i></button>
                        <button class="btn btn-sm btn-danger rejectLeaveBtn" data-id="${request.id}" title="Reject"><i class="fas fa-times"></i></button>
                    `;
                }
                if (userRole === 1 || request.user_id === userId) {
                    operations += `
                        <button class="btn btn-sm btn-danger deleteLeaveBtn" data-id="${request.id}" title="Delete"><i class="fas fa-trash"></i></button>
                    `;
                }
                operations += `</div>`;
                tableHtml += `
                    <tr id="leaveRequest_${request.id}" class="hover:bg-gray-50 transition duration-150" data-leave-request-id="${request.id}">
                        <td class="px-4 py-2 whitespace-nowrap">${request.id}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${request.employee || 'N/A'}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${formatLeaveType(request.leave_type)}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${formatDate(request.start_date)}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${formatDate(request.end_date)}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${request.duration ? request.duration + ' days' : 'N/A'}</td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <span class="badge ${
                                request.team_lead_status === 'Draft' ? 'badge-draft' :
                                request.team_lead_status === 'Submitted' ? 'badge-pending' :
                                request.team_lead_status === 'Approved' ? 'badge-completed' :
                                request.team_lead_status === 'Rejected' ? 'badge-hold' : 'badge-no-tasks'
                            } px-2 py-1 rounded">${request.team_lead_status ? request.team_lead_status : 'Submitted'}</span>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <span class="badge ${
                                request.hr_status === 'Draft' ? 'badge-draft' :
                                request.hr_status === 'Submitted' ? 'badge-pending' :
                                request.hr_status === 'Approved' ? 'badge-completed' :
                                request.hr_status === 'Rejected' ? 'badge-hold' : 'badge-no-tasks'
                            } px-2 py-1 rounded">${request.hr_status ? request.hr_status : 'Submitted'}</span>
                        </td>
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

    // Function to load leave requests with filters, sort, and pagination
    function loadLeaveRequests(page = 1) {
        const params = {
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            status: $('#status').val(),
            user_id: $('#user_id').val(),
            sort_by: $('#sort_by').val().split('|')[0],
            sort_direction: $('#sort_by').val().split('|')[1],
            page: page,
            ajax: true
        };

        $('#loadingOverlay').show();
        $('#leaveRequestsContainer').hide();

        $.ajax({
            url: "{{ route('leave_requests.index') }}",
            type: "GET",
            data: params,
            success: function(response) {
                if (response.success && Array.isArray(response.leave_requests)) {
                    const html = renderLeaveRequestTable(response.leave_requests);
                    const pagination = renderPagination(response.pagination);
                    $('#leaveRequestsContainer').html(html + '<div id="paginationContainer">' + pagination + '</div>');
                    $('.page-link').on('click', function(e) {
                        e.preventDefault();
                        const page = $(this).data('page');
                        if (page) loadLeaveRequests(page);
                    });
                    $('.sortable').on('click', function() {
                        const column = $(this).data('column');
                        const newDirection = (currentSortColumn === column && currentSortDirection === 'asc') ? 'desc' : 'asc';
                        currentSortColumn = column;
                        currentSortDirection = newDirection;
                        $('#sort_by').val(`${column}|${newDirection}`);
                        loadLeaveRequests(1);
                    });
                } else {
                    $('#leaveRequestsContainer').html(`
                        <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs">
                            No leave requests found for the selected criteria.
                        </div>
                    `);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'No leave requests found or invalid data.',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                        }
                    });
                }
                $('#loadingOverlay').hide();
                $('#leaveRequestsContainer').show();
            },
            error: function(xhr) {
                console.error("Leave Request Load Error:", xhr.responseText);
                $('#leaveRequestsContainer').html(`
                    <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs">
                        No leave requests found for the selected criteria.
                    </div>
                `);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error loading leave requests: ' + (xhr.responseJSON?.message || 'Server error'),
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'rounded-lg',
                        confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                    }
                });
                $('#loadingOverlay').hide();
                $('#leaveRequestsContainer').show();
            }
        });
    }

    // Filter change handler
    $('#start_date, #end_date, #status, #user_id').on('change', function() {
        loadLeaveRequests(1);
    });

    // Sort change via dropdown
    $('#sort_by').on('change', function() {
        const sortBy = $(this).val().split('|');
        currentSortColumn = sortBy[0];
        currentSortDirection = sortBy[1];
        loadLeaveRequests(1);
    });

    // Show popup message
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

    // Create Leave Request
    $(document).on('submit', '#addLeaveRequestForm', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitButton = $form.find('button[type="submit"]');

        // Disable submit button to prevent multiple submissions
        $submitButton.prop('disabled', true).text('Submitting...');

        const formData = new FormData(this);
        $.ajax({
            url: "{{ route('leave_requests.store') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
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
                        modals.addLeaveRequestModal.hide();
                        $form[0].reset(); // Reset form
                        loadLeaveRequests();
                    });
                } else {
                    $submitButton.prop('disabled', false).text('Submit Request'); // Re-enable button on error
                    showPopup("Error creating leave request: " + response.message);
                }
            },
            error: function(xhr) {
                console.error("Create Leave Request Error:", xhr.responseText);
                $submitButton.prop('disabled', false).text('Submit Request'); // Re-enable button on error
                showPopup("Error creating leave request: " + (xhr.responseJSON?.message || "Server error"));
            }
        });
    });

    // View Leave Request Details
    $(document).on('click', '.viewLeaveBtn', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        if (!id || isNaN(id)) {
            showPopup('Invalid leave request ID.');
            return;
        }

        $.ajax({
            url: "{{ url('leave-requests') }}/" + id,
            type: "GET",
            success: function(response) {
                if (response.success) {
                    $('#detailEmployee').text(response.data.employee || 'N/A');
                    $('#detailLeaveType').text(response.data.leave_type ? formatLeaveType(response.data.leave_type) : 'N/A');
                    $('#detailStartDate').text(formatDate(response.data.start_date));
                    $('#detailEndDate').text(formatDate(response.data.end_date));
                    $('#detailDuration').text(response.data.duration ? response.data.duration + ' days' : 'N/A');
                    $('#detailReason').text(response.data.reason || 'N/A');
                    $('#detailTeamLeadStatus').text(response.data.team_lead_status || 'Submitted');
                    $('#detailHrStatus').text(response.data.hr_status || 'Submitted');
                    $('#detailTeamLeadApprover').text(response.data.team_lead_approver || 'N/A');
                    $('#detailHrApprover').text(response.data.hr_approver || 'N/A');
                    $('#detailTeamLeadApprovedAt').text(response.data.team_lead_approved_at ? formatDate(response.data.team_lead_approved_at) : 'N/A');
                    $('#detailHrApprovedAt').text(response.data.hr_approved_at ? formatDate(response.data.hr_approved_at) : 'N/A');
                    $('#detailTeamLeadComments').text(response.data.team_lead_comments || 'N/A');
                    $('#detailHrComments').text(response.data.hr_comments || 'N/A');
                    modals.viewLeaveRequestModal.show();
                } else {
                    showPopup("Error loading leave request details: " + response.message);
                }
            },
            error: function(xhr) {
                console.error("View Leave Error:", xhr.responseText);
                showPopup("Error loading leave request details: " + (xhr.responseJSON?.message || "Server error"));
            }
        });
    });

    // Approve Leave Request
    $(document).on('click', '.approveLeaveBtn', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        if (!id || isNaN(id)) {
            showPopup('Invalid leave request ID.');
            return;
        }
        $('#approveLeaveRequestId').val(id);
        modals.approveLeaveModal.show();
    });

    $(document).on('submit', '#approveLeaveForm', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const id = $('#approveLeaveRequestId').val();

        $.ajax({
            url: "{{ url('leave-requests') }}/" + id + "/approve",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    modals.approveLeaveModal.hide();
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
                        $.ajax({
                            url: "{{ url('leave-requests') }}/" + id,
                            type: "GET",
                            success: function(detailResponse) {
                                if (detailResponse.success) {
                                    updateLeaveRequestRow({
                                        id: detailResponse.data.id,
                                        employee: detailResponse.data.employee || 'N/A',
                                        leave_type: detailResponse.data.leave_type,
                                        start_date: detailResponse.data.start_date,
                                        end_date: detailResponse.data.end_date,
                                        duration: detailResponse.data.duration,
                                        reason: detailResponse.data.reason,
                                        team_lead_status: detailResponse.data.team_lead_status,
                                        hr_status: detailResponse.data.hr_status
                                    });
                                    loadLeaveRequests();
                                }
                            },
                            error: function(xhr) {
                                console.error("Fetch Updated Leave Error:", xhr.responseText);
                                showPopup("Error fetching updated leave request: " + (xhr.responseJSON?.message || "Server error"));
                            }
                        });
                    });
                } else {
                    showPopup("Error approving leave request: " + response.message);
                }
            },
            error: function(xhr) {
                console.error("Approve Leave Error:", xhr.responseText);
                showPopup("Error approving leave request: " + (xhr.responseJSON?.message || "Server error"));
            }
        });
    });

    // Reject Leave Request
    $(document).on('click', '.rejectLeaveBtn', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        if (!id || isNaN(id)) {
            showPopup('Invalid leave request ID.');
            return;
        }
        $('#rejectLeaveRequestId').val(id);
        modals.rejectLeaveModal.show();
    });

    $(document).on('submit', '#rejectLeaveForm', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const id = $('#rejectLeaveRequestId').val();

        $.ajax({
            url: "{{ url('leave-requests') }}/" + id + "/reject",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    modals.rejectLeaveModal.hide();
                    clearModalBackdrops();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700',
                        }
                    }).then(() => {
                        $.ajax({
                            url: "{{ url('leave-requests') }}/" + id,
                            type: "GET",
                            success: function(detailResponse) {
                                if (detailResponse.success) {
                                    updateLeaveRequestRow({
                                        id: detailResponse.data.id,
                                        employee: detailResponse.data.employee || 'N/A',
                                        leave_type: detailResponse.data.leave_type,
                                        start_date: detailResponse.data.start_date,
                                        end_date: detailResponse.data.end_date,
                                        duration: detailResponse.data.duration,
                                        reason: detailResponse.data.reason,
                                        team_lead_status: detailResponse.data.team_lead_status,
                                        hr_status: detailResponse.data.hr_status
                                    });
                                    loadLeaveRequests();
                                }
                            },
                            error: function(xhr) {
                                console.error("Fetch Updated Leave Error:", xhr.responseText);
                                showPopup("Error fetching updated leave request: " + (xhr.responseJSON?.message || "Server error"));
                            }
                        });
                    });
                } else {
                    showPopup("Error rejecting leave request: " + response.message);
                }
            },
            error: function(xhr) {
                console.error("Reject Leave Error:", xhr.responseText);
                showPopup("Error rejecting leave request: " + (xhr.responseJSON?.message || "Server error"));
            }
        });
    });

    // Delete Leave Request
    $(document).on('click', '.deleteLeaveBtn', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        if (!id || isNaN(id)) {
            showPopup('Invalid leave request ID.');
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) { // Changed from result.value to result.isConfirmed for SweetAlert2
                $('#deleteLeaveRequestId').val(id);
                $('#deleteLeaveForm').submit();
            }
        });
    });

    $(document).on('submit', '#deleteLeaveForm', function(e) {
        e.preventDefault();
        const id = $('#deleteLeaveRequestId').val();
        const token = $('meta[name="csrf-token"]').attr('content') || $('#deleteLeaveForm input[name="_token"]').val();

        $.ajax({
            url: "{{ url('leave-requests') }}/" + id,
            type: "DELETE",
            headers: {
                'X-CSRF-TOKEN': token
            },
            success: function(response) {
                if (response.success) {
                    modals.deleteLeaveModal.hide();
                    clearModalBackdrops();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Leave request deleted successfully.',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                        }
                    }).then(() => {
                        loadLeaveRequests();
                    });
                } else {
                    showPopup("Error deleting leave request: " + (response.message || "Server error"));
                }
            },
            error: function(xhr) {
                console.error("Delete Leave Error:", xhr.responseText);
                showPopup("Error deleting leave request: " + (xhr.responseJSON?.message || "Server error"));
            }
        });
    });

    // Initial load
    loadLeaveRequests();
    if (@json($isAdminOrAuthorized)) {
        loadEmployees();
    }
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
    #leaveRequestsContainer {
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
    .badge-completed {
        background-color: #86efac;
        color: #1f2937;
    }
    .badge-hold {
        background-color: #f87171;
        color: #ffffff;
    }
    .badge-draft {
        background-color: #d1d5db;
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
@endsection
