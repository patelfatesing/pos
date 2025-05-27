<form id="priceUpdateForm">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="approveModalLabel">Stock Transfer Detail</h5>
    </div>

    <div class="modal-body">
        <div class="container mt-1">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <p><strong>From:</strong> Warehouse</p>
                        </div>
                        <div class="col-lg-6">
                            {{-- <p><strong>Date Time:</strong> Warehouse</p> --}}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><strong>Transfer Items</strong></div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Brand</th>
                                <th>SKU</th>
                                <th>Transfer Number</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stockTransfer as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->brand }}</td>
                                    <td>{{ $item->sku }}</td>
                                    <td>{{ $item->transfer_number }}</td>
                                    <td>{{ $item->quantity }}</td>
                                </tr>
                            @endforeach
                            @if ($stockTransfer->isEmpty())
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
        <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="nfModelCls()">Close</button>
        {{-- <button type="submit" class="btn btn-primary">Save changes</button> --}}
    </div>
</form>
