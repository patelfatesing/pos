<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commissionuser extends Model
{
    use HasFactory;
    protected $table = 'commission_users';

    // Specify fillable fields; note that user_id is removed.
    protected $fillable = [
        'commission_type',
        'commission_value',
        'applies_to',
        'reference_id',
        'is_active',
        'start_date',
        'end_date',
        'first_name',
        'middle_name',
        'last_name',
    ];

    /**
     * Get the images for the commission user.
     */
    public function images()
    {
        return $this->hasMany(CommissionUserImage::class, 'commission_user_id');
    }
    
}
