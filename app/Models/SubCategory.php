<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'pack_sise',
        'is_active',
        'is_delete',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
