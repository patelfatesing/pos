<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shoppingcart extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function product(){
        return $this->belongsTo(Product::class);
    }
    public const STATUS_PENDING = 'pending';
    public const STATUS_HOLD = 'Hold';
    public const STATUS_COMPLETED = 'completed';
    
    public static function GetCartItems()
    {
        return  Shoppingcart::with('product')
        ->where(['user_id'=>auth()->user()->id])
        ->where('status', Shoppingcart::STATUS_PENDING)
        ->paginate(7);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
