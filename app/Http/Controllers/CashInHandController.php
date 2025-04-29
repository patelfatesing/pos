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
    
                $cashNotes[$parts[1]][$denomination]['in'] = $count;
                $total += ((int)$denomination) * (int)$count;
            }
        }

        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        $start = date('Y-m-d H:i:s'); // current time
        $end = date('Y-m-d H:i:s', strtotime('+8 hours 30 minutes'));
        $cashNotes = json_encode($cashNotes) ?? [];
        // 💾 Save cash breakdown
        $cashBreakdown = \App\Models\CashBreakdown::create([
            'user_id' => auth()->id(),
            'branch_id' => $branch_id,
            'type' =>"cashinhand",
            'denominations' => $cashNotes,
            'total' => $request->amount,
        ]);
        UserShift::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'branch_id' => $branch_id,
                'status'=>'pending'
            ],
            [
                'start_time' => $start,
                'end_time' => $end,
                'opening_cash' => $request->amount,
                'cash_break_id' => $cashBreakdown->id,
            ]
        );

        return redirect()->route('items.cart')->with('notification-sucess', 'Cash in hand saved.');
        // return redirect()->back()->with('notification-sucess', 'Cash in hand saved.');
    }

}
