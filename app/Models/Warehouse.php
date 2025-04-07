<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = ['name', 'address', 'description', 'is_active', 'is_deleted'];

    public function inventories()
{
    return $this->morphMany(Inventory::class, 'location');
}

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'to_location_id');
    }

}