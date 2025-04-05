<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'size',
        'sub_category_id',
        'is_active',
        'is_deleted',
    ];

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
}
