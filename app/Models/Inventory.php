<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'product_id',
        'location_id',
        'store_id',
        'quantity',
        'batch_no',
        'expiry_date',
        'cost_price',
        'selling_price',
        'reorder_level'
    ];
    
public function product()
{
    return $this->belongsTo(Product::class);
}

public function location()
{
    return $this->morphTo();
}
}