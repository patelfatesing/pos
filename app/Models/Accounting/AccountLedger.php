<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountLedger extends Model
{
    protected $fillable = [
        'name',
        'group_id',
        'branch_id',
        'opening_balance',
        'opening_type', // Dr or Cr
        'is_active',
        'is_deleted',
        'updated_by',
        'contact_details'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    /** ---------------- Relations ---------------- */

    public function group(): BelongsTo
    {
        return $this->belongsTo(AccountGroup::class, 'group_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(VoucherLine::class, 'ledger_id');
    }

    /** ---------------- Helpers ---------------- */

    public function openingValue(): float
    {
        return $this->opening_type === 'Dr'
            ? $this->opening_balance
            : -1 * $this->opening_balance;
    }

    /**
     * Get balance between two dates
     */
    public function balanceBetween($from = null, $to = null): array
    {
        $qb = VoucherLine::query()
            ->where('ledger_id', $this->id)
            ->whereHas('voucher', function ($q) use ($from, $to) {
                if ($from) {
                    $q->where('voucher_date', '>=', $from);
                }
                if ($to) {
                    $q->where('voucher_date', '<=', $to);
                }
            });

        $dr = (clone $qb)->where('dc', 'Dr')->sum('amount');
        $cr = (clone $qb)->where('dc', 'Cr')->sum('amount');

        $net = $this->openingValue() + $dr - $cr;

        return [
            'sign'   => $net >= 0 ? 'Dr' : 'Cr',
            'amount' => abs($net),
        ];
    }
}
