<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceActivityLog extends Model
{
    protected $fillable = [
        'invoice_id',
        'action',
        'description',
        'old_data',
        'new_data',
        'user_id'
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
