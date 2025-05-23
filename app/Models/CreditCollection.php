<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'party_user_id',
        'amount',
        'collected_by',
        'cash_break_id',
        'note_data',
        'payment_method',
        'upi_amount',
        'online_amount',
        'cash_amount'
    ];

    protected $casts = [
        'note_data' => 'array',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function partyUser()
    {
        return $this->belongsTo(PartyUser::class);
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    // public function cashBreak()
    // {
    //     return $this->belongsTo(CashBreak::class);
    // }
}
