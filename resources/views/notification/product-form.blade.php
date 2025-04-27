
        <form id="priceUpdateForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">Low stock Product List Product</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <input type="hidden" name="product_id" id="product_id" value="">
                    <div class="container">
                        <h2 class="mb-4">⚠️ Low Stock Products</h2>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Brand</th>
                                    <th>Low Level Level</th>
                                    <th>Current Stock</th>
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
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">✅ All products are above reorder level.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                {{-- <button type="submit" class="btn btn-primary">Save changes</button> --}}
            </div>
        </form>
   