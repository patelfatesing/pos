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
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 12px;
            text-align: center;
            margin-bottom: 20px;
            color: #666;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
            padding-bottom: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f9f9f9;
        }

        .bold {
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .highlight {
            background-color: #f0f0f0;
        }
    </style>
</head>

<body>

    <div class="title">{{ $branch_name }} – Shift End Report</div>
    <div class="subtitle">{{ now()->format('d/m/Y, h:i A') }}</div>

    <div class="section">
        <div class="section-title">Shift Details</div>
        <table>
            {{-- <tr>
                <td><strong>User Staff:</strong></td>
                <td>{{ $shift->user->name ?? 'N/A' }}</td>
            </tr> --}}
            <tr>
                <td><strong>Start Date:</strong></td>
                <td>{{ $shift->start_time ? \Carbon\Carbon::parse($shift->start_time)->format('d/m/Y, h:i A') : 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>End Date:</strong></td>
                <td>{{ $shift->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('d/m/Y, h:i A') : 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Generated At:</strong></td>
                <td>{{ now()->setTimezone('Asia/Kolkata')->format('d/m/Y, h:i A') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Sales Summary</div>
        <table>
            @foreach ($categoryTotals['sales'] ?? [] as $category => $amount)
                @php
                    $class=$category === 'TOTAL' ? 'bold highlight' : '';
                    $category=$category === 'TOTAL' ? 'Total' : $category;
                @endphp
                <tr class="{{$class}}">
                    <td>{{ $category }}</td>
                    <td class="text-right">{{ $amount }}</td>
                </tr>
            @endforeach
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
                <td>Total</td>
                <td class="text-right">{{ $categoryTotals['payment']['TOTAL'] ?? 0 }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Cash Denomination</div>
        <table>
            <thead>
                <tr>
                    <th>Denomination (Rs.)</th>
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
                        <td>{{ $deno }}</td>
                        <td>{{ $qty }}</td>
                        <td class="text-right">{{ $amount }}</td>
                    </tr>
                @endforeach
                <tr class="bold highlight">
                    <td colspan="2">Total Denomination</td>
                    <td class="text-right">{{ $denoTotal }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Summary</div>
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
        <div class="section-title">Cash Comparison</div>
        <table>
            <tr>
                <td><strong>System Cash:</strong></td>
                <td class="text-right">
                    {{ $categoryTotals['payment']['CASH'] + ($categoryTotals['summary']['CREDIT COLLACTED BY CASH'] ?? 0) }}
                </td>
            </tr>
            <tr>
                <td><strong>Closing Cash:</strong></td>
                <td class="text-right">{{ $closing_cash ?? 0 }}</td>
            </tr>
            <tr class="bold highlight">
                <td><strong>Difference:</strong></td>
                <td class="text-right">{{ $cash_discrepancy ?? 0 }}</td>
            </tr>
        </table>
    </div>

</body>

</html>
