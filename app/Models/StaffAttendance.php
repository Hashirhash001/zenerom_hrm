<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    protected $table = 'staff_attendance';

    protected $fillable = [
        'user_id', 'attendance_date', 'mode', 'system_ip', 'approval_status', 'approved_by'
    ];

    protected $dates = ['attendance_date', 'approved_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
