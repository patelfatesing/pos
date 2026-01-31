<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Day End Report</title>
    <style>
        body {
            font-family: DejaVu Sans, monospace;
            font-size: 11px;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        /* Outer page border */
        .report-box {
            border: 1px solid #000;
            padding: 6px;
        }

        hr {
            border: 0;
            border-top: 1px solid #000;
            margin: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            padding: 2px 4px;
            border: 1px solid #000;
            /* table grid border */
        }

        .section-title {
            font-weight: bold;
            border: 1px solid #000;
            padding: 2px;
            margin-top: 4px;
        }
    </style>

</head>

<body>
    <div class="report-box">
        <!-- ================= PAGE 1 ================= -->

        <div class="center bold">
            {{ $shift->branch->name }}<br>
            {{ $shift->branch->address }}<br>
            VAT {{ $shift->branch->vat_no }}
        </div>

        <br>

        <div class="center bold">Day End Report</div>

        <table>
            <tr>
                <td>Day Code {{ $shift->shift_no }}</td>
                <td class="right">Closing Date {{ $shift->end_time->format('D d/m/Y') }}</td>
            </tr>
            <tr>
                <td colspan="2">Generated On {{ now()->format('d/m/Y H:i:s') }}</td>
            </tr>
        </table>

        <hr>

        <table>
            <tr>
                <td>Cash Diff.</td>
                <td class="right">{{ number_format($shift->cash_discrepancy, 2) }}</td>
            </tr>
            <tr>
                <td>No of Customer</td>
                <td class="right">{{ $noOfCustomer ?? 0 }}</td>
            </tr>
        </table>

        <hr>

        <table>
            @foreach ($summary as $key => $value)
                <tr>
                    <td>{{ $key }}</td>
                    <td class="right">{{ $value }}</td>
                </tr>
            @endforeach
        </table>

        <hr>

        <div class="bold">Payment Mode</div>
        <table>
            @foreach ($payments as $mode => $amount)
                <tr>
                    <td>{{ $mode }}</td>
                    <td class="right">{{ number_format($amount, 2) }}</td>
                </tr>
            @endforeach
        </table>

        <hr>

       

        <div class="page-break"></div>

        <!-- ================= PAGE 2 ================= -->

        <div class="center bold">
            {{ $shift->branch->name }}<br>
            {{ $shift->branch->address }}<br>
            VAT {{ $shift->branch->vat_no }}
        </div>

        <br>

        <div class="center bold">Day End Report</div>

        <table>
            <tr>
                <td>Day Code {{ $shift->shift_no }}</td>
                <td class="right">Closing Date {{ $shift->end_time->format('D d/m/Y') }}</td>
            </tr>
            <tr>
                <td colspan="2">Generated On {{ now()->format('d/m/Y H:i:s') }}</td>
            </tr>
        </table>

        <hr>

        <div class="bold">Department Sales</div>

        <table>
            <tr class="bold">
                <td>Department</td>
                <td class="right">Qty</td>
                <td class="right">Amount</td>
            </tr>

            @foreach ($departments as $dept)
                <tr>
                    <td>{{ $dept['name'] }}</td>
                    <td class="right">{{ $dept['qty'] }}</td>
                    <td class="right">{{ number_format($dept['gross'], 2) }}</td>
                </tr>
            @endforeach
        </table>

        <hr>

    
    </div>
</body>

</html>
