<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
</head>

<body style="margin:0; padding:20px; background:#f2f4f7; font-family: Arial, sans-serif;">

    <div style="max-width:900px; margin:auto;">

        <!-- HEADER -->
        <div style="background:#3aa6c9; color:#fff; padding:12px 15px; border-radius:6px 6px 0 0;">
            <strong>{{ $shift->shift_no }} - Shift Close Summary - {{ $shift->branch->name ?? '' }}</strong>
        </div>

        <div style="background:#fff; padding:15px; border-radius:0 0 6px 6px;">

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
                                            <div
                                                style="background:#e8f6fb; padding:8px; text-align:center; font-weight:bold;">
                                                Sales</div>

                                            <table width="100%" style="font-size:13px;">
                                                <tr>
                                                    <td>IMFL</td>
                                                    <td align="right">₹{{ $summary['TOTAL SALES'] ?? 0 }}</td>
                                                </tr>
                                                <tr style="background:#d4edda;">
                                                    <td><strong>TOTAL</strong></td>
                                                    <td align="right">
                                                        <strong>₹{{ $summary['TOTAL SALES'] ?? 0 }}</strong></td>
                                                </tr>
                                            </table>
                                        </td>

                                        <td width="50%" valign="top">
                                            <div
                                                style="background:#e8f6fb; padding:8px; text-align:center; font-weight:bold;">
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

            <!-- FOOTER -->
            <div style="margin-top:15px; text-align:center; color:#888; font-size:12px;">
                📎 Shift Report & Stock Summary attached
            </div>

        </div>

    </div>

</body>

</html>
