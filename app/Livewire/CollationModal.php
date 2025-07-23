<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Partyuser;
use Livewire\WithPagination;
use App\Models\CreditHistory;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB; // ✅ CORRECT

class CollationModal extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $showModal = false;
    public $showCollectModal = false;
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

    public function openCollectModal($userId)
    {
        $this->selectedUser = PartyUser::where('status', 'Active')->find($userId);
        $this->cashNotes = []; // Reset
        $this->calculateTotals();
        $this->showCollectModal = true;
    }

    public function incrementNote($key, $denomination, $type)
    {
        $this->cashNotes[$key][$denomination][$type] = ($this->cashNotes[$key][$denomination][$type] ?? 0) + 1;
        $this->calculateTotals();
        $this->calculateTotal();
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
        if ($this->paymentType === 'cash') {
            // validate cash breakdown logic
            $totalOut = $this->totals['totalOut'] ?? 0;
            $totalIn = $this->totals['totalIn'] ?? 0;

            $collectedAmount = $totalIn - $totalOut;

            if ($collectedAmount <= 0) {
                $this->dispatch('notiffication-error', ['message' => 'Collection amount must be greater than zero.']);

                return;
            }
        } elseif ($this->paymentType === 'online') {
            $totalOut =  0;
            $totalIn = 0;
            $this->validate(['onlineAmount' => 'required|min:1']);
            $collectedAmount = $this->onlineAmount;

            if ($collectedAmount <= 0) {
                $this->dispatch('notiffication-error', ['message' => 'Collection amount must be greater than zero.']);

                return;
            }
        } elseif ($this->paymentType === 'cash+upi') {
            $this->validate(['upiAmount' => 'required|min:1']);
            $totalOut = $this->totals['totalOut'] ?? 0;
            $totalIn = $this->totals['totalIn'] ?? 0;

            $collectedAmount = $totalIn - $totalOut;

            if (!empty($this->upiAmount)) {
                $collectedAmount = $collectedAmount + $this->upiAmount;
            }

            if ($collectedAmount <= 0) {
                $this->dispatch('notiffication-error', ['message' => 'Collection amount must be greater than zero.']);

                return;
            }
        }

        // Ensure a user is selected
        if (!$this->selectedUser) {
            session()->flash('error', 'No user selected.');
            return;
        }

        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        $remainingAmount = $collectedAmount;

        //DB::beginTransaction();
        //try {
        // Fetch latest user data
        $user = \App\Models\PartyUser::where('status', 'Active')->find($this->selectedUser->id);
        if ((int)$user->use_credit < (int)$collectedAmount) {
            $this->dispatch('notiffication-error', ['message' => 'Collection amount can not more then available credit']);
            return;
        }

        $remainingAmount = $collectedAmount;

        $unPaidInvoices = Invoice::where('branch_id', $branch_id)
            ->where('party_user_id', $this->selectedUser->id)
            ->whereIn('invoice_status', ['unpaid', 'partial_paid']) // Include both statuses
            ->where('creditpay', '>', 0)
            ->orderBy('id', 'asc')
            ->get();

        foreach ($unPaidInvoices as $invoice) {
            $alreadyPaid = $invoice->paid_credit;
            $dueAmount = $invoice->creditpay - $alreadyPaid;

            if ($dueAmount <= 0) {
                // Already paid
                $invoice->invoice_status = 'paid';
                $invoice->remaining_credit_pay = 0;
                $invoice->save();

                DB::table('credit_histories')
                    ->where('invoice_id', $invoice->id)
                    ->where('party_user_id', $this->selectedUser->id)
                    ->update([
                        'status' => 'paid',
                        'debit_amount' => $invoice->paid_credit,
                    ]);
                continue;
            }

            if ($remainingAmount <= 0) break;

            if ($remainingAmount >= $dueAmount) {
                // Fully pay this invoice
                $invoice->paid_credit += $dueAmount;
                $invoice->invoice_status = 'paid';
                $remainingAmount -= $dueAmount;
            } else {
                // Partial pay
                $invoice->paid_credit += $remainingAmount;
                $invoice->invoice_status = 'partial_paid';
                $remainingAmount = 0;
            }

            $invoice->remaining_credit_pay = $invoice->creditpay - $invoice->paid_credit;
            $invoice->save();

            DB::table('credit_histories')
                ->where('invoice_id', $invoice->id)
                ->where('party_user_id', $this->selectedUser->id)
                ->update([
                    'status' => $invoice->invoice_status,
                    'debit_amount' => $invoice->paid_credit, // use paid amount
                ]);
        }


        if ($this->paymentType === 'online') {
            if ($user->left_credit == 0) {
                $user->left_credit = $user->use_credit;
            }
            $collectedAmount = $this->onlineAmount;
            $user->use_credit = max(0, $user->use_credit - $collectedAmount);
            // Update left_credit (assuming it's reduced by amount collected)
            $user->left_credit = max(0, $user->left_credit + $this->onlineAmount);
        } else if ($this->paymentType === 'cash+upi') {
            if ($user->left_credit == 0) {
                $user->left_credit = $user->use_credit;
            }
            $collectedAmount += $this->upiAmount;
            // Update left_credit (assuming it's reduced by amount collected)
            $user->use_credit = max(0, $user->use_credit - $collectedAmount);
            $user->left_credit = max(0, $user->left_credit + $collectedAmount);
        } else {

            if ($user->left_credit == 0) {
                $user->left_credit = $user->use_credit;
            }
            $user->use_credit = max(0, $user->use_credit - $collectedAmount);
            // Update left_credit (assuming it's reduced by amount collected)
            $user->left_credit = max(0, $user->left_credit + $collectedAmount);
        }

        $user->save();
        $denominations = array_values($this->cashNotes);

        if ($this->paymentType != 'online') {
            $cashBreakdown = \App\Models\CashBreakdown::create([
                'user_id' => auth()->id(),
                'branch_id' => $branch_id,
                'denominations' => json_encode($denominations),
                'total' => $collectedAmount,
            ]);
        }

        // Optional: log this as a payment/collection entry
        \App\Models\CreditCollection::create([
            'party_user_id' => $user->id,
            'payment_method' => $this->paymentType,
            'cash_break_id' => $cashBreakdown->id ?? 0,
            'amount' => $collectedAmount,
            'upi_amount' => $this->upiAmount,
            'online_amount' => $this->onlineAmount,
            'cash_amount' => $totalIn - $totalOut,
            'collected_by' => auth()->id(),
            'note_data' => json_encode($this->cashNotes),
            'created_at' => now(),
        ]);

        CreditHistory::create(
            [
                'invoice_id' => null,
                'party_user_id' => $user->id ?? null,
                'credit_amount' => 0,
                'debit_amount' => $collectedAmount,
                'total_amount' => $collectedAmount,
                //'total_purchase_items' => $collectedAmount,
                'store_id' => $branch_id,
                'type' => 'debit',
                'transaction_kind' => 'collact_credit',
                'status' => 'paid',
                'created_by' => auth()->id(),
            ]
        );
        //DB::commit();

        $this->reset(['cashNotes', 'totals', 'selectedUser', 'showCollectModal']);
        $this->resetPage();
        //session()->flash('success', 'Credit collected successfully.');
        $this->dispatch('notiffication-sucess', ['message' => 'Credit collected successfully.']);

        // } catch (\Exception $e) {
        //     //DB::rollBack();
        //     //session()->flash('error', 'Error collecting credit: ' . $e->getMessage());
        //     $this->dispatch('notiffication-error', ['message' => 'Error collecting credit']);

        // }
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

        $partyUsers = $query->orderBy('use_credit', 'desc')->paginate(10);

        return view('livewire.collation-modal', [
            'partyUsers' => $partyUsers
        ]);
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

}
