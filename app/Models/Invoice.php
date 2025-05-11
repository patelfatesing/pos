<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

// app/Models/Invoice.php
class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'commission_user_id',
        'payment_mode',
        'party_user_id',
        'items',
        'sub_total',
        'tax',
        'commission_amount',
        'party_amount',
        'total',
        'cash_break_id',
        'status',
        'user_id',
        'branch_id',
        'upi_amount',
        'cash_amount',
        'creditpay',
        'total_item_qty',
        'total_item_total',
        'change_amount',
        'online_amount',
    ];

    protected $casts = [
        'items' => 'array',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function commissionUser()
    {
        return $this->belongsTo(Commissionuser::class, 'commission_user_id');
    }
    public function partyUser()
    {
        return $this->belongsTo(Partyuser::class, 'party_user_id');
    }

    public function cashBreak()
    {
        return $this->belongsTo(CashBreakdown::class, 'cash_break_id');
    }
    public function getTotalAttribute($value)
    {
        return number_format($value, 2);
    }

    public static function generateInvoiceNumber(): string
    {
        $today = Carbon::now()->format('Ymd'); // e.g., 20250510
        $datePrefix = 'LHUB-' . $today;

        // Count how many invoices already created today
        $countToday = Invoice::whereDate('created_at', Carbon::today())->count() + 1;

        // Pad the number to 4 digits (e.g., 0001, 0002)
        $invoiceNumber = $datePrefix . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

        return $invoiceNumber;
    }
}
