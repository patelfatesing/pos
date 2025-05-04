<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LiquorHub</title>
</head>
<body class="print-only">
    <style>
        @page {
            size: 100mm 215mm;
            margin: 0;
        }

        @media print {
            @page {
                size: 100mm 215mm;
            }
        }

        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            font-size: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .invoice-container {
            width: 100mm;
            height: 215mm;
            padding: 10px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        .text-center {
            text-align: center;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .mb-1 {
            margin-bottom: 5px;
        }

        .mb-3 {
            margin-bottom: 15px;
        }

        .mt-2 {
            margin-top: 10px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th, .table td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
        }

        .table th {
            background-color: #f8f8f8;
        }

        .table-sm th, .table-sm td {
            font-size: 11px;
        }

        .divider {
            border-top: 1px dashed #ccc;
            margin: 10px 0;
        }

        .footer {
            font-size: 10px;
            text-align: center;
            margin-top: 10px;
        }
    </style>

    <div class="invoice-container">
        <div class="text-center">
            <h5 class="mb-0">LiquorHub</h5>
            <p class="mb-0">Rajpath Road, Ahmedabad, Gujarat, India</p>
            <h6 class="mt-2">Invoice</h6>
        </div>

        <div class="divider"></div>

        <div>
            <p class="mb-1"><strong>Invoice No:</strong> {{ $invoiceData->invoice_number }}</p>
            <p class="mb-1"><strong>Name:</strong> {{ $invoiceData->customer_name }}</p>
            <p class="mb-3"><strong>Date:</strong> {{ $invoiceData->created_at->format('d/m/Y') }}</p>
        </div>

        <table class="table table-sm">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Name</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoiceData->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>{{ number_format($item['price'], 2) }}</td>
                        <td>{{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <div>
            <p><strong>Sub Total:</strong> ₹{{ number_format($invoiceData->sub_total, 2) }}</p>
            @if($invoiceData->commission_amount > 0)
                <p><strong>Commission:</strong> -₹{{ number_format((Int)$invoiceData->commission_amount, 2) }}</p>
            @endif
            @if($invoiceData->party_amount > 0)
                <p><strong>Party Deduction:</strong> -₹{{ number_format((Int)$invoiceData->party_amount, 2) }}</p>
            @endif
            <p><strong>Total:</strong> ₹{{ number_format((Int)$invoiceData->total, 2) }}</p>
            <p><strong>Paid By:</strong> Cash</p>
            <p><strong>Change:</strong> ₹0.00</p>
        </div>

        <div class="divider"></div>

        <div>
            <p><strong>Terms & Conditions:</strong></p>
            <p>Goods can be exchanged within 24 hours with the original invoice.</p>
            <p>No exchange on perishables or sensitive items.</p>
        </div>

        <div class="footer">
            <p>Thank you for shopping with us!</p>
            <p>Printed On: {{ now()->format('d/m/Y h:i A') }}</p>
        </div>
    </div>
</body>

</html>