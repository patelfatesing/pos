<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'type',
        'action',
        'message',
        'old_data',
        'new_data',
        'ip',
        'user_agent',
        'user_id'
    ];
}
