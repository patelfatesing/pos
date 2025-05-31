    <div class="modal-header">
        <h5 class="modal-title">
            @if (empty($photos->image_path) && empty($photos->product_image_path) && !empty($photos->transaction_image_path))
                Transaction Image
            @else
                {{ $imageType }} Customer Photos
            @endif
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center">
                    <h6>Customer Photo</h6>
                    @if (!empty($photos->image_path) && file_exists(public_path('storage/' . $photos->image_path)))
                        <img src="{{ asset('storage/' . $photos->image_path) }}" 
                            class="rounded shadow w-full max-h-64 object-contain" 
                            alt="Customer Photo" />
                    @else
                        <div class="bg-gray-100 rounded shadow flex items-center justify-center h-64 text-gray-500">
                            No Image Found
                        </div>
                    @endif
                </div>
                <div class="col-md-6 text-center">
                    <h6>Product Photo</h6>
                     @if (!empty($photos->product_image_path) && file_exists(public_path('storage/' . $photos->product_image_path)))
                        <img src="{{ asset('storage/' . $photos->product_image_path) }}" 
                            class="rounded shadow w-full max-h-64 object-contain" 
                            alt="Product Photo" />
                    @else
                        <div class="bg-gray-100 rounded shadow flex items-center justify-center h-64 text-gray-500">
                            No Image Found
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
