<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserShift;
use Carbon\Carbon;

class CashInHandController extends Controller
{
    //
    // app/Http/Controllers/CashInHandController.php
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        $start = Carbon::today();
        $end = $start->copy()->addHours(8)->addMinutes(30);

        UserShift::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'branch_id' => $branch_id,
                'start_time' => $start,
                'end_time' => $end,
                'closing_cash' => 0,
                'deshi_sales' => 0,
            ],
            [
                'opening_cash' => $request->amount
            ]
        );
        
        return redirect()->back()->with('success', 'Cash in hand saved.');
    }

}
