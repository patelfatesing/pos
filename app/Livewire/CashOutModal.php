<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Accounting\AccountLedger;

class CashOutModal extends Component
{
    public $amount;
    public $expense_category_id;
    public $withdraw_notes;
    public $cashNotes = [];
    public $inOutStatus;
    public  $narrations = [];
    public $showCashOutModal = false;
    public $sub_total = 0;
    // ✅ FIXED EVENT NAME
    protected $listeners = ['cashOutModal' => 'open'];

    // ==============================
    // ✅ OPEN MODAL
    // ==============================
    public function open()
    {
        $this->reset(['amount', 'expense_category_id', 'withdraw_notes']);
        $this->dispatch('show-cash-modal');
    }

    // ==============================
    // ✅ WITHDRAW LOGIC
    // ==============================
    public function withdraw()
    {
        $this->validate([
            'expense_category_id' => 'required',
            'amount' => 'required|numeric|min:1',
        ]);

        try {

            DB::beginTransaction();

            $branch_id = auth()->user()->userinfo->branch->id;
            $user_id   = auth()->id();

            $store = \App\Models\Branch::select('in_out_enable')->findOrFail($branch_id);
            $this->inOutStatus = $store->in_out_enable;

            // ==============================
            // ✅ CHECK BALANCE
            // ==============================
            if (!$this->inOutStatus) {

                $start_date = now()->startOfDay();
                $end_date   = now()->endOfDay();

                $inTotal = \App\Models\CashBreakdown::where('user_id', $user_id)
                    ->where('branch_id', $branch_id)
                    ->whereIn('type', ['cashinhand', 'cash'])
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->sum('total');

                $outTotal = \App\Models\CashBreakdown::where('user_id', $user_id)
                    ->where('branch_id', $branch_id)
                    ->whereIn('type', ['withdraw', 'expense'])
                    ->whereBetween('created_at', [$start_date, $end_date])
                    ->sum('total');

                $availableBalance = $inTotal - $outTotal;

                if ($this->amount > $availableBalance) {
                    $this->addError('amount', '❌ Not enough cash. Balance: ₹' . number_format($availableBalance, 2));
                    return;
                }
            }

            // ==============================
            // ✅ SHIFT CHECK
            // ==============================
            $shift = \App\Models\UserShift::where('user_id', $user_id)
                ->where('branch_id', $branch_id)
                ->where('status', 'pending')
                ->whereDate('start_time', now())
                ->first();

            if (!$shift) {
                $this->addError('amount', 'User shift not found!');
                return;
            }

            // ==============================
            // ✅ SAVE CASH BREAKDOWN
            // ==============================
            $cashBreak = \App\Models\CashBreakdown::create([
                'user_id' => $user_id,
                'branch_id' => $branch_id,
                'denominations' => json_encode($this->cashNotes),
                'total' => $this->amount,
                'type' => "withdraw",
            ]);

            // ==============================
            // ✅ SAVE WITHDRAW
            // ==============================
            \App\Models\WithdrawCash::create([
                'user_id' => $user_id,
                'branch_id' => $branch_id,
                'amount' => $this->amount,
                'note' => $this->withdraw_notes,
                'cash_break_id' => $cashBreak->id,
            ]);

            // ==============================
            // ✅ EXPENSE
            // ==============================
            $exp = \App\Models\ExpenseCategory::find($this->expense_category_id);

            \App\Models\Expense::create([
                'user_id' => $user_id,
                'branch_id' => $branch_id,
                'amount' => $this->amount,
                'description' => $this->withdraw_notes,
                'expense_category_id' => $this->expense_category_id,
                'title' => $exp->name ?? 'Withdrawal',
                'expense_date' => now(),
                'verify' => 'No',
            ]);

            // ==============================
            // ✅ LEDGER ENTRY
            // ==============================
            $amount = round((float) $this->amount, 2);

            $cashLedger = \App\Models\AccountLedger::where('name', 'CASH')->firstOrFail();

            $branch = \App\Models\Branch::findOrFail($branch_id);
            $ledgerName = strtoupper($branch->name) . ' Expense';

            $expenseLedger = \App\Models\AccountLedger::firstOrCreate(
                ['name' => $ledgerName, 'branch_id' => $branch_id],
                [
                    'group_id' => 11,
                    'opening_balance' => 0,
                    'debit_credit' => 'Dr',
                    'created_by' => $user_id,
                ]
            );

            $lines = [
                [
                    'ledger_id' => $cashLedger->id,
                    'dc' => 'Cr',
                    'amount' => $amount,
                    'line_narration' => 'Cash withdrawn',
                ],
                [
                    'ledger_id' => $expenseLedger->id,
                    'dc' => 'Dr',
                    'amount' => $amount,
                    'line_narration' => $this->withdraw_notes ?? 'Expense',
                ],
            ];

            // ==============================
            // ✅ REF NO
            // ==============================
            $prefix = "EXP-" . $branch_id . "-";

            $lastRef = \App\Models\Voucher::where('voucher_type', 'Payment')
                ->where('branch_id', $branch_id)
                ->where('ref_no', 'like', $prefix . '%')
                ->latest()
                ->value('ref_no');

            $nextRef = $prefix . str_pad(
                $lastRef ? ((int) str_replace($prefix, '', $lastRef)) + 1 : 1,
                4,
                '0',
                STR_PAD_LEFT
            );

            // ==============================
            // ✅ VOUCHER
            // ==============================
            app()->call('App\Http\Controllers\Controller@posTransaction', [
                'payload' => [
                    'voucher_type' => 'Payment',
                    'branch_id' => $branch_id,
                    'ref_no' => $nextRef,
                    'voucher_date' => now()->format('Y-m-d'),
                    'mode' => 'cash',
                    'cash_ledger_id' => $cashLedger->id,
                    'sub_total' => $amount,
                    'grand_total' => $amount,
                    'lines' => $lines,
                ]
            ]);

            DB::commit();

            session()->flash('success', 'Payment voucher created: ' . $nextRef);

            // ✅ CLOSE MODAL
            $this->dispatch('close-cashout-modal');

            // ✅ RESET
            $this->reset(['amount', 'expense_category_id', 'withdraw_notes']);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('amount', $e->getMessage());
        }
    }


    public function cashOutModal()
    {
        //$this->selectedUser = PartyUser::where('status', 'Active')->find($userId);
        $this->cashNotes = []; // Reset
        $this->calculateTotals();
        $this->showCashOutModal = true;
    }

    public function calculateTotals()
    {
        $this->sub_total = $this->cartitems->sum(
            fn($item) =>
            !empty($item->product->sell_price)
                ? $item->mrp * $item->quantity
                : 0
        );

        //$this->tax = $this->sub_total * 0.18;
        //$this->cashAmount = $this->total;
        // $this->remainingAmount = $this->cashAmount;
    }

    public function render()
    {
        $this->narrations  = AccountLedger::query()
            ->leftJoin('account_groups as g', 'g.id', '=', 'account_ledgers.group_id')
            ->whereIn('g.name', ['Indirect Expenses', 'Indirect Expense', 'Expense - Indirect']) // adjust names
            ->where(function ($q) {
                $q->where('account_ledgers.is_deleted', 'No')->orWhereNull('account_ledgers.is_deleted');
            })
            ->where(function ($q) {
                $q->where('account_ledgers.is_active', 1)->orWhere('account_ledgers.is_active', 'Yes');
            })
            ->orderBy('account_ledgers.name')
            ->pluck('account_ledgers.name', 'account_ledgers.id')
            ->toArray();

        return view('livewire.cash-out-modal');
    }
}
