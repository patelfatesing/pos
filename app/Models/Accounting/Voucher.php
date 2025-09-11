<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    protected $fillable = [
        'voucher_date',
        'voucher_type',
        'ref_no',
        'branch_id',
        'narration',
        'created_by',

        // UI-helper columns
        'party_ledger_id',
        'mode',               // cash|bank|upi|card (nullable)
        'instrument_no',
        'instrument_date',
        'cash_ledger_id',
        'bank_ledger_id',
        'from_ledger_id',
        'to_ledger_id',

        // trade totals
        'sub_total',
        'discount',
        'tax',
        'grand_total',
    ];

    protected $casts = [
        'voucher_date'   => 'date',
        'instrument_date'=> 'date',
        // keep money precise as strings in DB, cast to decimals here
        'sub_total'      => 'decimal:2',
        'discount'       => 'decimal:2',
        'tax'            => 'decimal:2',
        'grand_total'    => 'decimal:2',
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

    // Ledgers (nullable helpers)
    public function partyLedger(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Accounting\AccountLedger::class, 'party_ledger_id');
    }
    public function cashLedger(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Accounting\AccountLedger::class, 'cash_ledger_id');
    }
    public function bankLedger(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Accounting\AccountLedger::class, 'bank_ledger_id');
    }
    public function fromLedger(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Accounting\AccountLedger::class, 'from_ledger_id');
    }
    public function toLedger(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Accounting\AccountLedger::class, 'to_ledger_id');
    }

    /** ---------------- Aggregates ---------------- */

    public function totalDr(): float
    {
        return (float) $this->lines()->where('dc', 'Dr')->sum('amount');
    }

    public function totalCr(): float
    {
        return (float) $this->lines()->where('dc', 'Cr')->sum('amount');
    }

    public function isBalanced(): bool
    {
        return round($this->totalDr(), 2) === round($this->totalCr(), 2);
    }

    /** ---------------- Scopes (optional) ---------------- */

    public function scopeOfType($q, string $type)
    {
        return $q->where('voucher_type', $type);
    }

    public function scopeForBranch($q, $branchId)
    {
        return $q->where('branch_id', $branchId);
    }
}
