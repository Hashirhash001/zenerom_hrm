@extends('layouts.app')

@section('content')
<div class="container px-6 py-6">
    <h1 class="mt-5 mb-4 text-2xl font-bold text-gray-800" style="margin-top: 70px !important;">Attendance Dashboard</h1>

    <!-- Filter Form -->
    <form id="filterForm" action="{{ route('attendance.index') }}" method="GET" class="mb-4 bg-white p-4 rounded-lg shadow-md">
        <div class="row g-3">
            <!-- Start Date -->
            <div class="col-md-3">
                <label for="start_date" class="block text-xs font-medium text-gray-700">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $start_date }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-1">
            </div>
            <!-- End Date -->
            <div class="col-md-3">
                <label for="end_date" class="block text-xs font-medium text-gray-700">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $end_date }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-1">
            </div>
            <!-- Submit Button -->
            <div class="col-md-1 align-self-end">
                <button type="submit" class="w-full bg-indigo-600 text-white py-1 px-2 rounded-md hover:bg-indigo-700 transition duration-300 ease-in-out transform hover:-translate-y-1">
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </form>

    <!-- Attendance Controls -->
    <div id="attendanceControls" style="display: none;">
        <div class="mb-4 bg-white p-2 rounded-lg shadow-md flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <!-- Attendance Button -->
                <button type="button" class="relative attendanceBtn" id="attendanceBtn" style="background: linear-gradient(135deg, #10b981, #059669); border: none; padding: 8px 16px; border-radius: 6px; color: white; font-weight: 600; transition: transform 0.3s ease, box-shadow 0.3s ease;">
                    <img src="{{ asset('images/attendance/check-in.png') }}" alt="Check-In" class="inline-block w-5 h-5 mr-1.5" id="attendanceIcon" width="20" height="20">
                    <span id="attendanceText">Check-In</span>
                    <span class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-xs font-bold rounded-full h-4 w-4 flex items-center justify-center opacity-0 transition-opacity duration-300" id="attendancePulse">!</span>
                </button>
                <!-- Timer Display -->
                <div class="flex items-center bg-gray-100 rounded-md px-3 py-1.5">
                    <svg class="w-5 h-5 text-gray-600 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span id="timerDisplay" class="text-base font-mono text-gray-800" style="display: none;">00:00:00</span>
                    <svg id="pauseIcon" class="w-8 h-8 text-red-500 opacity-0 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6"></path>
                    </svg>
                </div>
                <!-- Break Button -->
                <button type="button" class="relative breakBtn" id="breakBtn" style="background: linear-gradient(135deg, #f59e0b, #d97706); border: none; padding: 8px 16px; border-radius: 6px; color: white; font-weight: 600; transition: transform 0.3s ease, box-shadow 0.3s ease;" disabled>
                    <img src="{{ asset('images/attendance/coffee-break.png') }}" alt="Break" class="inline-block w-5 h-5 mr-1.5" width="20" height="20">
                    <span id="breakText">Start Break</span>
                    <span class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-xs font-bold rounded-full h-4 w-4 flex items-center justify-center opacity-0 transition-opacity duration-300" id="breakPulse">!</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Attendance Records Table -->
    <div id="attendanceTableContainer">
        @if($attendances->count() > 0)
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="table table-bordered w-full text-xs" id="tasksTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Login</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logout</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Work Hours</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mode</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">System IP</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $today = \Carbon\Carbon::today('Asia/Kolkata')->toDateString();
                    @endphp
                    @foreach($attendances as $attendance)
                        <tr id="attendance_{{ $attendance->id }}" class="{{ is_null($attendance->logout) && $attendance->attendance_date == $today ? 'active-attendance' : '' }} hover:bg-gray-50 transition duration-150">
                            <td class="px-4 py-2 whitespace-nowrap">{{ $attendance->attendance_date }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $attendance->employee_name }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $attendance->created_at }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $attendance->logout ? $attendance->logout : 'Still working' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ number_format(floor(($attendance->total_work_seconds ?? 0) / 36) / 100, 2) }} hours</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ ucfirst($attendance->mode) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $attendance->system_ip }}</td>
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
<!-- Bootstrap CSS and JS (for modal compatibility) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<!-- Tailwind CSS CDN for modern styling -->
<script src="https://cdn.tailwindcss.com"></script>

<script>
    // Timer variables
    let timerInterval = null;
    let totalWorkSeconds = 0;
    let isTimerRunning = false;
    let dataTable = null;

    // Function to format seconds to decimal hours
    function formatHours(seconds) {
        const hours = Math.floor(seconds / 36) / 100;
        return `${hours.toFixed(2)} hours`;
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
        if (isTimerRunning) {
            totalWorkSeconds++;
            $('#timerDisplay').text(formatTime(totalWorkSeconds)).addClass('animate-pulse').addClass('text-gray-800').removeClass('text-red-500');
            setTimeout(() => $('#timerDisplay').removeClass('animate-pulse'), 200);
            // Update Total Work Hours in the active attendance row without pulsing
            const $activeRow = $('.active-attendance');
            if ($activeRow.length) {
                $activeRow.find('td').eq(4).text(formatHours(totalWorkSeconds));
            }
        }
    }

    // Function to start timer
    function startTimer(startSeconds = 0) {
        totalWorkSeconds = startSeconds;
        isTimerRunning = true;
        $('#timerDisplay').show().text(formatTime(totalWorkSeconds)).addClass('text-gray-800').removeClass('text-red-500');
        $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100');
        timerInterval = setInterval(updateTimer, 1000);
        $('#attendancePulse').addClass('opacity-100').removeClass('opacity-0');
        $('#breakPulse').addClass('opacity-0').removeClass('opacity-100');
    }

    // Function to stop timer
    function stopTimer() {
        isTimerRunning = false;
        clearInterval(timerInterval);
        $('#timerDisplay').show().text(formatTime(totalWorkSeconds)).addClass('text-red-500').removeClass('text-gray-800');
        $('#attendancePulse').addClass('opacity-0').removeClass('opacity-100');
    }

    // Function to update attendance table
    function updateAttendanceTable() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        $.ajax({
            url: '/fetch-attendances',
            type: 'GET',
            data: {
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                const tableContainer = $('#attendanceTableContainer');
                const today = '{{ \Carbon\Carbon::today('Asia/Kolkata')->toDateString() }}';
                if (response.attendances.length > 0) {
                    let tableHtml = `
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <table class="table table-bordered w-full text-xs" id="tasksTable">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Name</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Login</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logout</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Work Hours</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mode</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">System IP</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                    `;
                    response.attendances.forEach(attendance => {
                        const isActive = !attendance.logout && attendance.attendance_date === today;
                        const hours = Math.floor((attendance.total_work_seconds || 0) / 36) / 100;
                        tableHtml += `
                            <tr id="attendance_${attendance.id}" class="${isActive ? 'active-attendance' : ''} hover:bg-gray-50 transition duration-150">
                                <td class="px-4 py-2 whitespace-nowrap">${attendance.attendance_date}</td>
                                <td class="px-4 py-2 whitespace-nowrap">${attendance.employee_name}</td>
                                <td class="px-4 py-2 whitespace-nowrap">${attendance.created_at}</td>
                                <td class="px-4 py-2 whitespace-nowrap">${attendance.logout ? attendance.logout : 'Still working'}</td>
                                <td class="px-4 py-2 whitespace-nowrap">${hours.toFixed(2)} hours</td>
                                <td class="px-4 py-2 whitespace-nowrap">${attendance.mode.charAt(0).toUpperCase() + attendance.mode.slice(1)}</td>
                                <td class="px-4 py-2 whitespace-nowrap">${attendance.system_ip}</td>
                            </tr>
                        `;
                    });
                    tableHtml += `
                                </tbody>
                            </table>
                        </div>
                    `;
                    tableContainer.html(tableHtml);
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
                    // Update active row to match timer
                    const $activeRow = $('.active-attendance');
                    if ($activeRow.length) {
                        $activeRow.find('td').eq(4).text(formatHours(totalWorkSeconds));
                    }
                } else {
                    tableContainer.html(`
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
            }
        });
    }

    // Function to get current attendance status
    function checkAttendanceStatus() {
        $.ajax({
            url: '/attendance-status',
            type: 'GET',
            success: function(response) {
                if (response.isCheckedIn && !response.hasCheckedOut) {
                    $('#attendanceControls').show();
                    $('#attendanceBtn').css('background', 'linear-gradient(135deg, #ef4444, #b91c1c)').find('#attendanceText').text('Check-Out');
                    $('#attendanceIcon').attr('src', '{{ asset('images/attendance/logout.png') }}');
                    $('#breakBtn').prop('disabled', false).css('opacity', '1');
                    totalWorkSeconds = response.totalWorkSeconds;
                    if (response.isOnBreak) {
                        stopTimer();
                        $('#breakBtn').find('#breakText').text('End Break');
                        $('#attendanceBtn').prop('disabled', true).css('opacity', '0.5');
                        $('#timerDisplay').show().text(formatTime(totalWorkSeconds)).addClass('text-red-500').removeClass('text-gray-800');
                        $('#pauseIcon').addClass('opacity-100').removeClass('opacity-0');
                        $('#breakPulse').addClass('opacity-100').removeClass('opacity-0');
                        // Update table with totalWorkSeconds
                        const $activeRow = $('.active-attendance');
                        if ($activeRow.length) {
                            $activeRow.find('td').eq(4).text(formatHours(totalWorkSeconds));
                        }
                    } else {
                        startTimer(response.totalWorkSeconds);
                        $('#breakBtn').find('#breakText').text('Start Break');
                        $('#attendanceBtn').prop('disabled', false).css('opacity', '1');
                        $('#timerDisplay').addClass('text-gray-800').removeClass('text-red-500');
                        $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100');
                        $('#breakPulse').addClass('opacity-0').removeClass('opacity-100');
                    }
                } else {
                    if (response.hasCheckedOut) {
                        $('#attendanceControls').hide();
                    } else {
                        $('#attendanceControls').show();
                        $('#attendanceBtn').css('background', 'linear-gradient(135deg, #10b981, #059669)').find('#attendanceText').text('Check-In');
                        $('#attendanceIcon').attr('src', '{{ asset('images/attendance/check-in.png') }}');
                        $('#breakBtn').prop('disabled', true).css('opacity', '0.5');
                        $('#timerDisplay').hide().addClass('text-gray-800').removeClass('text-red-500');
                        $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100');
                        $('#breakPulse').addClass('opacity-0').removeClass('opacity-100');
                        stopTimer();
                    }
                }
                // Update table for checked-out rows
                updateAttendanceTable();
            },
            error: function(xhr) {
                console.error('Failed to fetch attendance status:', xhr.responseJSON?.message || 'An error occurred.');
                $('#attendanceControls').hide();
                $('#timerDisplay').addClass('text-gray-800').removeClass('text-red-500');
                $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
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
            } catch (e) {
                console.error('DataTables initialization failed:', e);
            }
        }, 100);
        @endif

        // Check initial attendance status
        checkAttendanceStatus();

        // Handle filter form submission via AJAX
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            updateAttendanceTable();
        });
    });

    $(document).ready(function() {
        // Attendance Button Click Handler
        $('#attendanceBtn').on('click', function() {
            const isCheckOut = $('#attendanceText').text() === 'Check-Out';
            if (!isCheckOut) {
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
                    if (result.isConfirmed) {
                        const selectedMode = result.value;
                        $.ajax({
                            url: '/check-in',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                mode: selectedMode
                            },
                            success: function(response) {
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
                                        $('#attendanceControls').show();
                                        $('#attendanceBtn').css('background', 'linear-gradient(135deg, #ef4444, #b91c1c)').find('#attendanceText').text('Check-Out');
                                        $('#attendanceIcon').attr('src', '{{ asset('images/attendance/logout.png') }}');
                                        $('#breakBtn').prop('disabled', false).css('opacity', '1');
                                        $('#timerDisplay').addClass('text-gray-800').removeClass('text-red-500');
                                        $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100');
                                        $('#breakPulse').addClass('opacity-0').removeClass('opacity-100');
                                        startTimer(0);
                                        updateAttendanceTable();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Error: ' + response.message,
                                        confirmButtonText: 'OK',
                                        customClass: {
                                            popup: 'rounded-lg',
                                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                                        }
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Check-in failed: ' + (xhr.responseJSON?.message || 'An error occurred.'),
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
                    Swal.fire({
                        title: 'Confirm Check-Out',
                        text: 'Are you sure you want to check out? This action will log your logout time.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Check Out',
                        cancelButtonText: 'Cancel',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700',
                            cancelButton: 'bg-gray-300 text-gray-800 px-3 py-1.5 rounded-md hover:bg-gray-400'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '/check-out',
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    total_work_seconds: totalWorkSeconds
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success',
                                            text: 'Check-out successful.',
                                            confirmButtonText: 'OK',
                                            customClass: {
                                                popup: 'rounded-lg',
                                                confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                                            }
                                        }).then(() => {
                                            stopTimer();
                                            $('#attendanceControls').hide();
                                            $('#attendanceBtn').css('background', 'linear-gradient(135deg, #10b981, #059669)').find('#attendanceText').text('Check-In');
                                            $('#attendanceIcon').attr('src', '{{ asset('images/attendance/check-in.png') }}');
                                            $('#breakBtn').prop('disabled', true).css('opacity', '0.5');
                                            $('#timerDisplay').hide().addClass('text-gray-800').removeClass('text-red-500');
                                            $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100');
                                            $('#breakPulse').addClass('opacity-0').removeClass('opacity-100');
                                            updateAttendanceTable();
                                        });
                                    } else if (response.incomplete_tasks && response.incomplete_tasks.length > 0) {
                                        let taskList = '<ul class="list-disc pl-5 mt-2 text-left text-xs">';
                                        response.incomplete_tasks.forEach(task => {
                                            taskList += `<li class="mb-2"><a href="/my-tasks/${task.task_id}/details" class="text-blue-600 hover:underline font-medium">${task.task_name} (ID: ${task.task_id})</a></li>`;
                                        });
                                        taskList += '</ul>';
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Incomplete Tasks',
                                            html: `
                                                <div class="text-gray-700 text-xs">
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
                                                htmlContainer: 'text-xs',
                                                confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700',
                                                cancelButton: 'bg-gray-300 text-gray-800 px-3 py-1.5 rounded-md hover:bg-gray-400'
                                            }
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                window.location.href = '{{ route("my_tasks.index") }}';
                                            }
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: 'Error: ' + response.message,
                                            confirmButtonText: 'OK',
                                            customClass: {
                                                popup: 'rounded-lg',
                                                confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                                            }
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Check-out failed: ' + (xhr.responseJSON?.message || 'An error occurred.'),
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
                }
            });

            // Break Button Click Handler
            $('#breakBtn').on('click', function() {
                const isBreakEnd = $('#breakText').text() === 'End Break';
                const action = isBreakEnd ? 'end' : 'start';
                $.ajax({
                    url: '/break',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        action: action
                    },
                    success: function(response) {
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
                                if (action === 'start') {
                                    stopTimer();
                                    $('#breakBtn').find('#breakText').text('End Break');
                                    $('#attendanceBtn').prop('disabled', true).css('opacity', '0.5');
                                    $('#timerDisplay').addClass('text-red-500').removeClass('text-gray-800');
                                    $('#pauseIcon').addClass('opacity-100').removeClass('opacity-0');
                                    $('#breakPulse').addClass('opacity-100').removeClass('opacity-0');
                                } else {
                                    startTimer(totalWorkSeconds);
                                    $('#breakBtn').find('#breakText').text('Start Break');
                                    $('#attendanceBtn').prop('disabled', false).css('opacity', '1');
                                    $('#timerDisplay').addClass('text-gray-800').removeClass('text-red-500');
                                    $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100');
                                    $('#breakPulse').addClass('opacity-0').removeClass('opacity-100');
                                }
                                updateAttendanceTable();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error: ' + response.message,
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'rounded-lg',
                                    confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Break action failed: ' + (xhr.responseJSON?.message || 'An error occurred.'),
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'rounded-lg',
                                confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                            }
                        });
                    }
                });
            });

            // Prevent timer manipulation
            $(window).on('focus blur', function(e) {
                if (e.type === 'blur' && isTimerRunning) {
                    stopTimer();
                    $.ajax({
                        url: '/sync-timer',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            total_work_seconds: totalWorkSeconds
                        },
                        success: function(response) {
                            console.log('Timer synced on blur');
                        }
                    });
                } else if (e.type === 'focus' && $('#attendanceText').text() === 'Check-Out' && $('#breakText').text() === 'Start Break') {
                    checkAttendanceStatus();
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
</style>
@endsection
