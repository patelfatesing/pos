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
            padding: 6px 4px;
            vertical-align: top;
            font-size: 13px;
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
            font-size: 15px;
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
    @if(@$duplicate == true)
        <div style="color: red; font-size: 14px;">Duplicate Invoice</div>
    @endif
    <div>
        @if(@$type == 'refund')
            <span style="font-size: 14px;">Refund Note</span>
        @else
            <span style="font-size: 14px;">Invoice</span>
        @endif
    </div>
</div>

<div class="line"></div>

<div>
    <strong>{{ @$type == 'refund' ? 'Refund' : 'Invoice' }}:</strong> {{ $invoice->invoice_number }}<br>
    <strong>Name:</strong> {{ $customer_name ?? '' }}<br>
    <strong>Date:</strong> {{ now()->format('d/m/Y H:i') }}
</div>

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
        @foreach($items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td class="left">
                {{ strlen($item['name']) > 10 ? substr($item['name'], 0, 10) . '...' . substr($item['name'], -5) : $item['name'] }}
            </td>
            <td class="center">{{ $item['quantity'] }}</td>
            <td class="center">{{ $item['mrp']}}</td>
            <td class="right">{{ $item['quantity']*$item['mrp']}}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="line"></div>

@php
    $totalAmount = (float) str_replace(',', '', $invoice->total);
    $discountAmount = (float) str_replace(',', '', $invoice->party_amount ?? 0);
    $sunTot = $totalAmount + $discountAmount;
@endphp

<table class="table">
    <tr class="total">
        <td class="left">TOTAL:</td>
        <td class="right">{{ number_format((float)$refund->total_item_price,2 ) }}</td>
    </tr>
</table>

<div class="line"></div>

<table class="table">
    <tr>
        <td class="left">Refunded:</td>
        <td class="right">{{ $refund->total_mrp }}</td>
    </tr>
    <tr>
        <td class="left">Refunded Items:</td>
        <td class="right">{{ $refund->total_item_qty }}</td>
    </tr>
    <tr>
        <td class="left">Discount:</td>
        <td class="right">{{ number_format($refund->party_amount > 0 ? -($refund->party_amount) : $refund->party_amount ?? 0, 2) }}</td>

    </tr>
    <tr>
        <td class="left">Credit:</td>
        <td class="right">
            {{ $refund->refund_credit_amount > 0 ? '-' . $refund->refund_credit_amount : $refund->refund_credit_amount }}
        </td>
    </tr>
    <tr>
        <td class="left">Round Off:</td>
        <td class="right">
            {{ number_format((float) $invoice->roundof ?? 0, 2) }}
        </td>
    </tr>

  
    <tr class="bold">
        <td class="left">Total Paid:</td>
        <td class="right">{{ number_format((float)$refund->amount,2) }}</td>
    </tr>
</table>

<div class="line"></div>

<table class="table">
    <tr>
        <td class="left">By Cash:</td>
        <td class="right">{{ number_format((float)$refund->amount,2) }}</td>
    </tr>
    {{-- <tr>
        <td class="left">By UPI:</td>
        <td class="right">{{ $invoice->upi_amount }}</td>
    </tr>
     <tr>
        <td class="left">By Online:</td>
        <td class="right">{{ $invoice->online_amount }}</td>
    </tr> --}}
</table>

<div class="line"></div>

<div class="centered bold" style="font-size: 14px;">
    Thank you for shopping with us!
</div>

</body>
</html>
