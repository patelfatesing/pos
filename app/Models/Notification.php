<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'content',
        'details',
        'notify_to',
        'status',
        'priority',
        'created_by',
    ];

    // Optional relationships
    public function store()
    {
        return $this->belongsTo(Branch::class, 'notify_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
