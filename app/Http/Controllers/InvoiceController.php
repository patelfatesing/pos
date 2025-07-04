<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Commissionuser;
use App\Models\Partyuser;

class InvoiceController extends Controller
{

    public function show(Invoice $invoice)
    {
        $commissionUser = Commissionuser::where('id', $invoice->commission_user_id)
            ->where('status', 'Active')
            ->first();
        $partyUser = Partyuser::where('id', $invoice->party_user_id)
            ->where('status', 'Active')
            ->first();

        return view('invoice.view', compact('invoice', 'commissionUser', 'partyUser'));
    }

    public function download(Invoice $invoice)
    {
        $commissionUser = Commissionuser::where('id', $invoice->commission_user_id)
            ->where('status', 'Active')
            ->first();

        $partyUser = Partyuser::where('id', $invoice->party_user_id)
            ->where('status', 'Active')
            ->first();

        $customer_name = 'N/A';

        if ($partyUser && !empty(trim($partyUser->first_name))) {
            $customer_name = $partyUser->first_name;
        } elseif ($commissionUser && !empty(trim($commissionUser->first_name))) {
            $customer_name = $commissionUser->first_name;
        }

        $pdf = PDF::loadView('invoice', [
            'invoice' => $invoice,
            'invoice_number' => $invoice->invoice_number,
            'cartitems' => collect($invoice->items),
            'items' => collect($invoice->items),
            'sub_total' => $invoice->sub_total,
            'tax' => $invoice->tax,
            'commissionAmount' => $invoice->commission_amount,
            'partyAmount' => $invoice->party_amount,
            'total' => $invoice->total,
            'commissionUser' => $commissionUser,
            'partyUser' => $partyUser,
            'customer_name' => $customer_name,
            'created_at' => $invoice->created_at,
        ]);
        return $pdf->download($invoice->invoice_number . '.pdf');
    }

    public function viewInvoice(Invoice $invoice,$shift_id = '')
    {
        $commissionUser = Commissionuser::where('status', 'Active')->find($invoice->commission_user_id);

        $partyUser = Partyuser::where('id', $invoice->party_user_id)
            ->where('status', 'Active')
            ->first();
        return view('invoice.viewInvoice', compact('invoice', 'commissionUser', 'partyUser', 'shift_id'));
    }

    public function viewHoldInvoice(Invoice $invoice,$shift_id)
    {
        $commissionUser = Commissionuser::where('status', 'Active')->find($invoice->commission_user_id);

        $partyUser = Partyuser::where('id', $invoice->party_user_id)
            ->where('status', 'Active')
            ->first();
        return view('invoice.viewHoldInvoice', compact('invoice', 'commissionUser', 'partyUser','shift_id'));
    }
}
