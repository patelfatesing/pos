<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    //
    protected $fillable = ['invoice_number','amount', 'description', 'invoice_id', 'store_id', 'user_id','items_refund','total_item_qty','total_item_price','total_mrp','party_amount','refund_credit_amount','type'];

     /**
     * Get the invoice that owns the refund.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
