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
            'amount' => 'required',
        ]);

        $withdrawAmount = $request->amount;
        $branch_id = auth()->user()->userinfo->branch->id ?? null;
        $user_id = auth()->id();

        $shift = UserShift::where('user_id', $user_id)
            ->where('branch_id', $branch_id)
            ->where('status', 'pending')
            ->whereDate('start_time', now()->toDateString())
            ->first();

        if (!$shift) {
            return redirect()->back()->with('error', 'User shift not found!');
        }

        // total invoice amount for this user and branch
        $totalInvoicedAmount = \App\Models\Invoice::where('user_id', $user_id)
            ->where('branch_id', $branch_id)
            ->sum('total');
        // check if withdraw exceeds available balance (after invoices)
        $availableBalance = $totalInvoicedAmount;

        // if ($withdrawAmount > $availableBalance) {
        //     return redirect()->back()->with('error', 'Withdrawal exceeds available balance!');

        // }

        // proceed with withdrawal
        //  $shift->opening_cash -= $withdrawAmount;
        $shift->save();
        // $user = User::find($user_id);
        // $user->is_login = 'No';
        // $user->save();
        // Auth::logout();

        $cashNotes = [];
        $total = 0;

        foreach ($data as $key => $value) {
            if (Str::startsWith($key, 'withcashNotes_')) {
                $parts = explode('_', $key);
                $denomination = (string) end($parts); // Ensure it's a string
                $count = (string) (int)$value;         // Convert to string after casting to int

                $cashNotes[@$parts[1]][$denomination]['out'] = $count;
                $total += ((int)$denomination) * (int)$count;
            }
        }

        // Save cash breakdown
        $CashBreakdown = CashBreakdown::create([
            'user_id' => $user_id,
            'branch_id' => $branch_id,
            'denominations' => json_encode($cashNotes),
            'total' => $total,
            'type' => "withdraw",

        ]);
        // Save shift close info
        $with = new WithdrawCash();
        $with->user_id = $user_id;
        $with->branch_id = $branch_id;
        $with->amount = $request->amount;
        $with->note = $request->narration;
        $with->cash_break_id = $CashBreakdown->id;
        $with->save();

        $exp_cate = ExpenseCategory::find($request->narration);

        
        $expense = new Expense();
        $expense->user_id = $user_id;
        $expense->branch_id = $branch_id;
        $expense->amount = $request->amount;
        $expense->description = $request->withdraw_notes;
        $expense->expense_category_id  = $request->narration;
        $expense->title = $exp_cate->name ?? 'Withdrawal';
        $expense->expense_date = Carbon::parse($expense->expense_date);
        $expense->verify = 'No';
        $expense->save();

        return redirect()->back()->with('notification-sucess', 'Amount withdrawn successfully.');
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
}
