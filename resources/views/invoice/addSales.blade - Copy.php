@extends('layouts.backend.layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- jQuery must be loaded first -->
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
                    <h4>Transaction Invoice Details - #{{ $branch_data->name }}</h4>
                    <a href="{{ route('sales.sales.list') }}" class="btn btn-secondary">Back</a>
                </div>

                <form id="invoice-items-form" method="POST" action="">
                    @csrf
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-5">
                                    <select id="new-product-id" class="form-control">
                                        <option value="">Select Product</option>
                                        @foreach ($allProducts as $product)
                                            <option value="{{ $product->id }}" data-name="{{ $product->name }}"
                                                data-mrp="{{ $product->mrp }}" data-sell_price="{{ $product->sell_price }}">
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" min="1" id="new-product-qty" class="form-control"
                                        placeholder="Qty">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary" id="add-product-btn">Add Item</button>
                                </div>
                                <div class="col-md-2">
                                    @if ($branch_data->branch_id == 1)
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

                    <div class="d-flex justify-content-end mt-3 total-summary">
                        <div>
                            <h5>Total: ₹<span id="total">0.00</span></h5>
                            <h5>Discount: ₹<span id="discount-total">0.00</span></h5>
                            <input type="hidden" id="total_discount" name="total_discount" value="0">
                            <input type="hidden" id="gr_total" name="total" value="0">
                            <div class="credit-section">
                                <p class="mb-1">
                                    <span class="fw-bold text-dark">Credit Limit:</span>
                                    ₹<span id="credit-limit">0.00</span>
                                </p>
                                <p class="mb-0">
                                    <span class="fw-bold text-dark">Credit Used (Invoice):</span>
                                    ₹<input type="number" name="creditpay" id="creditpay-input" min="0"
                                        step="0.01" class="form-control d-inline-block"
                                        style="width: 120px; display: inline;">
                                    <small id="creditpay-error" class="text-danger d-block" style="display:none;"></small>
                                </p>
                            </div>
                            <h5>Sub Total: ₹<span id="grand-total">0.00</span></h5>

                            <button type="submit" class="btn btn-success">Save Invoice Items</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

<script>
    // Ensure jQuery is loaded before using '$' symbol
  $(document).ready(function() {
    let itemIndex = 0;
    const storeId = {{ $branch_data->id }};
    let creditLimit = 0;

    // Update the totals dynamically
    function updateTotals() {
        let grandTotal = 0;
        let totalSellPrice = 0;
        let discountTotal = 0;

        $('#invoice-items-body tr').each(function() {
            const qty = parseFloat($(this).find('.qty-input').val()) || 0;
            const price = parseFloat($(this).find('.qty-input').data('price')) || 0;
            const discount = parseFloat($(this).find('.qty-input').data('discount')) || 0;

            totalSellPrice += price * qty;
            const rowTotal = qty * price;
            let dis = qty * (price - discount);

            $(this).find('.item-total').html('<b>₹' + rowTotal.toFixed(2) + '</b>');
            grandTotal += rowTotal - dis;
            discountTotal += dis;
        });

        $('#total').text(totalSellPrice.toFixed(2));
        $('#grand-total').text(grandTotal.toFixed(2));
        $('#discount-total').text(discountTotal.toFixed(2));
        $('#total_discount').val(discountTotal.toFixed(2));
        $('#gr_total').val(totalSellPrice.toFixed(2));
    }

    // Add product to the invoice
    $('#add-product-btn').on('click', function() {
        const selected = $('#new-product-id option:selected'); // Get selected product
        const productId = selected.val(); // Get product ID
        const name = selected.data('name'); // Get product name
        const mrp = parseFloat(selected.data('mrp')); // Get product MRP
        const discount = parseFloat(selected.data('discount')); // Get product discount
        const sell_price = parseFloat(selected.data('sell_price')); // Get product sell price
        const qty = parseInt($('#new-product-qty').val()) || 1; // Get quantity (default to 1)

        // Check if a product and quantity are selected
        if (!productId || !qty) {
            alert('Please select a product and quantity.');
            return; // Exit if no product or quantity is selected
        }

        // Check if product already exists in the invoice table
        let productRow = null;
        $('#invoice-items-body tr').each(function() {
            const existingId = $(this).find('input[name*="[product_id]"]').val();
            if (existingId == productId) {
                productRow = $(this); // Found existing row for the product
                return false; // Exit loop when product is found
            }
        });

        // Fetch discount based on selected party customer
        let discountPrice = discount; // Default to product's discount
        const partyId = $('#party-id').val(); // Get selected party customer ID

        if (partyId) {
            // Send an AJAX request to fetch the custom discount for the selected party customer
            const url = "{{ url('/party-customer-discount') }}/" + partyId;
            $.get(url, function(response) {
                if (response.discount) {
                    discountPrice = response
                    .discount; // Update discount price if a custom discount is found
                    updateProductDiscount(productRow, discountPrice); // Update product row if it exists
                }
            });
        }

        // If the product does not already exist, add it as a new row
        if (!productRow) {
            const row = `
            <tr>
                <td>#</td>
                <td>${name}
                    <input type="hidden" name="items[${itemIndex}][product_id]" value="${productId}">
                    <input type="hidden" name="items[${itemIndex}][name]" value="${name}">
                    <input type="hidden" name="items[${itemIndex}][mrp]" value="${sell_price}">
                    <input type="hidden" name="items[${itemIndex}][discount]" value="${discountPrice}">
                </td>
                <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control qty-input" value="${qty}" data-price="${sell_price}" data-discount="${discountPrice}"></td>
                <td>₹${sell_price.toFixed(2)}</td>
                <td class="item-total"><b>₹${(sell_price * qty).toFixed(2)}</b></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-item"><i class="fa fa-trash"></i></button></td>
            </tr>
        `;
            $('#invoice-items-body').append(row); // Add the new row to the table
            itemIndex++; // Increment item index
            updateTotals(); // Recalculate totals
        }

        // Clear the input fields
        $('#new-product-id').val('');
        $('#new-product-qty').val('');
    });

    // Update the product discount when party user is selected
    function updateProductDiscount(productRow, discountPrice) {
        if (productRow) {
            const qtyInput = productRow.find('.qty-input'); // Get the quantity input field in the row
            const price = parseFloat(qtyInput.data('price')); // Get product price
            qtyInput.data('discount', discountPrice); // Update discount data attribute
            const total = price * qtyInput.val(); // Calculate total for this row
            productRow.find('.item-total').html('<b>₹' + total.toFixed(2) + '</b>'); // Update the total column
            updateTotals(); // Recalculate totals after updating the discount
        }
    }

    // Update the totals (Total, Sub Total, Discount) dynamically
    function updateTotals() {
        let grandTotal = 0;
        let totalSellPrice = 0;
        let discountTotal = 0;

        // Loop through each product row in the table to calculate the totals
        $('#invoice-items-body tr').each(function() {
            const qty = parseFloat($(this).find('.qty-input').val()) || 0;
            const price = parseFloat($(this).find('.qty-input').data('price')) || 0;
            const discount = parseFloat($(this).find('.qty-input').data('discount')) || 0;

            totalSellPrice += price * qty; // Calculate total sell price
            const rowTotal = qty * price; // Calculate row total
            let dis = qty * (price - discount); // Calculate total discount for this row

            $(this).find('.item-total').html('<b>₹' + rowTotal.toFixed(2) + '</b>'); // Update row total
            grandTotal += rowTotal - dis; // Add row total minus discount to grand total
            discountTotal += dis; // Add row discount to discount total
        });

        // Update the displayed totals
        $('#total').text(totalSellPrice.toFixed(2));
        $('#grand-total').text(grandTotal.toFixed(2));
        $('#discount-total').text(discountTotal.toFixed(2));
        $('#total_discount').val(discountTotal.toFixed(2));
        $('#gr_total').val(totalSellPrice.toFixed(2));
    }


    // Remove product from invoice
    $(document).on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
        updateTotals();
    });

    // Credit pay validation
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
});
</script>
