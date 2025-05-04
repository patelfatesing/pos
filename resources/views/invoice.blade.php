<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 10px;
            width: 280px; /* For 80mm paper width */
        }

        .centered {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
        }

        .totals {
            margin-top: 10px;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 11px;
        }

        .large-text {
            font-size: 16px;
        }
    </style>
</head>
<body>

    <div class="centered bold">
        LiquorHub<br>
        456 Retail Avenue<br>
        City, 654321<br>
        Phone: 1234567890
    </div>

    <div class="line"></div>

    <div>
        Invoice #: {{ $invoice->invoice_number }}<br>
        Date: {{ $invoice->created_at->format('d-m-Y H:i') }}
    </div>

    <div class="line"></div>

    <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
        <thead>
            <tr style="border-bottom: 1px dashed #000; font-weight: bold;">
                <th style="text-align: left;">Name</th>
                <th style="text-align: center;">QTY</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td style="text-align: left;">{{ $item['name'] }}</td>
                    <td style="text-align: center;">{{ $item['quantity'] }}</td>
                    <td style="text-align: right;">{{ number_format($item['quantity'] * $item['price'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    <div class="totals">
      
        <div class="item-row bold large-text">
            <div>Total</div>
            <div>{{ number_format((Int)$invoice->total, 2) }}</div>
        </div>
    </div>

    {{-- <div class="line"></div>

    <div class="totals">
        <div class="item-row">
            <div>Open Balance</div>
            <div>{{ number_format($invoice->open_balance, 2) }}</div>
        </div>
        <div class="item-row">
            <div>Paid Cash</div>
            <div>{{ number_format($invoice->paid_cash, 2) }}</div>
        </div>
        <div class="item-row bold">
            <div>Close Balance</div>
            <div>{{ number_format($invoice->close_balance, 2) }}</div>
        </div>
    </div> --}}

    <div class="line"></div>

    <div class="footer">
        Thank you for shopping with us!<br>
        Visit Again!
    </div>

</body>
</html>
