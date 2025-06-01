<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Demand Order Details</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            font-size: 14px;
            color: #333;
            margin: 20px;
        }

        h2 {
            text-align: center;
            font-size: 22px;
            margin-bottom: 20px;
            color: #222;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 8px 10px;
            border: 1px solid #999;
            text-align: left;
        }

        thead {
            background-color: #f0f0f0;
        }

        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        .header-table th {
            width: 25%;
            background-color: #f9f9f9;
        }

        .section-title {
            font-weight: bold;
            margin: 20px 0 10px;
            font-size: 16px;
        }

    </style>
</head>
<body>

<h2 class="mb-0">
    Demand Order Details
    <span class="h6 text-muted ms-2" style="">{{ now()->format('d-m-Y H:i') }}</span>
</h2>


    {{-- Shop & Order Info Table --}}
    <table class="header-table">
        <tr>
            <th>Demand Date</th>
            <th>{{ \Carbon\Carbon::parse($data['demand_date'])->format('d-m-Y h:i A') }}</th>
        </tr>
        <tr>
            <th>Name</th>
            <td>Nag Ji Ram</td>
        </tr>
        <tr>
            <th>Delivery Address</th>
            <td>Shop No. 1 MAHARANA PRATAP CHOUK, RANIWARA (RANIWARA KALLAN/KHURD, JALERA KHURD, MEDA)</td>
        </tr>
        <tr>
            <th>Demand Quantity</th>
            <td >
                {{ count($data['products']) }} 
            </td>
        </tr>
    </table>

    {{-- Notes --}}
    @if(!empty($data['notes']))
        <p class="section-title">Notes:</p>
        <p>{{ $data['notes'] }}</p>
    @endif

    {{-- Product Table --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product Name</th>
                <th>MRP </th>
                <th>Quantity</th>
                <th>Cost price	 </th>
                <th>Amount </th>
            </tr>
        </thead>
        <tbody>
            @php $totalAmount = 0; @endphp
            @foreach ($data['products'] as $key => $product)
                @if(is_numeric($key))
                    <tr>
                        <td>{{ $key }}</td>
                        <td>{{ $product['brand_name'] }}</td>
                        <td>{{ number_format($product['mrp'], 2) }}</td>
                        <td>{{ $product['qnt'] }}</td>
                        <td>{{ number_format($product['rate'], 2) }}</td>
                        <td>{{ number_format($product['amount'], 2) }}</td>
                    </tr>
                    @php $totalAmount += $product['amount']; @endphp
                @endif
            @endforeach
            <tr class="total-row">
                <td colspan="5" style="text-align: right;">Total Amount </td>
                <td>{{ number_format($totalAmount, 2) }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>
