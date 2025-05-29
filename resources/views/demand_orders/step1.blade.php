@extends('layouts.backend.layouts')

@section('page-content')
    <!-- Wrapper Start -->
    <div class="wrapper">

        <div class="content-page">
            <div class="container-fluid add-form-list">
                <div class="row">
                    <div class="col-sm-12 col-lg-12">
                        <div class="iq-card">
                            <div class="iq-card-header d-flex justify-content-between">
                                <div class="iq-header-title">
                                    <h4 class="card-title">Create Demand Order</h4>
                                </div>
                            </div>
                            <div class="iq-card-body">
                                <form action="{{ route('demand-order.step1') }}" method="POST">
                                    @csrf
                                    <ul id="top-tab-list" class="p-0">
                                        <li class="active" id="account">
                                            <a href="javascript:void();">
                                                <i class="ri-lock-unlock-line"></i><span>Search Details</span>
                                            </a>
                                        </li>
                                        <li id="personal">
                                            <a href="javascript:void();">
                                                <i class="ri-user-fill"></i><span>Prediction</span>
                                            </a>
                                        </li>
                                        <li id="payment">
                                            <a href="javascript:void();">
                                                <i class="ri-file-text-line	"></i><span>Final Select</span>
                                            </a>
                                        </li>
                                        <li id="confirm">
                                            <a href="javascript:void();">
                                                <i class="ri-check-fill"></i><span>Finish</span>
                                            </a>
                                        </li>
                                    </ul>
                                    <!-- fieldsets -->
                                    <fieldset>
                                        <div class="form-card text-left">
                                            <div class="row">
                                                <div class="col-7">
                                                    <h3 class="mb-4">Search Details:</h3>
                                                </div>
                                                <div class="col-5">
                                                    <h2 class="steps">Step 1 - 4</h2>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="vendor_id">Vendor Name</label>
                                                  <select name="vendor_id" id="vendor_id" class="form-control">
                                                    <option value="">-- Select Party --</option>
                                                    @foreach ($vendors as $vendor)
                                                        <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                                            {{ $vendor->name }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                    @error('vendor_id')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-4">
                                                    <label for="purchase_date" class="form-label">Date</label>
                                                    <input type="date" class="form-control" id="purchase_date"
                                                        name="purchase_date" required>
                                                    @error('purchase_date')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                @php
                                                    $today = date('Y-m-d');
                                                    $maxDate = date('Y-m-d', strtotime('+3 months'));
                                                @endphp

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="shipping_date" class="form-label">Shipping Date</label>
                                                        <input type="date" id="shipping_date" name="shipping_date"
                                                            class="form-control" min="{{ $today }}"
                                                            max="{{ $maxDate }}">
                                                        @error('shipping_date')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                {{-- <div class="col-md-4"></div> --}}
                                                {{-- <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="notes" class="form-label">Notes</label>
                                                        <textarea name="notes" class="form-control"></textarea>

                                                    </div>
                                                </div> --}}
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="avg_sales">Average Sale</label>
                                                        <select name="avg_sales" id="avg_sales" class="form-control">
                                                            <option value="">-- Select Average Sale --</option>
                                                            <option value="7">Last week</option>
                                                            <option value="14">Last 2 week</option>
                                                            <option value="21">Last 3 week</option>
                                                            <option value="30">Last 1 Month</option>

                                                        </select>
                                                        @error('avg_sales')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary float-right">Next</button>

                                    </fieldset>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Wrapper End -->
@endsection

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {

        let srNo = 1;

        // Pre-fill product fields on select
        // $('#avg_sales').change(function() {
        //     const data = $(this).val();
        //     const product_id = $(this).val();
        //     $.ajax({
        //         url: "{{ url('/vendor/get-product-details/') }}/" + product_id,
        //         type: "GET",
        //         dataType: "json",
        //         success: function(data) {
        //             console.log(data);
        //             $('#batch').val(data.batch_no);
        //             $('#mfg_date').val(data.mfg_date);
        //             $('#mrp').val(data.cost_price);
        //             $('#rate').val(data.sell_price);
        //             $('#qty').val(1);
        //             updateAmount();
        //             addProduct(data);

        //         },
        //         error: function() {
        //             alert('Failed to fetch subcategories. Please try again.');
        //         }
        //     });
        //     if (!data) return;
        // });

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
                    <td><input type="text" name="products[${srNo - 1}][brand_name]" class="form-control" value="${brandVal}" readonly></td>
                    <td><input type="text" name="products[${srNo - 1}][batch]" class="form-control" value="${batch}"></td>
                    <td><input type="date" name="products[${srNo - 1}][mfg_date]" class="form-control" value="${mfg}"></td>
                    <td><input type="number" step="0.01" name="products[${srNo - 1}][mrp]" class="form-control" value="${mrp}"></td>
                    <td><input type="number" name="products[${srNo - 1}][qnt]" class="form-control" value="${qty}" min="1" data-prev="${qty}"></td>
                    <td><input type="number" step="0.01" name="products[${srNo - 1}][rate]" class="form-control" value="${rate}"></td>
                    <td><input type="number" step="0.01" name="products[${srNo - 1}][amount]" class="form-control" value="${amount}" readonly></td>
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
            $('#total_amount').text(newTotal.toFixed(2));

            $('.total_val').val(newTotal.toFixed(2));
            $('.total_amount').val(newTotal.toFixed(2));
        });

        function resetFields() {
            $('#avg_sales').val('');
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
            $('#total_amount').text(total.toFixed(2));

            calculation(total, '', '', '', '', '', '');
        });

        $('#excise_fee, #composition_vat, #surcharge_on_ca, #tcs, #aed_to_be_paid').on('input', function() {
            updateBillingTotal();
        });

        function updateBillingTotal() {
            const baseTotal = parseFloat($('#total').text()) || 0;

            const excise = parseFloat($('#excise_fee').val()) || 0;
            const compVat = parseFloat($('#composition_vat').val()) || 0;
            const surcharge = parseFloat($('#surcharge_on_ca').val()) || 0;
            const tcs = parseFloat($('#tcs').val()) || 0;
            const aed = parseFloat($('#aed_to_be_paid').val()) || 0;

            const grandTotal = baseTotal + excise + compVat + surcharge + tcs + aed;

            $('#total_amount').text(grandTotal.toFixed(2));
            $('.total_amount').text(grandTotal.toFixed(2));
        }

        function calculation(total, excise_fee, composition_vat, surcharge_on_ca, tcs, aed_to_be_paid,
            total_amount) {

            $('#total').text((total).toFixed(2));
            $("#total_amount").text('Rs. ' + total_amount);

            $('.total_val').val((total).toFixed(2));
            $(".total_amount").val('Rs. ' + total_amount);
        }

        function updateTotal() {
            let total = 0;
            $('input[name*="[amount]"]').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#total').text(total.toFixed(2));
            $('#total_amount').text(total.toFixed(2));

            $('.total_val').val(total.toFixed(2));
            $('.total_amount').val(total.toFixed(2));
        }
    });
</script>
