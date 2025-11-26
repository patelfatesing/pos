<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Payment; // if you have separate payments
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use App\Models\Accounting\{Voucher, VoucherLine, AccountLedger};

class DayBookController extends Controller
{
    public function index(Request $request)
    {
        // Date filters
        $fromDate = $request->input('from_date', now()->toDateString());
        $toDate   = $request->input('to_date', now()->toDateString());

        $from = Carbon::parse($fromDate)->startOfDay();
        $to   = Carbon::parse($toDate)->endOfDay();

        // Branch filter (optional)
        $branchId = $request->input('branch_id');

        // Voucher types filter (optional)
        $voucherType = $request->input('voucher_type');

        // ---- MAIN TALLY-LIKE DAY BOOK QUERY ----
        $vouchers = Voucher::with(['lines.ledger'])
            ->whereBetween('voucher_date', [$from, $to])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($voucherType, fn($q) => $q->where('voucher_type', $voucherType))
            ->orderBy('voucher_date')
            ->orderBy('id')
            ->get();

        // Build Tally-style line rows
        $entries = collect();

        foreach ($vouchers as $v) {
            foreach ($v->lines as $line) {
                $entries->push([
                    'voucher_id'   => $v->id,
                    'voucher_type' => $v->voucher_type,
                    'ref_no'       => $v->ref_no,
                    'date'         => $v->voucher_date,
                    'ledger'       => $line->ledger->name,
                    'dc'           => $line->dc,
                    'debit'        => $line->dc === 'Dr' ? $line->amount : 0,
                    'credit'       => $line->dc === 'Cr' ? $line->amount : 0,
                    'remarks'      => $v->narration,
                ]);
            }
        }

        $entries = $entries->sortBy('date')->values();

        // Totals
        $totalDebit  = $entries->sum('debit');
        $totalCredit = $entries->sum('credit');

        // Opening balance (optional)
        $openingBalance = (float) $request->input('opening_balance', 0);
        $closingBalance = $openingBalance + $totalDebit - $totalCredit;

        return view('reports.day_book', [
            'entries'        => $entries,
            'fromDate'       => $fromDate,
            'toDate'         => $toDate,
            'branchId'       => $branchId,
            'voucherType'    => $voucherType,
            'openingBalance' => $openingBalance,
            'totalDebit'     => $totalDebit,
            'totalCredit'    => $totalCredit,
            'closingBalance' => $closingBalance,
        ]);
    }

    // public function showVoucher($type, $id)
    // {
    //     switch ($type) {
    //         case 'invoice':
    //             $voucher = Invoice::findOrFail($id);
    //             $view = 'reports.day_book_voucher_invoice';
    //             $title = 'Invoice #' . ($voucher->invoice_number ?? $voucher->id);
    //             break;

    //         case 'expense':
    //             $voucher = Expense::findOrFail($id);
    //             $view = 'reports.day_book_voucher_expense';
    //             $title = 'Expense Voucher #' . ($voucher->voucher_no ?? $voucher->id);
    //             break;

    //         // case 'payment':
    //         //     $voucher = \App\Models\Payment::findOrFail($id);
    //         //     $view = 'reports.day_book_voucher_payment';
    //         //     $title = 'Payment Voucher #' . ($voucher->voucher_no ?? $voucher->id);
    //         //     break;

    //         default:
    //             abort(404);
    //     }

    //     $html = View::make($view, compact('voucher'))->render();

    //     return response()->json([
    //         'title' => $title,
    //         'html'  => $html,
    //     ]);
    // }

    public function showVoucher($id)
    {
        $voucher = Voucher::with(['lines.ledger'])->findOrFail($id);

        $title = $voucher->voucher_type . ' Voucher #' . ($voucher->ref_no ?? $voucher->id);

        $html = view('reports.day_book_voucher_generic', compact('voucher'))->render();

        return response()->json([
            'title' => $title,
            'html'  => $html,
        ]);
    }
}
