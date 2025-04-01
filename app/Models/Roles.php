<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    //

    protected $fillable = ['role_name', 'is_active', 'is_deleted'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
