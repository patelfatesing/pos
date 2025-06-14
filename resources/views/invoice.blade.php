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
    @if(@$duplicate == true)
        <div style="font-size: 14px;">( Duplicate )</div>
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
    @if(!empty($ref_no))
    <strong>Ref:</strong> {{ $ref_no."-".\Carbon\Carbon::parse($hold_date)->format('H:i') }}<br>
    @endif
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
            <td class="center">{{ $item['price'] / $item['quantity'] }}</td>
            <td class="right">{{ number_format((float)$item['price'], 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="line"></div>

<table class="table">
    <tr class="total">
        <td class="left">TOTAL:</td>
        <td></td>
        <td></td>
        <td class="center">{{ $invoice->total_item_qty }}</td>
        <td class="right">{{ number_format((float)$invoice->total_item_total, 2) }}</td>
    </tr>
</table>

<div class="line"></div>

<table class="table">
    <tr>
        <td class="left">Purchased:</td>
        <td class="right">{{ number_format((float)$invoice->sub_total, 2) }}</td>
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
        <td class="left">Total Savings:</td>
        <td class="right">{{ number_format(
        (float)str_replace(',', '', $invoice->sub_total) - 
        (float)str_replace(',', '', $invoice->total), 
        2
        )
        }}</td>
    </tr>
    <tr class="bold">
        <td class="left">Total Paid:</td>
        <td class="right">{{ $invoice->total }}</td>
    </tr>
</table>

<div class="line"></div>

<table class="table">
    <tr>
        <td class="left">By Cash:</td>
        <td class="right">{{ number_format((float)$invoice->cash_amount, 2) }}</td>
    </tr>
    <tr>
        <td class="left">By UPI:</td>
        <td class="right">{{ number_format((float)($invoice->upi_amount + $invoice->online_amount), 2) }}</td>
    </tr>
</table>

<div class="line"></div>

<div class="centered bold" style="font-size: 14px;">
    Thank you for shopping with us!
</div>

</body>
</html>
