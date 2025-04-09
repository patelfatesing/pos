<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice</title>
    <style>
        body { font-family: sans-serif; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ccc; padding: 8px; }
    </style>
</head>
<body>
    <h2>Invoice</h2>
    <p><strong>Date:</strong> {{ \Carbon\Carbon::now()->format('d M Y') }}</p>
    @if(!empty($commissionUser))
        <p><strong>Commission User:</strong> {{ $commissionUser->first_name }} {{ $commissionUser->last_name }}</p>
    @endif
    @if(!empty($partyUser))
    <p><strong>Party User:</strong> {{ $partyUser->first_name }} {{ $partyUser->last_name }}</p>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cartitems as $item)
            <tr>
                <td>{{ $item['name'] }}</td>
                <td>{{ $item['quantity'] }}</td>
                <td>{{ number_format($item['price'], 2) }}</td>
                <td>{{ number_format($item['price'] * $item['quantity'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p><strong>Subtotal:</strong> {{ number_format($sub_total, 2) }}</p>
    <p><strong>Tax (18%):</strong> {{ number_format($tax, 2) }}</p>
    @if($commissionAmount > 0)
        <p><strong>Commission Deduction:</strong> -{{ number_format($commissionAmount, 2) }}</p>
    @endif
    @if($partyAmount > 0)
        <p><strong>Party Deduction:</strong> -{{ number_format($partyAmount, 2) }}</p>
    @endif
    <p><strong>Total:</strong> {{ number_format($total, 2) }}</p>
</body>
</html>
