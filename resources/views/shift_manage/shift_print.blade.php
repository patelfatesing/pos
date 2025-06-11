<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .title { font-weight: bold; font-size: 16px; text-align: center; }
        .section { margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px; text-align: left; }
    </style>
</head>
<body>
    <div class="title">SR SHOP – Shift Report</div>

    <div class="section">
        <p><strong>User Staff:</strong> {{ $shift->user->name ?? 'N/A' }}</p>
        <p><strong>Start Date:</strong> {{ $shift->start_time }}</p>
        <p><strong>End Date:</strong> {{ $shift->end_time }}</p>
    </div>

    <div class="section">
        <strong>Payment Summary:</strong>
        <table>
            <tr><td>Cash</td><td>{{ $categoryTotals['payment']['CASH'] ?? 0 }}</td></tr>
            <tr><td>UPI Payment</td><td>{{ $categoryTotals['payment']['UPI PAYMENT'] ?? 0 }}</td></tr>
            <tr><td>Total</td><td>{{ $categoryTotals['payment']['TOTAL'] ?? 0 }}</td></tr>
        </table>
    </div>

    <div class="section">
        <strong>Sales Summary:</strong>
        <table>
            @foreach ($categoryTotals['sales'] ?? [] as $category => $amount)
                <tr><td>{{ $category }}</td><td>{{ $amount }}</td></tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <strong>Summary:</strong>
        <table>
            @foreach ($categoryTotals['summary'] ?? [] as $key => $val)
                <tr><td>{{ $key }}</td><td>{{ $val }}</td></tr>
            @endforeach
        </table>
    </div>
</body>
</html>
