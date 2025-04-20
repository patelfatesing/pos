<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserShift extends Model
{
    protected $table = 'shift_closings';
    protected $fillable = [
        'shop_name', 'start_time', 'end_time', 'opening_cash',
        'deshi_sales', 'beer_sales', 'english_sales', 'discount',
        'upi_payment', 'withdrawal_payment', 'today_cash', 'closing_cash','user_id','branch_id','closing_cash','cash_break_id'
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
    

}
