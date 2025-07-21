<form id="priceUpdateForm">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title" id="approveModalLabel">Expired Products</h5>
        <button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body">
        <div class="row">
            <div class="container mt-1">
                @if ($expiredProducts->isEmpty())
                    <p>No expired products found.</p>
                @else
                    <table class="table table-striped">
                        <thead class=" table-info">
                            <tr>
                                <th>Product Name</th>
                                <th>Batch No</th>
                                <th>Expiry Date</th>
                                <th>Quantity</th>
                                <th>Barcode</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($expiredProducts as $inventory)
                                <tr>
                                    <td>{{ $inventory->product_name }}</td>
                                    <td>{{ $inventory->batch_no }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($inventory->expiry_date)->format('Y-m-d') }}
                                    </td>
                                    <td>{{ $inventory->quantity }}</td>
                                    <td>{{ $inventory->barcode }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
        {{-- <button type="submit" class="btn btn-primary">Save changes</button> --}}
    </div>
</form>
