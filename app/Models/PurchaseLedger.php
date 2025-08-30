<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseLedger extends Model
{
    protected $fillable = [
        'name',
        'vendor_id',
        'is_active',
    ];

    public function vendor()
    {
        // assumes you have App\Models\VendorList and table vendor_lists
        return $this->belongsTo(VendorList::class, 'vendor_id');
    }

    // Small helper accessors if you like:
    public function scopeActive($query)
    {
        return $query->where('is_active', 'Yes');
    }
}
