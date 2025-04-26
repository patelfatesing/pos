<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Shoppingcart as Cart;


class HoldTransactions extends Component
{
    public $holdTransactions = [];

    protected $listeners = ['loadHoldTransactions'];

    public function loadHoldTransactions()
    {
        $this->holdTransactions = Cart::where('user_id', auth()->user()->id)->where('status', Cart::STATUS_HOLD)->get();

    }

    public function resumeTransaction($id)
    {
        $transaction = Cart::where('user_id', auth()->user()->id)->where('id', $id)->where('status', Cart::STATUS_HOLD)->first();
        $transaction->status =Cart::STATUS_PENDING;
        $transaction->save();

        $this->loadHoldTransactions(); // refresh list
        
        $this->dispatch('updateProductList');
        $this->dispatch('updateCartCount');
        $this->dispatch('close-hold-modal');

        session()->flash('message', 'Transaction re2sumed!');
    }

    public function render()
    {
        return view('livewire.hold-transactions');
    }
}
