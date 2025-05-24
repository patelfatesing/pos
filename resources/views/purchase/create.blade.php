@extends('layouts.backend.layouts')

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
                                    <h4 class="card-title">Delivery Invoice</h4>
                                </div>
                                <div>
                                    <a href="{{ route('purchase.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="card">
                                    <div class="card-body">
                                        <form action="{{ route('purchase.store') }}" method="POST"
                                            enctype="multipart/form-data">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="bill_no" class="form-label">Bill No</label>
                                                    <input type="text" class="form-control" id="bill_no" name="bill_no"
                                                        required>
                                                    @error('bill_no')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-4">
                                                    <label for="date" class="form-label">Date</label>
                                                    <input type="date" class="form-control" id="date" name="date"
                                                        required>
                                                    @error('date')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="vendor_id">Vendor Name</label>
                                                        <select name="vendor_id" id="vendor_id" class="form-control">
                                                            <option value="">-- Select Party --</option>
                                                            @foreach ($vendors as $vendor)
                                                                <option value="{{ $vendor->id }}">{{ $vendor->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('vendor_id')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="parchase_ledger">Purchase Ledger</label>
                                                        <select name="parchase_ledger" id="parchase_ledger"
                                                            class="form-control">
                                                            <option value="">-- Select Ledger --</option>
                                                            @foreach ($vendors as $vendor)
                                                                <option value="{{ $vendor->id }}">{{ $vendor->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('parchase_ledger')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="product_select">Product</label>
                                                        <select name="product_select" id="product_select"
                                                            class="form-control">
                                                            <option value="">-- Select Product --</option>
                                                            @foreach ($products as $product)
                                                                <option value="{{ $product['id'] }}">
                                                                    {{ $product['name'] }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('product_select')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <hr />
                                            <div class="table-responsive mb-3">
                                                <table class="table table-bordered" id="product_table">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Sr No</th>
                                                            <th>Brand</th>
                                                            <th>Batch</th>
                                                            <th>MFG Date</th>
                                                            <th>MRP</th>
                                                            <th>Qty</th>
                                                            <th>Rate</th>
                                                            <th>Amount</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="productBody">
                                                        @if (old('products'))
                                                            @foreach (old('products') as $i => $product)
                                                                <tr>
                                                                    <td>{{ $i + 1 }}</td>
                                                                    <td>
                                                                        <input type="hidden"
                                                                            name="products[{{ $i }}][product_id]"
                                                                            value="{{ $product['product_id'] }}">
                                                                        <input type="text" class="form-control"
                                                                            name="products[{{ $i }}][brand_name]"
                                                                            value="{{ $product['brand_name'] }}" readonly>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" class="form-control"
                                                                            name="products[{{ $i }}][batch]"
                                                                            value="{{ $product['batch'] }}">
                                                                        @error("products.$i.batch")
                                                                            <span
                                                                                class="text-danger">{{ $message }}</span>
                                                                        @enderror
                                                                    </td>
                                                                    <td>
                                                                        <input type="date" class="form-control"
                                                                            name="products[{{ $i }}][mfg_date]"
                                                                            value="{{ $product['mfg_date'] }}">
                                                                        @error("products.$i.mfg_date")
                                                                            <span
                                                                                class="text-danger">{{ $message }}</span>
                                                                        @enderror
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" class="form-control mrp"
                                                                            step="0.01"
                                                                            name="products[{{ $i }}][mrp]"
                                                                            value="{{ $product['mrp'] }}">
                                                                        @error("products.$i.mrp")
                                                                            <span
                                                                                class="text-danger">{{ $message }}</span>
                                                                        @enderror
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" class="form-control qnt"
                                                                            name="products[{{ $i }}][qnt]"
                                                                            value="{{ $product['qnt'] }}">
                                                                        @error("products.$i.qnt")
                                                                            <span
                                                                                class="text-danger">{{ $message }}</span>
                                                                        @enderror
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" class="form-control rate"
                                                                            step="0.01"
                                                                            name="products[{{ $i }}][rate]"
                                                                            value="{{ $product['rate'] }}">
                                                                        @error("products.$i.rate")
                                                                            <span
                                                                                class="text-danger">{{ $message }}</span>
                                                                        @enderror
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" class="form-control amount"
                                                                            step="0.01"
                                                                            name="products[{{ $i }}][amount]"
                                                                            value="{{ $product['amount'] }}" readonly>
                                                                        @error("products.$i.amount")
                                                                            <span
                                                                                class="text-danger">{{ $message }}</span>
                                                                        @enderror
                                                                    </td>
                                                                    <td><button type="button"
                                                                            class="btn btn-danger remove">Remove</button>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                    </tbody>

                                                </table>
                                            </div>
                                            <input type="hidden" name="total" class="total_val" value="" />
                                            <div class="table-responsive mb-1">
                                                <table class="table table-bordered">
                                                    <tbody class="">
                                                        <tr>
                                                            <td colspan="8">Total</td>
                                                            <input hidden class="total_amt">
                                                            <td id="total"></td>
                                                        </tr>
                                                    </tbody>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                            <hr />
                                            <div class="row mt-4 mb-3">
                                                <div class="offset-lg-8 col-lg-4">
                                                    <div class="or-detail rounded">
                                                        <div class="p-3">
                                                            <h5 class="mb-3">Billing Details</h5>

                                                            <!-- Vendor 1 Fields -->
                                                            <div id="vendor-1-fields" class="vendor-fields d-none">
                                                                <div class="form-group">
                                                                    <label>EXCISE FEE</label>
                                                                    <input type="tel" class="form-control"
                                                                        name="excise_fee" id="excise_fee" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>COMPOSITION VAT</label>
                                                                    <input type="tel" class="form-control"
                                                                        name="composition_vat" id="composition_vat" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>SURCHARGE ON CA</label>
                                                                    <input type="tel" class="form-control"
                                                                        name="surcharge_on_ca" id="surcharge_on_ca" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>AED TO BE PAID</label>
                                                                    <input type="tel" class="form-control"
                                                                        name="aed_to_be_paid" id="aed_to_be_paid" />
                                                                </div>
                                                            </div>

                                                            <!-- Vendor 2 and Others Fields -->
                                                            <div id="vendor-2-fields" class="vendor-fields d-none">
                                                                <div class="form-group">
                                                                    <label>VAT</label>
                                                                    <input type="tel" id="vat"
                                                                        class="form-control" name="vat" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>SURCHARGE ON VAT</label>
                                                                    <input type="tel" id="surcharge_on_vat"
                                                                        class="form-control" name="surcharge_on_vat" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>BLF</label>
                                                                    <input type="tel" id="blf"
                                                                        class="form-control" name="blf" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Permit Fee</label>
                                                                    <input type="tel" class="form-control"
                                                                        name="permit_fee" id="permit_fee" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>RSGSM Purchase</label>
                                                                    <input type="tel" class="form-control"
                                                                        name="rsgsm_purchase" id="rsgsm_purchase" />
                                                                </div>
                                                            </div>
                                                            <div class="vendor-common">
                                                                <div class="form-group">
                                                                    <label>TCS</label>
                                                                    <input type="tel" id="tcs"
                                                                        class="form-control" name="tcs" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="vendor-others-fields" class="vendor-fields d-none">
                                                            <div class="ttl-amt py-2 px-3 d-flex justify-content-between align-items-center border border-danger"
                                                                style="background-color: #fdf1f7;">
                                                                <div>
                                                                    <strong class="d-block">CASH PURCHASE</strong>
                                                                    <div class="d-flex align-items-center">
                                                                        <label class="mr-1 mb-0">(-)</label>
                                                                        <input type="number"
                                                                            class="form-control form-control-sm pur_dis"
                                                                            placeholder="%" name="case_purchase_per"
                                                                            style="width: 80px;" min="0"
                                                                            max="100">
                                                                        <span class="ml-1">%</span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-right d-flex align-items-center">
                                                                    <label class="mr-1 mb-0">(-)</label>
                                                                    <input type="number" name="case_purchase_amt"
                                                                        class="form-control form-control-sm pur_amt text-danger font-weight-bold"
                                                                        placeholder="Amount" style="width: 120px;"
                                                                        min="0">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Total after deduction -->


                                                        <div
                                                            class="ttl-amt py-2 px-3 d-flex justify-content-between align-items-center border-top">
                                                            <h6>Total Amount</h6>
                                                            <div>
                                                                <input type="hidden" name="total_amount"
                                                                    class="total_amount" value="" />
                                                                <h3 class="text-primary font-weight-700"
                                                                    id="total_amount"></h3>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                            <button type="submit" class="btn btn-primary mr-2">Add Purchase
                                                Order</button>
                                            <button type="reset" class="btn btn-danger">Reset</button>
                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Page end -->
            </div>
        </div>
    </div>


    <!-- Wrapper End -->
@endsection

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {

        // Hide all dynamic fields initially
        $('.vendor-group').hide();
        $('.vendor-total').hide();
        $('.vendor-common').hide();

        $('#vendor_id').change(function() {
            var vendorId = $(this).val();

            // Hide all groups and show only the relevant one
            $('.vendor-group').hide();
            $('.vendor-total').hide();
            $('.vendor-common').hide();

            if (vendorId == '1') {
                $('.vendor-1').show();
                $('.vendor-common').show();
                $('.vendor-total').show();
            } else if (vendorId == '2') {
                $('.vendor-2').show();
                $('.vendor-common').show();
                $('.vendor-total').show();
            } else {
                $('.vendor-others').show();
                $('.vendor-total').show();
            }
        });

        let srNo = 1;

        // Pre-fill product fields on select
        $('#product_select').change(function() {
            const data = $(this).val();
            const product_id = $(this).val();
            $.ajax({
                url: "{{ url('/vendor/get-product-details/') }}/" + product_id,
                type: "GET",
                dataType: "json",
                success: function(data) {
                    console.log(data);
                    $('#batch').val(data.batch_no);
                    $('#mfg_date').val(data.mfg_date);
                    $('#mrp').val(data.cost_price);
                    $('#rate').val(data.sell_price);
                    $('#qty').val(1);
                    updateAmount();
                    addProduct(data);

                },
                error: function() {
                    alert('Failed to fetch subcategories. Please try again.');
                }
            });
            if (!data) return;
        });

        // Auto-calculate amount on qty/rate change
        $('#qty, #rate').on('input', function() {
            updateAmount();
        });

        function updateAmount() {

            const rate = parseFloat($('#rate').val()) || 0;
            const qty = parseInt($('#qty').val()) || 0;
            const get_total = (rate * qty);
            $('#amount').val((rate * qty).toFixed(2));
        }

        // Add product row
        function addProduct(data) {
            const brand = data.id;
            const brandVal = data.name;
            const batch = data.batch_no;
            const mfg = data.mfg_date;
            const mrp = data.cost_price;
            const rate = data.sell_price;
            const qty = 1;
            const amount = rate * qty;

            let existingRow = null;

            // Check if product already exists in the table
            $('#product_table tbody tr').each(function() {
                const rowBrand = $(this).find('input[name*="[brand_name]"]').val();
                const rowBatch = $(this).find('input[name*="[batch]"]').val();

                if (rowBrand === brandVal && rowBatch === batch) {
                    existingRow = $(this);
                    return false; // break loop
                }
            });

            if (existingRow) {
                // Update quantity and amount
                const qtyInput = existingRow.find('input[name*="[qnt]"]');
                const rateInput = existingRow.find('input[name*="[rate]"]');
                const amountInput = existingRow.find('input[name*="[amount]"]');

                let existingQty = parseInt(qtyInput.val()) || 0;
                const newQty = existingQty + qty;
                const newAmount = (newQty * rate).toFixed(2);

                qtyInput.val(newQty);
                amountInput.val(newAmount);
                qtyInput.data('prev', newQty); // update previous value

                updateTotal();
            } else {
                // New row
                const row = `
                <tr>
                    <td>${srNo}</td>
                    <input type="hidden" name="products[${srNo - 1}][product_id]" value="${brand}">
                    <td style="width:20%"><input type="text" name="products[${srNo - 1}][brand_name]" class="form-control" value="${brandVal}" readonly></td>
                    <td><input type="text" name="products[${srNo - 1}][batch]" class="form-control" value="${batch}"></td>
                    <td><input type="date" name="products[${srNo - 1}][mfg_date]" class="form-control" value="${mfg}"></td>
                    <td><input type="number" step="0.01" name="products[${srNo - 1}][mrp]" class="form-control" value="${mrp}"></td>
                    <td><input type="number" name="products[${srNo - 1}][qnt]" class="form-control" value="${qty}" min="1" data-prev="${qty}"></td>
                    <td><input type="number" step="0.01" name="products[${srNo - 1}][rate]" class="form-control" value="${rate}"></td>
                    <td><input type="number" step="0.01" name="products[${srNo - 1}][amount]" class="form-control" value="${amount}"></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove">Remove</button></td>
                </tr>
                `;

                $('#product_table tbody').append(row);
                srNo++;
                updateTotal();
            }

            resetFields();
        }

        // Remove row
        $(document).on('click', '.remove', function() {
            $(this).closest('tr').remove();

            // Recalculate total after row removal
            let newTotal = 0;
            $('input[name*="[amount]"]').each(function() {
                newTotal += parseFloat($(this).val()) || 0;
            });

            $('#total').text(newTotal.toFixed(2));
            $(".total_amt").val(newTotal.toFixed(2));
            $('#total_amount').text(newTotal.toFixed(2));

            $('.total_val').val(newTotal.toFixed(2));
            $('.total_amount').val(newTotal.toFixed(2));
        });

        function resetFields() {
            $('#product_select').val('');
            $('#batch').val('');
            $('#mfg_date').val('');
            $('#mrp').val('');
            $('#qty').val(1);
            $('#rate').val('');
            $('#amount').val('');
        }

        $(document).on('input', 'input[name*="[qnt]"], input[name*="[rate]"]', function() {
            const $input = $(this);
            const $row = $(this).closest('tr');

            let am = parseFloat($row.find('input[name*="[amount]"]').val()) || 0;
            const qty = parseFloat($row.find('input[name*="[qnt]"]').val()) || 0;
            const rate = parseFloat($row.find('input[name*="[rate]"]').val()) || 0;

            const amount = (qty * rate).toFixed(2);
            $row.find('input[name*="[amount]"]').val(amount);
            const prevQty = parseFloat($input.data('prev')) || 0;
            const newQty = parseFloat($input.val()) || 0;

            const amountInput = $row.find('input[name*="[amount]"]');

            // const newAmount = (newQty * rate).toFixed(2);
            // amountInput.val(newAmount);

            // Compare
            if (newQty > prevQty) {
                // console.log(`Quantity increas
                // ed from ${prevQty} to ${newQty}`);
            } else if (newQty < prevQty) {
                // console.log(`Quantity decreased from ${prevQty} to ${newQty}`);
            } else {
                // console.log(`Quantity unchanged`);
            }

            // Update the prev value
            $input.data('prev', newQty);

            // Recalculate total
            let total = 0;
            $('input[name*="[amount]"]').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#total').text(total.toFixed(2));
            $(".total_amt").val(total.toFixed(2));
            $('#total_amount').text(total.toFixed(2));
            $(".total_amount").val(total);

            calculation(total, '', '', '', '', '', total);
            updateBillingTotal();
            updateFromPercentage();
            updateFromAmount();
        });

        $('#excise_fee, #composition_vat, #surcharge_on_ca, #tcs, #aed_to_be_paid,#vat,#surcharge_on_vat,#blf,#permit_fee,#rsgsm_purchase')
            .on('input', function() {
                updateBillingTotal();
                updateFromPercentage();
                updateFromAmount();
            });

        function updateBillingTotal() {
            const baseTotal = parseFloat($('#total').text()) || 0;

            const excise = parseFloat($('#excise_fee').val()) || 0;
            const compVat = parseFloat($('#composition_vat').val()) || 0;
            const surcharge = parseFloat($('#surcharge_on_ca').val()) || 0;
            const tcs = parseFloat($('#tcs').val()) || 0;
            const vat = parseFloat($('#vat').val()) || 0;
            const surcharge_on_vat = parseFloat($('#surcharge_on_vat').val()) || 0;
            const blf = parseFloat($('#blf').val()) || 0;
            const permit_fee = parseFloat($('#permit_fee').val()) || 0;
            const rsgsm_purchase = parseFloat($('#rsgsm_purchase').val()) || 0;
            // const case_purchase = parseFloat($('#case_purchase').val()) || 0;

            const aed = parseFloat($('#aed_to_be_paid').val()) || 0;

            const grandTotal = baseTotal + excise + compVat + surcharge + tcs + aed + vat + surcharge_on_vat +
                blf + permit_fee + rsgsm_purchase;

            // $('#total_amount').text(grandTotal.toFixed(2));
            $('#total_amount').text('₹' + grandTotal.toFixed(2));
            $('.total_amount').val(grandTotal.toFixed(2));
        }

        function calculation(total, excise_fee, composition_vat, surcharge_on_ca, tcs, aed_to_be_paid,
            total_amount) {

            $('#total').text((total).toFixed(2));
            $(".total_amt").val(total.toFixed(2));
            $("#total_amount").text('Rs. ' + total_amount);

            $('.total_val').val((total).toFixed(2));
            $(".total_amount").val(total_amount);
        }

        function updateTotal() {
            let total = 0;
            $('input[name*="[amount]"]').each(function() {
                total += parseFloat($(this).val()) || 0;
            });

            // $('#total').text(total.toFixed(2));
            // $('#total_amount').text(total.toFixed(2));
            $('#total').text(total.toFixed(2));

            $(".total_amt").val(total.toFixed(2));
            $('.total_val').val(total.toFixed(2));

            $('#total_amount').text('₹' + total.toFixed(2));
            $('.total_amount').val(total.toFixed(2));
        }

        $(document).on('input', 'input[name*="[amount]"]', function() {
            const $row = $(this).closest('tr');
            const amount = parseFloat($(this).val()) || 0;
            const qty = parseFloat($row.find('input[name*="[qnt]"]').val()) || 1;

            // Recalculate rate
            const rate = amount / qty;
            $row.find('input[name*="[rate]"]').val(rate.toFixed(2));

            // Update total
            updateTotal();
            updateBillingTotal();
            updateFromPercentage();
            updateFromAmount();
        });

        $('#vendor_id').on('change', function() {
            const vendorId = $(this).val();

            // Hide all vendor-specific fields first
            $('.vendor-fields').addClass('d-none');

            if (vendorId === '1') {
                $('#vendor-1-fields').removeClass('d-none');

            } else if (vendorId === '2') {
                $('#vendor-2-fields').removeClass('d-none');
            } else {
                $('#vendor-others-fields').removeClass('d-none'); // Default
            }
        });

        // Replace this with dynamic value if needed

        function updateFromPercentage() {
            let originalAmount = $(".total_val").val();

            let percent = parseFloat($('.pur_dis').val()) || 0;
            let discount = (originalAmount * percent) / 100;
            $('.pur_amt').val(discount.toFixed(2));

            let ta = originalAmount - discount;
            $('#total_amount').text('₹' + ta.toFixed(2));
            $('.total_amount').val(ta.toFixed(2));

        }

        function updateFromAmount() {
            let originalAmount = $(".total_val").val();
            let amount = parseFloat($('.pur_amt').val()) || 0;
            let percent = (amount / originalAmount) * 100;
            $('.pur_dis').val(percent.toFixed(2));

            let ta = originalAmount - amount;
            $('#total_amount').text('₹' + ta.toFixed(2));
            $('.total_amount').val(ta.toFixed(2));

        }

        $('.pur_dis').on('input', updateFromPercentage);
        $('.pur_amt').on('input', updateFromAmount);

    });
</script>
