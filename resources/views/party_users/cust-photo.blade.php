    <div class="modal-header">
        <h5 class="modal-title"> Party Customer Photos</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center">
                    <h6>Customer Photo</h6>
                    <img id="customerPhoto" src="{{ asset('storage/' . $photos[0]->image_path) }}"
                        class="img-fluid rounded" alt="Customer Photo" />
                </div>
                <div class="col-md-6 text-center">
                    <h6>Product Photo</h6>
                    <img id="productPhoto" src="{{ asset('storage/' . $photos[0]->product_image_path) }}" class="img-fluid rounded"
                        alt="Product Photo" />
                </div>
            </div>
        </div>
    </div>
