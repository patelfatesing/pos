<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseProduct extends Model
{
    protected $fillable = [
        'purchase_id',
        'sr_no',
        'brand_name',
        'batch',
        'mfg_date',
        'mrp',
        'qnt',
        'rate',
        'amount',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }
}
