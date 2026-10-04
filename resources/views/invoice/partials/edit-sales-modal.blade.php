<meta name="csrf-token" content="{{ csrf_token() }}">

<input type="hidden" id="loaded-invoice-no" value="{{ $invoice->invoice_number }}">
<input type="hidden" id="loaded-branch-name" value="{{ $branch_data->name }}">

<style>
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

    .qty-input,
    .item-price,
    .item-total-input {
        width: 100% !important;
        max-width: 95px;
        margin: 0 auto;
        text-align: center;
        border-radius: 6px;
        border: 1px solid #ced4da;
        height: 38px;
    }

    #items-table th:nth-child(1), #items-table td:nth-child(1) { width: 40px; text-align: center; }
    #items-table th:nth-child(2), #items-table td:nth-child(2) { width: auto; }
    #items-table th:nth-child(3), #items-table td:nth-child(3) { width: 105px; text-align: center; }
    #items-table th:nth-child(4), #items-table td:nth-child(4) { width: 170px; text-align: center; }
    #items-table th:nth-child(5), #items-table td:nth-child(5) { width: 115px; text-align: center; }
    #items-table th:nth-child(6), #items-table td:nth-child(6) { width: 70px; text-align: center; }

    #items-table td {
        vertical-align: middle;
        padding: 6px 8px;
    }

    .regular-price-label {
        font-weight: 700 !important;
        color: #dc3545 !important;
        font-size: 1.05em !important;
        margin-left: 6px !important;
        white-space: nowrap !important;
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
        padding: 10px 16px;
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
        padding: 6px 14px;
    }

    .order-detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        line-height: 1.3;
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
        color: #0d6efd;
    }

    .order-detail-item .value.danger {
        color: #dc3545;
    }

    .order-detail-item .value.success {
        color: #28a745;
    }

    .payment-method-group {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 6px;
    }

    .payment-method-group .form-check {
        margin: 0;
        padding-left: 20px;
    }

    .payment-method-group .form-check-label {
        font-size: 13px;
        font-weight: 500;
        color: #495057;
    }

    .credit-info {
        background: #f8f9fa;
        padding: 8px 12px;
        border-radius: 8px;
        margin: 8px 0;
    }

    .payment-input-group {
        background: #f8f9fa;
        padding: 8px 12px;
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

    .section-divider {
        border-top: 2px dashed #dee2e6;
        margin: 8px 0;
    }

    #payment-fields {
        display: flex;
        gap: 10px;
    }

    #payment-fields .payment-input-group {
        flex: 1;
    }

    .qty-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

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

    .qty-btn:hover {
        background: #e85d0f;
    }

    .qty-btn:active {
        transform: scale(0.95);
    }

    #product-table-card {
        margin-bottom: 10px !important;
    }
</style>

<form id="invoice-items-form" method="POST" action="{{ route('sales.invoice.updateItems', $invoice->id) }}">
    @csrf

    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col d-flex justify-content-start">
                    @if ($invoice->branch_id == 1 && $invoice->partyUser)
                        <span class="badge bg-success text-white">Party: {{ $invoice->partyUser->first_name }}</span>
                    @elseif (!empty($invoice->commission_user_id) && $invoice->commissionUser)
                        <span class="badge bg-success text-white">Commission: {{ $invoice->commissionUser->first_name }}</span>
                    @endif
                </div>

                <div class="col d-flex justify-content-end">
                    <label class="switch m-0 d-flex align-items-center">
                        @if(auth()->user()->role_id == 1)
                            @if ($invoice->admin_status != 'verify')
                                <span class="text-warning mr-2">Pending Sub Admin Verification</span>
                            @endif
                            <input type="checkbox" onchange="verifyInvoice({{ $invoice->id }}, this.checked, 'super_admin')"
                                {{ $invoice->super_admin_status == 'verify' ? 'checked' : '' }}>
                        @else
                            <input type="checkbox" onchange="verifyInvoice({{ $invoice->id }}, this.checked, 'admin')"
                                {{ $invoice->admin_status == 'verify' ? 'checked' : '' }}>
                        @endif
                        <span class="slider round ml-1"></span>
                        <span class="ms-2 ms-1 font-weight-bold">Verify Sale</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="type" value="{{ $type }}">
    <input type="hidden" name="verify" value="{{ $verify }}">

    <div class="card mb-3" id="product-table-card">
        <div class="card-body table-responsive">
            <table class="table table-bordered mb-0" id="items-table">
                <thead class="table-light">
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
                            $basePrice = $product ? $product->sell_price : ($item['sell_price'] ?? 0);
                            $discount = $product ? $product->discount_price : 0;
                            $mrp = $product ? $product->mrp : ($item['mrp'] ?? 0);

                            $partyDiscount = null;
                            if ($invoice->branch_id == 1) {
                                if ($invoice->party_user_id) {
                                    $partyDiscount = optional($partyPrices->where('product_id', $product->id ?? null)->first())->cust_discount_price;
                                } else {
                                    $partyDiscount = $discount;
                                }
                                $dis = $basePrice - ($partyDiscount ?? $basePrice);
                                $total_dis += $item['quantity'] * $dis;
                            } else {
                                if ($invoice->commission_user_id) {
                                    $partyDiscount = $discount;
                                } else {
                                    $partyDiscount = $basePrice;
                                }
                                $dis = $basePrice - ($discount ?? $basePrice);
                                $total_dis += $item['quantity'] * $dis;
                            }

                            $finalPrice = $partyDiscount ?? ($discount ?? $basePrice);
                            $lineBaseTotal = $basePrice * $item['quantity'];
                            $lineFinalTotal = $finalPrice * $item['quantity'];

                            $total += $lineBaseTotal;
                            $sub_total += $lineFinalTotal;
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                {{ $item['name'] }}
                                <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $item['product_id'] }}">
                                <input type="hidden" name="items[{{ $i }}][name]" value="{{ $item['name'] }}">
                                <input type="hidden" name="items[{{ $i }}][mrp]" value="{{ $mrp }}">
                                <input type="hidden" name="items[{{ $i }}][category]" value="{{ $product->category->name ?? '' }}">
                                <input type="hidden" name="items[{{ $i }}][subcategory]" value="{{ $product->subcategory->name ?? '' }}">
                                <input type="hidden" name="items[{{ $i }}][discount]" value="{{ $partyDiscount }}">
                                <input type="hidden" name="items[{{ $i }}][discount_price]" value="{{ $finalPrice }}">
                            </td>
                            <td>
                                <input type="number" name="items[{{ $i }}][quantity]" class="form-control qty-input"
                                    value="{{ $item['quantity'] }}" min="1"
                                    data-sell_price="{{ $basePrice }}"
                                    data-discount="{{ $finalPrice }}"
                                    data-mrp="{{ $mrp }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <input type="number" step="0.01" name="items[{{ $i }}][sell_price]" 
                                        class="form-control item-price" value="{{ $finalPrice }}">
                                    @if ($basePrice > $finalPrice)
                                        <span class="regular-price-label" style="text-decoration: line-through;">₹{{ number_format($basePrice, 0) }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="items[{{ $i }}][price]" 
                                    class="form-control item-total-input" value="{{ ceil($finalPrice * $item['quantity']) }}">
                            </td>
                            <td>
                                <img src="{{ asset('external/delete24dp1f1f1ffill0wght400grad0opsz2414471-7kar.svg') }}"
                                    alt="Delete" class="btn btn-sm remove-item p-0" style="cursor: pointer;">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row mb-3">
        <div class="offset-lg-8 col-lg-4">
            <div class="order-details-card">
                <div class="order-details-header">
                    <h5><i class="fas fa-shopping-cart mr-1"></i> Invoice Details</h5>
                </div>
                <div class="order-details-body">
                    <input type="hidden" id="total_discount" name="total_discount" value="{{ $total_dis }}">
                    <input type="hidden" id="gr_total" name="total" value="{{ $sub_total }}">
                    <input type="hidden" id="sub_total" name="sub_total" value="{{ $total }}">
                    <input type="hidden" id="ori_sub_total" name="ori_sub_total" value="{{ $sub_total }}">
                    <input type="hidden" id="left_credit_id" value="{{ $invoice->partyUser->left_credit ?? 0 }}">

                    <div class="order-detail-item">
                        <span class="label">Sub Total</span>
                        <span class="value highlight" id="total">₹{{ ceil($total) }}</span>
                    </div>

                    <div class="order-detail-item">
                        <span class="label">
                            @if ($invoice->branch_id == 1 && $invoice->partyUser)
                                Party Deduction
                            @else
                                Commission Deduction
                            @endif
                        </span>
                        <span class="value danger" id="discount-total">₹{{ number_format($total_dis, 2) }}</span>
                    </div>

                    @if ($invoice->branch_id == 1 && $invoice->partyUser)
                        <div class="section-divider"></div>
                        <div class="credit-info">
                            <div class="d-flex justify-content-between">
                                <span class="label">Credit Limit</span>
                                <span class="value" id="credit-limit">₹{{ number_format($invoice->partyUser->credit_points, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="label">Left Limit</span>
                                <span class="value success" id="left_credit">₹{{ number_format($invoice->partyUser->left_credit, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="label">Total Used Credit</span>
                                <span class="value danger" id="total-used-credit">₹{{ number_format($invoice->partyUser->use_credit ?? 0, 2) }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="section-divider"></div>
                    <div>
                        <span class="label d-block font-weight-bold" style="color: #495057;">Payment Method</span>
                        <div class="payment-method-group">
                            <div class="form-check">
                                <input type="radio" id="cash-option" name="payment_method" value="cash"
                                    class="form-check-input" @if ($invoice->payment_mode == 'cash') checked @endif>
                                <label class="form-check-label" for="cash-option">Cash</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" id="upi-option" name="payment_method" value="online"
                                    class="form-check-input" @if ($invoice->payment_mode == 'online') checked @endif>
                                <label class="form-check-label" for="upi-option">UPI</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" id="cash-upi-option" name="payment_method" value="cashupi"
                                    class="form-check-input" @if ($invoice->payment_mode == 'cashupi') checked @endif>
                                <label class="form-check-label" for="cash-upi-option">Cash + UPI</label>
                            </div>
                            <div class="form-check">
                                <input type="radio" id="credit-option" name="payment_method" value="credit"
                                    class="form-check-input" @if ($invoice->payment_mode == 'credit') checked @endif>
                                <label class="form-check-label" for="credit-option">Credit</label>
                            </div>
                        </div>
                    </div>

                    <div id="payment-fields">
                        <div id="cash-field" class="payment-input-group" style="{{ $invoice->payment_mode == 'credit' ? 'display: none;' : '' }}">
                            <label class="form-label">Cash Amount</label>
                            <input type="number" id="cash-amount" class="form-control" min="0" step="1"
                                name="cash_amount" value="{{ $invoice->cash_amount }}" readonly>
                        </div>

                        <div id="upi-field" class="payment-input-group" style="{{ in_array($invoice->payment_mode, ['online', 'cashupi']) ? '' : 'display: none;' }}">
                            <label class="form-label">UPI Amount</label>
                            <input type="number" id="upi-amount" class="form-control" name="upi_amount"
                                value="{{ $invoice->upi_amount ?? 0 }}" min="0" step="1" readonly>
                        </div>

                        <div id="credit-field" class="payment-input-group" style="{{ $invoice->payment_mode == 'credit' ? '' : 'display: none;' }}">
                            <label class="form-label">Credit Used</label>
                            <input type="number" name="creditpay" id="creditpay-input" min="0" step="1"
                                class="form-control" value="{{ number_format($invoice->creditpay, 0, '', '') }}">
                            <small id="creditpay-error" class="text-danger d-block" style="display:none;"></small>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-3 total-summary mb-3">
        <div>
            <button type="submit" class="btn btn-success px-4 font-weight-bold">Save Invoice Items</button>
        </div>
    </div>
</form>

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

        $('#invoice-items-body tr').each(function() {
            const $row = $(this);
            const qty = parseFloat($row.find('.qty-input').val()) || 0;
            const price = parseFloat($row.find('.item-price').val()) || 0;
            const sellPrice = parseFloat($row.find('.qty-input').data('sell_price')) || price;

            const rowTotal = Math.ceil(qty * price);
            $row.find('.item-total-input').val(rowTotal);

            const disAmt = (sellPrice - price) * qty;

            totalSellPrice += (sellPrice * qty);
            discountTotal += disAmt;
            grandTotal += rowTotal;
        });

        grandTotal = Math.ceil(grandTotal);
        totalSellPrice = Math.ceil(totalSellPrice);

        $('#total').text('₹' + totalSellPrice);
        $('#discount-total').text('₹' + discountTotal.toFixed(2));

        $('#total_discount').val(discountTotal.toFixed(2));
        $('#gr_total').val(grandTotal);
        $('#sub_total').val(totalSellPrice);

        updatePaymentFields();
    }

    $(document).on('input', '.qty-input', function() {
        const $input = $(this);
        let qty = parseInt($input.val()) || 1;
        if (qty < 1) qty = 1;
        updateQtyWithCheck($input, qty);
    });

    $(document).on('blur', '.item-price', function() {
        const $row = $(this).closest('tr');
        const qty = parseFloat($row.find('.qty-input').val()) || 0;
        const price = parseFloat($(this).val()) || 0;
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

    $(document).on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
        updateTotals();
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
            const price = parseFloat($row.find('.item-price').val()) || 0;
            $row.find('.item-total-input').val(Math.ceil(price * newQty));
            updateTotals();
        });
    }

    $('input[name="payment_method"]').on('change', function() {
        const selected = $(this).val();
        if (selected !== 'credit') {
            $('#creditpay-input').val(0).prop('readonly', false);
        } else {
            let total = parseFloat($('#gr_total').val()) || 0;
            $('#creditpay-input').val(Math.ceil(total)).prop('readonly', true);
        }
        updatePaymentFields();
    });

    $('#cash-amount').on('input', function() {
        let cash = parseFloat($(this).val()) || 0;
        let total = parseFloat($('#gr_total').val()) || 0;
        let credit = parseFloat($('#creditpay-input').val()) || 0;
        let payable = total - credit;

        if ($('#cash-upi-option').is(':checked')) {
            let upi = payable - cash;
            $('#upi-amount').val(upi >= 0 ? Math.ceil(upi) : 0);
        }
    });

    $('#upi-amount').on('input', function() {
        let upi = parseFloat($(this).val()) || 0;
        let total = parseFloat($('#gr_total').val()) || 0;
        let credit = parseFloat($('#creditpay-input').val()) || 0;
        let payable = total - credit;

        if ($('#cash-upi-option').is(':checked')) {
            let cash = payable - upi;
            $('#cash-amount').val(cash >= 0 ? Math.ceil(cash) : 0);
        }
    });

    $('#creditpay-input').on('input', function() {
        const entered = parseFloat($(this).val()) || 0;
        const errorEl = $('#creditpay-error');
        const userCreditLimit = parseFloat($('#left_credit_id').val()) || creditLimit;
        const total = parseFloat($('#gr_total').val()) || 0;

        let errorMsg = '';
        if (entered > userCreditLimit) {
            errorMsg = 'Credit Pay cannot exceed Credit Limit ₹' + userCreditLimit;
        } else if (entered > total) {
            Swal.fire("Credit Limit Exceeded", "Credit pay cannot exceed total invoice amount.", "error");
            $(this).val(total);
            return false;
        }

        if (errorMsg) {
            errorEl.text(errorMsg).show();
        } else {
            errorEl.hide();
        }
        updatePaymentFields();
    });

    function updatePaymentFields() {
        const method = $('input[name="payment_method"]:checked').val();
        let total = parseFloat($('#gr_total').val()) || 0;
        let creditPay = parseFloat($('#creditpay-input').val()) || 0;
        let payable = Math.max(0, Math.ceil(total - creditPay));

        if (method === 'cash') {
            $('#cash-field').show();
            $('#upi-field').hide();
            $('#credit-field').hide();
            $('#cash-amount').val(payable).prop('readonly', true);
            $('#upi-amount').val('').prop('readonly', true);
        } else if (method === 'online') {
            $('#cash-field').hide();
            $('#upi-field').show();
            $('#credit-field').hide();
            $('#upi-amount').val(payable).prop('readonly', true);
            $('#cash-amount').val('').prop('readonly', true);
        } else if (method === 'cashupi') {
            $('#cash-field').show();
            $('#upi-field').show();
            $('#credit-field').hide();
            let cash = parseFloat($('#cash-amount').val()) || payable;
            cash = Math.min(cash, payable);
            $('#cash-amount').val(cash).prop('readonly', false);
            $('#upi-amount').val(payable - cash).prop('readonly', false);
        } else if (method === 'credit') {
            $('#cash-field').hide();
            $('#upi-field').hide();
            $('#credit-field').show();
            $('#creditpay-input').val(Math.ceil(total)).prop('readonly', true);
        }
    }

    $('form').on('submit', function(e) {
        const creditPay = parseFloat($('input[name="creditpay"]').val()) || 0;
        const userCreditLimit = parseFloat($('#left_credit_id').val()) || creditLimit;

        if (creditPay > userCreditLimit) {
            e.preventDefault();
            Swal.fire("Credit Limit Exceeded", "Credit pay (₹" + creditPay + ") cannot exceed credit limit (₹" + userCreditLimit + ").", "error");
        }
    });

    $(document).on('click', '.qty-btn.plus', function() {
        const $input =$(this).siblings('.qty-input');
        let qty = parseInt($input.val()) || 0;
        qty++;
        updateQtyWithCheck($input, qty);
    });

    $(document).on('click', '.qty-btn.minus', function() {
        const $input =$(this).siblings('.qty-input');
        let qty = parseInt($input.val()) || 0;
        if (qty > 1) {
            qty--;
            updateQtyWithCheck($input, qty);
        }
    });

    $(document).on('click', '.qty-plus', function() {
        const $row =$(this).closest('tr');
        const $input =$row.find('.qty-input');
        let qty = parseInt($input.val()) || 0;
        qty++;
        updateQtyWithCheck($input, qty);
    });

    $(document).on('click', '.qty-minus', function() {
        const $row =$(this).closest('tr');
        const $input =$row.find('.qty-input');
        let qty = parseInt($input.val()) || 0;
        if (qty > 1) {
            qty--;
            updateQtyWithCheck($input, qty);
        }
    });

    $('#add-product-btn').on('click', function() {
        const selected = $('#new-product-id option:selected');
        const productId = selected.val();
        const name = selected.data('name');
        const mrp = parseFloat(selected.data('mrp'));
        const discount = parseFloat(selected.data('discount'));
        const sell_price = parseFloat(selected.data('sell_price'));
        const qty = parseInt($('#new-product-qty').val()) || 1;

        if (!productId || !qty) return alert('Select product and quantity.');

        let productRow = null;
        $('#invoice-items-body tr').each(function() {
            const existingId = $(this).find('input[name*="[product_id]"]').val();
            if (existingId == productId) {
                productRow = $(this);
                return false;
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
                const total = usePrice * qty;

                if (productRow) {
                    const qtyInput = productRow.find('.qty-input');
                    const currentQty = parseInt(qtyInput.val()) || 0;
                    qtyInput.val(currentQty + qty);
                    updateTotals();
                } else {
                    const regularStrike = (sell_price > usePrice) ? `<span class="regular-price-label" style="text-decoration: line-through;">₹${sell_price.toFixed(0)}</span>` : '';
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
                            <input type="number" name="items[${itemIndex}][quantity]" class="form-control qty-input"
                                value="${qty}" min="1" data-sell_price="${sell_price}" data-discount="${discount}" data-mrp="${mrp}">
                        </td>
                        <td>
                            <div class="d-flex align-items-center justify-content-center">
                                <input type="number" step="0.01" name="items[${itemIndex}][sell_price]" class="form-control item-price" value="${usePrice}">
                                ${regularStrike}
                            </div>
                        </td>
                        <td>
                            <input type="number" step="0.01" name="items[${itemIndex}][price]" class="form-control item-total-input" value="${Math.ceil(total)}">
                        </td>
                        <td>
                            <img src="{{ asset('external/delete24dp1f1f1ffill0wght400grad0opsz2414471-7kar.svg') }}" alt="Delete" class="btn btn-sm remove-item p-0" style="cursor: pointer;">
                        </td>
                    </tr>`;
                    $('#invoice-items-body').append(row);
                    itemIndex++;
                    updateTotals();
                }

                $('#new-product-id').val('');
                $('#new-product-qty').val('');
            }
        });
    });

    function verifyInvoice(invoice_id, isChecked, role) {
        let status = isChecked ? 'verify' : 'unverify';

        Swal.fire({
            title: "Are you sure you want to " + status + " this invoice?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "{{ route('shift.verify.invoice') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        invoice_id: invoice_id,
                        status: status, 
                        role: role
                    },
                    success: function(response) {
                        Swal.fire("Done!", "Invoice has been Verified", "success");
                    }
                });
            }
        });
    }
</script>