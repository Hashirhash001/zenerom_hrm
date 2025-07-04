<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectService extends Model
{
    // Either define fillable (and remove guarded)...
    protected $fillable = [
        'project_id',
        'service_id',
        'assigned_to_staff',
        'status',
        'notes'
    ];

    // Remove or comment out the guarded property
    // protected $guarded = ['*'];

    // Relationships...
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function milestones()
    {
        return $this->hasMany(\App\Models\ProjectMilestone::class, 'project_service_id');
    }


    public function assignedStaff()
    {
        return $this->belongsTo(Employee::class, 'assigned_to_staff');
    }
    // app/Models/ProjectService.php

   public function assignedStaffs()
    {
        return $this->belongsToMany(\App\Models\Employee::class, 'project_users', 'project_service_id', 'user_id')
                    ->withPivot('status', 'date_time')
                    ->withTimestamps();
    }
   public function descriptions()
    {
        return $this->hasMany(\App\Models\ProjectDescription::class, 'project_service_id');
    }




}
