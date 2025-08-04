@extends('layouts.app')

@section('content')
<div class="nk-content">
  <div class="container">
    <h4>
      Report from {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
    </h4>
    <!-- Filter Form -->
    <form method="GET" action="{{ route('attendance.todays_report') }}" class="row g-3 mb-4">
      <div class="col-md-3">
        <label for="start_date" class="form-label">Start Date</label>
        <input type="date" name="start_date" id="start_date"
               value="{{ request('start_date', \Carbon\Carbon::today()->toDateString()) }}"
               class="form-control" onclick="this.showPicker()">
      </div>
      <div class="col-md-3">
        <label for="end_date" class="form-label">End Date</label>
        <input type="date" name="end_date" id="end_date"
               value="{{ request('end_date', \Carbon\Carbon::today()->toDateString()) }}"
               class="form-control" onclick="this.showPicker()">
      </div>
      <div class="col-md-3">
        <label for="staff_id" class="form-label">Staff Name</label>
        <select name="staff_id" id="staff_id" class="form-control">
          <option value="">All</option>
          @foreach($employees as $employee)
            <option value="{{ $employee->user_id }}" {{ request('staff_id') == $employee->user_id ? 'selected' : '' }}>
              {{ $employee->first_name }} {{ $employee->middle_name ? $employee->middle_name . ' ' : '' }} {{ $employee->last_name }} ({{ $employee->employee_id }})
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3 align-self-end">
        <button type="submit" class="btn btn-primary">Apply Filters</button>
        <a href="{{ route('attendance.todays_report') }}" class="btn btn-secondary">Clear</a>
      </div>
    </form>

    <!-- Attendance Report Table -->
    <div class="table-responsive">
      <table class="datatable-init-export table" data-export-title="Attendance Report">
        <thead>
          <tr>
            <th>Si.No</th>
            <th>Date</th>
            <th>Staff Name (Employee ID)</th>
            <th>Mode</th>
            <th>Login Time</th>
            <th>Logout Time</th>
            <th>Work Hours</th>
            <th>Tasks</th>
          </tr>
        </thead>
        <tbody>
          @php $serial = 1; @endphp
          @foreach($employees as $employee)
            @if(!request('staff_id') || request('staff_id') == $employee->user_id)
              @php
                $attendanceRecords = isset($attendances[$employee->user_id]) ? $attendances[$employee->user_id] : collect([]);
              @endphp
              @if($attendanceRecords->isEmpty() && !request('staff_id'))
                <tr>
                  <td>{{ $serial++ }}</td>
                  <td>-</td>
                  <td>
                    {{ $employee->first_name }} {{ $employee->middle_name ? $employee->middle_name . ' ' : '' }} {{ $employee->last_name }}
                    ({{ $employee->employee_id }})
                  </td>
                  <td>
                    @php
                      \Illuminate\Support\Facades\Log::warning('No attendance record found for employee in todaysReport', [
                        'employee_id' => $employee->id,
                        'user_id' => $employee->user_id,
                        'employee_name' => $employee->first_name . ' ' . ($employee->middle_name ? $employee->middle_name . ' ' : '') . $employee->last_name,
                        'start_date' => $startDate,
                        'end_date' => $endDate
                      ]);
                    @endphp
                    Leave
                  </td>
                  <td>-</td>
                  <td>-</td>
                  <td>-</td>
                  <td>
                    @php
                      $tasks = isset($assignedTasks[$employee->user_id]) ? collect($assignedTasks[$employee->user_id])->flatten(1) : collect([]);
                    @endphp
                    @if($tasks->isNotEmpty())
                      <ul class="mb-0">
                        @foreach($tasks as $assignment)
                          <li>
                            <strong>{{ optional($assignment->task)->title ?? 'Task' }}</strong>
                            - {{ optional(optional($assignment->task)->project)->name ?? 'N/A' }}
                            - {{ optional(optional($assignment->task)->service)->name ?? 'N/A' }}
                          </li>
                        @endforeach
                      </ul>
                    @else
                      -
                    @endif
                  </td>
                </tr>
              @else
                @foreach($attendanceRecords as $attendance)
                  <tr>
                    <td>{{ $serial++ }}</td>
                    <td>{{ $attendance->attendance_date_formatted }}</td>
                    <td>
                      {{ $employee->first_name }} {{ $employee->middle_name ? $employee->middle_name . ' ' : '' }} {{ $employee->last_name }}
                      ({{ $employee->employee_id }})
                    </td>
                    <td>{{ ucfirst($attendance->mode) }}</td>
                    <td>{{ $attendance->formatted_login_time }}</td>
                    <td>{{ $attendance->formatted_logout_time }}</td>
                    <td>{{ $attendance->formatted_work_hours }}</td>
                    <td>
                      @php
                        $date = $attendance->attendance_date instanceof \Carbon\Carbon
                          ? $attendance->attendance_date->toDateString()
                          : $attendance->attendance_date;
                        $tasks = isset($assignedTasks[$employee->user_id][$date]) ? $assignedTasks[$employee->user_id][$date] : collect([]);
                      @endphp
                      @if($tasks->isNotEmpty())
                        <ul class="mb-0">
                          @foreach($tasks as $assignment)
                            <li>
                              <strong>{{ optional($assignment->task)->title ?? 'Task' }}</strong>
                              - {{ optional(optional($assignment->task)->project)->name ?? 'N/A' }}
                              - {{ optional(optional($assignment->task)->service)->name ?? 'N/A' }}
                            </li>
                          @endforeach
                        </ul>
                      @else
                        -
                      @endif
                    </td>
                  </tr>
                @endforeach
              @endif
            @endif
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
<script src="{{ asset('assets1/jquery.min.js') }}"></script>
@endsection
