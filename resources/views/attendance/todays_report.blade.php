@extends('layouts.app')

@section('content')
<div class="nk-content">
  <div class="container">
    <h4>
      Report from {{ \Carbon\Carbon::parse(request('start_date', \Carbon\Carbon::today()))->format('d M Y') }}
      to {{ \Carbon\Carbon::parse(request('end_date', \Carbon\Carbon::today()))->format('d M Y') }}
    </h4>
    <!-- Filter Form -->
    <form method="GET" action="{{ route('attendance.todays_report') }}" class="row g-3 mb-4">
      <div class="col-md-3">
        <label for="start_date" class="form-label">Start Date</label>
        <input type="date" name="start_date" id="start_date"
               value="{{ request('start_date', \Carbon\Carbon::today()->toDateString()) }}"
               class="form-control">
      </div>
      <div class="col-md-3">
        <label for="end_date" class="form-label">End Date</label>
        <input type="date" name="end_date" id="end_date"
               value="{{ request('end_date', \Carbon\Carbon::today()->toDateString()) }}"
               class="form-control">
      </div>
      <div class="col-md-3">
        <label for="staff_id" class="form-label">Staff Name</label>
        <select name="staff_id" id="staff_id" class="form-control">
          <option value="">All</option>
          @foreach($employees as $employee)
            <option value="{{ $employee->id }}" {{ request('staff_id') == $employee->id ? 'selected' : '' }}>
              {{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }} ({{ $employee->employee_id }})
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
            <th>Staff Name (Employee ID)</th>
            <th>Mode</th>
            <th>Login Time</th>
            <th>Logout Time</th>
            <th>Work Hours</th>
            <th>Today's Tasks</th>
          </tr>
        </thead>
        <tbody>
          @php $serial = 1; @endphp
          @foreach($employees as $employee)
            @if(!request('staff_id') || request('staff_id') == $employee->id)
              <tr>
                <td>{{ $serial++ }}</td>
                <td>
                  {{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }}
                  ({{ $employee->employee_id }})
                </td>
                <td>
                  @if(isset($attendances[$employee->id]))
                    {{ ucfirst($attendances[$employee->id]->mode) }}
                  @else
                    Leave
                  @endif
                </td>
                <td>
                  @if(isset($attendances[$employee->id]))
                    {{ $attendances[$employee->id]->formatted_login_time }}
                  @else
                    -
                  @endif
                </td>
                <td>
                  @if(isset($attendances[$employee->id]))
                    {{ $attendances[$employee->id]->formatted_logout_time }}
                  @else
                    -
                  @endif
                </td>
                <td>
                  @if(isset($attendances[$employee->id]))
                    {{ $attendances[$employee->id]->formatted_work_hours }}
                  @else
                    -
                  @endif
                </td>
                <td>
                  @if(isset($assignedTasks[$employee->id]) && $assignedTasks[$employee->id]->count() > 0)
                    <ul class="mb-0">
                      @foreach($assignedTasks[$employee->id] as $assignment)
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
            @endif
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
<script src="{{ asset('assets1/jquery.min.js') }}"></script>
@endsection
