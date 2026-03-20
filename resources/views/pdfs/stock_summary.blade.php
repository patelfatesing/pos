<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
        }

        /* HEADER */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .header-table td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: middle;
        }

        .logo {
            height: 55px;
        }

        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .details {
            text-align: right;
            font-size: 11px;
            line-height: 1.5;
        }

        /* DIVIDER */
        .divider {
            border-top: 2px solid #000;
            margin: 6px 0 10px;
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        th {
            background: #eaeaea;
            font-weight: bold;
        }

        td.text-left {
            text-align: left;
        }

        .highlight {
            background: #ffe5e5;
        }

        tfoot th {
            background: #dcdcdc;
            font-weight: bold;
        }

        /* FOOTER */
        .footer {
            position: fixed;
            bottom: 10px;
            width: 100%;
            text-align: right;
            font-size: 10px;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <table class="header-table">
        <tr>
            {{-- LOGO --}}
            <td style="width: 25%;">
                <img src="{{ public_path('assets/images/logo_pdf.png') }}" class="logo">
            </td>

            {{-- TITLE --}}
            <td style="width: 50%;" class="title">
                PRODUCT STOCK SUMMARY
            </td>

            {{-- DETAILS --}}
            <td style="width: 25%;" class="details">
                <strong>Branch:</strong> {{ $branch_name->name }}<br>
                <strong>Shift No:</strong> {{ $shift->shift_no }}<br>
                <strong>Date:</strong> {{ \Carbon\Carbon::parse($shift->created_at)->format('d-m-Y') }}<br>
                <strong>Start:</strong> {{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}<br>
                <strong>End:</strong> {{ $shift->end_time ? \Carbon\Carbon::parse($shift->end_time)->format('h:i A') : 'Running' }}
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- CALCULATION --}}
    @php
        $totalOpening = $totalAdded = $totalTransferred = $totalSold = $totalClosing = 0;
        $totalPhysical = $totalDifference = $totalModifyAdd = $totalModifyRemove = 0;
    @endphp

    {{-- TABLE --}}
    <table>
        <thead>
            <tr>
                <th width="25%">Product</th>
                <th width="8%">Category</th>
                <th>Opening</th>
                <th>In</th>
                <th>Out</th>
                <th>Sold</th>
                <th>+Mod</th>
                <th>-Mod</th>
                <th>Closing</th>
                <th>Physical</th>
                <th>Diff</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($rawStockData as $stock)
                @php
                    $totalOpening += $stock->opening_stock;
                    $totalAdded += $stock->added_stock;
                    $totalTransferred += $stock->transferred_stock;
                    $totalSold += $stock->sold_stock;
                    $totalClosing += $stock->closing_stock;
                    $totalPhysical += $stock->physical_stock ?? 0;
                    $totalDifference += $stock->difference_in_stock;
                    $totalModifyAdd += $stock->modify_sale_add_qty;
                    $totalModifyRemove += $stock->modify_sale_remove_qty;
                @endphp

                <tr class="{{ $stock->difference_in_stock != 0 ? 'highlight' : '' }}">
                    <td class="text-left">{{ $stock->product->name ?? 'N/A' }}</td>
                    <td>{{ $stock->product->subcategory->name ?? 'N/A' }}</td>
                    <td>{{ $stock->opening_stock }}</td>
                    <td>{{ $stock->added_stock }}</td>
                    <td>{{ $stock->transferred_stock }}</td>
                    <td>{{ $stock->sold_stock }}</td>
                    <td>{{ $stock->modify_sale_add_qty }}</td>
                    <td>{{ $stock->modify_sale_remove_qty }}</td>
                    <td>{{ $stock->closing_stock }}</td>
                    <td>{{ $stock->physical_stock }}</td>
                    <td>{{ $stock->difference_in_stock }}</td>
                </tr>

            @empty
                <tr>
                    <td colspan="11">No data available</td>
                </tr>
            @endforelse
        </tbody>

        <tfoot>
            <tr>
                <th colspan="2">TOTAL</th>
                <th>{{ $totalOpening }}</th>
                <th>{{ $totalAdded }}</th>
                <th>{{ $totalTransferred }}</th>
                <th>{{ $totalSold }}</th>
                <th>{{ $totalModifyAdd }}</th>
                <th>{{ $totalModifyRemove }}</th>
                <th>{{ $totalClosing }}</th>
                <th>{{ $totalPhysical }}</th>
                <th>{{ $totalDifference }}</th>
            </tr>
        </tfoot>
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        Generated on: {{ now()->format('d-m-Y H:i') }}
    </div>

</body>
</html>