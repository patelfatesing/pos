<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory;
    // Explicitly specify the table name
    protected $table = 'branches';

    protected $fillable = ['name', 'address', 'description', 'is_active', 'is_deleted', 'is_warehouser','in_out_enable'];

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

    public function shiftClosings()
    {
        return $this->hasMany(ShiftClosing::class, 'branch_id');
    }

    public function sourceApprovals()
    {
        return $this->hasMany(StockRequestApprove::class, 'source_store_id');
    }

    public function destinationApprovals()
    {
        return $this->hasMany(StockRequestApprove::class, 'destination_store_id');
    }
}
