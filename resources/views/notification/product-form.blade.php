<form id="stockRequestForm" action="{{ route('stock.stock-request-from-store') }}" method="POST">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">⚡ Low Stock Products</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <div class="container">
            <span class="main-screen-frame280">
                <h5 class="mb-3 main-screen-text72">Store : {{ $branch_name }}</h5>
            </span>
            <input type="hidden" name="store_id" value="2" />
            <table class="table table-bordered">
                <thead class="table-info">
                    <tr>
                        <th>Product</th>
                        <th>Low Level Stock</th>
                        <th>Current Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalCurrentStock = 0;
                    @endphp

                    @forelse($lowStockProducts as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->low_level_qty }}</td>
                            <td>{{ $product->total_stock }}</td>
                        </tr>
                        @php
                            $totalCurrentStock += $product->total_stock;
                        @endphp
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">✅ All products are above Low Level Stock.</td>
                        </tr>
                    @endforelse

                    @if ($lowStockProducts->count())
                        <tr>
                            <td colspan="2" class="text-start font-weight-bold total_bgc">Total Current Stock:</td>
                            <td class="font-weight-bold total_bgc">{{ $totalCurrentStock }}</td>
                        </tr>
                    @endif


                </tbody>
            </table>
            <tr>
                <input type="text" name="notes" class="form-control" placeholder="Enter Notes">
            </tr>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
        {{-- <button type="submit" class="btn btn-primary">Submit Stock Request</button> <!-- 🆕 --> --}}
    </div>
</form>
