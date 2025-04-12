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
        'reorder_level',
        'is_active',
        'is_deleted',
        'created_by',
        'discount_price',
        'case_size',
        'secondary_unitx',
        'vendor_id ',
        'mfg_date'
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