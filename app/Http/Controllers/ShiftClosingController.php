<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserShift; // Create this model if not already
use App\Models\WithdrawCash;
use App\Models\CashBreakdown;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class ShiftClosingController extends Controller
{
    public function store(Request $request)
    {
        $branch_id = auth()->user()->userinfo->branch->id ?? null;
        // Save cash breakdown
        // $CashBreakdown = CashBreakdown::create([
        //     'user_id' => Auth::id(),
        //     'branch_id' => $branch_id,
        //     'denominations' => json_encode($request->cash_breakdown),
        //     'total' => $request->today_cash,
        // ]);
        // // Save shift close info
        // $shift = new UserShift();
        // $shift->user_id = Auth::id();
        // $shift->branch_id = $branch_id;
        // $shift->start_time = $request->start_time;
        // $shift->end_time = $request->end_time;
        // $shift->opening_cash = $request->opening_cash;
        // // $shift->today_cash = $request->today_cash;
        // $shift->cash_break_id = $CashBreakdown->id;
        // // $shift->total_payments = $request->total_payments;
        // $shift->save();
        $shift = UserShift::where('user_id', $user_id)
        ->where('branch_id', $branch_id)
        ->delete();
        Auth::logout();

        // Invalidate session and regenerate token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to login
        return redirect('/login')->with('status', 'Shift closed. You have been logged out.');
    }
    public function withdraw(Request $request){
        $data = $request->all();
        $request->validate([
            'narration' => 'required',
            'amount' => 'required',
        ]);
        $withdrawAmount=$request->amount;
        $branch_id = auth()->user()->userinfo->branch->id ?? null;
        $user_id=Auth::id();
        
        $shift = UserShift::where('user_id', $user_id)
        ->where('branch_id', $branch_id)
        ->first();

        if (!$shift) {
            return redirect()->back()->with('error', 'User shift not found!');
        }

        // total invoice amount for this user and branch
        $totalInvoicedAmount = \App\Models\Invoice::where('user_id', $user_id)
        ->where('branch_id', $branch_id)
        ->sum('total');
        // check if withdraw exceeds available balance (after invoices)
        $availableBalance =$totalInvoicedAmount;

        if ($withdrawAmount > $availableBalance) {
            return redirect()->back()->with('error', 'Withdrawal exceeds available balance!');

        }

        // proceed with withdrawal
        $shift->opening_cash -= $withdrawAmount;
        $shift->save();

        $cashNotes = [];
        $total = 0;
    
        foreach ($data as $key => $value) {
            if (Str::startsWith($key, 'withcashNotes_')) {
                $parts = explode('_', $key);
                $denomination = (string) end($parts); // Ensure it's a string
                $count = (string) (int)$value;         // Convert to string after casting to int
    
                $cashNotes[$denomination] = $count;
                $total += ((int)$denomination) * (int)$count;
            }
        }

        $branch_id = auth()->user()->userinfo->branch->id ?? null;
        // Save cash breakdown
        $CashBreakdown = CashBreakdown::create([
            'user_id' => Auth::id(),
            'branch_id' => $branch_id,
            'denominations' => json_encode($cashNotes),
            'total' => $total,
        ]);
        // Save shift close info
        $with = new WithdrawCash();
        $with->user_id = Auth::id();
        $with->branch_id = $branch_id;
        $with->amount = $request->amount;
        $with->note = $request->narration;
        $with->cash_break_id = $CashBreakdown->id;
        $with->save();

        return redirect()->back()->with('success', 'Amount withdrawn successfully.');

    }

}
