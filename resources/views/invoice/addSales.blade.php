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

        .credit-section {
            margin-top: 20px;
        }

        .total-summary h5 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        #add-product-btn {
            white-space: nowrap;
        }

        #payment-fields {
            display: flex;
            gap: 10px;
        }

        #payment-fields .payment-input {
            flex: 1;
        }
    </style>

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between mb-3">
                    <div>
                        <h4 class="mb-0">Add Transaction - #{{ $branch_data->name }}</h4>
                    </div>
                    <a href="{{ route('sales.sales.list') }}" class="btn btn-secondary">Back</a>
                </div>

                <form id="invoice-items-form" method="POST" action="{{ route('sales.invoice.insert-sale') }}">
                    @csrf
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-4">
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
                                <input type="hidden" name="shift_id" value="{{ $Shift_data->id }}">
                                <div class="col-md-1">
                                    <input type="number" min="1" id="new-product-qty" class="form-control"
                                        placeholder="Qty">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-primary mr-2" id="add-product-btn">
                                        Add Item
                                    </button>
                                </div>
                                <div class="col-md-4 d-flex">
                                </div>
                                <div class="col-md-2 gap-2">
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


                    <div class="row mb-0">
                        <div class="offset-lg-8 col-lg-4">
                            <div class="or-detail rounded">
                                <div class="p-3">
                                    <h5 class="mb-3">Order Details</h5>
                                    <input type="hidden" id="total_discount" name="total_discount" value="0">
                                    <input type="hidden" id="gr_total" name="sub_total" value="0">
                                    <input type="hidden" id="sub_total" name="total" value="0">
                                    <input type="hidden" id="left_credit_id" value="0">

                                    <div class="mb-2 d-flex justify-content-between">
                                        <h6>Sub Total</h6>
                                        <p id="total"></p>
                                    </div>
                                    <div class="mb-2 d-flex justify-content-between">
                                        <h6 class="credit-section">Party Deduction</h6>
                                        <h6 class="commission-section">Commission Deduction</h6>
                                        <p id="discount-total">₹</p>
                                    </div>
                                    <div class="credit-section">
                                        <div class="mb-2 d-flex justify-content-between">
                                            <h6>Credit Limit</h6>
                                            <p id="credit-limit"></p>
                                        </div>
                                        <div class="mb-2 d-flex justify-content-between">
                                            <h6>Left Limit</h6>
                                            <p id="left_credit"></p>
                                        </div>
                                        <div class="mb-2 d-flex justify-content-between">
                                            <h6>Credit Used (Invoice)</h6>
                                            <p>₹<input type="number" name="creditpay" id="creditpay-input" min="0"
                                                    step="0.1" class="form-control d-inline-block"
                                                    style="width: 120px; display: inline;">
                                                <small id="creditpay-error" class="text-danger d-block"
                                                    style="display:none;"></small>
                                            </p>
                                        </div>
                                    </div>
                                    <!-- Payment Method Radio Buttons -->
                                    <div class="mb-2 d-flex justify-content-between">
                                        <label><strong>Payment Method</strong></label>
                                        <div>
                                            <input type="radio" id="cash-option" name="payment_method" value="cash"
                                                checked>
                                            <label for="cash-option">Cash</label>
                                            <input type="radio" id="upi-option" name="payment_method" value="online">
                                            <label for="upi-option">UPI</label>
                                            <input type="radio" id="cash-upi-option" name="payment_method"
                                                value="cashupi">
                                            <label for="cash-upi-option">Cash + UPI</label>
                                                <input type="radio" id="credit-option" name="payment_method" value="credit"
                                            >
                                            
<label for="credit-option">Credit</label>
                                        </div>
                                    </div>

                                    <!-- Cash and UPI Inputs Section -->
                                    <div id="payment-fields">
                                        <div id="cash-field" class="payment-input">
                                            <h6>Cash</h6>
                                            <input type="number" id="cash-amount" class="form-control" min="0"
                                                step="1" readonly name="cash_amount">
                                        </div>

                                        <div id="upi-field" class="payment-input" style="display: none;">
                                            <h6>UPI</h6>
                                            <input type="number" id="upi-amount" class="form-control" name="upi_amount"
                                                min="0" step="1" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="ttl-amt py-2 px-3 d-flex justify-content-between align-items-center">
                                    <h6>Total</h6>
                                    <h3 class="text-primary font-weight-700" id="grand-total"></h3>
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
                                    <input type="hidden" name="items[${itemIndex}][sell_price]" value="${price}">
                                    <input type="hidden" name="items[${itemIndex}][mrp]" value="${mrp}">
                                    <input type="hidden" name="items[${itemIndex}][discount_price]" value="${mrp}">
                                    <input type="hidden" name="items[${itemIndex}][category]" value="${category}">
                                    <input type="hidden" name="items[${itemIndex}][subcategory]" value="${subcategory}">
                                    <input type="hidden" name="items[${itemIndex}][price]" class="item_total_price" value="${Math.ceil(price * qty)}">
                                </td>
                                <td>
                                    <input type="number" name="items[${itemIndex}][quantity]" 
                                        class="form-control qty-input"
                                        value="${qty}" 
                                        data-price="${price}" 
                                        data-sell_price="${price}" 
                                        data-discount="${discount}" data-mrp="${mrp}">
                                </td>
                                <td>
                                    <div class="price-stack">
                                        <span class="discount">₹${discount}</span>
                                        <span class="sell_price">₹${price}</span>
                                    </div>
                                </td>
                                <td class="item-total"><b>₹${Math.ceil(price * qty)}</b></td>
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

                    const qty = parseFloat($(this).find('.qty-input').val()) || 0;

                    const mrp = parseFloat($(this).find('.qty-input').data('mrp')) || 0;
                    const sell_price = parseFloat($(this).find('.qty-input').data('sell_price')) || 0;
                    const price = parseFloat($(this).find('.qty-input').data('price')) || 0;
                    const discount = parseFloat($(this).find('.qty-input').data('discount')) || price;

                    let finalPrice = price; // default

                    // ✅ APPLY LOGIC
                    if (partyId || commissionId) {
                        finalPrice = discount;
                    }

                    // ✅ CALCULATIONS
                    const rowTotal = finalPrice * qty;
                    const subtotal = sell_price * qty;
                    const disAmt = (sell_price - discount) * qty;

                    // ✅ UPDATE ROW TOTAL
                    $(this).find('.item-total').html('<b>₹' + Math.ceil(rowTotal) + '</b>');
                    $(this).find('.item_total_price').val(Math.ceil(rowTotal));


                    // ✅ TOTALS
                    totalSellPrice += subtotal;
                    discountTotal += disAmt;
                    grandTotal += rowTotal;
                });

                $('#total').text(Math.ceil(totalSellPrice));
                $('#grand-total').text(Math.ceil(grandTotal));

                if (partyId || commissionId) {
                    $('#discount-total').text('₹' + discountTotal.toFixed(2));
                } else {
                    $('#discount-total').text('₹0');
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
                                    const row = `
                                        <tr>
                                            <td>#</td>
                                            <td>${name}
                                                <input type="hidden" name="items[${itemIndex}][product_id]" value="${productId}">
                                            </td>
                                            <td>
                                                <input type="number" name="items[${itemIndex}][quantity]" class="form-control qty-input" value="${qty}" data-price="${sell_price}" data-sell_price="${sell_price}" data-discount="${discount}" data-mrp="${mrp}">
                                                <input type="hidden" name="items[${itemIndex}][name]" value="${name}">
                                                <input type="hidden" name="items[${itemIndex}][sell_price]" value="${sell_price}">
                                                <input type="hidden" name="items[${itemIndex}][mrp]" value="${mrp}">
                                                <input type="hidden" name="items[${itemIndex}][discount_price]" value="${mrp}">
                                                <input type="hidden" name="items[${itemIndex}][category]" value="${category}">
                                                <input type="hidden" name="items[${itemIndex}][subcategory]" value="${subcategory}">
                                                <input type="hidden" name="items[${itemIndex}][price]" class="item_total_price" value="${Math.ceil(sell_price * qty)}">
                                            </td>
                                            <td>
                                                <div class="price-stack">
                                                     
                                                    <span class="discount">₹${discount}</span>
                                                    <span class="mrp">₹${sell_price}</span>
                                                   
                                                </div>
                                            </td>
                                            <td class="item-total"><b>₹${Math.ceil(sell_price * qty)}</b></td>
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

                            const row = `
                                <tr>
                                    <td>#</td>
                                    <td>${name}
                                        <input type="hidden" name="items[${itemIndex}][product_id]" value="${productId}">
                                    </td>
                                    <td>
                                        <input type="number" name="items[${itemIndex}][quantity]" class="form-control qty-input" value="${qty}" data-price="${sell_price}" data-sell_price="${sell_price}" data-discount="${discount}" data-mrp="${mrp}">
                                        <input type="hidden" name="items[${itemIndex}][name]" value="${name}">
                                        <input type="hidden" name="items[${itemIndex}][sell_price]" value="${sell_price}">
                                        <input type="hidden" name="items[${itemIndex}][mrp]" value="${mrp}">
                                        <input type="hidden" name="items[${itemIndex}][discount_price]" value="${mrp}">
                                         <input type="hidden" name="items[${itemIndex}][category]" value="${category}">
                                                <input type="hidden" name="items[${itemIndex}][subcategory]" value="${subcategory}">
                                                
                                        <input type="hidden" name="items[${itemIndex}][price]" class="item_total_price" value="${Math.ceil(sell_price * qty)}">
                                    </td>
                                    <td>
                                        <div class="price-stack">
                                            <span class="discount">${discount}</span>
                                            <span class="sell_price">₹${sell_price}</span>
                                            
                                            
                                        </div>
                                    </td>
                                    <td class="item-total"><b>₹${Math.ceil(sell_price * qty)}</b></td>
                                    <td><img src="{{ asset('external/delete24dp1f1f1ffill0wght400grad0opsz2414471-7kar.svg') }}" class="btn btn-sm remove-item"></td>
                                </tr>
                            `;
                            $('#invoice-items-body').append(row);
                            itemIndex++;
                            updateTotals();
                        }

                    } else {

                        if (!productRow) {

                            const row = `
                                <tr>
                                    <td>#</td>
                                    <td>${name}
                                        <input type="hidden" name="items[${itemIndex}][product_id]" value="${productId}">
                                    </td>
                                    <td>
                                        <input type="number" name="items[${itemIndex}][quantity]" class="form-control qty-input" value="${qty}" data-price="${sell_price}" data-sell_price="${sell_price}" data-discount="${discount}" data-mrp="${mrp}">
                                        <input type="hidden" name="items[${itemIndex}][name]" value="${name}">
                                        <input type="hidden" name="items[${itemIndex}][sell_price]" value="${sell_price}">
                                        <input type="hidden" name="items[${itemIndex}][mrp]" value="${mrp}">
                                        <input type="hidden" name="items[${itemIndex}][discount_price]" value="${mrp}">
                                          <input type="hidden" name="items[${itemIndex}][category]" value="${category}">
                                                <input type="hidden" name="items[${itemIndex}][subcategory]" value="${subcategory}">
                                        <input type="hidden" name="items[${itemIndex}][price]" class="item_total_price" value="${Math.ceil(sell_price * qty)}">
                                    </td>
                                    <td>
                                        <div class="price-stack">
                                            <span class="sell_price">${sell_price}</span>
                                        </div>
                                    </td>
                                    <td class="item-total"><b>₹${Math.ceil(sell_price * qty)}</b></td>
                                    <td><img src="{{ asset('external/delete24dp1f1f1ffill0wght400grad0opsz2414471-7kar.svg') }}" class="btn btn-sm remove-item"></td>
                                </tr>
                            `;
                            $('#invoice-items-body').append(row);
                            itemIndex++;
                            updateTotals();
                        }
                    }

                    $('#new-product-id').val('');
                    $('#new-product-qty').val('');

                });
                // ✅ INVENTORY CHECK END

            });

            // Remove product from invoice
            $(document).on('click', '.remove-item', function() {
                $(this).closest('tr').remove();
                updateTotals();
            });

            // Credit pay validation
            $('#creditpay-input').on('input', function() {
                const entered = parseFloat($(this).val()) || 0;
                const errorEl = $('#creditpay-error');
                const creditLimit = $("#left_credit_id").val();
                const grandTotal = parseFloat($('#grand-total').text()) || 0;
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
                const price = parseFloat($input.data('price')) || 0;
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
                const grandTotal = parseFloat($('#grand-total').text()) || 0;

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
                    $('#credit-limit').text(res.credit);
                    $('#left_credit').text(res.left_credit);
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
                    const qty = parseFloat(row.find('.qty-input').val()) || 1;
                    const mrp = parseFloat(row.find('.qty-input').data('mrp')) || 0;
                    const price = parseFloat(row.find('.qty-input').data('price')) || 0;


                    if (partyUserId) {

                        // 🔥 PARTY DISCOUNT (API)
                        $.get(`{{ url('/party-customer-discount') }}/${partyUserId}/${productId}`,
                            function(res) {

                                let discountPrice = res.discount ? parseFloat(res.discount) : price;

                                row.find('.qty-input').data('discount', discountPrice);

                                row.find('.price-stack').html(`
                                    <span class="discount">₹${discountPrice}</span>
                                    <span class="mrp">₹${price}</span>
                                `);

                                updateTotals();

                            });

                    } else if (commissionUserId) {

                        // 🔥 COMMISSION DISCOUNT (product table)
                        const discount = parseFloat(row.find('.qty-input').data('discount')) || price;

                        row.find('.qty-input').data('discount', discount);

                        row.find('.price-stack').html(`
                                <span class="discount">₹${discount}</span>
                                <span class="sell_price">₹${mrp}</span>
                            `);

                        updateTotals();

                    } else {

                        // 🔥 NORMAL (NO DISCOUNT)
                        row.find('.qty-input').data('discount', price);

                        row.find('.price-stack').html(`
                            <span class="discount">₹${price}</span>
                        `);

                        updateTotals();
                    }
                });
            }

            // Handle radio button change event
            $('input[name="payment_method"]').on('change', function() {

                const selectedPaymentMethod = $(this).val();

                let total = parseFloat($('#grand-total').text()) || 0;

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

                } 
                else if (selectedPaymentMethod === 'online') {

                    $('#cash-field').hide();
                    $('#upi-field').show();

                    $('#upi-amount').val(Math.ceil(total));
                    $('#upi-amount').prop('readonly', true);

                } 
                else if (selectedPaymentMethod === 'cashupi') {

                    $('#cash-field').show();
                    $('#upi-field').show();

                    $('#cash-amount').val(Math.ceil(total));
                    $('#upi-amount').val(0);

                    $('#cash-amount').prop('readonly', false);
                    $('#upi-amount').prop('readonly', false);
                } 
                else if (selectedPaymentMethod === 'credit') {

                    // ✅ CREDIT FULL PAYMENT

                    $('#cash-field').hide();
                    $('#upi-field').hide();

                    // Set full credit
                    $('#creditpay-input').val(Math.ceil(total));

                    // Optional: lock input
                    $('#creditpay-input').prop('readonly', true);
                }

            });

            $('input[name="payment_method"]').on('change', function () {
                if ($(this).val() !== 'credit') {
                    $('#creditpay-input').prop('readonly', false);
                }
            });

            // When Cash input changes
            // When Cash input changes
            $('#cash-amount').on('input', function() {
                let cash = parseFloat($(this).val()) || 0;

                if ($('#cash-upi-option').is(':checked')) {
                    let total = parseFloat($('#grand-total').text()) || 0;

                    let upi = total - cash;
                    $('#upi-amount').val(upi >= 0 ? Math.ceil(upi) : 0);
                }
            });

            // When UPI input changes
            // When UPI input changes
            $('#upi-amount').on('input', function() {
                let upi = parseFloat($(this).val()) || 0;

                if ($('#cash-upi-option').is(':checked')) {
                    let total = parseFloat($('#grand-total').text()) || 0;

                    let cash = total - upi;
                    $('#cash-amount').val(cash >= 0 ? Math.ceil(cash) : 0);
                }
            });
        });
    </script>
@endsection
