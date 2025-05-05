<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'invoice_id', 'cash_amount', 'upi_amount', 'creditpay', 'payment_mode',
        'invoice_number', 'commission_user_id', 'party_user_id', 'cash_break_id',
        'items', 'sub_total', 'tax', 'commission_amount', 'party_amount',
        'total', 'status', 'user_id', 'branch_id', 'change_type', 'updated_by', 'history_created_at',
    ];

    public static function logFromInvoice(\App\Models\Invoice $invoice, string $changeType, $updatedBy = null)
    {
        self::create([
            'invoice_id'         => $invoice->id,
            'cash_amount'        => $invoice->cash_amount,
            'upi_amount'         => $invoice->upi_amount,
            'creditpay'          => $invoice->creditpay,
            'payment_mode'       => $invoice->payment_mode,
            'invoice_number'     => $invoice->invoice_number,
            'commission_user_id' => $invoice->commission_user_id,
            'party_user_id'      => $invoice->party_user_id,
            'cash_break_id'      => $invoice->cash_break_id,
            'items' => is_array($invoice->items) ? json_encode($invoice->items) : $invoice->items,
            'sub_total'          => $invoice->sub_total,
            'tax'                => $invoice->tax,
            'commission_amount'  => $invoice->commission_amount,
            'party_amount'       => $invoice->party_amount,
            'total'              =>(Int) $invoice->total,
            'status'             => $invoice->status,
            'user_id'            => $invoice->user_id,
            'branch_id'          => $invoice->branch_id,
            'change_type'        => $changeType,
            'updated_by'         => $updatedBy,
            'history_created_at' => now(),
        ]);
    }
}
