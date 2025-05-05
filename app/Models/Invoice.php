<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Invoice.php
class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'commission_user_id',
        'party_user_id',
        'items',
        'sub_total',
        'tax',
        'commission_amount',
        'party_amount',
        'total',
        'cash_break_id',
        'status',
        'user_id',
        'branch_id',
        'upi_amount',
        'cash_amount',
        'creditpay'
    ];

    protected $casts = [
        'items' => 'array',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function commissionUser()
    {
        return $this->belongsTo(User::class, 'commission_user_id');
    }
    public function partyUser()
    {
        return $this->belongsTo(User::class, 'party_user_id');
    }

    public function cashBreak()
    {
        return $this->belongsTo(CashBreakdown::class, 'cash_break_id');
    }
    public function getTotalAttribute($value)
    {
        return number_format($value, 2);
    }
}
