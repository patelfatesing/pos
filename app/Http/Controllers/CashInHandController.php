<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashInHand;
class CashInHandController extends Controller
{
    //
    // app/Http/Controllers/CashInHandController.php
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        CashInHand::updateOrCreate(
            ['user_id' => auth()->id(), 'date' => today()],
            ['amount' => $request->amount]
        );

        return redirect()->back()->with('success', 'Cash in hand saved.');
    }

}
