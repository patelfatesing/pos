<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'bill_no',
        'vendor_id',
        'vendor_new_id',
        'parchase_ledger',
        'total',
        'date',
        'excise_fee',
        'composition_vat',
        'surcharge_on_ca',
        'tcs',
        'aed_to_be_paid',
        'total_amount',
        'status',
        'created_by',
        'updated_by',
        'vat',
        'surcharge_on_vat',
        'blf',
        'permit_fee',
        'rsgsm_purchase',
        'case_purchase',
        'case_purchase_per',
        'case_purchase_amt',
        'permit_fee_excise',
        'vend_fee_excise',
        'composite_fee_excise',
        'excise_total_amount',
        'loading_charges'

    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(VendorList::class);
    }

    public function productsItems(): HasMany
    {
        return $this->hasMany(PurchaseProduct::class);
    }

    public function purchaseProducts(): HasMany
    {
        return $this->hasMany(PurchaseProduct::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function PurchaseLedger(): BelongsTo
    {
        return $this->belongsTo(PurchaseLedger::class);
    }
}
