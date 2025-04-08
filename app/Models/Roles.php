<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    //

    protected $fillable = ['name', 'is_active', 'is_deleted'];

    // public function users()
    // {
    //     return $this->hasMany(User::class);
    // }

    

    // public function users()
    // {
    //     return $this->hasOne(User::class, 'role_id');
    // }
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
