<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskDocument extends Model
{
    protected $table = 'task_documents';

    protected $fillable = [
        'task_id',
        'task_assigned_id',
        'document_name',
        'file_path',
        'description',
        'user_id'
    ];
}
