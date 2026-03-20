<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserShift; // Create this model if not already
use App\Models\WithdrawCash;
use App\Models\CashBreakdown;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\ShiftClosing;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use App\Models\Branch;
use App\Models\Accounting\AccountLedger;
use App\Models\Accounting\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShiftClosingController extends Controller
{
    public function store(Request $request)
    {
        if (empty($request->closingCash)) {

            return redirect()->back()->with('notification-error', 'Closing cash is required and must be numeric.');
        }
        if (empty($request->start_time)) {
            return redirect()->back()->with('notification-error', 'Start time is required and must be numeric.');
        }


        // $validated = $request->validate([
        //     'opening_cash' => 'required',
        //     'closingCash' => 'required',
        //     'cash_discrepancy' => 'required|numeric',
        //     'cash' => 'required|numeric',

        // ]);
        $user_id = auth()->id();

        // Get the branch_id from the authenticated user's info
        $branch_id = auth()->user()->userinfo->branch->id ?? null;
        // Save cash breakdown
        $CashBreakdown = CashBreakdown::create([
            'user_id' => $user_id,
            'branch_id' => $branch_id,
            'denominations' => json_encode($request->cash_breakdown),
            'total' => $request->today_cash,
        ]);

        // Save shift close info
        $shift = UserShift::where('user_id', $user_id)
            ->where('branch_id', $branch_id)
            ->where('status', 'pending')
            ->first();
        if (!$shift) {
            return redirect()->back()->withErrors(['status' => 'No active shift found for this user.']);
        }
        // Update shift data
        $shift->user_id = $user_id;
        $shift->branch_id = $branch_id;
        $shift->start_time = $request->start_time;
        $shift->end_time = $request->end_time;
        $shift->opening_cash = str_replace([',', '₹'], '', $request->opening_cash);
        $shift->cash_discrepancy = str_replace([',', '₹'], '', $request->diffCash);
        $shift->closing_cash = str_replace([',', '₹'], '', $request->closingCash);
        $shift->cash_break_id = $CashBreakdown->id;
        $shift->deshi_sales = str_replace([',', '₹'], '', @$request->DESI ?? 0); // Using @ to suppress any potential error (if variable is not set)
        $shift->beer_sales = str_replace([',', '₹'], '', @$request->BEER ?? 0);
        $shift->english_sales = str_replace([',', '₹'], '', $request->ENGLISH ?? 0);
        $shift->upi_payment = str_replace([',', '₹'], '', $request->UPI_PAYMENT ?? 0);
        $shift->withdrawal_payment = str_replace([',', '₹'], '', $request->WITHDRAWAL_PAYMENT ?? 0);
        $shift->cash = str_replace([',', '₹'], '', $request->diffCash); // Assuming you want to store the same cash discrepancy here
        $shift->status = 'completed';  // Assuming you want to mark it as closed after shift ends
        $shift->save();

        $user = User::find($user_id);
        $user->is_login = 'No';
        $user->save();
        Auth::logout();
        return redirect()->route('login')->with('success', 'Shift closed. You have been logged out.');

        // Redirect to login with a status message
        //  return redirect('/login')->with('notification-sucess', 'Shift closed. You have been logged out.');

    }

    public function withdraw(Request $request)
    {

        $data = $request->all();

        $request->validate([
            'narration' => 'required',
            'amount' => 'required|numeric|min:1',
        ]);

        $branch_id = auth()->user()->userinfo->branch->id ?? null;
        $user_id   = auth()->id();

        $store = Branch::select('in_out_enable')->findOrFail($branch_id);
        $inOutStatus = $store->in_out_enable;

        // ==============================
        // ✅ CHECK AVAILABLE CASH
        // ==============================
        if (!$inOutStatus) {

            $start_date = date('Y-m-d');
            $end_date   = date('Y-m-d') . ' 23:59:59';

            $inTotal = CashBreakdown::where('user_id', $user_id)
                ->where('branch_id', $branch_id)
                ->whereIn('type', ['cashinhand', 'cash'])
                ->whereBetween('created_at', [$start_date, $end_date])
                ->sum('total');

            $outTotal = CashBreakdown::where('user_id', $user_id)
                ->where('branch_id', $branch_id)
                ->whereIn('type', ['withdraw', 'expense'])
                ->whereBetween('created_at', [$start_date, $end_date])
                ->sum('total');

            $availableBalance = $inTotal - $outTotal;

            if ($request->amount > $availableBalance) {
                return back()->withErrors([
                    'amount' => '❌ Not enough cash available. Current balance: ₹' . number_format($availableBalance, 2)
                ])->withInput();
            }
        }

        try {

            DB::beginTransaction();

          

            // ==============================
            // ✅ CHECK SHIFT
            // ==============================
            $shift = UserShift::where('user_id', $user_id)
                ->where('branch_id', $branch_id)
                ->where('status', 'pending')
                ->whereDate('start_time', now()->toDateString())
                ->first();

            if (!$shift) {
                return back()->with('error', 'User shift not found!');
            }

            // ==============================
            // ✅ CASH DENOMINATION
            // ==============================
            $cashNotes = [];
            $total = 0;

            if ($inOutStatus) {
                foreach ($data as $key => $value) {
                    if (Str::startsWith($key, 'withcashNotes_')) {
                        $parts = explode('_', $key);
                        $denomination = (int) end($parts);
                        $count = (int) $value;

                        $cashNotes[@$parts[1]][$denomination]['out'] = $count;
                        $total += $denomination * $count;
                    }
                }
            } else {
                $total = $request->amount;
            }

            // ==============================
            // ✅ SAVE CASH BREAKDOWN
            // ==============================
            $CashBreakdown = CashBreakdown::create([
                'user_id' => $user_id,
                'branch_id' => $branch_id,
                'denominations' => json_encode($cashNotes),
                'total' => $total,
                'type' => "withdraw",
            ]);

            // ==============================
            // ✅ SAVE WITHDRAW
            // ==============================
            $with = WithdrawCash::create([
                'user_id' => $user_id,
                'branch_id' => $branch_id,
                'amount' => $request->amount,
                'note' => $request->withdraw_notes,
                'cash_break_id' => $CashBreakdown->id,
            ]);

            // ==============================
            // ✅ SAVE EXPENSE
            // ==============================
            $exp_cate = ExpenseCategory::find($request->narration);

            Expense::create([
                'user_id' => $user_id,
                'branch_id' => $branch_id,
                'amount' => $request->amount,
                'description' => $request->withdraw_notes,
                'expense_category_id' => $request->narration,
                'title' => $exp_cate->name ?? 'Withdrawal',
                'expense_date' => now(),
                'verify' => 'No',
            ]);

            // ==============================
            // ✅ LEDGER ENTRIES (TALLY STYLE)
            // ==============================
            $lines = [];

            $amount = round((float) $request->amount, 2);

            // 🔹 CASH LEDGER (Cr)
            $cashLedger = AccountLedger::where('name', 'CASH')->firstOrFail();

            $lines[] = [
                'ledger_id'      => (int) $cashLedger->id,
                'dc'             => 'Cr',
                'amount'         => $amount,
                'line_narration' => 'Cash withdrawn',
            ];

            // 🔹 BRANCH EXPENSE LEDGER (Dr)
            $branch = Branch::findOrFail($branch_id);

            $ledgerName = strtoupper($branch->name) . ' Expense';

            $expenseLedger = AccountLedger::where('name', $ledgerName)
                ->where('branch_id', $branch_id)
                ->first();

            if (!$expenseLedger) {
                $expenseLedger = AccountLedger::create([
                    'name' => $ledgerName,
                    'group_id' => 11, // Expense group
                    'opening_balance' => 0,
                    'debit_credit' => 'Dr',
                    'branch_id' => $branch_id,
                    'created_by' => $user_id,
                ]);
            }

            $lines[] = [
                'ledger_id'      => (int) $expenseLedger->id,
                'dc'             => 'Dr',
                'amount'         => $amount,
                'line_narration' => $request->withdraw_notes ?? 'Branch expense',
            ];

            // ==============================
            // ✅ BALANCE CHECK
            // ==============================
            $dr = collect($lines)->where('dc', 'Dr')->sum('amount');
            $cr = collect($lines)->where('dc', 'Cr')->sum('amount');

            if (round($dr, 2) !== round($cr, 2)) {
                throw new \Exception("Withdraw voucher not balanced: Dr={$dr} Cr={$cr}");
            }

            // ==============================
            // ✅ REF NO
            // ==============================
            $prefix = "EXP-" . $branch_id . "-";

            $lastRef = Voucher::where('voucher_type', 'Payment')
                ->where('branch_id', $branch_id)
                ->where('ref_no', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->value('ref_no');

            $nextNumber = $lastRef
                ? ((int) str_replace($prefix, '', $lastRef)) + 1
                : 1;

            $nextRef = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            // ==============================
            // ✅ PAYLOAD (SAME AS SALES)
            // ==============================
            $payload = [
                'voucher_date'    => now()->format('Y-m-d'),
                'voucher_type'    => 'Payment',
                'branch_id'       => $branch_id,
                'ref_no'          => $nextRef,
                'narration'       => $request->withdraw_notes ?? 'Cash withdrawal',

                'party_ledger_id' => null,

                'mode'            => 'cash',
                'cash_ledger_id'  => $cashLedger->id,

                'sub_total'       => $amount,
                'discount'        => 0,
                'tax'             => 0,
                'grand_total'     => $amount,

                'lines'           => $lines,
            ];

            // ==============================
            // ✅ CREATE VOUCHER (MAIN ENTRY)
            // ==============================
            $voucher = $this->posTransaction($payload);

            DB::commit();

            return back()->with('notification-success', 'Amount withdrawn successfully.');
        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error('Withdraw failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', $e->getMessage());
        }
    }

    public function getShiftSummary($shiftId)
    {
        $shift = ShiftClosing::findOrFail($shiftId);

        // Fetch all invoices within the shift timing for the same branch
        $invoices = Invoice::with('cashBreakdown')
            ->where('branch_id', $shift->branch_id)
            ->whereBetween('created_at', [$shift->shift_open_time, $shift->shift_close_time])
            ->get();

        // Prepare initial values
        $denominationCounts = [
            '10' => 0,
            '20' => 0,
            '50' => 0,
            '100' => 0,
            '200' => 0,
            '500' => 0,
        ];

        $summary = [
            'opening_cash' => $shift->opening_cash ?? 0,
            'total_sales' => 0,
            'discount' => 0,
            'withdrawal_payment' => 0,
            'upi_payment' => 0,
            'total_cash' => 0,
            'refund' => 0,
            'credit' => 0,
            'refund_credit' => 0,
        ];

        foreach ($invoices as $invoice) {
            $summary['total_sales'] += $invoice->sub_total ?? 0;
            $summary['discount'] += $invoice->discount ?? 0;
            $summary['upi_payment'] += $invoice->upi_amount ?? 0;
            $summary['total_cash'] += $invoice->cash_amount ?? 0;
            $summary['refund'] += ($invoice->status === 'refund' ? $invoice->total : 0);
            $summary['credit'] += $invoice->creditpay ?? 0;
            $summary['refund_credit'] += $invoice->refund_credit ?? 0;

            $denoms = json_decode(optional($invoice->cashBreakdown)->denominations, true);
            if (is_array($denoms)) {
                foreach ($denominationCounts as $note => $_) {
                    $denominationCounts[$note] += $denoms[$note] ?? 0;
                }
            }
        }

        // Calculate total physical cash from note counts
        $denominationTotals = [];
        $physicalCashTotal = 0;
        foreach ($denominationCounts as $note => $count) {
            $total = intval($note) * $count;
            $denominationTotals[] = [
                'note' => $note,
                'count' => $count,
                'value' => $total,
            ];
            $physicalCashTotal += $total;
        }

        // Calculate discrepancy
        $discrepancy = $physicalCashTotal - $summary['total_cash'];

        // Final return array
        return response()->json([
            'shift' => [
                'id' => $shift->id,
                'branch_id' => $shift->branch_id,
                'start_time' => $shift->shift_open_time,
                'end_time' => $shift->shift_close_time,
            ],
            'summary' => $summary,
            'denominations' => $denominationTotals,
            'system_cash_sales' => $summary['total_cash'],
            'counted_cash' => $physicalCashTotal,
            'discrepancy_cash' => $discrepancy,
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
}
