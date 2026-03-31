@extends('layouts.backend.layouts')

@section('page-content')
    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid add-form-list">
                <div class="row align-items-center mb-3">
                    <div class="col-lg-12">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                            <div>
                                <h4 class="mb-0">Stock Transfer Store to Store</h4>
                            </div>
                            <div>
                                <a href="{{ route('stock-transfer.list') }}" class="btn btn-secondary">Back</a>
                            </div>
                        </div>
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
                                         @if($shift_id != "")
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Day</label>
                                            <input type="date"
                                                value="{{ \Carbon\Carbon::parse($shift->start_time)->format('Y-m-d') }}"
                                                class="form-control" disabled>
                                            @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            <input type="hidden" value="{{$shift_id}}" name="shift_id">
                                            <input type="hidden" value="{{$shift->start_time}}" name="date">
                                        </div>
                                    </div>
                                    <div class="col-md-6"></div>
                                    @endif
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
                                                            $oldSub = \App\Models\SubCategory::find(
                                                                old('subcategory_id'),
                                                            );
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
                                                                <input type="number"
                                                                    name="items[{{ $index }}][quantity]"
                                                                    class="form-control @error('items.' . $index . '.quantity') is-invalid @enderror"
                                                                    min="1"
                                                                    value="{{ old('items.' . $index . '.quantity') }}">
                                                            </td>
                                                            <td>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-danger remove-item">
                                                                    Remove
                                                                </button>

                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr class="item-row product_items">
                                                        <td class="sr-no">1</td>
                                                        <td>
                                                            <select name="items[0][product_id]"
                                                                class="form-control product-select">
                                                                <option value="">Select Product</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <div class="availability-container small text-muted"></div>
                                                        </td>
                                                        <td>
                                                            <input type="number" name="items[0][quantity]"
                                                                class="form-control" min="1">
                                                        </td>

                                                        <td>
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger remove-item">
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
                                            <button type="submit" id="submitBtn" class="btn btn-primary">Submit
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
        </div>
    </div>

    <script>
        $(document).ready(function() {
            updateAddButton();
            $('#category_id').on('change', function() {

                var categoryId = $(this).val();
                if (categoryId) {
                    $.ajax({
                        url: "{{ url('/products/subcategory') }}/" + categoryId,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            $('#sub_category_ids').empty();
                            $('#sub_category_ids').append(
                                '<option value="" disabled selected>Select Sub Category</option>'
                            );
                            $.each(data, function(key, value) {
                                $("#fate").text(value.name);
                                $('#sub_category_ids').append('<option value="' + value
                                    .id + '">' + value.name + '</option>');
                            });
                        },
                        error: function() {
                            alert('Failed to fetch subcategories. Please try again.');
                        }
                    });
                } else {
                    $('#sub_category_ids').empty();
                    $('#sub_category_ids').append(
                        '<option value="" disabled selected>Select Sub Category</option>');
                }
            });

            // When subcategory is selected, update the product dropdown for the last added row
            $('#sub_category_ids').on('change', function() {
                const subCategoryId = $(this).val();

                // Only update the product dropdown in the last added product row
                const lastProductRow = $('#product-items .item-row:last');
                lastProductRow.find('.product-select').empty().append(
                    '<option value="">Select Product</option>');

                // Populate the product dropdown for the selected subcategory
                if (subCategoryId) {
                    $.ajax({
                        url: "{{ url('/products/get-products') }}/" + subCategoryId,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            data.forEach(function(product) {
                                lastProductRow.find('.product-select').append(
                                    '<option value="' + product.id + '">' + product
                                    .name + '</option>');
                            });
                        },
                        error: function() {
                            alert('Failed to fetch products. Please try again.');
                        }
                    });
                }
            });

        });

        function updateSrNo() {
            $('#productBody tr').each(function(index) {
                $(this).find('.sr-no').text(index + 1);
            });
        }

        let itemIndex = {{ old('items') ? count(old('items')) : 1 }};

        // Prevent double submission and validate form
        document.getElementById('transferForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');

            // Clear previous error states
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            // Validate source and destination stores
            const fromStore = $('#from_store_id').val();
            const toStore = $('#to_store_id').val();
            let hasError = false;

            if (!fromStore) {
                $('#from_store_id').addClass('is-invalid');
                $('#from_store_id').after('<div class="invalid-feedback">Please select the source store.</div>');
                hasError = true;
            }

            if (!toStore) {
                $('#to_store_id').addClass('is-invalid');
                $('#to_store_id').after('<div class="invalid-feedback">Please select the destination store.</div>');
                hasError = true;
            }

            if (fromStore && toStore && fromStore === toStore) {
                $('#to_store_id').addClass('is-invalid');
                $('#to_store_id').after(
                    '<div class="invalid-feedback">Source and destination stores must be different.</div>');
                hasError = true;
            }

            // Validate products
            $('.product-select').each(function(index) {
                const productId = $(this).val();
                const quantityInput = $(this).closest('.row').find('input[type="number"]');
                const quantity = quantityInput.val();

                if (!productId) {
                    $(this).addClass('is-invalid');
                    $(this).after('<div class="invalid-feedback">Please select a product.</div>');
                    hasError = true;
                }

                if (!quantity || quantity < 1) {
                    quantityInput.addClass('is-invalid');
                    quantityInput.after(
                        '<div class="invalid-feedback">Please enter a valid quantity.</div>');
                    hasError = true;
                }
            });

            if (hasError) {
                e.preventDefault();
                return false;
            }

            if (submitBtn.disabled) {
                e.preventDefault();
                return false;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Processing...';
            return true;
        });

        $(document).on('click', '#add-item', function() {

            const template = `
                <tr class="item-row product_items">
                    <td class="sr-no"></td>

                    <td>
                        <select name="items[${itemIndex}][product_id]" 
                            class="form-control product-select">
                            <option value="">Select Product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </td>
  <td>
                        <div class="availability-container small text-muted"></div>
                    </td>
                    <td>
                        <input type="number" 
                            name="items[${itemIndex}][quantity]" 
                            class="form-control"
                            min="1">
                    </td>

                  

                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-item">
                            Remove
                        </button>
                    </td>
                </tr>
                `;

            $('#productBody').append(template);

            itemIndex++;

            updateSrNo();
            updateAddButton();
            updateTotalQuantity();
        });

        // Remove item handler
        $(document).on('click', '.remove-item', function() {
            if ($('#productBody tr').length > 1) {
                $(this).closest('tr').remove();
                updateSrNo();
                updateTotalQuantity();
                updateAddButton();
            }
        });

        // Product selection handler
        $(document).on('change', '.product-select', function() {
            const productId = $(this).val();
            const from_store_id = $("#from_store_id").val();
            const to_store_id = $("#to_store_id").val();
            const container = $(this).closest('.item-row').find('.availability-container');
            const currentSelect = $(this);

            // Check if this product is already selected in another row
            if (productId) {
                const isDuplicate = $('.product-select').not(this).toArray().some(select => select.value ===
                    productId);
                if (isDuplicate) {
                    alert("This product is already selected. Please choose a different product.");
                    currentSelect.val('');
                    container.empty();
                    return false;
                }
            }

            if (!from_store_id) {
                alert("Please select the source store first.");
                currentSelect.val('');
                return false;
            }

            if (!to_store_id) {
                alert("Please select the destination store first.");
                currentSelect.val('');
                return false;
            }

            if (productId) {
                $.ajax({
                    url: "{{ url('/products/get-availability-branch') }}/" + productId +
                        "?from=" + encodeURIComponent(from_store_id) +
                        "&to=" + encodeURIComponent(to_store_id),
                    type: "GET",
                    dataType: "json",
                    success: function(data) {

                        if (data.from_count <= 0) {
                            alert("Insufficient stock in the source store for this product.");

                            currentSelect.val('');
                            container.empty();
                            return false;
                        }
                        let html = `
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Source Store Stock:</strong> ${data.from_count}
                                </div>
                                <div class="col-md-6">
                                    <strong>Destination Store Stock:</strong> ${data.to_count}
                                </div>
                            </div>`;
                        container.html(html);
                    },
                    error: function() {
                        container.html(
                            '<div class="text-danger">Failed to load stock information. Please try again.</div>'
                        );
                    }
                });
            } else {
                container.empty();
            }
        });

        // Trigger change event for pre-selected products
        $(document).ready(function() {
            updateTotalQuantity();

            $('.product-select').each(function() {
                if ($(this).val()) {
                    $(this).trigger('change');
                }
            });
        });

        function updateTotalQuantity() {
            let total = 0;
            $('input[name^="items"][name$="[quantity]"]').each(function() {
                const val = parseInt($(this).val());
                if (!isNaN(val)) {
                    total += val;
                }
            });
            $('#total-quantity').text(total);
        }

        function updateAddButton() {

            // remove ALL existing add buttons
            $('#productBody #add-item').remove();

            // add button only in last row
            $('#productBody tr:last td:last').prepend(
                '<button type="button" id="add-item" class="btn btn-secondary btn-sm pull-right ml-1">+ Add Product</button>'
            );
        }
        // Trigger total update on quantity input change
        $(document).on('input', 'input[name^="items"][name$="[quantity]"]', updateTotalQuantity);

        // Also update total when new row is added
        $('#add-item').on('click', function() {
            setTimeout(updateTotalQuantity, 100); // small delay to allow DOM insert
        });

        // When row is removed
        $(document).on('click', '.remove-item', function() {
            setTimeout(updateTotalQuantity, 100);
        });

        $('#from_store_id').on('change', function() {

            let fromStore = $(this).val();

            // Reset To Store dropdown
            $('#to_store_id option').prop('disabled', false);

            if (fromStore) {
                // Disable same store in To Store
                $('#to_store_id option[value="' + fromStore + '"]').prop('disabled', true);

                // If already selected, clear it
                if ($('#to_store_id').val() == fromStore) {
                    $('#to_store_id').val('');
                }
            }

        });
    </script>
@endsection
