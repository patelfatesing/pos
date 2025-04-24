<?php

// app/Models/WithdrawCash.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WithdrawCash extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'cash_break_id',
        'amount',
        'note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashBreak()
    {
        return $this->belongsTo(CashBreakdown::class);
    }
}
