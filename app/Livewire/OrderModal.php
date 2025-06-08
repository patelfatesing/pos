<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Invoice;
use Carbon\Carbon;
use App\Models\UserShift;
use App\Models\Refund;
use Illuminate\Support\Facades\App;
use App\Models\Partyuser;

class OrderModal extends Component
{
    public $orders = [];
    public $refunds = [];
    public $showModal = false;

    public function openModal()
    {
        $today = Carbon::today();
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";


        $currentShift = UserShift::whereDate('start_time', $today)->where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->where(['status' =>"pending"])->first();

        $start_date = @$currentShift->start_time; // your start date (set manually)
        $end_date = date('Y-m-d') . ' 23:59:59'; // today's date till end of day
        $this->orders = Invoice::where('user_id', auth()->user()->id)
            ->where('branch_id', $branch_id)
            ->whereIn('status', ['Refunded', 'Paid']) // <-- added condition
            ->whereBetween('created_at', [$start_date, $end_date])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        $this->refunds = Refund::with('invoice')
        ->whereBetween('created_at', [$start_date, $end_date])
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();

        $this->showModal = true;

        // Dispatch browser event to show modal
        $this->dispatch('show-order-modal');
    }
    public function printInvoice($id)
    {
       $invoice = \App\Models\Invoice::where("id",$id)->latest('id')->first();
        
       $sunTot=(Int) $invoice->total+(Int)$invoice->party_amount;

        if (!$invoice) {
            // Handle case where no invoice exists
            return;
        }

        $pdfPath = storage_path('app/public/invoices/duplicate_' . $invoice->invoice_number . '.pdf');

        if (!file_exists($pdfPath)) {
            $partyUser = PartyUser::where('status', 'Active')->find($invoice->party_user_id);
            $pdf = App::make('dompdf.wrapper');
            $pdf->loadView('invoice', ['invoice' => $invoice, 'items' => $invoice->items, 'branch' => auth()->user()->userinfo->branch, 'duplicate' => true,'customer_name' => $partyUser->first_name]);
            $pdf->save($pdfPath);
        }
        
        $this->dispatch('triggerPrint', [
            'pdfPath' => asset('storage/invoices/duplicate_' . $invoice->invoice_number . '.pdf')
        ]);
    }
      public function printRefundInvoice($pdfPath)
        {
            $this->dispatch('triggerPrint', ['pdfPath' => $pdfPath]);
        }

    public function render()
    {
        return view('livewire.order-modal');
    }
}

