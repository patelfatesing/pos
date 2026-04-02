@foreach ($grouped as $key => $sales)
    @php
        $first = $sales->first();
        $date = \Carbon\Carbon::parse($first->created_at)->format('d-m-Y');
    @endphp

    <tr class="store-row" data-id="{{ $key }}" style="background:#f1f1f1; cursor:pointer;">

        <td>
            <strong>{{ $first->branch->name }} ({{ $date }})</strong>
        </td>

        <td>
            <div class="d-flex justify-content-between align-items-center">

                <span>
                    ₹{{ number_format($sales->sum(fn($i) => (float) $i->total), 2) }}
                </span>

                <!-- VIEW BUTTON -->
                <a class="badge bg-warning view-row open-shift" data-shift="{{ $first->shift_id }}">
                    View
                </a>

            </div>
        </td>

    </tr>

    <tr class="sales-row d-none" id="sales-{{ $key }}">
        <td colspan="2">

            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Date</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($sales as $sale)
                        <tr>
                            <td>{{ $sale->invoice_number }}</td>
                            <td>{{ $sale->created_at }}</td>
                            <td>₹{{ number_format((float) $sale->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>

            </table>

        </td>
    </tr>
@endforeach
