@extends('layouts.backend.layouts')

@section('page-content')

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid add-form-list">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0">Edit Stock Transfer</h4>
                    </div>
                    <div>
                        <a href="{{ route('stock-transfer.list') }}" class="btn btn-secondary">Back</a>
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
                        <form id="transferForm" action="{{ route('stock-transfer.update', $transfer->id) }}" method="POST">
                            @csrf

                            <div class="row">

                                <!-- From Store -->
                                <div class="col-md-6">
                                    <label>From Store *</label>
                                    <select name="from_store_id" id="from_store_id" class="form-control">
                                        <option value="">Select Store</option>
                                        @foreach ($stores as $store)
                                            <option value="{{ $store->id }}"
                                                {{ $transfer->from_branch_id == $store->id ? 'selected' : '' }}>
                                                {{ $store->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- To Store -->
                                <div class="col-md-6">
                                    <label>To Store *</label>
                                    <select name="to_store_id" id="to_store_id" class="form-control">
                                        <option value="">Select Store</option>
                                        @foreach ($stores as $store)
                                            <option value="{{ $store->id }}"
                                                {{ $transfer->to_branch_id == $store->id ? 'selected' : '' }}>
                                                {{ $store->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Category -->
                                <div class="col-md-6 mt-2">
                                    <label>Category</label>
                                    <select name="category_id" id="category_id" class="form-control">
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $cate)
                                            <option value="{{ $cate->id }}"
                                                {{ $transfer->category_id == $cate->id ? 'selected' : '' }}>
                                                {{ $cate->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Subcategory -->
                                <div class="col-md-6 mt-2">
                                    <label>Sub Category</label>
                                    <select id="sub_category_ids" name="subcategory_id" class="form-control">
                                        <option value="">Select Sub Category</option>

                                        @php
                                            $subs = \App\Models\SubCategory::where(
                                                'category_id',
                                                $transfer->category_id,
                                            )->get();
                                        @endphp

                                        @foreach ($subs as $sub)
                                            <option value="{{ $sub->id }}"
                                                {{ $transfer->subcategory_id == $sub->id ? 'selected' : '' }}>
                                                {{ $sub->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>

                            <!-- PRODUCTS -->
                            <div id="product-items" class="mt-3">
                                <h5>Products</h5>

                                <!-- HEADER -->
                                <div class="row fw-bold mb-2">
                                    <div class="col-md-6">Product</div>
                                    <div class="col-md-4 text-center">Quantity</div>
                                    <div class="col-md-2 text-center">Action</div>
                                </div>

                                @foreach ($items as $index => $item)
                                    <div class="item-row mb-3">
                                        <div class="row">

                                            <div class="col-md-6">
                                                <select name="items[{{ $index }}][product_id]"
                                                    class="form-control product-select">
                                                    <option value="">Select Product</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->id }}"
                                                            {{ $item->product_id == $product->id ? 'selected' : '' }}>
                                                            {{ $product->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <input type="number" name="items[{{ $index }}][quantity]"
                                                    class="form-control text-center" value="{{ $item->quantity }}"
                                                    min="1">
                                            </div>

                                            <div class="col-md-2 text-center">
                                                <button type="button" class="btn btn-danger remove-item">
                                                    Remove
                                                </button>
                                            </div>

                                        </div>

                                        <div class="availability-container mt-2 small text-muted"></div>
                                    </div>
                                @endforeach

                                <!-- ✅ TOTAL ROW -->
                                <div class="row mt-2">
                                    <div class="col-md-6"></div>
                                    <div class="col-md-4 text-center">
                                        <strong>Total: <span id="total-quantity">0</span></strong>
                                    </div>
                                    <div class="col-md-2"></div>
                                </div>

                            </div>

                            <!-- TOTAL -->
                            <div class="text-end mb-3">
                                <h5>Total Quantity: <span id="total-quantity">0</span></h5>
                            </div>

                            {{-- <div class="d-flex justify-content-end mb-3">
                                <button type="button" id="add-item" class="btn btn-secondary">
                                    + Add Product
                                </button>
                            </div> --}}

                            <div>
                                <button type="submit" id="submitBtn" class="btn btn-primary">
                                    Update Transfer
                                </button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- JS -->
    <script>
        let itemIndex = {{ count($items) }};

        // ADD ITEM
        $('#add-item').click(function() {

            let html = `
    <div class="item-row mb-3">
        <div class="row">

            <div class="col-md-6">
                <select name="items[${itemIndex}][product_id]" class="form-control product-select">
                    <option value="">Select Product</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <input type="number" name="items[${itemIndex}][quantity]"
                    class="form-control" min="1">
            </div>

            <div class="col-md-2">
                <button type="button" class="btn btn-danger remove-item">Remove</button>
            </div>

        </div>

        <div class="availability-container mt-2 small text-muted"></div>
    </div>`;

            $('#product-items').append(html);
            itemIndex++;
        });

        // REMOVE
        $(document).on('click', '.remove-item', function() {
            if ($('.item-row').length > 1) {
                $(this).closest('.item-row').remove();
                updateTotalQuantity();
            }
        });

        // TOTAL
        function updateTotalQuantity() {
            let total = 0;
            $('input[name^="items"]').each(function() {
                total += parseInt($(this).val()) || 0;
            });
            $('#total-quantity').text(total);
        }

        $(document).on('input', 'input[name^="items"]', updateTotalQuantity);

        // STOCK CHECK
        $(document).on('change', '.product-select', function() {

            let productId = $(this).val();
            let from = $('#from_store_id').val();
            let to = $('#to_store_id').val();
            let container = $(this).closest('.item-row').find('.availability-container');

            if (!productId || !from || !to) return;

            $.get(`/products/get-availability-branch/${productId}?from=${from}&to=${to}`, function(data) {

                if (data.from_count <= 0) {
                    alert('No stock available');
                    return;
                }

                container.html(`
            From: ${data.from_count} <br>
            To: ${data.to_count}
        `);
            });

        });

        // INIT
        $(document).ready(function() {

            updateTotalQuantity();

            $('.product-select').each(function() {
                if ($(this).val()) {
                    $(this).trigger('change');
                }
            });

        });
    </script>

@endsection
