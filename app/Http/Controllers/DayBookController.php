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

    $vouchers = Voucher::with(['lines.ledger'])
        ->whereBetween('voucher_date', [$from, $to])
        ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
        ->when($voucherType, fn($q) => $q->where('voucher_type', $voucherType))
        ->orderBy('voucher_date')
        ->orderBy('id')
        ->get();

    $entries = collect();

    foreach ($vouchers as $v) {

        $lines = $v->lines;

        // Decide which DC should appear first (Tally logic)
        $firstDc = match ($v->voucher_type) {
            'Payment'  => 'Cr',
            'Receipt'  => 'Dr',
            'Sales'    => 'Dr',
            'Purchase' => 'Cr',
            default    => null,
        };

        // Pick ONLY the first line
        $firstLine = $firstDc
            ? $lines->firstWhere('dc', $firstDc)
            : $lines->first();

        // Fallback if not found
        if (!$firstLine) {
            $firstLine = $lines->first();
        }

        if (!$firstLine) continue;

        $entries->push([
            'ledger_id'    => $v->id,
            'date'         => $v->voucher_date,
            'particulars'  => $firstLine->ledger->name ?? '---',
            'voucher_type' => $v->voucher_type,
            'voucher_no'   => $v->ref_no ?? $v->id,

            'debit'  => $firstLine->dc === 'Dr' ? $firstLine->amount : null,
            'credit' => $firstLine->dc === 'Cr' ? $firstLine->amount : null,
        ]);
    }

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
