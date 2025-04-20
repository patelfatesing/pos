<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccessIp extends Model
{
    use HasFactory;

    protected $table = 'access_ip_table';

    protected $fillable = [
        'ip',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

