<form id="priceUpdateForm">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="approveModalLabel">Stock Transfer Detail</h5>
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
                    <div class="row">
                        <div class="col-lg-6 main-screen-frame280">
                            <span class="main-screen-text72"><strong>From:</strong> {{ $from_store }}</span>
                        </div>
                        <div class="col-lg-6">
                            <p><strong>To:</strong> {{ $to_store }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header header_bgc"><strong>Transfer Items</strong></div>
                <div class="card-body p-1">
                    @if ($transfer_type == 'approved_stock')
                        <table class="table table-bordered">
                            <thead class="table-info">
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
                                        <th colspan="3" class="text-start total_bgc">Total:</th>
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
                                        <th colspan="3" class="text-start total_bgc">Total:</th>
                                        <th class="total_bgc">{{ $totalQty }}</th>
                                        <th colspan="2" class="total_bgc"></th>
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
