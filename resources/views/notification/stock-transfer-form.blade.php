<form id="priceUpdateForm">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="approveModalLabel">Stock Transfer Detail</h5>
        <button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body">
        <div class="container mt-1">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <p><strong>From:</strong> {{ $from_store }}</p>
                        </div>
                        <div class="col-lg-6">
                            <p><strong>To:</strong> {{ $to_store }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><strong>Transfer Items</strong></div>
                <div class="card-body p-0">
                    @if ($transfer_type == 'approved_stock')
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Transfer Date</th>
                                    <th>Quantity</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            @php
                                $totalQty = $stockTransfer->sum('approved_quantity');
                            @endphp
                            <tbody>
                                @foreach ($stockTransfer as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->product_name }}</td>
                                        <td>{{ $item->approved_at }}</td>
                                        <td>{{ $item->approved_quantity }}</td>
                                        <td>{{ $item->status }}</td>
                                    </tr>
                                @endforeach
                                @if ($stockTransfer->isEmpty())
                                    <tr>
                                        <td colspan="6" class="text-center">No items found.</td>
                                    </tr>
                                @endif
                            </tbody>
                            @if ($stockTransfer->isNotEmpty())
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total:</th>
                                        <th>{{ $totalQty }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    @else
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Transfer Number</th>
                                    <th>Transfer Date</th>
                                    <th>Quantity</th>
                                    <th>Status</th>
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
                                        <td>{{ $item->transfer_number }}</td>
                                        <td>{{ $item->transferred_at }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ $item->status }}</td>
                                    </tr>
                                @endforeach
                                @if ($stockTransfer->isEmpty())
                                    <tr>
                                        <td colspan="5" class="text-center">No items found.</td>
                                    </tr>
                                @endif
                            </tbody>
                            @if ($stockTransfer->isNotEmpty())
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total:</th>
                                        <th>{{ $totalQty }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    @endif
                </div>
            </div>
            <!-- Page end  -->
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal"
            onclick="nfModelCls()">Close</button>
        {{-- <button type="submit" class="btn btn-primary">Save changes</button> --}}
    </div>
</form>
