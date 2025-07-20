<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Shoppingcart as Cart;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\UserShift;
use Carbon\Carbon;
use App\Models\Partyuser;
use App\Models\Commissionuser;
use App\Models\PartyUserImage;
use App\Models\CommissionUserImage;
use Illuminate\Support\Facades\App;

class HoldTransactions extends Component
{
    public $holdTransactions = [];

    protected $listeners = ['loadHoldTransactions'];
    private const SESSION_KEY_PRODUCT = 'product_photo_path';
    private const SESSION_KEY_CUSTOMER = 'customer_photo_path';
    private const SESSION_KEY_TIMESTAMP = 'photos_timestamp';

    public function loadHoldTransactions()
    {
        $today = Carbon::today();

        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        $currentShift = UserShift::whereDate('start_time', $today)->where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->where(['status' => "pending"])->first();
        $start_date = @$currentShift->start_time; // your start date (set manually)
        $end_date = $currentShift->end_date ?? date('Y-m-d H:i:s'); // your start date (set manually)

        $this->holdTransactions = Invoice::with(['partyUser', 'commissionUser'])
            ->where(['user_id' => auth()->user()->id])
            ->where(['branch_id' => $branch_id])
            ->where('status', 'Hold')
            ->whereBetween('created_at', [$start_date, $end_date])
            ->get();
        // $this->holdTransactions = Cart::where('user_id', auth()->user()->id)->where('status', Cart::STATUS_HOLD)->get();

    }

    public function resumeTransaction($id, $commission_user_id = "", $party_user_id = "")
    {
        $invoice_number = Invoice::generateInvoiceNumber();
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        $transaction = Invoice::where(['user_id' => auth()->user()->id])->where('id', $id)->where(['branch_id' => $branch_id])->where('status', 'Hold')->first();
        $createdAt = Carbon::parse($transaction->hold_date); // ensure Carbon instance
        $transaction->ref_no = $transaction->invoice_number;
        $transaction->invoice_number = $invoice_number;
        $transaction->status = "resumed";
        $transaction->save();
        if (!empty($party_user_id)) {
            $partyUserImage = PartyUserImage::where('transaction_id', $transaction->id)->where('party_user_id', $party_user_id)->where('type', 'hold')->first(["image_path", "product_image_path"]);
            if (!empty($partyUserImage)) {
                $this->dispatch('setHoldImage', [
                    'type' => "party",
                    'customer' => $partyUserImage->image_path,
                    'product' => $partyUserImage->product_image_path
                ]);
            }
        } else if (!empty($commission_user_id)) {
            $commissionUserImage = CommissionUserImage::where('commission_user_id', $commission_user_id)->where('type', 'hold')->first(["image_path", "product_image_path"]);
            if (!empty($commissionUserImage->image_path)) {
                $this->dispatch('setHoldImage', [
                    'type' => "commission",
                    'customer' => $commissionUserImage->image_path,
                    'product' => $commissionUserImage->product_image_path
                ]);
            }
        }

        // Store in session that a transaction is being resumed
        session()->put('current_party_id', $party_user_id);
        session()->put('current_transaction_id', $transaction->id);
        session()->put('current_commission_id', $commission_user_id);
        //session()->put('resumed_transaction_time', now()); // optional timestamp
        foreach ($transaction->items as $key => $value) {
            $product = Product::where('name', $value['name'])->first();
            if (!empty($product)) {

                $item = new Cart();
                $item->user_id = auth()->user()->id;
                $item->quantity = $value['quantity'];
                $item->product_id = $product->id;
                $item->amount = $value['mrp'];
                $item->discount = $value['mrp'] - $value['price'];
                $item->net_amount = $value['mrp'] - $item->discount;
                $item->mrp = $value['mrp'];
                $item->save();
            }
        }
        $this->loadHoldTransactions(); // refresh list

        $this->dispatch('updateNewProductDetails');
        $this->dispatch('updateCartCount');
        $this->dispatch('setNotes');
        $this->dispatch('close-hold-modal');
        $this->dispatch('updateCustomerDetailHold', [
            'party_user_id' => $party_user_id,
            'commission_user_id' => $commission_user_id,
            'type' => 'resume',
            'invoice_number' => $transaction->invoice_number
        ]);
        $this->dispatch('notiffication-success', ['message' => 'Transaction resumed successfully']);
    }

    public function printInvoice($id)
    {
        $invoice = \App\Models\Invoice::where("id", $id)->latest('id')->first();
        $sunTot = (int) $invoice->total + (int)$invoice->party_amount;

        if (!$invoice) {
            // Handle case where no invoice exists
            return;
        }

        $pdfPath = storage_path('app/public/invoices/hold_invoice_' . $invoice->invoice_number . '.pdf');
        if (!empty($invoice->party_user_id)) {

            $partyUser = PartyUser::where('status', 'Active')->find($invoice->party_user_id);
        } else if (!empty($invoice->commission_user_id)) {

            $partyUser = Commissionuser::where('status', 'Active')->where('is_deleted', '!=', 'Yes')->find($invoice->commission_user_id);
        }

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('hold', ['invoice' => $invoice, 'items' => $invoice->items, 'branch' => auth()->user()->userinfo->branch, 'hold' => true, 'customer_name' => @$partyUser->first_name, "hold_date" => $invoice->hold_date, "ref_no" => $invoice->ref_no]);
        $pdf->save($pdfPath);

        $this->dispatch('triggerPrint', [
            'pdfPath' => asset('storage/invoices/hold_invoice_' . $invoice->invoice_number . '.pdf')
        ]);
    }

    public function deleteTransaction($id)
    {
        // Assuming you're using a model like HoldTransaction
        $transaction = Invoice::find($id);

        if ($transaction) {
            $transaction->delete(); // or clear from session, if using session storage
            $this->dispatch('notiffication-success', ['message' => 'Transaction deleted successfully']);
        }

        // Refresh the hold transactions list if necessary
        $this->loadHoldTransactions();
    }

    public function deleteConfirmed($id)
    {
        $transaction = Invoice::find($id);

        if ($transaction) {
            $transaction->delete();
            $this->loadHoldTransactions(); // Or however you're refreshing the list
            $this->dispatch('notiffication-success', ['message' => 'Transaction deleted successfully']);
        }
    }

    private function storePhotoPathsInSession(string $productPath, string $customerPath): void
    {
        session([
            auth()->id() . "_" . Auth::user()->role->name . "_" . self::SESSION_KEY_PRODUCT => $productPath,
            auth()->id() . "_" . Auth::user()->role->name . "_" . self::SESSION_KEY_CUSTOMER => $customerPath,
            auth()->id() . "_" . Auth::user()->role->name . "_" . self::SESSION_KEY_TIMESTAMP => now()->timestamp
        ]);
    }
    public function render()
    {
        return view('livewire.hold-transactions', [
            'holdTransactions' => $this->holdTransactions
        ]);
    }
}
