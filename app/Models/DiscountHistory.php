<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountHistory extends Model
{
    //
    protected $fillable = [
        'user_id',
        'discount_amount',
        
    ];
}
