@extends('layouts.backend.layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        input[type=number] {
            width: 90px !important;
        }

        .price-stack {
            display: flex;
            flex-direction: column;
            align-items: start;
            line-height: 1.2;
        }

        .price-stack .discount {
            color: #d9534f;
            font-weight: bold;
        }

        .price-stack .mrp {
            color: #333;
            text-decoration: line-through;
            font-size: 90%;
        }

        .item-price,
        .item-total-input {
            width: 100px !important;
        }

        .credit-section {
            margin-top: 20px;
        }

        .total-summary h5 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        #add-product-btn {
            white-space: nowrap;
            height: 38px;
            display: inline-flex;
            align-items: center;
            position: relative;
            margin-left: -6px;
            z-index: 2;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        #new-product-qty {
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        #payment-fields {
            display: flex;
            gap: 10px;
        }

        #payment-fields .payment-input {
            flex: 1;
        }

        /* ✅ Select2 styling fix for #new-product-id */
        .select2-container {
            min-width: 220px; /* base width */
        }

        .select2-container .select2-selection--single {
            height: 38px !important;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
            padding-left: 12px;
            padding-right: 45px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Arrow: pointer-events none so it never blocks the clear (x) icon underneath */
        .select2-container .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 6px;
            pointer-events: none;
        }

        /* Clear (x) icon: given its own space, higher z-index, and real click target */
        .select2-container .select2-selection--single .select2-selection__clear {
            position: absolute;
            right: 28px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            cursor: pointer;
            pointer-events: auto;
        }

        .select2-dropdown {
            width: auto !important;
            min-width: 100%;
            max-width: 480px;
        }

        .select2-results__option {
            white-space: nowrap;
            padding-right: 20px;
        }

        .select2-search--dropdown .select2-search__field {
            padding: 6px 8px;
        }

        /* Qty shifted a bit right for breathing room from the dropdown */
        #new-product-qty-wrap {
            margin-left: 15px;
        }

        .order-details-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            overflow: hidden;
        }

        .order-details-header {
            background: #ff7e41;
            padding: 10px 14px;
            border-bottom: none;
        }

        .order-details-header h5 {
            color: #ffffff;
            margin: 0;
            font-weight: 600;
            font-size: 16px;
            letter-spacing: 0.5px;
        }

        .order-details-body {
            padding: 10px;
        }

        .order-detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2px 0;
            border-bottom: 1px solid #f1f3f5;
        }

        .order-detail-item:last-child {
            border-bottom: none;
        }

        .order-detail-item .label {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
            margin: 0;
        }

        .order-detail-item .value {
            color: #2d3748;
            font-size: 15px;
            font-weight: 600;
            margin: 0;
        }

        .order-detail-item .value.highlight {
            color: #4a6cf7;
        }

        .order-detail-item .value.danger {
            color: #dc3545;
        }

        .order-detail-item .value.success {
            color: #28a745;
        }

        .total-amount-box {
            background: #32BDEA;
            padding: 5px 7px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 3px;
        }

        .total-amount-box h6 {
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
            font-size: 15px;
            font-weight: 500;
        }

        .total-amount-box h3 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .payment-method-group {
            display: flex;
            flex-wrap: wrap;
        }

        .payment-method-group .form-check {
            margin: 0;
            padding-left: 17px;
        }

        .payment-method-group .form-check-input {
            margin-top: 2px;
        }

        .payment-method-group .form-check-label {
            font-size: 13px;
            font-weight: 500;
            color: #495057;
        }

        .credit-info {
            background: #f8f9fa;
            padding: 12px 15px;
            border-radius: 8px;
            margin: 10px 0;
        }

        .credit-info .d-flex {
            padding: 4px 0;
        }

        .payment-input-group {
            background: #f8f9fa;
            padding: 5px 10px;
            border-radius: 8px;
            margin-top: 6px;
        }

        .payment-input-group .form-label {
            font-size: 13px;
            font-weight: 500;
            color: #495057;
            margin-bottom: 4px;
        }

        .payment-input-group .form-control {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 14px;
        }

        .payment-input-group .form-control:focus {
            border-color: #4a6cf7;
            box-shadow: 0 0 0 0.2rem rgba(74, 108, 247, 0.1);
        }

        .section-divider {
            border-top: 2px dashed #dee2e6;
            margin: 8px 0;
        }

        @media (max-width: 768px) {
            .order-details-body {
                padding: 15px;
            }

            .payment-method-group {
                gap: 10px;
            }

            .total-amount-box {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }

            .total-amount-box h3 {
                margin-top: 8px;
            }
        }
    </style>

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between mb-3">
                    <div>
                        <h4 class="mb-0">Add Transaction - #{{ $branch_data->name }}</h4>
                    </div>
                    <button onclick="window.history.back()" class="btn btn-secondary">
                        Back
                    </button>
                </div>

                <form id="invoice-items-form" method="POST" action="{{ route('sales.invoice.insert-sale') }}">
                    @csrf
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex flex-nowrap align-items-center" style="gap: 12px; overflow-x: auto;">
                                <div id="product-select-wrap" style="flex: 0 1 auto; min-width: 0;">
                                    <select id="new-product-id" class="form-control">
                                        <option value="">Select Product</option>
                                        @foreach ($allProducts as $product)
                                            <option value="{{ $product->id }}" data-name="{{ $product->name }}"
                                                data-mrp="{{ $product->mrp }}" data-sell_price="{{ $product->sell_price }}"
                                                data-discount="{{ $product->discount_price }}"
                                                data-category="{{ $product->category->name }}"
                                                data-subcategory="{{ $product->subcategory->name }}">
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="hidden" name="branch_id" value="{{ $branch_data->id }}">
                                <input type="hidden" name="type" value="{{ $type }}">
                                <input type="hidden" name="shift_id" value="{{ $Shift_data->id }}">
                                <div class="d-flex align-items-center" style="gap: 0; flex: 0 0 auto;">
                                    <div id="new-product-qty-wrap" style="flex: 0 0 90px;">
                                        <input type="number" min="1" id="new-product-qty" class="form-control"
                                            placeholder="Qty">
                                    </div>
                                    <div style="flex: 0 0 auto;">
                                        <button type="button" class="btn btn-success" id="add-product-btn">
                                            Add Item
                                        </button>
                                    </div>
                                </div>
                                <div style="flex: 1 1 auto;"></div>
                                <div style="flex: 0 0 auto; min-width: 200px;">
                                    @if ($branch_data->id == 1)
                                        <select id="party-id" class="form-control" name="party_user_id">
                                            <option value="">Select Party Customer</option>
                                            @foreach ($partyUsers as $cust)
                                                <option value="{{ $cust->id }}"
                                                    {{ old('party_user_id') == $cust->id ? 'selected' : '' }}>
                                                    {{ $cust->first_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <select id="commission-id" class="form-control" name="commission_user_id">
                                            <option value="">Select Commission Customer</option>
                                            @foreach ($commissionUsers as $cust)
                                                <option value="{{ $cust->id }}"
                                                    {{ old('commission_user_id') == $cust->id ? 'selected' : '' }}>
                                                    {{ $cust->first_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body table-responsive">
                            <table class="table table-bordered" id="items-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="invoice-items-body"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Enhanced Order Details Section -->
                    <div class="row mb-3">
                        <div class="offset-lg-8 col-lg-4">
                            <div class="order-details-card">
                                <div class="order-details-header">
                                    <h5><i class="fas fa-shopping-cart"></i> Order Details</h5>
                                </div>
                                <div class="order-details-body">
                                    <input type="hidden" id="total_discount" name="total_discount" value="0">
                                    <input type="hidden" id="gr_total" name="sub_total" value="0">
                                    <input type="hidden" id="sub_total" name="total" value="0">
                                    <input type="hidden" id="left_credit_id" value="0">

                                    <!-- Sub Total -->
                                    <div class="order-detail-item">
                                        <span class="label">Sub Total</span>
                                        <span class="value highlight" id="total">₹0.00</span>
                                    </div>

                                    <!-- Discount Section -->
                                    <div class="order-detail-item">
                                        <span class="label">
                                            <span class="credit-section" style="display:none;">Party Deduction</span>
                                            <span class="commission-section" style="display:none;">Commission Deduction</span>
                                        </span>
                                        <span class="value danger" id="discount-total">₹0.00</span>
                                    </div>

                                    <!-- Credit Section -->
                                    <div class="credit-section" style="display:none;">
                                        <div class="section-divider"></div>
                                        <div class="credit-info">
                                            <div class="d-flex justify-content-between">
                                                <span class="label">Credit Limit</span>
                                                <span class="value" id="credit-limit">₹0.00</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="label">Left Limit</span>
                                                <span class="value success" id="left_credit">₹0.00</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-2">
                                                <span class="label">Credit Used</span>
                                                <div>
                                                    <input type="number" name="creditpay" id="creditpay-input"
                                                        min="0" step="0.1"
                                                        class="form-control d-inline-block"
                                                        style="width: 120px; display: inline;">
                                                    <small id="creditpay-error" class="text-danger d-block"
                                                        style="display:none;"></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Payment Method -->
                                    <div class="section-divider"></div>
                                    <div>
                                        <span class="label d-block" style="font-weight: 600; color: #495057;">Payment Method</span>
                                        <div class="payment-method-group">
                                            <div class="form-check">
                                                <input type="radio" id="cash-option" name="payment_method" value="cash"
                                                    checked>
                                                <label class="form-check-label" for="cash-option">Cash</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="upi-option" name="payment_method" value="online">
                                                <label class="form-check-label" for="upi-option">UPI</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="cash-upi-option" name="payment_method"
                                                    value="cashupi">
                                                <label class="form-check-label" for="cash-upi-option">Cash + UPI</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" id="credit-option" name="payment_method"
                                                    value="credit">
                                                <label class="form-check-label" for="credit-option">Credit</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cash and UPI Inputs -->
                                    <div id="payment-fields">
                                        <div id="cash-field" class="payment-input-group">
                                            <label class="form-label">Cash Amount</label>
                                            <input type="number" id="cash-amount" class="form-control" min="0"
                                                step="1" readonly name="cash_amount">
                                        </div>

                                        <div id="upi-field" class="payment-input-group" style="display: none;">
                                            <label class="form-label">UPI Amount</label>
                                            <input type="number" id="upi-amount" class="form-control" name="upi_amount"
                                                min="0" step="1" readonly>
                                        </div>
                                    </div>

                                    <!-- Total -->
                                    <div class="total-amount-box mt-3">
                                        <h6>Total</h6>
                                        <h3 id="grand-total">₹0.00</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3 total-summary mb-3">
                        <div>
                            <button type="submit" class="btn btn-success">Save Invoice Items</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        function submitForm() {
            document.getElementById('vendorForm').submit();
        }
        let oldItems = @json(old('items', []));

        $(document).ready(function() {

            // ✅ Resize helper for #new-product-id select2 box (accessible everywhere below)
            function resizeProductSelect() {
                const $sel = $('#new-product-id');
                const text = $sel.find('option:selected').text().trim() || $sel.data('placeholder') ||
                    'Select Product';

                let $ruler = $('#product-select-ruler');
                if ($ruler.length === 0) {
                    $ruler = $('<span id="product-select-ruler"></span>').css({
                        position: 'absolute',
                        visibility: 'hidden',
                        whiteSpace: 'nowrap',
                        fontSize: '14px'
                    }).appendTo('body');
                }

                $ruler.text(text);
                const textWidth = $ruler.width();

                const minWidth = 220;
                const maxWidth = 420;
                const newWidth = Math.min(maxWidth, Math.max(minWidth, textWidth + 70));

                $sel.next('.select2-container').css('width', newWidth + 'px')
                    .find('.select2-selection__rendered').attr('title', text);
            }

            // ✅ Select2 init for Product dropdown
            if ($('#new-product-id').length) {
                $('#new-product-id').select2({
                    placeholder: 'Select Product',
                    allowClear: true,
                    width: 'resolve',
                    dropdownAutoWidth: true,
                });

                resizeProductSelect();

                $('#new-product-id').on('select2:select select2:unselect select2:clear', function() {
                    resizeProductSelect();
                });
            }

            if (oldItems && Object.keys(oldItems).length > 0) {

                Object.keys(oldItems).forEach(function(index) {

                    let item = oldItems[index];

                    let productId = item.product_id;
                    let name = item.name;
                    let qty = parseInt(item.quantity) || 1;
                    let price = parseFloat(item.sell_price) || 0;
                    let mrp = parseFloat(item.mrp) || 0;
                    let discount = parseFloat(item.discount ?? price);
                    let category = item.category;
                    let subcategory = item.subcategory;

                    const row = `
                            <tr>
                                <td>#</td>
                                <td>${name}
                                    <input type="hidden" name="items[${itemIndex}][product_id]" value="${productId}">
                                    <input type="hidden" name="items[${itemIndex}][name]" value="${name}">
                                    <input type="hidden" name="items[${itemIndex}][mrp]" value="${mrp}">
                                    <input type="hidden" name="items[${itemIndex}][discount_price]" value="${mrp}">
                                    <input type="hidden" name="items[${itemIndex}][category]" value="${category}">
                                    <input type="hidden" name="items[${itemIndex}][subcategory]" value="${subcategory}">
                                </td>
                                <td>
                                    <input type="number" name="items[${itemIndex}][quantity]" 
                                        class="form-control qty-input"
                                        value="${qty}" min="1"
                                        data-sell_price="${price}" 
                                        data-discount="${discount}" data-mrp="${mrp}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[${itemIndex}][sell_price]"
                                        class="form-control item-price" value="${discount}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[${itemIndex}][price]"
                                        class="form-control item-total-input" value="${Math.ceil(discount * qty)}">
                                </td>
                                <td>
                                    <img src="{{ asset('external/delete24dp1f1f1ffill0wght400grad0opsz2414471-7kar.svg') }}" 
                                        class="btn btn-sm remove-item">
                                </td>
                            </tr>
                        `;

                    $('#invoice-items-body').append(row);
                    itemIndex++;
                });

                updateTotals();
            }

            let itemIndex = 0;
            const storeId = {{ $branch_data->id }};

            let creditLimit = 0;
            let cashAmount = 0;
            let upiAmount = 0;
            let grandTotal = 0;
            let totalSellPrice = 0;
            let discountTotal = 0;

            // Initially hide both
            $(".credit-section").hide();
            $(".commission-section").hide();

            // Show based on branch
            if (storeId == 1) {
                $('#party-id').show();
                $('#commission-id').hide();
            } else {
                $('#party-id').hide();
                $('#commission-id').show();
            }

            // Update the totals dynamically
            function updateTotals() {

                grandTotal = 0;
                totalSellPrice = 0;
                discountTotal = 0;

                const partyId = $('#party-id').val();
                const commissionId = $('#commission-id').val();

                $('#invoice-items-body tr').each(function() {

                    const $row = $(this);
                    const qty = parseFloat($row.find('.qty-input').val()) || 0;
                    const price = parseFloat($row.find('.item-price').val()) || 0;

                    const sell_price = parseFloat($row.find('.qty-input').data('sell_price')) || 0;
                    const discount = parseFloat($row.find('.qty-input').data('discount')) || sell_price;

                    const rowTotal = Math.ceil(qty * price);
                    $row.find('.item-total-input').val(rowTotal);
                    const disAmt = (sell_price - discount) * qty;

                
                    totalSellPrice += rowTotal;
                    discountTotal += disAmt;
                    grandTotal += rowTotal;
                });

                $('#total').text('₹' + Math.ceil(totalSellPrice));
                $('#grand-total').text('₹' + Math.ceil(grandTotal));

                if (partyId || commissionId) {
                    $('#discount-total').text('₹' + discountTotal.toFixed(2));
                } else {
                    $('#discount-total').text('₹0.00');
                }

                $('#total_discount').val(discountTotal);
                $('#gr_total').val(totalSellPrice);

                $('#sub_total').val(grandTotal);

                $('#cash-amount').val(Math.ceil(grandTotal));
            }

            // Add product to the invoice
            $('#add-product-btn').on('click', function() {

                const selected = $('#new-product-id option:selected');
                const productId = selected.val();
                const name = selected.data('name');
                const mrp = parseFloat(selected.data('mrp'));
                let discount = parseFloat(selected.data('discount'));
                const sell_price = parseFloat(selected.data('sell_price'));
                const qty = parseInt($('#new-product-qty').val()) || 1;
                const category = selected.data('category');
                const subcategory = selected.data('subcategory');

                if (!productId || !qty) return alert('Select product and quantity.');

                // Check if product already exists in the table
                let productRow = null;
                $('#invoice-items-body tr').each(function() {
                    const existingId = $(this).find('input[name*="[product_id]"]').val();
                    if (existingId == productId) {
                        productRow = $(this);
                        return false;
                    }
                });

                // ✅ INVENTORY CHECK START
                $.post('{{ route('inventory.check') }}', {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    product_id: productId,
                    store_id: storeId,
                    quantity: qty
                }, function(response) {

                    if (response.status === 'error') {
                        Swal.fire("Stock Error", response.message, "error");
                        return;
                    }

                    // ✅ ONLY AFTER STOCK OK → RUN YOUR ORIGINAL LOGIC

                    const partyId = $('#party-id').val();
                    const commissionId = $('#commission-id').val();

                    if (partyId) {

                        $.get("{{ url('/party-customer-discount') }}/" + partyId + '/' + productId,
                            function(response) {

                                if (response.discount) {
                                    discount = response.discount;
                                }

                                if (!productRow) {

                                    const initialPrice = discount;

                                    const row = `
                                        <tr>
                                            <td>#</td>
                                            <td>${name}
                                                <input type="hidden" name="items[${itemIndex}][product_id]" value="${productId}">
                                                <input type="hidden" name="items[${itemIndex}][name]" value="${name}">
                                                <input type="hidden" name="items[${itemIndex}][mrp]" value="${mrp}">
                                                <input type="hidden" name="items[${itemIndex}][discount_price]" value="${mrp}">
                                                <input type="hidden" name="items[${itemIndex}][category]" value="${category}">
                                                <input type="hidden" name="items[${itemIndex}][subcategory]" value="${subcategory}">
                                            </td>
                                            <td>
                                                <input type="number" name="items[${itemIndex}][quantity]" class="form-control qty-input" value="${qty}" min="1" data-sell_price="${sell_price}" data-discount="${discount}" data-mrp="${mrp}">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="items[${itemIndex}][sell_price]" class="form-control item-price" value="${initialPrice}">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="items[${itemIndex}][price]" class="form-control item-total-input" value="${Math.ceil(initialPrice * qty)}">
                                            </td>
                                            <td><img src="{{ asset('external/delete24dp1f1f1ffill0wght400grad0opsz2414471-7kar.svg') }}" class="btn btn-sm remove-item"></td>
                                        </tr>
                                    `;
                                    $('#invoice-items-body').append(row);
                                    itemIndex++;
                                    updateTotals();
                                }

                            });

                    } else if (commissionId) {

                        if (!productRow) {

                            const initialPrice = discount;

                            const row = `
                                <tr>
                                    <td>#</td>
                                    <td>${name}
                                        <input type="hidden" name="items[${itemIndex}][product_id]" value="${productId}">
                                        <input type="hidden" name="items[${itemIndex}][name]" value="${name}">
                                        <input type="hidden" name="items[${itemIndex}][mrp]" value="${mrp}">
                                        <input type="hidden" name="items[${itemIndex}][discount_price]" value="${mrp}">
                                        <input type="hidden" name="items[${itemIndex}][category]" value="${category}">
                                        <input type="hidden" name="items[${itemIndex}][subcategory]" value="${subcategory}">
                                    </td>
                                    <td>
                                        <input type="number" name="items[${itemIndex}][quantity]" class="form-control qty-input" value="${qty}" min="1" data-sell_price="${sell_price}" data-discount="${discount}" data-mrp="${mrp}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[${itemIndex}][sell_price]" class="form-control item-price" value="${initialPrice}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[${itemIndex}][price]" class="form-control item-total-input" value="${Math.ceil(initialPrice * qty)}">
                                    </td>
                                    <td><img src="{{ asset('external/delete24dp1f1f1ffill0wght400grad0opsz2414471-7kar.svg') }}" class="btn btn-sm remove-item"></td>
                                </tr>
                            `;
                            $('#invoice-items-body').append(row);
                            itemIndex++;
                            updateTotals();
                        }

                    } else {

                        if (!productRow) {

                            const initialPrice = sell_price;

                            const row = `
                                <tr>
                                    <td>#</td>
                                    <td>${name}
                                        <input type="hidden" name="items[${itemIndex}][product_id]" value="${productId}">
                                        <input type="hidden" name="items[${itemIndex}][name]" value="${name}">
                                        <input type="hidden" name="items[${itemIndex}][mrp]" value="${mrp}">
                                        <input type="hidden" name="items[${itemIndex}][discount_price]" value="${mrp}">
                                        <input type="hidden" name="items[${itemIndex}][category]" value="${category}">
                                        <input type="hidden" name="items[${itemIndex}][subcategory]" value="${subcategory}">
                                    </td>
                                    <td>
                                        <input type="number" name="items[${itemIndex}][quantity]" class="form-control qty-input" value="${qty}" min="1" data-sell_price="${sell_price}" data-discount="${discount}" data-mrp="${mrp}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[${itemIndex}][sell_price]" class="form-control item-price" value="${initialPrice}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[${itemIndex}][price]" class="form-control item-total-input" value="${Math.ceil(initialPrice * qty)}">
                                    </td>
                                    <td><img src="{{ asset('external/delete24dp1f1f1ffill0wght400grad0opsz2414471-7kar.svg') }}" class="btn btn-sm remove-item"></td>
                                </tr>
                            `;
                            $('#invoice-items-body').append(row);
                            itemIndex++;
                            updateTotals();
                        }
                    }

                    // ✅ Reset dropdown + qty (with select2-aware reset)
                    $('#new-product-id').val('').trigger('change.select2');
                    resizeProductSelect();
                    $('#new-product-qty').val('');

                });
                // ✅ INVENTORY CHECK END

            });

            // Remove product from invoice
            $(document).on('click', '.remove-item', function() {
                $(this).closest('tr').remove();
                updateTotals();
            });

            $(document).on('blur', '.item-price, .qty-input', function() {
                const $row = $(this).closest('tr');
                const qty = parseFloat($row.find('.qty-input').val()) || 0;
                const price = parseFloat($row.find('.item-price').val()) || 0;
                $row.find('.item-total-input').val(Math.ceil(qty * price));
                updateTotals();
            });

            $(document).on('blur', '.item-total-input', function() {
                const $row = $(this).closest('tr');
                const total = parseFloat($(this).val()) || 0;
                const qty = parseFloat($row.find('.qty-input').val()) || 1;
                const price = qty > 0 ? (total / qty) : 0;
                $row.find('.item-price').val(price.toFixed(2));
                updateTotals();
            });

            // Credit pay validation
            $('#creditpay-input').on('input', function() {
                const entered = parseFloat($(this).val()) || 0;
                const errorEl = $('#creditpay-error');
                const creditLimit = $("#left_credit_id").val();
                const grandTotal = parseFloat($('#grand-total').text().replace('₹', '')) || 0;
                const selectedPaymentMethod = $('input[name="payment_method"]:checked').val();


                let remainingAmount = grandTotal;
                if (selectedPaymentMethod === 'online') {
                    let remainingAmount = grandTotal - entered;
                    $('#upi-amount').val(remainingAmount >= 0 ? remainingAmount : 0);
                } else if (selectedPaymentMethod === 'cashupi') {
                    let remainingAmount = grandTotal - entered;
                    remainingAmount = Math.ceil(remainingAmount);
                    $('#cash-amount').val(remainingAmount >= 0 ? remainingAmount : 0);
                } else {
                    let remainingAmount = grandTotal - entered;
                    remainingAmount = Math.ceil(remainingAmount);
                    $('#cash-amount').val(remainingAmount >= 0 ? remainingAmount : 0);
                }

                let errorMsg = '';

                if (entered > creditLimit) {
                    errorMsg = 'Credit Pay cannot exceed Credit Limit ₹' + creditLimit;

                } else if (entered > grandTotal) {

                    Swal.fire("Credit Pay cannot exceed Invoice Total", "Credit Limit Exceeded",
                        "Credit pay (₹" +
                        creditLimit + ") cannot exceed credit limit (₹" +
                        creditLimit + ").", "error");
                    $(this).val(grandTotal);
                    return false;
                }

                if (errorMsg) {
                    errorEl.text(errorMsg).show();
                } else {
                    errorEl.hide();
                }
            });

            $(document).on('input', '.qty-input', function() {
                const $input = $(this);
                const qty = parseInt($input.val()) || 0;
                const $row = $input.closest('tr');
                const productId = $row.find('input[name*="[product_id]"]').val();

                if (!productId || qty <= 0) {
                    $input.val(1);
                    updateTotals();
                    return;
                }

                $.post('{{ route('inventory.check') }}', {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    product_id: productId,
                    store_id: storeId,
                    quantity: qty
                }, function(response) {
                    if (response.status === 'error') {
                        Swal.fire("Stock Error", response.message, "error");
                        $input.val(1);
                    }
                    updateTotals();
                });
            });

            $('form').on('submit', function(e) {

                const branchName = "{{ $branch_data->name }}";
                const partyId = $('#party-id').val();

                // ✅ WAREHOUSE → Party Required
                if (branchName === 'WAREHOUSE' && !partyId) {
                    e.preventDefault();
                    Swal.fire(
                        "Validation Error",
                        "Please select Party Customer for WAREHOUSE transactions.",
                        "warning"
                    );
                    return false;
                }

                // Existing credit validation
                const creditLimit = $("#left_credit_id").val();
                const creditPay = parseFloat($('input[name="creditpay"]').val()) || 0;
                const paymentMethod = $('input[name="payment_method"]:checked').val();
                const grandTotal = parseFloat($('#grand-total').text().replace('₹', '')) || 0;

                if (paymentMethod === 'credit' && creditPay < grandTotal) {
                    e.preventDefault();
                    Swal.fire("Error", "Full amount must be paid via credit.", "error");
                    return false;
                }

                if (creditPay > creditLimit) {
                    e.preventDefault();
                    Swal.fire(
                        "Credit Limit Exceeded",
                        "Credit pay (₹" + creditPay + ") cannot exceed credit limit (₹" + creditLimit +
                        ").",
                        "error"
                    );
                }
            });

            // Listen for changes on partyUser select
            $('#party-id').on('change', function() {

                const partyUserId = $(this).val();

                // Reset commission
                $('#commission-id').val('');

                if (!partyUserId) {
                    $(".credit-section").hide();
                    updateProductDiscounts(null, null);

                    return;
                }

                // ✅ SHOW ONLY CREDIT
                $(".credit-section").show();
                $(".commission-section").hide();

                // Fetch credit
                $.get('{{ route('partyUserCredit', ':id') }}'.replace(':id', partyUserId), function(res) {
                    $('#credit-limit').text('₹' + res.credit);
                    $('#left_credit').text('₹' + res.left_credit);
                    $('#left_credit_id').val(res.left_credit);
                    $('#creditpay-input').val('');
                });

                // ✅ Apply discount
                updateProductDiscounts(partyUserId, null);
            });

            // Listen for changes on commissionUser select
            $('#commission-id').on('change', function() {

                const commissionUserId = $(this).val();

                // Reset party
                $('#party-id').val('');

                if (!commissionUserId) {
                    $(".commission-section").hide();
                    return;
                }

                // ✅ SHOW ONLY COMMISSION
                $(".commission-section").show();
                $(".credit-section").hide();

                // ✅ Apply discount
                updateProductDiscounts(null, commissionUserId);
            });

            // Function to update product discounts when partyUser or commissionUser is selected
            function updateProductDiscounts(partyUserId = null, commissionUserId = null) {

                $('#invoice-items-body tr').each(function() {

                    const row = $(this);
                    const productId = row.find('input[name*="[product_id]"]').val();
                    const sell_price = parseFloat(row.find('.qty-input').data('sell_price')) || 0;

                    if (partyUserId) {

                        // 🔥 PARTY DISCOUNT (API)
                        $.get(`{{ url('/party-customer-discount') }}/${partyUserId}/${productId}`,
                            function(res) {

                                let discountPrice = res.discount ? parseFloat(res.discount) : sell_price;

                                row.find('.qty-input').data('discount', discountPrice);
                                row.find('.item-price').val(discountPrice);

                                updateTotals();

                            });

                    } else if (commissionUserId) {

                        // 🔥 COMMISSION DISCOUNT (product table)
                        const discount = parseFloat(row.find('.qty-input').data('discount')) || sell_price;

                        row.find('.qty-input').data('discount', discount);
                        row.find('.item-price').val(discount);

                        updateTotals();

                    } else {

                        // 🔥 NORMAL (NO DISCOUNT)
                        row.find('.qty-input').data('discount', sell_price);
                        row.find('.item-price').val(sell_price);

                        updateTotals();
                    }
                });
            }

            // Handle radio button change event
            $('input[name="payment_method"]').on('change', function() {

                const selectedPaymentMethod = $(this).val();

                let total = parseFloat($('#grand-total').text().replace('₹', '')) || 0;

                let partyId = $('#party-id').val();
                let commissionId = $('#commission-id').val();
                let creditPay = parseFloat($('#creditpay-input').val()) || 0;

                // Apply credit deduction
                if (partyId || commissionId) {
                    total = total - creditPay;
                }

                // RESET
                $('#cash-amount').val('');
                $('#upi-amount').val('');

                if (selectedPaymentMethod === 'cash') {

                    $('#cash-field').show();
                    $('#upi-field').hide();

                    $('#cash-amount').val(Math.ceil(total));
                    $('#cash-amount').prop('readonly', true);

                } else if (selectedPaymentMethod === 'online') {

                    $('#cash-field').hide();
                    $('#upi-field').show();

                    $('#upi-amount').val(Math.ceil(total));
                    $('#upi-amount').prop('readonly', true);

                } else if (selectedPaymentMethod === 'cashupi') {

                    $('#cash-field').show();
                    $('#upi-field').show();

                    $('#cash-amount').val(Math.ceil(total));
                    $('#upi-amount').val(0);

                    $('#cash-amount').prop('readonly', false);
                    $('#upi-amount').prop('readonly', false);
                } else if (selectedPaymentMethod === 'credit') {

                    // ✅ CREDIT FULL PAYMENT

                    $('#cash-field').hide();
                    $('#upi-field').hide();

                    // Set full credit
                    $('#creditpay-input').val(Math.ceil(total));

                    // Optional: lock input
                    $('#creditpay-input').prop('readonly', true);
                }

            });

            $('input[name="payment_method"]').on('change', function() {
                if ($(this).val() !== 'credit') {
                    $('#creditpay-input').prop('readonly', false);
                }
            });

            // When Cash input changes
            $('#cash-amount').on('input', function() {
                let cash = parseFloat($(this).val()) || 0;

                if ($('#cash-upi-option').is(':checked')) {
                    let total = parseFloat($('#grand-total').text().replace('₹', '')) || 0;

                    let upi = total - cash;
                    $('#upi-amount').val(upi >= 0 ? Math.ceil(upi) : 0);
                }
            });

            // When UPI input changes
            $('#upi-amount').on('input', function() {
                let upi = parseFloat($(this).val()) || 0;

                if ($('#cash-upi-option').is(':checked')) {
                    let total = parseFloat($('#grand-total').text().replace('₹', '')) || 0;

                    let cash = total - upi;
                    $('#cash-amount').val(cash >= 0 ? Math.ceil(cash) : 0);
                }
            });
        });
    </script>
@endsection