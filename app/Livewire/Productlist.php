<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use App\Models\Shoppingcart as Cart;

class Productlist extends Component
{
    public $search = '';
    public $searchInput = '';
    public $total = 0;

    public function doSearch()
    {
        $this->search = $this->searchInput;
    }
    public function resetSearch()
    {
        $this->searchInput = '';
        $this->search = '';
    }
    public function getCartItemCount()
    {
        $this->cartCount =  Cart::with('product')
            ->where(['user_id'=>auth()->user()->id])
            ->where('status', '!=', Cart::STATUS['success'])
            ->count();

        $this->dispatch('updateCartCount');
    }
    public function loadCartData()
    {

        
        $this->cartitems = Cart::with('product')
        ->where(['user_id'=>auth()->user()->id])
        ->where('status', '!=', Cart::STATUS['success'])
        ->get();
        \Log::info('Product added to cart: '.json_encode($this->cartitems));

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
            !empty($item->product->sell_price) 
            ? $item->product->sell_price * $item->quantity 
            : 0
        );
        //$this->tax = $this->sub_total * 0.18;
        $this->cashAmount = $this->total;
    }

    public function incrementQty($id)
    {
        $item = Cart::where('product_id', $id)
                ->where('user_id', auth()->user()->id)
                ->first();
        if ($item) {
            $item->quantity++;
            $item->save();
            $this->loadCartData();
            $this->dispatch('updateProductList');

        }
    }

    public function decrementQty($id)
    {
        $item = Cart::where('product_id', $id)
                ->where('user_id', auth()->user()->id)
                ->first();
        if ($item && $item->quantity > 1) {
            $item->quantity--;
            $item->save();
            $this->loadCartData();
            $this->dispatch('updateProductList');

        }
    }
    public function render()
    {
        $products = Product::with('inventorie')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->get();

        return view('livewire.productlist', [
        'products' => $products
        ]);
    }
    public function addToCart($id){
        if(auth()->user()){
            // add to cart
            $data = [
                'user_id' => auth()->user()->id,
                'product_id' => $id,
            ];
            Cart::updateOrCreate($data);

            $this->dispatch('updateCartCount');
            $this->dispatch('updateProductList');

            session()->flash('success','Product added to the cart successfully');
        }else{
            // redirect to login page
            return redirect(route('login'));
        }
    }
}
