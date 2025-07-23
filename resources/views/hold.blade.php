<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        @page {
            size: 76mm 210mm;
            margin: 5mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .centered {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 2px 3px;
            vertical-align: top;
            font-size: 12px;
            line-height: 1.2;
        }

        .left {
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .total {
            font-size: 14px;
            font-weight: bold;
        }

        .section-title {
            margin-top: 10px;
            font-weight: bold;
            font-size: 14px;
        }

        .small {
            font-size: 11px;
        }
    </style>
</head>
<body>

<div class="centered bold">
    <div style="font-size: 16px;">LiquorHub</div>
    <div>{{ @$branch->address }}</div>
 
    <div>
        <span style="font-size: 14px;">Hold Invoice</span>
    </div>
</div>

<div class="line"></div>

<div>
    <strong>{{ @$type == 'refund' ? 'Refund' : 'Invoice' }}:</strong> {{ $invoice->invoice_number }}<br>
    <strong>Name:</strong> {{ $customer_name ?? '' }}<br>
    <strong>Date:</strong> {{ \Carbon\Carbon::parse($hold_date)->format('d/m/Y H:i')}}

<div class="line"></div>

<table class="table">
    <thead class="bold">
        <tr>
            <th style="width: 5%;">#</th>
            <th style="width: 50%;" class="left">Item</th>
            <th style="width: 15%;" class="center">Qty</th>
            <th style="width: 15%;" class="center">Rate</th>
            <th style="width: 30%;" class="right">Amount</th>
        </tr>
    </thead>
    <tbody>
        @php
            $total=0;
            $qty=0;
        @endphp
        @foreach($items as $index => $item)
        @php
            $item['price']=$item['mrp']*$item['quantity'];
            $total+=(float)$item['price'];
            $qty+=$item['quantity'];

        @endphp
        <tr>
            <td>{{ $index + 1 }}</td>
            <td class="left">
                {{ strlen($item['name']) > 10 ? substr($item['name'], 0, 10) . '...' . substr($item['name'], -5) : $item['name'] }}
            </td>
            <td class="center">{{ $item['quantity'] }}</td>
            <td class="center">{{ $item['mrp'] }}</td>
            <td class="right">{{ $item['quantity']*$item['mrp']}}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="line"></div>

<table class="table">
    <tr>
        <td class="left">Pieces Purchased :</td>
        <td class="right">{{$qty}}</td>
    </tr>
    <tr>
        <td class="left">Total:</td>
        <td class="right">{{ number_format((float)$total, 2) }}</td>
    </tr>
     <tr>
        <td class="left">Discount:</td>
        <td class="right">{{ number_format((float)$invoice->party_amount, 2) }}</td>
    </tr>
    <tr>
        <td class="left">Credit:</td>
        <td class="right">{{ number_format((float)$invoice->creditpay ?? 0, 2) }}</td>
    </tr>
    <tr>
        <td class="left">Round Off:</td>
        <td class="right">{{ number_format((float)$invoice->roundof ?? 0, 2) }}</td>
    </tr>
    {{-- <tr>
        <td class="left">Total Savings:</td>
        <td class="right">0</td>
    </tr> --}}
    {{-- <tr class="bold">
        <td class="left">Total Paid:</td>
        <td class="right">0</td>
    </tr> --}}
</table>
<div class="line"></div>

<table class="table">
    <tr class="total">
        <td class="left">TOTAL:</td>
        <td></td>
        <td></td>
        <td class="center">{{ $invoice->total_item_qty }}</td>
        <td class="right">{{ $invoice->total}}</td>
    </tr>
</table>

<div class="line"></div>

<div class="centered bold" style="font-size: 14px;">
    Thank you for shopping with us!
</div>

</body>
</html>
