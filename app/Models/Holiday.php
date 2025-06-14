<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'title',
        'date',
        'branch_id',
        'description',
    ];

    // Optional: Relationship if you have a branches table
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}

