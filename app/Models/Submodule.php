<?php

// app/Models/Submodule.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submodule extends Model
{
    protected $fillable = ['module_id', 'name', 'slug', 'is_active'];
    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
