<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserShift extends Model
{
    protected $table = 'shift_closings';
     // Add the new fields to the fillable array
     protected $fillable = [
        'user_id',
        'branch_id',
        'start_time',
        'end_time',
        'opening_cash',
        'closing_cash',
        'cash_discrepancy',
        'deshi_sales',
        'beer_sales',
        'english_sales',
        'discount',
        'upi_payment',
        'withdrawal_payment',
        'cash',
        'status',
        'cash_break_id',
        'closing_sales',
        'physical_photo',
    ];


    public function cashDetails()
    {
        return $this->hasMany(CashDetail::class);
    }
    public function cashBreakdown()
    {
        return $this->hasOne(CashBreakdown::class, 'id', 'cash_break_id');
    }
    public function branch()
    {
        return $this->hasOne(Branch::class, 'id', 'branch_id');

    }
    public static function getYesterdayShift($user_id, $branch_id)
    {
        return UserShift::where('status', 'pending')
        ->where('user_id', $user_id)
        ->where('branch_id', $branch_id)
        ->whereDate('start_time', '<', Carbon::today())
        ->latest('id')
        ->first();

    }

}
