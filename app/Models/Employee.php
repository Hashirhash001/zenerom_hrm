<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'employee_id', 
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'company_email',
        'phone',
        'emergency_contact',
        'emergency_contact_name',
        'age',
        'gender',
        'dob',
        'permanent_address',
        'local_address',
        'blood_group',
        'whatsapp',
        'image',
        'cv_file',
        'status',
        'department_id',
        'resignation',
        'resignation_details',
        'role_id'
    ];
   public function department()
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id');
    }

    public function role()
    {
        return $this->belongsTo(\App\Models\Role::class, 'role_id');
    }

    public function user()
    {
        return $this->hasOne(\App\Models\User::class, 'user_id');
    }
    public function accessPrivileges()
    {
        return $this->hasMany(\App\Models\UserAccessPrivilege::class, 'user_id');
    }



}

