<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Shoppingcart as Cart;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\UserShift;
use Carbon\Carbon;

class HoldTransactions extends Component
{
    public $holdTransactions = [];

    protected $listeners = ['loadHoldTransactions'];

    public function loadHoldTransactions()
    {
        $today = Carbon::today();

        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        $currentShift = UserShift::whereDate('created_at', $today)->where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->where(['status' =>"pending"])->first();
        $start_date = @$currentShift->start_time; // your start date (set manually)
        $totalWith = \App\Models\WithdrawCash::where('user_id',  auth()->user()->id)
        ->where('branch_id', $branch_id)->whereBetween('created_at', [$start_date, $currentShift->end_date])->sum('amount');
        $this->holdTransactions =  Invoice::where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->where('status', 'Hold')->get();

       // $this->holdTransactions = Cart::where('user_id', auth()->user()->id)->where('status', Cart::STATUS_HOLD)->get();

    }

    public function resumeTransaction($id)
    {
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        $transaction = Invoice::where(['user_id' => auth()->user()->id])->where('id', $id)->where(['branch_id' => $branch_id])->where('status', 'Hold')->first();
        $transaction->status =Cart::STATUS_PENDING;
        $transaction->save();
        
        foreach ($transaction->items as $key => $value) {
            $product =Product::where('name', $value['name'])->first();
            if(!empty($product)){

                $item=new Cart();
                $item->user_id = auth()->user()->id;
                $item->quantity = $value['quantity'];
                $item->product_id = $product->id;
                $item->amount = $value['price'];
                $item->net_amount= $value['price'];
                $item->mrp = $value['price'];
                $item->save();
            }
        }


        $this->loadHoldTransactions(); // refresh list
        
        $this->dispatch('updateNewProductDetails');
        $this->dispatch('updateCartCount');
        $this->dispatch('close-hold-modal');
        $this->dispatch('notiffication-success', ['message' => 'Transaction resumed successfully']);

    }

    public function render()
    {
        return view('livewire.hold-transactions');
    }
}
