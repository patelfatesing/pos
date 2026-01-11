<?php

// app/Models/Submodule.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\RolePermission;

class Submodule extends Model
{
    protected $fillable = ['module_id', 'name', 'slug', 'is_active','role_id'];
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

     public function permission()
    {
        return $this->hasOne(RolePermission::class, 'submodule_id');
    }
}
    