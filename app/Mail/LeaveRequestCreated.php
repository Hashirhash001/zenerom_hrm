<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeaveRequestCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $leaveRequest;
    public $employeeName;

    /**
     * Create a new message instance.
     *
     * @param LeaveRequest $leaveRequest
     * @param string $employeeName
     * @return void
     */
    public function __construct(LeaveRequest $leaveRequest, string $employeeName)
    {
        $this->leaveRequest = $leaveRequest;
        $this->employeeName = $employeeName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $leaveTypes = [
            'full_day' => 'Full Day',
            'half_day_first' => 'Half Day (First Half)',
            'half_day_second' => 'Half Day (Second Half)',
            'Sick' => 'Sick Leave',
            'Maternity' => 'Maternity Leave',
            'Unpaid' => 'Unpaid Leave',
            'Paid' => 'Paid Leave',
        ];

        $leaveType = $leaveTypes[$this->leaveRequest->leave_type] ?? ucfirst(str_replace('_', ' ', $this->leaveRequest->leave_type));

        return $this->subject('New Leave Request Submitted')
                    ->html(view('emails.leave_request_created')->with([
                        'employeeName' => $this->employeeName,
                        'leaveType' => $leaveType,
                        'startDate' => $this->leaveRequest->start_date->format('d M Y'),
                        'endDate' => $this->leaveRequest->end_date->format('d M Y'),
                        'duration' => $this->leaveRequest->duration ? $this->leaveRequest->duration . ' days' : 'N/A',
                        'reason' => $this->leaveRequest->reason ?? 'N/A',
                    ])->render());
    }
}
