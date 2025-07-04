@extends('layouts.app')

@section('content')
<div class="nk-content">

<div class="container">
    <h3>Work From Office Report</h3>
    <p>(Employees with attendance mode "Work from Office")</p>
    <!-- Filter Form -->
    <form method="GET" action="{{ route('attendance.workFromOffice') }}" class="row g-3 mb-4">
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
            <a href="{{ route('attendance.workFromOffice') }}" class="btn btn-secondary">Clear</a>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-bordered datatable-init-export" data-export-title="Work From Office Report">
            <thead>
                <tr>
                    <th>Employee Name</th>
                    <th>Attendance Date</th>
                    <th>Mode</th>
                    <th>Login Time</th>
                    <th>Logout Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>{{ $record->first_name }} {{ $record->last_name }} ({{ $record->employee_id }})</td>
                        <td>{{ $record->attendance_date }}</td>
                        <td>{{ $record->mode }}</td>
                        <td>{{ \Carbon\Carbon::parse($record->login_time)->format('d M Y H:i') }}</td>
                        <td>
                            @if($record->logout_time)
                                {{ \Carbon\Carbon::parse($record->logout_time)->format('d M Y H:i') }}
                            @else
                                Still Working
                            @endif
                        </td>
                    </tr>
                @empty
                    
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
@endsection
