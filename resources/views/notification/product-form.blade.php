
        <form id="priceUpdateForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">Product Price Change</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <input type="hidden" name="product_id" id="product_id" value="">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Old Price </label>
                            <input type="text" name="old_price" class="form-control" id="old_price">
                            <span class="text-danger" id="old_price_error"></span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>New Price</label>
                            <input type="text" name="new_price" class="form-control" id="new_price">
                            <span class="text-danger" id="new_price_error"></span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Price Apply Date</label>
                            <input type="date" name="changed_at" min=""
                                class="form-control" id="changed_at">
                            <span class="text-danger" id="changed_at_error"></span>
                        </div>
                    </div>
                </div>

                <span class="mt-2 badge badge-pill border border-secondary text-secondary">
                    {{ __('messages.change_date_msg') }}
                </span>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </form>
   