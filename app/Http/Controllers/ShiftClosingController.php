<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserShift; // Create this model if not already
use App\Models\WithdrawCash;
use App\Models\CashBreakdown;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;

class ShiftClosingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'opening_cash' => 'required',
            'closingCash' => 'required',
            'diffCash' => 'nullable', // Cash discrepancy is optional but if provided, must be numeric
            
        ]);
        
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
        $shift->opening_cash = str_replace(',', '', $request->opening_cash);
        $shift->cash_discrepancy = str_replace(',', '', $request->diffCash);
        $shift->closing_cash = str_replace(',', '', $request->closingCash);
        $shift->cash_break_id = $CashBreakdown->id;
        $shift->deshi_sales = str_replace(',', '', @$request->Desi); // Using @ to suppress any potential error (if variable is not set)
        $shift->beer_sales = str_replace(',', '', $request->BEER_SALES);
        $shift->english_sales = str_replace(',', '', $request->ENGLISH_SALES);
        $shift->upi_payment = str_replace(',', '', $request->UPI_PAYMENT);
        $shift->withdrawal_payment = str_replace(',', '', $request->WITHDRAWAL_PAYMENT);
        $shift->cash = str_replace(',', '', $request->diffCash); // Assuming you want to store the same cash discrepancy here
        $shift->status = 'completed';  // Assuming you want to mark it as closed after shift ends
        $shift->save();
        $user = User::find($user_id);
        $user->is_login = 'No';
        $user->save();
        Auth::logout(); 
        // Logout user after shift closure
        Auth::logout();

        // Redirect to login with a status message
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
        $user_id=auth()->id();
        
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
    
                $cashNotes[$denomination]['in'] = $count;
                $total += ((int)$denomination) * (int)$count;
            }
        }

        // Save cash breakdown
        $CashBreakdown = CashBreakdown::create([
            'user_id' => $user_id,
            'branch_id' => $branch_id,
            'denominations' => json_encode($cashNotes),
            'total' => $total,
        ]);
        // Save shift close info
        $with = new WithdrawCash();
        $with->user_id = $user_id;
        $with->branch_id = $branch_id;
        $with->amount = $request->amount;
        $with->note = $request->narration;
        $with->cash_break_id = $CashBreakdown->id;
        $with->save();
        return redirect()->back()->with('notification-sucess', 'Amount withdrawn successfully.');

    }

}
