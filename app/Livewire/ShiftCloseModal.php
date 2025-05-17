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
use App\Models\InvoiceHistory;
use Illuminate\Support\Facades\Auth;


class ShiftCloseModal extends Component
{

    public $cartItems = [];

    public $invoiceData;
    public $totalInvoicedAmount = 0;
    public $cash = 0;
    public $creditPay = 0;
    public $upi = 0;
    public $updatingField = null;
    public $showCloseButton = false;
    public $buttonEnabled = false;

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
    protected $listeners = ['updateProductList' => 'loadCartData', 'loadHoldTransactions', 'updateNewProductDetails', 'resetData'];
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

    protected $rules = [
        'closingCash' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'closingCash.required' => 'Closing cash is required.',
        'closingCash.numeric' => 'Closing cash must be a number.',
        'closingCash.min' => 'Closing cash cannot be negative.',
    ];

    public function openModal()
    {
        $today = Carbon::today();
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";

        // Fetch and assign your shift data here (dummy data for now)
        $this->shift = $currentShift = UserShift::with('cashBreakdown')->with('branch')->whereDate('start_time', $today)->where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->where(['status' => "pending"])->first();
        // $this->shift = Shift::latest()->first();
        $this->branch_name = $this->shift->branch->name ?? 'Shop';
        $sales = ['DESI', 'BEER', 'ENGLISH'];
        $discountTotal = $totalSales = $totalPaid = $totalRefund = $totalCashPaid = $totalSubTotal = $totalCreditPay=$totalUpiPaid = $totalRefundReturn=$totalOnlinePaid=0;

        $this->categoryTotals = [];
        $this->totalInvoicedAmount = \App\Models\Invoice::where('user_id', auth()->user()->id)
            ->where('branch_id', $branch_id)
            ->sum('total');
        // ✅ Initialize totals to 0 for all expected categories
        // foreach ($sales as $category) {
        //     $this->categoryTotals['sales'][$category] = 0;
        // }
        $start_date = @$currentShift->start_time; // your start date (set manually)
        $end_date = date('Y-m-d') . ' 23:59:59'; // today's date till end of day
        $totals = CreditHistory::whereBetween('created_at', [$start_date, $end_date])
        ->where('store_id', $branch_id)
        ->selectRaw('SUM(credit_amount) as credit_total, SUM(debit_amount) as debit_total')
        ->first();

        $invoices = Invoice::where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->whereBetween('created_at', [$start_date, $end_date])->where('status','!=', 'Hold')->where('invoice_number', 'not like', '%Hold%')->latest()->get();
        
        foreach ($invoices as $invoice) {
            $items = $invoice->items; // decode items from longtext JSON

            if (is_string($items)) {
                $items = json_decode($items, true); // decode if not already an array
            }

            if (is_array($items)) {
                foreach ($items as $item) {
                    $category = Str::upper($item['category'])  ?? 'Unknown';
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

            $totalSubTotal += (!empty($invoice->total)) ?parseCurrency($invoice->total) : 0;
            $totalUpiPaid  += (!empty($invoice->upi_amount)  && is_numeric($invoice->upi_amount)) ? (int)$invoice->upi_amount  : 0;
            $totalOnlinePaid  += (!empty($invoice->online_amount)  && is_numeric($invoice->online_amount)) ? (int)$invoice->online_amount  : 0;
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
       // $this->categoryTotals['payment']['UPI PAYMENT'] = $totalUpiPaid;
        $this->categoryTotals['summary']['OPENING CASH'] = @$currentShift->opening_cash;
        $this->categoryTotals['summary']['TOTAL SALES'] = $totalSubTotal + $discountTotal;
        $this->categoryTotals['summary']['DISCOUNT'] = $discountTotal * (-1);
        $this->categoryTotals['summary']['WITHDRAWAL PAYMENT'] = $totalWith * (-1);
        $this->categoryTotals['summary']['UPI PAYMENT'] = $totalUpiPaid * (-1);
        $this->categoryTotals['summary']['ONLINE PAYMENT'] = $totalOnlinePaid * (-1);
        // $this->categoryTotals['summary']['REFUND'] += $totalRefundReturn *(-1);
        $this->categoryTotals['summary']['TOTAL'] = $this->categoryTotals['summary']['OPENING CASH'] + $this->categoryTotals['summary']['TOTAL SALES'] + $this->categoryTotals['summary']['DISCOUNT'] + $this->categoryTotals['summary']['WITHDRAWAL PAYMENT'] + $this->categoryTotals['summary']['UPI PAYMENT'] + @$this->categoryTotals['summary']['REFUND']+
        $this->categoryTotals['summary']['ONLINE PAYMENT'];
        $this->categoryTotals['summary']['REFUND'] = $totalRefund*(-1)+$totalRefundReturn*(-1);
        //$this->categoryTotals['summary']['REFUND RETURN'] = $totalRefundReturn*(-1);
        $this->categoryTotals['summary']['CREDIT'] = $totals->credit_total;
        $this->categoryTotals['summary']['REFUND_CREDIT'] = $totals->debit_total;
        if (!empty($this->categoryTotals['summary']['REFUND_CREDIT'])) {
            $this->categoryTotals['summary']['REFUND_CREDIT'] = (Int)$this->categoryTotals['summary']['REFUND_CREDIT']*(-1);
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

    public function updatedClosingCash()
    {
        $expected = $this->categoryTotals['summary']['TOTAL'] ?? 0;
        $this->diffCash = (Int)$this->closingCash -(Int) $expected;
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
                ->first();

            if (!$shift) {
                $this->addError('shift', 'No active shift found for this user.');
                return;
            }

            $shift->start_time = $this->shift->start_time;
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
            $shift->status = 'completed';
            $shift->save();
          //  Invoice::where(['user_id' => $user_id])->where(['branch_id' => $branch_id])->where('status', 'Hold')->delete();
            $cartItems = Cart::where('user_id', $user_id)->delete(); // <-- get all matching rows
            // Update user login status and logout
            $user = User::find($user_id);
            $user->is_login = 'No';
            $user->save();
            if (session()->has('checkout_images')) {
                session()->forget('checkout_images');
            }

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
