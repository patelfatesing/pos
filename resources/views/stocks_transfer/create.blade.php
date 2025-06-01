@extends('layouts.backend.layouts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@section('page-content')
    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid add-form-list">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Stock Transfer Store to Store</h4>
                                </div>
                                <div>
                                    <a href="{{ route('stock-transfer.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>

                            <div class="card-body">
                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show">
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show">
                                        {{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert alert-danger alert-dismissible fade show">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif

                                <form id="transferForm" action="{{ route('stock-transfer.store') }}" method="POST">
                                    @csrf
                                    <div class="row">
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
                                    </div>

                                    <div id="product-items">
                                        <h5>Products</h5>
                                        @if (old('items'))
                                            @foreach (old('items') as $index => $item)
                                                <div class="item-row product_items mb-3">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
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
                                                                @error('items.' . $index . '.product_id')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <input type="number"
                                                                    name="items[{{ $index }}][quantity]"
                                                                    class="form-control @error('items.' . $index . '.quantity') is-invalid @enderror"
                                                                    placeholder="Quantity" min="1"
                                                                    value="{{ old('items.' . $index . '.quantity') }}">
                                                                @error('items.' . $index . '.quantity')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <button type="button"
                                                                class="btn btn-danger remove-item">Remove</button>
                                                        </div>
                                                    </div>
                                                    <div class="availability-container mt-2 small text-muted"></div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="item-row product_items mb-3">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <select name="items[0][product_id]"
                                                                class="form-control product-select @error('items.0.product_id') is-invalid @enderror">
                                                                <option value="">Select Product</option>
                                                                @foreach ($products as $product)
                                                                    <option value="{{ $product->id }}">
                                                                        {{ $product->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('items.0.product_id')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <input type="number" name="items[0][quantity]"
                                                                class="form-control @error('items.0.quantity') is-invalid @enderror"
                                                                placeholder="Quantity" min="1">
                                                            @error('items.0.quantity')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button"
                                                            class="btn btn-danger remove-item">Remove</button>
                                                    </div>
                                                </div>
                                                <div class="availability-container mt-2 small text-muted"></div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-12 text-end">
                                            <h5>Total Quantity: <span id="total-quantity">0</span></h5>
                                        </div>
                                    </div>
                                    <button type="button" id="add-item" class="btn btn-secondary mb-3">+ Add
                                        Product</button>

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

        document.getElementById('add-item').addEventListener('click', function() {
            const template = `
                <div class="item-row product_items mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <select name="items[${itemIndex}][product_id]" class="form-control product-select">
                                    <option value="">Select Product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <input type="number" name="items[${itemIndex}][quantity]" class="form-control" placeholder="Quantity" min="1">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger remove-item">Remove</button>
                        </div>
                    </div>
                    <div class="availability-container mt-2 small text-muted"></div>
                </div>
            `;

            document.getElementById('product-items').insertAdjacentHTML('beforeend', template);
            itemIndex++;
        });

        // Remove item handler
        $(document).on('click', '.remove-item', function() {
            if (document.querySelectorAll('.item-row').length > 1) {
                $(this).closest('.item-row').remove();
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
    </script>
@endsection
