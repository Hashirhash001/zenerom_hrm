@extends('layouts.app')

@section('content')
<div class="nk-content">
  <div class="container-fluid">
    <div class="nk-content-inner">
      <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
          <div class="nk-block-between">
            <div class="nk-block-head-content">
              <h3 class="nk-block-title page-title">Staff Dashboard</h3>
              <div class="nk-block-des text-soft">
                <p>Welcome, {{ $uname }} (ID: {{ $uid }})</p>
              </div>
            </div>
            <div class="nk-block-head-content">
              <!-- Optional tools -->
            </div>
          </div>
        </div>
        <div class="nk-block">
          <div class="row g-gs">
            <div class="col-12">
              <div class="card card-bordered">
                <div class="card-inner">
                  <div class="card-title-group">
                    <div class="card-title">
                      <h5 class="title">My Dashboard</h5>
                    </div>
                  </div>
                  <p>This dashboard is for individual staff. No aggregate data is available.</p>
                </div>
              </div>
            </div>
            <!-- Reminders Cards -->
            <div class="col-lg-6">
              <div class="card card-bordered">
                <div class="card-inner">
                  <div class="card-title-group align-start mb-2">
                    <div class="card-title">
                      <h6 class="title">This Week Reminders</h6>
                    </div>
                  </div>
                  <div id="thisWeekReminders" class="max-h-60 overflow-y-auto">
                    <div class="text-center text-gray-500 py-4">Loading reminders...</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="card card-bordered">
                <div class="card-inner">
                  <div class="card-title-group align-start mb-2">
                    <div class="card-title">
                      <h6 class="title">This Month Reminders</h6>
                    </div>
                  </div>
                  <div id="thisMonthReminders" class="max-h-60 overflow-y-auto">
                    <div class="text-center text-gray-500 py-4">Loading reminders...</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
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
    // Function to format dates for display
    function formatReminderDates(dates, frequency, timeOfDay) {
        if (!dates || !dates.length) return 'N/A';
        if (frequency === 'daily') {
            return `Daily at ${timeOfDay}`;
        } else if (frequency === 'weekly') {
            const days = dates.map(date => {
                return new Date(date).toLocaleDateString('en-US', { weekday: 'short' });
            });
            return `${days.join(', ')} at ${timeOfDay}`;
        } else if (frequency === 'monthly') {
            const date = new Date(dates[0]);
            return `${date.toLocaleDateString('en-US', { day: 'numeric', month: 'short' })} at ${timeOfDay}`;
        } else {
            const date = new Date(dates[0]);
            return `${date.toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' })} at ${timeOfDay}`;
        }
    }

    // Function to render reminders
    function renderReminders(containerId, reminders) {
        const $container = $(`#${containerId}`);
        if (reminders.length === 0) {
            $container.html('<div class="text-center text-gray-500 py-4">No reminders scheduled.</div>');
            return;
        }

        let html = '<ul class="divide-y divide-gray-200">';
        reminders.forEach(reminder => {
            const datesDisplay = formatReminderDates(reminder.dates, reminder.frequency, reminder.time_of_day);
            html += `
                <li class="py-3 px-4 hover:bg-gray-50 cursor-pointer transition duration-150" data-id="${reminder.id}">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-sm font-medium text-gray-800">${reminder.title}</h6>
                            <p class="text-xs text-gray-500">${reminder.project}${reminder.service !== 'N/A' ? ' (' + reminder.service + ')' : ''}</p>
                            <p class="text-xs text-gray-500">${datesDisplay}</p>
                        </div>
                        <span class="text-xs font-semibold text-indigo-600">${reminder.type}</span>
                    </div>
                </li>
            `;
        });
        html += '</ul>';
        $container.html(html);
    }

    // Load upcoming reminders
    function loadUpcomingReminders() {
        $.ajax({
            url: "{{ route('reminders.upcoming') }}",
            type: "GET",
            dataType: "json",
            success: function(response) {
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
                console.error('Error loading reminders:', xhr.responseText);
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

    // Handle reminder click to open edit modal
    $(document).on('click', '#thisWeekReminders li, #thisMonthReminders li', function() {
        const reminderId = $(this).data('id');
        window.location.href = "{{ url('reminders') }}/" + reminderId + "/edit";
    });

    // Initial load of reminders
    loadUpcomingReminders();
});
</script>
<style>
.card-bordered {
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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
    font-size: 1.5rem;
    font-weight: 600;
    color: #1f2937;
}
</style>
@endpush
@endsection
