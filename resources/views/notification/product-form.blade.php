<form id="stockRequestForm" action="{{ route('stock.stock-request-from-store') }}" method="POST">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">⚡ Low Stock Products</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body">
        <div class="container">
            <h5 class="mb-3">Low Stock Products</h5>
            <input type="hidden" name="store_id" value="2"/>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Brand</th>
                        <th>Low Level Stock</th>
                        <th>Current Stock</th>
                        {{-- <th>Request Quantity</th> <!-- 🆕 --> --}}
                    </tr>
                </thead>
                <tbody>
                    @forelse($lowStockProducts as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->brand }}</td>
                            <td>{{ $product->reorder_level }}</td>
                            <td>{{ $product->total_stock }}</td>
                            {{-- <td>
                                <input type="number" 
                                       name="items[{{ $product->id }}][quantity]" 
                                       class="form-control" 
                                       min="1"
                                       placeholder="Enter quantity">
                                <input type="hidden" 
                                       name="items[{{ $product->id }}][product_id]" 
                                       value="{{ $product->id }}">
                            </td> --}}
                        </tr>
                       
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">✅ All products are above Low Level Stock.</td>
                        </tr>
                    @endforelse
                   
                </tbody>
            </table>
            <tr>
                <input type="text" 
                name="notes" 
                class="form-control" 
                
                placeholder="Enter Notes">
            </tr>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        {{-- <button type="submit" class="btn btn-primary">Submit Stock Request</button> <!-- 🆕 --> --}}
    </div>
</form>
