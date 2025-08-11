@extends('layouts.backend.layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    </style>

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="d-flex justify-content-between mb-3">
                    <h4>Add Transaction - #{{ $branch_data->name }}</h4>
                    <a href="{{ route('sales.sales.list') }}" class="btn btn-secondary">Back</a>
                </div>
                <form id="invoice-items-form" method="POST" action="{{ route('sales.invoice.insert-sale') }}">
                    @csrf
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-5">
                                    <select id="new-product-id" class="form-control">
                                        <option value="">Select Product</option>
                                        @foreach ($allProducts as $product)
                                            <option value="{{ $product->id }}" data-name="{{ $product->name }}"
                                                data-mrp="{{ $product->mrp }}" data-sell_price="{{ $product->sell_price }}"
                                                data-discount="{{ $product->discount_price }}">
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="hidden" name="branch_id" value="{{ $branch_data->id }}">
                                <input type="hidden" name="shift_id" value="{{ $Shift_data->id }}">
                                <div class="col-md-3">
                                    <input type="number" min="1" id="new-product-qty" class="form-control"
                                        placeholder="Qty">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary" id="add-product-btn">Add Item</button>
                                </div>
                                <div class="col-md-2">
                                    @if ($branch_data->id == 1)
                                        <select id="party-id" class="form-control" name="party_user_id">
                                            <option value="">Select Party Customer</option>
                                            @foreach ($partyUsers as $cust)
                                                <option value="{{ $cust->id }}">
                                                    {{ $cust->first_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <select id="commission-id" class="form-control" name="commission_user_id">
                                            <option value="">Select Commission Customer</option>
                                            @foreach ($commissionUsers as $cust)
                                                <option value="{{ $cust->id }}">
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

                    <div class="card-body">
                        <div class="row mt-4 mb-3">
                            <div class="offset-lg-8 col-lg-4">
                                <div class="or-detail rounded">
                                    <div class="p-3">
                                        <h5 class="mb-3">Order Details</h5>
                                        <input type="hidden" id="total_discount" name="total_discount" value="0">
                                        <input type="hidden" id="gr_total" name="total" value="0">
                                        <input type="hidden" id="sub_total" name="sub_total" value="0">
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
                                                <p>₹<input type="number" name="creditpay" id="creditpay-input"
                                                        min="0" step="1" class="form-control d-inline-block"
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
                                                <input type="radio" id="cash-option" name="payment_method"
                                                    value="cash" checked>
                                                <label for="cash-option">Cash</label>
                                                <input type="radio" id="upi-option" name="payment_method"
                                                    value="online">
                                                <label for="upi-option">UPI</label>
                                                <input type="radio" id="cash-upi-option" name="payment_method"
                                                    value="cashupi">
                                                <label for="cash-upi-option">Cash + UPI</label>
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
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let itemIndex = 0;
            const storeId = {{ $branch_data->id }};

            let creditLimit = 0;
            let cashAmount = 0;
            let upiAmount = 0;
            let grandTotal = 0;
            let totalSellPrice = 0;
            let discountTotal = 0;

            if (storeId == 1) {
                $('#party-id').show();
                $('#commission-id').hide();
                $(".credit-section").hide();
                $(".commission-section").hide();
            } else {
                $('#party-id').hide();
                $('#commission-id').show();
                $(".credit-section").hide();
                $(".commission-section").show();
            }

            // Update the totals dynamically
            function updateTotals() {

                grandTotal = 0;
                totalSellPrice = 0;
                discountTotal = 0;
                // Loop through each product row to calculate the totals
                $('#invoice-items-body tr').each(function() {
                    const qty = parseFloat($(this).find('.qty-input').val()) || 0;
                    const price = parseFloat($(this).find('.qty-input').data('price')) || 0;
                    const discount = parseFloat($(this).find('.qty-input').data('discount')) || 0;

                    totalSellPrice += price * qty; // Calculate total sell price
                    const rowTotal = qty * price; // Calculate row total
                    let dis = qty * (price - discount); // Calculate row discount

                    $(this).find('.item-total').html('<b>₹' + rowTotal.toFixed(2) +
                        '</b>'); // Update row total

                    var selectedCommissionUserId = $('#commission-id').val();

                    if (storeId != 1 && selectedCommissionUserId == "") {
                        grandTotal += rowTotal; // Add row total to grand total
                        discountTotal = 0; // Add row discount to discount total
                    } else {
                        grandTotal += rowTotal - dis; // Add row total minus discount to grand total
                        discountTotal += dis; // Add row discount to discount total
                    }

                    $('#cash-amount').val(grandTotal);
                });

                // Update the displayed totals
                $('#total').text(totalSellPrice.toFixed(2));
                $('#grand-total').text(grandTotal.toFixed(2));
                $('#discount-total').text(discountTotal.toFixed(2));
                $('#total_discount').val(discountTotal.toFixed(2));
                $('#gr_total').val(totalSellPrice.toFixed(2));
                $('#sub_total').val(grandTotal.toFixed(2));
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

                if (!productId || !qty) return alert('Select product and quantity.');

                // Check if product already exists in the table
                let productRow = null;
                $('#invoice-items-body tr').each(function() {
                    const existingId = $(this).find('input[name*="[product_id]"]').val();
                    if (existingId == productId) {
                        productRow = $(this);
                        return false; // Exit loop when product is found
                    }
                });

                // Determine which price to use based on user selection (partyUser or commissionUser)
                let discountPrice = discount; // Default to product's discount
                const partyId = $('#party-id').val(); // Get selected party customer ID
                const commissionId = $('#commission-id').val(); // Get selected commission user ID

                if (partyId) {
                    // Fetch discount from party_customer_products_price table for partyUser
                    $.get("{{ url('/party-customer-discount') }}/" + partyId + '/' + productId, function(
                        response) {
                        if (response.discount) {
                            discount = response.discount;
                        }

                        // Add product to the invoice with the correct discount price
                        if (!productRow) {
                            const row = `
                        <tr>
                            <td>#</td>
                            <td>${name}
                                <input type="hidden" name="items[${itemIndex}][product_id]" value="${productId}">
                            </td>
                            <td>
                                <input type="number" name="items[${itemIndex}][quantity]" class="form-control qty-input" value="${qty}" data-price="${sell_price}" data-discount="${discount}">
                                <input type="hidden" name="items[${itemIndex}][name]" class="form-control qty-input" value="${name}" >
                                <input type="hidden" name="items[${itemIndex}][sell_price]" class="form-control qty-input" value="${sell_price}">
                                <input type="hidden" name="items[${itemIndex}][mrp]" class="form-control qty-input" value="${mrp}">
                                </td>
                            <td>
                                 <div class="price-stack">
                                                        <span class="discount">₹${discount}</span>
                                                        <span class="mrp">₹${sell_price.toFixed(2)}</span>
                                                    </div>
                                </td>
                            <td class="item-total"><b>₹${(sell_price * qty).toFixed(2)}</b></td>
                            <td><img src="{{ asset('external/delete24dp1f1f1ffill0wght400grad0opsz2414471-7kar.svg') }}" alt="Delete" class="main-screen-delete24dp1f1f1ffill0wght400grad0opsz24110 btn btn-sm remove-item"></td>
                        </tr>
                    `;
                            $('#invoice-items-body').append(row);
                            itemIndex++;
                            updateTotals();
                        }
                    });
                } else if (commissionId) {
                    // If commissionUser is selected, use discount_price from product table
                    if (!productRow) {

                        const row = `
                    <tr>
                        <td>#</td>
                        <td>${name}
                            <input type="hidden" name="items[${itemIndex}][product_id]" value="${productId}">
                        </td>
                        <td>
                            <input type="number" name="items[${itemIndex}][quantity]" class="form-control qty-input" value="${qty}" data-price="${sell_price}" data-discount="${discount}">
                            <input type="hidden" name="items[${itemIndex}][name]" class="form-control qty-input" value="${name}" >
                                <input type="hidden" name="items[${itemIndex}][sell_price]" class="form-control qty-input" value="${sell_price}">
                                <input type="hidden" name="items[${itemIndex}][mrp]" class="form-control qty-input" value="${mrp}">
                            </td>
                        <td>
                             
                                                         <div class="price-stack">
                                                        <span class="discount">${discount.toFixed(2)}</span>
                                                        <span class="mrp">₹${sell_price.toFixed(2)}</span>
                                                    </div>
                            </td>
                        
                        <td class="item-total"><b>₹${(sell_price * qty).toFixed(2)}</b></td>
                        <td><img src="{{ asset('external/delete24dp1f1f1ffill0wght400grad0opsz2414471-7kar.svg') }}" alt="Delete" class="main-screen-delete24dp1f1f1ffill0wght400grad0opsz24110 btn btn-sm remove-item"></td>
                    </tr>
                `;
                        $('#invoice-items-body').append(row);
                        itemIndex++;
                        updateTotals();
                    }
                } else {
                    // If no user is selected, use product's discount price
                    if (!productRow) {
                        const row = `
                    <tr>
                        <td>#</td>
                        <td>${name}
                            <input type="hidden" name="items[${itemIndex}][product_id]" value="${productId}">
                        </td>
                        <td>
                            <input type="number" name="items[${itemIndex}][quantity]" class="form-control qty-input" value="${qty}" data-price="${sell_price}" data-discount="${discount}">
                            <input type="hidden" name="items[${itemIndex}][name]" class="form-control qty-input" value="${name}" >
                                <input type="hidden" name="items[${itemIndex}][sell_price]" class="form-control qty-input" value="${sell_price}">
                                <input type="hidden" name="items[${itemIndex}][mrp]" class="form-control qty-input" value="${mrp}">
                            </td>
                        <td>
                            
                                                         <div class="price-stack">
                                                        <span class="discount">${sell_price.toFixed(2)}</span>
                                                    </div>
                                                    </td>
                        <td class="item-total"><b>₹${(sell_price * qty).toFixed(2)}</b></td>
                        <td><img src="{{ asset('external/delete24dp1f1f1ffill0wght400grad0opsz2414471-7kar.svg') }}" alt="Delete" class="main-screen-delete24dp1f1f1ffill0wght400grad0opsz24110 btn btn-sm remove-item"></td>
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
                    $('#cash-amount').val(remainingAmount >= 0 ? remainingAmount : 0);
                } else {
                    let remainingAmount = grandTotal - entered;
                    $('#cash-amount').val(remainingAmount >= 0 ? remainingAmount : 0);
                }

                let errorMsg = '';

                if (entered > creditLimit) {
                    errorMsg = 'Credit Pay cannot exceed Credit Limit ₹' + creditLimit.toFixed(2);

                } else if (entered > grandTotal) {

                    Swal.fire("Credit Pay cannot exceed Invoice Total", "Credit Limit Exceeded",
                        "Credit pay (₹" +
                        creditLimit + ") cannot exceed credit limit (₹" +
                        creditLimit + ").", "error");
                    $(this).val(grandTotal.toFixed(2));
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
                const creditLimit = $("#left_credit_id").val();
                const creditPay = parseFloat($('input[name="creditpay"]').val()) || 0;

                if (creditPay > creditLimit) {
                    e.preventDefault();
                    Swal.fire("Credit Limit Exceeded", "Credit pay (₹" + creditPay +
                        ") cannot exceed credit limit (₹" +
                        creditLimit + ").", "error");
                }
            });

            // Listen for changes on partyUser select
            $('#party-id').on('change', function() {
                const partyUserId = $('#party-id').val();
                if (!partyUserId) {
                    $(".credit-section").hide();
                    return;
                }

                $('#commission-id').val(''); // Reset commission user selection
                $(".credit-section").show();
                if (partyUserId) {
                    updateProductDiscounts(partyUserId); // Update all discounts for the selected partyUser
                }

                if (partyUserId) {

                    $.get('{{ route('partyUserCredit', ':partyUserId') }}'.replace(':partyUserId',
                            partyUserId),
                        function(response) {

                            console.log('Party User Credit Response:', response);
                            $('#credit-limit').text(response.credit);
                            $('#left_credit').text(response.left_credit);
                            $('#creditpay-input').val(response.use_credit);
                            $('#left_credit_id').val(response.left_credit);
                        });

                }
            });

            // Listen for changes on commissionUser select
            $('#commission-id').on('change', function() {
                const commissionUserId = $('#commission-id').val();
                if (commissionUserId) {
                    updateProductDiscounts(); // Update all discounts for the selected commissionUser
                }
            });

            // Function to update product discounts when partyUser or commissionUser is selected
            function updateProductDiscounts(partyUserId = null) {
                $('#invoice-items-body tr').each(function() {
                    const productId = $(this).find('input[name*="[product_id]"]').val();
                    const qty = $(this).find('.qty-input').val();
                    const price = parseFloat($(this).find('.qty-input').data(
                        'price')); // Get the current price
                    const discount = parseFloat($(this).find('.qty-input').data(
                        'discount')); // Get the current price
                    const commissionUserId = $('#commission-id').val(); // Get the selected commission user

                    console.log('Updating discounts for product:', productId, 'Qty:', qty, 'Price:', price);

                    if (partyUserId) {
                        // Fetch the discount for the selected partyUser
                        $.get(`{{ url('/party-customer-discount') }}/${partyUserId}/${productId}`,
                            function(response) {
                                let discountPrice =
                                    price; // Default to product price if no discount is available
                                if (response.discount) {
                                    discountPrice = response.discount;
                                }

                                // Update the discount price and total in the row
                                $(this).find('.qty-input').data('discount', discountPrice);
                                $(this).find('.item-total').html('<b>₹' + (discountPrice * qty).toFixed(
                                        2) +
                                    '</b>');

                                // Update the price-stack to reflect the updated discount price
                                $(this).find('.price-stack .discount').text(`₹${discountPrice}`);
                                let mrpElement = $(this).find('.price-stack .mrp');

                                // If MRP is not set, append it
                                if (mrpElement.length === 0) {
                                    $(this).find('.price-stack').append(
                                        `<span class="mrp">₹${price.toFixed(2)}</span>`);
                                } else {
                                    mrpElement.text(`₹${price.toFixed(2)}`);
                                }

                                updateTotals(); // Recalculate totals
                            }.bind(this));
                    } else if (commissionUserId) {
                        // If commissionUser is selected, apply discount_price from product table
                        // const discountPrice = price; // Use the original price for commission user

                        $(this).find('.qty-input').data('discount', discount);

                        // Update the price-stack to reflect the price for commission user
                        $(this).find('.price-stack .discount').text(`₹${discount}`);
                        let mrpElement = $(this).find('.price-stack .mrp');

                        // If MRP is not set, append it
                        if (mrpElement.length === 0) {
                            $(this).find('.price-stack').append(
                                `<span class="mrp">₹${price.toFixed(2)}</span>`);
                        } else {
                            mrpElement.text(`₹${price.toFixed(2)}`);
                        }

                        updateTotals(); // Recalculate totals
                    }
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
        });
    </script>
@endsection
