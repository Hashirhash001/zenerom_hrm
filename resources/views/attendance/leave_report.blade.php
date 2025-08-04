@extends('layouts.app')

@section('content')
<style>
    /* Ensure table alignment within container */
    .table-responsive {
        overflow-x: auto;
        margin: 0 auto;
        width: 100%;
        max-width: 100%;
    }
    #leave-report-table {
        width: 100% !important;
        margin: 0 auto;
        border-collapse: collapse;
    }
    #leave-report-table th,
    #leave-report-table td {
        padding: 8px;
        text-align: left;
        vertical-align: middle;
    }
    /* Align export buttons to the right of the search bar */
    .dataTables_wrapper .dt-buttons {
        float: right;
        margin-left: 10px;
        margin-bottom: 10px;
    }
    .dataTables_wrapper .dataTables_filter {
        float: right;
        margin-left: 30px;
        margin-right: 50px;
    }
    .dataTables_wrapper .dataTables_length {
        float: left;
    }
</style>

<div class="nk-content">
    <div class="container">
        <h3 style="margin-top: 100px !important;">Attendance Report</h3>
        <p>(Employees with no attendance record or marked as 'Leave' in the selected date range)</p>
        <form method="GET" action="{{ route('attendance.attendanceReport') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="form-control" onclick="this.showPicker()" required>
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="form-control" onclick="this.showPicker()" required>
            </div>
            <div class="col-md-3">
                <label for="department_id" class="form-label">Department</label>
                <select name="department_id" id="department_id" class="form-control">
                    <option value="">All</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ $departmentId == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="role_id" class="form-label">Role</label>
                <select name="role_id" id="role_id" class="form-control">
                    <option value="">All</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ $roleId == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="staff_id" class="form-label">Staff</label>
                <select name="staff_id" id="staff_id" class="form-control">
                    <option value="">All</option>
                    @foreach($activeEmployees as $emp)
                        <option value="{{ $emp->id }}" {{ $staffId == $emp->id ? 'selected' : '' }}>
                            {{ $emp->first_name }} {{ $emp->middle_name ? $emp->middle_name . ' ' : '' }} {{ $emp->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-9 align-self-end">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="{{ route('attendance.attendanceReport') }}" class="btn btn-secondary">Clear</a>
            </div>
        </form>
        <div class="table-responsive">
            <table id="leave-report-table" class="table table-bordered" data-export-title="{{ $exportTitle }}">
                <thead>
                    <tr>
                        <th>Employee Name</th>
                        <th>Employee ID</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Email</th>
                        <th>Leave Count</th>
                        <th>WFH Count</th>
                        <th>Half Day Count</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $employee)
                        <tr>
                            <td>{{ $employee->first_name }} {{ $employee->middle_name ? $employee->middle_name . ' ' : '' }} {{ $employee->last_name }}</td>
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
                            <td colspan="8" class="text-center">No records found for the selected criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable only for the specific table
        let table = $('#leave-report-table');
        if ($.fn.DataTable.isDataTable('#leave-report-table')) {
            table.DataTable().destroy();
        }

        table.DataTable({
            dom: 'lfBrtip', // Add 'l' for row count filter, place buttons after filter
            destroy: true,
            autoWidth: false, // Prevent DataTables from overriding table width
            buttons: [
                {
                    extend: 'csv',
                    title: 'ZENEROM - {{ $exportTitle }}',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'excel',
                    title: 'ZENEROM - {{ $exportTitle }}',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'pdf',
                    exportOptions: {
                        columns: ':visible'
                    },
                    customize: function(doc) {
                        doc.info = {
                            title: 'ZENEROM - {{ $exportTitle }}',
                            author: 'Zenerom',
                            subject: 'Attendance Report'
                        };
                        doc.header = null; // Disable default header
                        doc.footer = null; // Disable default footer
                        doc.pageMargins = [40, 40, 40, 40]; // Set margins to ensure content placement
                        // Clear default content and add only custom headers
                        doc.content = [
                            {
                                text: 'ZENEROM',
                                style: 'mainHeader',
                                alignment: 'center',
                                margin: [0, 0, 0, 10]
                            },
                            {
                                text: '{{ $exportTitle }}',
                                style: 'subHeader',
                                alignment: 'left',
                                margin: [0, 0, 0, 10]
                            },
                            ...doc.content.filter(item => item.table) // Keep only the table content
                        ];
                        doc.styles.mainHeader = {
                            fontSize: 18,
                            bold: true,
                            color: '#000000'
                        };
                        doc.styles.subHeader = {
                            fontSize: 14,
                            bold: false,
                            color: '#333333'
                        };
                    }
                }
            ]
        });
    });
</script>
@endpush
