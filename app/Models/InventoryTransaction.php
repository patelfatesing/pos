<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'product_id',
        'inventory_id',
        'from_location_id',
        'from_location_type',
        'to_location_id',
        'to_location_type',
        'quantity',
        'type'
    ];
}
