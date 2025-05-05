<form id="stockRequestForm" action="{{ route('stock.stock-request-from-store') }}" method="POST">
    @csrf
    <div class="modal-body">
        <div class="container">
            <h5 class="mb-3">Low Stock Products</h5>

            <input type="hidden" name="store_id" value="2" />

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Brand</th>
                        <th>Low Level Stock</th>
                        <th>Current Stock</th>
                        <th>Request Quantity</th>
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
                            <td>
                                <input type="number" name="items[{{ $product->id }}][quantity]" class="form-control"
                                    min="1" placeholder="Enter quantity">
                                <input type="hidden" name="items[{{ $product->id }}][product_id]"
                                    value="{{ $product->id }}">
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="text-center">✅ All products are above Low Level Stock.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="form-group mt-3">
                <label for="notes">Notes</label>
                <input type="text" name="notes" class="form-control" placeholder="Enter notes...">
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary"
        wire:click="closeNotificationDetail">Close</button>
        <button type="submit" class="btn btn-primary">Submit Stock Request</button>
    </div>
</form>
