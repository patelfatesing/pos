@extends('layouts.backend.layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        input[type=number] {
            width: 60px;
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

        input[type=number] {
            width: 90px !important;
        }

        .qty-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .qty-input {
            width: 60px;
            height: 40px;
            text-align: center;
            border-radius: 12px;
            border: 1px solid #ddd;
            font-weight: 500;
            font-size: 16px;
        }

        /* Orange Buttons */
        .qty-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: none;
            background: #ff6a1a;
            color: #fff;
            font-size: 18px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.2s;
        }

        /* Hover Effect */
        .qty-btn:hover {
            background: #e85d0f;
        }

        /* Click Effect */
        .qty-btn:active {
            transform: scale(0.95);
        }
    </style>

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0">Transaction Invoice Details - #{{ $invoice->invoice_number }}</h4>
                    </div>
                    <div>
                        <span class="mr-2">#{{ $branch_data->name }}</span>
                        {{-- <a href="{{ route('sales.sales.list') }}" class="btn btn-secondary">Back</a> --}}
                        <button onclick="window.history.back()" class="btn btn-secondary">
                            Back
                        </button>
                    </div>
                </div>

                <form id="invoice-items-form" method="POST"
                    action="{{ route('sales.invoice.updateItems', $invoice->id) }}">

                    @csrf
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-2 align-items-center">
                                <div class="col d-flex justify-content-end">

                                    @if ($invoice->branch_id == 1 && $invoice->partyUser)
                                        <span class="badge bg-success text-dark">Party:
                                            {{ $invoice->partyUser->first_name }}</span>
                                    @elseif (!empty($invoice->commission_user_id) && $invoice->commissionUser)
                                        <span class="badge bg-success text-dark">Commission:
                                            {{ $invoice->commissionUser->first_name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="verify" value="{{ $verify }}">
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
                                <tbody id="invoice-items-body">
                                    @php
                                        $total = 0;
                                        $sub_total = 0;
                                        $total_dis = 0;
                                    @endphp
                                    @foreach ($invoice->items as $i => $item)
                                        @php
                                            $product = $allProducts->where('id', $item['product_id'])->first();

                                            // $basePrice = $product->mrp;

                                            $basePrice = $product->sell_price;
                                            $discount = $product->discount_price;

                                            $partyDiscount = null;
                                            if ($invoice->branch_id == 1) {
                                                if ($invoice->party_user_id) {
                                                    $partyDiscount = optional(
                                                        $partyPrices->where('product_id', $product->id)->first(),
                                                    )->cust_discount_price;
                                                } else {
                                                    $partyDiscount = $product->discount_price;
                                                }

                                                $dis = $product->sell_price - $partyDiscount;

                                                $total_dis += $item['quantity'] * $dis;
                                            } else {
                                                if ($invoice->commission_user_id) {
                                                    $partyDiscount = $product->discount_price;
                                                } else {
                                                    $partyDiscount = $product->sell_price;
                                                }
                                                $dis = $product->sell_price - $discount;
                                                $total_dis += $item['quantity'] * $dis;
                                            }

                                            $finalPrice = $partyDiscount ?? ($discount ?? $basePrice);

                                            $total += $product->sell_price * $item['quantity'];
                                            $sub_total += $finalPrice * $item['quantity'];
                                        @endphp
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>
                                                {{ $item['name'] }}
                                                <input type="hidden" name="items[{{ $i }}][product_id]"
                                                    value="{{ $item['product_id'] }}">
                                                <input type="hidden" name="items[{{ $i }}][name]"
                                                    value="{{ $item['name'] }}">
                                                <input type="hidden" name="items[{{ $i }}][sell_price]"
                                                    value="{{ $product->sell_price }}">

                                                <input type="hidden" name="items[{{ $i }}][mrp]"
                                                    value="{{ $finalPrice }}">
                                                <input type="hidden" name="items[{{ $i }}][price]"
                                                    value="{{ number_format($finalPrice * $item['quantity'], 2) }}">
                                                <input type="hidden" name="items[{{ $i }}][category]"
                                                    value="{{ $product->category->name }}">
                                                <input type="hidden" name="items[{{ $i }}][subcategory]"
                                                    value="{{ $product->subcategory->name }}">
                                                <input type="hidden" name="items[{{ $i }}][discount]"
                                                    value="{{ $partyDiscount }}">
                                                <input type="hidden" name="items[{{ $i }}][discount_price]"
                                                    value="{{ $finalPrice }}">

                                            </td>
                                            <td>
                                                <div class="qty-wrapper">
                                                    <button type="button" class="qty-btn minus">−</button>

                                                    <input type="number" name="items[{{ $i }}][quantity]"
                                                        class="qty-input" value="{{ $item['quantity'] }}"
                                                        data-price="{{ $basePrice }}"
                                                        data-discount="{{ $partyDiscount }}">

                                                    <button type="button" class="qty-btn plus">+</button>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($finalPrice < $basePrice)
                                                    <div class="price-stack">
                                                        <span class="discount">₹{{ number_format($finalPrice, 2) }}</span>
                                                        <span class="mrp">₹{{ number_format($basePrice, 2) }}</span>
                                                    </div>
                                                @else
                                                    ₹{{ number_format($basePrice, 2) }}
                                                @endif
                                            </td>
                                            <td class="item-total">
                                                <b>₹{{ number_format($finalPrice * $item['quantity'], 2) }}</b>
                                            </td>
                                            <td>
                                                <img src="{{ asset('external/delete24dp1f1f1ffill0wght400grad0opsz2414471-7kar.svg') }}"
                                                    alt="Delete"
                                                    class="main-screen-delete24dp1f1f1ffill0wght400grad0opsz24110 btn btn-sm remove-item">
                                            </td>
                                        </tr>
                                    @endforeach
                                    <?php
                                    
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- <div class="d-flex justify-content-end mt-3">
                        <div>
                            <h5>Total: ₹<span id="total">{{ number_format($total, 2) }}</span></h5>
                            <h5>Discount: ₹<span id="discount-total">{{ number_format($total_dis, 2) }}</span></h5>
                            <input type="hidden" id ="total_discount" name="total_discount" value="{{ $total_dis }}">
                            <input type="hidden" id="gr_total" name="total" value="{{ $total }}">
                            @if ($invoice->branch_id == 1 && $invoice->partyUser)
                                <p class="mb-1">
                                    <span class="fw-bold text-dark">Credit Limit:</span>
                                    ₹{{ number_format($invoice->partyUser->credit_points, 2) }}
                                </p>
                                <p class="mb-0">
                                    <span class="fw-bold text-dark">Credit Used (Invoice):</span>
                                    ₹<input type="number" name="creditpay" id="creditpay-input" min="0"
                                        step="0.01" class="form-control d-inline-block"
                                        style="width: 120px; display: inline;"
                                        value="{{ number_format($invoice->creditpay, 2, '.', '') }}">

                                    <small id="creditpay-error" class="text-danger d-block" style="display:none;"></small>
                                </p>
                                <h5>Sub Total: ₹<span id="grand-total">{{ number_format($sub_total, 2) }}</span></h5>
                            @endif
                            <button type="submit" class="btn btn-success">Save Invoice Items</button>

                        </div>
                    </div> --}}

                    <div class="card-body">
                        <div class="row mt-4 mb-3">
                            <div class="offset-lg-8 col-lg-4">
                                <div class="or-detail rounded">
                                    <div class="p-3">
                                        <h5 class="mb-3">Order Details</h5>
                                        <input type="hidden" id="total_discount" name="total_discount" value="0">
                                        <input type="hidden" id="ori_total_discount" name="ori_total_discount"
                                            value="0">
                                        <input type="hidden" id="gr_total" name="total" value="{{ $total }}">
                                        <input type="hidden" id="sub_total" name="sub_total" value="{{ $sub_total }}">
                                        <input type="hidden" id="ori_sub_total" name="ori_sub_total"
                                            value="{{ $sub_total }}">
                                        <input type="hidden" id="left_credit_id" value="0">

                                        <div class="mb-2 d-flex justify-content-between">
                                            <h6>Sub Total</h6>
                                            <p id="total">{{ number_format($total, 2) }}</p>
                                        </div>
                                        <div class="mb-2 d-flex justify-content-between">
                                            @if ($invoice->branch_id == 1 && $invoice->partyUser)
                                                <h6 class="credit-section">Party Deduction</h6>
                                            @else
                                                <h6 class="commission-section">Commission Deduction</h6>
                                            @endif
                                            <p id="discount-total">₹{{ number_format($total_dis, 2) }}</p>
                                        </div>
                                        @if ($invoice->branch_id == 1 && $invoice->partyUser)
                                            <div class="credit-section">
                                                <div class="mb-2 d-flex justify-content-between">
                                                    <h6>Credit Limit</h6>
                                                    <p id="credit-limit">
                                                        ₹{{ number_format($invoice->partyUser->credit_points, 2) }}</p>
                                                </div>
                                                <div class="mb-2 d-flex justify-content-between">
                                                    <h6>Left Limit</h6>
                                                    <p id="left_credit">
                                                        ₹{{ number_format($invoice->partyUser->left_credit, 2) }}</p>
                                                </div>
                                                <div class="mb-2 d-flex justify-content-between">
                                                    <h6>Credit Used (Invoice)</h6>
                                                    <p>₹<input type="number" name="creditpay" id="creditpay-input"
                                                            min="0" step="1"
                                                            class="form-control d-inline-block"
                                                            style="width: 120px; display: inline;"
                                                            value="{{ number_format($invoice->creditpay, 2, '.', '') }}">
                                                        <small id="creditpay-error" class="text-danger d-block"
                                                            style="display:none;"></small>
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                        <!-- Payment Method Radio Buttons -->
                                        <div class="mb-2 d-flex justify-content-between">
                                            <label><strong>Payment Method</strong></label>
                                            <div>
                                                <input type="radio" id="cash-option" name="payment_method"
                                                    value="cash" @if ($invoice->payment_mode == 'cash') checked @endif>
                                                <label for="cash-option">Cash</label>
                                                <input type="radio" id="upi-option" name="payment_method"
                                                    value="online" @if ($invoice->payment_mode == 'online') checked @endif>
                                                <label for="upi-option">UPI</label>
                                                <input type="radio" id="cash-upi-option" name="payment_method"
                                                    value="cashupi" @if ($invoice->payment_mode == 'cashupi') checked @endif>
                                                <label for="cash-upi-option">Cash + UPI</label>
                                                <input type="radio" id="credit-option" name="payment_method"
                                                    value="credit" @if ($invoice->payment_mode == 'credit') checked @endif>
                                                <label for="credit-option">Credit</label>
                                            </div>
                                        </div>

                                        <!-- Cash and UPI Inputs Section -->
                                        <div id="payment-fields">
                                            <div id="cash-field" class="payment-input">
                                                <h6>Cash</h6>
                                                <input type="number" id="cash-amount" class="form-control"
                                                    min="0" step="1" readonly name="cash_amount">
                                            </div>

                                            <div id="upi-field" class="payment-input" style="display: none;">
                                                <h6>UPI</h6>
                                                <input type="number" id="upi-amount" class="form-control"
                                                    name="upi_amount" min="0" step="1" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ttl-amt py-2 px-3 d-flex justify-content-between align-items-center">
                                        <h6>Total</h6>
                                        <h3 class="text-primary font-weight-700" id="grand-total">
                                            {{ number_format($sub_total, 2) }}</h3>
                                    </div>
                                    <div class="ttl-amt py-2 px-3 d-flex justify-content-between align-items-center">
                                        <h6>Return Amount</h6>
                                        <h3 class="text-primary font-weight-700" id="return-amt">
                                            {{ number_format($sub_total, 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-3 total-summary mb-3">
                            <div>

                                <button type="submit" class="btn btn-success">Save Invoice Items</button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        let itemIndex = {{ count($invoice->items) }};
        const storeId = {{ $invoice->branch_id }};
        const creditLimit = {{ $invoice->partyUser->credit_points ?? 0 }};
        let grandTotal = 0;

        $(document).ready(function() {
            updateTotals();
        });

        function updateTotals() {

            grandTotal = 0;
            let totalSellPrice = 0;
            let discountTotal = 0;
            let ori_sub_total = parseFloat($('#ori_sub_total').val()) || 0;

            $('#invoice-items-body tr').each(function() {

                const qty = parseFloat($(this).find('.qty-input').val()) || 0;

                const price = parseFloat($(this).find('.qty-input').data('price')) || 0;
                const discount = parseFloat($(this).find('.qty-input').data('discount')) || price;

                const finalPrice = discount > 0 ? discount : price;

                const rowTotal = finalPrice * qty;

                // ✅ APPLY SAME ROUNDING
                const roundedRowTotal = Math.ceil(rowTotal);

                // Update row total
                $(this).find('.item-total').html('<b>₹' + roundedRowTotal + '</b>');

                grandTotal += roundedRowTotal;

                totalSellPrice += Math.ceil(price * qty);
                discountTotal += (price - finalPrice) * qty;
            });

            // ✅ ROUND TOTALS SAME AS ADD PAGE
            grandTotal = Math.ceil(grandTotal);
            totalSellPrice = Math.ceil(totalSellPrice);

            // Prevent negative return
            let returnAmt = Math.max(0, ori_sub_total - grandTotal);

            $('#return-amt').text(Math.ceil(returnAmt));
            $('#grand-total').text(grandTotal);
            $('#discount-total').text(discountTotal.toFixed(2));

            $('#total_discount').val(discountTotal.toFixed(2));
            $('#gr_total').val(grandTotal);

            $('#total').text(totalSellPrice);
            $('#sub_total').val(totalSellPrice);
        }


        // ADD NEW PRODUCT
        $('#add-product-btn').on('click', function() {
            const selected = $('#new-product-id option:selected');
            const productId = selected.val();
            const name = selected.data('name');
            const mrp = parseFloat(selected.data('mrp'));
            const discount = parseFloat(selected.data('discount'));
            const sell_price = parseFloat(selected.data('sell_price'));
            const qty = parseInt($('#new-product-qty').val()) || 1;

            if (!productId || !qty) return alert('Select product and quantity.');

            // Check if product already exists in the table
            let productRow = null;
            $('#invoice-items-body tr').each(function() {
                const existingId = $(this).find('input[name*="[product_id]"]').val();
                if (existingId == productId) {
                    productRow = $(this);
                    return false; // break loop
                }
            });

            $.post('{{ route('inventory.check') }}', {
                _token: $('meta[name="csrf-token"]').attr('content'),
                product_id: productId,
                store_id: storeId,
                quantity: qty
            }, function(response) {
                if (response.status === 'error') {
                    Swal.fire("Stock Error", response.message, "error");
                } else {
                    const usePrice = (discount && discount < sell_price) ? discount : sell_price;
                    const priceDisplay = (discount && discount < sell_price) ?
                        `<div class="price-stack"><span class="discount">₹${discount.toFixed(2)}</span><span class="mrp">₹${sell_price.toFixed(2)}</span></div>` :
                        `₹${sell_price.toFixed(2)}`;
                    const total = usePrice * qty;

                    if (productRow) {
                        // ➕ Product exists → increase quantity
                        const qtyInput = productRow.find('.qty-input');
                        const currentQty = parseInt(qtyInput.val()) || 0;
                        const newQty = currentQty + qty;
                        qtyInput.val(newQty);
                        updateTotals();
                    } else {
                        // ➕ New Product Row
                        const row = `
                    <tr>
                        <td>#</td>
                        <td>
                            ${name}
                            <input type="hidden" name="items[${itemIndex}][product_id]" value="${productId}">
                            <input type="hidden" name="items[${itemIndex}][name]" value="${name}">
                            <input type="hidden" name="items[${itemIndex}][mrp]" value="${sell_price}">
                            <input type="hidden" name="items[${itemIndex}][discount]" value="${discount}">
                            <input type="hidden" name="items[${itemIndex}][discount_price]" value="${mrp}">
                        </td>
                       <td>
                                <div class="d-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-sm btn-secondary qty-minus">−</button>

                                    <input type="number" 
                                        name="items[${itemIndex}][quantity]" 
                                        class="form-control qty-input text-center"
                                        value="${qty}" 
                                        data-price="${sell_price}" 
                                        data-discount="${discount}"
                                        style="width:60px;">

                                    <button type="button" class="btn btn-sm btn-secondary qty-plus">+</button>
                                </div>
                            </td>
                        <td>${priceDisplay}</td>
                        <td class="item-total"><b>₹${total.toFixed(2)}</b></td>
                        <td><img src="{{ asset('external/delete24dp1f1f1ffill0wght400grad0opsz2414471-7kar.svg') }}" alt="Delete" class="main-screen-delete24dp1f1f1ffill0wght400grad0opsz24110 btn btn-sm remove-item"></td>
                    </tr>
                `;
                        $('#invoice-items-body').append(row);
                        itemIndex++;
                        updateTotals();
                    }

                    // Clear input fields
                    $('#new-product-id').val('');
                    $('#new-product-qty').val('');
                }
            });
        });


        // QUANTITY INPUT VALIDATION
        $(document).on('input', '.qty-input', function() {

            const $input = $(this);
            let qty = parseInt($input.val()) || 1;

            if (qty < 1) qty = 1;

            updateQtyWithCheck($input, qty);
        });

        // REMOVE PRODUCT
        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
            updateTotals();
        });

        $('#creditpay-input').on('input', function() {
            const entered = parseFloat($(this).val()) || 0;
            const errorEl = $('#creditpay-error');
            const creditLimit = {{ $invoice->partyUser->credit_points ?? 0 }};
            const grandTotal = parseFloat($('#grand-total').text()) || 0;

            let errorMsg = '';

            if (entered > creditLimit) {
                errorMsg = 'Credit Pay cannot exceed Credit Limit ₹' + creditLimit.toFixed(2);

            } else if (entered > grandTotal) {

                Swal.fire("Credit Pay cannot exceed Invoice Total", "Credit Limit Exceeded", "Credit pay (₹" +
                    creditLimit + ") cannot exceed credit limit (₹" +
                    creditLimit + ").", "error");
                $(this).val(grandTotal.toFixed(2));
                return false;
                // errorMsg = 'Credit Pay cannot exceed Invoice Total ₹' + grandTotal.toFixed(2);
            }

            if (errorMsg) {
                errorEl.text(errorMsg).show();
            } else {
                errorEl.hide();
            }
        });


        $('form').on('submit', function(e) {
            const creditLimit = {{ $invoice->partyUser->credit_points ?? 0 }};
            const creditPay = parseFloat($('input[name="creditpay"]').val()) || 0;

            if (creditPay > creditLimit) {
                e.preventDefault();
                Swal.fire("Credit Limit Exceeded", "Credit pay (₹" + creditPay + ") cannot exceed credit limit (₹" +
                    creditLimit + ").", "error");
            }
        });

        // ===================== PLUS BUTTON =====================
        $(document).on('click', '.qty-plus', function() {

            const $row = $(this).closest('tr');
            const $input = $row.find('.qty-input');

            let qty = parseInt($input.val()) || 0;
            qty++;

            updateQtyWithCheck($input, qty);
        });

        // ===================== MINUS BUTTON =====================
        $(document).on('click', '.qty-minus', function() {

            const $row = $(this).closest('tr');
            const $input = $row.find('.qty-input');

            let qty = parseInt($input.val()) || 0;

            if (qty > 1) {
                qty--;
                updateQtyWithCheck($input, qty);
            }
        });

        function updateQtyWithCheck($input, newQty) {

            const $row = $input.closest('tr');
            const productId = $row.find('input[name*="[product_id]"]').val();

            $.post('{{ route('inventory.check') }}', {
                _token: $('meta[name="csrf-token"]').attr('content'),
                product_id: productId,
                store_id: storeId,
                quantity: newQty
            }, function(response) {

                if (response.status === 'error') {
                    Swal.fire("Stock Error", response.message, "error");
                    return;
                }

                $input.val(newQty);

                // 🔥 IMPORTANT
                updateTotals();
            });
        }
        // Handle radio button change event
        $('input[name="payment_method"]').on('change', function() {
            const selectedPaymentMethod = $(this).val();

            // Reset cash and UPI input fields
            $('#cash-amount').val('');
            $('#upi-amount').val('');

            let get_total = $('#gr_total').val();

            if (selectedPaymentMethod === 'cash') {
                // Show Cash field only and make it editable
                $('#cash-amount').val(get_total);
                $('#upi-amount').val('');
                $('#cash-field').show();
                $('#upi-field').hide();
                $('#cash-amount').prop('readonly', true);
                $('#upi-amount').prop('readonly', true);
            } else if (selectedPaymentMethod === 'online') {
                // Show UPI field only and make it editable
                $('#cash-amount').val('');
                $('#upi-amount').val(get_total);
                $('#cash-field').hide();
                $('#upi-field').show();
                $('#cash-amount').prop('readonly', true);
                $('#upi-amount').prop('readonly', true);
            } else if (selectedPaymentMethod === 'cashupi') {
                // Show both fields and allow dynamic adjustment between them
                $('#cash-amount').val(get_total);
                $('#upi-amount').val('');
                $('#cash-field').show();
                $('#upi-field').show();
                $('#cash-amount').prop('readonly', false);
                $('#upi-amount').prop('readonly', false);
            }
            updatePaymentFields();
        });

        // When Cash input changes
        $('#cash-amount').on('input', function() {
            cashAmount = parseFloat($(this).val()) || 0;

            // If both Cash + UPI are selected, update the total
            if ($('#cash-upi-option').is(':checked')) {
                let remainingAmount = grandTotal - cashAmount;
                $('#upi-amount').val(remainingAmount >= 0 ? remainingAmount : 0);
            }
        });

        // When UPI input changes
        $('#upi-amount').on('input', function() {
            upiAmount = parseFloat($(this).val()) || 0;

            // If both Cash + UPI are selected, update the total
            if ($('#cash-upi-option').is(':checked')) {
                let remainingAmount = grandTotal - upiAmount;
                $('#cash-amount').val(remainingAmount >= 0 ? remainingAmount : 0);
            }
        });

        // PLUS
        $(document).on('click', '.qty-btn.plus', function() {
            const $input = $(this).siblings('.qty-input');
            let qty = parseInt($input.val()) || 0;
            qty++;

            updateQtyWithCheck($input, qty);
        });

        // MINUS
        $(document).on('click', '.qty-btn.minus', function() {
            const $input = $(this).siblings('.qty-input');
            let qty = parseInt($input.val()) || 0;

            if (qty > 1) {
                qty--;
                updateQtyWithCheck($input, qty);
            }
        });

        function updatePaymentFields() {

            const method = $('input[name="payment_method"]:checked').val();

            let grandTotal = parseFloat($('#grand-total').text()) || 0;
            let creditPay = parseFloat($('#creditpay-input').val()) || 0;

            grandTotal = Math.ceil(grandTotal);

            // RESET
            $('#cash-amount').val('');
            $('#upi-amount').val('');

            // 👉 CASH
            if (method === 'cash') {

                let payable = grandTotal - creditPay;
                payable = payable < 0 ? 0 : payable;

                $('#cash-field').show();
                $('#upi-field').hide();

                $('#cash-amount').val(payable);
                $('#cash-amount').prop('readonly', true);
            }

            // 👉 UPI
            else if (method === 'online') {

                let payable = grandTotal - creditPay;
                payable = payable < 0 ? 0 : payable;

                $('#cash-field').hide();
                $('#upi-field').show();

                $('#upi-amount').val(payable);
                $('#upi-amount').prop('readonly', true);
            }

            // 👉 CASH + UPI
            else if (method === 'cashupi') {

                let payable = grandTotal - creditPay;
                payable = payable < 0 ? 0 : payable;

                $('#cash-field').show();
                $('#upi-field').show();

                $('#cash-amount').val(payable);
                $('#upi-amount').val(0);

                $('#cash-amount').prop('readonly', false);
                $('#upi-amount').prop('readonly', false);
            }

            // 👉 CREDIT (FULL PAYMENT)
            else if (method === 'credit') {

                $('#cash-field').hide();
                $('#upi-field').hide();

                // ✅ Always full amount
                $('#creditpay-input').val(grandTotal);

                $('#creditpay-input').prop('readonly', true);
            }
        }
    </script>
@endsection
