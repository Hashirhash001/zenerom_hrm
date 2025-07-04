<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerContact extends Model
{
    protected $fillable = [
        'customer_id',
        'contact_name',
        'contact_email',
        'contact_phone'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
