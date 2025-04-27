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

class Shoppingcart extends Component
{

    use WithPagination;
    public $cartItems = [];

    public $invoiceData;
    public $totalInvoicedAmount=0;
    public $cash = 0;
    public $upi = 0;
    public $updatingField = null;
    public $showCloseButton = false;

    public $shift;
    public $shiftcash;
   public  $narrations = [
        'Personal Expenses',
        'Business Investment',
        'Loan Repayment',
        'Medical Emergency',
        'Education Fees',
        'Travel Expenses',
        'Other'
    ];


    public $changeAmount = 0;
    public $showBox = false;
    public $shoeCashUpi = false;
    public $cashPayAmt;
    public $cashPaTenderyAmt;
    public $cashPayChangeAmt;
    public $categoryTotals = [];
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
    public $basicPartyAmt=0;
    public $productSearch = '';
    public $searchResults = [];
    public $products = [];
    public $tenderedAmount = 0;
    public $showModal = false;
    public $selectedUser = 0;
    protected $listeners = ['updateProductList' => 'loadCartData','loadHoldTransactions','updateNewProductDetails'];
    public $noteDenominations = [10, 20, 50, 100, 200,500];
    public $remainingAmount = 0;
    public $totalBreakdown = [];
    public $searchTerm = '';
    public $branch_name = '';
    public $quantities = [];
    public $showSuggestions = false;
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
    public $holdTransactions=[];
    public $headertitle="";
    public function updatedSearch($value)
    {
        $this->selectedProduct = Product::Where('barcode', 'like', "%{$value}%")
            ->first();
    }

    public function addToCartBarCode()
    {
        if (!$this->selectedProduct) return;

        $existingItemsum = Cart::where('product_id', $this->selectedProduct->id)
            ->where('user_id', auth()->id())
            ->where('status', Cart::STATUS_HOLD)
            ->sum('quantity');

            // Fetch product with inventory
            $product = \App\Models\Product::with('inventorie')->find($this->selectedProduct->id);
            //dd($product->inventorie->quantity);
            if (!$product || !$product->inventorie || $product->inventorie->quantity < $existingItemsum) {
            // session()->flash('error', 'Product is out of stock and cannot be added to cart.');
            $this->dispatch('notiffication-error', ['message' => 'Product is out of stock and cannot be added to cart']);

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
            $user = Partyuser::find($this->selectedPartyUser);
            if (!empty($user)) {
                $myCart=$user->credit_points;
            } else {
                $myCart=0;

            }

          
            $item=new Cart();
            $item->user_id = auth()->user()->id;
            $item->product_id = $this->selectedProduct->id;
            $item->mrp = $product->sell_price;
            $item->amount = $product->sell_price-$myCart;
            $item->discount = $myCart;
            $item->net_amount = $product->sell_price-$myCart;
            $item->save();
           // $this->updateQty($item->id);
            $this->dispatch('updateNewProductDetails');
            $this->reset('searchTerm', 'searchResults', 'showSuggestions'.'search');
          //  session()->flash('success', 'Product added to the cart successfully');
            $this->dispatch('notiffication-success', ['message' => 'Product added to the cart successfully']);
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
        if (!empty($this->products->toArray())) {
            $this->headertitle="Cash";
            $this->shoeCashUpi = false;
            $this->showBox = true;
            $this->paymentType = "cash";
            $this->total = $this->cashAmount;
            
        } else {
            session()->flash('error', 'add minimum one product');
            $this->dispatch('alert_remove');
        }
    }
    public function cashupitoggleBox()
    {
        if (!empty($this->products->toArray())) {
            $this->showBox = false;
            $this->shoeCashUpi = true;
            $this->paymentType = "cashupi";
            $this->headertitle="Cash + UPI";

            $this->total = $this->cashAmount;
            

        } else {
            session()->flash('error', 'add minimum one product');
            $this->dispatch('alert_remove');
        }
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
    public function mount()
    {
        $this->branch_name = (!empty(auth()->user()->userinfo->branch->name)) ? auth()->user()->userinfo->branch->name : "";

        $today = Carbon::today();
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";

        $UserShift = UserShift::whereDate('created_at', $today)->where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->exists();
        if (empty($UserShift)) {
             $this->showModal = true;
        }
        $this->shift = UserShift::with('cashBreakdown')->with('branch')->whereDate('created_at', $today)->where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->first();
        $this->shiftEndTime = $this->shift->end_time ?? 0;
        $invoices = Invoice::where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->latest()->get();
        $discountTotal = $totalSales = $totalPaid = $totalCashPaid = $totalUpiPaid = 0;
        $sales = ['Desi', 'BEER SALES', 'ENGLISH SALES'];
        $this->categoryTotals = [];
        $this->totalInvoicedAmount = \App\Models\Invoice::where('user_id', auth()->user()->id)
        ->where('branch_id', $branch_id)
        ->sum('total');
        // ✅ Initialize totals to 0 for all expected categories
        foreach ($sales as $category) {
            $this->categoryTotals['sales'][$category] = 0;
        }

        foreach ($invoices as $invoice) {
            $items = $invoice->items; // decode items from longtext JSON

            if (is_string($items)) {
                $items = json_decode($items, true); // decode if not already an array
            }

            if (is_array($items)) {
                foreach ($items as $item) {
                    $category = $item['category'] ?? 'Unknown';
                    $amount = $item['price'] ?? 0;

                    if (!isset($this->categoryTotals['sales'][$category])) {
                        $this->categoryTotals['sales'][$category] = 0;
                    }

                    $this->categoryTotals['sales'][$category] += $amount;
                }
            }

           // $discountTotal += ($invoice->commission_amount ?? 0) + ($invoice->party_amount ?? 0);
           $discountTotal += (!empty($invoice->commission_amount) && is_numeric($invoice->commission_amount)) ? (int)$invoice->commission_amount : 0;
           $discountTotal += (!empty($invoice->party_amount) && is_numeric($invoice->party_amount)) ? (int)$invoice->party_amount : 0;
       
           $totalCashPaid += (!empty($invoice->cash_amount) && is_numeric($invoice->cash_amount)) ? (int)$invoice->cash_amount : 0;
           $totalUpiPaid  += (!empty($invoice->upi_amount)  && is_numeric($invoice->upi_amount)) ? (int)$invoice->upi_amount  : 0;
       
           $totalSales    += (!empty($invoice->sub_total)   && is_numeric($invoice->sub_total)) ? (int)$invoice->sub_total : 0;
           $totalPaid     += (!empty($invoice->total)       && is_numeric($invoice->total)) ? (int)$invoice->total : 0;
        }
        if (isset($this->categoryTotals['Desi'])) {
            $this->categoryTotals['DESHI SALES'] = $this->categoryTotals['Desi'];
            unset($this->categoryTotals['Desi']);
        }

        $this->todayCash = $totalPaid;
        $totalWith = \App\Models\WithdrawCash::where('user_id',  auth()->user()->id)
        ->where('branch_id', $branch_id)
        ->sum('amount');

        $this->categoryTotals['payment']['TOTAL'] =$totalCashPaid-$totalWith;
        $this->categoryTotals['payment']['DISCOUNT'] = $discountTotal * (-1);
        $this->categoryTotals['payment']['TOTAL SALES'] = $totalSales+@$this->shift->opening_cash;
        $this->categoryTotals['payment']['UPI PAYMENT'] = $totalUpiPaid;
        $this->categoryTotals['payment']['WITHDRAWAL PAYMENT'] = $totalWith*(-1);
        // $this->categoryTotals['TOTAL CASH'] =$this->shift->opening_cash+ $totalCashPaid-$totalWith;

        //TOTAL CASH
        $cashBreakdowns = CashBreakdown::where(['user_id' => auth()->user()->id])
            ->where(['branch_id' => $branch_id])
            ->where('type', '!=', 'cashinhand')  // Add condition where type is not 'cashinhand'
            ->get();


        $noteCount = [];
        
        foreach ($cashBreakdowns as $breakdown) {
            $denominations = json_decode($breakdown->denominations, true);
            if (is_array($denominations)) {
                foreach ($denominations as $denomination => $notes) {
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
      //  print_r($noteCount);exit;
        // Decode cash JSON to array
        $this->shiftcash = $noteCount;
        $this->checkTime();

        // return view('shift_closing.show', compact('shift'));
        //$this->loadCartData();
        $this->commissionUsers = Commissionuser::all(); // Assuming you have a model for this
        $this->partyUsers = Partyuser::all(); // Assuming you have a model for this
        foreach ($this->cartitems as $item) {
            $this->quantities[$item->id] = $item->quantity;
        }

        $this->holdTransactions =  Invoice::where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->latest()->get();

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
            
            $this->dispatch('notiffication-sucess', ['message' => 'Your cart has been voided successfully.']);

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
        $quantity = (isset($this->quantities[$itemId])) ? (int) $this->quantities[$itemId] : 0;
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
                    $item->quantity = $this->quantities[$itemId];
                    $item->net_amount=($item->mrp-$item->discount)*$this->quantities[$itemId];
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
        $this->cashAmount = $this->cartitems->sum('net_amount');
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
            $item->quantity++;
            // $item->amount+=$item->net_amount*($item->quantity);
            $item->net_amount=($item->mrp-$item->discount)*$item->quantity;
            $item->save();
          //  $this->cashAmount=$item->net_amount;
            if (isset($this->quantities[$id])) {
                $this->quantities[$id]++;
               // $this->updateQty($id);
            }
              //  $this->calculateParty();
            $this->dispatch('updateNewProductDetails');
           // $this->dispatch('updateProductList');
        }
    }
    public function incrementNote($key, $denomination, $type)
    {
        $this->cashNotes[$key][$denomination][$type] = ($this->cashNotes[$key][$denomination][$type] ?? 0) + 1;
    }
    
    public function decrementNote($key, $denomination, $type)
    {
        $current = $this->cashNotes[$key][$denomination][$type] ?? 0;
        if ($current > 0) {
            $this->cashNotes[$key][$denomination][$type] = $current - 1;
        }
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
            $item->quantity--;
            $item->net_amount=($item->mrp-$item->discount)*$item->quantity;
            
            $item->save();
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
        Cart::find($id)?->delete();
        $this->showBox = false;
        $this->dispatch('updateNewProductDetails');

//        $this->loadCartData();
    }

    public function calculateCommission()
    {
        $this->dispatch('user-selection-updated', ['userId' => $this->selectedUser]);
        $sum=$commissionTotal=0;
        $user = Commissionuser::find($this->selectedCommissionUser);
        if (!empty($user)) {
            // $getDiscountAmt = Cart::with(['product', 'product.inventorie'])
            //     ->where(['user_id' => auth()->user()->id])
            //     ->where('status', '!=', Cart::STATUS_HOLD)
            //     ->get()
            //     ->sum(fn($cart) => $cart->product->discount_price ?? 0);
            // $this->commissionAmount = $getDiscountAmt;
            $mycarts = Cart::with(['product', 'product.inventorie'])->where('user_id', auth()->user()->id)->where('status', Cart::STATUS_PENDING)->get();

            foreach ($mycarts as $key => $mycart) {
               $mycart->net_amount=$mycart->net_amount-($mycart->product->discount_price*$mycart->quantity);
               $mycart->discount=$mycart->product->discount_price*$mycart->quantity;
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
        $user = Partyuser::find($this->selectedPartyUser);
        if (!empty($user)) {
            $mycarts = Cart::where('user_id', auth()->user()->id)->where('status', Cart::STATUS_PENDING)->get();
            foreach ($mycarts as $key => $mycart) {
               $mycart->net_amount=$mycart->net_amount-($user->credit_points*$mycart->quantity);
               $mycart->discount=$user->credit_points*$mycart->quantity;
               $mycart->save();
               $sum=$sum+$mycart->net_amount;
            }
            
            //$this->basicPartyAmt=$user->credit_points*$mycart->quantity;
            $this->partyAmount = $user->credit_points;
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
      //  print_r($this->quantities);
        return view('livewire.shoppingcart', [
            'itemCarts' => $itemCarts,
            'narrations' => $this->narrations,
            'searchResults' => $this->searchTerm,
            'stores'=>$stores,
            'products'=>$products,
            'data'=>$data,
            'getNotification'=>$getNotification,

        ]);
    }

    public function addToCart($id)
    {
        
        if (auth()->user()) {
            $existingItemsum = Cart::where('product_id', $id)
            ->where('user_id', auth()->id())
            ->where('status', Cart::STATUS_PENDING)
            ->sum('quantity');

            // Fetch product with inventory
            $product = \App\Models\Product::with('inventorie')->find($id);
            //dd($product->inventorie->quantity);
            if (!$product || !$product->inventorie || $product->inventorie->quantity < $existingItemsum) {
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
                $commissionUser = CommissionUser::find($this->selectedCommissionUser);
                if(!empty($commissionUser)){
                    $myCart=$product->discount_price;

                }else{
                    $myCart=0;
                }

            }else{

                $user = Partyuser::find($this->selectedPartyUser);
                if (!empty($user)) {
                    $myCart=$user->credit_points;
                } else {
                    $myCart=0;
    
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
                $item->discount = $myCart;
                $item->net_amount = $product->sell_price-$myCart;
                $item->save();

            }
          
         
           // $this->updateQty($item->id);
            $this->dispatch('updateNewProductDetails');
            $this->reset('searchTerm', 'searchResults', 'showSuggestions');
            $this->dispatch('notiffication-success', ['message' => 'Product added to the cart successfull.']);


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
            }else{
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

            $commissionUser = CommissionUser::find($this->selectedCommissionUser);
            $partyUser = PartyUser::find($this->selectedPartyUser);
            $cartitems = $this->cartitems;

            foreach ($cartitems as $key => $cartitem) {
                $product = $cartitem->product->inventorie;
                if ($product && $product->quantity > 0) {
                    $product->quantity -= $cartitem->quantity;
                    $product->save();
                }
            }
            if ($this->paymentType == "cash") {

                $cashNotes = json_encode($this->cashNotes) ?? [];
            }else{
                $cashNotes = json_encode($this->cashupiNotes) ?? [];

            }

            $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
            // 💾 Save cash breakdown
            $cashBreakdown = \App\Models\CashBreakdown::create([
                'user_id' => auth()->id(),
                'branch_id' => $branch_id,
                'denominations' => $cashNotes,
                'total' => $this->cash,
            ]);

            $invoice_number = 'INV-' . strtoupper(Str::random(8));
            if (!empty($commissionUser)) {
                $address = $commissionUser->address ?? null;
            } else  if (!empty($partyUser)) {
                $address = $partyUser->address ?? null;
            }
            if ($this->paymentType == "cash") {
                $this->cash = $this->cashPaTenderyAmt;
                $this->upi = 0;
            }
            
            $invoice = Invoice::create([
                'user_id' => auth()->id(),
                'branch_id' => $branch_id,
                'invoice_number' => $invoice_number,
                'commission_user_id' => $commissionUser->id ?? null,
                'party_user_id' => $partyUser->id ?? null,
                'items' => $cartitems->map(fn($item) => [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'category' => $item->product->category->name,
                    'price' => $item->product->sell_price,
                ]),
                'upi_amount' => $this->upi,
                'cash_amount' => $this->cash,
                'sub_total' => $this->cashAmount,
                'tax' => $this->tax,
                'status' => "Paid",
                'commission_amount' => $this->commissionAmount,
                'party_amount' => $this->partyAmount,
                'total' => $this->cashAmount,
                'cash_break_id' => $cashBreakdown->id,
                //'billing_address'=> $address,
            ]);
             // Only warehouse role gets invoice for printing
             if (auth()->user()->hasRole('warehouse')) {
                $this->invoiceData = $invoice;
                $this->dispatch('triggerPrint');
            }
            
            //return redirect()->route('invoice.show', $invoice->id);
            Cart::where('user_id', auth()->user()->id)
                ->where('status', '!=', Cart::STATUS_HOLD)
                ->delete();
                $this->dispatch('notiffication-success', ['message' => 'Order placed successfully.']);
            $this->reset('searchTerm', 'searchResults', 'showSuggestions','cashAmount','shoeCashUpi','showBox','cashNotes','quantities','cartCount');
          //  return redirect()->back()->with('success', 'Order placed successfully.');


        } catch (\Illuminate\Validation\ValidationException $e) {
            // 🔔 Flash message for Laravel Blade
            $this->dispatch('notiffication-error', ['message' => 'Something went wrong']);

          //  return redirect()->back()->with('success', 'Withdraw amount successful.');

        }
    }
}
