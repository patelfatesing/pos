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
    ];

    protected $casts = [
        'items' => 'array',
    ];
}
