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

    public function index(Request $request)
    {
        $fromDate = $request->input('from_date', now()->toDateString());
        $toDate   = $request->input('to_date', now()->toDateString());

        $from = Carbon::parse($fromDate)->startOfDay();
        $to   = Carbon::parse($toDate)->endOfDay();

        $branchId    = $request->input('branch_id');
        $voucherType = $request->input('voucher_type');

        // Fetch vouchers WITH lines
        $vouchers = Voucher::with(['lines.ledger'])
            ->whereBetween('voucher_date', [$from, $to])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($voucherType, fn($q) => $q->where('voucher_type', $voucherType))
            ->orderBy('voucher_date')
            ->orderBy('id')
            ->get();

        /**
         * Build DAY BOOK rows (LINE WISE – like Tally)
         */
        $entries = collect();

        foreach ($vouchers as $v) {
            foreach ($v->lines as $line) {
                $entries->push([
                    'date'         => $v->voucher_date,
                    'particulars'  => $line->ledger->name ?? '---',
                    'voucher_type' => $v->voucher_type,
                    'voucher_no'   => $v->ref_no ?? $v->id,

                    // Show amount in ONLY one column
                    'debit'  => $line->dc === 'Dr' ? $line->amount : null,
                    'credit' => $line->dc === 'Cr' ? $line->amount : null,
                ]);
            }
        }

        // Totals
        $totalDebit  = $entries->sum('debit');
        $totalCredit = $entries->sum('credit');

        return view('reports.day_book', compact(
            'entries',
            'fromDate',
            'toDate',
            'totalDebit',
            'totalCredit'
        ));
    }

    /**
     * AJAX: return voucher detail HTML for modal.
     * Route: GET /reports/day-book/voucher/{id}
     */
    public function voucher(Request $request, $id)
    {
        $voucher = Voucher::with(['lines.ledger', 'branch', 'createdBy']) // adjust relations as available
            ->findOrFail($id);

        // Render a blade partial (create resources/views/reports/_voucher_detail.blade.php)
        $html = view('reports._voucher_detail', [
            'voucher' => $voucher
        ])->render();

        return response()->json([
            'title' => $voucher->voucher_type . ' - ' . ($voucher->ref_no ?? ''),
            'html'  => $html,
        ]);
    }
}
