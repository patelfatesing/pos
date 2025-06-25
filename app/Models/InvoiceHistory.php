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
            'cash_amount'           => self::sanitizeDecimal($invoice->cash_amount),
            'upi_amount'            => self::sanitizeDecimal($invoice->upi_amount),
            'online_amount'         => self::sanitizeDecimal($invoice->online_amount),
            'creditpay'             => self::sanitizeDecimal($invoice->creditpay),
            'remaining_credit_pay'  => self::sanitizeDecimal($invoice->remaining_credit_pay),
            'paid_credit'           => self::sanitizeDecimal($invoice->paid_credit),
            'payment_mode'          => $invoice->payment_mode ?? null,
            'invoice_number'        => $invoice->invoice_number ?? '',
            'ref_no'                => $invoice->ref_no ?? null,
            'commission_user_id'    => $invoice->commission_user_id ?? null,
            'party_user_id'         => $invoice->party_user_id ?? null,
            'cash_break_id'         => $invoice->cash_break_id ?? null,
            'items'                 => is_array($invoice->items) ? json_encode($invoice->items) : ($invoice->items ?? '[]'),
            'total_item_qty'        => $invoice->total_item_qty ?? 0,
            'total_item_total'      => self::sanitizeDecimal($invoice->total_item_total),
            'sub_total'             => self::sanitizeDecimal($invoice->sub_total),
            'tax'                   => self::sanitizeDecimal($invoice->tax),
            'commission_amount'     => self::sanitizeDecimal($invoice->commission_amount),
            'party_amount'          => self::sanitizeDecimal($invoice->party_amount),
            'total'                 => self::sanitizeDecimal($invoice->total),
            'roundof'               => self::sanitizeDecimal($invoice->roundof),
            'change_amount'         => self::sanitizeDecimal($invoice->change_amount),
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

    /**
     * Remove commas from decimal strings and convert to float.
     */
    protected static function sanitizeDecimal($value): float
    {
        if (is_null($value)) {
            return 0.00;
        }

        return floatval(str_replace(',', '', $value));
    }

}
