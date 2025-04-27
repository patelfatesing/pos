<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandOrderProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'demand_order_id',
        'product_id',
        'quantity',
        'barcode',
        'mrp',
        'rate',
        'sell_price',
        'delivery_status',
        'delivery_quantity',
    ];

    // 🔥 Relationship: belongs to DemandOrder
    public function demandOrder()
    {
        return $this->belongsTo(DemandOrder::class);
    }

    // 🔥 Relationship: belongs to Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
