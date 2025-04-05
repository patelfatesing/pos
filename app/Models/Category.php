<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'is_active', 'is_delete'];

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
