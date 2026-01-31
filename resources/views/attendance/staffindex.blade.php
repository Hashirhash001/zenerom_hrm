@extends('layouts.app')

@section('content')
<div class="container px-6 py-6">
    <h1 class="mt-5 mb-4 text-2xl font-bold text-gray-800" style="margin-top: 70px !important;">Attendance Dashboard</h1>

    <!-- Filter Form -->
    <form id="filterForm" action="{{ route('attendance.index') }}" method="GET" class="mb-4 bg-white p-4 rounded-lg shadow-md">
        <div class="row g-3">
            <div class="col-md-3">
                <label for="start_date" class="block text-xs font-medium text-gray-700">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $start_date }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-1" onclick="this.showPicker()">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="block text-xs font-medium text-gray-700">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $end_date }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-1" onclick="this.showPicker()">
            </div>
            <div class="col-md-3">
                <label for="name" class="block text-xs font-medium text-gray-700">Staff Name/ID</label>
                <input type="text" name="name" id="name" value="{{ $nameFilter ?? '' }}" placeholder="Enter name or ID" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-xs p-1">
            </div>
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
                <!-- Break Button with Dropdown -->
                <div class="relative inline-block" id="breakContainer">
                    <button type="button" class="relative breakBtn" id="breakBtn"
                            style="background: linear-gradient(135deg, #f59e0b, #d97706); border: none; padding: 8px 16px; border-radius: 6px; color: white; font-weight: 600; transition: transform 0.3s ease, box-shadow 0.3s ease;"
                            disabled>
                        <img src="{{ asset('images/attendance/coffee-break.png') }}" alt="Break" class="inline-block w-5 h-5 mr-1.5" width="20" height="20">
                        <span id="breakText">Start Break</span>
                        <svg class="inline-block w-4 h-4 ml-1" id="breakDropdownIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        <span class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-xs font-bold rounded-full h-4 w-4 flex items-center justify-center opacity-0 transition-opacity duration-300" id="breakPulse">!</span>
                    </button>

                    <!-- Dropdown menu -->
                    <div id="breakDropdown" class="hidden absolute z-10 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5" style="left: 0;">
                        <div class="py-1" role="menu">
                            <button class="break-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-duration="5">5 minutes</button>
                            <button class="break-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-duration="10">10 minutes</button>
                            <button class="break-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-duration="15">15 minutes</button>
                            <button class="break-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-duration="20">20 minutes</button>
                            <button class="break-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-duration="25">25 minutes</button>
                            <button class="break-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-duration="30">30 minutes</button>
                            <button class="break-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-duration="35">35 minutes</button>
                            <button class="break-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-duration="40">40 minutes</button>
                            <button class="break-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-duration="45">45 minutes</button>
                            <button class="break-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-duration="50">50 minutes</button>
                            <button class="break-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-duration="55">55 minutes</button>
                            <button class="break-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-duration="60">60 minutes</button>
                            <hr class="my-1">
                            <button class="break-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-duration="manual">Manual (no auto-end)</button>
                        </div>
                    </div>
                </div>

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
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Work Hours</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Break Hours</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mode</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">System IP</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $today = \Carbon\Carbon::today('Asia/Kolkata')->toDateString();
                    @endphp
                    @foreach($attendances as $attendance)
                        <tr id="attendance_{{ $attendance->id }}" class="{{ is_null($attendance->logout) && $attendance->attendance_date == $today ? ($attendance->is_on_break ? 'bg-yellow-100 !important' : 'active-attendance') : '' }} hover:bg-gray-50 transition duration-150" data-total-work-seconds="{{ $attendance->total_work_seconds ?? 0 }}" data-is-on-break="{{ $attendance->is_on_break ? 'true' : 'false' }}" data-total-break-seconds="{{ $attendance->total_break_seconds ?? 0 }}">
                            <td class="px-4 py-2 whitespace-nowrap text-xs {{ is_null($attendance->logout) && $attendance->attendance_date == $today && $attendance->is_on_break ? 'bg-yellow-100 !important' : '' }}">{{ $attendance->attendance_date }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-xs {{ is_null($attendance->logout) && $attendance->attendance_date == $today && $attendance->is_on_break ? 'bg-yellow-100 !important' : '' }}">{{ $attendance->employee_name }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-xs {{ is_null($attendance->logout) && $attendance->attendance_date == $today && $attendance->is_on_break ? 'bg-yellow-100 !important' : '' }}">{{ \Carbon\Carbon::parse($attendance->created_at)->format('h:i:s A') }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-xs logout-time {{ is_null($attendance->logout) && $attendance->attendance_date == $today && $attendance->is_on_break ? 'bg-yellow-100 !important' : '' }}">{{ $attendance->logout ? \Carbon\Carbon::parse($attendance->logout)->format('h:i:s A') : ($attendance->is_on_break ? 'On Break' : 'Still working') }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-right text-xs font-mono total-work-hours {{ is_null($attendance->logout) && $attendance->attendance_date == $today && $attendance->is_on_break ? 'bg-yellow-100 !important' : '' }}">{{ floor(($attendance->total_work_seconds ?? 0) / 3600) }}h {{ floor((($attendance->total_work_seconds ?? 0) % 3600) / 60) }}min</td>
                            <td class="px-4 py-2 whitespace-nowrap text-right text-xs font-mono total-break-hours {{ is_null($attendance->logout) && $attendance->attendance_date == $today && $attendance->is_on_break ? 'bg-yellow-100 !important' : '' }}">{{ floor(($attendance->total_break_seconds ?? 0) / 3600) }}h {{ floor((($attendance->total_break_seconds ?? 0) % 3600) / 60) }}min</td>
                            <td class="px-4 py-2 whitespace-nowrap text-xs {{ is_null($attendance->logout) && $attendance->attendance_date == $today && $attendance->is_on_break ? 'bg-yellow-100 !important' : '' }}">{{ ucfirst($attendance->mode) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-xs {{ is_null($attendance->logout) && $attendance->attendance_date == $today && $attendance->is_on_break ? 'bg-yellow-100 !important' : '' }}">{{ $attendance->system_ip }}</td>
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
<!-- Tailwind CSS CDN for modern styling -->
<script src="https://cdn.tailwindcss.com"></script>

<script>
    // ==================== GLOBAL VARIABLES ====================
    let timerInterval = null;
    let totalWorkSeconds = 0;
    let totalBreakSeconds = 0;
    let isTimerRunning = false;
    let isOnBreak = false;
    let lastSyncTime = null;
    let lastSyncAttempt = 0;
    let dataTable = null;
    let tableUpdateInterval = null;
    const SYNC_DEBOUNCE_MS = 1000;
    const currentUserId = {{ Auth::id() }};

    // Break-specific variables
    let breakTimerInterval = null;
    let breakEndTime = null;
    let currentBreakDuration = null;

    // ==================== HELPER FUNCTIONS ====================

    // Function to format seconds to HH:MM:SS for timer display
    function formatTime(seconds) {
        const hrs = Math.floor(seconds / 3600);
        const mins = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        return `${String(hrs).padStart(2, '0')}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }

    // Function to format seconds to friendly time (e.g., "1h 10min")
    function formatTimeFriendly(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        let result = '';
        if (hours > 0) result += `${hours}h `;
        if (minutes > 0 || hours === 0) result += `${minutes}min`;
        return result.trim() || '0min';
    }

    // ==================== TIMER FUNCTIONS ====================

    // Function to start timer
    function startTimer(startSeconds = 0) {
        totalWorkSeconds = startSeconds;
        isTimerRunning = true;
        $('#timerDisplay').show().text(formatTime(totalWorkSeconds)).css('color', '#1f2937 !important').attr('style', 'color: #1f2937 !important;');
        $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100');
        $('#attendancePulse').addClass('opacity-100').removeClass('opacity-0');
        $('#breakPulse').addClass('opacity-0').removeClass('opacity-100');
        if (!timerInterval) {
            timerInterval = setInterval(updateTimer, 1000);
        }
    }

    // Function to stop timer
    function stopTimer() {
        isTimerRunning = false;
        clearInterval(timerInterval);
        timerInterval = null;
        $('#timerDisplay').show().text(formatTime(totalWorkSeconds)).css('color', '#ef4444 !important').attr('style', 'color: #ef4444 !important;');
        $('#attendancePulse').addClass('opacity-0').removeClass('opacity-100');
    }

    // Function to update timer display and table
    function updateTimer() {
        if (isTimerRunning && !isOnBreak && lastSyncTime) {
            totalWorkSeconds++;
            $('#timerDisplay').text(formatTime(totalWorkSeconds)).addClass('animate-pulse').css('color', '#1f2937 !important');
            setTimeout(() => $('#timerDisplay').removeClass('animate-pulse'), 200);
            const $currentRow = $(`#attendance_${currentUserId}`);
            if ($currentRow.length) {
                $currentRow.find('.total-work-hours').text(formatTimeFriendly(totalWorkSeconds));
                $currentRow.data('total-work-seconds', totalWorkSeconds);
            }
            if (totalWorkSeconds % 30 === 0 && !isOnBreak) {
                syncTimerWithServer();
            }
        }
    }

    // ==================== BREAK FUNCTIONALITY ====================

    // Toggle dropdown on button click
    $('#breakBtn').on('click', function(e) {
        e.stopPropagation();
        const breakText = $('#breakText').text();

        if (!$(this).prop('disabled')) {
            if (breakText === 'Start Break') {
                // Show dropdown to select duration
                $('#breakDropdown').toggleClass('hidden');
            } else if (breakText === 'End Break' || breakText.includes('Break (')) {
                // Manually end break
                endBreak();
            }
        }
    });

    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#breakContainer').length) {
            $('#breakDropdown').addClass('hidden');
        }
    });

    // Handle break option selection
    $('.break-option').on('click', function(e) {
        e.stopPropagation();
        const duration = $(this).data('duration');
        $('#breakDropdown').addClass('hidden');

        if (duration === 'manual') {
            startBreak(null);
        } else {
            startBreak(duration);
        }
    });

    // Start break function
    function startBreak(duration) {
        console.log('Starting break with duration:', duration);

        $.ajax({
            url: '{{ route("break") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                action: 'start',
                duration: duration,
                total_work_seconds: totalWorkSeconds
            },
            success: function(response) {
                console.log('Break start response:', response);

                if (response.success) {
                    isOnBreak = true;
                    totalWorkSeconds = response.total_work_seconds || totalWorkSeconds;
                    totalBreakSeconds = response.total_break_seconds || totalBreakSeconds;
                    currentBreakDuration = duration;

                    stopTimer();

                    if (duration) {
                        $('#breakText').text(`Break (${duration}m)`);
                        $('#breakDropdownIcon').hide();
                    } else {
                        $('#breakText').text('End Break');
                        $('#breakDropdownIcon').hide();
                    }

                    $('#attendanceBtn').prop('disabled', true).css('opacity', 0.5);
                    $('#timerDisplay').show().text(formatTime(totalWorkSeconds))
                        .css('color', '#ef4444 !important');
                    $('#pauseIcon').addClass('opacity-100').removeClass('opacity-0');
                    $('#breakPulse').addClass('opacity-100').removeClass('opacity-0');
                    $('#attendancePulse').addClass('opacity-0').removeClass('opacity-100');

                    // Update current row in table
                    const $currentRow = $(`#attendance_${currentUserId}`);
                    if ($currentRow.length) {
                        $currentRow.removeClass('active-attendance').addClass('bg-yellow-100 !important');
                        $currentRow.find('td').addClass('bg-yellow-100 !important');
                        $currentRow.find('.logout-time').text('On Break');
                        $currentRow.data('is-on-break', true);
                        $currentRow.data('total-break-seconds', totalBreakSeconds);
                        $currentRow.find('.total-break-hours').text(formatTimeFriendly(totalBreakSeconds));
                    }

                    // Set up auto-end if duration is provided
                    if (duration && response.autoEndTime) {
                        breakEndTime = new Date(response.autoEndTime);
                        startBreakCountdown();
                    }

                    updateAttendanceTable();

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        confirmButtonText: 'OK',
                        timer: 2000,
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700'
                        }
                    });
                }
            },
            error: function(xhr) {
                console.error('Break start error:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to start break',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'rounded-lg',
                        confirmButton: 'bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700'
                    }
                });
            }
        });
    }

    // End break function
    function endBreak() {
        console.log('Ending break');

        if (breakTimerInterval) {
            clearInterval(breakTimerInterval);
            breakTimerInterval = null;
        }

        $.ajax({
            url: '{{ route("break") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                action: 'end'
            },
            success: function(response) {
                console.log('Break end response:', response);

                if (response.success) {
                    isOnBreak = false;
                    totalWorkSeconds = response.total_work_seconds || totalWorkSeconds;
                    totalBreakSeconds = response.total_break_seconds || totalBreakSeconds;
                    currentBreakDuration = null;
                    breakEndTime = null;

                    startTimer(totalWorkSeconds);
                    $('#breakText').text('Start Break');
                    $('#breakDropdownIcon').show();
                    $('#attendanceBtn').prop('disabled', false).css('opacity', 1);
                    $('#breakPulse').removeClass('opacity-100').addClass('opacity-0');
                    $('#attendancePulse').addClass('opacity-100').removeClass('opacity-0');

                    // Update current row in table
                    const $currentRow = $(`#attendance_${currentUserId}`);
                    if ($currentRow.length) {
                        $currentRow.removeClass('bg-yellow-100 !important').addClass('active-attendance');
                        $currentRow.find('td').removeClass('bg-yellow-100 !important');
                        $currentRow.find('.logout-time').text('Still working');
                        $currentRow.data('is-on-break', false);
                        $currentRow.data('total-break-seconds', totalBreakSeconds);
                        $currentRow.find('.total-break-hours').text(formatTimeFriendly(totalBreakSeconds));
                    }

                    updateAttendanceTable();

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Break ended successfully',
                        confirmButtonText: 'OK',
                        timer: 2000,
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700'
                        }
                    });
                }
            },
            error: function(xhr) {
                console.error('Break end error:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to end break',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'rounded-lg',
                        confirmButton: 'bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700'
                    }
                });
            }
        });
    }

    // Break countdown function
    function startBreakCountdown() {
        if (breakTimerInterval) {
            clearInterval(breakTimerInterval);
        }

        breakTimerInterval = setInterval(function() {
            const now = new Date();
            const timeLeft = Math.max(0, Math.floor((breakEndTime - now) / 1000));

            if (timeLeft > 0) {
                const minutesLeft = Math.ceil(timeLeft / 60);
                $('#breakText').text(`Break (${minutesLeft}m left)`);
            } else {
                // Auto-end break
                clearInterval(breakTimerInterval);
                breakTimerInterval = null;

                Swal.fire({
                    icon: 'info',
                    title: 'Break Time Over',
                    text: 'Your break time has ended. Resuming work timer.',
                    confirmButtonText: 'OK',
                    timer: 3000,
                    customClass: {
                        popup: 'rounded-lg',
                        confirmButton: 'bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700'
                    }
                });

                endBreak();
            }
        }, 1000);
    }

    // ==================== SYNC AND UPDATE FUNCTIONS ====================

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
                    total_work_seconds: isOnBreak ? null : totalWorkSeconds
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
                                $currentRow.removeClass('active-attendance').addClass('bg-yellow-100 !important');
                                $currentRow.find('td').addClass('bg-yellow-100 !important');
                                $currentRow.find('.logout-time').text('On Break');
                                stopTimer();
                            } else if (!response.logout) {
                                $currentRow.removeClass('bg-yellow-100 !important').addClass('active-attendance');
                                $currentRow.find('td').removeClass('bg-yellow-100 !important');
                                $currentRow.find('.logout-time').text('Still working');
                                if (!isTimerRunning) startTimer(totalWorkSeconds);
                            }
                        }
                        $('#timerDisplay').text(formatTime(totalWorkSeconds));
                        if (isOnBreak || response.logout) {
                            $('#timerDisplay').css('color', '#ef4444 !important').attr('style', 'color: #ef4444 !important;');
                            stopTimer();
                        } else {
                            $('#timerDisplay').css('color', '#1f2937 !important').attr('style', 'color: #1f2937 !important;');
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
        const nameFilter = $('#name').val();
        $.ajax({
            url: '/fetch-attendances',
            type: 'GET',
            data: {
                start_date: startDate,
                end_date: endDate,
                name: nameFilter
            },
            success: function(response) {
                console.log('Fetch attendances response:', response);
                const tableContainer = $('#attendanceTableContainer');
                const today = '{{ \Carbon\Carbon::today('Asia/Kolkata')->toDateString() }}';
                if (response.attendances && response.attendances.length > 0) {
                    let tableHtml = `
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <table class="table table-bordered w-full text-xs" id="tasks-table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Name</th>
                                        <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Login</th>
                                        <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logout</th>
                                        <th class="px-2 py-1 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Work Hours</th>
                                        <th class="px-2 py-1 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Break Hours</th>
                                        <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mode</th>
                                        <th class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">System IP</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                    `;
                    response.attendances.forEach(attendance => {
                        const isActive = !attendance.logout && attendance.attendance_date === today;
                        const isOnBreak = attendance.is_on_break;
                        const workHours = formatTimeFriendly(attendance.total_work_seconds || 0);
                        const breakHours = formatTimeFriendly(attendance.total_break_seconds || 0);
                        const loginTime = new Date(attendance.created_at).toLocaleTimeString('en-US', { hour12: true });
                        const logoutTime = isOnBreak ? 'On Break' : (attendance.logout ? new Date(attendance.logout).toLocaleTimeString('en-US', { hour12: true }) : 'Still working');
                        tableHtml += `
                            <tr id="attendance_${attendance.id}" class="${isActive ? (isOnBreak ? 'bg-yellow-100 !important' : 'active-attendance') : ''} hover:bg-gray-50 transition duration-150" data-total-work-seconds="${attendance.total_work_seconds || 0}" data-is-on-break="${isOnBreak}" data-total-break-seconds="${attendance.total_break_seconds || 0}">
                                <td class="px-2 py-1 whitespace-nowrap text-xs ${isActive && isOnBreak ? 'bg-yellow-100 !important' : ''}">${attendance.attendance_date}</td>
                                <td class="px-2 py-1 whitespace-nowrap text-xs ${isActive && isOnBreak ? 'bg-yellow-100 !important' : ''}">${attendance.employee_name}</td>
                                <td class="px-2 py-1 whitespace-nowrap text-xs ${isActive && isOnBreak ? 'bg-yellow-100 !important' : ''}">${loginTime}</td>
                                <td class="px-2 py-1 whitespace-nowrap text-xs logout-time ${isActive && isOnBreak ? 'bg-yellow-100 !important' : ''}">${logoutTime}</td>
                                <td class="px-2 py-1 whitespace-nowrap text-right text-xs font-mono total-work-hours ${isActive && isOnBreak ? 'bg-yellow-100 !important' : ''}">${workHours}</td>
                                <td class="px-2 py-1 whitespace-nowrap text-right text-xs font-mono total-break-hours ${isActive && isOnBreak ? 'bg-yellow-100 !important' : ''}">${breakHours}</td>
                                <td class="px-2 py-1 whitespace-nowrap text-xs ${isActive && isOnBreak ? 'bg-yellow-100 !important' : ''}">${attendance.mode.charAt(0).toUpperCase() + attendance.mode.slice(1)}</td>
                                <td class="px-2 py-1 whitespace-nowrap text-xs ${isActive && isOnBreak ? 'bg-yellow-100 !important' : ''}">${attendance.system_ip}</td>
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
                    dataTable = $('#tasks-table').DataTable({
                        dom: "<'col-sm-12'tr>",
                        paging: false,
                        searching: false,
                        ordering: true,
                        info: false,
                        autoWidth: true,
                        responsive: true,
                        language: {
                            emptyTable: "No attendance records found for the given criteria."
                        },
                        columnDefs: [
                            { targets: [4, 5], className: 'dt-right' }
                        ]
                    });
                    const $currentRow = $(`#attendance_${currentUserId}`);
                    if ($currentRow.length) {
                        $currentRow.find('.total-work-hours').text(formatTimeFriendly(totalWorkSeconds));
                        $currentRow.find('.total-break-hours').text(formatTimeFriendly(totalBreakSeconds || 0));
                    }
                } else {
                    tableContainer.html(`
                        <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-600 text-xs">
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
                $('#attendanceTableContainer').html(`
                    <div class="bg-white rounded-lg shadow-md p-4 text-center text-gray-600 text-xs">
                        No attendance records found.
                    </div>
                `);
                if (dataTable) {
                    dataTable.destroy();
                    dataTable = null;
                }
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
                        totalBreakSeconds = response.breakSeconds || 0;
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
                            $('#breakDropdownIcon').hide();
                            $('#attendanceBtn').prop('disabled', true).css('opacity', '0.5');
                            $('#timerDisplay').show().text(formatTime(totalWorkSeconds)).css('color', '#ef4444 !important').attr('style', 'color: #ef4444 !important;');
                            $('#pauseIcon').addClass('opacity-100').removeClass('opacity-0');
                            $('#breakPulse').addClass('opacity-100').removeClass('opacity-0');
                            $('#attendancePulse').addClass('opacity-0').removeClass('opacity-100');
                            if ($currentRow.length) {
                                $currentRow.removeClass('active-attendance').addClass('bg-yellow-100 !important');
                                $currentRow.find('td').addClass('bg-yellow-100 !important');
                                $currentRow.find('.logout-time').text('On Break');
                            }
                        } else {
                            isTimerRunning = true;
                            startTimer(totalWorkSeconds);
                            $('#breakBtn').find('#breakText').text('Start Break');
                            $('#breakDropdownIcon').show();
                            $('#attendanceBtn').prop('disabled', false).css('opacity', '1');
                            $('#timerDisplay').show().text(formatTime(totalWorkSeconds)).css('color', '#1f2937 !important').attr('style', 'color: #1f2937 !important;');
                            $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100');
                            $('#breakPulse').addClass('opacity-0').removeClass('opacity-100');
                            $('#attendancePulse').addClass('opacity-100').removeClass('opacity-0');
                            if ($currentRow.length) {
                                $currentRow.removeClass('bg-yellow-100 !important').addClass('active-attendance');
                                $currentRow.find('td').removeClass('bg-yellow-100 !important');
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
                            totalBreakSeconds = 0;
                            isOnBreak = false;
                        } else {
                            $('#attendanceControls').show();
                            $('#attendanceBtn').css('background', 'linear-gradient(135deg, #10b981, #059669)').find('#attendanceText').text('Check-In');
                            $('#attendanceIcon').attr('src', '{{ asset('images/attendance/check-in.png') }}');
                            $('#breakBtn').prop('disabled', true).css('opacity', '0.5');
                            $('#timerDisplay').hide().css('color', '#1f2937 !important').attr('style', 'color: #1f2937 !important;');
                            $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100');
                            $('#breakPulse').addClass('opacity-0').removeClass('opacity-100');
                            $('#attendancePulse').addClass('opacity-0').removeClass('opacity-100');
                            stopTimer();
                            totalWorkSeconds = 0;
                            totalBreakSeconds = 0;
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
                    $('#timerDisplay').css('color', '#1f2937 !important').attr('style', 'color: #1f2937 !important;');
                    $('#pauseIcon').addClass('opacity-0').removeClass('opacity-100');
                    $('#breakPulse').addClass('opacity-0').removeClass('opacity-100');
                    $('#attendancePulse').addClass('opacity-0').removeClass('opacity-100');
                    stopTimer();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to fetch attendance status: ' + (xhr.responseJSON?.message || 'An error occurred.'),
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700'
                        }
                    });
                    reject(xhr);
                }
            });
        });
    }

    // ==================== ATTENDANCE BUTTON CLICK HANDLER ====================

    $('#attendanceBtn').on('click', function() {
        console.log('Attendance button clicked');
        const isCheckOut = $('#attendanceText').text() === 'Check-Out';
        if (!isCheckOut) {
            // Check-in logic (keep your existing check-in code here)
            // ... (your existing check-in code)
        } else {
            // Check-out logic (keep your existing check-out code here)
            // ... (your existing check-out code)
        }
    });

    // ==================== INITIALIZATION ====================

    // Initialize DataTable and check attendance status on page load
    document.addEventListener('DOMContentLoaded', async function() {
        console.log('DOM loaded, resetting timer');
        totalWorkSeconds = 0;
        totalBreakSeconds = 0;
        isTimerRunning = false;
        isOnBreak = false;
        lastSyncTime = null;
        lastSyncAttempt = 0;
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
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
                    autoWidth: true,
                    responsive: true,
                    language: {
                        emptyTable: "No attendance records found for the given criteria."
                    },
                    columnDefs: [
                        { targets: [4, 5], className: 'dt-right' }
                    ]
                });
                console.log('DataTable initialized');
            } catch (e) {
                console.error('DataTables initialization failed:', e);
            }
        }, 100);
        @endif

        // Wait for checkAttendanceStatus to complete
        await checkAttendanceStatus();
        if (!isOnBreak) {
            await syncTimerWithServer();
        }
    });

    // Handle filter form submission
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        updateAttendanceTable();
    });
</script>

<style>
    .active-attendance {
        background-color: #d1fae5 !important;
    }
    .active-attendance td {
        background-color: #d1fae5 !important;
    }
    .bg-yellow-100 {
        background-color: #fef9c3 !important;
    }
</style>


@endsection
