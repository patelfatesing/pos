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
    //
    public function show(Invoice $invoice)
    {
        $commissionUser = Commissionuser::find($invoice->commission_user_id);
        $partyUser = Partyuser::find($invoice->party_user_id);
        return view('invoice.view', compact('invoice', 'commissionUser', 'partyUser'));
    }
    public function download(Invoice $invoice)
    {
        $commissionUser = Commissionuser::find($invoice->commission_user_id);
        $partyUser = Partyuser::find($invoice->party_user_id);
        
        $pdf = PDF::loadView('invoice', [
            'invoice_number' => $invoice->invoice_number,
            'cartitems' => collect($invoice->items),
            'sub_total' => $invoice->sub_total,
            'tax' => $invoice->tax,
            'commissionAmount' => $invoice->commission_amount,
            'partyAmount' => $invoice->party_amount,
            'total' => $invoice->total,
            'commissionUser' => $commissionUser,
            'partyUser' => $partyUser,
        ]);
        return $pdf->download($invoice->invoice_number . '.pdf');
    }

}
