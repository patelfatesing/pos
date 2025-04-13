<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Shoppingcart as Cart;
use App\Models\Commissionuser;
use App\Models\Partyuser;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use App\Models\Invoice;

class Shoppingcart extends Component
{
    public $cartitems = [];
    public $sub_total = 0;
    public $tax = 0;
    public $cashAmount = 0;
    public $onlineAmount = 0;
    public $cartCount = 0;
    public $selectedCommissionUser;
    public $selectedPartyUser;
    public $commissionUsers = [];
    public $partyUsers = [];
    public $commissionAmount = 0;
    public $partyAmount = 0;
    public $productSearch = '';
    public $searchResults = [];
    public $products = [];
    public $selectedUser = 0;
    protected $listeners = ['updateProductList' => 'loadCartData'];

    public function mount()
    {
        $this->loadCartData();
        $this->commissionUsers = Commissionuser::all(); // Assuming you have a model for this
        $this->partyUsers = Partyuser::all(); // Assuming you have a model for this
    }

    public function loadCartData()
    {
        $this->cartitems = Cart::with('product')
                ->where(['user_id'=>auth()->user()->id])
                ->where('status', '!=', Cart::STATUS['success'])
                ->get();

        $this->calculateTotals();
        $this->getCartItemCount();
        $this->products = Cart::with('product')
                ->where(['user_id'=>auth()->user()->id])
                ->where('status', '!=', Cart::STATUS['success'])
                ->get();
    }

    public function calculateTotals()
    {
        $this->sub_total = $this->cartitems->sum(fn($item) => 
            !empty($item->product->inventorie->sell_price) 
            ? $item->product->inventorie->sell_price * $item->quantity 
            : 0
        );
        //$this->tax = $this->sub_total * 0.18;
        $this->cashAmount = $this->total;
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


    public function calculateCommission()
    {
        $this->dispatch('user-selection-updated', ['userId' => $this->selectedUser]);

       
        $user = Commissionuser::find($this->selectedCommissionUser);
        if (!empty($user)) {
            $getDiscountAmt = Cart::with(['product', 'product.inventorie'])
            ->where(['user_id' => auth()->user()->id])
            ->where('status', '!=', Cart::STATUS['success'])
            ->get()
            ->sum(fn($cart) => $cart->product->inventorie->discount_price ?? 0);
            $this->commissionAmount = $getDiscountAmt;
            $this->total=$this->cashAmount = $this->total - $getDiscountAmt;

        } else {
            $this->commissionAmount = 0;
        }
    }
    public function calculateParty(){
        $user = Partyuser::find($this->selectedPartyUser);
        if (!empty($user)) {
            $this->partyAmount =$user->credit_points;
        } else {
            $this->partyAmount = 0;
        }
        $this->total = $this->total - $this->partyAmount;
        $this->cashAmount = $this->total;
    }
    public function render()
    {
        return view('livewire.shoppingcart');
    }

    public function checkout()
    {
        if (!empty($this->commissionAmount)) {
            $this->total = $this->total - $this->commissionAmount;
        }
        if (!empty($this->partyAmount)) {
            $this->total = $this->total - $this->partyAmount;
        }
        $commissionUser = CommissionUser::find($this->selectedCommissionUser);
        $partyUser = PartyUser::find($this->selectedPartyUser);
        $cartitems = $this->cartitems;
        foreach ($cartitems as $key => $cartitem) {
            $product = $cartitem->product->inventorie;
            if ($product) {
                $product->quantity -= $cartitem->quantity;
                $product->save();
            }
        }
        $invoice_number = 'INV-' . strtoupper(Str::random(8));
        
        $invoice = Invoice::create([
            'invoice_number' => $invoice_number,
            'commission_user_id' => $commissionUser->id ?? null,
            'party_user_id' => $partyUser->id ?? null,
            'items' => $cartitems->map(fn($item) => [
                'name' => $item->product->name,
                'quantity' => $item->quantity,
                'price' => $item->product->inventorie->sell_price,
            ]),
            'sub_total' => $this->sub_total,
            'tax' => $this->tax,
            'commission_amount' => $this->commissionAmount,
            'party_amount' => $this->partyAmount,
            'total' => $this->total,
        ]);

        // Clear cart if needed
        // Cart::clear();

        return redirect()->route('invoice.show', $invoice->id);
    }
}

