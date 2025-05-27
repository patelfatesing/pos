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
use PhpParser\Node\Expr\PreInc;
use App\Models\CashBreakdown;
use Illuminate\Support\Facades\Log;
use App\Models\Branch;
use App\Models\User;
use App\Models\CreditHistory;
use App\Models\DiscountHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Models\Refund;
use App\Models\InvoiceHistory;
use App\Models\PartyCustomerProductsPrice;
use App\Models\CommissionUserImage;
use App\Models\PartyUserImage;
use App\Models\DailyProductStock;
use Illuminate\Support\Facades\Storage;

class Shoppingcart extends Component
{

    use WithPagination;
    public $cartItems = [];

    public $invoiceData;
    public $totalInvoicedAmount=0;
    public $cash = 0;
    public $creditPay=0;
    public $upi = 0;
    public $updatingField = null;
    public $showCloseButton = false;
    public $partImsgs=[];
    public $shift;
    public $shiftcash;
   public  $narrations = [
        'Personal Expenses',
        'Travel Expenses',
        'Other'
    ];
    public $errorInCredit = false;
    public $changeAmount = 0;
    public $showBox = false;
    public $shoeCashUpi = false;
    public $showRefund=false;
    public $showOnline=false;
    public $showSr = false;
    public $cashPayAmt;
    public $cashPaTenderyAmt;
    public $cashPayChangeAmt;
    public $categoryTotals = [];
    public $cartitems = [];
    public $sub_total = 0;
    public $productImage = "";
    public $userImage = "";
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
    public $basicPartyAmt=0;
    public $productSearch = '';
    public $searchResults = [];
    public $searchSalesResults = [];

    public $products = [];
    public $tenderedAmount = 0;
    public $showModal = false;
    public $availableNotes="";
    public $selectedUser = 0;
    protected $listeners = ['updateProductList' => 'loadCartData','loadHoldTransactions','updateNewProductDetails','resetData','hideSuggestions'];
    public $noteDenominations = [10, 20, 50, 100, 200,500];
    public $remainingAmount = 0;
    public $totalBreakdown = [];
    public $searchTerm = '';
    public $searchSalesReturn='';
    public $branch_name = '';
    public $quantities = [];
    public $showSuggestions = false;
    public $showSuggestionsSales = false;
    public $showCheckbox=false;
    public $selectedNote;
    public $cashNotes = [];
    public $todayCash = 0;
    public $upiPayment = 0;
    public $cashPayment = 0;
    public $paymentType = "";
    public $scTotalCashAmt = 0;
    public $scTotalUpiAmt = 0;
    public $shiftEndTime = "";
    public $cashupiNotes=[];
    public $numpadValue = '0'; // Default value of numpad
    public $focusedField = null; // Track the currently focused input field
    public $search = '';

    public $selectedProduct;
    public $selectedSalesReturn;
    public $holdTransactions=[];
    public $headertitle="";
    public $language;
    public $refundDesc="";
    public bool $useCredit = false;  // Tracks checkbox state
    public $partyUserDetails;
    public $partyUserDiscountAmt=0;
    public $finalDiscountPartyAmount=0;
    public $productStock = [];

    // This method is triggered whenever the checkbox is checked or unchecked
    public function updatedUseCredit($value)
    {
        // Reset the credit amount to 0 if the checkbox is unchecked
        if (!$value) {
            $this->useCredit = false;  // Optional: Reset the amount if checkbox is unchecked
        }
    }

    public function toggleCheck()
    {
        $this->useCredit = !$this->useCredit;
         if ($this->useCredit && $this->selectedPartyUser) {
            $this->partyUserDetails = Partyuser::where('status', 'Active')->where('is_delete','!=', 'Yes')->find($this->selectedPartyUser);
            if($this->selectedSalesReturn){
                $this->creditPay=$this->selectedSalesReturn->creditpay;
                $this->creditPayChanged();
            }
        } else {
            $this->creditPay=0;
            $this->partyUserDetails = null;
            $this->creditPayChanged();
        }
     
    }

    public function printLastInvoice()
    {
        $invoice = \App\Models\Invoice::latest('id')->first();
        $sunTot=(Int) $invoice->total+(Int)$invoice->party_amount;

        if (!$invoice) {
            // Handle case where no invoice exists
            return;
        }

        $pdfPath = storage_path('app/public/invoices/duplicate_' . $invoice->invoice_number . '.pdf');

        if (!file_exists($pdfPath)) {
            $pdf = App::make('dompdf.wrapper');
            $pdf->loadView('invoice', ['invoice' => $invoice, 'items' => $invoice->items, 'branch' => auth()->user()->userinfo->branch, 'duplicate' => true]);
            $pdf->save($pdfPath);
        }
        $this->reset('searchTerm', 'searchResults', 'showSuggestions', 'cashAmount', 'shoeCashUpi', 'showBox', 'cashNotes', 'quantities', 'cartCount','selectedSalesReturn', 'selectedPartyUser', 'selectedCommissionUser', 'paymentType', 'creditPay', 'partyAmount', 'commissionAmount', 'sub_total', 'tax', 'totalBreakdown','cartitems');
        
        $this->dispatch('triggerPrint', [
            'pdfPath' => asset('storage/invoices/duplicate_' . $invoice->invoice_number . '.pdf')
        ]);
    }

    public function updatedSearch($value)
    {
        $this->selectedProduct = Product::where('barcode', $value)->first();
        //  if (!$this->selectedProduct) {
        //     $this->dispatch('notiffication-error', [
        //         'message' => 'Product with this barcode not found please check with admin.'
        //     ]);
        //     return;
        // }
    }

    public function updatedSearchSalesReturn($value)
    {
        $this->selectedSalesReturn = Invoice::with('cashBreak')
            ->where('invoice_number', $value)
            ->where('user_id', auth()->id())
            ->where('branch_id', auth()->user()->userinfo->branch->id ?? null)
            ->first();
    }

    public function addToCartBarCode()
    {
        if (!$this->selectedProduct) return;
            $currentQty=$this->cartCount+1;
            $totalQuantity = $this->selectedSalesReturn ? collect($this->selectedSalesReturn->items)->sum('quantity') : 0;
            if (!empty($this->selectedSalesReturn) && $this->cartCount >= $totalQuantity) {
                $this->dispatch('notiffication-error', [
                    'message' => 'Adding more items is not allowed in a refund transaction.'
                ]);
                return;
            }

            // Fetch product with inventory

            $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";

            $product = Product::select('products.*', 'inventory_summary.total_quantity')
                ->leftJoin(DB::raw('(
                    SELECT product_id, SUM(quantity) as total_quantity
                    FROM inventories where store_id = '.$branch_id.'
                    GROUP BY product_id
                ) as inventory_summary'), 'products.id', '=', 'inventory_summary.product_id')
                ->where('products.id', $this->selectedProduct->id)
                ->first();

            if ( $currentQty > $product['total_quantity']) {
                $this->dispatch('notiffication-error', ['message' => 'Product is out of stock and cannot be added to cart.']);
                return;
            }
            
            // $item = Cart::where('product_id', $id)
            // ->where('user_id', auth()->id())
            // ->where('status', Cart::STATUS_PENDING)
            // ->first();
            //  if (!empty($item)) {
            //     $item->quantity = $item->quantity + 1;
            //     $item->save();
            // }else{
            //     $item=new Cart();
            //     $item->user_id = auth()->user()->id;
            //     $item->product_id = $id;
            //     $item->save();

            // }
            $user = Partyuser::where('status', 'Active')->find($this->selectedPartyUser);
            if (!empty($user)) {
                $myCart=$user->credit_points;
            } else {
                $myCart=0;
            }
            $item = Cart::where('product_id', $this->selectedProduct->id)
            ->where('user_id', auth()->id())
            ->where('status', Cart::STATUS_PENDING)
            ->first();
             if (!empty($item)) {
                $this->incrementQty($item->id);
            }else{
                $item=new Cart();
                $item->user_id = auth()->user()->id;
                $item->product_id = $this->selectedProduct->id;
                $item->mrp = $product->sell_price;
                $item->amount = $product->sell_price-$myCart;
                $item->discount = $myCart;
                $item->net_amount = $product->sell_price-$myCart;
                $item->save();

            }

            $this->finalDiscountParty();
            if($this->selectedCommissionUser){
                $this->commissionAmount = $this->finalDiscountPartyAmount;
            }else{
                $this->partyAmount = $this->finalDiscountPartyAmount;
            }  
            
           // $this->updateQty($item->id);
            $this->dispatch('updateNewProductDetails');
            $this->reset('searchTerm', 'searchResults', 'showSuggestions','search');
          //  session()->flash('success', 'Product added to the cart successfully');
           // $this->dispatch('notiffication-sucess', ['message' => 'Product added to the cart successfully']);
    }

    public function addToSalesreturn()
    {
        if(@$this->selectedSalesReturn->status=="Paid"){
           // $this->cashNotes = json_decode($this->selectedSalesReturn->cashBreak->denominations ?? 0, true);
            $existingItem = Cart::where('user_id', auth()->id())
                ->where('status', Cart::STATUS_PENDING)
                ->first();
            //check
            if ($existingItem) {
                $this->dispatch('notiffication-error', ['message' => 'Product already exists in the cart. Please clear it first.']);
                return;
            }
            $this->selectedPartyUser = $this->selectedSalesReturn->party_user_id ?? 0;
            
          

            //$this->partyAmount = $this->selectedSalesReturn->party_amount ?? 0;
          
        
            //$this->showBox = true;
            $this->paymentType = "cash";
            $sumQty=0;
            // if (!$this->selectedProduct) return;
            foreach ($this->selectedSalesReturn->items as $key => $value) {
        
                $product = Product::where('id', $value['product_id'])->first();
                if (!empty($product)) {
                    $sumQty+=$value['quantity'];
                    $this->getDiscountPrice($value['product_id'],$this->selectedPartyUser);
                    $item = new Cart();
                    $item->user_id = auth()->user()->id;
                    $item->product_id = $product->id;
                    $item->mrp = $value['mrp'];
                    $item->quantity = $value['quantity'];
                    $item->amount = $value['mrp'];
                    $item->discount=$this->partyUserDiscountAmt*$item->quantity;
                     $item->net_amount = ($value['price'] );
                    $item->save();
                }
            }
              $this->finalDiscountParty();

            $this->partyAmount = $this->finalDiscountPartyAmount;
          //  $this->partyAmount = $this->partyAmount*$sumQty;
            if (!empty($this->selectedSalesReturn->creditpay)) {
               // $this->toggleCheck();
                  $this->useCredit=true;
                  $this->showCheckbox=true;
     
                $this->creditPay = $this->selectedSalesReturn->creditpay;
                //$this->creditPay = $this->selectedSalesReturn->creditpay/$sumQty;

            }else{
                $this->useCredit=false;
                $this->showCheckbox=false;
            }
            //$this->cashAmount = $this->selectedSalesReturn->creditpay;
            // $this->updateQty($item->id);
            $this->dispatch('updateNewProductDetails');
        
            $this->reset('searchTerm', 'searchResults', 'showSuggestions', 'search');
            //  session()->flash('success', 'Product added to the cart successfully');
            $this->dispatch('notiffication-sucess', ['message' => 'Product added to the cart successfully']);
        

        }else{
            $this->dispatch('notiffication-error', ['message' => 'Product already refunded']);
            return;
        }


    }

        // Trigger numpad when an input field is clicked
        public function setFocusedField($field)
        {
            $this->focusedField = $field;
            $this->numpadValue = '0'; // Reset numpad value to 0
            $this->dispatch('show-numpad-modal'); // Show the numpad modal
        }

        // Append the clicked number to the focused field
        public function appendNumpadValue($number)
        {
            if ($this->focusedField) {
                $current = (string) data_get($this, $this->focusedField, '');
                $updated = ltrim($current . $number, '0');
                data_set($this, $this->focusedField, $updated ?: '0');

                $this->numpadValue = $updated ?: '0'; // Update the numpad value
                //$this->dispatch('hide-numpad-modal'); // Close modal after entering value
            }
        }

    // Backspace functionality
    public function backspaceNumpad()
    {
        if ($this->focusedField) {
            $current = (string) data_get($this, $this->focusedField, '');
            $updated = substr($current, 0, -1);
            data_set($this, $this->focusedField, $updated);
            $this->numpadValue = $updated ?: '0';
        }
    }

    public function creditPayChanged()
    {
        if($this->creditPay>0){
            $user = Partyuser::where('status', 'Active')->find($this->selectedPartyUser);

            if($this->creditPay > $this->cashAmount){
                $this->errorInCredit = true;
                $this->dispatch('notiffication-error', ['message' => 'Credit payment cannot be greater than cash amount']);
                $this->creditPay=0;
                $this->cashAmount=((Int)$this->sub_total-(Int)$this->partyAmount-(Int)$this->creditPay);
                $this->clearCashNotes();

                return;
            }else if($this->creditPay > $user->credit_points){
                $this->errorInCredit = true;
                $this->dispatch('notiffication-error', ['message' => 'Credit payment cannot be greater than available credit.']);
                $this->creditPay=0;
                $this->cashAmount=((Int)$this->sub_total-(Int)$this->partyAmount-(Int)$this->creditPay);
                $this->clearCashNotes();

                return;
            }else{
                $this->errorInCredit = false;
            }
            $this->cashAmount=((Int)$this->sub_total-(Int)$this->partyAmount-(Int)$this->creditPay);
            $this->clearCashNotes();
        }else{
            $this->cashAmount=((Int)$this->sub_total-(Int)$this->partyAmount);
        }
        
    }

    // Clear numpad value
    public function clearNumpad()
    {
        if ($this->focusedField) {
            data_set($this, $this->focusedField, ''); // Clear the focused field value
            $this->numpadValue = '0'; // Reset the numpad value
        }
    }

    // Apply numpad value
    public function applyNumpadValue()
    {
        $this->dispatch('hide-numpad-modal'); // Close modal when 'OK' is clicked
    }
    
    public function toggleBox()
    {
        if (auth()->user()->hasRole('warehouse')) {
            
            $warehouse_product_photo_path = session(auth()->id().'_warehouse_product_photo_path', []);
            $warehouse_customer_photo_path = session(auth()->id().'_warehouse_customer_photo_path', []);
            if (empty($this->selectedPartyUser)) {
                $this->dispatch('notiffication-error', ['message' => 'Please selecte party customer.']);
    
            }else if (empty($warehouse_product_photo_path) || empty($warehouse_customer_photo_path) )  {
                $this->dispatch('notiffication-error', ['message' => 'Please upload both product,customer images first.']);
    
            }else{
    
                if (!empty($this->products->toArray())) {
                    $this->headertitle="Cash";
                    $this->shoeCashUpi = false;
                    $this->showBox = true;
                    $this->paymentType = "cash";
                    $this->total = $this->cashAmount;
                    $this->useCredit=false;
                } else {
                    $this->dispatch('notiffication-error', ['message' => 'Add minimum one product.']);
                }
            }
        
        }else{
            $cashier_product_photo_path = session(auth()->id().'_cashier_product_photo_path', []);
            $cashier_customer_photo_path = session(auth()->id().'_cashier_customer_photo_path', []);
            
            if (empty($cashier_product_photo_path) || empty($cashier_customer_photo_path))  {
                $this->dispatch('notiffication-error', ['message' => 'Please upload both product,customer images first.']);
    
            }else{

                if (!empty($this->products->toArray())) {
                    $this->headertitle="Cash";
                    $this->shoeCashUpi = false;
                    $this->showBox = true;
                    $this->paymentType = "cash";
                    $this->total = $this->cashAmount;
                    $this->useCredit=false;
                    
                } else {
                    $this->dispatch('notiffication-error', ['message' => 'Add minimum one product.']);
                }
            }
        }

    }

    public function cashupitoggleBox()
    {
        if (auth()->user()->hasRole('warehouse')) {
            $warehouse_product_photo_path = session(auth()->id().'_warehouse_product_photo_path', []);
            $warehouse_customer_photo_path = session(auth()->id().'_warehouse_customer_photo_path', []);
            if (empty($this->selectedPartyUser)) {
                $this->dispatch('notiffication-error', ['message' => 'Please selecte party customer.']);
    
            }else if (empty($warehouse_product_photo_path) || empty($warehouse_customer_photo_path) )  {
                $this->dispatch('notiffication-error', ['message' => 'Please upload both product,customer images first.']);
    
            }else{

                if (!empty($this->products->toArray())) {
                    $this->showBox = false;
                    $this->showOnline=false;
                    $this->shoeCashUpi = true;
                    $this->paymentType = "cashupi";
                    $this->headertitle="Cash + UPI";
                    $this->useCredit=false;
                    $this->total = $this->cashAmount;
                    
        
                } else {
                    $this->dispatch('notiffication-error', ['message' => 'Add minimum one product.']);
                }
            }
        }else{
            $cashier_product_photo_path = session(auth()->id().'_cashier_product_photo_path', []);
            $cashier_customer_photo_path = session(auth()->id().'_cashier_customer_photo_path', []);
            
            if (empty($cashier_product_photo_path) || empty($cashier_customer_photo_path))  {
                $this->dispatch('notiffication-error', ['message' => 'Please upload both product,customer images first.']);
    
            }else{

                if (!empty($this->products->toArray())) {
                    $this->showBox = false;
                    $this->showOnline=false;
                    $this->shoeCashUpi = true;
                    $this->paymentType = "cashupi";
                    $this->headertitle="Cash + UPI";
                    $this->useCredit=false;
                    $this->total = $this->cashAmount;
                    
        
                } else {
                    $this->dispatch('notiffication-error', ['message' => 'Add minimum one product.']);
                }
            }
        }
        
    }

    public function onlinePayment()
    {
        if (auth()->user()->hasRole('warehouse')) {
            $warehouse_product_photo_path = session(auth()->id().'_warehouse_product_photo_path', []);
            $warehouse_customer_photo_path = session(auth()->id().'_warehouse_customer_photo_path', []);
            if (empty($warehouse_product_photo_path) || empty($warehouse_customer_photo_path) )  {
                $this->dispatch('notiffication-error', ['message' => 'Please upload both product,customer images first.']);
    
            }else{

                if (!empty($this->products->toArray())) {
                    $this->showBox = false;
                    $this->shoeCashUpi = true;
                    $this->paymentType = "online";
                    $this->headertitle="Online";
                    $this->showOnline=true;
                    $this->total = $this->cashAmount;
                    
        
                } else {
                    $this->dispatch('notiffication-error', ['message' => 'Add minimum one product.']);
                }
            }
        }else{
            $cashier_product_photo_path = session(auth()->id().'_cashier_product_photo_path', []);
            $cashier_customer_photo_path = session(auth()->id().'_cashier_customer_photo_path', []);
            
            if (empty($this->selectedCommissionUser)) {
                $this->dispatch('notiffication-error', ['message' => 'Please select commission customer.']);
    
            }else if (empty($cashier_product_photo_path) || empty($cashier_customer_photo_path))  {
                $this->dispatch('notiffication-error', ['message' => 'Please upload both product,customer images first.']);
    
            }else{

                if (!empty($this->products->toArray())) {
                    $this->showBox = false;
                    $this->shoeCashUpi = true;
                    $this->paymentType = "online";
                    $this->headertitle="Online";
                    $this->showOnline=true;
                    $this->total = $this->cashAmount;
                    
        
                } else {
                    $this->dispatch('notiffication-error', ['message' => 'Add minimum one product.']);
                }
            }
        }
    }

    public function processRefund()
    {
        try {
            $this->validate([
                'cashNotes' => 'required',
            ]);

            $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";

            $cashNotes = json_encode($this->cashNotes) ?? [];
            $cashBreakdown = CashBreakdown::create([
                'user_id' => auth()->id(),
                'branch_id' => $branch_id,
                'denominations' => $cashNotes,
                'total' => $this->cashAmount,
            ]);

            $refundInvoiceNumber = 'REF-' . strtoupper(Str::random(8));
            // $totalQuantity = $cartitems->sum(fn($item) => $item->quantity);
            // $total_item_total = $cartitems->sum(fn($item) => $item->net_amount);

            $refundInvoice = Invoice::create([
                'user_id' => auth()->id(),
                'branch_id' => $branch_id,
                'invoice_number' => $refundInvoiceNumber,
                'items' => $this->cartitems->map(fn($item) => [
                    'product_id' => $item->product->id,
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->net_amount,
                    'mrp' => $item->mrp,
                ]),
                'cash_amount' => $this->cashAmount,
                'status' => 'Refunded',
                'total' => $this->cashAmount,
                'cash_break_id' => $cashBreakdown->id,
            ]);

            foreach ($this->cartitems as $cartItem) {
                $product = $cartItem->product;
                $inventory = $product->inventories->first();
                if ($inventory) {
                    $inventory->quantity += $cartItem->quantity;
                    $inventory->save();
                }
            }

            Cart::where('user_id', auth()->id())->delete();
            $this->reset('searchTerm', 'searchResults', 'showSuggestions', 'cashAmount', 'cashNotes', 'cartitems', 'cartCount');
            $this->dispatch('notiffication-sucess', ['message' => 'Refund processed successfully.']);
        } catch (\Exception $e) {
            Log::error('Refund processing failed: ' . $e->getMessage());
            $this->dispatch('notiffication-error', ['message' => 'Failed to process refund.']);
        }
    }

    public function refundoggleBox()
    {
        $this->showBox = true;
        $this->shoeCashUpi = false;
        $this->showRefund=false;
        $this->paymentType = "refund";
        $this->headertitle="Refund";
        $this->paymentType="cash";

        $this->total = $this->cashAmount;

    }

    public function srtoggleBox()
    {
        $invoice = Invoice::where('invoice_number', $this->searchSalesReturn)
        ->where('user_id', auth()->id())
        ->first();
        if (!$invoice) {
            $this->dispatch('notiffication-error', ['message' => 'Invoice not found.']);
            return;
        }
         $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        $partyUser = PartyUser::where('status', 'Active')->find($this->selectedPartyUser);
        if(!empty($partyUser)){
            $partyUser->credit_points += $this->creditPay;
            $partyUser->save();
        }
         if (!empty($partyUser->id)) {
            CreditHistory::create(
                
                [
                    'invoice_id' => $invoice->id,
                    'party_user_id' => $partyUser->id ?? null,
                    'credit_amount' => 0.00,
                    'debit_amount' => $this->creditPay,
                    'total_amount' => $this->cashAmount,
                    'total_purchase_items' => $this->cashAmount,
                    'store_id' => $branch_id,
                    'created_by' => auth()->id(),
                ]
            );
        
        }
        $groupedProducts = [];

        foreach ($this->cartitems as $cartitem) {

            $productId = $cartitem->product_id;
            if (!isset($groupedProducts[$productId])) {
                $groupedProducts[$productId] = 0;
            }
            $totalQuantity = collect($this->selectedSalesReturn->items)
                ->where('product_id', $productId)
                ->sum('quantity');
            $groupedProducts[$productId] = $totalQuantity;
        }
        //Loop through each product group and deduct from inventories
        foreach ($groupedProducts as $productId => $totalQuantity) {
            
            $product = $this->cartitems->firstWhere('product_id', $productId)->product;
            $inventories = $product->inventories;
            
            if (isset($inventories[0]) && $inventories[0]->quantity >= $totalQuantity) {
                // Deduct only from the first inventory if it has enough quantity
                $inventories[0]->quantity += $totalQuantity;
                $inventories[0]->save();
            }
        }
        
        // Delete associated cash breakdown entry
        if ($invoice->cash_break_id) {
            CashBreakdown::where('id', $invoice->cash_break_id)->delete();
        }
        $this->useCredit=true;
        $invoice->status = 'Returned';
        //$invoice->total = 0;
        $invoice->items = $this->cartitems->map(function ($item) {
                return [
                    'name' => $item->product->name ?? 'N/A',
                    'quantity' => 0,
                    'category' => $item->product->category->name ?? 'Uncategorized',
                    'price' =>0,
                ];
            });

        $invoice->cash_amount =0;
        $invoice->sub_total =0;
        $invoice->creditpay = 0;
        $invoice->party_amount = 0;
        $invoice->cash_break_id = null; // Clear the cash_break_id
        $invoice->save();
        $cartItems = Cart::where('user_id', auth()->id())->delete(); // <-- get all matching rows

        InvoiceHistory::logFromInvoice($invoice, 'returned', auth()->id());


        $this->reset('searchTerm', 'searchResults', 'showSuggestions', 'cashAmount', 'shoeCashUpi', 'showBox', 'cashNotes', 'quantities', 'cartCount','selectedSalesReturn', 'selectedPartyUser', 'selectedCommissionUser', 'paymentType', 'creditPay', 'partyAmount', 'commissionAmount', 'sub_total', 'tax', 'totalBreakdown','cartitems','searchSalesReturn');
         $this->dispatch('notiffication-sucess', ['message' => 'Sales return initiated.']);
    }

    public function updatedCash($value)
    {
        if ($this->updatingField !== 'upi') {
            $this->updatingField = 'cash';
            $this->cash = floatval($value);
            $this->upi = $this->cashAmount - $this->cash;
            $this->updatingField = null;
            $this->total = $this->cashAmount;
            

        }
    }

    public function updatedUpi($value)
    {
        if ($this->updatingField !== 'cash') {
            $this->updatingField = 'upi';
            $this->upi = floatval($value);
            $this->cash = $this->cashAmount - $this->upi;
            $this->updatingField = null;
            $this->total = $this->cashAmount;
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

    public function getTotalCash()
    {
        $total = 0;

        foreach ($this->cashNotes as $denominationGroup) {
            foreach ($denominationGroup as $denomination => $values) {
                $in = $values['in'] ?? 0;
                $out = $values['out'] ?? 0;
                $total += ($in - $out) * $denomination;
            }
        }
        return $total;
    }

    public function selectNote($key, $denomination, $type)
    {
        $this->cashNotes[$key] = [
            'type' => $type,
            'count' => $this->cashNotes[$key]['count'] ?? 0
        ];
    }

    public function getTotalCashInProperty()
    {
        $total = 0;
        foreach ($this->cashNotes as $key => $note) {
            if ($note['type'] === 'in') {
                $total += $note['count'] * $this->noteDenominations[$key];
            }
        }
        

        return $total;
    }

    public function getTotalCashOutProperty()
    {
        $total = 0;
        foreach ($this->cashNotes as $key => $note) {
            if ($note['type'] === 'out') {
                $total += $note['count'] * $this->noteDenominations[$key];
            }
        }
        return $total;
    }

    public function updatedLanguage($value)
    {
        Session::put('locale', $value);
        App::setLocale($value);
        $this->language = $value; // Update the language property
        $this->dispatch('language-updated', ['language' => $value]); // Notify frontend
    }
   
    public function mount()
    {
        //$this->getImages();
        if (auth()->user()->hasRole('warehouse')) {

            $partyImages = session('checkout_images.party', []);
            $this->productImage=$partyImages[0]['product_image_path']??'';
            $this->userImage=$partyImages[0]['user_image_path']??'';
        }else{
            $cashierImages = session('checkout_images.cashier', []);
            $this->productImage=$cashierImages[0]['product_image_path']??'';
            $this->userImage=$cashierImages[0]['user_image_path']??'';
        }
        $this->language = Session::get('locale') ?? config('app.locale');

        $this->branch_name = (!empty(auth()->user()->userinfo->branch->name)) ? auth()->user()->userinfo->branch->name : "";

        $today = Carbon::today();
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";

       
        $this->shift =$currentShift= UserShift::with('cashBreakdown')->with('branch')->whereDate('start_time', $today)->where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->where(['status' =>"pending"])->first();
        //
        if (empty($currentShift)) {
            $this->dispatch('openModal');
        }
        //
        $currentShift = UserShift::whereDate('start_time', $today)->where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->where(['status' =>"pending"])->first();

        $this->shiftEndTime = $this->shift->end_time ?? 0;

        $start_date = @$currentShift->start_time; // your start date (set manually)
        $end_date = date('Y-m-d') . ' 23:59:59'; // today's date till end of day
        $this->categoryTotals = [];
        $this->totalInvoicedAmount = \App\Models\Invoice::where('user_id', auth()->user()->id)
        ->where('branch_id', $branch_id)
        ->whereBetween('created_at', [$start_date, $end_date])
        ->sum('total');
      
        $start_date = @$currentShift->start_time; // your start date (set manually)
        $end_date = date('Y-m-d') . ' 23:59:59'; // today's date till end of day
        $noteCount = [];
    
       //print_r($noteCount);exit;
        // Decode cash JSON to array
        $cashBreakdowns = CashBreakdown::where('user_id', auth()->id())
            ->where('branch_id', $branch_id)
           // ->where('type', '!=', 'cashinhand')
            ->whereBetween('created_at', [$start_date, $end_date])
            ->get();

        $noteCount = [];
        
        foreach ($cashBreakdowns as $breakdown) {
            $denominations1 = json_decode($breakdown->denominations, true);
            // echo "<pre>";
            // print_r($denominations1);
            if (is_array($denominations1)) {
                foreach ($denominations1 as $denomination => $notes) {
                    foreach ($notes as $noteValue => $action) {
                        // Check for 'in' (added notes) and 'out' (removed notes)
                        if (isset($action['in'])) {
                            if (!isset($noteCount[$noteValue])) {
                                $noteCount[$noteValue] = 0;
                            }
                            $noteCount[$noteValue] += $action['in'];
                        }
                        if (isset($action['out'])) {
                            if (!isset($noteCount[$noteValue])) {
                                $noteCount[$noteValue] = 0;
                            }
                            $noteCount[$noteValue] -= $action['out'];
                        }
                    }
                }
                
            }
        }
        $this->shiftcash = $noteCount;
        $this->availableNotes = json_encode($this->shiftcash);
        //$this->checkTime();

        // return view('shift_closing.show', compact('shift'));
        //$this->loadCartData();
        $this->commissionUsers = Commissionuser::where('status', 'Active')->where('is_deleted','!=' ,'Yes')->get(); // Assuming you have a model for this
        $this->partyUsers = Partyuser::where('status', 'Active')->where('is_delete','!=' ,'Yes')->get(); // Assuming you have a model for this
        foreach ($this->cartitems as $item) {
            $this->quantities[$item->id] = $item->quantity;
        }

        $this->holdTransactions =  Invoice::where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->where('status', 'Hold')->whereBetween('created_at', [$start_date, $end_date])->get();

        if (empty($this->selectedPartyUser)) {
            $mycarts = Cart::where('user_id', auth()->user()->id)->where('status', Cart::STATUS_PENDING)->get();
            $sum=0;
            foreach ($mycarts as $key => $mycart) {
               $mycart->discount=0;
               $mycart->net_amount=$mycart->amount*$mycart->quantity;
               $mycart->save();
               $sum=$sum+$mycart->net_amount;
            }
            $this->dispatch('updateNewProductDetails');

            //$this->cashAmount=$sum;
            //$this->basicPartyAmt=$user->credit_points*$mycart->quantity;
            // $this->partyAmount = $user->credit_points;
        }

        $this->products = Product::all();

        // $date = Carbon::yesterday();
      
        $this->productStock = DailyProductStock::with('product')
                        ->where('branch_id', $branch_id)
                        ->whereDate('date', Carbon::yesterday())
                        ->get();

        foreach ($this->noteDenominations as $index => $denomination) {
            $this->cashNotes[$index][$denomination] = ['in' => 0, 'out' => 0];
        }
       
        if (session()->has('notification-sucess')) {
            $this->dispatch('notiffication-sucess', [
                'message' => session('notification-sucess')
            ]);
        }
        if (session()->has('notification-error')) {
            $this->dispatch('notiffication-error', [
                'message' => session('notification-error')
            ]);
        }
        

    }
    
    // public function loadHoldTransactions()
    // {
    //     $this->holdTransactions = Cart::where('user_id', auth()->user()->id)->where('status', Cart::STATUS_HOLD)->get();
    // }

    // public function resumeTransaction($id)
    // {
    //     $transaction = Cart::where('user_id', auth()->user()->id)->where('status', Cart::STATUS_HOLD)->first();
    //     $transaction->status =Cart::STATUS_PENDING;
    //     $transaction->save();

    //     $this->loadHoldTransactions(); // refresh list
    //     session()->flash('message', 'Transaction resumed!');
    // }
    
    public function checkTime()
    {
        // Get the current time in IST using PHP's DateTime
        $now = new \DateTime('now', new \DateTimeZone('Asia/Kolkata'));
    
        // Log current time in IST
        Log::info('Current time (IST): ' . $now->format('Y-m-d H:i:s'));
    
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
    
        // Example: get the active shift for the current user or by any logic
        $shift = UserShift::where('user_id', auth()->id())
                        ->where('branch_id', $branch_id)
                        ->whereDate('end_time', today())
                        ->latest()
                        ->first();
    
        // Log the shift data retrieved
        if ($shift) {
            Log::info('Shift found for user: ' . auth()->id() . ' - End Time: ' . $shift->end_time);
        } else {
            Log::info('No active shift found for user: ' . auth()->id());
        }
    
        if ($shift && $shift->end_time) {
            // Parse the DB end time and convert it to IST using PHP's DateTime
            $endTime = new \DateTime($shift->end_time, new \DateTimeZone('Asia/Kolkata'));
    
            // Log the parsed end time in IST
            Log::info('Shift end time (IST): ' . $endTime->format('Y-m-d H:i:s'));
    
            // Compare the current time with the shift end time (subtract 10 minutes)
            $tenMinutesBeforeEnd = clone $endTime;
            $tenMinutesBeforeEnd->modify('-10 minutes');
    
            // Show button if within 10 minutes of the shift end time
            $this->showCloseButton = ($now >= $tenMinutesBeforeEnd);
    
            // Log the result of the comparison
            Log::info('Show close button: ' . ($this->showCloseButton ? 'Yes' : 'No'));
        } else {
            $this->showCloseButton = false;
            Log::info('No valid shift end time found.');
        }
        $this->showCloseButton = true;

    }
    

    public function clearCashNotes()
    {
        foreach ($this->cashNotes as $key => $denominations) {
            foreach ($denominations as $denomination => $values) {
                $this->cashNotes[$key][$denomination]['in'] = 0;
                $this->cashNotes[$key][$denomination]['out'] = 0;
            }
        }
    }

    public function clearCashUpiNotes()
    {
        foreach ($this->cashupiNotes as $key => $denominations) {
            foreach ($denominations as $denomination => $values) {
                $this->cashupiNotes[$key][$denomination]['in'] = 0;
                $this->cashupiNotes[$key][$denomination]['out'] = 0;
            }
        }
    }

    public function voidSale()
    {
        $cartItems = Cart::where('user_id', auth()->user()->id)
            ->where('status', '!=', Cart::STATUS_HOLD);
    
        if ($cartItems->count() === 0) {
            // No cart items to clear
            $this->dispatch('notiffication-error', ['message' => 'No cart data to void.']);
            return;
        }
    
        // Clear the cart
        $cartItems->delete();
    
        // Reset search-related properties
        $this->reset('searchTerm', 'searchResults', 'showSuggestions','cashAmount','shoeCashUpi','showBox','cashNotes','quantities','cartCount');
    
        // Dispatch browser event or Livewire event
        $this->dispatch('notiffication-sucess', ['message' => 'Your transaction has been cleared.']);
    
        // Success message
        // session()->flash('message', 'Cart has been cleared.');
    }
    
    public function holdSale()
    {
        $cartItems = Cart::where('user_id', auth()->user()->id)
            ->where('status', Cart::STATUS_PENDING)
            ->get(); // <-- get all matching rows
    
        if ($cartItems->isNotEmpty()) {
            foreach ($cartItems as $item) {
                $item->status = Cart::STATUS_HOLD;
                $item->save();
            }
            $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
            $invoice_number = 'Hold-' . strtoupper(Str::random(8));
            $commissionUser = CommissionUser::where('status', 'Active')->where('is_deleted','!=' ,'Yes')->find($this->selectedCommissionUser);
            $partyUser = PartyUser::where('status', 'Active')->find($this->selectedPartyUser);
            $invoice = Invoice::create([
                'user_id' => auth()->id(),
                'branch_id' => $branch_id,
                'invoice_number' => $invoice_number,
                'commission_user_id' => $commissionUser->id ?? null,
                'party_user_id' => $partyUser->id ?? null,
                'items' => $cartItems->map(fn($item) => [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'category' => $item->product->category->name,
                    'price' => $item->product->sell_price,
                ]),
                'sub_total' => $this->cashAmount,
                'tax' => $this->tax,
                'status' => "Hold",
                'commission_amount' => $this->commissionAmount,
                'party_amount' => $this->partyAmount,
                'total' => $this->cashAmount,
                //'billing_address'=> $address,
            ]);
            $cartItems = Cart::where('user_id', auth()->user()->id)->where('status', Cart::STATUS_HOLD)->delete(); // <-- get all matching rows

            // Optional: reset UI inputs
            //$this->dispatch('updateCartCount');
            $this->dispatch('updateNewProductDetails');
            $this->reset('searchTerm', 'searchResults', 'showSuggestions','cashAmount','shoeCashUpi','showBox','cashNotes','quantities','cartCount');
            
            $this->dispatch('notiffication-sucess', ['message' => 'Your transaction has been added to hold.']);

            // Optional: flash message or dispatch event
            //session()->flash('message', 'Your transaction has been added to hold.');
        }
    }
    

    public function showHoldList()
    {
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        $holdTransactions= Invoice::where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->latest()->get();
        //$holdTransactions = Cart::where('user_id', auth()->user()->id)->where('status', 'hold')->get();
        return view('transactions.hold_list', compact('holdTransactions'));
    }

    public function loadCartData()
    {

        $this->branch_name = (!empty(auth()->user()->userinfo->branch->name)) ? auth()->user()->userinfo->branch->name : "";
        $this->cartitems = $this->products=Cart::with('product')
            ->where(['user_id' => auth()->user()->id])
            //  ->where(['branch_id'=>$branch_id])
            ->where('status', Cart::STATUS_PENDING)
            ->get();

        $this->calculateTotals();
       // $this->getCartItemCount();
        // $this->products = Cart::with('product')
        //     ->where(['user_id' => auth()->user()->id])
        //     ->where('status', Cart::STATUS_PENDING)
        //     ->get();
    }

    public function updateQty($itemId)
    {

        $getSignlecart = Cart::where(['user_id' => auth()->user()->id])->find($itemId);
        
        //$currentQty=$this->cartCount+1;
        if(!empty($this->quantities[$itemId])){
            $currentQty=$this->quantities[$itemId] +1;

        }else{
            $currentQty=1;
        }

        // Fetch product with inventory
        
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";

        $product = Product::select('products.*', 'inventory_summary.total_quantity')
            ->leftJoin(DB::raw('(
                SELECT product_id, SUM(quantity) as total_quantity
                FROM inventories where store_id = '.$branch_id.'
                GROUP BY product_id
            ) as inventory_summary'), 'products.id', '=', 'inventory_summary.product_id')
            ->where('products.id', $getSignlecart->product_id)
            ->first();
        
        // Fetch product with inventory
        if ( $currentQty > $product['total_quantity']) {
            $this->dispatch('notiffication-error', ['message' => 'Product is out of stock and cannot be added to cart.']);
            return;
        }
        $quantity = (isset($this->quantities[$itemId])) ? (int) $this->quantities[$itemId] : 0;
        if (!empty($this->selectedPartyUser)) {
            $this->getDiscountPrice($getSignlecart->product_id,$this->selectedPartyUser);
            //$this->cashAmount = $this->cartitems->sum('net_amount')-$user->credit_points;
            $this->partyAmount =$quantity* $this->partyUserDiscountAmt;
        }
        if ($quantity < 1) {
            $quantity = 1;
            $this->quantities[$itemId] = 1;
        }else{
            
            // $item1 = Cart::where(['user_id' => auth()->user()->id])->find($itemId);
            // if ($item1) {
            //     $item=new Cart();
            //     $item->quantity = $item1->quantity + 1;
            //     $item->user_id = auth()->user()->id;
            //     $item->product_id = $item1->product_id;
            //     $item->save();
            //     $this->quantities[$itemId] = $item1->quantity + 1;

            // }
            $item = Cart::with(['product'])->where('id', $itemId)
            ->where('user_id', auth()->id())
            ->where('status', Cart::STATUS_PENDING)
            ->first();
            
            if ($item) {
                if (isset($this->quantities[$itemId])) {
                    $curtDiscount=$item->discount/$item->quantity;
                     $item->quantity = $this->quantities[$itemId];
                    $item->discount=$curtDiscount*($this->quantities[$itemId]);
                    $item->net_amount=($item->mrp*$this->quantities[$itemId])-$item->discount;
                    // $item->quantity = $this->quantities[$itemId];
                    // $item->net_amount=($item->mrp-$item->discount)*$this->quantities[$itemId];
                    $item->save();
                }

                $this->dispatch('updateNewProductDetails');
            }
        }

    //    / this->basicPartyAmt
        // Optional: refresh cart items if needed
        // $this->cartitems = Cart::with('product')
        //     ->where(['user_id' => auth()->user()->id])
        //     ->where(['product_id' => auth()->user()->id])
        //     ->where('status', Cart::STATUS_PENDING)
        //     ->get();

       // $this->dispatch('updateCartCount');
        //$this->dispatch('updateProductList');
    }
    public function updateNewProductDetails(){
        $this->cartitems= $this->products=Cart::with('product')
        ->where(['user_id' => auth()->user()->id])
        ->where('status', Cart::STATUS_PENDING)
        ->get();
        $user = Partyuser::where('status', 'Active')->find($this->selectedPartyUser);
        if (!empty($user)) {
            //$this->cashAmount = $this->cartitems->sum('net_amount')-$user->credit_points;
            $this->cashAmount = $this->cartitems->sum('net_amount');
        }else{

            $this->cashAmount = $this->cartitems->sum('net_amount');
        }
        if (!empty($this->creditPay)) {
            //$this->cashAmount = $this->cartitems->sum('net_amount')-$user->credit_points;
            $this->cashAmount = $this->cashAmount-$this->creditPay;
        }

        $this->calculateTotals();

        $this->getCartItemCount();

    }
    public function calculateTotals()
    {
        $this->sub_total = $this->cartitems->sum(
            fn($item) =>
            !empty($item->product->sell_price)
                ? $item->mrp *$item->quantity 
                : 0
        );

        //$this->tax = $this->sub_total * 0.18;
        //$this->cashAmount = $this->total;
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
            ->where('status', '!=', Cart::STATUS_HOLD)
            ->sum('quantity');

        $this->dispatch('updateCartCount');
    }
    //sanjay
    public function incrementQty($id,$amount=0)
    {
       
        $item = Cart::with(['product'])->where('id', $id)
            ->where('user_id', auth()->id())
            ->where('status', Cart::STATUS_PENDING)
            ->first();
            
        if ($item) {
            $curtDiscount=$item->discount/$item->quantity;
            $totalQuantity = collect(@$this->selectedSalesReturn->items)
                ->where('product_id', $item->product_id)
                ->sum('quantity');
            if (!empty($this->selectedSalesReturn) && $item->quantity>= $totalQuantity) {
                $this->dispatch('notiffication-error', [
                    'message' => 'Adding more items is not allowed in a refund transaction.'
                ]);
                return;
            }
            $item->quantity++;
            $item->discount=$curtDiscount*($item->quantity);
            $item->net_amount=($item->mrp*$item->quantity)-$item->discount;
            $item->save();
            if($this->selectedPartyUser){

                $this->partyAmount = $item->discount;
            }else{
                $this->commissionAmount = $item->discount;

            }          //  $this->cashAmount=$item->net_amount;
            if (isset($this->quantities[$id])) {
                $this->quantities[$id]++;
               // $this->updateQty($id);
            }
              //  $this->calculateParty();
            $this->dispatch('updateNewProductDetails');
            $this->finalDiscountParty();
            if($this->selectedCommissionUser){
                $this->commissionAmount = $this->finalDiscountPartyAmount;
            }else{
                $this->partyAmount = $this->finalDiscountPartyAmount;
            }  
            
           // $this->dispatch('updateProductList');
        }
    }
    public function incrementNote($key, $denomination, $type)
    {
        if (!isset($this->cashNotes[$key][$denomination][$type])) {
            $this->cashNotes[$key][$denomination][$type] = 0;
        }
        $this->cashNotes[$key][$denomination][$type]++;
    }

    public function decrementNote($key, $denomination, $type)
    {
        if ($this->cashNotes[$key][$denomination][$type] > 0) {
            $this->cashNotes[$key][$denomination][$type]--;
        }
    }

    public function getTotals()
    {
        $totalIn = $totalOut = $totalAmount =$totalInCount=$totalOutCount= 0;

        foreach ($this->noteDenominations as $key => $denomination) {
            $in = $this->cashNotes[$key][$denomination]['in'] ?? 0;
            $out = $this->cashNotes[$key][$denomination]['out'] ?? 0;

            $totalIn += $in * $denomination;
            $totalOut += $out * $denomination;
            $totalAmount += ($in - $out) * $denomination;
            $totalInCount += $in;
            $totalOutCount += $out;
        }

        $this->cashPaTenderyAmt = $totalIn;
        $this->cashPayChangeAmt = $this->cashAmount - $totalIn;

        return compact('totalIn', 'totalOut', 'totalAmount', 'totalInCount', 'totalOutCount');
    }
    public function incrementCashUpiNote($key, $denomination, $type)
    {
        $this->cashupiNotes[$key][$denomination][$type] = ($this->cashupiNotes[$key][$denomination][$type] ?? 0) + 1;
    }
    
    public function decrementCashUpiNote($key, $denomination, $type)
    {
        $current = $this->cashupiNotes[$key][$denomination][$type] ?? 0;
        if ($current > 0) {
            $this->cashupiNotes[$key][$denomination][$type] = $current - 1;
        }
    }
    
    public function decrementQty($id)
    {
        $item = Cart::with(['product'])->where('id', $id)
        ->where('user_id', auth()->id())
        ->where('status', Cart::STATUS_PENDING)
        ->first();
        if ($item && $item->quantity > 1) {
            $curtDiscount=$item->discount/$item->quantity;
            $item->quantity--;
            $item->discount=$curtDiscount*$item->quantity;
            $item->net_amount=($item->mrp-$curtDiscount)*$item->quantity;
            $item->save();

            if($this->selectedPartyUser){

                $this->partyAmount = $item->discount;
            }else{
                $this->commissionAmount = $item->discount;

            }

            //$this->cashAmount=$item->net_amount;
            if (isset($this->quantities[$id]) && $this->quantities[$id] > 1) {
                $this->quantities[$id]--;
              //  $this->updateQty($id);
            }
            //$this->loadCartData();
            $this->dispatch('updateNewProductDetails');
            // $this->dispatch('updateProductList');
            // $this->calculateParty();

        }
    }

    public function removeItem($id)
    {
       // dd($this);
        $userId = auth()->id(); // Get the currently authenticated user's ID

        // Count only this user's cart items
        $cartItem = Cart::where('user_id', $userId)->count();
        
        if ($cartItem === 0) {
            return;
        }

        $newParty = $this->partyAmount / $cartItem;

        // Find and delete the cart item belonging to the user
        Cart::where('id', $id)->where('user_id', $userId)->first()?->delete();
        // Recalculate cart item count after deletion
        $cartItem = Cart::where('user_id', $userId)->count();
        if ($cartItem == 0) {
            $this->reset('selectedPartyUser', 'selectedCommissionUser');
        }
       //  $this->getDiscountPrice($cartDetails->product_id,$this->selectedPartyUser);
        //dd($this->partyAmount,$this->partyUserDiscountAmt);
        $this->finalDiscountParty();
        $this->showBox = false;
        $this->dispatch('updateNewProductDetails');
        if($this->selectedCommissionUser){
            $this->commissionAmount = $this->finalDiscountPartyAmount;
        }else{
            $this->partyAmount = $this->finalDiscountPartyAmount;
        }
        
    //    $this->loadCartData();
    }

    public function finalDiscountParty(){
        $partyAmtTotal=0;
        $userId = auth()->id(); // Get the currently authenticated user's ID
        $cartDetails = Cart::where('user_id', $userId)->get();
        foreach ($cartDetails as $key => $cartDetailNew) {
            $partyAmtTotal+=$cartDetailNew->discount;
        }
        $this->finalDiscountPartyAmount=$partyAmtTotal;
    }
    public function calculateCommission()
    {
        $this->dispatch('user-selection-updated', ['userId' => $this->selectedUser]);
        $sum=$commissionTotal=0;
        $user = Commissionuser::where('status', 'Active')->where('is_deleted','!=' ,'Yes')->find($this->selectedCommissionUser);
        if (!empty($user)) {
            // $getDiscountAmt = Cart::with(['product', 'product.inventorie'])
            //     ->where(['user_id' => auth()->user()->id])
            //     ->where('status', '!=', Cart::STATUS_HOLD)
            //     ->get()
            //     ->sum(fn($cart) => $cart->product->discount_amt ?? 0);
            // $this->commissionAmount = $getDiscountAmt;
            $mycarts = Cart::with(['product', 'product.inventorie'])->where('user_id', auth()->user()->id)->where('status', Cart::STATUS_PENDING)->get();

            foreach ($mycarts as $key => $mycart) {
               $this->getDiscountPrice($mycart->product->id);
                
               $mycart->net_amount=($mycart->mrp *$mycart->quantity)-$this->partyUserDiscountAmt;
               $mycart->discount=$this->partyUserDiscountAmt*$mycart->quantity;
               $mycart->save();
               $sum=$sum+$mycart->net_amount;
               $commissionTotal=$commissionTotal+$mycart->discount;

            }
            $this->commissionAmount = @$commissionTotal;
          
        } else {
            $mycarts = Cart::with(['product', 'product.inventorie'])->where('user_id', auth()->user()->id)->where('status', Cart::STATUS_PENDING)->get();
            foreach ($mycarts as $key => $mycart) {
               $mycart->net_amount=@$mycart->mrp*$mycart->quantity;
               $mycart->discount=0;
               $mycart->save();
               $sum=$sum+$mycart->net_amount;

            }
            
            $this->basicPartyAmt=0;
            $this->commissionAmount = 0;
        }
       // $this->cashAmount=$sum;
       $this->dispatch('updateNewProductDetails');

    }

    public function calculateParty()
    {
        $sum=$partyCredit=0;
        $user = Partyuser::where('status', 'Active')->find($this->selectedPartyUser);
        if (!empty($user)) {
            $mycarts = Cart::with(['product', 'product.inventorie'])->where('user_id', auth()->user()->id)->where('status', Cart::STATUS_PENDING)->get();
            foreach ($mycarts as $key => $mycart) {
                $this->getDiscountPrice($mycart->product->id,$this->selectedPartyUser);

                $curtDiscount=$this->partyUserDiscountAmt;
                $mycart->discount=$curtDiscount*($mycart->quantity);
                $mycart->net_amount=($mycart->mrp*$mycart->quantity)-$mycart->discount;
    

               //$mycart->net_amount=$mycart->net_amount-($discountAmt*$mycart->quantity);
                //\Log::info('Net Amount: ' . $mycart->net_amount . ' Discount: ' . $discountAmt. ' Quantity: ' . $mycart->quantity);
                
               //$mycart->discount=$discountAmt*$mycart->quantity;

               $mycart->save();
               $sum=$sum+$mycart->net_amount;
               $partyCredit=$partyCredit+$mycart->discount;

            }
            // $mycarts = Cart::where('user_id', auth()->user()->id)->where('status', Cart::STATUS_PENDING)->get();
            // foreach ($mycarts as $key => $mycart) {
            //    $mycart->net_amount=$mycart->net_amount-($user->credit_points*$mycart->quantity);
            //    $mycart->discount=$user->credit_points*$mycart->quantity;
            //    $mycart->save();
            //    $sum=$sum+$mycart->net_amount;
            // }
            
            //$this->basicPartyAmt=$user->credit_points*$mycart->quantity;
            $this->partyAmount = $partyCredit;
        } else {
            $mycarts = Cart::where('user_id', auth()->user()->id)->where('status', Cart::STATUS_PENDING)->get();
            foreach ($mycarts as $key => $mycart) {
               $mycart->net_amount=$mycart->mrp*$mycart->quantity;
               $mycart->discount=0;
               $mycart->save();
               $sum=$sum+$mycart->net_amount;

            }
            
            $this->basicPartyAmt=0;
            $this->partyAmount = 0;
        }
        $this->dispatch('updateNewProductDetails');
        
       // $this->cashAmount=$sum;
        
    }

    public function render()
    {
        
        if (strlen($this->searchSalesReturn) > 0) {
            $this->searchSalesResults = Invoice::when($this->searchSalesReturn, function ($query) {
                    $query->where('invoice_number', $this->searchSalesReturn);
                })
                ->where('user_id', auth()->id())
                ->where('branch_id', auth()->user()->userinfo->branch->id ?? null)
                ->get();
            //$this->showSuggestionsSales = true;
        } else {
          //  $this->searchSalesResults = [];
        }

        if (strlen($this->searchTerm) > 0) {
            $this->searchResults = Product::with('inventorie')
                ->when($this->searchTerm, function ($query) {
                    $query->where('name', 'like', '%' . $this->searchTerm . '%');
                })
                ->get();
            $this->showSuggestions = true;
        } else {
            $this->searchResults = [];
        }
        $itemCarts = Cart::GetCartItems();
        foreach ($itemCarts as $item) {
            $this->quantities[$item->id] = $item->quantity;
        }
        $stores = Branch::all();
        $products = Product::all();
        $data = User::with('userInfo')
        ->where('users.id', auth()->id())
        ->where('is_deleted', 'no')
        ->firstOrFail();

        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        $getNotification=getNotificationsByNotifyTo(auth()->id(),$branch_id,10);
        $totals = $this->getTotals();

    //    dd($getNotification);
        return view('livewire.shoppingcart', [
            'itemCarts' => $itemCarts,
            'narrations' => $this->narrations,
            'searchResults' => $this->searchTerm,
            'stores'=>$stores,
            'products'=>$products,
            'allProducts'=>$products,
            'data'=>$data,
            'searchSalesResults' => $this->searchSalesResults,
            'getNotification'=>$getNotification,
            'totals' => $totals,

        ]);
    }

    public function addToCart($id)
    {
        
        if (auth()->user()) {
            $currentQty=$this->cartCount+1;
            $totalQuantity = $this->selectedSalesReturn ? collect($this->selectedSalesReturn->items)->sum('quantity') : 0;
            if (!empty($this->selectedSalesReturn) && $this->cartCount>= $totalQuantity) {
                $this->dispatch('notiffication-error', [
                    'message' => 'Adding more items is not allowed in a refund transaction.'
                ]);
                return;
            }
            // Fetch product with inventory
         
            $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";

            $product = Product::select('products.*', 'inventory_summary.total_quantity')
                ->leftJoin(DB::raw('(
                    SELECT product_id, SUM(quantity) as total_quantity
                    FROM inventories where store_id = '.$branch_id.'
                    GROUP BY product_id
                ) as inventory_summary'), 'products.id', '=', 'inventory_summary.product_id')
                ->where('products.id', $id)
                ->first();

            
            if ( $currentQty > $product['total_quantity']) {
                $this->dispatch('notiffication-error', ['message' => 'Product is out of stock and cannot be added to cart.']);
                return;
            }

            // $item = Cart::where('product_id', $id)
            // ->where('user_id', auth()->id())
            // ->where('status', Cart::STATUS_PENDING)
            // ->first();
            //  if (!empty($item)) {
            //     $item->quantity = $item->quantity + 1;
            //     $item->save();
            // }else{
            //     $item=new Cart();
            //     $item->user_id = auth()->user()->id;
            //     $item->product_id = $id;
            //     $item->save();

            // }
            //

            if($this->selectedCommissionUser){
                $commissionUser = CommissionUser::where('status', 'Active')->where('is_deleted','!=' ,'Yes')->find($this->selectedCommissionUser);
                if(!empty($commissionUser)){
                     $this->getDiscountPrice($id);
                    $myCart=$this->partyUserDiscountAmt;
                    $this->commissionAmount=$myCart;
                }else{
                    $myCart=0;
                    $this->commissionAmount=$myCart;
                }

            }else{

                 $user = Partyuser::where('status', 'Active')->find($this->selectedPartyUser);
                if (!empty($user)) {
                   // $myCart=$user->credit_points;
                   //$myCart=$product->discount_amt;
                   //
                    $this->getDiscountPrice($id,$user->id);
                    //$this->cashAmount = $this->cartitems->sum('net_amount')-$user->credit_points;
                    $myCart =$this->partyUserDiscountAmt;
                   // $this->partyAmount=$myCart;   
                    // $partyCustomerProductsPrice = PartyCustomerProductsPrice::where('product_id', $id)
                    // ->where('party_user_id', $user->id)
                    // ->first();
                    // if ($partyCustomerProductsPrice) {
                    //     $myCart = $partyCustomerProductsPrice->cust_discount_amt;
                    //     // return $product->discount_amt;
                    // }else{
                    //      $myCart=0;
                    // }

                } else {
                    
                    $myCart=0;
                    //$this->partyAmount=$myCart;
    
                }
            }
            $item = Cart::where('product_id', $id)
            ->where('user_id', auth()->id())
            ->where('status', Cart::STATUS_PENDING)
            ->first();
             if (!empty($item)) {
                $this->incrementQty($item->id);
            }else{
                $item=new Cart();
                $item->user_id = auth()->user()->id;
                $item->product_id = $id;
                $item->mrp = $product->sell_price;
                $item->amount = $product->sell_price-$myCart;
                $item->discount = $myCart ?? 0;
                $item->net_amount = $product->sell_price-$myCart;
                $item->save();

            }
            $this->finalDiscountParty();
            if($this->selectedCommissionUser){
                $this->commissionAmount = $this->finalDiscountPartyAmount;
            }else{
                $this->partyAmount = $this->finalDiscountPartyAmount;
            }  
            
           // $this->updateQty($item->id);
            $this->dispatch('updateNewProductDetails');
            $this->reset('searchTerm', 'searchResults', 'showSuggestions');
            //$this->dispatch('notiffication-sucess', ['message' => 'Product added to the cart successfull.']);


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
    //             'price' => $item->product->sell_price,
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

    // public function checkout()
    // {
    //     $this->validate([
    //         'cashNotes' => 'required',

    //     ]);

    //     if (!empty($this->commissionAmount)) {
    //         $this->total -= $this->commissionAmount;
    //     }
    //     if (!empty($this->partyAmount)) {
    //         $this->total -= $this->partyAmount;
    //     }

    //     $commissionUser = CommissionUser::find($this->selectedCommissionUser);
    //     $partyUser = PartyUser::find($this->selectedPartyUser);
    //     $cartitems = $this->cartitems;

    //     foreach ($cartitems as $key => $cartitem) {
    //         $product = $cartitem->product->inventorie;
    //         if ($product && $product->quantity>0) {
    //             $product->quantity -= $cartitem->quantity;
    //             $product->save();
    //         }
    //     }

    //     $cashNotes = json_encode($this->cashNotes) ?? [];

    //     $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
    //     // 💾 Save cash breakdown
    //     $cashBreakdown = \App\Models\CashBreakdown::create([
    //         'user_id' => auth()->id(),
    //         'branch_id' => $branch_id,
    //         'denominations' => $cashNotes,
    //         'total' => $this->total,
    //     ]);

    //     $invoice_number = 'INV-' . strtoupper(Str::random(8));
    //     if(!empty($commissionUser)){
    //         $address = $commissionUser->address ?? null;
    //     }else  if(!empty($partyUser)){
    //         $address = $partyUser->address ?? null;
    //     }
    //     if($this->paymentType=="cash"){
    //         $this->cash=$this->cashPaTenderyAmt;
    //         $this->upi=0;

    //     }

    //     $invoice = Invoice::create([
    //         'user_id' => auth()->id(),
    //         'branch_id' => $branch_id,
    //         'invoice_number' => $invoice_number,
    //         'commission_user_id' => $commissionUser->id ?? null,
    //         'party_user_id' => $partyUser->id ?? null,
    //         'items' => $cartitems->map(fn($item) => [
    //             'name' => $item->product->name,
    //             'quantity' => $item->quantity,
    //             'category'=> $item->product->category->name,
    //             'price' => $item->product->sell_price,
    //         ]),
    //         'upi_amount' => $this->upi,
    //         'cash_amount' => $this->cash,
    //         'sub_total' => $this->sub_total,
    //         'tax' => $this->tax,
    //         'status'=>"Paid",
    //         'commission_amount' => $this->commissionAmount,
    //         'party_amount' => $this->partyAmount,
    //         'total' => $this->total,
    //         'cash_break_id' => $cashBreakdown->id,
    //         //'billing_address'=> $address,
    //     ]);
    //     // ✅ Set invoice data for the view
    //     $this->invoiceData = $invoice;
    //     // ✅ Trigger print via browser event
    //     $this->dispatch('triggerPrint');
    //     //return redirect()->route('invoice.show', $invoice->id);
    //     Cart::where('user_id', auth()->user()->id)
    //         ->where('status', '!=', Cart::STATUS_HOLD)
    //         ->delete();
    //     $this->reset('searchTerm', 'searchResults', 'showSuggestions');

    // }
    public function checkout()
    {
        try {

            if ($this->paymentType == "cash") {

                $this->validate([
                    'cashNotes' => 'required',
                ]);
            } else {
                $this->validate([
                    'cashupiNotes' => 'required',
                ]);
            }


            // if (!empty($this->commissionAmount)) {
            //     $this->total -= $this->commissionAmount;


            // }
            // if (!empty($this->partyAmount)) {
            //     $this->total -= $this->partyAmount;


            // }
            $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";

            $commissionUser = CommissionUser::where('status', 'Active')->where('is_deleted','!=' ,'Yes')->find($this->selectedCommissionUser);
            $partyUser = PartyUser::where('status', 'Active')->find($this->selectedPartyUser);
            if(!empty($partyUser)){
                $partyUser->credit_points -= $this->creditPay;
                $partyUser->save();
            }

            $cartitems = $this->cartitems;

            // $productQtyp = 0;
            // foreach ($cartitems as $key => $cartitem) {
            //     $inventories = $cartitem->product->inventories;

            //     foreach ($inventories as $inventory) {
            //         if ($cartitem->quantity > 0 && $inventory->quantity > 0) {
            //             $deductQty = min($cartitem->quantity, $inventory->quantity);
            //             $inventory->quantity -= $deductQty;
            //             $inventory->save();
            //         }

            //         if ($cartitem->quantity <= 0) {
            //             break;
            //         }
            //     }
            // }

            // Group by product ID and sum total quantity
            $groupedProducts = [];

            foreach ($this->cartitems as $cartitem) {

                $productId = $cartitem->product_id;
                if (!isset($groupedProducts[$productId])) {
                    $groupedProducts[$productId] = 0;
                }
                $groupedProducts[$productId] += $cartitem->quantity;
            }
            // Loop through each product group and deduct from inventories
            foreach ($groupedProducts as $productId => $totalQuantity) {
                $product = $this->cartitems->firstWhere('product_id', $productId)->product;
                $inventories = $product->inventories;
                $inventory =$product->inventorie;
                
                if ($inventory && $inventory->quantity < $inventory->product->reorder_level) {
                    // You can use your custom function like sendNotification, or better use Laravel Notification system

                    // Example with your function:
                    $arr['id'] = $inventory->product->id;
                    sendNotification('low_stock', 'Store stock request', null, auth()->id(),json_encode($arr));

                }

                stockStatusChange($inventory->product->id,$branch_id,$totalQuantity,'sold_stock');

                if (isset($inventories[0]) && $inventories[0]->quantity >= $totalQuantity) {
                    // Deduct only from the first inventory if it has enough quantity
                    $inventories[0]->quantity -= $totalQuantity;
                    $inventories[0]->save();
                } else {
                    // Deduct from all inventories if the first one doesn't have enough
                    foreach ($inventories as $inventory) {
                        if ($totalQuantity <= 0) {
                            break;
                        }

                        if ($inventory->quantity > 0) {
                            $deductQty = min($totalQuantity, $inventory->quantity);
                            $inventory->quantity -= $deductQty;
                            $inventory->save();
                            $totalQuantity -= $deductQty;
                        }
                    }
                }
            }
            if ($this->paymentType == "cash") {

                $cashNotes = json_encode($this->cashNotes) ?? [];
            } else {
                $cashNotes = json_encode($this->cashupiNotes) ?? [];
            }
            if ($this->paymentType == "cash") {
                $this->cash = $this->cashAmount;
                $this->upi = 0;
            }

            // 💾 Save cash breakdown
            $cashBreakdownCash = ($this->paymentType == "cash") ? $this->cashAmount : $this->cash;
            $cashBreakdown = \App\Models\CashBreakdown::create([
                'user_id' => auth()->id(),
                'branch_id' => $branch_id,
                'denominations' => $cashNotes,
                'total' => $cashBreakdownCash,
            ]);
            
            $totalQuantity = $cartitems->sum(fn($item) => $item->quantity);
            $total_item_total = $cartitems->sum(fn($item) => $item->net_amount);
            $invoice_number =Invoice::generateInvoiceNumber();


            $invoice = Invoice::create([
                'user_id' => auth()->id(),
                'branch_id' => $branch_id,
                'invoice_number' => $invoice_number,
                'commission_user_id' => $commissionUser->id ?? null,
                'party_user_id' => $partyUser->id ?? null,
                'payment_mode' => $this->paymentType,
                'items' => $cartitems->map(fn($item) => [
                    'product_id' => $item->product->id,
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'category' => $item->product->category->name,
                    'subcategory' => $item->product->subcategory->name,
                    'price' => $item->net_amount,
                    'mrp' => $item->mrp,

                ]),
                'total_item_qty' => $totalQuantity,
                'total_item_total' => $total_item_total,
                'upi_amount' => $this->upi,
                'change_amount' => $this->cashPayChangeAmt,
                'creditpay' => $this->creditPay,
                'cash_amount' => $this->cash,
               // 'sub_total' => $this->cashAmount,
                'sub_total' => $this->sub_total,

                'tax' => $this->tax,
                'status' => "Paid",
                'commission_amount' => $this->commissionAmount,
                'party_amount' => $this->partyAmount,
                'total' => $this->cashAmount,
                'cash_break_id' => $cashBreakdown->id,
                //'billing_address'=> $address,
            ]);
            InvoiceHistory::logFromInvoice($invoice, 'created', auth()->id());


                if ($this->selectedPartyUser) {
                    $warehouse_product_photo_path = session(auth()->id().'_warehouse_product_photo_path', []);
                    $warehouse_customer_photo_path = session(auth()->id().'_warehouse_customer_photo_path', []);
                    $userImgName = basename($warehouse_customer_photo_path);
                    $productImgName = basename($warehouse_product_photo_path);
                    // Define source and destination paths
                    //$sourcePath = 'uploaded_photos/' . $image['filename'];
                    $destinationProductPath = 'uploaded_photos/' . $invoice_number . '/' . $productImgName;
                    $destinationUserPath = 'uploaded_photos/' . $invoice_number . '/' . $userImgName;

                    if (Storage::disk('public')->exists($warehouse_product_photo_path)) {
                        Storage::disk('public')->move($warehouse_product_photo_path, $destinationUserPath);
                    }
                    if (Storage::disk('public')->exists($warehouse_customer_photo_path)) {
                        Storage::disk('public')->move($warehouse_customer_photo_path, $destinationProductPath);
                    }  
                    // Save the updated image path (in the order folder) to the database
                    PartyUserImage::create([
                        'party_user_id' => $invoice->party_user_id,
                        'type' => '',
                        'image_path' => $destinationUserPath, // new path
                        'image_name' => '',
                        'product_image_path' => $destinationProductPath, // assuming same
                        'transaction_id' => $invoice->id,
                    ]);
                    

                    // Optional: clear the session images
                    session()->forget(auth()->id().'_warehouse_product_photo_path', []);
                    session()->forget(auth()->id().'_warehouse_customer_photo_path', []);
                }
                else{

                $cashier_product_photo_path = session(auth()->id().'_cashier_product_photo_path', []);
                $cashier_customer_photo_path = session(auth()->id().'_cashier_customer_photo_path', []);
                $userImgName = basename($cashier_customer_photo_path);
                $productImgName = basename($cashier_product_photo_path);
                // Define source and destination paths
                //$sourcePath = 'uploaded_photos/' . $image['filename'];
                $destinationProductPath = 'uploaded_photos/' . $invoice_number . '/' . $productImgName;
                $destinationUserPath = 'uploaded_photos/' . $invoice_number . '/' . $userImgName;

                if (Storage::disk('public')->exists($cashier_customer_photo_path)) {
                    Storage::disk('public')->move($cashier_customer_photo_path, $destinationUserPath);
                }
                if (Storage::disk('public')->exists($cashier_product_photo_path)) {
                    Storage::disk('public')->move($cashier_product_photo_path, $destinationProductPath);
                }  
                CommissionUserImage::create([
                    'commission_user_id' => $invoice->commission_user_id,
                    'type' => '',
                    'image_path' => $userImgName,
                    'image_name' =>'',
                    'product_image_path' => $destinationProductPath,
                    'transaction_id' => $invoice->id,
                ]);
                session()->forget(auth()->id().'_cashier_product_photo_path', []);
                session()->forget(auth()->id().'_cashier_customer_photo_path', []);
            }
            // Retrieve session data


            // Clear session
            session()->forget('checkout_images');
            if (!empty($commissionUser->id)) {

                DiscountHistory::create([
                    'invoice_id' => $invoice->id,
                    'discount_amount' => $this->commissionAmount,
                    'total_amount' => $this->cashAmount,
                    'total_purchase_items' => $totalQuantity,
                    'commission_user_id' => $commissionUser->id ?? null,
                    'store_id' => $branch_id,
                    'created_by' => auth()->id(),
                ]);
            }
            if (!empty($partyUser->id)) {

                CreditHistory::create([
                    'invoice_id' => $invoice->id,
                    'credit_amount' => $this->creditPay,
                    'total_amount' => $this->cashAmount,
                    'total_purchase_items' => $totalQuantity,
                    'party_user_id' => $partyUser->id ?? null,
                    'store_id' => $branch_id,
                    'created_by' => auth()->id(),
                ]);
            }
            //dd($invoice);
            if (auth()->user()->hasRole('warehouse')) {
                $this->invoiceData = $invoice;
               // $this->dispatch('triggerPrint');
                 // Generate PDF and store it in local storage
                 $pdf = App::make('dompdf.wrapper');
                 $pdf->loadView('invoice', ['invoice' => $invoice,'items' => $invoice->items,'branch'=>auth()->user()->userinfo->branch, 'customer_name' => $partyUser->first_name.' '.$partyUser->last_name]);
                 $pdfPath = storage_path('app/public/invoices/' . $invoice->invoice_number . '.pdf');
                 $pdf->save($pdfPath);
                //  $this->dispatch('triggerPrint', [
                //     'pdfPath' => route('print.pdf', $invoice->invoice_number)
                // ]);
                
                 // Trigger print via browser event
                 $this->dispatch('triggerPrint', ['pdfPath' => asset('storage/invoices/' . $invoice->invoice_number . '.pdf')]);
            } else {
                $this->dispatch('order-saved');
            }
            Invoice::where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->where('status', 'Hold')->delete();

            //return redirect()->route('invoice.show', $invoice->id);
            Cart::where('user_id', auth()->user()->id)
                ->where('status', '!=', Cart::STATUS_HOLD)
                ->delete();
            $this->reset('searchTerm', 'searchResults', 'showSuggestions', 'cashAmount', 'shoeCashUpi', 'showBox', 'cashNotes', 'quantities', 'cartCount','selectedSalesReturn', 'selectedPartyUser', 'selectedCommissionUser', 'paymentType', 'creditPay', 'partyAmount', 'commissionAmount', 'sub_total', 'tax', 'totalBreakdown');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // 🔔 Flash message for Laravel Blade
            $this->dispatch('notiffication-error', ['message' => 'Something went wrong']);

            //  return redirect()->back()->with('success', 'Withdraw amount successful.');

        }
    }
    public function refund(){
       // try {
             // if ($this->paymentType == "cash") {

                //     $this->validate([
                //         'cashNotes' => 'required',
                //     ]);
                // } else {
                //     $this->validate([
                //         'cashupiNotes' => 'required',
                //     ]);
                // }

            
                $commissionUser = CommissionUser::where('status', 'Active')->where('is_deleted','!=' ,'Yes')->find($this->selectedCommissionUser);
                $partyUser = PartyUser::where('status', 'Active')->find($this->selectedPartyUser);
                if(!empty($partyUser)){
                    $partyUser->credit_points += $this->creditPay;
                    $partyUser->save();
                }
                $cartitems = $this->cartitems;
                // Group by product ID and sum total quantity
                $groupedProducts = [];

                foreach ($this->cartitems as $cartitem) {

                    $productId = $cartitem->product_id;
                    if (!isset($groupedProducts[$productId])) {
                        $groupedProducts[$productId] = 0;
                    }
                    $totalQuantity = collect($this->selectedSalesReturn->items)
                        ->where('product_id', $productId)
                        ->sum('quantity');
                    $groupedProducts[$productId] = $totalQuantity-$cartitem->quantity;
                }
                
                //Loop through each product group and deduct from inventories
                foreach ($groupedProducts as $productId => $totalQuantity) {
                    
                    $product = $this->cartitems->firstWhere('product_id', $productId)->product;
                    $inventories = $product->inventories;
                    
                    if (isset($inventories[0]) && $inventories[0]->quantity >= $totalQuantity) {
                        // Deduct only from the first inventory if it has enough quantity
                        $inventories[0]->quantity += $totalQuantity;
                        $inventories[0]->save();
                    }
                }
                $cashNotes = json_encode($this->cashNotes) ?? [];
                $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
                // 💾 Save cash breakdown 
                //full refund
                //dd($this);
                // if((int)$this->cashAmount === (int)$this->totalInvoicedAmount){
                //     $fullRefund=0;

                // }else{
                //     $fullRefund=$this->cashAmount;
                // }
                $cashBreakdownCash = ($this->paymentType == "cash") ? $this->cashAmount : $this->cash;
                $cashBreakdown = \App\Models\CashBreakdown::create([
                    'user_id' => auth()->id(),
                    'branch_id' => $branch_id,
                    'denominations' => $cashNotes,
                    'total' => $cashBreakdownCash,
                ]);

                $invoice_number =$this->selectedSalesReturn->invoice_number;
              
                if ($this->paymentType == "cash") {
                    $this->cash = $this->cashAmount;
                    $this->upi = 0;
                }
                $lastInvoice = Invoice::with('cashBreak')
                ->where('invoice_number', $invoice_number)
                ->where('user_id', auth()->id())
                ->where('branch_id', auth()->user()->userinfo->branch->id ?? null)
                ->first();
                $productDetails = $cartitems->map(function($item) {
                return [
                 'product_id' => $item->product->id,
                'name' => $item->product->name,
                'quantity' => $item->quantity,
                'category' => $item->product->category->name,
                'price' => $item->net_amount,
                'mrp' => $item->mrp,

                ];
                })->toArray();
                $invoiceItems = $lastInvoice->items;
                $filteredArray = [];
                $totalMrp=0;
                foreach ($invoiceItems as $invoiceItem) {
                    $productId = $invoiceItem['product_id'];
                    $totalMrp += $invoiceItem['mrp'];
                    $matchingCartItem = collect($productDetails)->firstWhere('product_id', $productId);

                    if (!$matchingCartItem) {
                        // No match, keep as is
                        $filteredArray[] = $invoiceItem;
                    } else {
                        // Match found - compare quantity
                        if ($invoiceItem['quantity'] > $matchingCartItem['quantity']) {
                            // Subtract quantities and prices proportionally
                            $remainingQty = $invoiceItem['quantity'] - $matchingCartItem['quantity'];
                            $unitPrice = $invoiceItem['price'] / $invoiceItem['quantity'];
                            $unitMrp = $invoiceItem['mrp'] / $invoiceItem['quantity'];

                            $filteredArray[] = [
                                'product_id' => $invoiceItem['product_id'],
                                'name' => $invoiceItem['name'],
                                'quantity' => $remainingQty,
                                'category' => $invoiceItem['category'],
                                'price' => round($unitPrice * $remainingQty, 2),
                                'mrp' => round($unitMrp * $remainingQty, 2),
                            ];
                        }
                        // If equal or less, item is removed (do not include)
                    }
                }

               // dd($filteredArray);
                // // Extract product_ids to be removed
                // $removeIds = array_column($productDetails, 'product_id');

                // // Filter main array
                // $filteredArray = array_filter($invoiceItems, function ($item) use ($removeIds) {
                // return !in_array($item['product_id'], $removeIds);
                // });

                // // Reindex array
                // $filteredArray = array_values($filteredArray);
                // dd($filteredArray);
                $totalQuantity=$total_item_total=0;
                foreach ($filteredArray as $key => $filteredItem) {
                    $totalQuantity += $filteredItem['quantity'];
                    $total_item_total += $filteredItem['price'];
                }
                $currentDis=parseCurrency($lastInvoice->party_amount) /$lastInvoice->total_item_qty;
                $newPartyAmt=$currentDis*$totalQuantity;
                $amountAfterRefund = (Integer) parseCurrency($lastInvoice->total) -(Integer) $this->cashAmount;
              //  $amountafterParty =(Integer) $lastInvoice->party_amount - $this->partyAmount;
              //  $totalQuantity = $filteredArray->sum(fn($item) => $item->quantity);
                //$total_item_total = $filteredArray->sum(fn($item) => $item->net_amount);
                $invoice = Invoice::updateOrCreate(
                    [
                        'user_id' => auth()->id(),
                        'branch_id' => $branch_id,
                        'invoice_number' => $invoice_number,
                    ],
                    [
                        'commission_user_id' => $commissionUser->id ?? null,
                        'party_user_id' => $partyUser->id ?? null,
                        'payment_mode' => $this->paymentType,
                        'items' => $filteredArray,
                       // 'upi_amount' => $fullRefund,
                       // 'creditpay' => $fullRefund,
                        'total_item_total' => $total_item_total,
                        'total_item_qty' => $totalQuantity,
                        'cash_amount' => $amountAfterRefund,

                      'sub_total' => $totalMrp*$totalQuantity,
                       // 'tax' => $fullRefund,
                        'status' => "Refunded",
                      //  'commission_amount' => $fullRefund,
                        'party_amount' => $newPartyAmt,
                        'total' => $amountAfterRefund,
                        'cash_break_id' => $cashBreakdown->id,
                        'change_amount' => 0,
                        //'billing_address'=> $address,
                    ]
                );
                InvoiceHistory::logFromInvoice($invoice, 'refunded', auth()->id());

                // if (!empty($commissionUser->id)) {
                //     DiscountHistory::updateOrCreate(
                //         [
                //             'invoice_id' => $invoice->id,
                //             'commission_user_id' => $commissionUser->id ?? null,
                //         ],
                //         [
                //             'discount_amount' => $this->commissionAmount,
                //             'total_amount' => $this->cashAmount,
                //             'total_purchase_items' => $totalQuantity,
                //             'store_id' => $branch_id,
                //             'created_by' => auth()->id(),
                //         ]
                //     );
                // }
                if (!empty($partyUser->id)) {
                    CreditHistory::create(
                      
                        [
                            'invoice_id' => $invoice->id,
                            'party_user_id' => $partyUser->id ?? null,
                            'credit_amount' => 0.00,
                            'debit_amount' => $this->creditPay,
                            'total_amount' => $this->cashAmount,
                            'total_purchase_items' => $this->cashAmount,
                            'store_id' => $branch_id,
                            'created_by' => auth()->id(),
                        ]
                    );
                
                }
                $refund_item_qty = $cartitems->sum(fn($item) => $item->quantity);
                $refund_item_amount = $cartitems->sum(fn($item) => $item->net_amount);
                 $total_mrp = $cartitems->sum(fn($item) => $item->mrp);
                $refundSub=$refund_item_amount/$refund_item_qty;
                $refundMain=($refundSub-$totalMrp)*$refund_item_qty;
                    $this->finalDiscountParty();
                    if($this->selectedCommissionUser){
                        $this->commissionAmount = $this->finalDiscountPartyAmount;
                    }else{
                        $this->partyAmount = $this->finalDiscountPartyAmount;
                    }                $refund=Refund::create([
                    'amount' => $this->cashAmount,
                    'description' => $this->refundDesc,
                    'invoice_id' => $invoice->id,
                    'total_item_qty'=>$refund_item_qty,
                    'total_item_price'=>$refund_item_amount,
                    'total_mrp'=>$total_mrp,
                    'party_amount'=>$this->partyAmount,
                    'items_refund'=> $cartitems->map(fn($item) => [
                    'product_id' => $item->product->id,
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'category' => $item->product->category->name,
                    'price' => $item->net_amount,
                    'mrp' => $item->mrp,
                    
                    ]),
                    'refund_credit_amount'=>$this->creditPay,
                    'store_id' => $branch_id,
                    'user_id' => auth()->id(), // auto-assign current user
                ]);
        
                

                Invoice::where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->where('status', 'Hold')->delete();

                //return redirect()->route('invoice.show', $invoice->id);
                Cart::where('user_id', auth()->user()->id)
                    ->where('status', '!=', Cart::STATUS_HOLD)
                    ->delete();
                 $this->reset('searchTerm', 'searchResults', 'showSuggestions', 'cashAmount', 'shoeCashUpi', 'showBox', 'cashNotes', 'quantities', 'cartCount','selectedSalesReturn', 'selectedPartyUser', 'selectedCommissionUser', 'paymentType', 'creditPay', 'partyAmount', 'commissionAmount', 'sub_total', 'tax', 'totalBreakdown','cartitems','searchSalesReturn');
                $this->invoiceData = $invoice;
                
                $pdf = App::make('dompdf.wrapper');
                $pdf->loadView('refund', ['invoice' => $invoice,'items' => $refund->items_refund,'branch'=>auth()->user()->userinfo->branch,'type'=>'refund','refund'=>$refund,'customer_name' => $partyUser->first_name.' '.$partyUser->last_name]);
                $pdfPath = storage_path('app/public/invoices/refund_' . $invoice->invoice_number . '.pdf');
                $pdf->save($pdfPath);
            //     $this->dispatch('triggerPrint', [
            //        'pdfPath' => route('print.pdf', $invoice->invoice_number)
            //    ]);
                $this->dispatch('triggerPrint', ['pdfPath' => asset('storage/invoices/refund_' . $invoice->invoice_number . '.pdf')]);

        // } catch (\Throwable $th) {
        //     $this->dispatch('notiffication-error', ['message' => 'Something went wrong']);

        // }
        
    }

    public function hideSuggestions()
    {
        $this->showSuggestions = false;
    }
    public function resetData(){
        $this->reset();
    }
   
    public function onlinePaymentCheckout()
    {
        try {
            $commissionUser = CommissionUser::where('status', 'Active')->where('is_deleted','!=' ,'Yes')->find($this->selectedCommissionUser);
            $partyUser = PartyUser::where('status', 'Active')->find($this->selectedPartyUser);
            if(!empty($partyUser)){
                $partyUser->credit_points -= $this->creditPay;
                $partyUser->save();
            }

            $cartitems = $this->cartitems;

            // Group by product ID and sum total quantity
            $groupedProducts = [];

            foreach ($this->cartitems as $cartitem) {

                $productId = $cartitem->product_id;
                if (!isset($groupedProducts[$productId])) {
                    $groupedProducts[$productId] = 0;
                }
                $groupedProducts[$productId] += $cartitem->quantity;
            }
            // Loop through each product group and deduct from inventories
            foreach ($groupedProducts as $productId => $totalQuantity) {
                $product = $this->cartitems->firstWhere('product_id', $productId)->product;
                $inventories = $product->inventories;

                if (isset($inventories[0]) && $inventories[0]->quantity >= $totalQuantity) {
                    // Deduct only from the first inventory if it has enough quantity
                    $inventories[0]->quantity -= $totalQuantity;
                    $inventories[0]->save();
                } else {
                    // Deduct from all inventories if the first one doesn't have enough
                    foreach ($inventories as $inventory) {
                        if ($totalQuantity <= 0) {
                            break;
                        }

                        if ($inventory->quantity > 0) {
                            $deductQty = min($totalQuantity, $inventory->quantity);
                            $inventory->quantity -= $deductQty;
                            $inventory->save();
                            $totalQuantity -= $deductQty;
                        }
                    }
                }
            }
          
            $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
            // 💾 Save cash breakdown
            $totalQuantity = $cartitems->sum(fn($item) => $item->quantity);
            $total_item_total = $cartitems->sum(fn($item) => $item->net_amount);
            
            $invoice_number =Invoice::generateInvoiceNumber();
            $invoice = Invoice::create([
                'user_id' => auth()->id(),
                'branch_id' => $branch_id,
                'invoice_number' => $invoice_number,
                'commission_user_id' => $commissionUser->id ?? null,
                'party_user_id' => $partyUser->id ?? null,
                'payment_mode' => $this->paymentType,
                'items' => $cartitems->map(fn($item) => [
                    'product_id' => $item->product->id,
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'category' => $item->product->category->name,
                    'price' => $item->net_amount,
                    'mrp' => $item->mrp,

                ]),
                'total_item_qty' => $totalQuantity,
                'total_item_total' => $total_item_total,
                'upi_amount' => 0,
                'change_amount' => $this->cashAmount,
                'creditpay' => $this->creditPay,
                'cash_amount' => 0,
                'online_amount' => $this->cashAmount,
               // 'sub_total' => $this->cashAmount,
                'sub_total' => $this->cashAmount,
                'tax' => $this->tax,
                'status' => "Paid",
                'commission_amount' => $this->commissionAmount,
                'party_amount' => $this->partyAmount,
                'total' => $this->cashAmount,
                'cash_break_id' => null,
                //'billing_address'=> $address,
            ]);
            InvoiceHistory::logFromInvoice($invoice, 'created', auth()->id());

            if (!empty($commissionUser->id)) {

                DiscountHistory::create([
                    'invoice_id' => $invoice->id,
                    'discount_amount' => $this->commissionAmount,
                    'total_amount' => $this->cashAmount,
                    'total_purchase_items' => $totalQuantity,
                    'commission_user_id' => $commissionUser->id ?? null,
                    'store_id' => $branch_id,
                    'created_by' => auth()->id(),
                ]);
            }
            if (!empty($partyUser->id)) {

                CreditHistory::create([
                    'invoice_id' => $invoice->id,
                    'credit_amount' => $this->creditPay,
                    'total_amount' => $this->cashAmount,
                    'total_purchase_items' => $totalQuantity,
                    'party_user_id' => $partyUser->id ?? null,
                    'store_id' => $branch_id,
                    'created_by' => auth()->id(),
                ]);
            }
            //dd($invoice);
            if (auth()->user()->hasRole('warehouse')) {
                $this->invoiceData = $invoice;
               // $this->dispatch('triggerPrint');
                 // Generate PDF and store it in local storage
                 $pdf = App::make('dompdf.wrapper');
                 $pdf->loadView('invoice', ['invoice' => $invoice,'items' => $invoice->items,'branch'=>auth()->user()->userinfo->branch, 'customer_name' => $partyUser->first_name.' '.$partyUser->last_name]);
                 $pdfPath = storage_path('app/public/invoices/' . $invoice->invoice_number . '.pdf');
                 $pdf->save($pdfPath);
                //  $this->dispatch('triggerPrint', [
                //     'pdfPath' => route('print.pdf', $invoice->invoice_number)
                // ]);
                
                 // Trigger print via browser event
                 $this->dispatch('triggerPrint', ['pdfPath' => asset('storage/invoices/' . $invoice->invoice_number . '.pdf')]);
            } else {
                $this->dispatch('order-saved');
            }
            Invoice::where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->where('status', 'Hold')->delete();

            //return redirect()->route('invoice.show', $invoice->id);
            Cart::where('user_id', auth()->user()->id)
                ->where('status', '!=', Cart::STATUS_HOLD)
                ->delete();
            $this->reset('searchTerm', 'searchResults', 'showSuggestions', 'cashAmount', 'shoeCashUpi', 'showBox', 'cashNotes', 'quantities', 'cartCount','selectedSalesReturn', 'selectedPartyUser', 'selectedCommissionUser', 'paymentType', 'creditPay', 'partyAmount', 'commissionAmount', 'sub_total', 'tax', 'totalBreakdown');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // 🔔 Flash message for Laravel Blade
            $this->dispatch('notiffication-error', ['message' => 'Something went wrong']);

            //  return redirect()->back()->with('success', 'Withdraw amount successful.');

        }
    }
     public  function getDiscountPrice($product_id,$party_user_id="")
    {
         if($this->selectedCommissionUser){
            $product = Product::where('id', $product_id)
                ->first();
            if ($product) {
                $discount=$product->sell_price-$product->discount_price;
                $this->partyUserDiscountAmt=$this->commissionAmount = $discount;
            }
         }else{
            $partyCustomerProductsPrice = PartyCustomerProductsPrice::with('product')
            ->where('product_id', $product_id)
            ->where('party_user_id', $party_user_id)
            ->first();
            if ($partyCustomerProductsPrice) {
                $discount=$partyCustomerProductsPrice->product->sell_price-$partyCustomerProductsPrice->cust_discount_price;
                $this->partyAmount=$this->partyUserDiscountAmt = $discount;
               // return $product->discount_amt;
            }else{
                $this->partyAmount=$this->partyUserDiscountAmt=0;
            }
         }
         Log::info("this->partyUserDiscountAmt::::".$this->partyUserDiscountAmt);
       
    }
}