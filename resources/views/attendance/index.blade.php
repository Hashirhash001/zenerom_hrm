@extends('layouts.app')

@section('content')
@php
    // Convert session privileges into a collection for easier access
    $attendancePrivileges = collect(session('user_privileges'));
    $showControls = !in_array(Auth::user()->role_id, [2, 7]) || ($attendancePrivileges->has(12) && $attendancePrivileges->get(12)->can_edit);
@endphp
<div class="container px-6 py-6">
    <h1 class="mt-5 mb-4 text-2xl font-bold text-gray-800" style="margin-top: 70px !important;">Attendance Records</h1>

    <!-- Filter Form -->
    <form id="filterForm" action="{{ route('attendance.index') }}" method="GET" class="mb-4 bg-white p-4 rounded-lg shadow-md">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="start_date" class="block text-xs font-medium text-gray-700">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $start_date }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-1">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="block text-xs font-medium text-gray-700">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $end_date }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-1">
            </div>
            <div class="col-md-3">
                <label for="name" class="block text-xs font-medium text-gray-700">Staff Name</label>
                <input type="text" name="name" id="name" value="{{ $nameFilter ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-1" placeholder="Enter name">
            </div>
            <div class="col-md-3">
                <label for="role" class="block text-xs font-medium text-gray-700">Role</label>
                <select name="role" id="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-1">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ $roleFilter == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="department" class="block text-xs font-medium text-gray-700">Department</label>
                <select name="department" id="department" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-1">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ $departmentFilter == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="block text-xs font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-1">
                    <option value="">All Statuses</option>
                    <option value="still_working" {{ $statusFilter == 'still_working' ? 'selected' : '' }}>Still Working</option>
                    <option value="on_break" {{ $statusFilter == 'on_break' ? 'selected' : '' }}>On Break</option>
                    <option value="logged_out" {{ $statusFilter == 'logged_out' ? 'selected' : '' }}>Logged Out</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="w-full bg-indigo-600 text-white py-1 px-2 rounded-md hover:bg-indigo-700 transition duration-300 ease-in-out transform hover:-translate-y-1">
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                </button>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <a href="{{ route('attendance.todays_report') }}" target="_blank" class="w-full bg-yellow-500 text-white py-1 px-2 rounded-md hover:bg-yellow-600 transition duration-300 ease-in-out transform hover:-translate-y-1 text-center">Report</a>
            </div>
        </div>
    </form>

    <!-- Attendance Controls -->
    @if($showControls)
    <div id="attendanceControls" style="display: none;">
        <div class="mb-4 bg-white p-2 rounded-lg shadow-md flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <button type="button" class="relative attendanceBtn" id="attendanceBtn" style="background: linear-gradient(135deg, #10b981, #059669); border: none; padding: 8px 16px; border-radius: 6px; color: white; font-weight: 600; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                    <img src="{{ asset('images/attendance/check-in.png') }}" alt="Check-In" class="inline-block w-5 h-5 mr-1.5" id="attendanceIcon" width="20" height="20">
                    <span id="attendanceText">Check-In</span>
                    <span class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-xs font-bold rounded-full h-4 w-4 flex items-center justify-center opacity-0 transition-opacity duration-300" id="attendancePulse">!</span>
                </button>
                <div class="flex items-center bg-gray-100 rounded-md px-3 py-1.5">
                    <svg class="w-5 h-5 text-gray-600 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span id="timerDisplay" class="text-base font-mono text-gray-800" style="display: none;">00:00:00</span>
                    <svg id="pauseIcon" class="w-8 h-8 text-red-500 opacity-0 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6"></path>
                    </svg>
                </div>
                <button type="button" class="relative breakBtn" id="breakBtn" style="background: linear-gradient(135deg, #f59e0b, #d97706); border: none; padding: 8px 16px; border-radius: 6px; color: white; font-weight: 600; transition: transform 0.3s ease, box-shadow 0.3s ease;" disabled>
                    <img src="{{ asset('images/attendance/coffee-break.png') }}" alt="Break" class="inline-block w-5 h-5 mr-1.5" width="20" height="20">
                    <span id="breakText">Start Break</span>
                    <span class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-xs font-bold rounded-full h-4 w-4 flex items-center justify-center opacity-0 transition-opacity duration-300" id="breakPulse">!</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Attendance Records Table -->
    <div id="attendanceTableContainer">
        @if($attendances->count() > 0)
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="table table-bordered w-full text-xs" id="tasksTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Login</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logout</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Work Hours</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Break Hours</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mode</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">System IP</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $today = \Carbon\Carbon::today('Asia/Kolkata')->toDateString();
                    @endphp
                    @foreach($attendances as $attendance)
                        @php
                            $isOnBreak = \DB::table('staff_breaks')
                                ->where('attendance_id', $attendance->id)
                                ->whereNotNull('break_start')
                                ->whereNull('break_end')
                                ->exists();
                            $totalBreakSeconds = \DB::table('staff_breaks')
                                ->where('attendance_id', $attendance->id)
                                ->whereNotNull('break_end')
                                ->select(\DB::raw('SUM(TIME_TO_SEC(TIMEDIFF(break_end, break_start))) as total_break_seconds'))
                                ->value('total_break_seconds') ?? 0;
                            if ($isOnBreak) {
                                $activeBreak = \DB::table('staff_breaks')
                                    ->where('attendance_id', $attendance->id)
                                    ->whereNotNull('break_start')
                                    ->whereNull('break_end')
                                    ->first();
                                $totalBreakSeconds += \Carbon\Carbon::now('Asia/Kolkata')->diffInSeconds(\Carbon\Carbon::parse($activeBreak->break_start));
                            }
                            $totalBreakFormatted = floor($totalBreakSeconds / 36) / 100;
                        @endphp
                        <tr id="attendance_{{ $attendance->id }}"
                            class="{{ is_null($attendance->logout) && $attendance->attendance_date == $today ? ($isOnBreak ? 'bg-yellow-100' : 'active-attendance') : '' }}"
                            data-user-id="{{ $attendance->user_id }}"
                            data-total-work-seconds="{{ $attendance->total_work_seconds ?? 0 }}"
                            data-last-timer-start="{{ $attendance->last_timer_start ?? '' }}"
                            data-is-on-break="{{ $isOnBreak ? 'true' : 'false' }}"
                            data-total-break-seconds="{{ $totalBreakSeconds }}"
                            class="hover:bg-gray-50 transition duration-150">
                            <td class="px-4 py-2 whitespace-nowrap">{{ $attendance->attendance_date }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $attendance->employee_name }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $attendance->role }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $attendance->department }}</td>
                            <td class="px-4 py-2 whitespace-nowrap login-time">{{ $attendance->created_at }}</td>
                            <td class="px-4 py-2 whitespace-nowrap logout-time">{{ $attendance->logout ? $attendance->logout : 'Still working' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap total-work-hours">{{ number_format(floor(($attendance->total_work_seconds ?? 0) / 36) / 100, 2) }} hours</td>
                            <td class="px-4 py-2 whitespace-nowrap total-break-hours">{{ number_format($totalBreakFormatted, 2) }} hours</td>
                            <td class="px-4 py-2 whitespace-nowrap mode">{{ ucfirst($attendance->mode) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $attendance->system_ip }}</td>
                            <td class="px-4 py-2 whitespace-nowrap status">{{ $isOnBreak ? 'On Break' : ucfirst($attendance->approval_status) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                @if($attendancePrivileges->has(12) && $attendancePrivileges->get(12)->can_edit)
                                    <button class="btn btn-sm btn-primary editAttendanceBtn"
                                            data-id="{{ $attendance->id }}"
                                            data-login="{{ $attendance->created_at }}"
                                            data-logout="{{ $attendance->logout ?? '' }}"
                                            data-mode="{{ $attendance->mode }}"
                                            style="padding: 4px 8px; border-radius: 4px;">
                                        Edit
                                    </button>
                                @else
                                    <button class="btn btn-sm btn-primary disabled" title="Not Authorized" style="padding: 4px 8px; border-radius: 4px;">
                                        Edit
                                    </button>
                                @endif
                                @if($attendance->approval_status !== 'approved')
                                    @if($attendancePrivileges->has(12) && $attendancePrivileges->get(12)->can_edit)
                                        <button class="btn btn-sm btn-success approveAttendanceBtn" data-id="{{ $attendance->id }}" style="padding: 4px 8px; border-radius: 4px;">
                                            Approve
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-success disabled" title="Not Authorized" style="padding: 4px 8px; border-radius: 4px;">
                                            Approve
                                        </button>
                                    @endif
                                @else
                                    <span class="badge bg-success" style="padding: 4px 8px; border-radius: 4px;">Verified</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs">
            No attendance records found for the given criteria.
        </div>
        @endif
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
                            <small class="form-text text-muted">Format: YYYY-MM-DD HH:MM:SS (leave blank if still working)</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_mode" class="form-label">Mode</label>
                            <select class="form-control" id="edit_mode" name="mode">
                                <option value="Work from office">Work from office</option>
                                <option value="Half Day">Half Day</option>
                                <option value="Work from Home">Work from Home</option>
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
</div>

<!-- jQuery (CDN with local fallback) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>window.jQuery || document.write('<script src="{{ asset('assets1/jquery.min.js') }}"><\/script>')</script>
<!-- DataTables CSS (CDN) -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
<!-- DataTables JS (CDN) -->
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Bootstrap JS (for modal compatibility) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<!-- Tailwind CSS CDN for modern styling -->
<script src="https://cdn.tailwindcss.com"></script>

    <script>
        let totalWorkSeconds = 0;
        let isTimerRunning = false;
        let timerInterval = null;
        let dataTable = null;
        let tableUpdateInterval = null;
        let currentUserId = {{ Auth::id() }};
        let lastSyncTime = null;
        let isOnBreak = false;
        let lastSyncAttempt = 0;
        const SYNC_DEBOUNCE_MS = 1000; // Debounce sync requests by 1 second

        // Function to format seconds to decimal hours
        function formatHours(seconds) {
            const hours = Math.floor(seconds / 36) / 100;
            return `${hours.toFixed(2)} hours`;
        }

        // Function to format seconds to Xh Ymin
        function formatTimeFriendly(seconds) {
            const hrs = Math.floor(seconds / 3600);
            const mins = Math.floor((seconds % 3600) / 60);
            if (hrs > 0) {
                return `${hrs}h ${mins}min`;
            }
            return `${mins}min`;
        }

        // Function to format seconds to HH:MM:SS for timer display
        function formatTime(seconds) {
            const hrs = Math.floor(seconds / 3600);
            const mins = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            return `${String(hrs).padStart(2, '0')}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        }

        // Function to update timer display and table
        function updateTimer() {
            if (isTimerRunning && !isOnBreak && lastSyncTime) {
                totalWorkSeconds++;
                $('#timerDisplay').text(formatTime(totalWorkSeconds)).addClass('animate-pulse').addClass('text-gray-800').removeClass('text-red-500');
                setTimeout(() => $('#timerDisplay').removeClass('animate-pulse'), 200);
                const $currentRow = $(`#attendance_${currentUserId}`);
                if ($currentRow.length) {
                    $currentRow.find('.total-work-hours').text(formatTimeFriendly(totalWorkSeconds));
                    $currentRow.data('total-work-seconds', totalWorkSeconds);
                }
                // Sync with server every 30 seconds, but only if not on break
                if (totalWorkSeconds % 30 === 0 && !isOnBreak) {
                    syncTimerWithServer();
                }
            }
        }

        // Function to start timer
        function startTimer(startSeconds = 0) {
            totalWorkSeconds = startSeconds;
            isTimerRunning = true;
            isOnBreak = false;
            $('#timerDisplay').show().text(formatTime(totalWorkSeconds)).addClass('text-gray-800').removeClass('text-red-500');
            $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100');
            $('#breakPulse').addClass('opacity-0').removeClass('opacity-100');
            $('#attendancePulse').addClass('opacity-100').removeClass('opacity-0');
            if (timerInterval) clearInterval(timerInterval);
            timerInterval = setInterval(updateTimer, 1000);
        }

        // Function to stop timer (for breaks or check-out)
        function stopTimer() {
            isTimerRunning = false;
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
            $('#timerDisplay').show().text(formatTime(totalWorkSeconds)).addClass('text-red-500').removeClass('text-gray-800');
            $('#pauseIcon').addClass('opacity-100').removeClass('opacity-0');
            $('#breakPulse').addClass('opacity-100').removeClass('opacity-0');
            $('#attendancePulse').addClass('opacity-0').removeClass('opacity-100');
            console.log('Timer stopped', { totalWorkSeconds });
        }

        // Function to sync timer with server
        function syncTimerWithServer() {
            return new Promise((resolve, reject) => {
                const now = Date.now();
                if (now - lastSyncAttempt < SYNC_DEBOUNCE_MS) {
                    console.log('Sync skipped due to debounce');
                    resolve({ success: true });
                    return;
                }
                lastSyncAttempt = now;
                $.ajax({
                    url: '/sync-timer',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        user_id: currentUserId,
                        total_work_seconds: isOnBreak ? null : totalWorkSeconds // Do not send totalWorkSeconds during break
                    },
                    success: function(response) {
                        console.log('Timer sync response:', response);
                        if (response.success) {
                            if (!isOnBreak) {
                                totalWorkSeconds = response.totalWorkSeconds || totalWorkSeconds;
                                lastSyncTime = new Date();
                            }
                            isOnBreak = response.is_on_break || false;
                            isTimerRunning = !isOnBreak && !response.logout;
                            const $currentRow = $(`#attendance_${response.attendance_id}`);
                            if ($currentRow.length) {
                                $currentRow.data('total-work-seconds', totalWorkSeconds);
                                $currentRow.data('is-on-break', isOnBreak);
                                $currentRow.data('total-break-seconds', response.total_break_seconds || 0);
                                $currentRow.find('.total-work-hours').text(formatTimeFriendly(totalWorkSeconds));
                                $currentRow.find('.total-break-hours').text(formatTimeFriendly(response.total_break_seconds || 0));
                                if (isOnBreak) {
                                    $currentRow.removeClass('active-attendance').addClass('bg-yellow-100');
                                    $currentRow.find('td').removeClass('active-attendance').addClass('bg-yellow-100');
                                    $currentRow.find('.status').text('On Break');
                                    $currentRow.find('.logout-time').text('On Break');
                                    stopTimer();
                                } else if (!response.logout) {
                                    $currentRow.removeClass('bg-yellow-100').addClass('active-attendance');
                                    $currentRow.find('td').removeClass('bg-yellow-100').addClass('active-attendance');
                                    $currentRow.find('.status').text(response.approval_status ? response.approval_status.charAt(0).toUpperCase() + response.approval_status.slice(1) : 'Pending');
                                    $currentRow.find('.logout-time').text('Still working');
                                    if (!isTimerRunning) startTimer(totalWorkSeconds);
                                }
                            }
                            $('#timerDisplay').text(formatTime(totalWorkSeconds));
                            if (isOnBreak || response.logout) {
                                $('#timerDisplay').addClass('text-red-500').removeClass('text-gray-800');
                                stopTimer();
                            } else {
                                $('#timerDisplay').addClass('text-gray-800').removeClass('text-red-500');
                                if (!isTimerRunning) startTimer(totalWorkSeconds);
                            }
                            resolve(response);
                        } else {
                            console.error('Timer sync failed:', response.message);
                            stopTimer();
                            $('#timerDisplay').hide();
                            reject(response);
                        }
                    },
                    error: function(xhr) {
                        console.error('Timer sync failed:', xhr.responseJSON?.message || 'An error occurred.');
                        stopTimer();
                        $('#timerDisplay').hide();
                        reject(xhr);
                    }
                });
            });
        }

        // Function to update attendance table
        function updateAttendanceTable() {
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();
            const name = $('#name').val();
            const role = $('#role').val();
            const department = $('#department').val();
            const status = $('#status').val();
            $.ajax({
                url: '/fetch-attendances',
                type: 'GET',
                data: {
                    start_date: startDate,
                    end_date: endDate,
                    name: name,
                    role: role,
                    department: department,
                    status: status
                },
                success: function(response) {
                    console.log('Fetch attendances response:', response);
                    const tableContainer = $('#attendanceTableContainer');
                    const today = '{{ \Carbon\Carbon::today('Asia/Kolkata')->toDateString() }}';
                    if (response.attendances.length > 0) {
                        const currentUserAttendances = response.attendances.filter(a => a.user_id == currentUserId);
                        const otherAttendances = response.attendances
                            .filter(a => a.user_id != currentUserId)
                            .sort((a, b) => a.employee_name.localeCompare(b.employee_name));
                        const sortedAttendances = [...currentUserAttendances, ...otherAttendances];

                        let tableHtml = `
                            <style>
                                .bg-yellow-100, .bg-yellow-100 td { background-color: #fefcbf !important; }
                                .active-attendance, .active-attendance td { background-color: #e6fffa !important; }
                                .hover\\:bg-gray-50:hover, .hover\\:bg-gray-50:hover td { background-color: #f9fafb !important; }
                            </style>
                            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                                <table class="table table-bordered w-full text-xs" id="tasksTable">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Name</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Login</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logout</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Work Hours</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Break Hours</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mode</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">System IP</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                        `;
                        sortedAttendances.forEach(attendance => {
                            const isActive = !attendance.logout && attendance.attendance_date === today;
                            const isRowOnBreak = attendance.is_on_break;
                            const rowClass = isActive ? (isRowOnBreak ? 'bg-yellow-100' : 'active-attendance') : '';
                            const hours = isActive && !isRowOnBreak && attendance.user_id === currentUserId
                                ? formatTimeFriendly(totalWorkSeconds)
                                : formatTimeFriendly(attendance.total_work_seconds || 0);
                            const breakHours = formatTimeFriendly(attendance.total_break_seconds || 0);
                            const loginTime = new Date(attendance.created_at).toLocaleTimeString('en-US', { hour12: true, hour: 'numeric', minute: '2-digit', second: '2-digit' });
                            const logoutTime = isRowOnBreak ? 'On Break' : (attendance.logout ? new Date(attendance.logout).toLocaleTimeString('en-US', { hour12: true, hour: 'numeric', minute: '2-digit', second: '2-digit' }) : 'Still working');
                            const canEdit = {{ $attendancePrivileges->has(12) && $attendancePrivileges->get(12)->can_edit ? 'true' : 'false' }};
                            const isApproved = attendance.approval_status === 'approved';
                            tableHtml += `
                                <tr id="attendance_${attendance.id}"
                                    class="${rowClass} hover:bg-gray-50 transition duration-150"
                                    data-user-id="${attendance.user_id}"
                                    data-total-work-seconds="${attendance.user_id === currentUserId ? totalWorkSeconds : (attendance.total_work_seconds || 0)}"
                                    data-is-on-break="${isRowOnBreak}"
                                    data-total-break-seconds="${attendance.total_break_seconds || 0}">
                                    <td class="px-4 py-2 whitespace-nowrap ${rowClass}">${attendance.attendance_date}</td>
                                    <td class="px-4 py-2 whitespace-nowrap ${rowClass}">${attendance.employee_name}</td>
                                    <td class="px-4 py-2 whitespace-nowrap ${rowClass}">${attendance.role}</td>
                                    <td class="px-4 py-2 whitespace-nowrap ${rowClass}">${attendance.department}</td>
                                    <td class="px-4 py-2 whitespace-nowrap login-time ${rowClass}">${loginTime}</td>
                                    <td class="px-4 py-2 whitespace-nowrap logout-time ${rowClass}">${logoutTime}</td>
                                    <td class="px-4 py-2 whitespace-nowrap total-work-hours ${rowClass}">${hours}</td>
                                    <td class="px-4 py-2 whitespace-nowrap total-break-hours ${rowClass}">${breakHours}</td>
                                    <td class="px-4 py-2 whitespace-nowrap mode ${rowClass}">${attendance.mode.charAt(0).toUpperCase() + attendance.mode.slice(1)}</td>
                                    <td class="px-4 py-2 whitespace-nowrap ${rowClass}">${attendance.system_ip}</td>
                                    <td class="px-4 py-2 whitespace-nowrap status ${rowClass}">${isRowOnBreak ? 'On Break' : (attendance.approval_status ? attendance.approval_status.charAt(0).toUpperCase() + attendance.approval_status.slice(1) : 'Pending')}</td>
                                    <td class="px-4 py-2 whitespace-nowrap ${rowClass}">
                                        ${canEdit ? `
                                            <button class="btn btn-sm btn-primary editAttendanceBtn"
                                                    data-id="${attendance.id}"
                                                    data-login="${attendance.created_at}"
                                                    data-logout="${attendance.logout || ''}"
                                                    data-mode="${attendance.mode}"
                                                    style="padding: 4px 8px; border-radius: 4px;">
                                                Edit
                                            </button>
                                        ` : `
                                            <button class="btn btn-sm btn-primary disabled" title="Not Authorized" style="padding: 4px 8px; border-radius: 4px;">
                                                Edit
                                            </button>
                                        `}
                                        ${!isApproved && canEdit ? `
                                            <button class="btn btn-sm btn-success approveAttendanceBtn" data-id="${attendance.id}" style="padding: 4px 8px; border-radius: 4px;">
                                                Approve
                                            </button>
                                        ` : !isApproved ? `
                                            <button class="btn btn-sm btn-success disabled" title="Not Authorized" style="padding: 4px 8px; border-radius: 4px;">
                                                Approve
                                            </button>
                                        ` : `
                                            <span class="badge bg-success" style="padding: 4px 8px; border-radius: 4px;">Verified</span>
                                        `}
                                    </td>
                                </tr>
                            `;
                        });
                        tableHtml += `
                                    </tbody>
                                </table>
                            </div>
                        `;
                        tableContainer.empty().html(tableHtml);
                        if (dataTable) {
                            dataTable.destroy();
                        }
                        dataTable = $('#tasksTable').DataTable({
                            dom: "<'col-sm-12'tr>",
                            paging: false,
                            searching: false,
                            ordering: true,
                            info: false,
                            autoWidth: false,
                            responsive: true,
                            language: {
                                emptyTable: "No attendance records found for the given criteria."
                            }
                        });
                    } else {
                        tableContainer.empty().html(`
                            <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-500 text-xs">
                                No attendance records found for the given criteria.
                            </div>
                        `);
                        if (dataTable) {
                            dataTable.destroy();
                            dataTable = null;
                        }
                    }
                },
                error: function(xhr) {
                    console.error('Failed to fetch attendance records:', xhr.responseJSON?.message || 'An error occurred.');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to fetch attendance records: ' + (xhr.responseJSON?.message || 'An error occurred.'),
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                        }
                    });
                }
            });
        }

        // Function to get current attendance status
        function checkAttendanceStatus() {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: '/attendance-status',
                    type: 'GET',
                    success: function(response) {
                        console.log('Attendance status:', response);
                        if (response.isCheckedIn && !response.hasCheckedOut) {
                            $('#attendanceControls').show();
                            $('#attendanceBtn').css('background', 'linear-gradient(135deg, #ef4444, #b91c1c)').find('#attendanceText').text('Check-Out');
                            $('#attendanceIcon').attr('src', '{{ asset('images/attendance/logout.png') }}');
                            $('#breakBtn').prop('disabled', false).css('opacity', '1');
                            totalWorkSeconds = response.totalWorkSeconds || 0;
                            isOnBreak = response.isOnBreak || false;
                            lastSyncTime = new Date();
                            const $currentRow = $(`#attendance_${response.attendance_id}`);
                            if ($currentRow.length) {
                                $currentRow.data('total-work-seconds', totalWorkSeconds);
                                $currentRow.data('is-on-break', isOnBreak);
                                $currentRow.data('total-break-seconds', response.breakSeconds || 0);
                                $currentRow.find('.total-work-hours').text(formatTimeFriendly(totalWorkSeconds));
                                $currentRow.find('.total-break-hours').text(formatTimeFriendly(response.breakSeconds || 0));
                            }
                            if (response.isOnBreak) {
                                isTimerRunning = false;
                                stopTimer();
                                $('#breakBtn').find('#breakText').text('End Break');
                                $('#attendanceBtn').prop('disabled', true).css('opacity', '0.5');
                                $('#timerDisplay').show().text(formatTime(totalWorkSeconds)).addClass('text-red-500').removeClass('text-gray-800');
                                $('#pauseIcon').addClass('opacity-100').removeClass('opacity-0');
                                $('#breakPulse').addClass('opacity-100').removeClass('opacity-0');
                                $('#attendancePulse').addClass('opacity-0').removeClass('opacity-100');
                                if ($currentRow.length) {
                                    $currentRow.removeClass('active-attendance').addClass('bg-yellow-100');
                                    $currentRow.find('td').removeClass('active-attendance').addClass('bg-yellow-100');
                                    $currentRow.find('.status').text('On Break');
                                    $currentRow.find('.logout-time').text('On Break');
                                }
                            } else {
                                isTimerRunning = true;
                                startTimer(totalWorkSeconds);
                                $('#breakBtn').find('#breakText').text('Start Break');
                                $('#attendanceBtn').prop('disabled', false).css('opacity', '1');
                                $('#timerDisplay').show().text(formatTime(totalWorkSeconds)).addClass('text-gray-800').removeClass('text-red-500');
                                $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100');
                                $('#breakPulse').addClass('opacity-0').removeClass('opacity-100');
                                $('#attendancePulse').addClass('opacity-100').removeClass('opacity-0');
                                if ($currentRow.length) {
                                    $currentRow.removeClass('bg-yellow-100').addClass('active-attendance');
                                    $currentRow.find('td').removeClass('bg-yellow-100').addClass('active-attendance');
                                    $currentRow.find('.status').text(response.approval_status ? response.approval_status.charAt(0).toUpperCase() + response.approval_status.slice(1) : 'Pending');
                                    $currentRow.find('.logout-time').text('Still working');
                                }
                            }
                            if (!tableUpdateInterval) {
                                tableUpdateInterval = setInterval(() => {
                                    console.log('Table update triggered at', new Date().toISOString());
                                    updateAttendanceTable();
                                }, 30000);
                            }
                        } else {
                            if (response.hasCheckedOut) {
                                $('#attendanceControls').hide();
                                stopTimer();
                                totalWorkSeconds = 0;
                                isOnBreak = false;
                                $('#timerDisplay').hide().addClass('text-gray-800').removeClass('text-red-500').attr('style', 'display: none;');
                                $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100').attr('style', 'opacity: 0 !important;');
                                $('#breakPulse').addClass('opacity-0').removeClass('opacity-100').attr('style', 'opacity: 0 !important;');
                            } else {
                                $('#attendanceControls').show();
                                $('#attendanceBtn').css('background', 'linear-gradient(135deg, #10b981, #059669)').find('#attendanceText').text('Check-In');
                                $('#attendanceIcon').attr('src', '{{ asset('images/attendance/check-in.png') }}');
                                $('#breakBtn').prop('disabled', true).css('opacity', '0.5');
                                $('#timerDisplay').hide().addClass('text-gray-800').removeClass('text-red-500').attr('style', 'display: none;');
                                $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100').attr('style', 'opacity: 0 !important;');
                                $('#breakPulse').addClass('opacity-0').removeClass('opacity-100').attr('style', 'opacity: 0 !important;');
                                $('#attendancePulse').addClass('opacity-0').removeClass('opacity-100');
                                stopTimer();
                                totalWorkSeconds = 0;
                                isOnBreak = false;
                            }
                            if (tableUpdateInterval) {
                                clearInterval(tableUpdateInterval);
                                tableUpdateInterval = null;
                            }
                        }
                        updateAttendanceTable();
                        resolve(response);
                    },
                    error: function(xhr) {
                        console.error('Failed to fetch attendance status:', xhr.responseJSON?.message || 'An error occurred.');
                        $('#attendanceControls').hide();
                        $('#timerDisplay').hide().addClass('text-gray-800').removeClass('text-red-500').attr('style', 'display: none;');
                        $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100').attr('style', 'opacity: 0 !important;');
                        $('#breakPulse').addClass('opacity-0').removeClass('opacity-100').attr('style', 'opacity: 0 !important;');
                        $('#attendancePulse').addClass('opacity-0').removeClass('opacity-100');
                        stopTimer();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to fetch attendance status: ' + (xhr.responseJSON?.message || 'An error occurred.'),
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'rounded-lg',
                                confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                            }
                        });
                        reject(xhr);
                    }
                });
            });
        }

        // Initialize on DOM load
        document.addEventListener('DOMContentLoaded', async function() {
            console.log('DOM loaded, resetting timer, pause icon, and break pulse');
            totalWorkSeconds = 0;
            isTimerRunning = false;
            isOnBreak = false;
            lastSyncTime = null;
            lastSyncAttempt = 0;
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
            // Explicitly hide pause icon and break pulse on page load
            $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100');
            $('#breakPulse').addClass('opacity-0').removeClass('opacity-100');
            try {
                const editModalElement = document.getElementById('editAttendanceModal');
                if (editModalElement && typeof bootstrap !== 'undefined') {
                    const editModal = new bootstrap.Modal(editModalElement);
                    console.log('Bootstrap modal initialized');
                } else {
                    console.error('Bootstrap modal or element not found');
                }
            } catch (e) {
                console.error('Bootstrap modal initialization failed:', e);
            }

            @if($attendances->count() > 0)
            setTimeout(function() {
                try {
                    dataTable = $('#tasksTable').DataTable({
                        dom: "<'col-sm-12'tr>",
                        paging: false,
                        searching: false,
                        ordering: true,
                        info: false,
                        autoWidth: false,
                        responsive: true,
                        language: {
                            emptyTable: "No attendance records found for the given criteria."
                        }
                    });
                    console.log('DataTable initialized');
                } catch (e) {
                    console.error('DataTables initialization failed:', e);
                }
            }, 100);
            @endif

            // Wait for checkAttendanceStatus to complete
            await checkAttendanceStatus();
            // Sync timer only if not on break
            if (!isOnBreak) {
                await syncTimerWithServer();
            }
        });

        $(document).ready(function() {
            console.log('Document ready');

            // Attendance Button Click Handler
            $('#attendanceBtn').on('click', function() {
                console.log('Attendance button clicked');
                const isCheckOut = $('#attendanceText').text() === 'Check-Out';
                if (!isCheckOut) {
                    console.log('Initiating check-in');
                    Swal.fire({
                        title: 'Select Work Mode',
                        html: `
                            <select id="mode" class="swal2-select" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #d1d5db; font-size: 14px;">
                                <option value="">Choose Mode</option>
                                <option value="Work from office">Work from office</option>
                                <option value="Half Day">Half Day</option>
                                <option value="Work from Home">Work from Home</option>
                            </select>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Check In',
                        cancelButtonText: 'Cancel',
                        focusConfirm: false,
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700',
                            cancelButton: 'bg-gray-300 text-gray-800 px-3 py-1.5 rounded-md hover:bg-gray-400'
                        },
                        preConfirm: () => {
                            const mode = document.getElementById('mode').value;
                            if (!mode) {
                                Swal.showValidationMessage('Please select a work mode');
                                return false;
                            }
                            return mode;
                        }
                    }).then((result) => {
                        console.log('Check-in Swal result:', result);
                        if (result.isConfirmed) {
                            const selectedMode = result.value;
                            console.log('Check-in mode selected:', selectedMode);
                            $.ajax({
                                url: '/check-in',
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    mode: selectedMode
                                },
                                success: function(response) {
                                    console.log('Check-in AJAX response:', response);
                                    if (response.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success',
                                            text: 'Check-in successful',
                                            confirmButtonText: 'OK',
                                            customClass: {
                                                popup: 'rounded-lg',
                                                confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                                            }
                                        }).then(() => {
                                            console.log('Check-in success Swal closed');
                                            $('#attendanceControls').show();
                                            $('#attendanceBtn').css('background', 'linear-gradient(135deg, #ef4444, #b91c1c)').find('#attendanceText').text('Check-Out');
                                            $('#attendanceIcon').attr('src', '{{ asset('images/attendance/logout.png') }}');
                                            $('#breakBtn').prop('disabled', false).css('opacity', '1');
                                            $('#timerDisplay').show().addClass('text-gray-800').removeClass('text-red-500');
                                            $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100');
                                            $('#breakPulse').addClass('opacity-0').removeClass('opacity-100');
                                            $('#attendancePulse').addClass('opacity-100').removeClass('opacity-0');
                                            startTimer(0);
                                            updateAttendanceTable();
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: 'Error: ' + (response.message || 'Check-in failed.'),
                                            confirmButtonText: 'OK',
                                            customClass: {
                                                popup: 'rounded-lg',
                                                confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                                            }
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    console.log('Check-in AJAX error:', xhr);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Check-in failed: ' + (xhr.responseJSON?.error?.message || 'An error occurred.'),
                                        confirmButtonText: 'OK',
                                        customClass: {
                                            popup: 'rounded-lg',
                                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    } else {
                        console.log('Initiating check-out');
                        function performCheckout(forceCheckout = null) {
                            console.log('Performing checkout with force_checkout:', forceCheckout);
                            $.ajax({
                                url: '/check-out',
                                method: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    total_work_seconds: totalWorkSeconds,
                                    force_checkout: forceCheckout
                                },
                                success: function(response) {
                                    console.log('Check-out response:', JSON.stringify(response, null, 2));
                                    if (response.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success',
                                            html: `
                                                <div class="text-gray-700 text-sm">
                                                    <p>${response.message}</p>
                                                    <p>Login: ${response.login_time}, Logout: ${response.logout_time}</p>
                                                    <p>Total Break Time: ${response.total_break_formatted}</p>
                                                </div>
                                            `,
                                            confirmButtonText: 'OK',
                                            customClass: {
                                                popup: 'rounded-lg',
                                                confirmButton: 'bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700'
                                            }
                                        }).then(() => {
                                            console.log('Check-out success Swal closed');
                                            stopTimer();
                                            totalWorkSeconds = 0;
                                            isOnBreak = false;
                                            $('#attendanceControls').hide();
                                            $('#attendanceBtn').css('background', 'linear-gradient(135deg, #10b981, #059669)').find('#attendanceText').text('Check-In');
                                            $('#attendanceIcon').attr('src', '{{ asset('images/attendance/check-in.png') }}');
                                            $('#breakBtn').prop('disabled', true).css('opacity', '0.5');
                                            $('#timerDisplay').hide().addClass('text-gray-800').removeClass('text-red-500');
                                            $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100');
                                            $('#breakPulse').addClass('opacity-0').removeClass('opacity-100');
                                            $('#attendancePulse').addClass('opacity-0').removeClass('opacity-100');
                                            updateAttendanceTable();
                                        });
                                    } else if (response.incomplete_tasks && response.incomplete_tasks.length > 0) {
                                        console.log('Incomplete tasks detected:', response.incomplete_tasks);
                                        let taskList = '<ul class="list-disc pl-5 mt-2 text-left text-sm">';
                                        response.incomplete_tasks.forEach(task => {
                                            const escapedTaskName = task.taskName
                                                .replace(/</g, '<')
                                                .replace(/>/g, '>')
                                                .replace(/"/g, '"')
                                                .replace(/'/g, '');
                                            taskList += `<li class="mb-2"><a href="/my-tasks/${task.task_id}/details" class="text-blue-600 hover:underline font-medium">${escapedTaskName} (ID: ${task.task_id})</a></li>`;
                                        });
                                        taskList += '</ul>';
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Incomplete Tasks',
                                            html: `
                                                <div class="text-gray-700 text-sm">
                                                    <p class="mb-3">Please update the following tasks before checking out:</p>
                                                    ${taskList}
                                                </div>
                                            `,
                                            confirmButtonText: 'Go to My Tasks',
                                            cancelButtonText: 'Cancel',
                                            showCancelButton: true,
                                            customClass: {
                                                popup: 'rounded-lg max-w-md',
                                                title: 'text-lg font-bold text-gray-800',
                                                htmlContainer: 'text-sm',
                                                confirmButton: 'bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700',
                                                cancelButton: 'bg-gray-300 text-gray-800 px-3 py-1.5 rounded-md hover:bg-gray-400'
                                            }
                                        }).then((result) => {
                                            console.log('Incomplete tasks Swal result:', result);
                                            if (result.isConfirmed) {
                                                window.location.href = '{{ route("my_tasks.index") }}';
                                            }
                                        });
                                    } else if (response.leave_warning) {
                                        console.log('Leave warning triggered:', response);
                                        const message = response.message || 'You have not worked enough hours. Checking out now will mark your attendance as "Leave".';
                                        const escapedMessage = message
                                            .replace(/</g, '<')
                                            .replace(/>/g, '>')
                                            .replace(/"/g, '"')
                                            .replace(/'/g, '');
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Leave Warning',
                                            html: `
                                                <div class="text-gray-700 text-sm">
                                                    <p class="mb-3">${escapedMessage}</p>
                                                    <p>Proceed with check-out? This will mark your attendance as 'Leave'. Current login: ${response.login_time || 'N/A'}, Current logout: ${response.logout_time || 'N/A'}</p>
                                                    <p>Total Break Time: ${response.total_break_formatted}</p>
                                                </div>
                                            `,
                                            confirmButtonText: 'Proceed with Check-Out',
                                            cancelButtonText: 'Cancel',
                                            showCancelButton: true,
                                            customClass: {
                                                popup: 'rounded-lg max-w-md',
                                                title: 'text-lg font-bold text-gray-800',
                                                htmlContainer: 'text-sm',
                                                confirmButton: 'bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700',
                                                cancelButton: 'bg-gray-300 text-gray-800 px-3 py-1.5 rounded-md hover:bg-gray-400'
                                            }
                                        }).then((result) => {
                                            console.log('Leave Swal result:', result);
                                            if (result.isConfirmed) {
                                                console.log('Sending check-out AJAX for leave');
                                                performCheckout('leave');
                                            }
                                        });
                                    } else if (response.half_day_warning) {
                                        console.log('Half-day warning triggered:', response);
                                        const message = response.message || 'You have not worked enough hours for a full day. Checking out now will mark your attendance as "Half-day".';
                                        const escapedMessage = message
                                            .replace(/</g, '<')
                                            .replace(/>/g, '>')
                                            .replace(/"/g, '"')
                                            .replace(/'/g, '');
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Half Day Warning',
                                            html: `
                                                <div class="text-gray-700 text-sm">
                                                    <p class="mb-3">${escapedMessage}</p>
                                                    <p>Proceed with check-out? This will mark your attendance as 'Half Day'. Current login: ${response.login_time || 'N/A'}, Current logout: ${response.logout_time || 'N/A'}</p>
                                                    <p>Total Break Time: ${response.total_break_formatted}</p>
                                                </div>
                                            `,
                                            confirmButtonText: 'Proceed with Check-Out',
                                            cancelButtonText: 'Cancel',
                                            showCancelButton: true,
                                            customClass: {
                                                popup: 'rounded-lg max-w-md',
                                                title: 'text-lg font-bold text-gray-800',
                                                htmlContainer: 'text-sm',
                                                confirmButton: 'bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700',
                                                cancelButton: 'bg-gray-300 text-gray-800 px-3 py-1.5 rounded-md hover:bg-gray-400'
                                            }
                                        }).then((result) => {
                                            console.log('Half-day Swal result:', result);
                                            if (result.isConfirmed) {
                                                console.log('Sending check-out AJAX for half day');
                                                performCheckout('half_day');
                                            }
                                        });
                                    } else {
                                        console.log('Other check-out error:', response);
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: 'Error: ' + (response.message || 'Check-out failed.'),
                                            confirmButtonText: 'OK',
                                            customClass: {
                                                popup: 'rounded-lg',
                                                confirmButton: 'bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700'
                                            }
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    console.log('Check-out error:', JSON.stringify(xhr.responseJSON, null, 2));
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Check-out failed: ' + (xhr.responseJSON?.message || 'An error occurred.'),
                                        confirmButtonText: 'OK',
                                        customClass: {
                                            popup: 'rounded-lg',
                                            confirmButton: 'bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700'
                                        }
                                    });
                                }
                            });
                        }

                        Swal.fire({
                            title: 'Confirm Check-Out',
                            text: 'Are you sure you want to check out? This action will log your logout time.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, Check Out',
                            cancelButtonText: 'Cancel',
                            customClass: {
                                popup: 'rounded-lg',
                                confirmButton: 'bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700',
                                cancelButton: 'bg-gray-300 text-gray-800 px-3 py-1.5 rounded-md hover:bg-gray-400'
                            }
                        }).then((result) => {
                            console.log('Check-out Swal result:', result);
                            if (result.isConfirmed) {
                                console.log('Sending initial check-out AJAX');
                                performCheckout();
                            }
                        });
                    }
                });

                // Break Button Click Handler
                $('#breakBtn').on('click', function() {
                    console.log('Break button clicked');
                    const isBreakEnd = $('#breakText').text() === 'End Break';
                    const action = isBreakEnd ? 'end' : 'start';

                    // Stop timer immediately for break start to prevent increments
                    if (action === 'start') {
                        stopTimer();
                    }

                    $.ajax({
                        url: '/break',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            action: action,
                            total_work_seconds: action === 'start' ? totalWorkSeconds : null
                        },
                        success: function(response) {
                            console.log('Break AJAX response:', response);
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1000,
                                    timerProgressBar: true,
                                    customClass: {
                                        popup: 'rounded-lg'
                                    }
                                }).then(() => {
                                    console.log('Break success Swal closed');
                                    totalWorkSeconds = response.total_work_seconds ?? totalWorkSeconds;
                                    isOnBreak = response.is_on_break;
                                    const $currentRow = $(`#attendance_${response.attendance_id || ''}`);
                                    if (action === 'start') {
                                        isTimerRunning = false;
                                        stopTimer();
                                        $('#breakBtn').find('#breakText').text('End Break');
                                        $('#attendanceBtn').prop('disabled', true).css('opacity', '0.5');
                                        $('#timerDisplay').text(formatTime(totalWorkSeconds)).addClass('text-red-500').removeClass('text-gray-800');
                                        $('#pauseIcon').addClass('opacity-100').removeClass('opacity-0');
                                        $('#breakPulse').addClass('opacity-100').removeClass('opacity-0');
                                        $('#attendancePulse').addClass('opacity-0').removeClass('opacity-100');
                                        if ($currentRow.length) {
                                            $currentRow.removeClass('active-attendance').addClass('bg-yellow-100');
                                            $currentRow.find('td').removeClass('active-attendance').addClass('bg-yellow-100');
                                            $currentRow.find('.status').text('On Break');
                                            $currentRow.find('.logout-time').text('On Break');
                                            $currentRow.data('is-on-break', true);
                                            $currentRow.data('total-work-seconds', totalWorkSeconds);
                                            $currentRow.data('total-break-seconds', response.total_break_seconds || 0);
                                            $currentRow.find('.total-break-hours').text(formatTimeFriendly(response.total_break_seconds || 0));
                                        }
                                    } else {
                                        isTimerRunning = true;
                                        startTimer(totalWorkSeconds);
                                        $('#breakBtn').find('#breakText').text('Start Break');
                                        $('#attendanceBtn').prop('disabled', false).css('opacity', '1');
                                        $('#timerDisplay').text(formatTime(totalWorkSeconds)).addClass('text-gray-800').removeClass('text-red-500');
                                        $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100');
                                        $('#breakPulse').addClass('opacity-0').removeClass('opacity-100');
                                        $('#attendancePulse').addClass('opacity-100').removeClass('opacity-0');
                                        if ($currentRow.length) {
                                            $currentRow.removeClass('bg-yellow-100').addClass('active-attendance');
                                            $currentRow.find('td').removeClass('bg-yellow-100').addClass('active-attendance');
                                            $currentRow.find('.status').text(response.approval_status ? response.approval_status.charAt(0).toUpperCase() + response.approval_status.slice(1) : 'Pending');
                                            $currentRow.find('.logout-time').text('Still working');
                                            $currentRow.data('is-on-break', false);
                                            $currentRow.data('total-work-seconds', totalWorkSeconds);
                                            $currentRow.data('total-break-seconds', response.total_break_seconds || 0);
                                            $currentRow.find('.total-break-hours').text(formatTimeFriendly(response.total_break_seconds || 0));
                                        }
                                    }
                                    updateAttendanceTable();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Error: ' + (response.message || 'Break action failed.'),
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        popup: 'rounded-lg',
                                        confirmButton: 'bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700'
                                    }
                                });
                                if (action === 'start') {
                                    // Restart timer if break start fails
                                    startTimer(totalWorkSeconds);
                                }
                            }
                        },
                        error: function(xhr) {
                            console.log('Break AJAX error:', xhr);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Break action failed: ' + (xhr.responseJSON?.error?.message || 'An error occurred.'),
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'rounded-lg',
                                    confirmButton: 'bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700'
                                }
                            });
                            if (action === 'start') {
                                // Restart timer if break start fails
                                startTimer(totalWorkSeconds);
                            }
                        }
                    });
                });

                // Edit Attendance Button Handler
                $(document).on('click', '.editAttendanceBtn', function() {
                    console.log('Edit button clicked');
                    try {
                        const id = $(this).data('id');
                        const login = $(this).data('login');
                        const logout = $(this).data('logout');
                        const mode = $(this).data('mode');

                        console.log('Edit data:', { id, login, logout, mode });

                        $('#attendance_id').val(id);
                        $('#edit_login').val(login);
                        $('#edit_logout').val(logout || '');
                        $('#edit_mode').val(mode);

                        const editModalElement = document.getElementById('editAttendanceModal');
                        if (editModalElement && typeof bootstrap !== 'undefined') {
                            const editModal = new bootstrap.Modal(editModalElement);
                            editModal.show();
                            console.log('Edit modal shown');
                        } else {
                            console.error('Bootstrap modal or element not found');
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Modal initialization failed. Please ensure Bootstrap is loaded correctly.',
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'rounded-lg',
                                    confirmButton: 'bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700'
                                }
                            });
                        }
                    } catch (e) {
                        console.error('Error opening edit modal:', e);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to open edit modal: ' + e.message,
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'rounded-lg',
                                confirmButton: 'bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700'
                            }
                        });
                    }
                });

                // Edit Attendance Form Submission
                $('#editAttendanceForm').on('submit', function(e) {
                    e.preventDefault();
                    console.log('Edit form submitted');
                    const formData = $(this).serialize();
                    $.ajax({
                        url: '{{ route("attendance.update") }}',
                        method: 'POST',
                        data: formData,
                        success: function(response) {
                            console.log('Edit AJAX response:', response);
                            const id = response.id;
                            const row = $(`#attendance_${id}`);
                            if (row.length) {
                                row.find('.login-time').text(new Date(response.created_at).toLocaleTimeString('en-US', { hour12: true, hour: 'numeric', minute: '2-digit', second: '2-digit' }));
                                row.find('.logout-time').text(response.is_on_break ? 'On Break' : (response.logout ? new Date(response.logout).toLocaleTimeString('en-US', { hour12: true, hour: 'numeric', minute: '2-digit', second: '2-digit' }) : 'Still working'));
                                row.find('.mode').text(response.mode.charAt(0).toUpperCase() + response.mode.slice(1));
                                const statusText = response.is_on_break ? 'On Break' : (response.approval_status ? response.approval_status.charAt(0).toUpperCase() + response.approval_status.slice(1) : 'Pending');
                                row.find('.status').text(statusText);
                                row.find('.total-work-hours').text(formatTimeFriendly(response.total_work_seconds || 0));
                                row.find('.total-break-hours').text(formatTimeFriendly(response.total_break_seconds || 0));
                                row.data('total-work-seconds', response.total_work_seconds || 0);
                                row.data('is-on-break', response.is_on_break || false);
                                row.data('total-break-seconds', response.total_break_seconds || 0);
                                const rowClass = response.is_on_break ? 'bg-yellow-100' : (!response.logout && response.attendance_date === '{{ \Carbon\Carbon::today('Asia/Kolkata')->toDateString() }}' ? 'active-attendance' : '');
                                row.removeClass('bg-yellow-100 active-attendance').addClass(rowClass);
                                row.find('td').removeClass('bg-yellow-100 active-attendance').addClass(rowClass);
                                if (response.user_id === currentUserId) {
                                    totalWorkSeconds = response.total_work_seconds || 0;
                                    isOnBreak = response.is_on_break || false;
                                    lastSyncTime = new Date();
                                    if (!response.is_on_break && !response.logout) {
                                        isTimerRunning = true;
                                        startTimer(totalWorkSeconds);
                                        $('#timerDisplay').show().text(formatTime(totalWorkSeconds)).addClass('text-gray-800').removeClass('text-red-500');
                                        $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100');
                                        $('#breakPulse').addClass('opacity-0').removeClass('opacity-100');
                                        $('#attendancePulse').addClass('opacity-100').removeClass('opacity-0');
                                    } else {
                                        isTimerRunning = false;
                                        stopTimer();
                                        $('#timerDisplay').show().text(formatTime(totalWorkSeconds)).addClass('text-red-500').removeClass('text-gray-800');
                                        $('#pauseIcon').addClass('opacity-100').removeClass('opacity-0');
                                        $('#breakPulse').addClass('opacity-100').removeClass('opacity-0');
                                        $('#attendancePulse').addClass('opacity-0').removeClass('opacity-100');
                                    }
                                }
                            }
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Attendance updated successfully.',
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'rounded-lg',
                                    confirmButton: 'bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700'
                                }
                            }).then(() => {
                                const editModal = bootstrap.Modal.getInstance(document.getElementById('editAttendanceModal'));
                                if (editModal) {
                                    editModal.hide();
                                }
                                updateAttendanceTable();
                            });
                        },
                        error: function(xhr) {
                            console.error('Edit AJAX error:', xhr);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to update attendance: ' + (xhr.responseJSON?.error?.message || 'An error occurred.'),
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'rounded-lg',
                                    confirmButton: 'bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700'
                                }
                            });
                        }
                    });
                });

                // Approve Attendance Button Handler
                $(document).on('click', '.approveAttendanceBtn', function() {
                    console.log('Approve button clicked');
                    const id = $(this).data('id');
                    Swal.fire({
                        title: 'Confirm Approval',
                        text: 'Are you sure you want to approve this attendance record?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Approve',
                        cancelButtonText: 'Cancel',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700',
                            cancelButton: 'bg-gray-300 text-gray-800 px-3 py-1.5 rounded-md hover:bg-gray-400'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '{{ route("attendance.approve") }}',
                                method: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    id: id
                                },
                                success: function(response) {
                                    console.log('Approve AJAX response:', response);
                                    const row = $(`#attendance_${id}`);
                                    if (row.length) {
                                        row.find('.status').text('Approved');
                                        row.find('.approveAttendanceBtn').replaceWith('<span class="badge bg-success" style="padding: 4px 8px; border-radius: 4px;">Verified</span>');
                                    }
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: 'Attendance approved successfully.',
                                        confirmButtonText: 'OK',
                                        customClass: {
                                            popup: 'rounded-lg',
                                            confirmButton: 'bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700'
                                        }
                                    });
                                    updateAttendanceTable();
                                },
                                error: function(xhr) {
                                    console.error('Approve AJAX error:', xhr);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Failed to approve attendance: ' + (xhr.responseJSON?.error?.message || 'An error occurred.'),
                                        confirmButtonText: 'OK',
                                        customClass: {
                                            popup: 'rounded-lg',
                                            confirmButton: 'bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700'
                                        }
                                    });
                                }
                            });
                        }
                    });
                });

                // Sync timer on focus to ensure accuracy
                $(window).on('focus', function() {
                    console.log('Window focus event');
                    if ($('#attendanceText').text() === 'Check-Out' && !isOnBreak) {
                        syncTimerWithServer().then(() => {
                            checkAttendanceStatus();
                        });
                    }
                });
            });
    </script>

    <style type="text/css">
        #attendanceBtn:hover:not(:disabled), #breakBtn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        #attendanceBtn:disabled, #breakBtn:disabled {
            cursor: not-allowed;
        }
        .animate-pulse {
            animation: pulse 0.3s ease-in-out;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .text-red-500 {
            --tw-text-opacity: 1;
            color: rgb(239 68 68 / var(--tw-text-opacity, 1)) !important;
        }
        /* Ensure Bootstrap modal styles are not overridden by Tailwind */
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
        .form-control {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }
        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }
    </style>
@endsection
