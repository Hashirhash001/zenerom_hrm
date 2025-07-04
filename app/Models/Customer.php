<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'contact_info'
    ];

    // A Customer can have many contacts.
    public function contacts()
    {
        return $this->hasMany(\App\Models\CustomerContact::class);
    }

}
