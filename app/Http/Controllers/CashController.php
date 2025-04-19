<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CashController extends Controller
{
    public function index()
    {
        return view('cash-tender');
    }

    public function calculateChange(Request $request)
    {
        $cash = $request->input('cash');
        $tender = $request->input('tender');
        $change = $tender - $cash;

        // Denominations of notes
        $notes = [2000, 500, 100, 50, 20, 10, 5, 1];
        $breakdown = [];

        if ($change >= 0) {
            foreach ($notes as $note) {
                if ($change >= $note) {
                    $quantity = floor($change / $note);
                    $breakdown[] = "$note x $quantity note(s)";
                    $change = $change % $note;
                }
            }
        }

        return response()->json(['change' => number_format($tender - $cash, 2), 'breakdown' => $breakdown]);
    }
}
