<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyProductStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_id',
        'date',
        'opening_stock',
        'added_stock',
        'transferred_stock',
        'sold_stock',
        'closing_stock',
        'shift_id'
    ];

    protected $dates = ['date'];

    // Relations
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
