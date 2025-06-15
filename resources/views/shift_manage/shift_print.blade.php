<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }

        .header {
            background: #2CA9E1;
            color: #fff;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: right;
        }

        .table th:first-child,
        .table td:first-child {
            text-align: left;
        }

        .highlight td {
            background: #e8f5e9;
            font-weight: bold;
        }

        h3 {
            background: #34b3f1;
            color: #fff;
            padding: 6px;
            margin: 0 0 10px 0;
            font-size: 14px;
        }

        .cash-header {
            background: #F57C00;
            color: #fff;
            padding: 6px;
            margin: -10px -10px 10px -10px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="header">{{ $branch_name ?? 'Shop' }} - Shift Report </div>

    <div style="font-size:11px; margin-bottom:6px;">
<<<<<<< Updated upstream
=======
        <strong>User Staff:</strong> {{ $user_name }}<br>
>>>>>>> Stashed changes
        <strong>Start:</strong> {{ $shift->start_time }}<br>
        <strong>End:</strong> {{ $shift->end_time }}
    </div>
    <!-- Sales and Summary Side-by-Side -->
    <table class="table" cellspacing="0" cellpadding="0" style="margin-top:20px;">
        <tr valign="top">
            <!-- Summary Column -->
            <td width="50%" style="padding-left:10px; vertical-align:top;">
                {{-- <h3>Sales</h3> --}}
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="2">Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categoryTotals['payment'] as $k => $v)
                            <tr @if (strtoupper($k) === 'TOTAL') class="highlight" @endif>
                                <td>{{ strtoupper($k) }}</td>
                                <td>{{ format_inr($v) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
            <!-- Sales Column -->
            <td width="50%" style="padding-right:10px; vertical-align:top;">
                {{-- <h3>Payment</h3> --}}
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="2">Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categoryTotals['sales'] ?? [] as $k => $v)
                            <tr @if (strtoupper($k) === 'TOTAL') class="highlight" @endif>
                                <td>{{ strtoupper($k) }}</td>
                                <td>{{ format_inr($v) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <table class="table" cellspacing="0" cellpadding="0" style="margin-top:20px;">
        <tr valign="top">

            <!-- Summary Column -->
            <td width="50%" style="padding-left:10px; vertical-align:top;">
                {{-- <h3>Payment Summary</h3> --}}
                <table class="table">
                    {{-- <thead>
                        <tr>
                            <th colspan="2">Payment Summary</th>
                        </tr>
                    </thead> --}}
                    <tbody>
                        @foreach ($categoryTotals['summary'] ?? [] as $k => $v)
                            <tr @if (strtoupper($k) === 'TOTAL') class="highlight" @endif>
                                <td>{{ strtoupper(str_replace('_', ' ', $k)) }}
                                    @if (in_array(strtoupper($k), ['CREDIT', 'REFUND_CREDIT']))
                                        <small>(Excluded from Cash)</small>
                                    @endif
                                </td>
                                <td>{{ format_inr($v) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
            <!-- Sales Column -->
            <td width="50%" style="padding-right:10px; vertical-align:top;">
                {{-- <h3>Denomination</h3> --}}
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="6">Denomination</th>
                        </tr>
                    </thead>
                    <thead>
                        <tr>
                            <th>Denomination</th>
                            <th>Notes</th>
                            <th>x</th>
                            <th>Amount</th>
                            <th>=</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalDenom = 0; @endphp
                        @foreach ($shiftcash ?? [] as $denom => $qty)
                            @php
                                $row = $denom * $qty;
                                $totalDenom += $row;
                            @endphp
                            <tr>
                                <td>{{ format_inr($denom) }}</td>
                                <td>{{ $qty }}</td>
                                <td>x</td>
                                <td>{{ format_inr($denom) }}</td>
                                <td>=</td>
                                <td>{{ format_inr($row) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="highlight">
                            <td colspan="5">Total</td>
                            <td>{{ format_inr($totalDenom) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
    </table>

    <table class="table" cellspacing="0" cellpadding="0" style="margin-top:20px;">
        <tr valign="top">

            <!-- Summary Column -->
            <td width="50%" style="padding-left:10px; vertical-align:top;">
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="2">Payment Summary</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categoryTotals['summary'] ?? [] as $k => $v)
                            <tr @if (strtoupper($k) === 'TOTAL') class="highlight" @endif>
                                <td>{{ strtoupper(str_replace('_', ' ', $k)) }}
                                    @if (in_array(strtoupper($k), ['CREDIT', 'REFUND_CREDIT']))
                                        <small>(Excluded from Cash)</small>
                                    @endif
                                </td>
                                <td>{{ format_inr($v) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
            <!-- Sales Column -->
            <td width="50%" style="padding-right:10px; vertical-align:top;">
                <table class="table" style="margin-top:10px;">
                    <tr>
                        <td><strong>System Cash Sales</strong></td>
                        <td class="text-end">{{ format_inr($totalDenom) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Cash Amount</strong></td>
                        <td class="text-end">{{ format_inr($categoryTotals['summary']['TOTAL'] ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Closing Cash</strong></td>
                        <td class="text-end">{{ format_inr($closing_cash ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Discrepancy Cash</strong></td>
                        <td class="text-end">{{ format_inr($cash_discrepancy ?? 0) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
