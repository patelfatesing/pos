<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockRequest extends Model
{
    protected $fillable = [
        'store_id',
        'requested_by',
        'approved_by',
        'status',
        'notes',
        'total_product',
        'total_quantity',
        'requested_at',
        'approved_at',
        'created_by',
        'total_request_quantity',
        'rejected_at',
        'reject_reason'
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'requested_by');
    }

    public function tobranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'store_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockRequestItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvals()
    {
        return $this->hasMany(StockRequestApprove::class, 'stock_request_id');
    }
}
