<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Shoppingcart as Cart;
use App\Models\Commissionuser;
use App\Models\Partyuser;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Str;
use App\Models\Invoice;
use App\Models\Product;
use Livewire\WithPagination;
use Carbon\Carbon;
use App\Models\CashInHand;
use App\Models\UserShift;

class Shoppingcart extends Component
{

    use WithPagination;
    public $invoiceData;

    public $changeAmount = 0;
    public $showBox = false;
    public $cashPayAmt;
    public $cashPaTenderyAmt;
    public $cashPayChangeAmt;

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
    public $tenderedAmount = 0;
    public $showModal = false;
    public $selectedUser = 0;
    protected $listeners = ['updateProductList' => 'loadCartData'];
    public $noteDenominations = [
        0 => 2000,
        1 => 500,
        2 => 200,
        3 => 100,

    ];
    public $remainingAmount = 0;
    public $totalBreakdown = [];
    public $searchTerm = '';
    public $branch_name = '';
    public $quantities = [];
    public $showSuggestions = false;
    public $selectedNote;
    public $cashNotes = [];

    public function toggleBox()
    {
        if (!empty($this->products->toArray())) {
            $this->showBox = true;
            $this->total = $this->cashAmount;
        } else {
            session()->flash('error', 'add minimum one product');
            $this->dispatch('alert_remove');

        }
    }


    public function updatedNoteDenominations()
    {
        $this->calculateBreakdown();
    }

    public function updatedTenderedAmount()
    {
        $this->changeAmount = $this->tenderedAmount - $this->cashAmount;
    }

    public function calculateBreakdown()
    {
        $this->totalBreakdown = [];
        $total = 0;

        foreach ($this->noteDenominations as $note => $count) {
            $amount = $note * $count;
            $this->totalBreakdown[$note] = $amount;
            $total += $amount;
        }

        //$this->remainingAmount = $this->cashAmount - $total;
    }


    public function mount()
    {
        $today = Carbon::today();
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";

        $UserShift = UserShift::whereDate('created_at', $today)->where(['user_id' => auth()->user()->id])->where(['branch_id'=>$branch_id])->exists();
        if(empty($UserShift)){ 
            $this->showModal = true;
        } 

        $this->loadCartData();
        $this->commissionUsers = Commissionuser::all(); // Assuming you have a model for this
        $this->partyUsers = Partyuser::all(); // Assuming you have a model for this
        foreach ($this->cartitems as $item) {
            $this->quantities[$item->id] = $item->quantity;
        }
    }

    public function loadCartData()
    {
        $this->branch_name = (!empty(auth()->user()->userinfo->branch->name)) ? auth()->user()->userinfo->branch->name : "";
        $this->cartitems = Cart::with('product')
            ->where(['user_id' => auth()->user()->id])
            ->where('status', '!=', Cart::STATUS['success'])
            ->get();


        $this->calculateTotals();
        $this->getCartItemCount();
        $this->products = Cart::with('product')
            ->where(['user_id' => auth()->user()->id])
            ->where('status', '!=', Cart::STATUS['success'])
            ->get();
    }
    public function updateQty($itemId)
    {
        $quantity = (isset($this->quantities[$itemId])) ? (int) $this->quantities[$itemId] : 0;
        if ($quantity < 1) {
            $quantity = 1;
            $this->quantities[$itemId] = 1;
        }

        $item = Cart::find($itemId);
        if ($item) {
            $item->quantity = $quantity;
            $item->save();
        }

        // Optional: refresh cart items if needed
        $this->cartitems = Cart::with('product')
            ->where(['user_id' => auth()->user()->id])
            ->where('status', '!=', Cart::STATUS['success'])
            ->get();

        $this->dispatch('updateCartCount');
        $this->dispatch('updateProductList');
    }


    public function calculateTotals()
    {
        $this->sub_total = $this->cartitems->sum(
            fn($item) =>
            !empty($item->product->inventorie->sell_price)
                ? $item->product->inventorie->sell_price * $item->quantity
                : 0
        );
        //$this->tax = $this->sub_total * 0.18;
        $this->cashAmount = $this->total;
        // $this->remainingAmount = $this->cashAmount;
    }
    // public function calculateBreakdown()
    // {
    //     $remaining = $this->cashAmount;
    //     $this->totalBreakdown = [];

    //     foreach ($this->noteDenominations as $note => $count) {
    //         $breakdown = $note * (int)$count;
    //         $remaining -= $breakdown;

    //         $this->totalBreakdown[$note] = $breakdown;
    //     }

    //     $this->remainingAmount = $remaining;
    // }
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
        $this->cartCount = Cart::where('user_id', auth()->id())
            ->where('status', '!=', Cart::STATUS['success'])
            ->sum('quantity');


        $this->dispatch('updateCartCount');
    }

    public function incrementQty($id)
    {
        $item = Cart::find($id);
        if ($item) {
            $item->quantity++;
            $item->save();
            if (isset($this->quantities[$id])) {
                $this->quantities[$id]++;
                $this->updateQty($id);
            }
            $this->loadCartData();
        }
    }

    public function decrementQty($id)
    {
        $item = Cart::find($id);
        if ($item && $item->quantity > 1) {
            $item->quantity--;
            $item->save();
            if (isset($this->quantities[$id]) && $this->quantities[$id] > 1) {
                $this->quantities[$id]--;
                $this->updateQty($id);
            }
            $this->loadCartData();
        }
    }

    public function removeItem($id)
    {
        Cart::find($id)?->delete();
        $this->showBox = false;
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
            $this->total = $this->cashAmount = $this->total - $getDiscountAmt;
        } else {
            $this->commissionAmount = 0;
        }
    }
    public function calculateParty()
    {
        $user = Partyuser::find($this->selectedPartyUser);
        if (!empty($user)) {
            $this->partyAmount = $user->credit_points;
        } else {
            $this->partyAmount = 0;
        }
        $this->total = $this->total - $this->partyAmount;
        $this->cashAmount = $this->total;
    }
    public function render()
    {

        if (strlen($this->searchTerm) > 1) {
            $this->searchResults = Product::with('inventorie')
                ->when($this->searchTerm, function ($query) {
                    $query->where('name', 'like', '%' . $this->searchTerm . '%');
                })
                ->take(5)
                ->get();
            $this->showSuggestions = true;
        } else {
            $this->searchResults = [];
        }
        $itemCarts = Cart::GetCartItems();

        return view('livewire.shoppingcart', [
            'itemCarts' => $itemCarts,
            'searchResults' => $this->searchTerm,
        ]);
    }
    public function addToCart($id)
    {
        if (auth()->user()) {
            // add to cart
            $data = [
                'user_id' => auth()->user()->id,
                'product_id' => $id,
            ];
            $CartDb = Cart::updateOrCreate($data);
            $this->updateQty($CartDb->id);
            $this->dispatch('updateCartCount');
            $this->dispatch('updateProductList');
            $this->reset('searchTerm', 'searchResults', 'showSuggestions');
            session()->flash('success', 'Product added to the cart successfully');
        } else {
            // redirect to login page
            return redirect(route('login'));
        }
    }

    // public function checkout()
    // {
    //     if (!empty($this->commissionAmount)) {
    //         $this->total = $this->total - $this->commissionAmount;
    //     }
    //     if (!empty($this->partyAmount)) {
    //         $this->total = $this->total - $this->partyAmount;
    //     }
    //     $commissionUser = CommissionUser::find($this->selectedCommissionUser);
    //     $partyUser = PartyUser::find($this->selectedPartyUser);
    //     $cartitems = $this->cartitems;
    //     foreach ($cartitems as $key => $cartitem) {
    //         $product = $cartitem->product->inventorie;
    //         if ($product) {
    //             $product->quantity -= $cartitem->quantity;
    //             $product->save();
    //         }
    //     }
    //     $invoice_number = 'INV-' . strtoupper(Str::random(8));

    //     $invoice = Invoice::create([
    //         'invoice_number' => $invoice_number,
    //         'commission_user_id' => $commissionUser->id ?? null,
    //         'party_user_id' => $partyUser->id ?? null,
    //         'items' => $cartitems->map(fn($item) => [
    //             'name' => $item->product->name,
    //             'quantity' => $item->quantity,
    //             'price' => $item->product->inventorie->sell_price,
    //         ]),
    //         'sub_total' => $this->sub_total,
    //         'tax' => $this->tax,
    //         'commission_amount' => $this->commissionAmount,
    //         'party_amount' => $this->partyAmount,
    //         'total' => $this->total,
    //     ]);

    //     // Clear cart if needed
    //     // Cart::clear();

    //     return redirect()->route('invoice.show', $invoice->id);
    // }
    public function checkout()
    {
        if (!empty($this->commissionAmount)) {
            $this->total -= $this->commissionAmount;
        }
        if (!empty($this->partyAmount)) {
            $this->total -= $this->partyAmount;
        }

        $commissionUser = CommissionUser::find($this->selectedCommissionUser);
        $partyUser = PartyUser::find($this->selectedPartyUser);
        $cartitems = $this->cartitems;

        foreach ($cartitems as $key => $cartitem) {
            $product = $cartitem->product->inventorie;
            if ($product && $product->quantity>0) {
                $product->quantity -= $cartitem->quantity;
                $product->save();
            }
        }

        $cashNotes = json_encode($this->cashNotes) ?? [];

        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        // 💾 Save cash breakdown
        $cashBreakdown = \App\Models\CashBreakdown::create([
            'user_id' => auth()->id(),
            'branch_id' => $branch_id,
            'denominations' => $cashNotes,
            'total' => $this->total,
        ]);

        $invoice_number = 'INV-' . strtoupper(Str::random(8));
        if(!empty($commissionUser)){
            $address = $commissionUser->address ?? null;
        }else  if(!empty($partyUser)){
            $address = $partyUser->address ?? null;
        }
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
            'status'=>"Paid",
            'commission_amount' => $this->commissionAmount,
            'party_amount' => $this->partyAmount,
            'total' => $this->total,
            'cash_break_id' => $cashBreakdown->id,
            //'billing_address'=> $address,
        ]);
        // ✅ Set invoice data for the view
        $this->invoiceData = $invoice;
        // ✅ Trigger print via browser event
        $this->dispatch('triggerPrint');
        //return redirect()->route('invoice.show', $invoice->id);
        $this->reset('searchTerm', 'searchResults', 'showSuggestions');

    }
}
