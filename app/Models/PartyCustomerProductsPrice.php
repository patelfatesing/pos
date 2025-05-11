<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PartyCustomerProductsPrice extends Model
{
    use HasFactory;

    protected $table = 'party_customer_products_price';

    protected $fillable = [
        'party_user_id',
        'product_id',
        'discount_price',
        'discount_amt',
        'status',
        'created_by',
        'updated_by',
    ];

    // Relationships
    public function partyUser()
    {
        return $this->belongsTo(PartyUser::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
