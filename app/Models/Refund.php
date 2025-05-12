<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    //
    protected $fillable = ['amount', 'description', 'invoice_id', 'store_id', 'user_id','items_refund','total_item_qty','total_item_price','total_mrp','party_amount','refund_credit_amount'];

}
