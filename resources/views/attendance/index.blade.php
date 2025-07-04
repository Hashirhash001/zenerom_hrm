@extends('layouts.app')

@section('content')
@php
    // Convert session privileges into a collection for easier access
    $attendancePrivileges = collect(session('user_privileges'));
@endphp
<div class="container">
    <h1 class="mb-4">Attendance Records</h1>

    <!-- Filter Form -->
    <form action="{{ route('attendance.index') }}" method="GET" class="mb-4">
        <div class="row">
            <!-- Start Date -->
            <div class="col-md-2">
                <label for="start_date">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $start_date }}" class="form-control">
            </div>
            <!-- End Date -->
            <div class="col-md-2">
                <label for="end_date">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $end_date }}" class="form-control">
            </div>
            <!-- Name Filter -->
            <div class="col-md-3">
                <label for="name">Staff Name</label>
                <input type="text" name="name" id="name" value="{{ $nameFilter }}" class="form-control" placeholder="Enter name">
            </div>
            <!-- Submit Button -->
            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2 align-self-end">
                <a href="{{ route('attendance.todays_report') }}" target="_blank" class="btn btn-warning w-100">Today's Report</a>
            </div>

        </div>
    </form>

    <!-- Attendance Records Table -->
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <table class="datatable-init-export table" data-export-title="Export">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Staff Name</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Login</th>
                        <th>Logout</th>
                        <th>Duration</th>
                        <th>Mode</th>
                        <th>System IP</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
    @if($attendances->count() > 0)
        @foreach($attendances as $attendance)
            @php
                $loginTime = \Carbon\Carbon::parse($attendance->created_at);
                // If logout is null, use the current time for calculation.
                if($attendance->logout) {
                    $logoutTime = \Carbon\Carbon::parse($attendance->logout);
                } else {
                    $logoutTime = \Carbon\Carbon::now();
                }
                $totalMinutes = $logoutTime->diffInMinutes($loginTime);
                $hours = floor($totalMinutes / 60);
                $minutes = $totalMinutes % 60;
            @endphp
            <tr id="attendance_{{ $attendance->id }}">
                <td>{{ $attendance->attendance_date }}</td>
                <td>{{ $attendance->employee_name }}</td>
                <td>{{ $attendance->role }}</td>
                <td>{{ $attendance->department }}</td>
                <td class="login-time">{{ $attendance->created_at }}</td>
                <td class="logout-time">{{ $attendance->logout }}</td>
                <td>
                    @if($attendance->logout)
                        {{ sprintf('%02d:%02d', $hours, $minutes) }}
                    @else
                        {{ sprintf('%02d:%02d', $hours, $minutes) }} (Since login)
                    @endif
                </td>
                <td class="mode">{{ ucfirst($attendance->mode) }}</td>
                <td>{{ $attendance->system_ip }}</td>
                <td class="status">{{ ucfirst($attendance->approval_status) }}</td>
                <td>
                    @if($attendancePrivileges->has(12) && $attendancePrivileges->get(12)->can_edit)
                        <button class="btn btn-sm btn-primary editAttendanceBtn"
                            data-id="{{ $attendance->id }}"
                            data-login="{{ $attendance->created_at }}"
                            data-logout="{{ $attendance->logout }}"
                            data-mode="{{ $attendance->mode }}">
                            Edit
                        </button>
                    @else
                        <button class="btn btn-sm btn-primary disabled" title="Not Authorized">
                            Edit
                        </button>
                    @endif

                    @if($attendance->approval_status !== 'approved')
                        @if($attendancePrivileges->has(12) && $attendancePrivileges->get(12)->can_edit)
                            <button class="btn btn-sm btn-success approveAttendanceBtn" data-id="{{ $attendance->id }}">
                                Approve
                            </button>
                        @else
                            <button class="btn btn-sm btn-success disabled" title="Not Authorized">
                                Approve
                            </button>
                        @endif
                    @else
                        <span class="badge bg-success">Verified</span>
                    @endif
                </td>
            </tr>
        @endforeach
    @else
        <tr>
            <td colspan="11" class="text-center">No attendance records found for the given criteria.</td>
        </tr>
    @endif
</tbody>

            </table>
        </div>
    </div>
</div>

<!-- Edit Attendance Modal -->
<div class="modal fade" id="editAttendanceModal" tabindex="-1" aria-labelledby="editAttendanceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editAttendanceForm">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="editAttendanceModalLabel">Edit Attendance</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="attendance_id" id="attendance_id">
            <div class="mb-3">
                <label for="edit_login" class="form-label">Login (Created At)</label>
                <input type="text" class="form-control" id="edit_login" name="login">
                <small class="form-text text-muted">Format: YYYY-MM-DD HH:MM:SS</small>
            </div>
            <div class="mb-3">
                <label for="edit_logout" class="form-label">Logout</label>
                <input type="text" class="form-control" id="edit_logout" name="logout">
                <small class="form-text text-muted">Format: YYYY-MM-DD HH:MM:SS</small>
            </div>
            <div class="mb-3">
                <label for="edit_mode" class="form-label">Mode</label>
                <select class="form-control" id="edit_mode" name="mode">
                    <option value="Work from office">Work from office</option>
                    <option value="Half Day">Half Day</option>
                    <option value="Work from Home">Work From Home</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Attendance</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script src="{{ asset('assets1/jquery.min.js') }}"></script>

<script>
$(document).ready(function(){

    // Open edit modal and populate it with the current attendance data
    $('.editAttendanceBtn').on('click', function(){
        var id = $(this).data('id');
        var login = $(this).data('login');
        var logout = $(this).data('logout');
        var mode = $(this).data('mode');

        $('#attendance_id').val(id);
        $('#edit_login').val(login);
        $('#edit_logout').val(logout);
        $('#edit_mode').val(mode);

        $('#editAttendanceModal').modal('show');
    });

    // Handle edit form submission via AJAX
    $('#editAttendanceForm').on('submit', function(e){
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: '{{ route("attendance.update") }}',
            method: 'POST',
            data: formData,
            success: function(response){
                // Update the table row with new values
                var id = response.id;
                var row = $('#attendance_' + id);
                row.find('.login-time').text(response.created_at);
                row.find('.logout-time').text(response.logout);
                row.find('.mode').text(response.mode.charAt(0).toUpperCase() + response.mode.slice(1));
                $('#editAttendanceModal').modal('hide');
            },
            error: function(xhr){
                alert('Failed to update attendance.');
            }
        });
    });

    // Handle approve button click via AJAX
    $('.approveAttendanceBtn').on('click', function(){
        if (!confirm('Are you sure you want to approve this attendance record?')) return;
        var btn = $(this);
        var id = btn.data('id');
        $.ajax({
            url: '{{ route("attendance.approve") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                attendance_id: id
            },
            success: function(response){
                var row = $('#attendance_' + id);
                row.find('.status').text('Verified');
                btn.replaceWith('<span class="badge bg-success">Verified</span>');
            },
            error: function(xhr){
                alert('Failed to approve attendance.');
            }
        });
    });
});
</script>
@endsection
