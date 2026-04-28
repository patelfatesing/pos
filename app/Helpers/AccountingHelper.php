<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

if (!function_exists('ledger_balance')) {

    function ledger_balance($ledger_id, $fromDate = null, $toDate = null)
    {
        $ledger = DB::table('account_ledgers')->where('id', $ledger_id)->first();

        if (!$ledger) return null;

        $opening = $ledger->opening_balance;
        $openingType = $ledger->opening_type;

        // 👉 If no date → simple balance
        if (!$fromDate && !$toDate) {

            $totals = DB::table('voucher_lines')
                ->where('ledger_id', $ledger_id)
                ->selectRaw("
                    SUM(CASE WHEN dc='Dr' THEN amount ELSE 0 END) as dr,
                    SUM(CASE WHEN dc='Cr' THEN amount ELSE 0 END) as cr
                ")
                ->first();

            $dr = $totals->dr ?? 0;
            $cr = $totals->cr ?? 0;

            if ($openingType == 'Dr') {
                $closing = ($opening + $dr) - $cr;
            } else {
                $closing = ($opening + $cr) - $dr;
            }

            return format_balance($opening, $openingType, $dr, $cr, $closing);
        }

        // 👉 STEP 1: Opening before date (Tally logic)
        $before = DB::table('voucher_lines')
            ->join('vouchers', 'vouchers.id', '=', 'voucher_lines.voucher_id')
            ->where('voucher_lines.ledger_id', $ledger_id)
            ->whereDate('vouchers.voucher_date', '<', $fromDate)
            ->selectRaw("
                SUM(CASE WHEN dc='Dr' THEN amount ELSE 0 END) as dr,
                SUM(CASE WHEN dc='Cr' THEN amount ELSE 0 END) as cr
            ")
            ->first();

        $beforeDr = $before->dr ?? 0;
        $beforeCr = $before->cr ?? 0;

        if ($openingType == 'Dr') {
            $opening = ($opening + $beforeDr) - $beforeCr;
        } else {
            $opening = ($opening + $beforeCr) - $beforeDr;
        }

        // Fix opening type
        $openingType = $opening >= 0 ? $openingType : ($openingType == 'Dr' ? 'Cr' : 'Dr');
        $opening = abs($opening);

        // 👉 STEP 2: Transactions in date range
        $period = DB::table('voucher_lines')
            ->join('vouchers', 'vouchers.id', '=', 'voucher_lines.voucher_id')
            ->where('voucher_lines.ledger_id', $ledger_id)
            ->whereBetween('vouchers.voucher_date', [$fromDate, $toDate])
            ->selectRaw("
                SUM(CASE WHEN dc='Dr' THEN amount ELSE 0 END) as dr,
                SUM(CASE WHEN dc='Cr' THEN amount ELSE 0 END) as cr
            ")
            ->first();

        $dr = $period->dr ?? 0;
        $cr = $period->cr ?? 0;

        return format_balance($opening, $openingType, $dr, $cr);
    }
}


if (!function_exists('format_balance')) {

    function format_balance($opening, $openingType, $dr, $cr, $closing = null)
    {
        // Total Dr/Cr
        $totalDr = ($openingType == 'Dr' ? $opening : 0) + $dr;
        $totalCr = ($openingType == 'Cr' ? $opening : 0) + $cr;

        if ($closing === null) {
            if ($totalDr > $totalCr) {
                $closing = $totalDr - $totalCr;
                $closingType = 'Dr';
            } else {
                $closing = $totalCr - $totalDr;
                $closingType = 'Cr';
            }
        } else {
            $closingType = $closing >= 0 ? $openingType : ($openingType == 'Dr' ? 'Cr' : 'Dr');
            $closing = abs($closing);
        }

        return [
            'opening' => $opening,
            'opening_type' => $openingType,
            'dr' => $dr,
            'cr' => $cr,
            'closing' => $closing,
            'closing_type' => $closingType,
        ];
    }
}
