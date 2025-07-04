<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['project_id', 'invoice_number', 'amount', 'status', 'issued_date', 'due_date'];

    protected $dates = ['issued_date', 'due_date'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
