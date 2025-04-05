<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Shoppingcart as Cart;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class Shoppingcart extends Component
{
    public $cartitems = [];
    public $sub_total = 0;
    public $tax = 0;
    public $cashAmount = 0;
    public $onlineAmount = 0;
    public $cartCount = 0;

    public function mount()
    {
        $this->loadCartData();
    }

    public function loadCartData()
    {
        $this->cartitems = Cart::with('product')
                ->where(['user_id'=>auth()->user()->id])
                ->where('status', '!=', Cart::STATUS['success'])
                ->get();

        $this->calculateTotals();
        $this->getCartItemCount();
    }

    public function calculateTotals()
    {
        $this->sub_total = $this->cartitems->sum(fn($item) => $item->product->price * $item->quantity);
        $this->tax = $this->sub_total * 0.18;
    }

    public function getTotalProperty()
    {
        return $this->sub_total + $this->tax;
    }

    public function getNoteBreakdownProperty()
    {
        $cash = $this->cashAmount;

        return [
            'thousand' => intdiv($cash, 1000),
            'five_hundred' => intdiv($cash % 1000, 500),
            'two_hundred' => intdiv(($cash % 1000) % 500, 200),
        ];
    }

    public function getCartItemCount()
    {
        $this->cartCount =  Cart::with('product')
            ->where(['user_id'=>auth()->user()->id])
            ->where('status', '!=', Cart::STATUS['success'])
            ->count();

        $this->dispatch('updateCartCount');
    }

    public function incrementQty($id)
    {
        $item = Cart::find($id);
        if ($item) {
            $item->quantity++;
            $item->save();
            $this->loadCartData();
        }
    }

    public function decrementQty($id)
    {
        $item = Cart::find($id);
        if ($item && $item->quantity > 1) {
            $item->quantity--;
            $item->save();
            $this->loadCartData();
        }
    }

    public function removeItem($id)
    {
        Cart::find($id)?->delete();
        $this->loadCartData();
    }

    public function checkout()
    {
        $totalPaid = $this->cashAmount + $this->onlineAmount;

        if ($totalPaid < $this->total) {
            session()->flash('error', 'Payment amount is less than total.');
            return;
        }

        foreach ($this->cartitems as $item) {
            $item->status = Shoppingcart::STATUS['success'];
            $item->save();
        }

        session()->flash('success', 'Order placed successfully!');
        $this->cashAmount = 0;
        $this->onlineAmount = 0;
        $this->loadCartData();
    }

    public function render()
    {
        return view('livewire.shoppingcart');
    }
}

