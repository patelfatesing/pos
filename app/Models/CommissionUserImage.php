<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionUserImage extends Model
{
    use HasFactory;

    // Specify fillable fields
    protected $fillable = [
        'commission_user_id',
        'type',
        'image_path',
        'image_name',
        'product_image_path'
    ];

    /**
     * Get the CommissionUser that owns the image.
     */
    public function commissionUser()
    {
        return $this->belongsTo(CommissionUser::class);
    }
}

