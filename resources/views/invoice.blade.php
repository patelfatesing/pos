<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: 280px; /* 80mm approx */
            margin: 0;
            padding: 10px;
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

        .table th,
        .table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .right {
            text-align: right;
        }

        .left {
            text-align: left;
        }

        .small {
            font-size: 10px;
        }
    </style>
</head>
<body>

<div class="centered bold">
    LiquorHub<br>
    {{ $branch->address }}<br>
</div>
<br>
<div class="line"></div>

<div>
    <strong>Invoice:</strong> {{ $invoice->invoice_number }}<br>
    <strong>Name:</strong> {{ $invoice->customer_name ?? 'Walk-in' }}<br>
    <strong>Date:</strong> {{ $invoice->created_at->format('d/m/Y H:i') }}
</div>

<div class="line"></div>

<table class="table">
    <thead class="bold">
        <tr>
            <th>#</th>
            <th class="left">Item</th>
            <th>Qty</th>
            <th class="right">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td class="left">{{ $item['name'] }}</td>
            <td>{{ number_format($item['quantity'], 2) }}</td>
            <td class="right">{{ number_format($item['quantity'] * $item['price'], 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="line"></div>

<div>
    <strong>ROUND OFF:</strong> {{ $invoice->total}}<br>
    <strong>TOTAL:</strong> {{ $invoice->total}}<br>
    DISCOUNT ITEMS: {{ $invoice->party_amount ?? 0}}<br>
    TOTAL SAVINGS: {{ $invoice->total_savings ?? 0}}<br>
    BY CASH: {{ $invoice->cash_amount}}<br>
    BY UPI: {{ $invoice->cash_amount}}<br>
</div>

<div class="line"></div>

<div class="bold">Customer Details</div>
Address: {{ $invoice->customer_address ?? ', Rajasthan, Gujarat' }}<br>
PIECES PURCHASED: {{ count($items) }}

<div class="line"></div>

<div class="small centered">
    E & O E.<br>
    Printed On: {{ now()->format('d/m/Y h:i A') }}
</div>

<div class="line"></div>

<div class="centered bold">
    Thank you for shopping with us!
</div>

</body>
</html>
