<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Shift End Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            text-align: left;
        }

        .header-info {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5px;
        }

        .generated {
            font-size: 12px;
            color: #666;
            text-align: right;
        }

        .subtitle {
            font-size: 12px;
            color: #666;
            margin-bottom: 15px;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
            border-bottom: 1px solid #999;
            padding-bottom: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th,
        td {
            padding: 6px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f0f0f0;
        }

        .bold {
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .highlight {
            background-color: #f9f9f9;
        }
    </style>
</head>

<body>
    <div class="header-info">
        <div class="generated">Generated : {{ now()->format('d/m/Y : h:i A') }}</div>
        <div class="title">Shift end report - {{ $branch_name }}</div>
    </div>

    <div class="section">
        <table>
            <tr>
                <td><strong> Shift user :</strong></td>
                <td>{{ $user_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Date :</strong></td>
                <td>{{ \Carbon\Carbon::parse($shift->start_time)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Shift start time :</strong></td>
                <td>{{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}</td>
            </tr>
            <tr>
                <td><strong>Shift end time :</strong></td>
                <td>{{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}</td>
            </tr>
            <tr>
                <td><strong>Total shift hours :</strong></td>
                <td>{{ $shift->start_time && $shift->end_time? \Carbon\Carbon::parse($shift->start_time)->diff(\Carbon\Carbon::parse($shift->end_time))->format('%H:%I hours'): 'N/A' }}
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Category</div>
        <table>
            @foreach ($categoryTotals['sales'] ?? [] as $category => $amount)
                @php
                    $category = $category === 'TOTAL' ? 'Total sales' : $category;
                @endphp
                <tr>
                    <td>{{ $category }}</td>
                    <td class="text-right">{{ $amount }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <div class="section-title">Stock Summary</div>
        <table>
            <tr>
                <td>Opening stock</td>
                <td class="text-right">{{ $stockTotals->total_opening_stock ?? 0 }}</td>
            </tr>
            <tr>
                <td>Total transaction</td>
                <td class="text-right">
                    {{ ($totalTrasaction) }}</td>
            </tr>
            <tr>
                <td>Total Products sold</td>
                <td class="text-right">{{ $stockTotals->total_sold_stock ?? 0 }}</td>
            </tr>
            <tr>
                <td>Transfer IN</td>
                <td class="text-right">{{ $stockTotals->total_added_stock ?? 0 }}</td>
            </tr>
            <tr>
                <td>Transfer OUT</td>
                <td class="text-right">{{ $stockTotals->total_transferred_stock ?? 0 }}</td>
            </tr>
            <tr>
                <td>Closing stock</td>
                <td class="text-right">{{ $stockTotals->total_closing_stock ?? 0 }}</td>
            </tr>
            <tr>
                <td>Physical stock</td>
                <td class="text-right">{{ $stockTotals->total_physical_stock ?? 0 }}</td>
            </tr>
            <tr class="bold highlight">
                <td>Difference</td>
                <td class="text-right">{{ $stockTotals->total_difference_in_stock ?? 0 }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Payment Summary</div>
        <table>
            <tr>
                <td>Cash</td>
                <td class="text-right">{{ $categoryTotals['payment']['CASH'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>UPI</td>
                <td class="text-right">{{ $categoryTotals['payment']['UPI PAYMENT'] ?? 0 }}</td>
            </tr>
            <tr class="bold highlight">
                <td>Cash + UPI</td>
                <td class="text-right">{{ $categoryTotals['payment']['TOTAL'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Credit</td>
                <td class="text-right">{{ $categoryTotals['summary']['CREDIT'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Credit collection</td>
                <td class="text-right">{{ $categoryTotals['summary']['CREDIT COLLACTED BY CASH'] ?? 0 }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Sales Summary</div>
        <table>
            @foreach ($categoryTotals['summary'] ?? [] as $key => $val)
                <tr>
                    <td>{{ $key }}</td>
                    <td class="text-right">{{ $val }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <div class="section-title">Cash Detail Denomination</div>
        <table>
            <thead>
                <tr>
                    <th>Denomination</th>
                    <th>Qty</th>
                    <th class="text-right">Amount</th>
                </tr>
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
                        <td >{{ $deno }} X </td>
                        <td>{{ $qty }}</td>
                        <td class="text-right">{{ $amount }}</td>
                    </tr>
                @endforeach
                <tr class="bold highlight">
                    <td colspan="2">Total Notes =</td>
                    <td class="text-right">{{ $denoTotal }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Cash Summary</div>
        <table>
            <tr>
                <td>System cash</td>
                <td class="text-right">
                    {{ $categoryTotals['payment']['CASH'] + ($categoryTotals['summary']['CREDIT COLLACTED BY CASH'] ?? 0) }}
                </td>
            </tr>
            {{-- <tr>
                <td>Withdrawal Payment (-)</td>
                <td class="text-right">{{ $categoryTotals['summary']['WITHDRAWAL'] ?? 0 }}</td>
            </tr> --}}
            <tr>
                <td>Expense (-)</td>
                <td class="text-right">{{ $categoryTotals['summary']['EXPENSE'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Physical cash</td>
                <td class="text-right">{{ $denoTotal }}</td>
            </tr>
            <tr>
                <td>Closing Cash</td>
                <td class="text-right">{{ $closing_cash ?? 0 }}</td>
            </tr>
            <tr class="bold highlight">
                <td>Discrepancy</td>
                <td class="text-right">{{ $cash_discrepancy ?? 0 }}</td>
            </tr>
        </table>
    </div>
</body>

</html>
