<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductPriceChangeHistory extends Model
{
    use HasFactory;

    protected $table = 'product_price_change_history';

    protected $fillable = [
        'product_id',
        'old_price',
        'new_price',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
