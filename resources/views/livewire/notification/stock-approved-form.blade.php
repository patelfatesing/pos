<form id="priceUpdateForm">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="approveModalLabel">Stock Request Detail</h5>
    </div>

    <div class="modal-body">
        <div class="container mt-1">
            <div class="card mb-4">
                <div class="card-body">

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Store: </label> <span
                                    class="ml-2">{{ $stockRequest->store->name ?? 'warehouse' }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Approved By: </label>
                                <span class="ml-2"> Admin</span>

                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Date:</label>
                                <span class="ml-2"> {{ $stockRequest->approved_at->format('d M Y h:i A') }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status: </label>
                                <span
                                    class=" ml-2 badge 
                                            {{ $stockRequest->status === 'pending'
                                                ? 'bg-warning'
                                                : ($stockRequest->status === 'approved'
                                                    ? 'bg-success'
                                                    : 'bg-danger') }}">
                                    {{ ucfirst($stockRequest->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Notes: </label>
                                <span class="ml-2"> {{ $stockRequest->notes ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-2">
                <div class="card-header"><strong>Requested Items</strong></div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Brand</th>
                                <th>SKU</th>
                                <th>Size</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stockRequest->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->product->name }}</td>
                                    <td>{{ $item->product->brand }}</td>
                                    <td>{{ $item->product->sku }}</td>
                                    <td>{{ $item->product->size }}</td>
                                    <td>{{ $item->quantity }}</td>
                                </tr>
                            @endforeach
                            @if ($stockRequest->items->isEmpty())
                                <tr>
                                    <td colspan="6" class="text-center">No items found.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Page end  -->
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    </div>
</form>
