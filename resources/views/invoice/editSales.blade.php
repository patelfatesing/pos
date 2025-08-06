@extends('layouts.backend.layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
    </style>

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="d-flex justify-content-between mb-3">
                    <h4>Transaction Invoice Details - #{{ $invoice->invoice_number }}</h4>
                    <a href="{{ route('sales.sales.list') }}" class="btn btn-secondary">Back</a>
                </div>

                <form id="invoice-items-form" method="POST" action="{{ route('sales.invoice.updateItems', $invoice->id) }}">

                    @csrf
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-5">
                                    <select id="new-product-id" class="form-control">
                                        <option value="">Select Product</option>
                                        @foreach ($allProducts as $product)
                                            @php
                                                $partyDiscount = null;
                                                if ($invoice->branch_id == 1 && $invoice->party_user_id) {
                                                    $partyDiscount = optional(
                                                        $partyPrices->where('product_id', $product->id)->first(),
                                                    )->cust_discount_price;
                                                }
                                                $finalPrice =
                                                    $partyDiscount ??
                                                    ($product->discount_price ?? $product->sell_price);
                                            @endphp
                                            <option value="{{ $product->id }}" data-name="{{ $product->name }}"
                                                data-mrp="{{ $product->mrp }}" data-sell_price="{{ $product->sell_price }}"
                                                data-discount="{{ $partyDiscount }}">
                                                {{ $product->name }} - ₹{{ number_format($finalPrice, 2) }}
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
                                    @if ($invoice->branch_id == 1 && $invoice->partyUser)
                                        <span class="badge bg-info text-dark">Party:
                                            {{ $invoice->partyUser->first_name }}</span>
                                    @elseif (!empty($invoice->commission_user_id) && $invoice->commissionUser)
                                        <span class="badge bg-warning text-dark">Commission:
                                            {{ $invoice->commissionUser->first_name }}</span>
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
                                <tbody id="invoice-items-body">
                                    @php
                                        $total = 0;
                                        $sub_total = 0;
                                        $total_dis = 0;
                                    @endphp
                                    @foreach ($invoice->items as $i => $item)
                                        @php
                                            $product = $allProducts->where('id', $item['product_id'])->first();
                                            // dd($product);
                                            // $basePrice = $product->mrp;

                                            $basePrice = $product->sell_price;
                                            $discount = $product->discount_price;

                                            $partyDiscount = null;
                                            if ($invoice->branch_id == 1 && $invoice->party_user_id) {
                                                $partyDiscount = optional(
                                                    $partyPrices->where('product_id', $product->id)->first(),
                                                )->cust_discount_price;

                                                $dis = $product->sell_price - $partyDiscount;

                                                $total_dis += $item['quantity'] * $dis;
                                            } else {
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
                                                <input type="hidden" name="items[{{ $i }}][discount]"
                                                    value="{{ $partyDiscount }}">
                                            </td>
                                            <td>
                                                <input type="number" name="items[{{ $i }}][quantity]"
                                                    class="form-control qty-input" value="{{ $item['quantity'] }}"
                                                    data-price="{{ $basePrice }}" data-sell_price="{{ $finalPrice }}"
                                                    data-discount="{{ $partyDiscount }}">
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
                                            <td><button type="button" class="btn btn-danger btn-sm remove-item"><i
                                                        class="fa fa-trash"></i></button></td>
                                        </tr>
                                    @endforeach
                                    <?php
                                    
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
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
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let itemIndex = {{ count($invoice->items) }};
        const storeId = {{ $invoice->branch_id }};
        const creditLimit = {{ $invoice->partyUser->credit_points ?? 0 }};

        function updateTotals() {
            let grandTotal = 0;
            let totalSellPrice = 0;
            let discountTotal = 0;
            $('#invoice-items-body tr').each(function() {
                const qty = parseFloat($(this).find('.qty-input').val()) || 0;
                const price = parseFloat($(this).find('.qty-input').data('price')) || 0;
                const discount = parseFloat($(this).find('.qty-input').data('discount')) || 0;
                const sell_price = parseFloat($(this).find('.qty-input').data('sell_price')) || 0;
                
                totalSellPrice += price * qty;
                const rowTotal = qty * price;
                let dis = qty * (price - discount);

                $(this).find('.item-total').html('<b>₹' + rowTotal.toFixed(2) + '</b>');
                grandTotal += rowTotal -dis;
                discountTotal += dis;
            });
            $('#total').text(totalSellPrice.toFixed(2));
            $('#grand-total').text(grandTotal.toFixed(2));
            $('#discount-total').text(discountTotal.toFixed(2));
            $('#total_discount').val(discountTotal.toFixed(2));
            $('#gr_total').val(totalSellPrice.toFixed(2));
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
                        </td>
                        <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control qty-input" value="${qty}" data-price="${sell_price}" data-discount="${discount}"></td>
                        <td>${priceDisplay}</td>
                        <td class="item-total"><b>₹${total.toFixed(2)}</b></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-item"><i class="fa fa-trash"></i></button></td>
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
    </script>
@endsection
