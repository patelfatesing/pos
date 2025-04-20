<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashDetail extends Model
{
    protected $fillable = ['shift_closing_id', 'denomination', 'quantity', 'total'];

    public function UserShift()
    {
        return $this->belongsTo(UserShift::class);
    }
}
