<div class="container-fluid">
    @php
        // Helper to safely format any value as 2-decimal number
        $nf = function ($value) {
            return number_format((float) ($value ?? 0), 2);
        };
    @endphp

    {{-- HEADER --}}
    <div class="row mb-2">
        <div class="col-md-6">
            <h6 class="mb-1">
                Invoice #{{ $voucher->invoice_number ?? $voucher->id }}
            </h6>
            <div class="small text-muted">
                Date:
                {{ optional($voucher->created_at)->format('d-m-Y H:i') }}
            </div>

            @if (!empty($voucher->party_user_id))
                <div class="small">
                    Customer ID: {{ $voucher->party_user_id }}
                    {{-- If you have relation: {{ optional($voucher->party)->name }} --}}
                </div>
            @endif
        </div>

        <div class="col-md-6 text-end">
            <div>
                <strong>Total:</strong>
                {{ $nf($voucher->total ?? ($voucher->grand_total ?? 0)) }}
            </div>
            <div class="small">
                Cash: {{ $nf($voucher->cash_amount ?? 0) }}
            </div>
            <div class="small">
                UPI: {{ $nf($voucher->upi_amount ?? 0) }}
            </div>
            <div class="small">
                Online: {{ $nf($voucher->online_amount ?? 0) }}
            </div>
        </div>
    </div>

    {{-- ITEMS TABLE --}}
    @php
        // If items stored as JSON string, decode it safely
        if (is_array($voucher->items ?? null)) {
            $items = $voucher->items;
        } else {
            $items = json_decode($voucher->items ?? '[]', true) ?: [];
        }
    @endphp

    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 40px">#</th>
                    <th>Item</th>
                    <th class="text-end" style="width: 80px">Qty</th>
                    <th class="text-end" style="width: 100px">Rate</th>
                    <th class="text-end" style="width: 120px">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $i => $it)
                    @php
                        // Try to detect common keys from your POS
                        $name = $it['name'] ?? ($it['product_name'] ?? ($it['item_name'] ?? ''));

                        $qty = $it['qty'] ?? ($it['quantity'] ?? ($it['qty_sold'] ?? 0));

                        $rate = $it['rate'] ?? ($it['price'] ?? ($it['mrp'] ?? 0));

                        $amount = $it['amount'] ?? ($it['net_amount'] ?? ($it['total'] ?? $qty * $rate));
                    @endphp

                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $name }}</td>
                        <td class="text-end">{{ $nf($qty) }}</td>
                        <td class="text-end">{{ $nf($rate) }}</td>
                        <td class="text-end">{{ $nf($amount) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">
                            No items found for this invoice.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- OPTIONAL FOOTER INFO --}}
    @if (!empty($voucher->note) || !empty($voucher->remarks))
        <div class="mt-2 small">
            <strong>Remarks:</strong>
            {{ $voucher->note ?? $voucher->remarks }}
        </div>
    @endif
</div>
