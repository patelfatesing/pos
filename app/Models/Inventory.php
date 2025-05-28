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
        'is_active',
        'is_deleted',
        'created_by',
        'added_by',
        'low_level_qty'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->morphTo();
    }

    public static function countQty($productId, $storeId)
    {
        return self::where('product_id', $productId)
            ->where('store_id', $storeId)
            ->sum('quantity');
    }

    protected $casts = [
        'expiry_date' => 'datetime', // Automatically cast to Carbon instance
    ];
}
