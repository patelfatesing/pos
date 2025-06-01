<form id="priceUpdateForm">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="approveModalLabel">Stock Request Detail</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body">
        <div class="container mt-1">
            <h4 class="mb-4">Stock Request #{{ $stockRequest->id }}</h4>

            <div class="card mb-4">
                <div class="card-body">
                    <p><strong>Store:</strong> {{ $stockRequest->store->name ?? 'warehouse' }}</p>
                    <p><strong>Requested By:</strong> {{ $stockRequest->user->name ?? 'N/A' }}</p>
                    <p><strong>Date:</strong> {{ $stockRequest->requested_at->format('d M Y h:i A') }}</p>
                    <p><strong>Status:</strong>
                        <span
                            class="badge 
                                    {{ $stockRequest->status === 'pending'
                                        ? 'bg-warning'
                                        : ($stockRequest->status === 'approved'
                                            ? 'bg-success'
                                            : 'bg-danger') }}">
                            {{ ucfirst($stockRequest->status) }}
                        </span>
                    </p>
                    <p><strong>Notes:</strong> {{ $stockRequest->notes ?? '-' }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><strong>Requested Items</strong></div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Brand</th>
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
        <button type="submit" class="btn btn-primary">Approve</button>
    </div>
</form>
