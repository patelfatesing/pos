<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Shift Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .title { font-weight: bold; font-size: 18px; text-align: center; margin-bottom: 10px; }
        .subtitle { font-size: 12px; text-align: center; margin-bottom: 20px; }
        .section { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th, td { border: 1px solid #000; padding: 5px; font-size: 11px; }
        .noborder td { border: none; padding: 2px; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>

    <div class="title">SR SHOP – Shift Report</div>
    <div class="subtitle">{{ now()->format('d/m/y, h:i A') }} | Sales Register</div>

    <div class="section">
        <table class="noborder">
            <tr>
                <td><strong>User Staff:</strong> {{ $shift->user->name ?? 'N/A' }}</td>
                <td><strong>Start Date:</strong> {{ $shift->start_time }}</td>
                <td><strong>End Date:</strong> {{ $shift->end_time }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table>
            <tr><td>Opening stock</td><td>{{ $shift->opening_stock ?? 'N/A' }}</td></tr>
            <tr><td>Cash in Hand</td><td>{{ $shift->opening_cash ?? 'N/A' }}</td></tr>
            <tr><td>No. of Transactions</td><td>{{ $shift->transaction_count ?? 'N/A' }}</td></tr>
            <tr><td>No. of Products Sold</td><td>{{ $shift->product_sold_count ?? 'N/A' }}</td></tr>
            <tr><td>Discount (-)</td><td>{{ $categoryTotals['summary']['DISCOUNT'] ?? 0 }}</td></tr>
            <tr><td>Sales Return (+)</td><td>{{ $categoryTotals['summary']['REFUND'] ?? 0 }}</td></tr>
            <tr><td>Net Cash (Last Drop)</td><td>{{ $shift->last_cash_drop ?? '0.00' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <strong>Payment Summary:</strong>
        <table>
            <tr><td>Cash</td><td>{{ $categoryTotals['payment']['CASH'] ?? 0 }}</td></tr>
            <tr><td>UPI</td><td>{{ $categoryTotals['payment']['UPI PAYMENT'] ?? 0 }}</td></tr>
            <tr><td>Total</td><td>{{ $categoryTotals['payment']['TOTAL'] ?? 0 }}</td></tr>
        </table>
    </div>

    <div class="section">
        <strong>Sales Summary:</strong>
        <table>
            @foreach ($categoryTotals['sales'] ?? [] as $category => $amount)
                <tr>
                    <td>{{ $category }}</td>
                    <td>{{ $amount }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <strong>Cash Denomination:</strong>
        <table>
            <thead>
                <tr><th>Denomination (Rs.)</th><th>Qty</th><th>Amount</th></tr>
            </thead>
            <tbody>
                @php $denoTotal = 0; @endphp
                @foreach ([10, 20, 50, 100, 200, 500] as $deno)
                    @php
                        $qty = $shiftcash[$deno] ?? 0;
                        $amount = $qty * $deno;
                        $denoTotal += $amount;
                    @endphp
                    <tr>
                        <td>{{ $deno }}</td>
                        <td>{{ $qty }}</td>
                        <td>{{ $amount }}</td>
                    </tr>
                @endforeach
                <tr class="bold">
                    <td colspan="2">Total Denomination</td>
                    <td>{{ $denoTotal }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <strong>Summary:</strong>
        <table>
            @foreach ($categoryTotals['summary'] ?? [] as $key => $val)
                <tr><td>{{ $key }}</td><td>{{ $val }}</td></tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <table>
            <tr><td><strong>System Cash:</strong></td><td>{{ $categoryTotals['payment']['CASH'] + ($categoryTotals['summary']['CREDIT COLLACTED BY CASH'] ?? 0) }}</td></tr>
            <tr><td><strong>Closing Cash:</strong></td><td>{{ $closing_cash ?? 0 }}</td></tr>
            <tr><td><strong>Difference:</strong></td><td>{{ $cash_discrepancy ?? 0 }}</td></tr>
        </table>
    </div>

</body>
</html>
