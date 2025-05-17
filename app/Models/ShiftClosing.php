<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShiftClosing extends Model
{
    use HasFactory;

    protected $table = 'shift_closings';

    protected $fillable = [
        'branch_id',
        'user_id',
        'start_time',
        'end_time',
        'opening_cash',
        'created_at',
        'updated_at',
        'closing_shift_time'
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
