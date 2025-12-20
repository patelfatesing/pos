<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Accounting\{Voucher, VoucherLine, AccountLedger};

use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;


class CashBankReportController extends Controller
{
    // =============================
    // CASH & BANK SUMMARY (Tally)
    // =============================
    public function index(Request $request)
    {
        $from = $request->from_date
            ? Carbon::parse($request->from_date)
            : now()->startOfMonth();

        $to = $request->to_date
            ? Carbon::parse($request->to_date)
            : now();

        // Cash + Bank ledgers only
        $ledgers = AccountLedger::whereIn('group_id', [1, 2]) // Cash + Bank groups
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->get();

        $rows = [];

        foreach ($ledgers as $ledger) {

            // 🔹 Opening balance (Dr + / Cr -)
            $opening = $ledger->opening_type === 'Cr'
                ? -$ledger->opening_balance
                :  $ledger->opening_balance;

            // 🔹 Transactions before FROM date
            $before = VoucherLine::where('ledger_id', $ledger->id)
                ->whereHas('voucher', function ($q) use ($from) {
                    $q->whereDate('voucher_date', '<', $from);
                })
                ->select(
                    DB::raw('SUM(debit) as debit'),
                    DB::raw('SUM(credit) as credit')
                )->first();

            $opening += ($before->credit ?? 0) - ($before->debit ?? 0);

            // 🔹 Period totals
            $period = VoucherLine::where('ledger_id', $ledger->id)
                ->whereHas('voucher', function ($q) use ($from, $to) {
                    $q->whereBetween('voucher_date', [$from, $to]);
                })
                ->select(
                    DB::raw('SUM(debit) as debit'),
                    DB::raw('SUM(credit) as credit')
                )->first();

            $closing = $opening
                + ($period->credit ?? 0)
                - ($period->debit ?? 0);

            $rows[] = [
                'ledger'   => $ledger,
                'opening' => $opening,
                'receipt' => $period->credit ?? 0,
                'payment' => $period->debit ?? 0,
                'closing' => $closing,
            ];
        }

        return view('reports.cash_bank', compact('rows', 'from', 'to'));
    }

    // =============================
    // LEDGER DRILL-DOWN (Tally Enter)
    // =============================
    public function ledger(Request $request, AccountLedger $ledger)
    {
        $from = Carbon::parse($request->from_date);
        $to   = Carbon::parse($request->to_date);

        // Opening balance
        $balance = $ledger->opening_type === 'Cr'
            ? -$ledger->opening_balance
            :  $ledger->opening_balance;

        $before = VoucherLine::where('ledger_id', $ledger->id)
            ->whereHas('voucher', fn($q) =>
                $q->whereDate('voucher_date', '<', $from)
            )
            ->select(
                DB::raw('SUM(debit) as debit'),
                DB::raw('SUM(credit) as credit')
            )->first();

        $balance += ($before->credit ?? 0) - ($before->debit ?? 0);

        $entries = VoucherLine::with('voucher')
            ->where('ledger_id', $ledger->id)
            ->whereHas('voucher', fn($q) =>
                $q->whereBetween('voucher_date', [$from, $to])
            )
            ->orderBy('voucher_id')
            ->get();

        return view('reports.ledger', compact(
            'ledger', 'entries', 'balance', 'from', 'to'
        ));
    }
}
