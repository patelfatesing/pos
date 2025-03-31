<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory;
     // Explicitly specify the table name
     protected $table = 'branch';

    protected $fillable = ['name', 'address', 'description', 'is_active', 'is_deleted'];
}
