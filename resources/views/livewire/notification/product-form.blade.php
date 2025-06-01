<form id="stockRequestForm" action="{{ route('stock.stock-request-from-store') }}" method="POST">
    @csrf
    <div class="modal-body">
        <div class="container">
            <h5 class="mb-3">Low Stock Products</h5>

            <input type="hidden" name="store_id" value="{{ $branch_id }}" />

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Brand</th>
                        <th>Low Level Stock</th>
                        <th>Current Stock</th>
                        @if ($branch_id != 1)
                            <th>Request Quantity</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->brand }}</td>
                            <td>{{ $product->reorder_level }}</td>
                            <td>{{ $product->total_stock }}</td>
                            @if ($branch_id != 1)
                                <td>
                                    <input type="number" name="items[{{ $product->id }}][quantity]"
                                        class="form-control" min="1" placeholder="Enter quantity">
                                    <input type="hidden" name="items[{{ $product->id }}][product_id]"
                                        value="{{ $product->id }}">
                                </td>
                            @endif
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="text-center">✅ All products are above Low Level Stock.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($branch_id != 1)
                <div class="form-group mt-3">
                    <label for="notes">Notes</label>
                    <input type="text" name="notes" class="form-control" placeholder="Enter notes...">
                </div>
            @endif
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" wire:click="closeNotificationDetail">Close</button>
        @if ($branch_id != 1)
            <button type="submit" class="btn btn-primary">Submit Stock Request</button>
        @endif
    </div>
</form>
