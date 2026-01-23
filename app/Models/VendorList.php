<?php

// app/Models/VendorList.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorList extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'gst_number',
        'address',
        'is_active',
        'created_by',
        'updated_by'
    ];
}
