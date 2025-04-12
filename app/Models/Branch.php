<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory;
     // Explicitly specify the table name
     protected $table = 'branches';

    protected $fillable = ['name', 'address', 'description', 'is_active', 'is_deleted','is_warehouser'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

//     public function inventories()
// {
//     return $this->morphMany(Inventory::class, 'location');
// }

public function inventories()
{
    return $this->hasMany(Inventory::class, 'store_id'); // or StockEntry
}

}
