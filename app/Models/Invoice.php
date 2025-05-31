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
     public function refunds()
    {
        return $this->hasMany(Refund::class);
    }
  public static function generateInvoiceNumber($type=""): string
    {
        // $today = Carbon::now()->format('Ymd'); // e.g., 20250516
        // $datePrefix = 'LHUB' . $today;

        // // Find the latest invoice number for today
        // $latestInvoice = Invoice::where('invoice_number', 'like', $datePrefix . '%')
        //     ->orderBy('invoice_number', 'desc')
        //     ->first();

        // if ($latestInvoice) {
        //     // Extract the number part (e.g., from LHUB-20250516-0003 get 0003)
        //     $lastNumber = (int)substr($latestInvoice->invoice_number, -4);
        //     $nextNumber = $lastNumber + 1;
        // } else {
        //     $nextNumber = 1;
        // }

        // // Pad the number to 4 digits (e.g., 0001)
        // $invoiceNumber = $datePrefix . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // return $invoiceNumber;
        // Example prefix
        $prefix = 'LQH-';
        if(!empty($type)){
            $prefix.=$type."-";
        }

        // Get last invoice number from DB, e.g., 'LHINV-0045'
        $lastInvoice = Invoice::latest('id')->value('invoice_number'); 

        if ($lastInvoice) {
            $number = (int) str_replace($prefix, '', $lastInvoice);
            $newNumber = $prefix . str_pad($number + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = $prefix . '0001';
        }
        return $newNumber; 
    }

}
