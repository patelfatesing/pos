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

        .total {
            font-size: 16px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="centered bold">
    LiquorHub<br>
    {{ @$branch->address }}<br>
    @if(@$duplicate == true)
        <span style="color: red;">Duplicate Invoice</span><br>
    @endif
    @if(@$type == 'refund')
        <span>Credit Note</span><br>
    @else
        <span>Invoice</span><br>
    @endif
</div>
<br>
<div class="line"></div>

<div>
    <strong>{{ @$type == 'refund' ? 'Refund' : 'Invoice' }}:</strong> {{ $invoice->invoice_number }}<br>
    <strong>Name:</strong> {{ $invoice->customer_name ?? 'Walk-in' }}<br>
    <strong>Date:</strong> {{ $invoice->created_at->format('d/m/Y H:i') }}
</div>

<div class="line"></div>

<table class="table">
    <thead class="bold">
        <tr>
            <th>#</th>
            <th class="left">Item</th>
            <th class="centered">Qty</th>
            <th class="right">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td class="left">{{ substr($item['name'], 0, 10) }}...{{ substr($item['name'], -5) }}</td>
            <td class="centered">{{ $item['quantity'] }}</td>
            <td class="right">{{ number_format($item['price'], 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="line"></div>
<div>
    @php
        $totalAmount = (float) str_replace(',', '', $invoice->total);
        $discountAmount = (float) str_replace(',', '', $invoice->party_amount ?? 0);

        // Sum or subtract
        $sunTot = $totalAmount + $discountAmount; // or $totalAmount - $discountAmount
    @endphp 
    <table class="table">
        {{-- <tr>
            <td class="left">ROUND OFF:</td>
            <td class="right">{{ $sunTot }}</td>
        </tr> --}}
        <tr>
            <td class="left">SUB TOTAL:</td>
            <td class="right">{{$sunTot }}</td>
        </tr>
        <tr>
            <td class="left">DISCOUNT ITEMS:</td>
            <td class="right">{{ $invoice->party_amount ?? 0 }}</td>
        </tr>
        <tr class="total">
            <td class="left"><strong>TOTAL:</strong></td>
            <td class="right">{{ $invoice->total }}</td>
        </tr>
        <tr>
            <td class="left">BY CASH:</td>
            <td class="right">{{ $invoice->cash_amount }}</td>
        </tr>
        <tr>
            <td class="left">BY UPI:</td>
            <td class="right">{{ $invoice->upi_amount }}</td>
        </tr>
    </table>
</div>

<div class="line"></div>

<div class="centered bold">
    Thank you for shopping with us!
</div>

</body>
</html>
