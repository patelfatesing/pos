<div class="card">

    <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
        <div>
            <h4 class="mb-0">Stock Transfer Store to Store</h4>
        </div>

    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="card">

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}

                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('error') }}

                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>

                        </div>
                    @endif

                    <form id="transferForm" action="{{ route('stock-transfer.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            @if ($shift_id != '')
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Day</label>
                                        <input type="date"
                                            value="{{ \Carbon\Carbon::parse($shift->start_time)->format('Y-m-d') }}"
                                            class="form-control" disabled>
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        <input type="hidden" value="{{ $shift_id }}" name="shift_id">
                                        <input type="hidden" value="{{ $shift->start_time }}" name="date">
                                    </div>
                                </div>
                                <div class="col-md-6"></div>
                            @endif
                            <input type="hidden" name="type" value="{{ request('type') }}">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>From Store *</label>
                                    <select name="from_store_id" id="from_store_id"
                                        class="form-control @error('from_store_id') is-invalid @enderror">
                                        <option value="">Select Store</option>
                                        @foreach ($stores as $store)
                                            <option value="{{ $store->id }}"
                                                {{ old('from_store_id') == $store->id ? 'selected' : '' }}>
                                                {{ $store->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('from_store_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>To Store *</label>
                                    <select name="to_store_id" id="to_store_id"
                                        class="form-control @error('to_store_id') is-invalid @enderror">
                                        <option value="">Select Store</option>
                                        @foreach ($stores as $store)
                                            <option value="{{ $store->id }}"
                                                {{ old('to_store_id') == $store->id ? 'selected' : '' }}>
                                                {{ $store->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('to_store_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Category</label>
                                    <select name="category_id" id="category_id"
                                        class="form-control @error('category_id') is-invalid @enderror">
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $cate)
                                            <option value="{{ $cate->id }}"
                                                {{ old('category_id') == $cate->id ? 'selected' : '' }}>
                                                {{ $cate->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sub Category *</label>
                                    <select id="sub_category_ids" name="subcategory_id" class="form-control"
                                        data-style="py-0">
                                        <option value="" selected>Select Sub Category</option>
                                        @if (old('subcategory_id'))
                                            @php
                                                $oldSub = \App\Models\SubCategory::find(old('subcategory_id'));
                                            @endphp

                                            @if ($oldSub)
                                                <option value="{{ $oldSub->id }}" selected>
                                                    {{ $oldSub->name }}
                                                </option>
                                            @endif
                                        @endif
                                    </select>
                                    @error('subcategory_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table table-bordered" id="product-items">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">Sr No</th>
                                        <th width="40%">Product</th>
                                        <th width="25%">Stock Info</th>
                                        <th width="10%">Quantity</th>
                                        <th width="20%">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="productBody">

                                    @if (old('items'))
                                        @foreach (old('items') as $index => $item)
                                            <tr class="item-row product_items">
                                                <td class="sr-no">{{ $index + 1 }}</td>

                                                <td>
                                                    <select name="items[{{ $index }}][product_id]"
                                                        class="form-control product-select @error('items.' . $index . '.product_id') is-invalid @enderror">
                                                        <option value="">Select Product</option>
                                                        @foreach ($products as $product)
                                                            <option value="{{ $product->id }}"
                                                                {{ old('items.' . $index . '.product_id') == $product->id ? 'selected' : '' }}>
                                                                {{ $product->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <div class="availability-container small text-muted"></div>
                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $index }}][quantity]"
                                                        class="form-control @error('items.' . $index . '.quantity') is-invalid @enderror"
                                                        min="1"
                                                        value="{{ old('items.' . $index . '.quantity') }}">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger remove-item">
                                                        Remove
                                                    </button>

                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="item-row product_items">
                                            <td class="sr-no">1</td>
                                            <td>
                                                <select name="items[0][product_id]" class="form-control product-select">
                                                    <option value="">Select Product</option>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="availability-container small text-muted"></div>
                                            </td>
                                            <td>
                                                <input type="number" name="items[0][quantity]" class="form-control"
                                                    min="1">
                                            </td>

                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger remove-item">
                                                    Remove
                                                </button>
                                            </td>
                                        </tr>
                                    @endif

                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="3" style="text-align: right !important;">Total
                                            Quantity</th>
                                        <th style="text-align: center !important;">
                                            <span id="total-quantity">0</span>
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" id="submitBtn" class="btn btn-success">Submit
                                    Transfer</button>
                                <button type="reset" class="btn btn-danger">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>


<script>
    // ✅ GLOBAL SAFE VARIABLE
    window.itemIndex = window.itemIndex || {{ old('items') ? count(old('items')) : 1 }};

    // ✅ INITIAL LOAD (NO document.ready)
    setTimeout(function() {
        updateAddButton();
        updateTotalQuantity();

        $('.product-select').each(function() {
            if ($(this).val()) {
                $(this).trigger('change');
            }
        });
    }, 100);


    // ✅ CATEGORY → SUBCATEGORY
    $(document).on('change', '#category_id', function() {

        var categoryId = $(this).val();

        if (categoryId) {
            $.get("{{ url('/products/subcategory') }}/" + categoryId, function(data) {

                $('#sub_category_ids').empty()
                    .append('<option value="">Select Sub Category</option>');

                data.forEach(function(item) {
                    $('#sub_category_ids').append(
                        `<option value="${item.id}">${item.name}</option>`
                    );
                });

            });
        }
    });


    // ✅ SUBCATEGORY → PRODUCT (LAST ROW ONLY)
    $(document).on('change', '#sub_category_ids', function() {

        const subId = $(this).val();
        const lastRow = $('#productBody tr:last');

        let dropdown = lastRow.find('.product-select');

        dropdown.html('<option value="">Select Product</option>');

        if (subId) {
            $.get("{{ url('/products/get-products') }}/" + subId, function(data) {

                data.forEach(function(p) {
                    dropdown.append(`<option value="${p.id}">${p.name}</option>`);
                });

            });
        }
    });


    // ✅ ADD PRODUCT
    $(document).on('click', '#add-item', function() {

        let html = `
        <tr class="item-row">
            <td class="sr-no"></td>

            <td>
                <select name="items[${window.itemIndex}][product_id]"
                    class="form-control product-select">
                    <option value="">Select Product</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </td>

            <td><div class="availability-container small text-muted"></div></td>

            <td>
                <input type="number"
                    name="items[${window.itemIndex}][quantity]"
                    class="form-control"
                    min="1">
            </td>

            <td>
                <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
            </td>
        </tr>`;

        $('#productBody').append(html);

        window.itemIndex++;

        updateSrNo();
        updateAddButton();
        updateTotalQuantity();
    });


    // ✅ REMOVE PRODUCT
    $(document).on('click', '.remove-item', function() {

        if ($('#productBody tr').length > 1) {
            $(this).closest('tr').remove();

            updateSrNo();
            updateAddButton();
            updateTotalQuantity();
        }
    });


    // ✅ PRODUCT STOCK CHECK
    $(document).on('change', '.product-select', function() {

        let productId = $(this).val();
        let from = $('#from_store_id').val();
        let to = $('#to_store_id').val();
        let container = $(this).closest('tr').find('.availability-container');

        if (!productId || !from || !to) return;

        // duplicate check
        let duplicate = $('.product-select').not(this).toArray()
            .some(el => el.value === productId);

        if (duplicate) {
            alert("Product already selected");
            $(this).val('');
            return;
        }

        $.get(`/products/get-availability-branch/${productId}?from=${from}&to=${to}`, function(data) {

            if (data.from_count <= 0) {
                alert("No stock available");
                container.html('');
                return;
            }

            container.html(`
                <div>From: ${data.from_count}</div>
                <div>To: ${data.to_count}</div>
            `);
        });
    });


    // ✅ TOTAL CALCULATION
    function updateTotalQuantity() {
        let total = 0;

        $('input[name$="[quantity]"]').each(function() {
            total += parseInt($(this).val()) || 0;
        });

        $('#total-quantity').text(total);
    }

    $(document).on('input', 'input[name$="[quantity]"]', updateTotalQuantity);


    // ✅ SR NO
    function updateSrNo() {
        $('#productBody tr').each(function(i) {
            $(this).find('.sr-no').text(i + 1);
        });
    }


    // ✅ ADD BUTTON ONLY LAST ROW
    function updateAddButton() {

        $('#productBody #add-item').remove();

        $('#productBody tr:last td:last').prepend(
            '<button type="button" id="add-item" class="btn btn-secondary btn-sm mr-1">+ Add</button>'
        );
    }


    // ✅ STORE VALIDATION (FROM != TO)
    $(document).on('change', '#from_store_id', function() {

        let from = $(this).val();

        $('#to_store_id option').prop('disabled', false);

        if (from) {
            $('#to_store_id option[value="' + from + '"]').prop('disabled', true);

            if ($('#to_store_id').val() == from) {
                $('#to_store_id').val('');
            }
        }
    });


          
</script>
