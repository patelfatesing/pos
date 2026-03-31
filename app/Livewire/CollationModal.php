<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Partyuser;
use Livewire\WithPagination;
use App\Models\CreditHistory;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB; // ✅ CORRECT
use App\Models\Branch;
use App\Models\Accounting\AccountLedger;
use App\Models\Accounting\Voucher;

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
    public $inOutStatus = false;

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
        $this->totalCollected = 0;
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

    public function updatedAmount($value)
    {
        $this->totalCollected = $value;
    }

    public function submitCredit()
    {
        $totalOut = 0;
        $totalIn = 0;

        if ($this->paymentType === 'cash') {
            if (!$this->inOutStatus) {
                $collectedAmount = $this->amount;
            } else {
                $totalOut = $this->totals['totalOut'] ?? 0;
                $totalIn = $this->totals['totalIn'] ?? 0;

                $collectedAmount = $totalIn - $totalOut;
            }
            // validate cash breakdown logic


            if ($collectedAmount <= 0) {
                $this->dispatch('notiffication-error', ['message' => 'Collection amount must be greater than zero.']);

                return;
            }
        } elseif ($this->paymentType === 'online') {
            if (!$this->inOutStatus) {
                $collectedAmount = $this->amount;
            } else {
                $totalOut =  0;
                $totalIn = 0;
                $this->validate(['onlineAmount' => 'required|min:1']);
                $collectedAmount = $this->onlineAmount;
            }

            if ($collectedAmount <= 0) {
                $this->dispatch('notiffication-error', ['message' => 'Collection amount must be greater than zero.']);

                return;
            }
        } elseif ($this->paymentType === 'cash+upi') {
            $this->validate(['upiAmount' => 'required|min:1']);
            if (!$this->inOutStatus) {
                $collectedAmount = $this->amount;
            } else {
                $totalOut = $this->totals['totalOut'] ?? 0;
                $totalIn = $this->totals['totalIn'] ?? 0;

                $collectedAmount = $totalIn - $totalOut;

                if (!empty($this->upiAmount)) {
                    $collectedAmount = $collectedAmount + $this->upiAmount;
                }
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

        $case_amt = 0;
        if (!$this->inOutStatus) {
            $case_amt = $collectedAmount;
        } else {
            $case_amt = $totalIn - $totalOut;
        }

        // Optional: log this as a payment/collection entry
        \App\Models\CreditCollection::create([
            'party_user_id' => $user->id,
            'payment_method' => $this->paymentType,
            'cash_break_id' => $cashBreakdown->id ?? 0,
            'amount' => $collectedAmount,
            'upi_amount' => $this->upiAmount,
            'online_amount' => $this->onlineAmount,
            'cash_amount' => $case_amt,
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

        // =================== TALLY VOUCHER (CREDIT COLLECTION) ===================

        $lines = [];
        $cashLedgerId = null;

        // 1️⃣ Payment split
        $cashPaid = 0;
        $upiPaid  = 0;

        if ($this->paymentType === 'cash') {
            $cashPaid = round((float) $collectedAmount, 2);
        } elseif ($this->paymentType === 'online') {
            $upiPaid = round((float) $this->onlineAmount, 2);
        } elseif ($this->paymentType === 'cash+upi') {
            $cashPaid = round((float) ($totalIn - $totalOut), 2);
            $upiPaid  = round((float) $this->upiAmount, 2);
        }

        // 2️⃣ CASH DR
        if ($cashPaid > 0) {
            $cashLedger = AccountLedger::where('name', 'CASH')->firstOrFail();
            $cashLedgerId = $cashLedger->id;

            $lines[] = [
                'ledger_id' => $cashLedgerId,
                'dc' => 'Dr',
                'amount' => $cashPaid,
                'line_narration' => 'Cash received (credit collection)',
            ];
        }

        // 3️⃣ UPI DR
        if ($upiPaid > 0) {

            $branchData = Branch::where('branches.id', $branch_id)
                ->leftJoin('account_ledgers', 'branches.bank_ledger_id', '=', 'account_ledgers.id')
                ->select('account_ledgers.name as bank_ledger_name')
                ->firstOrFail();

            $upiLedger = AccountLedger::where('name', $branchData->bank_ledger_name)->firstOrFail();

            $lines[] = [
                'ledger_id' => $upiLedger->id,
                'dc' => 'Dr',
                'amount' => $upiPaid,
                'line_narration' => 'UPI received (credit collection)',
            ];
        }

        // 4️⃣ CUSTOMER CR (IMPORTANT - opposite of sales)
        $customerLedger = AccountLedger::where('name', $user->first_name)->first();

        if (!$customerLedger) {
            $customerLedger = AccountLedger::create([
                'name' => $user->first_name,
                'group_name' => 'Sundry Debtors',
                'group_id' => 19,
                'opening_balance' => 0,
                'debit_credit' => 'Dr',
                'created_by' => auth()->id(),
            ]);
        }

        $lines[] = [
            'ledger_id' => $customerLedger->id,
            'dc' => 'Cr', // 🔥 IMPORTANT (collection reduces receivable)
            'amount' => ($cashPaid + $upiPaid),
            'line_narration' => 'Credit collection from customer',
        ];

        // 5️⃣ DR/CR CHECK
        $dr = collect($lines)->where('dc', 'Dr')->sum('amount');
        $cr = collect($lines)->where('dc', 'Cr')->sum('amount');

        if (round($dr, 2) !== round($cr, 2)) {
            throw new \Exception("Voucher not balanced: Dr={$dr} Cr={$cr}");
        }

        // 6️⃣ REF NUMBER
        $prefix = "RCPT-" . $branch_id . "-";

        $lastRef = Voucher::where('voucher_type', 'Receipt')
            ->where('branch_id', $branch_id)
            ->where('ref_no', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('ref_no');

        $nextNumber = $lastRef ? ((int) str_replace($prefix, '', $lastRef) + 1) : 1;

        $nextRef = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // 7️⃣ MODE
        $mode = null;
        if ($cashPaid > 0 && $upiPaid > 0) {
            $mode = 'cash';
        } elseif ($cashPaid > 0) {
            $mode = 'cash';
        } elseif ($upiPaid > 0) {
            $mode = 'upi';
        }

        // 8️⃣ FINAL PAYLOAD
        $payload = [
            'voucher_date'    => now()->format('Y-m-d'),
            'voucher_type'    => 'Receipt', // 🔥 IMPORTANT
            'branch_id'       => $branch_id,
            'ref_no'          => $nextRef,
            'narration'       => 'Credit collection',

            'party_ledger_id' => $customerLedger->id,

            'mode'            => $mode,
            'cash_ledger_id'  => $cashLedgerId,

            'sub_total'       => ($cashPaid + $upiPaid),
            'discount'        => 0,
            'tax'             => 0,
            'grand_total'     => ($cashPaid + $upiPaid),

            'lines'           => $lines,
        ];

        // 9️⃣ SAVE VOUCHER
        $voucher = $this->posTransaction($payload);

        // LINK (optional)
        if ($voucher) {
            $voucher->gen_id = $user->id;
            $voucher->save();
        }

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
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";

        $store = Branch::select('in_out_enable', 'one_time_sales')->findOrFail($branch_id);
        $this->inOutStatus = $store->in_out_enable;

        return view('livewire.collation-modal', [
            'partyUsers' => $partyUsers
        ]);
    }

    public function posTransaction(array $arr_data)
    {

        $nv = function ($v) {
            return ($v === '' || $v === null) ? null : $v;
        };

        $type = $nv($arr_data['voucher_type'] ?? null);
        $mode = $nv($arr_data['mode'] ?? null);

        // Party normalization
        $partyFromPR = $nv($arr_data['party_ledger_id'] ?? null) ?: $nv($arr_data['pr_party_ledger'] ?? null);
        $partyFromTR = $nv($arr_data['party_ledger_id'] ?? null) ?: $nv($arr_data['tr_party_ledger'] ?? null);

        if (in_array($type, ['Payment', 'Receipt'])) {
            $party = $partyFromPR ?: $partyFromTR;
        } elseif (in_array($type, ['Sales', 'Purchase', 'DebitNote', 'CreditNote'])) {
            $party = $partyFromTR ?: $partyFromPR;
        } else {
            $party = null;
        }

        // Mode: cash / bank / upi / card
        $cashLedger = $nv($arr_data['cash_ledger_id'] ?? null) ?: $nv($arr_data['pr_cash_ledger'] ?? null);
        $bankLedger = $nv($arr_data['bank_ledger_id'] ?? null) ?: $nv($arr_data['pr_bank_ledger'] ?? null);

        if ($mode === 'cash') {
            $bankLedger = null;
        } elseif (in_array($mode, ['bank', 'upi', 'card'])) {
            $cashLedger = null;
        } else {
            $cashLedger = $bankLedger = null;
        }

        // Totals
        $subTotal   = $nv($arr_data['sub_total'] ?? $arr_data['tr_subtotal'] ?? null);
        $discount   = $nv($arr_data['discount'] ?? $arr_data['tr_discount'] ?? null);
        $tax        = $nv($arr_data['tax'] ?? $arr_data['tr_tax'] ?? null);
        $grandTotal = $nv($arr_data['grand_total'] ?? $arr_data['tr_grand'] ?? null);

        if (!$grandTotal && ($subTotal || $discount || $tax)) {
            $grandTotal = round(($subTotal ?? 0) - ($discount ?? 0) + ($tax ?? 0), 2);
        }

        $fromLedger = $nv($arr_data['from_ledger_id'] ?? $arr_data['ct_from'] ?? null);
        $toLedger   = $nv($arr_data['to_ledger_id'] ?? $arr_data['ct_to'] ?? null);
        $branchId   = $nv($arr_data['branch_id'] ?? null);
        $refNo      = $nv($arr_data['ref_no'] ?? null);

        // Lines array
        $lines = $arr_data['lines'] ?? [];
        if (count($lines) < 2) {
            throw new \Exception('At least two lines (Dr/Cr) are required.');
        }

        // --- Check Debit/Credit Balance ---
        $dr = 0;
        $cr = 0;
        foreach ($lines as $line) {
            if (($line['dc'] ?? '') === 'Dr') {
                $dr += (float)($line['amount'] ?? 0);
            } elseif (($line['dc'] ?? '') === 'Cr') {
                $cr += (float)($line['amount'] ?? 0);
            }
        }

        if (round($dr, 2) !== round($cr, 2)) {
            // throw new \Exception('Debit and Credit total mismatch.');
        }

        try {
            return DB::transaction(function () use (
                $arr_data,
                $type,
                $party,
                $mode,
                $cashLedger,
                $bankLedger,
                $fromLedger,
                $toLedger,
                $branchId,
                $refNo,
                $subTotal,
                $discount,
                $tax,
                $grandTotal,
                $lines
            ) {
                // ... existing create code ...

                $voucher = \App\Models\Accounting\Voucher::create([
                    'voucher_date'    => $arr_data['voucher_date'] ?? now(),
                    'voucher_type'    => $type,
                    'ref_no'          => $refNo,
                    'branch_id'       => $branchId,
                    'narration'       => $arr_data['narration'] ?? null,
                    'created_by'      => 1,
                    'party_ledger_id' => $party,
                    'mode'            => $mode,
                    'instrument_no'   => $arr_data['instrument_no'] ?? null,
                    'instrument_date' => $arr_data['instrument_date'] ?? null,
                    'cash_ledger_id'  => $cashLedger,
                    'bank_ledger_id'  => $bankLedger,
                    'from_ledger_id'  => $fromLedger,
                    'to_ledger_id'    => $toLedger,
                    'sub_total'       => $subTotal ?? 0,
                    'discount'        => $discount ?? 0,
                    'tax'             => $tax ?? 0,
                    'grand_total'     => $grandTotal ?? 0,
                ]);


                foreach ($lines as $line) {
                    $voucher->lines()->create([
                        'ledger_id'      => $line['ledger_id'],
                        'dc'             => $line['dc'],
                        'amount'         => round((float)$line['amount'], 2),
                        'line_narration' => $line['line_narration'] ?? null,
                    ]);
                }
                // dd($voucher);
                return $voucher;
            });
        } catch (QueryException $qe) {
            // DB-level error: show SQL + bindings + error info
            Log::error('posTransaction QueryException', [
                'message' => $qe->getMessage(),
                'sql'     => $qe->sql ?? null,
                'bindings' => $qe->bindings ?? null,
                'errorInfo' => $qe->errorInfo ?? null,
                'payload' => $arr_data,
            ]);
            throw $qe; // rethrow so Livewire / caller gets exception (or return/handle)
        } catch (\Throwable $e) {
            Log::error('posTransaction Exception', [
                'class' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $arr_data,
            ]);
            throw $e;
        }
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
