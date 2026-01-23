<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $fillable = [
        'brand',
        'name',
        'size',
        'sku',
        'category_id',
        'subcategory_id',
        'image',
        'description',
        'barcode',
        'abv',
        'abv',
        'discount_price',
        'case_size',
        'secondary_unitx',
        'vendor_id ',
        'mfg_date',
        'cost_price',
        'sell_price',
        'reorder_level',
        'is_deleted',
        'mrp',
        'created_by',
        'updated_by',
    ];

    public static function generateSku($brand, $name, $size, $p_id)
    {
        $brandCode = strtoupper(Str::slug(Str::words($brand, 1, ''), ''));
        $nameCode = strtoupper(Str::slug(Str::words($name, 1, ''), ''));
        $sizeCode = preg_replace('/[^0-9]/', '', $size); // extract ml only

        return "{$brandCode}-{$nameCode}-{$sizeCode}-{$p_id}";
    }

    public function inventories()
    {
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";

        return $this->hasMany(Inventory::class)->where('store_id', $branch_id);
    }
    
    public function inventorie()
    {
        $branch_id = auth()->user()->userinfo->branch->id ?? null;

        return $this->hasOne(Inventory::class, 'product_id')
            ->where('store_id', $branch_id);
    }

    public function inventorieUnfiltered()
    {
        return $this->hasOne(Inventory::class, 'product_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function customerPrices()
    {
        return $this->hasMany(PartyCustomerProductsPrice::class);
    }
}
