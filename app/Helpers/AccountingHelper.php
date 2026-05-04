<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

if (!function_exists('ledger_balance')) {

    function ledger_balance($ledger_id, $fromDate = null, $toDate = null, $branchId = null)
    {
        $ledger = DB::table('account_ledgers')->where('id', $ledger_id)->first();
        if (!$ledger) return null;

        $opening = $ledger->opening_balance;
        $openingType = $ledger->opening_type;

        // Base query
        $query = DB::table('voucher_lines')
            ->join('vouchers', 'vouchers.id', '=', 'voucher_lines.voucher_id')
            ->where('voucher_lines.ledger_id', $ledger_id);

        // ✅ Branch filter
        if ($branchId) {
            $query->where('vouchers.branch_id', $branchId);
        }

        // ================= NO DATE =================
        if (!$fromDate && !$toDate) {

            $totals = (clone $query)->selectRaw("
                SUM(CASE WHEN dc='Dr' THEN amount ELSE 0 END) as dr,
                SUM(CASE WHEN dc='Cr' THEN amount ELSE 0 END) as cr
            ")->first();

            $dr = $totals->dr ?? 0;
            $cr = $totals->cr ?? 0;

            $closing = ($openingType == 'Dr')
                ? ($opening + $dr - $cr)
                : ($opening + $cr - $dr);

            return format_balance($opening, $openingType, $dr, $cr, $closing);
        }

        // ================= OPENING BEFORE DATE =================
        if ($fromDate) {
            $before = (clone $query)
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

            // Fix type
            if ($opening < 0) {
                $openingType = ($openingType == 'Dr') ? 'Cr' : 'Dr';
                $opening = abs($opening);
            }
        }

        // ================= PERIOD =================
        $periodQuery = clone $query;

        // ✅ SAFE DATE CONDITIONS (NO ERROR)
        if ($fromDate && $toDate) {
            $periodQuery->whereBetween('vouchers.voucher_date', [$fromDate, $toDate]);
        } elseif ($toDate) {
            $periodQuery->whereDate('vouchers.voucher_date', '<=', $toDate);
        }

        $period = $periodQuery->selectRaw("
            SUM(CASE WHEN dc='Dr' THEN amount ELSE 0 END) as dr,
            SUM(CASE WHEN dc='Cr' THEN amount ELSE 0 END) as cr
        ")->first();

        $dr = $period->dr ?? 0;
        $cr = $period->cr ?? 0;

        return format_balance($opening, $openingType, $dr, $cr);
    }
}


if (!function_exists('format_balance')) {

    function format_balance($opening, $openingType, $dr, $cr, $closing = null)
    {
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
