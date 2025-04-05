<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $fillable = [
        'product_type', 'name', 'code', 'barcode_symbology', 'category', 
        'cost', 'price', 'tax_method', 'quantity', 'image', 'description'
    ];
    
}
