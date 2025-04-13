<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/CashInHand.php
class CashInHand extends Model
{
    protected $fillable = ['user_id', 'amount', 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

