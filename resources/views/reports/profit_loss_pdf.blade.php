@php
    $header = $payload['header'] ?? [];
    $trading = $payload['trading'] ?? [];
    $pl = $payload['pl'] ?? [];
    $extras = $payload['extras'] ?? [];

    $trDrRows = $trading['dr']['rows'] ?? [];
    $trCrRows = $trading['cr']['rows'] ?? [];

    $tradingTotal = $trading['table_total'] ?? '0.00';
    $plTotal = $pl['table_total'] ?? '0.00';

    // helper
    $h = fn($arr, $key, $default = '') => $arr[$key] ?? $default;
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Profit & Loss</title>
    <style>
        @page {
            margin: 18mm 14mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .title {
            font-size: 18px;
            font-weight: 700;
        }

        .sub {
            color: #666;
            margin-top: 3px;
        }

        .section-title {
            font-weight: 700;
            margin: 14px 0 6px;
            font-size: 14px;
        }

        .two-col {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .col {
            display: table-cell;
            vertical-align: top;
            width: 50%;
            padding-right: 10px;
        }

        .col+.col {
            padding-right: 0;
            padding-left: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px 8px;
        }

        th {
            background: #f3f3f3;
            text-align: left;
            font-weight: 700;
        }

        tfoot td {
            font-weight: 700;
        }

        .row-label {
            width: 65%;
        }

        .row-amt {
            width: 35%;
            text-align: right;
        }

        .muted {
            color: #666;
            font-size: 11px;
        }

        /* nested table for Purchase Accounts children */
        .nested {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            font-size: 11px;
        }

        .nested th,
        .nested td {
            border: 1px dashed #ddd;
            padding: 4px 6px;
        }

        .nested th {
            background: #fafafa;
        }

        .totals {
            margin-top: 6px;
        }

        .tag {
            display: inline-block;
            padding: 2px 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #fafafa;
            font-size: 11px;
            margin-right: 6px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">{{ $h($header, 'title', 'Profit & Loss') }}</div>
        <div class="sub">
            Period: {{ $h($header, 'period') }} |
            Branch: {{ $h($header, 'branch', 'All Branches') }}
        </div>
    </div>

    {{-- Trading Account --}}
    <div class="section-title">Trading Account</div>
    <div class="two-col">
        <div class="col">
            <table>
                <thead>
                    <tr>
                        <th colspan="2">{{ $h($trading['dr'] ?? [], 'title', 'Trading Account (Dr)') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($trDrRows as $row)
                        <tr>
                            <td class="row-label">
                                {{ $h($row, 'label') }}
                                @if (!empty($row['children']))
                                    <table class="nested">
                                        <thead>
                                            <tr>
                                                <th>Ledger</th>
                                                <th style="width:80px; text-align:right;">Bills</th>
                                                <th style="width:120px; text-align:right;">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($row['children'] as $child)
                                                <tr>
                                                    <td>{{ $h($child, 'label', '—') }}</td>
                                                    <td style="text-align:right;">{{ $h($child, 'bills', 0) }}</td>
                                                    <td style="text-align:right;">{{ $h($child, 'amount', '0.00') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </td>
                            <td class="row-amt">{{ $h($row, 'amount', '0.00') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td class="row-amt">{{ $tradingTotal }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="col">
            <table>
                <thead>
                    <tr>
                        <th colspan="2">{{ $h($trading['cr'] ?? [], 'title', 'Trading Account (Cr)') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($trCrRows as $row)
                        <tr>
                            <td class="row-label">{{ $h($row, 'label') }}</td>
                            <td class="row-amt">{{ $h($row, 'amount', '0.00') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td class="row-amt">{{ $tradingTotal }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Profit & Loss --}}
    <div class="section-title" style="margin-top:16px;">Profit & Loss</div>
    <div class="two-col">
        <div class="col">
            <table>
                <thead>
                    <tr>
                        <th colspan="2">{{ $h($pl['dr'] ?? [], 'title', 'Profit & Loss (Dr)') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pl['dr']['rows'] ?? [] as $row)
                        <tr>
                            <td class="row-label">{{ $h($row, 'label') }}</td>
                            <td class="row-amt">{{ $h($row, 'amount', '0.00') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td class="row-amt">{{ $plTotal }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="col">
            <table>
                <thead>
                    <tr>
                        <th colspan="2">{{ $h($pl['cr'] ?? [], 'title', 'Profit & Loss (Cr)') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pl['cr']['rows'] ?? [] as $row)
                        <tr>
                            <td class="row-label">{{ $h($row, 'label') }}</td>
                            <td class="row-amt">{{ $h($row, 'amount', '0.00') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td class="row-amt">{{ $plTotal }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Extras (optional badges) --}}
    <div class="totals">
        @if (!empty($extras['gross_profit']) && $extras['gross_profit'] !== '0.00')
            <span class="tag">Gross Profit: {{ $extras['gross_profit'] }}</span>
        @endif
        @if (!empty($extras['gross_loss']) && $extras['gross_loss'] !== '0.00')
            <span class="tag">Gross Loss: {{ $extras['gross_loss'] }}</span>
        @endif
        @if (!empty($extras['nett_profit']) && $extras['nett_profit'] !== '0.00')
            <span class="tag">Nett Profit: {{ $extras['nett_profit'] }}</span>
        @endif
        @if (!empty($extras['nett_loss']) && $extras['nett_loss'] !== '0.00')
            <span class="tag">Nett Loss: {{ $extras['nett_loss'] }}</span>
        @endif
    </div>
</body>

</html>
