<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserShift; // Create this model if not already
use App\Models\ShiftCategory;
use App\Models\CashBreakdown;
use Illuminate\Support\Facades\Auth;


class ShiftClosingController extends Controller
{
    public function store(Request $request)
    {
        $branch_id = auth()->user()->userinfo->branch->id ?? null;
        // Save cash breakdown
        $CashBreakdown = CashBreakdown::create([
            'user_id' => Auth::id(),
            'branch_id' => $branch_id,
            'denominations' => json_encode($request->cash_breakdown),
            'total' => $request->today_cash,
        ]);
        // Save shift close info
        $shift = new UserShift();
        $shift->user_id = Auth::id();
        $shift->branch_id = $branch_id;
        $shift->start_time = $request->start_time;
        $shift->end_time = $request->end_time;
        $shift->opening_cash = $request->opening_cash;
        // $shift->today_cash = $request->today_cash;
        $shift->cash_break_id = $CashBreakdown->id;
        // $shift->total_payments = $request->total_payments;
        $shift->save();
        Auth::logout();

        // Invalidate session and regenerate token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to login
        return redirect('/login')->with('status', 'Shift closed. You have been logged out.');
    }
}
