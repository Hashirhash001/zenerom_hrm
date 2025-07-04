<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccessPrivilege extends Model
{
    protected $fillable = [
        'user_id',       // Add this field
        'menu_item_id',
        'can_view',
        'can_edit',
        'can_delete',
        'can_create',
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }
}
