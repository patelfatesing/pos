<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditHistory extends Model
{
    //
    protected $fillable = [
        'invoice_id',
        'debit_amount',
        'credit_amount',
        'total_amount',
        'total_purchase_items',
        'party_user_id',
        'store_id',
        'created_by'
    ];
}
