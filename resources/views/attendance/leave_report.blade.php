@extends('layouts.app')

@section('content')
<style>
    /* Modern HRMS design with enhanced leave details */
    * {
        box-sizing: border-box;
    }

    .nk-content {
        background: #fafbfc;
        min-height: 100vh;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 24px;
    }

    /* Clean Header (No Logo) */
    .page-header {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin: 24px 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        border: 1px solid #e5e7eb;
    }

    .page-title {
        font-size: 22px;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 6px 0;
        letter-spacing: -0.025em;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 14px;
        margin: 0;
        font-weight: 400;
    }

    /* Clean Filter Form */
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin: 24px 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        border: 1px solid #e5e7eb;
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-label {
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
    }

    .form-control {
        height: 40px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 0 12px;
        font-size: 14px;
        background: white;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .btn {
        height: 40px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
        padding: 0 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-primary {
        background: #3b82f6;
        color: white;
    }

    .btn-primary:hover {
        background: #2563eb;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #6b7280;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    /* Main Table with Specific Column Widths */
    .table-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        border: 1px solid #e5e7eb;
    }

    .table-responsive {
        overflow-x: auto;
        width: 100%;
    }

    #leave-report-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin: 0;
        table-layout: fixed;
    }

    /* Specific column widths */
    #leave-report-table th:nth-child(1), #leave-report-table td:nth-child(1) { width: 50px; max-width: 50px; text-align: center; padding: 12px 4px; }
    #leave-report-table th:nth-child(2), #leave-report-table td:nth-child(2) { width: 180px; max-width: 180px; text-align: left; padding: 12px 8px; }
    #leave-report-table th:nth-child(3), #leave-report-table td:nth-child(3) { width: 90px; max-width: 90px; text-align: center; padding: 12px 6px; }
    #leave-report-table th:nth-child(4), #leave-report-table td:nth-child(4) { width: 120px; max-width: 120px; text-align: left; padding: 12px 8px; }
    #leave-report-table th:nth-child(5), #leave-report-table td:nth-child(5) { width: 100px; max-width: 100px; text-align: left; padding: 12px 8px; }
    #leave-report-table th:nth-child(6), #leave-report-table td:nth-child(6) { width: 200px; max-width: 200px; text-align: left; padding: 12px 8px; }
    #leave-report-table th:nth-child(7), #leave-report-table td:nth-child(7) { width: 70px; max-width: 70px; text-align: center; padding: 12px 6px; }
    #leave-report-table th:nth-child(8), #leave-report-table td:nth-child(8) { width: 70px; max-width: 70px; text-align: center; padding: 12px 6px; }
    #leave-report-table th:nth-child(9), #leave-report-table td:nth-child(9) { width: 80px; max-width: 80px; text-align: center; padding: 12px 6px; }

    #leave-report-table th, #leave-report-table td {
        border-bottom: 1px solid #f1f5f9;
        color: #374151;
        font-size: 13px;
        vertical-align: middle;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #leave-report-table thead th {
        background: #f9fafb;
        color: #374151;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    #leave-report-table tbody tr {
        transition: background 0.15s ease;
    }

    #leave-report-table tbody tr:hover {
        background: #f8fafc;
    }

    .dt-control {
        width: 28px;
        height: 28px;
        background: #f3f4f6;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #6b7280;
        font-size: 14px;
        font-weight: 600;
    }

    .dt-control:hover {
        background: #e5e7eb;
        color: #374151;
    }

    .dt-control::before {
        content: '+';
        line-height: 1;
    }

    .dt-control.opened::before {
        content: '−';
    }

    .child-row {
        background: #fafbfc !important;
        border: none !important;
        width: 1% !important;
    }

    .child-row td {
        padding: 0 !important;
        border: none !important;
        width: 1% !important;
        max-width: none !important;
        white-space: normal !important;
    }

    .detail-wrapper {
        width: 100%;
        min-width: 100%;
        margin: 0;
        background: white;
        border-radius: 0;
        overflow: hidden;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
        display: block;
    }

    .employee-header {
        background: #f8fafc;
        padding: 24px;
        border-bottom: 1px solid #e5e7eb;
        border-top: 2px solid #3b82f6;
        width: 100%;
    }

    .employee-name {
        font-size: 20px;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 16px 0;
        letter-spacing: -0.025em;
        text-align: left;
    }

    .employee-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        width: 100%;
    }

    .meta-item {
        background: white;
        padding: 14px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
    }

    .meta-label {
        font-size: 10px;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 4px;
        display: block;
        text-align: left;
    }

    .meta-value {
        font-size: 14px;
        color: #1f2937;
        font-weight: 600;
        line-height: 1.2;
        text-align: left;
    }

    /* Stats Grid WITHOUT Total Breaks */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        background: white;
        border-bottom: 1px solid #e5e7eb;
        width: 100%;
    }

    .stat-item {
        padding: 20px 12px;
        text-align: center;
        border-right: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .stat-item:last-child {
        border-right: none;
    }

    .stat-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 40px;
        height: 2px;
        background: #3b82f6;
        border-radius: 0 0 2px 2px;
    }

    .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 4px;
        line-height: 1;
    }

    .stat-label {
        font-size: 10px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 500;
    }

    /* Enhanced Leave Details Design */
    .leave-details {
        background: #f8fafc;
        padding: 24px;
        border-bottom: 1px solid #e5e7eb;
        width: 100%;
    }

    .leave-section {
        margin-bottom: 20px;
    }

    .leave-section:last-child {
        margin-bottom: 0;
    }

    .leave-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }

    .leave-title {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin: 0;
    }

    .leave-count-badge {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        font-size: 10px;
        padding: 4px 8px;
        border-radius: 12px;
        font-weight: bold;
        min-width: 20px;
        text-align: center;
    }

    .leave-count-badge.leave { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .leave-count-badge.wfh { background: linear-gradient(135deg, #06b6d4, #0891b2); }
    .leave-count-badge.half-day { background: linear-gradient(135deg, #f59e0b, #d97706); }

    .leave-dates-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 8px;
        margin-top: 8px;
    }

    .leave-date-card {
        background: white;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 11px;
        color: #374151;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .leave-date-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: #3b82f6;
    }

    .leave-date-card.leave::before { background: #ef4444; }
    .leave-date-card.wfh::before { background: #06b6d4; }
    .leave-date-card.half-day::before { background: #f59e0b; }

    .leave-date-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .leave-date-main {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 2px;
    }

    .leave-date-type {
        font-size: 9px;
        color: #6b7280;
        text-transform: uppercase;
        font-weight: 500;
        letter-spacing: 0.05em;
    }

    /* Detail Table */
    .detail-section {
        background: white;
        padding: 0;
        width: 100%;
        overflow: hidden;
    }

    .detail-table-header {
        background: #f9fafb;
        padding: 16px 24px;
        border-bottom: 1px solid #e5e7eb;
        width: 100%;
    }

    .detail-table-title {
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        text-align: left;
    }

    .detail-table-wrapper {
        max-height: 500px;
        overflow-y: auto;
        overflow-x: auto;
        margin: 0;
        width: 100%;
    }

    .detail-table {
        width: 100%;
        min-width: 800px;
        border-collapse: separate;
        border-spacing: 0 8px;
        margin: 0;
        padding: 12px;
        table-layout: auto;
    }

    .detail-table thead th {
        background: #f9fafb;
        padding: 14px 10px;
        border-bottom: 2px solid #e5e7eb;
        font-size: 11px;
        font-weight: 600;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-align: left;
        vertical-align: middle;
        position: sticky;
        top: 0;
        z-index: 10;
        white-space: nowrap;
    }

    .detail-table tbody td {
        padding: 14px 10px;
        background: white;
        font-size: 12px;
        color: #374151;
        vertical-align: middle;
        text-align: left;
        white-space: nowrap;
        border: none;
    }

    .detail-table tbody tr {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
        transition: all 0.2s ease;
    }

    .detail-table tbody tr:hover {
        background: #f8fafc;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
    }

    .detail-table tbody td:first-child {
        border-radius: 8px 0 0 8px;
    }

    .detail-table tbody td:last-child {
        border-radius: 0 8px 8px 0;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
        vertical-align: middle;
    }

    .status-present { background: #10b981; }
    .status-late { background: #f59e0b; }
    .status-early { background: #ef4444; }
    .status-overtime { background: #3b82f6; }
    .status-leave { background: #8b5cf6; }
    .status-absent { background: #ef4444; }
    .status-wfh { background: #06b6d4; }

    .badge {
        font-size: 9px;
        padding: 3px 6px;
        border-radius: 4px;
        font-weight: 600;
        margin-left: 6px;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        vertical-align: middle;
        display: inline-block;
    }

    .badge-late { background: #fef3c7; color: #92400e; }
    .badge-early { background: #fee2e2; color: #991b1b; }
    .badge-overtime { background: #dbeafe; color: #1e40af; }

    .download-btn {
        background: #10b981;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 12px 20px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        margin: 20px 24px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
    }

    .download-btn:hover {
        background: #059669;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
    }

    .download-btn svg {
        width: 14px;
        height: 14px;
    }

    .dataTables_wrapper {
        padding: 16px;
        overflow: hidden;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 16px;
    }

    .dataTables_wrapper .dataTables_length { float: left; }
    .dataTables_wrapper .dataTables_filter { float: right; }

    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 6px 10px;
        margin-left: 6px;
        width: 180px;
        font-size: 13px;
    }

    .dataTables_wrapper .dt-buttons {
        float: right;
        margin-left: 10px;
        margin-bottom: 12px;
    }

    .dataTables_wrapper .dt-buttons .btn {
        background: white;
        border: 1px solid #d1d5db;
        color: #374151;
        border-radius: 6px;
        padding: 6px 12px;
        margin-right: 6px;
        font-size: 12px;
    }

    .dataTables_wrapper .dt-buttons .btn:hover {
        background: #f3f4f6;
    }

    .loading-state {
        padding: 40px 24px;
        text-align: center;
        color: #6b7280;
    }

    .loading-state .spinner {
        width: 24px;
        height: 24px;
        border: 2px solid #e5e7eb;
        border-radius: 50%;
        border-top-color: #3b82f6;
        animation: spin 1s ease-in-out infinite;
        margin: 0 auto 12px;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    @media (max-width: 1200px) {
        .filter-row { grid-template-columns: repeat(3, 1fr); }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .employee-meta { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 768px) {
        .filter-row, .stats-grid, .employee-meta { grid-template-columns: 1fr; }
        .employee-header { padding: 16px; }
        .employee-name { font-size: 18px; }
        .download-btn { margin: 16px; padding: 10px 16px; }

        .table-responsive,
        .detail-table-wrapper {
            overflow-x: auto;
        }
    }
</style>

<div class="nk-content">
    <div class="container">
        <!-- Simple Header without Logo -->
        <div class="page-header">
            <h1 class="page-title">Attendance Report</h1>
            <p class="page-subtitle">View detailed attendance records and employee performance metrics</p>
        </div>

        <!-- Clean Filter Form -->
        <div class="filter-card">
            <form method="GET" action="{{ route('attendance.attendanceReport') }}">
                <div class="filter-row">
                    <div class="form-group">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="department_id" class="form-label">Department</label>
                        <select name="department_id" id="department_id" class="form-control">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ $departmentId == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="role_id" class="form-label">Role</label>
                        <select name="role_id" id="role_id" class="form-control">
                            <option value="">All Roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ $roleId == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="staff_id" class="form-label">Employee</label>
                        <select name="staff_id" id="staff_id" class="form-control">
                            <option value="">All Employees</option>
                            @foreach($activeEmployees as $emp)
                                <option value="{{ $emp->id }}" {{ $staffId == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->first_name }} {{ $emp->middle_name ? $emp->middle_name . ' ' : '' }} {{ $emp->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('attendance.attendanceReport') }}" class="btn btn-secondary">Clear Filters</a>
                </div>
            </form>
        </div>

        <!-- Table with Fixed Column Widths -->
        <div class="table-card">
            <div class="table-responsive">
                <table id="leave-report-table" class="table" data-export-title="{{ $exportTitle }}">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Employee</th>
                            <th>ID</th>
                            <th>Department</th>
                            <th>Role</th>
                            <th>Email</th>
                            <th>Leave</th>
                            <th>WFH</th>
                            <th>Half Day</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $employee)
                            <tr data-employee-id="{{ $employee->id }}">
                                <td><span class="dt-control"></span></td>
                                <td><strong>{{ $employee->first_name }} {{ $employee->middle_name ? $employee->middle_name . ' ' : '' }} {{ $employee->last_name }}</strong></td>
                                <td>{{ $employee->employee_id }}</td>
                                <td>{{ $employee->department ?? 'N/A' }}</td>
                                <td>{{ $employee->role ?? 'N/A' }}</td>
                                <td>{{ $employee->email }}</td>
                                <td>{{ $employee->leave_count }}</td>
                                <td>{{ $employee->wfh_count }}</td>
                                <td>{{ $employee->half_day_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 30px; color: #6b7280;">
                                    No records found for the selected criteria
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
$(document).ready(function() {
    let table = $('#leave-report-table');
    if ($.fn.DataTable.isDataTable('#leave-report-table')) {
        table.DataTable().destroy();
    }

    var dataTable = table.DataTable({
        dom: 'lfBrtip',
        destroy: true,
        autoWidth: false,
        scrollX: false,
        pageLength: 25,
        columnDefs: [
            { className: "text-center", orderable: false, targets: 0 },
            { targets: [2, 6, 7, 8], className: "text-center" }
        ],
        order: [[1, 'asc']],
        buttons: [
            {
                extend: 'csv',
                title: 'ZENEROM - {{ $exportTitle }}',
                exportOptions: { columns: ':visible:not(:first-child)' },
                className: 'btn'
            },
            {
                extend: 'excel',
                title: 'ZENEROM - {{ $exportTitle }}',
                exportOptions: { columns: ':visible:not(:first-child)' },
                className: 'btn'
            },
            {
                extend: 'pdf',
                title: 'ZENEROM - {{ $exportTitle }}',
                exportOptions: { columns: ':visible:not(:first-child)' },
                className: 'btn'
            }
        ]
    });

    $('#leave-report-table tbody').on('click', '.dt-control', function() {
        var tr = $(this).closest('tr');
        var row = dataTable.row(tr);
        var employeeId = tr.data('employee-id');

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            $(this).removeClass('opened');
        } else {
            $(this).addClass('opened');
            showEmployeeDetails(row, employeeId);
            tr.addClass('shown');
        }
    });

    function showEmployeeDetails(row, employeeId) {
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();

        var loadingHtml = '<td colspan="9" class="child-row">';
        loadingHtml += '<div class="loading-state">';
        loadingHtml += '<div class="spinner"></div>';
        loadingHtml += '<p>Loading detailed attendance information...</p>';
        loadingHtml += '</div>';
        loadingHtml += '</td>';

        row.child(loadingHtml).show();

        $.ajax({
            url: '{{ route("attendance.employeeDetails", ":id") }}'.replace(':id', employeeId),
            method: 'GET',
            data: { start_date: startDate, end_date: endDate },
            success: function(response) {
                var detailHtml = formatEmployeeDetails(response, employeeId, startDate, endDate);
                row.child(detailHtml).show();
            },
            error: function(xhr) {
                var errorHtml = '<td colspan="9" class="child-row">';
                errorHtml += '<div style="padding: 30px; text-align: center; color: #ef4444;">';
                errorHtml += '<strong>Error loading attendance details.</strong><br>Please try again.';
                errorHtml += '</div>';
                errorHtml += '</td>';
                row.child(errorHtml).show();
            }
        });
    }

    function formatDuration(totalSeconds) {
        if (totalSeconds === 0 || totalSeconds === '-' || !totalSeconds) {
            return '-';
        }

        var hours = Math.floor(totalSeconds / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);

        var parts = [];
        if (hours > 0) parts.push(hours + 'h');
        if (minutes > 0) parts.push(minutes + 'm');

        return parts.length > 0 ? parts.join(' ') : '0m';
    }

    function formatEmployeeDetails(data, employeeId, startDate, endDate) {
        var html = '<td colspan="9" class="child-row">';
        html += '<div class="detail-wrapper">';

        // Employee Header
        html += '<div class="employee-header">';
        html += '<div class="employee-name">' + data.employee.name + '</div>';
        html += '<div class="employee-meta">';
        html += '<div class="meta-item">';
        html += '<span class="meta-label">Employee ID</span>';
        html += '<span class="meta-value">' + data.employee.employee_id + '</span>';
        html += '</div>';
        html += '<div class="meta-item">';
        html += '<span class="meta-label">Department</span>';
        html += '<span class="meta-value">' + data.employee.department + '</span>';
        html += '</div>';
        html += '<div class="meta-item">';
        html += '<span class="meta-label">Role</span>';
        html += '<span class="meta-value">' + data.employee.role + '</span>';
        html += '</div>';
        html += '<div class="meta-item">';
        html += '<span class="meta-label">Work Schedule</span>';
        html += '<span class="meta-value">' + data.employee.work_schedule + '</span>';
        html += '</div>';
        html += '</div>';
        html += '</div>';

        // Stats Grid WITHOUT Total Break Count
        html += '<div class="stats-grid">';
        html += '<div class="stat-item">';
        html += '<div class="stat-value">' + data.summary.present_days + '/' + data.summary.total_days + '</div>';
        html += '<div class="stat-label">Present Days</div>';
        html += '</div>';
        html += '<div class="stat-item">';
        html += '<div class="stat-value">' + data.summary.late_entries + '</div>';
        html += '<div class="stat-label">Late Entries</div>';
        html += '</div>';
        html += '<div class="stat-item">';
        html += '<div class="stat-value">' + data.summary.early_exits + '</div>';
        html += '<div class="stat-label">Early Exits</div>';
        html += '</div>';
        html += '<div class="stat-item">';
        html += '<div class="stat-value">' + formatDuration(data.summary.total_overtime_hours * 3600) + '</div>';
        html += '<div class="stat-label">Overtime Hours</div>';
        html += '</div>';
        html += '<div class="stat-item">';
        html += '<div class="stat-value">' + (data.summary.leave_count || 0) + '</div>';
        html += '<div class="stat-label">Leave Days</div>';
        html += '</div>';
        html += '<div class="stat-item">';
        html += '<div class="stat-value">' + (data.summary.wfh_count || 0) + '</div>';
        html += '<div class="stat-label">WFH Days</div>';
        html += '</div>';
        html += '<div class="stat-item">';
        html += '<div class="stat-value">' + (data.summary.half_day_count || 0) + '</div>';
        html += '<div class="stat-label">Half Days</div>';
        html += '</div>';
        html += '</div>';

        // Enhanced Leave Details Section
        var leaveData = data.leave_data || {};
        if ((leaveData.leave_dates && leaveData.leave_dates.length > 0) ||
            (leaveData.wfh_dates && leaveData.wfh_dates.length > 0) ||
            (leaveData.half_day_dates && leaveData.half_day_dates.length > 0)) {

            html += '<div class="leave-details">';

            if (leaveData.leave_dates && leaveData.leave_dates.length > 0) {
                html += '<div class="leave-section">';
                html += '<div class="leave-header">';
                html += '<h4 class="leave-title">Leave Days</h4>';
                html += '<span class="leave-count-badge leave">' + leaveData.leave_dates.length + '</span>';
                html += '</div>';
                html += '<div class="leave-dates-grid">';
                leaveData.leave_dates.forEach(function(date) {
                    html += '<div class="leave-date-card leave">';
                    html += '<div class="leave-date-main">' + date.replace(' (Absent)', '') + '</div>';
                    if (date.includes('(Absent)')) {
                        html += '<div class="leave-date-type">Absent</div>';
                    } else {
                        html += '<div class="leave-date-type">Leave</div>';
                    }
                    html += '</div>';
                });
                html += '</div>';
                html += '</div>';
            }

            if (leaveData.wfh_dates && leaveData.wfh_dates.length > 0) {
                html += '<div class="leave-section">';
                html += '<div class="leave-header">';
                html += '<h4 class="leave-title">Work From Home Days</h4>';
                html += '<span class="leave-count-badge wfh">' + leaveData.wfh_dates.length + '</span>';
                html += '</div>';
                html += '<div class="leave-dates-grid">';
                leaveData.wfh_dates.forEach(function(date) {
                    html += '<div class="leave-date-card wfh">';
                    html += '<div class="leave-date-main">' + date + '</div>';
                    html += '<div class="leave-date-type">Work From Home</div>';
                    html += '</div>';
                });
                html += '</div>';
                html += '</div>';
            }

            if (leaveData.half_day_dates && leaveData.half_day_dates.length > 0) {
                html += '<div class="leave-section">';
                html += '<div class="leave-header">';
                html += '<h4 class="leave-title">Half Days</h4>';
                html += '<span class="leave-count-badge half-day">' + leaveData.half_day_dates.length + '</span>';
                html += '</div>';
                html += '<div class="leave-dates-grid">';
                leaveData.half_day_dates.forEach(function(date) {
                    html += '<div class="leave-date-card half-day">';
                    html += '<div class="leave-date-main">' + date + '</div>';
                    html += '<div class="leave-date-type">Half Day</div>';
                    html += '</div>';
                });
                html += '</div>';
                html += '</div>';
            }

            html += '</div>';
        }

        // Download Button
        html += '<button class="download-btn" onclick="downloadIndividualReport(' + employeeId + ', \'' + startDate + '\', \'' + endDate + '\', \'' + data.employee.name + '\')">';
        html += '<svg fill="currentColor" viewBox="0 0 16 16"><path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293V6.5z"/><path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/></svg>';
        html += 'Download Report';
        html += '</button>';

        // Daily Details Table
        html += '<div class="detail-section">';
        html += '<div class="detail-table-header">';
        html += '<h3 class="detail-table-title">Daily Attendance Details</h3>';
        html += '</div>';
        html += '<div class="detail-table-wrapper">';
        html += '<table class="detail-table">';
        html += '<thead>';
        html += '<tr>';
        html += '<th>Date</th><th>Day</th><th>Login</th><th>Logout</th><th>Mode</th><th>Hours</th><th>Breaks</th><th>Status</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';

        data.records.forEach(function(record) {
            html += '<tr>';
            html += '<td><strong>' + record.date + '</strong></td>';
            html += '<td>' + record.day + '</td>';
            html += '<td><strong>' + record.login_time + '</strong>';
            if (record.late_entry) {
                html += '<span class="badge badge-late">+' + record.late_minutes + 'm</span>';
            }
            html += '</td>';
            html += '<td><strong>' + record.logout_time + '</strong>';
            if (record.early_exit) {
                html += '<span class="badge badge-early">-' + record.early_minutes + 'm</span>';
            }
            html += '</td>';
            html += '<td>' + record.mode + '</td>';
            html += '<td><strong>' + record.total_work_hours + '</strong>';
            if (record.overtime_hours > 0) {
                html += '<span class="badge badge-overtime">+' + formatDuration(record.overtime_hours * 3600) + '</span>';
            }
            html += '</td>';
            html += '<td>' + record.total_break_time + ' <small>(' + record.breaks_count + ')</small></td>';
            html += '<td><span class="status-dot ' + record.status_class + '"></span>' + getStatusText(record) + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div></div>';
        html += '</div>';
        html += '</td>';

        return html;
    }

    function getStatusText(record) {
        var status = [];
        if (record.late_entry) status.push('Late');
        if (record.early_exit) status.push('Early Exit');
        if (record.overtime_hours > 0) status.push('Overtime');
        return status.length === 0 ? record.mode : status.join(', ');
    }
});

// Fixed PDF generation with proper cardWidth declaration
function downloadIndividualReport(employeeId, startDate, endDate, employeeName) {
    var button = event.target;
    var originalText = button.innerHTML;
    button.innerHTML = '<div class="spinner" style="width: 12px; height: 12px; margin-right: 6px;"></div>Generating...';
    button.disabled = true;

    $.ajax({
        url: '{{ route("attendance.employeeDetails", ":id") }}'.replace(':id', employeeId),
        method: 'GET',
        data: { start_date: startDate, end_date: endDate },
        success: function(response) {
            generateCorporatePDF(response, employeeName, startDate, endDate);
            button.innerHTML = originalText;
            button.disabled = false;
        },
        error: function() {
            alert('Error generating report. Please try again.');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    });
}

function generateCorporatePDF(data, employeeName, startDate, endDate) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Minimal Header Design
    doc.setFillColor(12, 2, 56);
    doc.rect(0, 0, 210, 30, 'F');

    // Logo with your requested width
    const logoBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAADa4AAANFCAYAAAAp84o1AAAACXBIWXMAAC4jAAAuIwF4pT92AAAJBWlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS42LWMxNDUgNzkuMTYzNDk5LCAyMDE4LzA4LzEzLTE2OjQwOjIyICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdEV2dD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlRXZlbnQjIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIiB4bWxuczpwZGY9Imh0dHA6Ly9ucy5hZG9iZS5jb20vcGRmLzEuMy8iIHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyIgeG1wOk1vZGlmeURhdGU9IjIwMjItMDItMjRUMTc6MTc6MjgrMDU6MzAiIHhtcDpDcmVhdGVEYXRlPSIyMDIyLTAxLTA0VDIwOjM3OjE5KzA1OjMwIiB4bXA6TWV0YWRhdGFEYXRlPSIyMDIyLTAyLTI0VDE3OjE3OjI4KzA1OjMwIiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIElsbHVzdHJhdG9yIDEuMCAoaU9TKSIgeG1wTU06RG9jdW1lbnRJRD0iYWRvYmU6ZG9jaWQ6cGhvdG9zaG9wOmY1N2RhZGM1LWUzOGMtYmQ0Yi1iN2Q0LTkxMDA5M2Y0MWFhZSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDpkMmU0NjEwNC1lMGZhLWUyNDEtODkzMS0xNjc5NzkzYjc2ZjciIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0idXVpZDozZjEyYWQ3YS1iYzY1LTRmNDAtOTE2My1iZjI1ZDQzOWNjZGUiIGRjOmZvcm1hdD0iaW1hZ2UvcG5nIiBwZGY6UHJvZHVjZXI9IkFkb2JlIFBERiBsaWJyYXJ5IDE2LjAzIiBwaG90b3Nob3A6Q29sb3JNb2RlPSIzIiBwaG90b3Nob3A6SUNDUHJvZmlsZT0ic1JHQiBJRUM2MTk2Ni0yLjEiPiA8eG1wTU06SGlzdG9yeT4gPHJkZjpTZXE+IDxyZGY6bGkgc3RFdnQ6YWN0aW9uPSJjb252ZXJ0ZWQiIHN0RXZ0OnBhcmFtZXRlcnM9ImZyb20gYXBwbGljYXRpb24vcGRmIHRvIGFwcGxpY2F0aW9uL3ZuZC5hZG9iZS5waG90b3Nob3AiLz4gPHJkZjpsaSBzdEV2dDphY3Rpb249InNhdmVkIiBzdEV2dDppbnN0YW5jZUlEPSJ4bXAuaWlkOmYwMWViZTFhLTg1NjMtNzM0NC05YTE5LWM5M2FiZDJmMmMzMiIgc3RFdnQ6d2hlbj0iMjAyMi0wMi0yNFQxNzoxNzoyOCswNTozMCIgc3RFdnQ6c29mdHdhcmVBZ2VudD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTkgKFdpbmRvd3MpIiBzdEV2dDpjaGFuZ2VkPSIvIi8+IDxyZGY6bGkgc3RFdnQ6YWN0aW9uPSJjb252ZXJ0ZWQiIHN0RXZ0OnBhcmFtZXRlcnM9ImZyb20gYXBwbGljYXRpb24vcGRmIHRvIGltYWdlL3BuZyIvPiA8cmRmOmxpIHN0RXZ0OmFjdGlvbj0iZGVyaXZlZCIgc3RFdnQ6cGFyYW1ldGVycz0iY29udmVydGVkIGZyb20gYXBwbGljYXRpb24vdm5kLmFkb2JlLnBob3Rvc2hvcCB0byBpbWFnZS9wbmciLz4gPHJkZjpsaSBzdEV2dDphY3Rpb249InNhdmVkIiBzdEV2dDppbnN0YW5jZUlEPSJ4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';
    try {
        doc.addImage(logoBase64, 'PNG', 15, 8, 45, 12); // Your requested size: 45x12
    } catch (e) {
        // Minimal fallback
        doc.setFillColor(255, 255, 255);
        doc.circle(22.5, 14, 6, 'F');
        doc.setTextColor(12, 2, 56);
        doc.setFontSize(10);
        doc.setFont(undefined, 'bold');
        doc.text('Z', 22.5, 17, { align: 'center' });
    }

    // Compact header text
    doc.setFontSize(16);
    doc.setFont(undefined, 'bold');
    doc.setTextColor(255, 255, 255);
    doc.text('ZENEROM HRM', 105, 14, { align: 'center' });

    doc.setFontSize(10);
    doc.setFont(undefined, 'normal');
    doc.text('Individual Attendance Report', 105, 22, { align: 'center' });

    // Combined Employee Info + Attendance Summary
    let yPos = 42;

    // Employee Info Section (Compact with rounded corners)
    doc.setFillColor(248, 249, 250);
    doc.roundedRect(15, yPos, 180, 35, 3, 3, 'F'); // Added border radius

    // Employee info
    doc.setFontSize(8);
    doc.setTextColor(12, 2, 56);
    doc.setFont(undefined, 'bold');
    doc.text('EMPLOYEE DETAILS', 20, yPos + 8);

    doc.setFont(undefined, 'normal');
    doc.setTextColor(51, 51, 51);
    doc.setFontSize(7);

    // Compact 3-column layout
    doc.text('Name: ' + employeeName, 20, yPos + 15);
    doc.text('ID: ' + data.employee.employee_id, 20, yPos + 20);
    doc.text('Department: ' + data.employee.department, 20, yPos + 25);

    doc.text('Period: ' + startDate + ' to ' + endDate, 85, yPos + 15);
    doc.text('Role: ' + data.employee.role, 85, yPos + 20);
    doc.text('Schedule: ' + data.employee.work_schedule, 85, yPos + 25);

    // Attendance stats in same section
    doc.setTextColor(12, 2, 56);
    doc.setFont(undefined, 'bold');
    doc.text('SUMMARY', 150, yPos + 8);

    doc.setFont(undefined, 'normal');
    doc.setTextColor(51, 51, 51);
    doc.text('Present: ' + data.summary.present_days + '/' + data.summary.total_days, 150, yPos + 15);
    doc.text('Late: ' + data.summary.late_entries, 150, yPos + 20);
    doc.text('Early Exit: ' + data.summary.early_exits, 150, yPos + 25);
    doc.text('Overtime: ' + data.summary.total_overtime_hours.toFixed(1) + 'h', 150, yPos + 30);

    yPos += 40;

    // Minimal Leave Details with Border Radius
    var leaveData = data.leave_data || {};
    var hasLeaveData = (leaveData.leave_dates && leaveData.leave_dates.length > 0) ||
                       (leaveData.wfh_dates && leaveData.wfh_dates.length > 0) ||
                       (leaveData.half_day_dates && leaveData.half_day_dates.length > 0);

    if (hasLeaveData) {
        doc.setFontSize(8);
        doc.setTextColor(12, 2, 56);
        doc.setFont(undefined, 'bold');
        doc.text('LEAVE DETAILS', 15, yPos);
        yPos += 8;

        // Card settings - 6 per row
        let cardWidth = 28;
        let cardHeight = 10;
        let cardSpacing = 3;
        let cardsPerRow = 6;
        let startX = 15;

        // Leave Days with rounded cards
        if (leaveData.leave_dates && leaveData.leave_dates.length > 0) {
            // Mini header with border radius
            doc.setFillColor(239, 68, 68);
            doc.roundedRect(15, yPos, 30, 5, 2, 2, 'F'); // Added border radius
            doc.setFontSize(6);
            doc.setTextColor(255, 255, 255);
            doc.setFont(undefined, 'bold');
            doc.text('LEAVE (' + leaveData.leave_dates.length + ')', 17, yPos + 3.5);
            yPos += 8;

            let xPos = startX;
            doc.setTextColor(51, 51, 51);
            doc.setFont(undefined, 'normal');
            doc.setFontSize(5);

            leaveData.leave_dates.forEach(function(date, index) {
                if (yPos > 270) {
                    doc.addPage();
                    yPos = 20;
                    xPos = startX;
                }

                // Card with border radius
                doc.setFillColor(254, 226, 226);
                doc.roundedRect(xPos, yPos, cardWidth, cardHeight, 2, 2, 'F'); // Added border radius
                doc.setFillColor(239, 68, 68);
                doc.rect(xPos, yPos, 1, cardHeight, 'F'); // Left border

                doc.setTextColor(127, 29, 29);
                doc.setFont(undefined, 'bold');
                let displayDate = date.replace(' (Absent)', '').substring(0, 11);
                doc.text(displayDate, xPos + 2, yPos + 3);

                doc.setFont(undefined, 'normal');
                doc.setFontSize(4);
                doc.text(date.includes('(Absent)') ? 'ABSENT' : 'LEAVE', xPos + 2, yPos + 7);
                doc.setFontSize(5);

                xPos += cardWidth + cardSpacing;

                if ((index + 1) % cardsPerRow === 0) {
                    yPos += cardHeight + 2;
                    xPos = startX;
                }
            });

            if (leaveData.leave_dates.length % cardsPerRow !== 0) {
                yPos += cardHeight + 2;
            }
            yPos += 5;
        }

        // WFH Days with rounded cards
        if (leaveData.wfh_dates && leaveData.wfh_dates.length > 0) {
            doc.setFillColor(6, 182, 212);
            doc.roundedRect(15, yPos, 30, 5, 2, 2, 'F'); // Added border radius
            doc.setFontSize(6);
            doc.setTextColor(255, 255, 255);
            doc.setFont(undefined, 'bold');
            doc.text('WFH (' + leaveData.wfh_dates.length + ')', 17, yPos + 3.5);
            yPos += 8;

            let xPos = startX;
            doc.setFontSize(5);

            leaveData.wfh_dates.forEach(function(date, index) {
                if (yPos > 270) {
                    doc.addPage();
                    yPos = 20;
                    xPos = startX;
                }

                doc.setFillColor(207, 250, 254);
                doc.roundedRect(xPos, yPos, cardWidth, cardHeight, 2, 2, 'F'); // Added border radius
                doc.setFillColor(6, 182, 212);
                doc.rect(xPos, yPos, 1, cardHeight, 'F');

                doc.setTextColor(8, 145, 178);
                doc.setFont(undefined, 'bold');
                doc.text(date.substring(0, 11), xPos + 2, yPos + 3);

                doc.setFont(undefined, 'normal');
                doc.setFontSize(4);
                doc.text('WFH', xPos + 2, yPos + 7);
                doc.setFontSize(5);

                xPos += cardWidth + cardSpacing;

                if ((index + 1) % cardsPerRow === 0) {
                    yPos += cardHeight + 2;
                    xPos = startX;
                }
            });

            if (leaveData.wfh_dates.length % cardsPerRow !== 0) {
                yPos += cardHeight + 2;
            }
            yPos += 5;
        }

        // Half Days with rounded cards
        if (leaveData.half_day_dates && leaveData.half_day_dates.length > 0) {
            doc.setFillColor(245, 158, 11);
            doc.roundedRect(15, yPos, 35, 5, 2, 2, 'F'); // Added border radius
            doc.setFontSize(6);
            doc.setTextColor(255, 255, 255);
            doc.setFont(undefined, 'bold');
            doc.text('HALF DAY (' + leaveData.half_day_dates.length + ')', 17, yPos + 3.5);
            yPos += 8;

            let xPos = startX;
            doc.setFontSize(5);

            leaveData.half_day_dates.forEach(function(date, index) {
                if (yPos > 270) {
                    doc.addPage();
                    yPos = 20;
                    xPos = startX;
                }

                doc.setFillColor(254, 243, 199);
                doc.roundedRect(xPos, yPos, cardWidth, cardHeight, 2, 2, 'F'); // Added border radius
                doc.setFillColor(245, 158, 11);
                doc.rect(xPos, yPos, 1, cardHeight, 'F');

                doc.setTextColor(146, 64, 14);
                doc.setFont(undefined, 'bold');
                doc.text(date.substring(0, 11), xPos + 2, yPos + 3);

                doc.setFont(undefined, 'normal');
                doc.setFontSize(4);
                doc.text('HALF DAY', xPos + 2, yPos + 7);
                doc.setFontSize(5);

                xPos += cardWidth + cardSpacing;

                if ((index + 1) % cardsPerRow === 0) {
                    yPos += cardHeight + 2;
                    xPos = startX;
                }
            });

            if (leaveData.half_day_dates.length % cardsPerRow !== 0) {
                yPos += cardHeight + 2;
            }
            yPos += 8;
        }
    }

    // Table with FULL WIDTH (Fixed)
    function getPDFStatusText(record) {
        var status = [];
        if (record.late_entry) status.push('Late');
        if (record.early_exit) status.push('Early Exit');
        if (record.overtime_hours > 0) status.push('OT');
        return status.length === 0 ? record.mode : status.join(', ');
    }

    const tableData = data.records.map(record => [
        record.date,
        record.day.substring(0, 3),
        record.login_time,
        record.logout_time,
        record.total_work_hours,
        record.total_break_time + (record.breaks_count > 0 ? ' (' + record.breaks_count + ')' : ''),
        record.mode,
        getPDFStatusText(record)
    ]);

    // CORRECTED: Full Width Table Configuration
    doc.autoTable({
        head: [['Date', 'Day', 'Login', 'Logout', 'Hours', 'Breaks', 'Mode', 'Status']],
        body: tableData,
        startY: yPos,
        styles: {
            fontSize: 7,
            cellPadding: 2,
            lineColor: [255, 255, 255],
            lineWidth: 0,
            textColor: [51, 51, 51]
        },
        headStyles: {
            fillColor: [12, 2, 56],
            textColor: [255, 255, 255],
            fontStyle: 'bold',
            fontSize: 7,
            halign: 'center',
            cellPadding: 3
        },
        alternateRowStyles: {
            fillColor: [248, 249, 250]
        },
        columnStyles: {
            0: { fontStyle: 'bold', halign: 'center', cellWidth: 'auto' },
            1: { halign: 'center', cellWidth: 'auto' },
            2: { fontStyle: 'bold', halign: 'center', cellWidth: 'auto' },
            3: { fontStyle: 'bold', halign: 'center', cellWidth: 'auto' },
            4: { fontStyle: 'bold', halign: 'center', cellWidth: 'auto' },
            5: { halign: 'center', cellWidth: 'auto' },
            6: { halign: 'center', cellWidth: 'auto' },
            7: { halign: 'center', cellWidth: 'auto' }
        },
        // KEY FIX: These settings ensure full width
        margin: { left: 15, right: 15 },
        tableWidth: 'wrap', // Use available width
        theme: 'plain'
    });

    // Minimal Footer
    const finalY = doc.lastAutoTable.finalY + 8;
    doc.setFontSize(6);
    doc.setTextColor(102, 102, 102);
    doc.setFont(undefined, 'normal');
    doc.text('Generated by ZENEROM HRM on ' + new Date().toLocaleString('en-IN', {
        timeZone: 'Asia/Kolkata',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }), 105, finalY, { align: 'center' });

    const fileName = employeeName.replace(/\s+/g, '_') + '_Attendance_Report_' + startDate + '_to_' + endDate + '.pdf';
    doc.save(fileName);
}

</script>
@endpush

