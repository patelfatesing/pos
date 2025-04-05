<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'product_type', 'name', 'code', 'barcode_symbology', 'category', 
        'cost', 'price', 'tax_method', 'quantity', 'image', 'description'
    ];
    
}
