<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'purchase_date',
        'purchase_order_no',
        'shipping_date',
        'notes',
        'status',
        'file_name',
    ];

    // 🔥 Relationship: A DemandOrder has many DemandOrderProducts
    public function products()
    {
        return $this->hasMany(DemandOrderProduct::class);
    }

    // 🔥 Relationship: Vendor
    public function vendor()
    {
        return $this->belongsTo(VendorList::class, 'vendor_id');
    }
}
