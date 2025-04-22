<div class="print-only p-4" style="font-family: monospace; max-width: 400px; margin: auto;">
    <div class="text-center">
        <h5 class="mb-0">LiquorHub</h5>
        <p class="mb-0">Rajpath Road, Ahmedabad, Gujarat, India</p>
        <h6 class="mt-2">Invoice</h6>
    </div>

    <hr>

    <div>
        <p class="mb-1"><strong>Invoice No:</strong> {{ $invoiceData->invoice_number }}</p>
        <p class="mb-1"><strong>Name:</strong> {{ $invoiceData->customer_name }}</p>
        <p class="mb-3"><strong>Date:</strong> {{ $invoiceData->created_at->format('d/m/Y') }}</p>
    </div>

    <table class="table table-sm" style="font-size: 12px;">
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

    <hr>

    <div style="font-size: 13px;">
        <p><strong>Sub Total:</strong> ₹{{ number_format($invoiceData->sub_total, 2) }}</p>
        @if($invoiceData->commission_amount > 0)
            <p><strong>Commission:</strong> -₹{{ number_format($invoiceData->commission_amount, 2) }}</p>
        @endif
        @if($invoiceData->party_amount > 0)
            <p><strong>Party Deduction:</strong> -₹{{ number_format($invoiceData->party_amount, 2) }}</p>
        @endif
        <p><strong>Total:</strong> ₹{{ number_format($invoiceData->total, 2) }}</p>
        <p><strong>Paid By:</strong> Cash</p>
        <p><strong>Change:</strong> ₹0.00</p>
    </div>

    <hr>

    <div style="font-size: 11px;">
        <p><strong>Terms & Conditions:</strong></p>
        <p>Goods can be exchanged within 24 hours with the original invoice.</p>
        <p>No exchange on perishables or sensitive items.</p>
    </div>

    <div class="text-center mt-3">
        <p>Thank you for shopping with us!</p>
        <p>Printed On: {{ now()->format('d/m/Y h:i A') }}</p>
    </div>
</div>