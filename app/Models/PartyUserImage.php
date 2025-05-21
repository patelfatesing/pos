<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartyUserImage extends Model
{
    use HasFactory;
    protected $table = 'party_images';
    // Specify fillable fields
    protected $fillable = [
        'party_user_id',
        'image_path',
        'product_image_path',
        'type',
        'transaction_id'
    ];

    /**
     * Get the Partyuser that owns the image.
     */
    public function commissionUser()
    {
        return $this->belongsTo(Partyuser::class);
    }
}

