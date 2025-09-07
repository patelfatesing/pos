<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    protected $fillable = [
        'voucher_date',
        'voucher_type', // Journal, Payment, Receipt, Sales, Purchase...
        'ref_no',
        'branch_id',
        'narration',
        'created_by',
    ];

    protected $casts = [
        'voucher_date' => 'date',
    ];

    /** ---------------- Relations ---------------- */

    public function lines(): HasMany
    {
        return $this->hasMany(VoucherLine::class, 'voucher_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /** ---------------- Helpers ---------------- */

    public function totalDr(): float
    {
        return $this->lines()->where('dc', 'Dr')->sum('amount');
    }

    public function totalCr(): float
    {
        return $this->lines()->where('dc', 'Cr')->sum('amount');
    }

    public function isBalanced(): bool
    {
        return round($this->totalDr(), 2) === round($this->totalCr(), 2);
    }
}
