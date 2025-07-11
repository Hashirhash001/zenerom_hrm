<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
        'reason',
        'team_lead_status',
        'team_lead_approved_by',
        'team_lead_approved_at',
        'team_lead_comments',
        'hr_status',
        'hr_approved_by',
        'hr_approved_at',
        'hr_comments',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'team_lead_approved_at' => 'datetime',
        'hr_approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function teamLeadApprover()
    {
        return $this->belongsTo(User::class, 'team_lead_approved_by');
    }

    public function hrApprover()
    {
        return $this->belongsTo(User::class, 'hr_approved_by');
    }

    public function getDurationAttribute()
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function getStatusAttribute()
    {
        if ($this->team_lead_status === 'Approved' && $this->hr_status === 'Approved') {
            return 'Approved';
        } elseif ($this->team_lead_status === 'Rejected' || $this->hr_status === 'Rejected') {
            return 'Rejected';
        } elseif ($this->team_lead_status === 'Submitted' || $this->hr_status === 'Submitted') {
            return 'Submitted';
        } else {
            return 'Draft';
        }
    }
}
