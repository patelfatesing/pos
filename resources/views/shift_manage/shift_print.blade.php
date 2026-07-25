<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Shift End Report</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #222;
        }

        /* ===== Top header ===== */
        .top-header {
            width: 100%;
        }

        .top-header td {
            vertical-align: top;
            padding: 0;
        }

        .shop-name {
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            letter-spacing: 0.5px;
        }

        .shop-sub {
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            text-decoration: underline;
            margin-top: 2px;
            margin-bottom: 10px;
        }

        .generated-on {
            font-size: 12px;
            text-align: right;
            color: #333;
            padding-top: 4px;
        }

        .report-bar {
            background: #bfbfbf;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            padding: 4px 0;
            margin: 6px 0 8px 0;
        }

        /* ===== shift meta row ===== */
        .meta-table {
            width: 100%;
            margin-bottom: 8px;
        }

        .meta-table td {
            padding: 1px 0;
            font-size: 12px;
        }

        .meta-left {
            width: 50%;
        }

        .meta-right {
            width: 50%;
            text-align: right;
        }

        .meta-label {
            font-weight: bold;
        }

        /* ===== 2 square cards per row ===== */
        .grid-row {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
            margin-bottom: 8px;
        }

        .grid-row td.col {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }

        .grid-row td.col:first-child {
            padding-right: 5px;
        }

        .grid-row td.col:last-child {
            padding-left: 5px;
        }

        .card {
            border: 1px solid #999;
        }

        .card-title {
            background: #e6e6e6;
            font-weight: bold;
            font-size: 12px;
            padding: 3px 6px;
            border-bottom: 1px solid #999;
        }

        .card table {
            width: 100%;
            border-collapse: collapse;
        }

        .card table td {
            padding: 2px 6px;
            font-size: 12px;
            border-bottom: 1px solid #eee;
        }

        .card table tr:last-child td {
            border-bottom: none;
        }

        .card .total-row td {
            font-weight: bold;
            background: #f2f2f2;
            border-top: 1px solid #999;
        }

        .text-right {
            text-align: right;
        }

        .full-card {
            width: 100%;
            margin-bottom: 8px;
            border: 1px solid #999;
        }

        .full-card table {
            width: 100%;
            border-collapse: collapse;
        }

        .full-card table td {
            padding: 2px 6px;
            font-size: 12px;
            border-bottom: 1px solid #eee;
        }

        .full-card table tr:last-child td {
            border-bottom: none;
        }

        .full-card .total-row td {
            font-weight: bold;
            background: #f2f2f2;
            border-top: 1px solid #999;
        }

        .empty-note {
            font-style: italic;
            color: #888;
            font-size: 9px;
        }
    </style>
</head>

<body>

    {{-- ===== Header ===== --}}
    <table class="top-header">
        <tr>
            <td style="width:30%;"></td>
            <td style="width:40%;">
                <div class="shop-name">LIQUOR HUB</div>
                <div class="shop-sub">{{ $branch_name }}</div>
            </td>
            <td style="width:30%;">
                <div class="generated-on">Generated On {{ now()->format('d-m-Y H:i:s') }}</div>
            </td>
        </tr>
    </table>

    <div class="report-bar">Shift Summary Report</div>

    @php
        // Total shift hours (restored from old template)
        $totalShiftHours = ($shift->start_time && $shift->end_time)
            ? \Carbon\Carbon::parse($shift->start_time)->diff(\Carbon\Carbon::parse($shift->end_time))->format('%H:%I hours')
            : 'N/A';
    @endphp

    <table class="meta-table">
        <tr>
            <td class="meta-left">
                <span class="meta-label">Shift Code :</span> {{ $shift->shift_no ?? '-' }}<br>
                <span class="meta-label">Shift User :</span> {{ $user_name ?? 'N/A' }}<br>
                <span class="meta-label">Cash Diff. :</span>
                {{ number_format(abs($cash_discrepancy ?? 0), 2) }}
                {{ ($cash_discrepancy ?? 0) < 0 ? '(Short)' : (($cash_discrepancy ?? 0) > 0 ? '(Excess)' : '') }}
            </td>
            <td class="meta-right">
                Shift Start : {{ \Carbon\Carbon::parse($shift->start_time)->format('d-m-Y h:i A') }}<br>
                Shift End :
                {{ $shift->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('d-m-Y h:i A') : '-' }}<br>
                Total Shift Hours : {{ $totalShiftHours }}
            </td>
        </tr>
    </table>

    @php
        // ---------- Section 1 : Sales Summary  vs  Category Wise Sales ----------
        $salesSummaryRows = [
            ['Opening Cash', number_format($categoryTotals['summary']['OPENING CASH'] ?? 0, 2)],
            ['Cash Added', number_format($categoryTotals['summary']['CASH ADDED'] ?? 0, 2)],
            ['Total Sales', number_format($categoryTotals['summary']['TOTAL SALES'] ?? 0, 2)],
            ['Credit', number_format($categoryTotals['summary']['CREDIT'] ?? 0, 2)],
            ['Credit Collection (Cash)', number_format($categoryTotals['summary']['CREDIT COLLACTED BY CASH'] ?? 0, 2)],
            ['Discount', number_format($categoryTotals['summary']['DISCOUNT'] ?? 0, 2)],
            ['Refund', number_format($categoryTotals['summary']['REFUND'] ?? 0, 2)],
            ['Round Off', number_format($categoryTotals['summary']['ROUND OFF'] ?? 0, 2)],
        ];
        $salesSummaryTotal = ['Total Shop Sales', number_format($categoryTotals['summary']['TOTAL'] ?? 0, 2)];

        $categoryRows = [];
        foreach (($categoryTotals['sales'] ?? []) as $cat => $amt) {
            if ($cat !== 'TOTAL') {
                $categoryRows[] = [ucwords(strtolower($cat)), number_format($amt, 2)];
            }
        }
        $categoryTotalRow = ['Total Category Wise Sales', number_format($categoryTotals['sales']['TOTAL'] ?? 0, 2)];

        $maxRows1 = max(count($salesSummaryRows), count($categoryRows));
        while (count($salesSummaryRows) < $maxRows1) $salesSummaryRows[] = ['', ''];
        while (count($categoryRows) < $maxRows1) $categoryRows[] = ['', ''];
    @endphp

    {{-- ===== Row 1 : Sales Summary | Category Wise Sales ===== --}}
    <table class="grid-row">
        <tr>
            <td class="col">
                <div class="card">
                    <div class="card-title">Sales Summary</div>
                    <table>
                        @for ($i = 0; $i < $maxRows1; $i++)
                            <tr>
                                <td>{{ $salesSummaryRows[$i][0] !== '' ? $salesSummaryRows[$i][0] : "\u{00A0}" }}</td>
                                <td class="text-right">{{ $salesSummaryRows[$i][0] !== '' ? $salesSummaryRows[$i][1] : "\u{00A0}" }}</td>
                            </tr>
                        @endfor
                        <tr class="total-row">
                            <td>{{ $salesSummaryTotal[0] }}</td>
                            <td class="text-right">{{ $salesSummaryTotal[1] }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td class="col">
                <div class="card">
                    <div class="card-title">Category Wise Sales</div>
                    <table>
                        @for ($i = 0; $i < $maxRows1; $i++)
                            <tr>
                                <td>{{ $categoryRows[$i][0] !== '' ? $categoryRows[$i][0] : "\u{00A0}" }}</td>
                                <td class="text-right">{{ $categoryRows[$i][0] !== '' ? $categoryRows[$i][1] : "\u{00A0}" }}</td>
                            </tr>
                        @endfor
                        <tr class="total-row">
                            <td>{{ $categoryTotalRow[0] }}</td>
                            <td class="text-right">{{ $categoryTotalRow[1] }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    @php
        // ---------- Section 2 : Paid Out Expenses  vs  Stock Summary Report ----------
        $expenseRows = [];
        foreach (($expenses ?? []) as $expense) {
            $expenseRows[] = [$expense->title ?? $expense->name ?? 'Expense', number_format($expense->amount ?? 0, 2)];
        }
        $expenseNote = count($expenseRows) === 0;
        if ($expenseNote) {
            $expenseRows[] = ['No expense entries for this shift', ''];
        }
        $expenseTotal = ['Total Expenses', number_format($categoryTotals['summary']['EXPENSE'] ?? 0, 2)];

        // Stock rows - Difference row restored from old template
        $stockRows = [
            ['Opening Stock', $stockTotals->total_opening_stock ?? 0],
            ['Total Transaction', $totalTrasaction ?? 0],
            ['Products Sold', $stockTotals->total_sold_stock ?? 0],
            ['Transfer IN', $stockTotals->total_added_stock ?? 0],
            ['Transfer OUT', $stockTotals->total_transferred_stock ?? 0],
            ['Physical Stock', $stockTotals->total_physical_stock ?? 0],
            ['Difference', $stockTotals->total_difference_in_stock ?? 0],
        ];
        $stockTotal = ['Closing Stock', $stockTotals->total_closing_stock ?? 0];

        $maxRows2 = max(count($expenseRows), count($stockRows));
        while (count($expenseRows) < $maxRows2) $expenseRows[] = ['', ''];
        while (count($stockRows) < $maxRows2) $stockRows[] = ['', ''];
    @endphp

    {{-- ===== Row 2 : Paid Out Expenses | Stock Summary Report ===== --}}
    <table class="grid-row">
        <tr>
            <td class="col">
                <div class="card">
                    <div class="card-title">Paid Out Expenses</div>
                    <table>
                        @for ($i = 0; $i < $maxRows2; $i++)
                            @if ($expenseNote && $i === 0)
                                <tr>
                                    <td class="empty-note" colspan="2">{{ $expenseRows[$i][0] }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td>{{ $expenseRows[$i][0] !== '' ? $expenseRows[$i][0] : "\u{00A0}" }}</td>
                                    <td class="text-right">{{ $expenseRows[$i][0] !== '' ? $expenseRows[$i][1] : "\u{00A0}" }}</td>
                                </tr>
                            @endif
                        @endfor
                        <tr class="total-row">
                            <td>{{ $expenseTotal[0] }}</td>
                            <td class="text-right">{{ $expenseTotal[1] }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td class="col">
                <div class="card">
                    <div class="card-title">Stock Summary Report</div>
                    <table>
                        @for ($i = 0; $i < $maxRows2; $i++)
                            <tr @if ($stockRows[$i][0] === 'Difference') class="total-row" @endif>
                                <td>{{ $stockRows[$i][0] !== '' ? $stockRows[$i][0] : "\u{00A0}" }}</td>
                                <td class="text-right">{{ $stockRows[$i][0] !== '' ? $stockRows[$i][1] : "\u{00A0}" }}</td>
                            </tr>
                        @endfor
                        <tr class="total-row">
                            <td>{{ $stockTotal[0] }}</td>
                            <td class="text-right">{{ $stockTotal[1] }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    @php
        // ---------- Section 3 : Payment Mode  vs  Cash Denomination ----------
        // Credit fallback: use payment array first, fall back to summary array (matches old template's data source)
        $creditValue = $categoryTotals['payment']['CREDIT'] ?? $categoryTotals['summary']['CREDIT'] ?? 0;

        $paymentRows = [
            ['Cash', number_format($categoryTotals['payment']['CASH'] ?? 0, 2)],
            ['UPI', number_format($categoryTotals['payment']['UPI PAYMENT'] ?? 0, 2)],
            ['Credit', number_format($creditValue, 2)],
            ['Credit Collection', number_format($categoryTotals['summary']['CREDIT COLLACTED BY CASH'] ?? 0, 2)],
        ];
        $paymentTotal = ['Total', number_format($categoryTotals['payment']['TOTAL'] ?? 0, 2)];

        $denomRows = [];
        $denoTotal = 0;
        foreach ([10, 20, 50, 100, 200, 500] as $deno) {
            $qty = $shiftcash[$deno] ?? 0;
            $amount = $qty * $deno;
            $denoTotal += $amount;
            $denomRows[] = [$deno . ' X', $qty, number_format($amount, 2)];
        }
        $denomTotalFormatted = number_format($denoTotal, 2);

        $maxRows3 = max(count($paymentRows), count($denomRows));
        while (count($paymentRows) < $maxRows3) $paymentRows[] = ['', ''];
        while (count($denomRows) < $maxRows3) $denomRows[] = ['', '', ''];
    @endphp

    {{-- ===== Row 3 : Payment Mode | Cash Denomination ===== --}}
    <table class="grid-row">
        <tr>
            <td class="col">
                <div class="card">
                    <div class="card-title">Payment Mode</div>
                    <table>
                        <tr>
                            <td>{!! "&nbsp;" !!}</td>
                            <td class="text-right">{!! "&nbsp;" !!}</td>
                        </tr>
                        @for ($i = 0; $i < $maxRows3; $i++)
                            <tr>
                                <td>{{ $paymentRows[$i][0] !== '' ? $paymentRows[$i][0] : "\u{00A0}" }}</td>
                                <td class="text-right">{{ $paymentRows[$i][0] !== '' ? $paymentRows[$i][1] : "\u{00A0}" }}</td>
                            </tr>
                        @endfor
                        <tr class="total-row">
                            <td>{{ $paymentTotal[0] }}</td>
                            <td class="text-right">{{ $paymentTotal[1] }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td class="col">
                <div class="card">
                    <div class="card-title">Cash Denomination</div>
                    <table>
                        <tr>
                            <td><strong>Denomination</strong></td>
                            <td class="text-right"><strong>Amount</strong></td>
                        </tr>
                        @for ($i = 0; $i < $maxRows3; $i++)
                            <tr>
                                <td>{{ $denomRows[$i][0] }} {{ $denomRows[$i][1] }}</td>
                                <td class="text-right">{{ $denomRows[$i][2] }}</td>
                            </tr>
                        @endfor
                        <tr class="total-row">
                            <td>Total Notes</td>
                            <td class="text-right">{{ $denomTotalFormatted }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- ===== Cash Summary (full width card) ===== --}}
    <div class="full-card">
        <div class="card-title">Cash Summary</div>
        <table>
            <tr>
                <td style="width:70%;">System Cash</td>
                <td class="text-right" style="width:30%;">
                    {{ number_format(($categoryTotals['payment']['CASH'] ?? 0) + ($categoryTotals['summary']['CREDIT COLLACTED BY CASH'] ?? 0), 2) }}
                </td>
            </tr>
            <tr>
                <td>Expense (-)</td>
                <td class="text-right">{{ number_format($categoryTotals['summary']['EXPENSE'] ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Physical Cash</td>
                <td class="text-right">{{ $denomTotalFormatted }}</td>
            </tr>
            <tr>
                <td>Closing Cash</td>
                <td class="text-right">{{ number_format($closing_cash ?? 0, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>Discrepancy</td>
                <td class="text-right">{{ number_format($cash_discrepancy ?? 0, 2) }}</td>
            </tr>
        </table>
    </div>

</body>

</html>