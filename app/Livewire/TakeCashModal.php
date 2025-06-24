<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Partyuser;
use Livewire\WithPagination;
use Illuminate\Container\Attributes\DB;
use App\Models\CreditHistory;
use App\Models\Invoice;
use App\Models\UserShift;
use Carbon\Carbon;
use App\Models\CashBreakdown;

class TakeCashModal extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $showModal = false;
    public $showCollectModal = false;
    public $transactionType = 'change'; // default value: 'add' or 'change'

    public $selectedUser;
    public $amount;
    public $noteDenominations = [10, 20, 50, 100, 200, 500];
    public $cashNotes = [];
    public $totals = [
        'totalIn' => 0,
        'totalInCount' => 0,
        'totalOut' => 0,
        'totalOutCount' => 0,
    ];
    public $paymentType = 'cash';
    public $onlineAmount = 0;
    public $upiAmount = 0;
    public $totalCollected = 0;
    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedPaymentType()
    {
        if ($this->paymentType !== 'cash+upi') {
            $this->upiAmount = 0;
        }
        if ($this->paymentType !== 'online') {
            $this->onlineAmount = 0;
        }
    }
    public function paymentTypeChanged($type)
    {
        $this->paymentType = $type;

        // You can also reset other values here
        if ($type === 'online') {
            $this->upiAmount = 0;
        } elseif ($type === 'cash') {
            $this->onlineAmount = 0;
            $this->upiAmount = 0;
        } elseif ($type === 'cash+upi') {
            $this->onlineAmount = 0;
        }
        $this->totalCollected = 0;
    }
    public function openModal()
    {
        $this->reset(['showCollectModal', 'amount', 'search']);
        $this->resetPage();
        $this->showModal = true;
    }

    public function openCollectModal()
    {
        //$this->selectedUser = PartyUser::where('status', 'Active')->find($userId);
        $this->cashNotes = []; // Reset
        $this->calculateTotals();
        $this->showCollectModal = true;
    }

    public function incrementNote($key, $denomination, $type)
    {
        if (!isset($this->cashNotes[$key][$denomination][$type])) {
            $this->cashNotes[$key][$denomination][$type] = 0;
        }
        if($type =="out"){
             $this->countAvailableNote(); // This updates $this->availableNotes
            $shiftAvailabeNotes = json_decode($this->availableNotes, true);
            if (!isset($shiftAvailabeNotes[$denomination])) {
                $shiftAvailabeNotes[$denomination] = 0;
            }
             $this->cashNotes[$key][$denomination][$type]++;
             if($this->cashNotes[$key][$denomination][$type]>$shiftAvailabeNotes[$denomination]){
                $this->dispatch('note-unavailable', [
                    'message' => "Note of ₹$denomination ($type) is not available for $shiftAvailabeNotes[$denomination]."
                ]);
                $this->cashNotes[$key][$denomination][$type]--;
                return;
            }
        }else{

            $this->cashNotes[$key][$denomination][$type]++;
           // $this->cashNotes[$key][$denomination][$type] = ($this->cashNotes[$key][$denomination][$type] ?? 0) + 1;
        }
        $this->calculateTotals();
        $this->calculateTotal();
    }
    public function setTransactionType($type)
    {
        $this->transactionType = $type;
      
    }

     public function countAvailableNote()
    {
        $noteCount = [];
        $today = Carbon::today();
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        $currentShift = UserShift::with('cashBreakdown')->with('branch')->whereDate('start_time', $today)->where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->where(['status' => "pending"])->first();
        //
        $cashBreakdowns = CashBreakdown::where('user_id', auth()->id())
            ->where('branch_id', $branch_id)
            // ->where('type', '!=', 'cashinhand')
            ->whereBetween('created_at', [$currentShift->start_time, $currentShift->end_time])
            ->get();

        $noteCount = [];
        foreach ($cashBreakdowns as $breakdown) {
            $denominations1 = json_decode($breakdown->denominations, true);
            // echo "<pre>";
            // print_r($denominations1);
            if (is_array($denominations1)) {
                // Handle array of objects: [{"10":{"in":"0"}},{"20":{"in":"0"}},...]
                if (array_keys($denominations1) === range(0, count($denominations1) - 1)) {
                    foreach ($denominations1 as $item) {
                        if (is_array($item)) {
                            foreach ($item as $noteValue => $action) {
                                if (isset($action['in'])) {
                                    if (!isset($noteCount[$noteValue])) {
                                        $noteCount[$noteValue] = 0;
                                    }
                                    $noteCount[$noteValue] += (int)$action['in'];
                                }
                                if (isset($action['out'])) {
                                    if (!isset($noteCount[$noteValue])) {
                                        $noteCount[$noteValue] = 0;
                                    }
                                    $noteCount[$noteValue] -= (int)$action['out'];
                                }
                            }
                        }
                    }
                } else {
                    // Handle object with nested arrays: {"5":{"500":{"in":4}}, "3":{"100":{"out":1}}}
                    foreach ($denominations1 as $outer) {
                        if (is_array($outer)) {
                            foreach ($outer as $noteValue => $action) {
                                if (isset($action['in'])) {
                                    if (!isset($noteCount[$noteValue])) {
                                        $noteCount[$noteValue] = 0;
                                    }
                                    $noteCount[$noteValue] += (int)$action['in'];
                                }
                                if (isset($action['out'])) {
                                    if (!isset($noteCount[$noteValue])) {
                                        $noteCount[$noteValue] = 0;
                                    }
                                    $noteCount[$noteValue] -= (int)$action['out'];
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->shiftcash = $noteCount;
        $this->availableNotes = json_encode($this->shiftcash);
    }
    public function decrementNote($key, $denomination, $type)
    {
        $this->cashNotes[$key][$denomination][$type] = max(0, ($this->cashNotes[$key][$denomination][$type] ?? 0) - 1);
        $this->calculateTotals();
        $this->calculateTotal();
    }

    public function clearCashNotes()
    {
        $this->cashNotes = [];
        $this->calculateTotals();
        $this->totalCollected = 0; // fallback

    }

    public function calculateTotals()
    {
        $in = $inCount = $out = $outCount = 0;

        foreach ($this->noteDenominations as $key => $denomination) {
            $inQty = $this->cashNotes[$key][$denomination]['in'] ?? 0;
            $outQty = $this->cashNotes[$key][$denomination]['out'] ?? 0;

            $in += $denomination * $inQty;
            $inCount += $inQty;

            $out += $denomination * $outQty;
            $outCount += $outQty;
        }

        $this->totals = [
            'totalIn' => $in,
            'totalInCount' => $inCount,
            'totalOut' => $out,
            'totalOutCount' => $outCount,
        ];
    }


    // public function submitCredit()
    // {
    //     if ($this->selectedUser && $this->amount > 0) {
    //         $this->selectedUser->left_credit -= $this->amount;
    //         if ($this->selectedUser->left_credit <= 0) {
    //             $this->selectedUser->payment_status = 'full_paid';
    //             $this->selectedUser->left_credit = 0;
    //         } else {
    //             $this->selectedUser->payment_status = 'partial_paid';
    //         }
    //         $this->selectedUser->save();
    //         session()->flash('message', 'Credit collected successfully.');
    //         $this->reset(['showCollectModal', 'amount', 'selectedUser']);
    //         $this->openModal(); // Refresh list
    //     }
    // }
    public function calculateTotal()
    {
        $baseAmount = ($this->totals['totalIn'] ?? 0) - ($this->totals['totalOut'] ?? 0);
        $upi = (!empty($this->upiAmount) && $this->upiAmount > 0) ? $this->upiAmount : 0;

        if ($this->paymentType === 'cash') {
            $this->totalCollected = $baseAmount;
        } elseif ($this->paymentType === 'online') {
            $this->totalCollected = $this->onlineAmount;
        } elseif ($this->paymentType === 'cash+upi') {
            $this->totalCollected = $baseAmount + $upi;
        } else {
            $this->totalCollected = 0; // fallback
        }
    }
    public function submitCredit()
    {
        $this->calculateTotals();

        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        // validate cash breakdown logic
        $totalOut = $this->totals['totalOut'] ?? 0;
        $totalIn = $this->totals['totalIn'] ?? 0;
        
          // Optional: Add logic to reset or calculate something
        if ($this->transactionType === 'add') {
            if(empty($totalIn)){
                 $this->dispatch('note-add', [
                   'message' => "Please add amount."
               ]);
               return;
            }
            // logic for Add Money clicked
        } elseif ( $this->transactionType=== 'change') {
            // logic for Change clicked
            if(empty($totalIn) && empty($totalOut)){
                 $this->dispatch('note-add', [
                   'message' => "Please add amount."
               ]);
               return;
            }else if($totalIn!=$totalOut){

                $this->dispatch('note-add', [
                   'message' => "Please match IN OUT amount for change."
               ]);
               return;
            }
        }


        $collectedAmount = $totalIn - $totalOut;
        $denominations = array_values($this->cashNotes);
        $currentShift = UserShift::getYesterdayShift(auth()->user()->id, $branch_id,"pending");
        $date = "";
        if (!empty($currentShift)) {
            $date = \Carbon\Carbon::parse($currentShift->start_time)->toDateString();
        } else {
            $date = Carbon::today();
        }
        $currentShift = UserShift::with('cashBreakdown')->with('branch')->whereDate('start_time', $date)->where(['user_id' => auth()->user()->id])->where(['branch_id' => $branch_id])->where(['status' => "pending"])->first();

        if(!empty($currentShift)) {
            $currentShift->cash_added=$collectedAmount;
            $currentShift->save();
        }
        $cashBreakdown = \App\Models\CashBreakdown::create([
            'user_id' => auth()->id(),
            'branch_id' => $branch_id,
            'denominations' => json_encode($denominations),
            'total' => $collectedAmount,
            'type' => $this->transactionType." cash"
        ]);
        $this->cashNotes = []; // Reset
        $this->showCollectModal = false;
    }

    public function render()
    {
        $query = Partyuser::query()
            ->where('status', 'Active')
            ->where('is_delete', 'No');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%')
                    ->orWhere('credit_points', 'like', '%' . $this->search . '%');
            });
        }

        $partyUsers = $query->paginate(10);

        return view('livewire.take-cash-modal', [
            'partyUsers' => $partyUsers
        ]);
    }
}
