<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * Get the roles for the permission.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }
}
