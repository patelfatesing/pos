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

        th,
        td {
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

    <!-- HEADER TABLE -->
    <table width="100%" cellpadding="0" cellspacing="0"
        style="border-collapse:collapse; margin-bottom:10px; background:#fff;">
        <tr>

            <!-- LOGO -->
            <td style="width:25%; border:1px solid #000; padding:8px;">
                <img src="{{ public_path('assets/images/logo_pdf.png') }}" class="logo">
            </td>

            <!-- TITLE -->
            <td
                style="width:50%; border:1px solid #000; text-align:center; font-weight:bold; font-size:16px; letter-spacing:1px;">
                SHIFT CLOSE SUMMARY
            </td>

            <!-- DETAILS -->
            <td style="width:25%; border:1px solid #000; font-size:12px; line-height:1.5; padding:8px;">
                <strong>Branch:</strong> {{ $shift->branch->name ?? '' }}<br>
                <strong>Shift No:</strong> {{ $shift->shift_no }}<br>
                <strong>Date:</strong> {{ \Carbon\Carbon::parse($shift->created_at)->format('d-m-Y') }}<br>
                <strong>Start:</strong> {{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}<br>
                <strong>End:</strong> {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}
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
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>

            <!-- LEFT SIDE -->
            <td width="50%" valign="top" style="padding-right:10px;">

                <!-- SALES DETAILS -->
                <div style="border:1px solid #ddd; border-radius:6px; margin-bottom:15px;">
                    <div style="background:#3aa6c9; color:#fff; padding:8px; border-radius:6px 6px 0 0;">
                        Sales Details
                    </div>

                    <div style="padding:10px;">

                        <table width="100%">
                            <tr>
                                <td width="50%" valign="top">
                                    <div style="background:#e8f6fb; padding:8px; text-align:center; font-weight:bold;">
                                        Sales</div>

                                    <table width="100%" style="font-size:13px;">
                                        <tr>
                                            <td>IMFL</td>
                                            <td align="right">₹{{ $summary['TOTAL SALES'] ?? 0 }}</td>
                                        </tr>
                                        <tr style="background:#d4edda;">
                                            <td><strong>TOTAL</strong></td>
                                            <td align="right">
                                                <strong>₹{{ $summary['TOTAL SALES'] ?? 0 }}</strong>
                                            </td>
                                        </tr>
                                    </table>
                                </td>

                                <td width="50%" valign="top">
                                    <div style="background:#e8f6fb; padding:8px; text-align:center; font-weight:bold;">
                                        Payment</div>

                                    <table width="100%" style="font-size:13px;">
                                        @foreach ($payments as $key => $value)
                                            <tr>
                                                <td>{{ $key }}</td>
                                                <td align="right">₹{{ number_format($value, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </td>
                            </tr>
                        </table>

                    </div>
                </div>

                <!-- SUMMARY -->
                <div style="border:1px solid #ddd; border-radius:6px;">
                    <div style="background:#3aa6c9; color:#fff; padding:8px; border-radius:6px 6px 0 0;">
                        Summary
                    </div>

                    <table width="100%" cellpadding="6" style="font-size:13px;">
                        @foreach ($summary as $label => $value)
                            <tr style="border-bottom:1px solid #eee;">
                                <td>{{ $label }}</td>
                                <td align="right">{{ $value }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>

            </td>

            <!-- RIGHT SIDE -->
            <td width="50%" valign="top" style="padding-left:10px;">

                <!-- TIME -->
                <div style="border:1px solid #ddd; border-radius:6px; margin-bottom:15px; padding:10px;">
                    <table width="100%" style="font-size:13px;">
                        <tr>
                            <td><strong>Start Time</strong></td>
                            <td><strong>End Time</strong></td>
                        </tr>
                        <tr>
                            <td>{{ $shift->start_time }}</td>
                            <td>{{ $shift->end_time }}</td>
                        </tr>
                    </table>
                </div>

                <!-- CASH DETAILS -->
                <div style="border:1px solid #ddd; border-radius:6px;">
                    <div style="padding:8px; color:#28a745; font-weight:bold;">
                        💵 Cash Details
                    </div>

                    <table width="100%" border="1" cellpadding="6" cellspacing="0"
                        style="border-collapse:collapse; font-size:12px;">
                        <tr style="background:#f1f1f1;">
                            <th>Denomination</th>
                            <th>Qty</th>
                            <th>Amount</th>
                            <th>Total</th>
                        </tr>

                        @foreach ($closing_sales ?? [] as $row)
                            <tr>
                                <td>₹{{ $row['denomination'] ?? '' }}</td>
                                <td align="center">{{ $row['qty'] ?? 0 }}</td>
                                <td align="right">₹{{ $row['denomination'] ?? 0 }}</td>
                                <td align="right">₹{{ $row['total'] ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </table>

                    <div style="padding:10px; font-size:13px;">
                        <p>System Cash Sales: ₹{{ $summary['TOTAL'] ?? 0 }}</p>
                        <p>Total Cash Amount: ₹{{ $summary['TOTAL'] ?? 0 }}</p>
                        <p>Closing Cash: ₹{{ $shift->closing_cash }}</p>
                        <p>Discrepancy Cash: ₹{{ $shift->cash_discrepancy }}</p>
                    </div>
                </div>

            </td>

        </tr>
    </table>


    <h3>Customer Summary</h3>

    <table border="1" width="100%" cellpadding="5">
        <tr>
            <th>Name</th>
            <th>Total Sales</th>
            <th>Commission</th>
            <th>Credit Used</th>
            <th>Remaining Credit</th>
        </tr>

        @foreach ($customers as $c)
            <tr>
                <td>{{ $c['name'] }}</td>
                <td align="right">₹{{ number_format($c['total_sales'], 2) }}</td>
                <td align="right">₹{{ number_format($c['commission_amount'], 2) }}</td>
                <td align="right">₹{{ number_format($c['credit_used'], 2) }}</td>
                <td align="right">₹{{ number_format($c['remaining_credit'], 2) }}</td>
            </tr>
        @endforeach
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        Generated on: {{ now()->format('d-m-Y H:i') }}
    </div>

</body>

</html>
