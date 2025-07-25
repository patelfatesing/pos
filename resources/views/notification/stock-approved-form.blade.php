<form id="priceUpdateForm">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="approveModalLabel">Stock Request Approved Detail</h5>
        @if (auth()->user()->hasRole('admin'))
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
        @else
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        @endif
    </div>

    <div class="modal-body">
        <div class="container mt-1">
            <div class="card mb-4">
                <div class="card-body">
                    @if ($transfer_type == 'approved_stock')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group main-screen-frame280">
                                    <span class="main-screen-text72"><strong>Store:</strong>
                                        {{ $stockRequest->store->name ?? 'warehouse' }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Requested By:</strong> {{ $stockRequest->user->name ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Date:</strong> {{ $stockRequest->requested_at->format('d M Y h:i A') }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status: </label>
                                    <span
                                        class="badge 
                                    {{ $stockRequest->status === 'pending'
                                        ? 'bg-warning'
                                        : ($stockRequest->status === 'approved'
                                            ? 'bg-success'
                                            : 'bg-danger') }}">
                                        {{ ucfirst($stockRequest->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Notes: </label>
                                    <span class="ml-2"> {{ $stockRequest->notes ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group main-screen-frame280">
                                    <span class="main-screen-text72"><strong>Store:</strong>
                                        {{ $stockRequest->store->name ?? 'warehouse' }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Requested By:</strong> {{ $stockRequest->user->name ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <strong>Date:</strong> {{ $stockRequest->requested_at->format('d M Y h:i A') }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status: </label>
                                    <span
                                        class="badge 
                                    {{ $stockRequest->status === 'pending'
                                        ? 'bg-warning'
                                        : ($stockRequest->status === 'approved'
                                            ? 'bg-success'
                                            : 'bg-danger') }}">
                                        {{ ucfirst($stockRequest->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Notes: </label>
                                    <span class="ml-2"> {{ $stockRequest->notes ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header header_bgc"><strong>Requested Items</strong></div>
                <div class="card-body p-1">
                    @if ($transfer_type == 'approved_stock')
                        <table class="table table-bordered mb-0">
                            <thead class="table-info">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Transfer Date</th>
                                    <th>Quantity</th>
                                    <th>Status</th>
                                    <th>From</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalQty = $stockTransfer->sum('approved_quantity');
                                @endphp
                                @foreach ($stockTransfer as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->product_name }}</td>
                                        <td>{{ $item->approved_at }}</td>
                                        <td>{{ $item->approved_quantity }}</td>
                                        <td>{{ $item->status }}</td>
                                        <td>{{ $item->source_branch_name }}</td>
                                    </tr>
                                @endforeach
                                @if ($stockTransfer->isEmpty())
                                    <tr>
                                        <td colspan="6" class="text-center">No items found.</td>
                                    </tr>
                                @endif
                            </tbody>
                            @if ($stockTransfer->isNotEmpty())
                                <tfoot class="">
                                    <tr class="">
                                        <th colspan="3" class="text-end total_bgc">Total:</th>
                                        <th class="total_bgc">{{ $totalQty }}</th>
                                        <th colspan="2" class="total_bgc"></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    @else
                        <table class="table table-bordered mb-0">
                            <thead class="table-info">
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Size</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalQty = 0;
                                @endphp

                                @foreach ($stockRequest->items as $index => $item)
                                    @php
                                        $totalQty += $item->quantity;
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->product->name }}</td>
                                        <td>{{ $item->product->size }}</td>
                                        <td>{{ $item->quantity }}</td>
                                    </tr>
                                @endforeach
                                @if ($stockRequest->items->isEmpty())
                                    <tr>
                                        <td colspan="6" class="text-center">No items found.</td>
                                    </tr>
                                @else
                                    <tr class="">
                                        <td colspan="4" class="text-right font-weight-bold total_bgc">Total Quantity:
                                        </td>
                                        <td class="font-weight-bold total_bgc">{{ $totalQty }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
            <!-- Page end  -->
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
        {{-- <button type="submit" class="btn btn-primary">Approve</button> --}}
    </div>
</form>
