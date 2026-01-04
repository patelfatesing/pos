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

    public function cashBankSummary(Request $request)
    {
        $date = $request->date ?? now()->format('Y-m-d');

        $groups = [
            'Cash-in-Hand' => 18,
            'Bank Accounts' => 17,
        ];

        $result = [];

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($groups as $groupName => $groupId) {

            $ledgers = DB::table('account_ledgers')
                ->where('group_id', $groupId)
                ->where('is_active', 1)
                ->where('is_deleted', 0)
                ->get();

            $groupDebit = 0;
            $groupCredit = 0;
            $ledgerRows = [];

            foreach ($ledgers as $ledger) {

                $opening = $ledger->opening_balance;
                if ($ledger->opening_type === 'Cr') {
                    $opening *= -1;
                }

                $movement = DB::table('voucher_lines')
                    ->join('vouchers', 'vouchers.id', '=', 'voucher_lines.voucher_id')
                    ->where('voucher_lines.ledger_id', $ledger->id)
                    ->whereDate('vouchers.voucher_date', '<=', $date)
                    ->selectRaw("
                    SUM(CASE WHEN dc = 'Dr' THEN amount ELSE 0 END) as dr,
                    SUM(CASE WHEN dc = 'Cr' THEN amount ELSE 0 END) as cr
                ")
                    ->first();

                $balance = $opening
                    + ($movement->dr ?? 0)
                    - ($movement->cr ?? 0);

                $debit = $balance > 0 ? $balance : 0;
                $credit = $balance < 0 ? abs($balance) : 0;

                $groupDebit += $debit;
                $groupCredit += $credit;

                $ledgerRows[] = [
                    'id' => $ledger->id,
                    'name' => $ledger->name,
                    'debit' => $debit,
                    'credit' => $credit,
                ];
            }

            $totalDebit += $groupDebit;
            $totalCredit += $groupCredit;

            $result[] = [
                'group' => $groupName,
                'debit' => $groupDebit,
                'credit' => $groupCredit,
                'ledgers' => $ledgerRows,
            ];
        }

        // NET CLOSING BALANCE (like Tally)
        $netBalance = $totalDebit - $totalCredit;

        return view('reports.cash_bank_summary', compact(
            'result',
            'date',
            'totalDebit',
            'totalCredit',
            'netBalance'
        ));
    }

    public function ledgerMonthly($ledgerId)
    {
        $ledger = DB::table('account_ledgers')->find($ledgerId);

        // Financial Year (April–March)
        $fyYear  = now()->month < 4 ? now()->year - 1 : now()->year;
        $fyStart = Carbon::create($fyYear, 4, 1);

        // Opening balance sign
        $opening = $ledger->opening_type === 'Cr'
            ? -$ledger->opening_balance
            : $ledger->opening_balance;

        $months  = collect();
        $running = $opening;

        for ($i = 0; $i < 12; $i++) {
            $from = Carbon::create($fyStart->year, 4, 1)
                ->addMonths($i)
                ->startOfMonth();

            $to = Carbon::create($fyStart->year, 4, 1)
                ->addMonths($i)
                ->endOfMonth();
            // dd($from);

            $txn = DB::table('voucher_lines')
                ->join('vouchers', 'vouchers.id', '=', 'voucher_lines.voucher_id')
                ->where('voucher_lines.ledger_id', $ledgerId)
                ->whereBetween('vouchers.voucher_date', [$from, $to])
                ->selectRaw("
                SUM(CASE WHEN LOWER(dc)='dr' THEN amount ELSE 0 END) AS dr,
                SUM(CASE WHEN LOWER(dc)='cr' THEN amount ELSE 0 END) AS cr
            ")
                ->first();

            $dr = (float) ($txn->dr ?? 0);
            $cr = (float) ($txn->cr ?? 0);

            $closing = $running + $dr - $cr;

            $months->push([
                'month'   => $from->format('F'),
                'from'    => $from->format('Y-m-d'),
                'to'      => $to->format('Y-m-d'),
                'dr'      => $dr,
                'cr'      => $cr,
                'closing' => $closing,
            ]);

            $running = $closing;
        }

        return view('reports.ledger_monthly', compact(
            'ledger',
            'ledgerId',
            'months',
            'opening'
        ));
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
            ->whereHas(
                'voucher',
                fn($q) =>
                $q->whereDate('voucher_date', '<', $from)
            )
            ->select(
                DB::raw('SUM(debit) as debit'),
                DB::raw('SUM(credit) as credit')
            )->first();

        $balance += ($before->credit ?? 0) - ($before->debit ?? 0);

        $entries = VoucherLine::with('voucher')
            ->where('ledger_id', $ledger->id)
            ->whereHas(
                'voucher',
                fn($q) =>
                $q->whereBetween('voucher_date', [$from, $to])
            )
            ->orderBy('voucher_id')
            ->get();

        return view('reports.ledger', compact(
            'ledger',
            'entries',
            'balance',
            'from',
            'to'
        ));
    }
}
