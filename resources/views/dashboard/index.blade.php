@extends('layouts.app')

@section('content')
<div class="nk-content">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="nk-block-head nk-block-head-sm">
                    <div class="nk-block-between">
                        <div class="nk-block-head-content">
                            <h3 class="nk-block-title page-title">Admin Dashboard</h3>
                            <div class="nk-block-des text-soft">
                                {{-- <p>Welcome, {{ $uname }} (ID: {{ $uid }})</p> --}}
                            </div>
                        </div>
                        <div class="nk-block-head-content">
                            <!-- Optional tools -->
                        </div>
                    </div>
                </div>
                <div class="nk-block">
                    <div class="row g-gs">
                        <!-- Global Counts -->
                        <div class="col-sm-6 col-lg-3">
                            <div class="card card-bordered bg-gradient-to-r from-blue-100 to-blue-200">
                                <div class="card-inner">
                                    <div class="card-title-group align-start mb-2">
                                        <div class="card-title">
                                            <h6 class="title">Total Employees</h6>
                                        </div>
                                        <div class="card-tools">
                                            <em class="icon ni ni-users text-blue-500"></em>
                                        </div>
                                    </div>
                                    <div class="nk-sale-data">
                                        <span class="amount text-blue-600">{{ $total_employees }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card card-bordered bg-gradient-to-r from-green-100 to-green-200">
                                <div class="card-inner">
                                    <div class="card-title-group align-start mb-2">
                                        <div class="card-title">
                                            <h6 class="title">Total Projects</h6>
                                        </div>
                                        <div class="card-tools">
                                            <em class="icon ni ni-briefcase text-green-500"></em>
                                        </div>
                                    </div>
                                    <div class="nk-sale-data">
                                        <span class="amount text-green-600">{{ $total_projects }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card card-bordered bg-gradient-to-r from-purple-100 to-purple-200">
                                <div class="card-inner">
                                    <div class="card-title-group align-start mb-2">
                                        <div class="card-title">
                                            <h6 class="title">Total Services</h6>
                                        </div>
                                        <div class="card-tools">
                                            <em class="icon ni ni-server text-purple-500"></em>
                                        </div>
                                    </div>
                                    <div class="nk-sale-data">
                                        <span class="amount text-purple-600">{{ $total_services }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="card card-bordered bg-gradient-to-r from-yellow-100 to-yellow-200">
                                <div class="card-inner">
                                    <div class="card-title-group align-start mb-2">
                                        <div class="card-title">
                                            <h6 class="title">Total Clients</h6>
                                        </div>
                                        <div class="card-tools">
                                            <em class="icon ni ni-users-fill text-yellow-500"></em>
                                        </div>
                                    </div>
                                    <div class="nk-sale-data">
                                        <span class="amount text-yellow-600">{{ $total_clients }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Attendance Cards -->
                        <div class="col-sm-6 col-lg-4">
                            <div class="card card-bordered bg-gradient-to-r from-teal-100 to-teal-200">
                                <div class="card-inner">
                                    <div class="card-title-group align-start mb-2">
                                        <div class="card-title">
                                            <h6 class="title">Work From Office</h6>
                                        </div>
                                        <div class="card-tools">
                                            <em class="icon ni ni-building text-teal-500"></em>
                                        </div>
                                    </div>
                                    <div class="nk-sale-data">
                                        <span class="amount text-teal-600">{{ $work_from_office }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="card card-bordered bg-gradient-to-r from-indigo-100 to-indigo-200">
                                <div class="card-inner">
                                    <div class="card-title-group align-start mb-2">
                                        <div class="card-title">
                                            <h6 class="title">Work From Home</h6>
                                        </div>
                                        <div class="card-tools">
                                            <em class="icon ni ni-home text-indigo-500"></em>
                                        </div>
                                    </div>
                                    <div class="nk-sale-data">
                                        <span class="amount text-indigo-600">{{ $work_from_home }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="card card-bordered bg-gradient-to-r from-red-100 to-red-200">
                                <div class="card-inner">
                                    <div class="card-title-group align-start mb-2">
                                        <div class="card-title">
                                            <h6 class="title">Leave Count</h6>
                                        </div>
                                        <div class="card-tools">
                                            <em class="icon ni ni-signout text-red-500"></em>
                                        </div>
                                    </div>
                                    <div class="nk-sale-data">
                                        <span class="amount text-red-600">{{ $leave_count }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Reminders Cards -->
                        <div class="col-lg-6">
                            <div class="card card-bordered bg-gradient-to-r from-gray-50 to-gray-100">
                                <div class="card-inner">
                                    <div class="card-title-group align-start mb-2">
                                        <div class="card-title">
                                            <h6 class="title">This Week Reminders</h6>
                                        </div>
                                        <div class="card-tools">
                                            <em class="icon ni ni-calendar text-blue-500"></em>
                                        </div>
                                    </div>
                                    <div id="thisWeekReminders" class="max-h-60 overflow-y-auto">
                                        <div class="text-center text-gray-500 py-4">Loading reminders...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card card-bordered bg-gradient-to-r from-gray-50 to-gray-100">
                                <div class="card-inner">
                                    <div class="card-title-group align-start mb-2">
                                        <div class="card-title">
                                            <h6 class="title">This Month Reminders</h6>
                                        </div>
                                        <div class="card-tools">
                                            <em class="icon ni ni-calendar text-blue-500"></em>
                                        </div>
                                    </div>
                                    <div id="thisMonthReminders" class="max-h-60 overflow-y-auto">
                                        <div class="text-center text-gray-500 py-4">Loading reminders...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- .row -->
                </div><!-- .nk-block -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.tailwindcss.com"></script>
<script>
$(document).ready(function() {
    // CSRF Token Setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Function to get type badge color
    function getTypeBadgeColor(type) {
        const colors = {
            'Report Submission': 'bg-blue-100 text-blue-800',
            'Meeting': 'bg-green-100 text-green-800',
            'Follow Up': 'bg-yellow-100 text-yellow-800',
            'Other': 'bg-gray-100 text-gray-800'
        };
        return colors[type] || 'bg-gray-100 text-gray-800';
    }

    // Function to render reminders
    function renderReminders(containerId, reminders) {
        const $container = $(`#${containerId}`);
        if (reminders.length === 0) {
            $container.html('<div class="text-center text-gray-500 py-4">No reminders scheduled.</div>');
            return;
        }

        // Sort reminders: is_overdue first, then is_today, then by date
        reminders.sort((a, b) => {
            if (a.is_overdue !== b.is_overdue) {
                return b.is_overdue - a.is_overdue;
            }
            if (a.is_today !== b.is_today) {
                return b.is_today - a.is_today;
            }
            return a.date.localeCompare(b.date);
        });

        let html = '<ul class="divide-y divide-gray-200">';
        reminders.forEach(reminder => {
            console.log('Rendering reminder:', reminder); // Debug log
            const isThisMonth = containerId === 'thisMonthReminders';
            const datesDisplay = isThisMonth ? reminder.frequency_display : `${reminder.display_date} at ${reminder.time_of_day}`;

            // Card color logic: Prioritize completed, then overdue, then is_today
            let cardColor = 'bg-white';
            if (reminder.status === 'completed') {
                cardColor = 'bg-gradient-to-r from-green-50 to-green-100';
            } else if (reminder.is_overdue) {
                cardColor = 'bg-gradient-to-r from-red-50 to-red-100 animate-pulse';
            } else if (reminder.is_today) {
                cardColor = 'bg-gradient-to-r from-blue-50 to-blue-100';
            }

            // Border class logic: Prioritize completed, then overdue, then is_today
            let borderClass = 'border-l-4 border-transparent';
            if (reminder.status === 'completed') {
                borderClass = 'border-l-4 border-green-500';
            } else if (reminder.is_overdue) {
                borderClass = 'border-l-4 border-red-500';
            } else if (reminder.is_today) {
                borderClass = 'border-l-4 border-blue-500';
            }

            const statusChecked = reminder.status === 'completed' ? 'checked' : '';
            const todayBadge = reminder.is_today ? '<span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-500 text-white ml-2">Today</span>' : '';

            // Show checkbox for overdue or today's not completed tasks in this_week
            const checkboxHtml = (containerId === 'thisWeekReminders' && (reminder.is_overdue || (reminder.is_today && reminder.status === 'not_completed'))) ? `
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only status-toggle" data-id="${reminder.id}" data-date="${reminder.date}" ${statusChecked}>
                    <div class="w-10 h-5 bg-gray-200 rounded-full shadow-inner"></div>
                    <div class="dot absolute w-6 h-6 rounded-full shadow -left-1 -top-0.5 transition ${reminder.status === 'completed' ? 'translate-x-6 bg-green-500' : 'bg-gray-400'}"></div>
                </label>
            ` : '';

            html += `
                <li class="py-3 px-4 hover:bg-gray-50 transition duration-150 reminder-card ${cardColor} ${borderClass}" data-id="${reminder.id}" data-date="${reminder.date}">
                    <div class="flex justify-between items-center">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <div class="relative">
                                    <div class="w-3 h-3 rounded-full ${reminder.status === 'completed' ? 'bg-green-500' : (reminder.is_overdue ? 'bg-red-500' : (reminder.is_today ? 'bg-blue-500' : 'bg-gray-300'))}"></div>
                                </div>
                                <div>
                                    <h6 class="text-sm font-medium text-gray-800">${reminder.title}${todayBadge}</h6>
                                    <div class="mt-1">
                                        <p class="text-xs text-gray-500">${reminder.project}${reminder.services !== 'N/A' ? ' (' + reminder.services + ')' : ''}</p>
                                        <p class="text-xs text-gray-500">${datesDisplay}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="text-xs font-semibold px-2 py-1 rounded-full ${getTypeBadgeColor(reminder.type)}">${reminder.type}</span>
                            ${checkboxHtml}
                        </div>
                    </div>
                </li>
            `;
        });
        html += '</ul>';
        $container.html(html);
    }

    // Function to load upcoming reminders
    function loadUpcomingReminders() {
        $.ajax({
            url: "{{ route('reminders.upcoming') }}",
            type: "GET",
            dataType: "json",
            cache: false,
            success: function(response) {
                console.log('Upcoming reminders response:', response); // Debug log
                if (response.success) {
                    renderReminders('thisWeekReminders', response.this_week);
                    renderReminders('thisMonthReminders', response.this_month);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to load reminders.',
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'rounded-lg',
                            confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                        }
                    });
                    $('#thisWeekReminders, #thisMonthReminders').html('<div class="text-center text-gray-500 py-4">Failed to load reminders.</div>');
                }
            },
            error: function(xhr) {
                console.error('Error loading reminders:', {
                    status: xhr.status,
                    responseText: xhr.responseText,
                    responseJSON: xhr.responseJSON
                });
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to load reminders.',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'rounded-lg',
                        confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                    }
                });
                $('#thisWeekReminders, #thisMonthReminders').html('<div class="text-center text-gray-500 py-4">Failed to load reminders.</div>');
            }
        });
    }

    // Handle status toggle with confirmation
    $(document).on('change', '.status-toggle', function() {
        const reminderId = $(this).data('id');
        const reportDate = $(this).data('date');
        const newStatus = $(this).is(':checked') ? 'completed' : 'not_completed';
        const $checkbox = $(this);
        const $dot = $(this).siblings('.dot');
        const $li = $(this).closest('.reminder-card');

        Swal.fire({
            title: 'Confirm Status Update',
            text: `Are you sure you want to mark this reminder as ${newStatus === 'completed' ? 'Completed' : 'Not Completed'} for ${new Date(reportDate).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' })}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, update it!',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'rounded-lg',
                confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700',
                cancelButton: 'bg-gray-300 text-gray-800 px-3 py-1.5 rounded-md hover:bg-gray-400'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('reminders') }}/" + reminderId + "/status",
                    type: "PATCH",
                    data: { status: newStatus, report_date: reportDate },
                    success: function(response) {
                        console.log('Status update response:', response); // Debug log
                        if (response.success) {
                            $dot.toggleClass('bg-gray-400 bg-green-500 translate-x-6', newStatus === 'completed');
                            // Update card and border classes
                            if (newStatus === 'completed') {
                                $li.removeClass('bg-white bg-gradient-to-r from-blue-50 to-blue-100 bg-gradient-to-r from-red-50 to-red-100 animate-pulse')
                                   .addClass('bg-gradient-to-r from-green-50 to-green-100');
                                $li.removeClass('border-l-4 border-transparent border-l-4 border-blue-500 border-l-4 border-red-500')
                                   .addClass('border-l-4 border-green-500');
                            } else {
                                $li.removeClass('bg-gradient-to-r from-green-50 to-green-100');
                                $li.removeClass('border-l-4 border-green-500');
                                if ($li.find('.bg-blue-500.text-white').length) {
                                    $li.addClass('bg-gradient-to-r from-blue-50 to-blue-100 border-l-4 border-blue-500');
                                } else if (new Date(reportDate) < new Date()) {
                                    $li.addClass('bg-gradient-to-r from-red-50 to-red-100 animate-pulse border-l-4 border-red-500');
                                } else {
                                    $li.addClass('bg-white border-l-4 border-transparent');
                                }
                            }
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            // Delay refresh to ensure DB update is reflected
                            setTimeout(loadUpcomingReminders, 1000);
                        } else {
                            $checkbox.prop('checked', newStatus === 'not_completed');
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to update status.',
                                confirmButtonText: 'OK',
                                customClass: {
                                    popup: 'rounded-lg',
                                    confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        $checkbox.prop('checked', newStatus === 'not_completed');
                        console.error('Error updating status:', {
                            status: xhr.status,
                            responseText: xhr.responseText,
                            responseJSON: xhr.responseJSON
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Failed to update status.',
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'rounded-lg',
                                confirmButton: 'bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700'
                            }
                        });
                    }
                });
            } else {
                $checkbox.prop('checked', newStatus === 'not_completed');
            }
        });
    });

    // Initial load of reminders
    loadUpcomingReminders();
});
</script>
<style>
.card-bordered {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease-in-out;
}
.card-bordered:hover {
    transform: translateY(-2px);
}
.card-inner {
    padding: 1.5rem;
}
.max-h-60 {
    max-height: 15rem;
}
.text-soft {
    color: #6b7280;
}
.nk-sale-data .amount {
    font-size: 1.75rem;
    font-weight: 700;
    line-height: 1;
}
.status-toggle:checked + .dot {
    transform: translateX(1.25rem);
    background-color: #10b981 !important;
}
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
.reminder-card {
    border: none !important;
    border-left: 4px solid transparent !important;
}
.reminder-card.border-l-4 {
    border-left-width: 4px !important;
    border-top-width: 0 !important;
    border-right-width: 0 !important;
    border-bottom-width: 0 !important;
}
.reminder-card.border-blue-500 {
    border-left-color: #3b82f6 !important;
}
.reminder-card.border-green-500 {
    border-left-color: #10b981 !important;
}
.reminder-card.border-red-500 {
    border-left-color: #ef4444 !important;
}
.reminder-card.border-transparent {
    border-left-color: transparent !important;
}
</style>
@endpush
@endsection
