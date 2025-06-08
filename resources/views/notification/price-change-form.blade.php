<form id="priceUpdateForm">
    @csrf
    <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="approveModalLabel">Product Price Change</h5>
        <button type="button" class="close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body">
        <div class="container-fluid">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h6 class="text-muted">Product Details</h6>
                            <hr>
                        </div>

                        <div class="col-md-6 mb-2">
                            <strong>Name:</strong>
                            <p class="mb-0">{{ $priceChange->name }}</p>
                        </div>

                        <div class="col-md-3 mb-2">
                            <strong>Old Price:</strong>
                            <p class="mb-0 text-danger">₹{{ number_format($priceChange->old_price, 2) }}</p>
                        </div>

                        <div class="col-md-3 mb-2">
                            <strong>New Price:</strong>
                            <p class="mb-0 text-success">₹{{ number_format($priceChange->new_price, 2) }}</p>
                        </div>

                        <div class="col-md-6 mb-2">
                            <strong>Applicable Date:</strong>
                            <p class="mb-0">{{ \Carbon\Carbon::parse($priceChange->changed_at)->format('d M Y') }}</p>
                        </div>

                        <div class="col-md-6 mb-2">
                            <strong>Change Created At:</strong>
                            <p class="mb-0">
                                {{ \Carbon\Carbon::parse($priceChange->created_at)->format('d M Y h:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        
        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
        {{-- <button type="submit" class="btn btn-primary">Save changes</button> --}}
    </div>
</form>
