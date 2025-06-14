<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\UserShift;
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
use App\Models\CreditCollection;
use Illuminate\Support\Facades\Auth;
use App\Models\DailyProductStock;
use Illuminate\Support\Facades\Storage;

class ShiftCloseModal extends Component
{

    public $cartItems = [];

    public $invoiceData;
    public $shft_id;
    public $totalInvoicedAmount = 0;
    public $cash = 0;
    public $creditPay = 0;
    public $upi = 0;
    public $updatingField = null;
    public $showCloseButton = false;
    public $buttonEnabled = false;
    public $creditCollacted=[];
    public $shift;
    public $shiftcash;
    public  $narrations = [
        'Personal Expenses',
        'Travel Expenses',
        'Other'
    ];
    public $stockStatus = [];
    public $addstockStatus = [];
    public $closing_sales="";
    public $errorInCredit = false;
    public $changeAmount = 0;
    public $showBox = false;
    public $shoeCashUpi = false;
    public $showRefund = false;
    public $showSr = false;
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
    public $basicPartyAmt = 0;
    public $productSearch = '';
    public $searchResults = [];
    public $searchSalesResults = [];

    public $products = [];
    public $tenderedAmount = 0;
    public $showModal = false;
    public $availableNotes = "";
    public $selectedUser = 0;
    protected $listeners = ['updateProductList' => 'loadCartData','openCloseModal' => 'openModal', 'loadHoldTransactions', 'updateNewProductDetails', 'resetData','setCapturedImage'];
    public $noteDenominations = [10, 20, 50, 100, 200, 500];
    public $remainingAmount = 0;
    public $totalBreakdown = [];
    public $searchTerm = '';
    public $searchSalesReturn = '';
    public $branch_name = '';
    public $quantities = [];
    public $showSuggestions = false;
    public $showSuggestionsSales = false;

    public $selectedNote;
    public $cashNotes = [];
    public $todayCash = 0;
    public $upiPayment = 0;
    public $cashPayment = 0;
    public $paymentType = "";
    public $scTotalCashAmt = 0;
    public $scTotalUpiAmt = 0;
    public $shiftEndTime = "";
    public $cashupiNotes = [];
    public $numpadValue = '0'; // Default value of numpad
    public $focusedField = null; // Track the currently focused input field
    public $search = '';

    public $selectedProduct;
    public $selectedSalesReturn;
    public $holdTransactions = [];
    public $headertitle = "";
    public $language;
    public $refundDesc = "";
    public $closingCash = "";
    public $diffCash = 0;
    public bool $showStockModal = false;
    public bool $showPhysicalModal = false;
    public $capturedImage;
    public $currentShift="";
    protected $rules = [
        'closingCash' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'closingCash.required' => 'Closing cash is required.',
        'closingCash.numeric' => 'Closing cash must be a number.',
        'closingCash.min' => 'Closing cash cannot be negative.',
    ];
    public $productStock = [];
    public $showCloseModal = false;
    public $shiftTime;
    public $image;
    public $showYesterDayShiftTime = false;
    public $currentShiftDate = "";



    public function setCapturedImage($image="")
    {
        $this->capturedImage = $image;
    }
    public function getCurrentShift()
    {
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        $this->currentShift = UserShift::getYesterdayShift(auth()->user()->id, $branch_id);
        $date="";
        if(!empty($this->currentShift)){
            $date = \Carbon\Carbon::parse($this->currentShift->start_time)->toDateString();
            $this->showYesterDayShiftTime=true;
         }else{
             $date = Carbon::today();
            
         }
        $this->currentShift = UserShift::with('cashBreakdown')->with('branch')->whereDate('start_time', $date)->where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->where(['status' => "pending"])->first();
        
    }
     public function removeHold()
    {
        $date = \Carbon\Carbon::parse($this->currentShift->start_time)->toDateString();
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";

        $archived = Invoice::whereDate('created_at', $date)->where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->where(['status' => "hold"])->update(['status' => 'archived']);
      
        $this->dispatch('notiffication-sucess', ['message' => 'Hold removed. You can now close the shift.']);


    }
    
    public function openModal($shiftTime=[])
    {
        $this->getCurrentShift();
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        //  if(!empty($this->currentShift)){
        //     $today = \Carbon\Carbon::parse($this->currentShift->start_time)->toDateString();
        //     $this->showYesterDayShiftTime=true;
        //  }else{
        //      $today = Carbon::today();
        //  }


        // // Fetch and assign your shift data here (dummy data for now)
        // $this->currentShift = $currentShift = UserShift::with('cashBreakdown')->with('branch')->whereDate('start_time', $today)->where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->where(['status' => "pending"])->first();

        // if (empty($this->currentShift) && $now->greaterThanOrEqualTo($cutoff)) {
        //         $this->dispatch('close-shift-12am');
        //         return;
        // }
        $this->currentShift=$this->currentShift;
        $this->shft_id = $this->currentShift->id ?? null;
        // $this->currentShift = Shift::latest()->first();
        $this->branch_name = $this->currentShift->branch->name ?? 'Shop';
        $sales = ['DESI', 'BEER', 'ENGLISH'];
        $discountTotal = $totalSales = $totalPaid = $totalRefund = $totalCashPaid = $totalSubTotal = $totalCreditPay = $totalUpiPaid = $totalRefundReturn = $totalOnlinePaid =$totalRoundOf= 0;

        $this->categoryTotals = [];
        $this->totalInvoicedAmount = \App\Models\Invoice::where('user_id', auth()->user()->id)
            ->where('branch_id', $branch_id)
            ->sum('total');
        // ✅ Initialize totals to 0 for all expected categories
        // foreach ($sales as $category) {
        //     $this->categoryTotals['sales'][$category] = 0;
        // }
        $start_date = @$this->currentShift->start_time; // your start date (set manually)
        $end_date = @$this->currentShift->end_time; // today's date till end of day
        $totals = CreditHistory::whereBetween('created_at', [$start_date, $end_date])
            ->where('store_id', $branch_id)
            ->selectRaw('SUM(credit_amount) as credit_total, SUM(debit_amount) as debit_total')
            ->first();
        $this->creditCollacted = \DB::table('credit_collections')
        ->selectRaw('
        SUM(cash_amount) as collacted_cash_amount,
        SUM(online_amount) as collacted_online_amount,
        SUM(upi_amount) as collacted_upi_amount
        ')
        ->whereBetween('created_at', [$start_date, $end_date])
        ->first();

        // $invoices = Invoice::where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->whereBetween('created_at', [$start_date, $end_date])->where('status', '!=', 'Hold')->where('invoice_number', 'not like', '%Hold%')->latest()->get();
        $invoices = Invoice::where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->whereBetween('created_at', [$start_date, $end_date])->whereNotIn('status',[ 'Hold','resumed','archived'])->latest()->get();
        foreach ($invoices as $invoice) {
            $items = $invoice->items; // decode items from longtext JSON

            if (is_string($items)) {
                $items = json_decode($items, true); // decode if not already an array
            }

            if (is_array($items)) {
                $totalSalesNew=0;
                foreach ($items as $item) {
                    if(!empty($item['subcategory'])){

                        $category =  Str::upper($item['subcategory'])  ?? 'Unknown';
                        $amount = $item['price'] ?? 0;
    
                        if (!isset($this->categoryTotals['sales'][$category])) {
                            $this->categoryTotals['sales'][$category] = 0;
                        }
    
                        $this->categoryTotals['sales'][$category] += $amount+$invoice->roundof;
                        $totalSalesNew= $this->categoryTotals['sales'][$category];
                    }
                }
                $this->categoryTotals['sales']["TOTAL"] = $totalSalesNew;
            }

            $this->closing_sales=@$this->categoryTotals['sales'];
            // $discountTotal += ($invoice->commission_amount ?? 0) + ($invoice->party_amount ?? 0);
            $discountTotal += (!empty($invoice->commission_amount) && is_numeric($invoice->commission_amount)) ? (int)$invoice->commission_amount : 0;
            $discountTotal += (!empty($invoice->party_amount) && is_numeric($invoice->party_amount)) ? (int)$invoice->party_amount : 0;

            $totalCashPaid += (!empty($invoice->cash_amount) && is_numeric($invoice->cash_amount)) ? (int)$invoice->cash_amount : 0;

            $totalSubTotal += (!empty($invoice->total)) ? parseCurrency($invoice->total) : 0;
            $totalUpiPaid  += (!empty($invoice->upi_amount)  && is_numeric($invoice->upi_amount)) ? (int)$invoice->upi_amount  : 0;
            $totalOnlinePaid  += (!empty($invoice->online_amount)  && is_numeric($invoice->online_amount)) ? (int)$invoice->online_amount  : 0;
            $totalRoundOf  += (!empty($invoice->roundof)  && is_numeric($invoice->roundof)) ? (int)$invoice->roundof  : 0;
            
            if ($invoice->status == "Returned") {
                $totalRefundReturn += floatval(str_replace(',', '', $invoice->total));
            }



            $totalCreditPay  += (!empty($invoice->creditpay)  && is_numeric($invoice->creditpay)) ? (int)$invoice->creditpay  : 0;

            $totalSales    += (!empty($invoice->sub_total)   && is_numeric($invoice->sub_total)) ? (int)$invoice->sub_total : 0;
            $totalPaid     += (!empty($invoice->total)       && is_numeric($invoice->total)) ? (int)$invoice->total : 0;
            if ($invoice->status == "Refunded") {
                $refund = Refund::where('invoice_id', $invoice->id)
                    ->where('user_id', auth()->id())
                    ->first();
                if ($refund) {
                    $totalRefund     += (!empty($refund->amount)       && is_numeric($refund->amount)) ? (int)$refund->amount : 0;
                }
            }
        }
        $this->todayCash = $totalPaid;
        $totalWith = \App\Models\WithdrawCash::where('user_id',  auth()->user()->id)
            ->where('branch_id', $branch_id)->whereBetween('created_at', [$start_date, $end_date])->sum('amount');
        $this->categoryTotals['payment']['CASH'] = $totalCashPaid;
        $this->categoryTotals['payment']['UPI PAYMENT'] =  ($totalUpiPaid+$totalOnlinePaid) ;
        $this->categoryTotals['payment']['TOTAL'] = $totalCashPaid+ ($totalUpiPaid+$totalOnlinePaid) ;

        $this->categoryTotals['summary']['OPENING CASH'] = @$this->currentShift->opening_cash;
        $this->categoryTotals['summary']['TOTAL SALES'] = $totalSubTotal + $discountTotal-$totalRefundReturn-$totalRoundOf;
        $this->categoryTotals['summary']['DISCOUNT'] = $discountTotal * (-1);
        
        $this->categoryTotals['summary']['WITHDRAWAL PAYMENT'] = $totalWith * (-1);
        $this->categoryTotals['summary']['UPI PAYMENT'] = ($totalUpiPaid+$totalOnlinePaid) * (-1);
        $this->categoryTotals['summary']['ROUND OFF'] = $totalRoundOf;
        //$this->categoryTotals['summary']['ONLINE PAYMENT'] = $totalOnlinePaid * (-1);
        if(!empty($this->creditCollacted->collacted_cash_amount))
        $this->categoryTotals['summary']['CREDIT COLLACTED BY CASH'] = $this->creditCollacted->collacted_cash_amount;
        // $this->categoryTotals['summary']['REFUND'] += $totalRefundReturn *(-1);
        $this->categoryTotals['summary']['TOTAL'] = $this->categoryTotals['summary']['OPENING CASH'] + $this->categoryTotals['summary']['TOTAL SALES'] + $this->categoryTotals['summary']['DISCOUNT'] + $this->categoryTotals['summary']['WITHDRAWAL PAYMENT'] + $this->categoryTotals['summary']['UPI PAYMENT'] + @$this->categoryTotals['summary']['REFUND'] +
            @$this->categoryTotals['summary']['ONLINE PAYMENT']+ @$this->categoryTotals['summary']['CREDIT COLLACTED BY CASH']+$totalRoundOf;
        $this->categoryTotals['summary']['REFUND'] = $totalRefund * (-1) + $totalRefundReturn * (-1);
        //$this->categoryTotals['summary']['REFUND RETURN'] = $totalRefundReturn*(-1);
        $this->categoryTotals['summary']['CREDIT'] = $totals->debit_total;
        $this->categoryTotals['summary']['REFUND_CREDIT'] = $totals->credit_total;
        if (!empty($this->categoryTotals['summary']['REFUND_CREDIT'])) {
            $this->categoryTotals['summary']['REFUND_CREDIT'] = (int)$this->categoryTotals['summary']['REFUND_CREDIT'] * (-1);
        }

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
        //print_r($noteCount);exit;
        // Decode cash JSON to array
        $this->shiftcash = $noteCount;
        $this->availableNotes = json_encode($this->shiftcash);

        $this->showModal = true;
    }

    public function openClosingStocksModal()
    {
      
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        $user_id = auth()->id();
     
        if (isset($this->currentShift->physical_stock_added) && $this->currentShift->physical_stock_added==0) {
                $this->dispatch('notiffication-error', ['message' => 'Please add physical sales first']);
                return;
        }
        $this->showStockModal = true;
        $rawStockData = DailyProductStock::with('product')
            ->where('branch_id', $branch_id)
            ->where('shift_id', $this->currentShift->id)
            ->get()->toArray();
        
        $this->stockStatus = array_map(function ($item) {
            $item['closing_stock'] =
                $item['opening_stock'] +
                $item['added_stock'] -
                $item['transferred_stock'] -
                $item['sold_stock'];
            return $item;
        }, $rawStockData);

    }
    public function addphysicalStock()
    {
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
       
        if ($this->currentShift->physical_stock_added==1) {
             $this->dispatch('notiffication-error', ['message' => 'Physical stock already added.']);
            return;
        }
        $this->showPhysicalModal = true;
        $this->dispatch('test');

        $rawStockData = DailyProductStock::with('product')
            ->where('branch_id', $branch_id)
            ->where('shift_id', $this->currentShift->id)
            ->get()->toArray();

        $this->addstockStatus = array_map(function ($item) {
            $item['closing_stock'] =
                $item['opening_stock'] +
                $item['added_stock'] -
                $item['transferred_stock'] -
                $item['sold_stock'];
            return $item;
        }, $rawStockData);
        
    }
    //  public function rules()
    // {
    //     return [
    //         'products.*.qty' => 'required|integer|min:1',
    //     ];
    // }
      public function save()
    {
        $this->validate([
            'products.*.qty' => 'required|integer',
        ]);
          if($this->showYesterDayShiftTime){
            $dateMatch = Carbon::yesterday();
        }else{
            $dateMatch = Carbon::today();
        }
        if (empty($this->products)) {
            $this->dispatch('notiffication-error', ['message' => 'Please add qty of product ']);
            return;
        }
         if (empty($this->capturedImage)) {
            $this->dispatch('notiffication-error', ['message' => 'Please add 
            physical stock image']);
            return;
        }
         if ($this->capturedImage) {
            // Decode and store image
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $this->capturedImage));
            $filename = 'captured_images/' . uniqid() . '.jpg';
            Storage::disk('public')->put($filename, $imageData);

            // Store path in DB (if needed)
            // Example: PhysicalStock::create([... , 'image_path' => $filename]);
        }
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";

        foreach ($this->products as $product_id =>  $product) {
             $dailyProductStock = DailyProductStock::where('branch_id', $branch_id)
                ->where('product_id', $product_id)->whereDate('date', $dateMatch)
                ->first();
            if(!empty($dailyProductStock)){
                  // Calculate closing_stock using the formula
                $closingStock = $dailyProductStock->opening_stock
                    + $dailyProductStock->added_stock
                    - $dailyProductStock->transferred_stock
                    - $dailyProductStock->sold_stock;

                $dailyProductStock->physical_stock = $product['qty'];
                $dailyProductStock->closing_stock = $closingStock ?? 0;
                $dailyProductStock->difference_in_stock = $closingStock-$product['qty'];
                $dailyProductStock->save();
            }
        }
        $shift = UserShift::where('id', $this->shft_id) 
        ->where('branch_id', $branch_id)
        ->whereBetween('created_at', [$this->currentShift->start_time, $this->currentShift->end_time])
        ->where('status', 'pending')
        ->update([
        'physical_stock_added' => true,
        'physical_photo' => $filename,
        ]);
        $this->showPhysicalModal = false;
        $this->dispatch('notiffication-sucess', ['message' => 'Physical sales added successfully']);

    }

    public function closeStockModal()
    {
        $this->showStockModal = false;
    }
     public function closePhyStockModal()
    {
        $this->showPhysicalModal = false;
    }
    public function updatedClosingCash()
    {
        $expected = $this->categoryTotals['summary']['TOTAL'] ?? 0;
        $this->diffCash = (int)$this->closingCash - (int) $expected;
    }

    public function submit()
    {
        $this->validate();
        DB::beginTransaction();

        try {
           
            $user_id = auth()->id();
            $branch_id = auth()->user()->userinfo->branch->id ?? null;
            $holdInvoiceExists = Invoice::where('user_id', $user_id)
            ->where('branch_id', $branch_id)
            ->whereBetween('created_at', [$this->currentShift->start_time, $this->currentShift->end_time])
            ->where('status', 'Hold')
            ->exists();
            if ($holdInvoiceExists) {
                DB::rollBack();
                $this->dispatch('close-shift-error');
                return;
            }

            // Save cash breakdown
            $cashBreakdown = CashBreakdown::create([
                'user_id' => $user_id,
                'branch_id' => $branch_id,
                'denominations' => json_encode($this->shiftcash),
                'total' => $this->todayCash,
            ]);

            // Update shift data
            $shift = UserShift::where('user_id', $user_id)
                ->where('branch_id', $branch_id)
                ->where('status', 'pending')
                 ->where('id', $this->currentShift->id)
                ->first();
            
            if ($shift->physical_stock_added==0) {
                 $this->dispatch('notiffication-error', ['message' => 'Please add physical sales first']);
                 return;
            }else if (!$shift) {
                $this->addError('shift', 'No active shift found for this user.');
                return;
            }

            $shift->start_time = $this->currentShift->start_time;
            $shift->end_time = now();
            $shift->opening_cash = str_replace([',', '₹'], '', $this->categoryTotals['summary']['OPENING CASH'] ?? 0);
            $shift->cash_discrepancy = str_replace([',', '₹'], '', $this->diffCash ?? 0);
            $shift->closing_cash = str_replace([',', '₹'], '', $this->closingCash);
            $shift->cash_break_id = $cashBreakdown->id;
            $shift->deshi_sales = str_replace([',', '₹'], '', $this->categoryTotals['sales']['DESI'] ?? 0);
            $shift->beer_sales = str_replace([',', '₹'], '', $this->categoryTotals['sales']['BEER'] ?? 0);
            $shift->english_sales = str_replace([',', '₹'], '', $this->categoryTotals['sales']['ENGLISH'] ?? 0);
            $shift->upi_payment = str_replace([',', '₹'], '', $this->categoryTotals['payment']['UPI PAYMENT'] ?? 0);
            $shift->withdrawal_payment = str_replace([',', '₹'], '', $this->categoryTotals['summary']['WITHDRAWAL PAYMENT'] ?? 0);
            $shift->cash = str_replace([',', '₹'], '', $this->closingCash ?? 0);
            $shift->closing_sales=json_encode($this->closing_sales);
            $shift->status = 'completed';
            $shift->save();
            //  Invoice::where(['user_id' => $user_id])->where(['branch_id' => $branch_id])->where('status', 'Hold')->delete();
            $cartItems = Cart::where('user_id', $user_id)->delete(); // <-- get all matching rows
            // Update user login status and logout
            $user = User::find($user_id);
            $user->is_login = 'No';
            $user->save();

            // $stocks = DailyProductStock::with('product')
            //     ->where('branch_id', $branch_id)
            //     ->whereDate('date', $dateMatch)
            //     ->get();

            // foreach ($stocks as $stock) {

            //     // Calculate closing_stock using the formula
            //     $closingStock = $stock->opening_stock
            //         + $stock->added_stock
            //         - $stock->transferred_stock
            //         - $stock->sold_stock;

            //     // Optionally, save closing_stock if it's not saved yet
            //     if ($stock->closing_stock !== $closingStock) {
            //         $stock->closing_stock = $closingStock;
            //         $stock->save();
            //     }
            // }

            session()->forget(auth()->id().'_warehouse_product_photo_path', []);
            session()->forget(auth()->id().'_warehouse_customer_photo_path', []);
            session()->forget(auth()->id().'_cashier_product_photo_path', []);
            session()->forget(auth()->id().'_cashier_customer_photo_path', []);

            Auth::logout();

            DB::commit();

            session()->flash('success', 'Shift closed successfully. You have been logged out.');
            return redirect()->route('login');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error closing shift', ['error' => $e->getMessage()]);
            $this->addError('general', 'An error occurred while closing the shift. Please try again.');
        }
    }
    public function getStockStatus(): array
    {
        // Example DB fetch, adapt to your DB structure
        return \App\Models\Product::select('name', 'quantity', 'price')->get()->toArray();
    }
    public function calculateDiscrepancy()
    {
        $expected = $this->categoryTotals['summary']['TOTAL'] ?? 0;
        $this->diffCash = round($this->closingCash - $expected, 2);
    }


    public function render()
    {
        return view('livewire.shift-close-modal');
    }
}
