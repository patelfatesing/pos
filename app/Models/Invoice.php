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
        'roundof',
        'ref_no',
        'hold_date',
        'paid_credit',
        'invoice_status'
    ];

    protected $casts = [
        'items' => 'array',
        'total' => 'float',
        'roundof' => 'float',
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
    public static function generateInvoiceNumber($type = ""): string
    {
        $today = Carbon::now()->format('ymd'); // e.g., 20250516
        $prefix = "";
        if (!empty($type)) {
            $prefix .= $type . "-";
        }
        $branchName = auth()->user()->userinfo->branch->name ?? '';
        $branchPrefix = strtoupper(substr(preg_replace('/\s+/', '', $branchName), 0, 2)); // First 2 letters, uppercase, no spaces

        $datePrefix = $prefix . $branchPrefix.'-' . $today;
        // Find the latest invoice number for today
        $latestInvoice = Invoice::where('invoice_number', 'like', $datePrefix . '-%');

        if (empty($type)) {
            $latestInvoice = $latestInvoice->where('status', '!=', 'hold');
        }

        $latestInvoice=$latestInvoice->orderByRaw("CAST(SUBSTRING_INDEX(invoice_number, '-', -1) AS UNSIGNED) DESC")

        ->first();
        

        if ($latestInvoice) {
            // Extract the sequence number (e.g., from LH-20250516-0003 get 0003)
            $parts = explode('-', $latestInvoice->invoice_number);
            if (empty($type)) {
                $lastNumber = isset($parts[2]) ? (int)$parts[2] : 0;
            } else {
                $lastNumber = isset($parts[3]) ? (int)$parts[3] : 0;
            }
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Pad the number to 4 digits (e.g., 0001)
        $invoiceNumber = $datePrefix . '-' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);

        return $invoiceNumber;
        // Example prefix
        // $prefix = 'LQH-';
        // if(!empty($type)){
        //     $prefix.=$type."-";
        // }

        // // Get last invoice number from DB, e.g., 'LHINV-0045'
        // $lastInvoice = Invoice::where('status', '!=', 'hold')->latest('id')->value('invoice_number'); 
        // if ($lastInvoice) {
        //     $number = (int) str_replace($prefix, '', $lastInvoice);
        //     $newNumber = $prefix . str_pad($number + 1, 4, '0', STR_PAD_LEFT);
        // } else {
        //     $newNumber = $prefix . '0001';
        // }
        // return $newNumber; 
    }
}
