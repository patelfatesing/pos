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
        '<expiry_dat></expiry_dat>e',
        'is_active',
        'is_deleted',
        'created_by',
        'added_by'
    ];
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->morphTo();
    }

    protected $casts = [
        'expiry_date' => 'datetime', // Automatically cast to Carbon instance
    ];
}