<?php

// app/Models/Module.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['name', 'slug', 'is_active'];
    public function submodules()
    {
        return $this->hasMany(Submodule::class);
    }
}
