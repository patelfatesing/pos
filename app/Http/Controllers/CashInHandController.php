<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserShift;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\DailyProductStock;


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

        $branch_id = (!empty(auth()->user()->userinfo->branch->id)) ? auth()->user()->userinfo->branch->id : "";
        if (empty($branch_id)) {
            return redirect('/login');
        }
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
            'type' => "cashinhand",
            'denominations' => $cashNotes,
            'total' => $request->amount,
        ]);
        $datePart = now()->format('dmy'); // e.g., 1106 for 11 June

        $lastShift = UserShift::where([
            'user_id' => auth()->id(),
            'branch_id' => $branch_id,
            //'status' => 'pending',
           // 'created_at' => Carbon::now(),
            ])
            ->whereDate('created_at', now())
            ->count() + 1;

        $branchName = auth()->user()->userinfo->branch->name ?? '';
        $branchPrefix = strtoupper(substr(preg_replace('/\s+/', '', $branchName), 0, 2)); // First 2 letters, uppercase, no spaces

        $shiftNo = $branchPrefix ."-". $datePart . '-' . str_pad($lastShift, 2, '0', STR_PAD_LEFT);


        $userShift=UserShift::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'branch_id' => $branch_id,
                'status' => 'pending',
                'created_at' => Carbon::now(),
            ],
            [
                'start_time' => $start,
                'end_time' => $end,
                'shift_no'=>$shiftNo,
                'opening_cash' => $request->amount,
                'cash_break_id' => $cashBreakdown->id,
            ]
        );

        $lastShift = UserShift::getYesterdayShift(auth()->user()->id, $branch_id);

        $stocksQuery = DailyProductStock::with('product')
            ->where('branch_id', $branch_id);

        if (!empty($lastShift)) {
            // Match with shift_id
            $stocksQuery->where('shift_id', $lastShift->shift_id);
        } else {
            // Match where shift_id is null
            $stocksQuery->whereNull('shift_id');
        }
        
        $stocks = $stocksQuery->get();
        foreach ($stocks as $key) {
            $key->opening_stock=$key->closing_stock;
            $key->save();
            // DailyProductStock::updateOrCreate(
            //     [
            //         'product_id' => $key->product_id,
            //         'shift_id'=>$userShift->id,
            //         'branch_id' => $branch_id,
            //         'date' => Carbon::today(),
            //         'opening_stock' => $key->closing_stock,
            //     ]
            // );
        }

        //return redirect()->route('items.cart')->with('notification-sucess', 'Cash in hand saved.');
        return redirect()->back()->with('notification-sucess', 'Cash in hand saved.');
    }
}
