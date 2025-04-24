<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserShift;
use Carbon\Carbon;
use Illuminate\Support\Str;
 

class CashInHandController extends Controller
{
    //
    // app/Http/Controllers/CashInHandController.php
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
           // 'cashNotes' => 'required|array',
        ]);
        $data = $request->all();
        $cashNotes = [];
        $total = 0;
    
        foreach ($data as $key => $value) {
            if (Str::startsWith($key, 'cashNotes_')) {
                $parts = explode('_', $key);
                $denomination = (string) end($parts); // Ensure it's a string
                $count = (string) (int)$value;         // Convert to string after casting to int
    
                $cashNotes[$denomination] = $count;
                $total += ((int)$denomination) * (int)$count;
            }
        }

        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        $start = Carbon::today();
        $end = $start->copy()->addHours(8)->addMinutes(30);
        $cashNotes = json_encode($cashNotes) ?? [];
        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        // 💾 Save cash breakdown
        $cashBreakdown = \App\Models\CashBreakdown::create([
            'user_id' => auth()->id(),
            'branch_id' => $branch_id,
            'type' =>'cashinhand',
            'denominations' => $cashNotes,
            'total' => $request->amount,
        ]);
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
                'opening_cash' => $request->amount,
                'cash_break_id' => $cashBreakdown->id,
            ]
        );
        
        return redirect()->back()->with('success', 'Cash in hand saved.');
    }

}
