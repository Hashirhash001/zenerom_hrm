@extends('layouts.app')

@section('content')
<div class="nk-content">
<div class="container">
    <h3>Leave Report</h3>
    <p>(Employees with no attendance record in the selected date range)</p>
    <form method="GET" action="{{ route('attendance.leaveReport') }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="form-control">
        </div>
        <div class="col-md-4">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="form-control">
        </div>
        <div class="col-md-4">
            <label for="staff_id" class="form-label">Staff</label>
            <select name="staff_id" id="staff_id" class="form-control">
                <option value="">All</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ $staffId == $emp->id ? 'selected' : '' }}>
                        {{ $emp->first_name }} {{ $emp->last_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="{{ route('attendance.leaveReport') }}" class="btn btn-secondary">Clear</a>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-bordered datatable-init-export" data-export-title="Leave Report">
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Employee ID</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $employee)
                    <tr>
                        <td>{{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }}</td>
                        <td>{{ $employee->employee_id }}</td>
                        <td>{{ $employee->department }}</td>
                        <td>{{ $employee->role }}</td>
                        <td>{{ $employee->email }}</td>
                    </tr>
                @empty
                    
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
@endsection
