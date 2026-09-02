@extends('layouts.backend.layouts')

@section('page-content')

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid add-form-list">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0">Edit Stock Transfer - {{ $transfer->transfer_number }}</h4>
                    </div>
                    <div>
                        <button onclick="window.history.back()" class="btn btn-secondary">
                            Back
                        </button>
                    </div>
                </div>


                <div class="card">
                    <div class="card-body">

                        <!-- Alerts -->
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- FORM -->
                        <form id="transferForm" action="{{ route('stock-transfer.update-form', $transfer->id) }}" method="POST">
                            @csrf
                            <div class="row">
                                <input type="hidden" name="type" value="{{ request('type') }}">
                                <input type="hidden" name="shift_id" value="{{ $transfer->shift_id }}">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>From Store *</label>
                                        <select name="from_store_id" id="from_store_id" class="form-control @error('from_store_id') is-invalid @enderror">
                                            <option value="">Select Store</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}" {{ $transfer->from_branch_id == $store->id ? 'selected' : '' }}>
                                                    {{ $store->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>To Store *</label>
                                        <select name="to_store_id" id="to_store_id" class="form-control @error('to_store_id') is-invalid @enderror">
                                            <option value="">Select Store</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}" {{ $transfer->to_branch_id == $store->id ? 'selected' : '' }}>
                                                    {{ $store->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Category *</label>
                                        @php
                                            $subCategories = \App\Models\SubCategory::all();
                                        @endphp
                                        <select id="sub_category_ids" name="subcategory_id" class="form-control" data-style="py-0">
                                            <option value="">Select Category</option>
                                            @foreach ($subCategories as $cate)
                                                <option value="{{ $cate->id }}"
                                                    {{ (isset($transfer) && $transfer->subcategory_id == $cate->id) ? 'selected' : '' }}>
                                                    {{ $cate->name }}
                                                </option>
                                            @endforeach
                                        </select>
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
                                        @foreach ($items as $index => $item)
                                            <tr class="item-row product_items">
                                                <td class="sr-no">{{ $index + 1 }}</td>
                                                <td>
                                                    <select name="items[{{ $index }}][product_id]" class="form-control product-select">
                                                        <option value="">Select Product</option>
                                                        @foreach ($products as $product)
                                                            <option value="{{ $product->id }}" {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                                {{ $product->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <div class="availability-container small text-muted"></div>
                                                </td>
                                                <td>
                                                    <input type="number" name="items[{{ $index }}][quantity]" class="form-control" value="{{ $item->quantity }}" min="1">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger remove-item">Remove</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="3" style="text-align: right !important;">Total Quantity</th>
                                            <th style="text-align: center !important;">
                                                <span id="total-quantity">0</span>
                                            </th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" id="submitBtn" class="btn btn-success">Update Transfer</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .select2-container { width: 100% !important; }
        .select2-container .select2-selection--single { height: 38px !important; border: 1px solid #ced4da; border-radius: 4px; }
        .select2-container .select2-selection--single .select2-selection__rendered { line-height: 36px !important; padding-left: 12px; }
        .select2-container .select2-selection--single .select2-selection__arrow { height: 36px !important; }
    </style>

    <script>
        function initProductSelect2(context) {
            context.find('.product-select').each(function() {
                const $sel = $(this);
                if ($sel.hasClass('select2-hidden-accessible')) return;
                $sel.select2({ placeholder: 'Select Product', allowClear: true, width: '100%' });
            });
        }

        function updateSrNo() {
            $('#productBody tr').each(function(index) {
                $(this).find('.sr-no').text(index + 1);
            });
        }

        function updateTotalQuantity() {
            let total = 0;
            $('input[name^="items"][name$="[quantity]"]').each(function() {
                total += parseInt($(this).val()) || 0;
            });
            $('#total-quantity').text(total);
        }

        function updateAddButton() {
            $('#productBody #add-item').remove();
            $('#productBody tr:last td:last').prepend('<button type="button" id="add-item" class="btn btn-secondary btn-sm pull-right ml-1">+ Add Product</button>');
        }

        $(document).on('click', '.remove-item', function() {
            if ($('#productBody tr').length > 1) {
                $(this).closest('tr').remove();
                updateSrNo();
                updateTotalQuantity();
                updateAddButton();
            }
        });

        $(document).on('input', 'input[name^="items"][name$="[quantity]"]', updateTotalQuantity);

        $(document).ready(function() {
            initProductSelect2($('#productBody'));
            updateTotalQuantity();
            updateAddButton();

            // Load stock info for existing products on page load (Batch attempt)
            setTimeout(function() {
                let productChecks = [];
                const from = $('#from_store_id').val();
                const to = $('#to_store_id').val();

                if (from && to) {
                    $('.product-select').each(function() {
                        let productId = $(this).val();
                        if (productId) {
                            let container = $(this).closest('.item-row').find('.availability-container');
                            productChecks.push({
                                productId: productId,
                                container: container
                            });
                        }
                    });

                    // Execute checks
                    productChecks.forEach(check => {
                        $.get(`/products/get-availability-branch/${check.productId}?from=${from}&to=${to}`, function(data) {
                            if (data.from_count > 0) {
                                check.container.html(`
                                    <div class="row">
                                        <div class="col-md-6"><strong>Source Stock:</strong> ${data.from_count}</div>
                                        <div class="col-md-6"><strong>Destination Stock:</strong> ${data.to_count}</div>
                                    </div>`);
                            }
                        });
                    });
                }
            }, 500);

            // Category change handler to update product dropdown
            $('#sub_category_ids').on('change', function() {
                const subCategoryId = $(this).val();
                
                // Only update the product dropdown in the last added product row
                const lastProductRow = $('#productBody tr:last');
                const $lastSelect = lastProductRow.find('.product-select');

                $lastSelect.empty().append('<option value="">Select Product</option>');
                $lastSelect.trigger('change.select2');

                if (subCategoryId) {
                    $.ajax({
                        url: "{{ url('/products/get-products') }}/" + subCategoryId,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            data.forEach(function(product) {
                                $lastSelect.append(
                                    '<option value="' + product.id + '">' + product.name + '</option>');
                            });

                            $lastSelect.trigger('change.select2');
                            $lastSelect.trigger('change');
                        },
                        error: function() {
                            alert('Failed to fetch products. Please try again.');
                        }
                    });
                }
            });

            // Handle add product
            $(document).on('click', '#add-item', function() {
                let index = $('#productBody tr').length;
                let subCategoryId = $('#sub_category_ids').val();
                let template = `
                    <tr class="item-row product_items">
                        <td class="sr-no">${index + 1}</td>
                        <td>
                            <select name="items[${index}][product_id]" class="form-control product-select">
                                <option value="">Select Product</option>
                            </select>
                        </td>
                        <td><div class="availability-container small text-muted"></div></td>
                        <td><input type="number" name="items[${index}][quantity]" class="form-control" min="1"></td>
                        <td><button type="button" class="btn btn-sm btn-danger remove-item">Remove</button></td>
                    </tr>`;
                
                let newRow = $(template);
                $('#productBody').append(newRow);
                initProductSelect2(newRow);
                updateAddButton();

                if (subCategoryId) {
                    $.get("{{ url('/products/get-products') }}/" + subCategoryId, function(data) {
                        let select = newRow.find('.product-select');
                        data.forEach(p => select.append(`<option value="${p.id}">${p.name}</option>`));
                        select.trigger('change.select2');
                    });
                }
            });

            // Stock check
            $(document).on('select2:select', '.product-select', function(e) {
                const productId = e.params?.data?.id || $(this).val();
                const from = $('#from_store_id').val();
                const to = $('#to_store_id').val();
                const container = $(this).closest('.item-row').find('.availability-container');
                
                if (!productId || !from || !to) return;

                $.get(`/products/get-availability-branch/${productId}?from=${from}&to=${to}`, function(data) {
                    if (data.from_count <= 0) {
                        alert('No stock available');
                        return;
                    }
                    container.html(`
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Source Store Stock:</strong> ${data.from_count}
                            </div>
                            <div class="col-md-6">
                                <strong>Destination Store Stock:</strong> ${data.to_count}
                            </div>
                        </div>`);
                });

            });
        });
    </script>

@endsection
