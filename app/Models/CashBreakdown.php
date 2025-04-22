<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashBreakdown extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'denominations',
        'total',
        'type'

    ];

    protected $casts = [
        'denominations' => 'array', // Cast JSON to PHP array
        'total' => 'decimal:2',
    ];

    // 🔁 Relationships (optional)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function posSession()
    // {
    //     return $this->belongsTo(PosSession::class);
    // }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
    public function userShift()
    {
        return $this->hasOne(UserShift::class);
    }
}
