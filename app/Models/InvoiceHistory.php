<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceHistory extends Model
{
    protected $table = 'invoice_logs';
    public $timestamps = false;

    protected $fillable = [
        'invoice_id',
        'cash_amount',
        'upi_amount',
        'online_amount',
        'creditpay',
        'remaining_credit_pay',
        'paid_credit',
        'payment_mode',
        'invoice_number',
        'ref_no',
        'commission_user_id',
        'party_user_id',
        'cash_break_id',
        'items',
        'total_item_qty',
        'total_item_total',
        'sub_total',
        'tax',
        'commission_amount',
        'party_amount',
        'total',
        'roundof',
        'change_amount',
        'status',
        'invoice_status',
        'hold_date',
        'user_id',
        'branch_id',
        'change_type',
        'updated_by',
        'history_created_at',
    ];

    public static function logFromInvoice(\App\Models\Invoice $invoice, string $changeType, $updatedBy = null): void
    {
        self::create([
            'invoice_id'            => $invoice->id,
            'cash_amount'           => $invoice->cash_amount ?? 0.00,
            'upi_amount'            => $invoice->upi_amount ?? 0.00,
            'online_amount'         => $invoice->online_amount ?? 0.00,
            'creditpay'             => $invoice->creditpay ?? 0.00,
            'remaining_credit_pay'  => $invoice->remaining_credit_pay ?? 0.00,
            'paid_credit'           => $invoice->paid_credit ?? 0.00,
            'payment_mode'          => $invoice->payment_mode ?? null,
            'invoice_number'        => $invoice->invoice_number ?? '',
            'ref_no'                => $invoice->ref_no ?? null,
            'commission_user_id'    => $invoice->commission_user_id ?? null,
            'party_user_id'         => $invoice->party_user_id ?? null,
            'cash_break_id'         => $invoice->cash_break_id ?? null,
            'items'                 => is_array($invoice->items) ? json_encode($invoice->items) : ($invoice->items ?? '[]'),
            'total_item_qty'        => $invoice->total_item_qty ?? 0,
            'total_item_total'      => $invoice->total_item_total ?? 0.00,
            'sub_total'             => $invoice->sub_total ?? 0.00,
            'tax'                   => $invoice->tax ?? 0.00,
            'commission_amount'     => $invoice->commission_amount ?? 0.00,
            'party_amount'          => $invoice->party_amount ?? 0.00,
            'total'                 => $invoice->total ?? 0.00,
            'roundof'               => $invoice->roundof ?? 0.00,
            'change_amount'         => $invoice->change_amount ?? 0.00,
            'status'                => $invoice->status ?? 'pending',
            'invoice_status'        => $invoice->invoice_status ?? 'unpaid',
            'hold_date'             => $invoice->hold_date ?? null,
            'user_id'               => $invoice->user_id ?? null,
            'branch_id'             => $invoice->branch_id ?? null,
            'change_type'           => $changeType,
            'updated_by'            => $updatedBy,
            'history_created_at'    => now(),
        ]);

    }
}
