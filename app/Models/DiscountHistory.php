<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountHistory extends Model
{
    //
    protected $fillable = [
        'invoice_id',
        'discount_amount',
        'total_amount',
        'total_purchase_items',
        'commission_user_id',
        'store_id',
        'created_by'
    ];
}
