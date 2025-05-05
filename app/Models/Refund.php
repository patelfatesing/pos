<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    //
    protected $fillable = ['amount', 'description', 'invoice_id', 'store_id', 'user_id'];

}
