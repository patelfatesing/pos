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
            font-size: 11px;
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
            margin: 6px 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 4px 2px;
            vertical-align: top;
            font-size: 11px;
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
            font-size: 13px;
            font-weight: bold;
        }

        .small {
            font-size: 10px;
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
            <th style="width: 5%;">#</th>
            <th style="width: 50%;" class="left">Item</th>
            <th style="width: 15%;" class="center">Qty</th>
            <th style="width: 30%;" class="right">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td class="left">
                {{ strlen($item['name']) > 15 ? substr($item['name'], 0, 12) . '...' : $item['name'] }}
            </td>
            <td class="center">{{ $item['quantity'] }}</td>
            <td class="right">{{ number_format($item['price'], 2) }}</td>
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
    <tr>
        <td class="left">SUB TOTAL:</td>
        <td class="right">{{ number_format($sunTot, 2) }}</td>
    </tr>
    <tr>
        <td class="left">DISCOUNT:</td>
        <td class="right">{{ number_format($invoice->party_amount ?? 0, 2) }}</td>
    </tr>
    <tr class="total">
        <td class="left">TOTAL:</td>
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

<div class="line"></div>

<div class="centered bold">
    Thank you for shopping with us!
</div>

</body>
</html>
