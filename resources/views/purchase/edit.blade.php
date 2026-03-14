@extends('layouts.backend.layouts')

@section('page-content')
    <div class="content-page">
        <div class="container-fluid add-form-list">

            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <div class="">
                    <h4 class="mb-0">Edit Purchase Invoice</h4>
                </div>
                <div>
                    <a href="{{ route('purchase.list') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">


                        <div class="card-body">
                            <div class="card">
                                <div class="card-body">

                                    <form action="{{ route('purchase.update', $purchase->id) }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')

                                        <div class="row">
                                            <div class="col-md-4">
                                                <label>Bill No</label>
                                                <input type="text" class="form-control" name="bill_no"
                                                    value="{{ old('bill_no', $purchase->bill_no) }}">
                                            </div>

                                            <div class="col-md-4">
                                                <label>Date</label>
                                                <input type="date" class="form-control" name="date"
                                                    value="{{ old('date', $purchase->date) }}">
                                            </div>

                                            <div class="col-md-4">
                                                <label>Vendor</label>
                                                <select name="vendor_id" id="vendor_id" class="form-control">
                                                    <option value="">-- Select Party --</option>
                                                    @foreach ($vendors as $vendor)
                                                        <option value="{{ $vendor->id }}" @selected(old('vendor_id', $purchase->vendor_id) == $vendor->id)>
                                                            {{ $vendor->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label>Ledger Name</label>
                                                <select name="vendor_new_id" id="vendor_new_id" class="form-control">
                                                    <option value="">-- Select Ledger Name --</option>
                                                    @foreach ($ledgersAll as $vendor)
                                                        <option value="{{ $vendor->id }}" @selected(old('vendor_new_id', $purchase->vendor_new_id) == $vendor->id)>
                                                            {{ $vendor->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label>Purchase Ledger</label>
                                                <select name="parchase_ledger" id="parchase_ledger" class="form-control">
                                                    <option value="">-- Select Ledger --</option>
                                                    @foreach ($ledgers as $ven)
                                                        <option value="{{ $ven->id }}" @selected(old('parchase_ledger', $purchase->parchase_ledger) == $ven->id)>
                                                            {{ $ven->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label>Sub Category</label>
                                                <select name="subcategories" id="subcategories" class="form-control">
                                                    <option value="">-- Select Sub Category --</option>
                                                    @foreach ($subcategories as $subcat)
                                                        <option value="{{ $subcat->id }}" data-id="{{ $subcat->id }}"
                                                            @selected(old('subcategories', $purchaseProducts[0]['subcategory_id'] ?? '') == $subcat->id)>
                                                            {{ $subcat->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label>Product</label>
                                                <select id="product_select" class="form-control">
                                                    <option value="">-- Select Product --</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product['id'] }}">{{ $product['name'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <hr>

                                        <table class="table table-bordered" id="product_table">
                                            <thead>
                                                <tr>
                                                    <th>Sr No</th>
                                                    <th>Product</th>
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
                                                @foreach ($purchaseProducts as $i => $product)
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <input type="hidden"
                                                            name="products[{{ $i }}][product_id]"
                                                            value="{{ $product['product_id'] }}">
                                                        <td><input class="form-control"
                                                                name="products[{{ $i }}][brand_name]"
                                                                value="{{ $product['brand_name'] }}" readonly></td>
                                                        <td><input class="form-control"
                                                                name="products[{{ $i }}][batch]"
                                                                value="{{ $product['batch'] }}"></td>
                                                        <td><input type="date" class="form-control"
                                                                name="products[{{ $i }}][mfg_date]"
                                                                value="{{ $product['mfg_date'] }}"></td>
                                                        <td>
                                                            <input type="hidden" name="products[{{ $i }}][mrp]"
                                                                value="{{ $product['mrp'] }}">
                                                            <input class="form-control" value="{{ $product['mrp'] }}"
                                                                disabled>
                                                        </td>
                                                        <td><input class="form-control"
                                                                name="products[{{ $i }}][qnt]"
                                                                value="{{ $product['qnt'] }}"></td>
                                                        <td><input class="form-control"
                                                                name="products[{{ $i }}][rate]"
                                                                value="{{ $product['rate'] }}"></td>
                                                        <td><input class="form-control"
                                                                name="products[{{ $i }}][amount]"
                                                                value="{{ $product['amount'] }}"></td>
                                                        <td><button type="button"
                                                                class="btn btn-danger remove">Remove</button></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                        <input type="hidden" name="total" class="total_val"
                                            value="{{ old('total', $purchase->total) }}">
                                        <div class="text-end">
                                            <h5>Sub Total : <span id="total"></span></h5>
                                        </div>

                                        <hr>

                                        {{-- LICENSE LEDGER --}}
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <div class="or-detail rounded" id="license-ledger-box">
                                                    <div class="p-3">
                                                        <h5 class="mb-3">Details For License Ledger</h5>

                                                        <div class="form-group">
                                                            <label>AED TO BE PAID</label>
                                                            <input type="number" class="form-control"
                                                                name="aed_to_be_paid" id="aed_to_be_paid"
                                                                value="{{ old('aed_to_be_paid', $purchase->aed_to_be_paid) }}">
                                                        </div>

                                                        <div class="form-group">
                                                            <label>Guarantee Fulfilled</label>
                                                            <input type="number" class="form-control"
                                                                name="guarantee_fulfilled" id="guarantee_fulfilled"
                                                                value="{{ old('guarantee_fulfilled', $purchase->guarantee_fulfilled) }}">
                                                        </div>

                                                        <div class="form-group">
                                                            <label>Loading Charges (Including Tax)</label>
                                                            <input type="number" class="form-control"
                                                                name="loading_charges" id="loading_charges"
                                                                value="{{ old('loading_charges', $purchase->loading_charges) }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- EXCISE --}}
                                            <div class="col-lg-4 excise-section d-none">
                                                <div class="or-detail rounded">
                                                    <div class="p-3">
                                                        <h5 class="mb-3">Excise Fee</h5>

                                                        <div class="form-group">
                                                            <label>Permit Fee</label>
                                                            <input type="number" class="form-control"
                                                                name="permit_fee_excise" id="permit_fee_excise"
                                                                value="{{ old('permit_fee_excise', $purchase->permit_fee_excise) }}">
                                                        </div>

                                                        <div class="form-group">
                                                            <label>Vend Fee</label>
                                                            <input type="number" class="form-control"
                                                                name="vend_fee_excise" id="vend_fee_excise"
                                                                value="{{ old('vend_fee_excise', $purchase->vend_fee_excise) }}">
                                                        </div>

                                                        <div class="form-group">
                                                            <label>Composite Fee (For RTDC Shop)</label>
                                                            <input type="number" class="form-control"
                                                                name="composite_fee_excise" id="composite_fee_excise"
                                                                value="{{ old('composite_fee_excise', $purchase->composite_fee_excise) }}">
                                                        </div>
                                                    </div>

                                                    <div
                                                        class="ttl-amt py-2 px-3 d-flex justify-content-between align-items-center border-top">
                                                        <h6>Total</h6>
                                                        <div>
                                                            <input type="hidden" name="excise_total_amount"
                                                                class="excise_total_amount"
                                                                value="{{ old('excise_total_amount', $purchase->excise_total_amount) }}">
                                                            <h3 class="text-primary" id="excise_total_amount">
                                                                ₹{{ number_format(old('excise_total_amount', $purchase->excise_total_amount), 0) }}
                                                            </h3>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- BILLING --}}
                                            <div class="col-lg-4 offset-lg-4" id="billing-column">
                                                <div class="or-detail rounded">
                                                    <div class="p-3">
                                                        <h5 class="mb-3">Billing Details</h5>

                                                        {{-- Vendor 1 --}}
                                                        <div id="vendor-1-fields" class="vendor-fields d-none">
                                                            <div class="form-group">
                                                                <label>EXCISE FEE</label>
                                                                <input type="number" class="form-control"
                                                                    name="excise_fee" id="excise_fee"
                                                                    value="{{ old('excise_fee', $purchase->excise_fee) }}">
                                                            </div>

                                                            <div class="form-group">
                                                                <label>COMPOSITION VAT</label>
                                                                <input type="number" class="form-control"
                                                                    name="composition_vat" id="composition_vat"
                                                                    value="{{ old('composition_vat', $purchase->composition_vat) }}">
                                                            </div>

                                                            <div class="form-group">
                                                                <label>SURCHARGE ON CA</label>
                                                                <input type="number" class="form-control"
                                                                    name="surcharge_on_ca" id="surcharge_on_ca"
                                                                    value="{{ old('surcharge_on_ca', $purchase->surcharge_on_ca) }}">
                                                            </div>
                                                        </div>

                                                        {{-- Vendor 2 --}}
                                                        <div id="vendor-2-fields" class="vendor-fields d-none">
                                                            <div class="form-group">
                                                                <label>VAT</label>
                                                                <input type="number" class="form-control" name="vat"
                                                                    id="vat"
                                                                    value="{{ old('vat', $purchase->vat) }}">
                                                            </div>

                                                            <div class="form-group">
                                                                <label>SURCHARGE ON VAT</label>
                                                                <input type="number" class="form-control"
                                                                    name="surcharge_on_vat" id="surcharge_on_vat"
                                                                    value="{{ old('surcharge_on_vat', $purchase->surcharge_on_vat) }}">
                                                            </div>

                                                            <div class="form-group">
                                                                <label>BLF</label>
                                                                <input type="number" class="form-control" name="blf"
                                                                    id="blf"
                                                                    value="{{ old('blf', $purchase->blf) }}">
                                                            </div>

                                                            <div class="form-group">
                                                                <label>Permit Fee</label>
                                                                <input type="number" class="form-control"
                                                                    name="permit_fee" id="permit_fee"
                                                                    value="{{ old('permit_fee', $purchase->permit_fee) }}">
                                                            </div>
                                                        </div>

                                                        {{-- Common --}}
                                                        <div class="vendor-common">
                                                            <div class="form-group">
                                                                <label>TCS</label>
                                                                <input type="number" class="form-control" name="tcs"
                                                                    id="tcs"
                                                                    value="{{ old('tcs', $purchase->tcs) }}">
                                                            </div>
                                                        </div>

                                                        {{-- Cash purchase --}}
                                                        <div id="vendor-others-fields" class="vendor-fields d-none">
                                                            <div class="form-group">
                                                                <label>Cash Purchase %</label>
                                                                <input type="number" class="form-control pur_dis"
                                                                    name="case_purchase_per"
                                                                    value="{{ old('case_purchase_per', $purchase->case_purchase_per) }}">
                                                            </div>

                                                            <div class="form-group">
                                                                <label>Cash Purchase Amount</label>
                                                                <input type="number" class="form-control pur_amt"
                                                                    name="case_purchase_amt"
                                                                    value="{{ old('case_purchase_amt', $purchase->case_purchase_amt) }}">
                                                            </div>
                                                        </div>

                                                        <div
                                                            class="ttl-amt py-2 px-3 d-flex justify-content-between align-items-center border-top">
                                                            <h6>Total Amount</h6>
                                                            <div>
                                                                <input type="hidden" class="total_amt"
                                                                    value="{{ old('total', $purchase->total) }}">

                                                                <input type="hidden" name="total_amount"
                                                                    class="total_amount"
                                                                    value="{{ old('total_amount', $purchase->total_amount) }}">
                                                                <h3 id="total_amount">
                                                                    ₹{{ number_format(old('total_amount', $purchase->total_amount), 0) }}
                                                                </h3>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        <button type="submit" class="btn btn-primary">Update Purchase</button>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    // ---------- HELPERS (GLOBAL) ----------

    function calculateProductTotals() {
        let total = 0;
        $('input[name*="[rate]"]').each(function() {
            const $row = $(this).closest('tr');
            const rate = parseFloat($(this).val()) || 0;
            const qty = parseFloat($row.find('input[name*="[qnt]"]').val()) || 0;
            const amount = (rate * qty);
            $row.find('input[name*="[amount]"]').val(amount);
            total += parseFloat(amount);
        });

        $('#total').text(total);
        $(".total_amt").val(total);
        $('.total_val').val(total);

        return total;
    }

    function updateExciseSection() {
        const permit = parseFloat($('#permit_fee_excise').val()) || 0;
        const vend = parseFloat($('#vend_fee_excise').val()) || 0;
        const composite = parseFloat($('#composite_fee_excise').val()) || 0;

        const totalExcise = permit + vend + composite;

        $('#excise_total_amount').text('₹' + totalExcise);
        $('.excise_total_amount').val(totalExcise);

        // Push to Billing Details -> EXCISE FEE
        $('#excise_fee').val(totalExcise);

        updateBillingTotal();
    }

    function updateBillingTotal() {

        const baseTotal = parseFloat($(".total_amt").val()) || 0;

        const excise = parseFloat($('#excise_fee').val()) || 0;
        const compVat = parseFloat($('#composition_vat').val()) || 0;
        const surcharge = parseFloat($('#surcharge_on_ca').val()) || 0;
        const tcs = parseFloat($('#tcs').val()) || 0;
        const vat = parseFloat($('#vat').val()) || 0;
        const surcharge_on_vat = parseFloat($('#surcharge_on_vat').val()) || 0;
        const blf = parseFloat($('#blf').val()) || 0;
        const permit_fee = parseFloat($('#permit_fee').val()) || 0;
        const aed = parseFloat($('#aed_to_be_paid').val()) || 0;
        const loading = parseFloat($('#loading_charges').val()) || 0;

        let grandTotal = baseTotal +
            excise + compVat + surcharge + tcs +
            vat + surcharge_on_vat + blf +
            permit_fee + aed + loading;

        // apply discount ONCE here
        const discountPercent = parseFloat($('.pur_dis').val()) || 0;
        const discountAmount = parseFloat($('.pur_amt').val()) || 0;

        if (discountPercent > 0) {
            const discount = (grandTotal * discountPercent) / 100;
            grandTotal -= discount;
            $('.pur_amt').val(discount);
        } else if (discountAmount > 0) {
            grandTotal -= discountAmount;
        }

        $('#total_amount').text('₹' + grandTotal);
        $('.total_amount').val(grandTotal);
    }


    function filterSubcategoriesByVendor(vendorId) {

        const vendor1Subs = ['1', '2']; // vendor_id = 1
        const vendor2Subs = ['3', '4']; // vendor_id = 2

        $('#subcategories option').each(function() {

            const subId = $(this).data('id');

            // Always show default option
            if (!subId) {
                $(this).show();
                return;
            }

            if (vendorId === '1') {
                $(this).toggle(vendor1Subs.includes(String(subId)));
            } else if (vendorId === '2') {
                $(this).toggle(vendor2Subs.includes(String(subId)));
            } else {
                // Other vendors → show all
                $(this).show();
            }
        });

        // Reset if selected option becomes hidden
        if ($('#subcategories option:selected').is(':hidden')) {
            $('#subcategories').val('');
        }
    }

    function onVendorChange(vendorId) {
        $('.vendor-fields').addClass('d-none');
        $('.vendor-common').hide();
        $('.excise-section').addClass('d-none');

        $('#excise_fee, #composition_vat, #surcharge_on_ca, #aed_to_be_paid').val(0);
        $('#vat, #surcharge_on_vat, #blf, #permit_fee, #rsgsm_purchase').val(0);
        $('.pur_dis, .pur_amt').val(0);

        $('#permit_fee_excise, #vend_fee_excise, #composite_fee_excise').val(0);
        $('#excise_total_amount').text('₹0.00');
        $('.excise_total_amount').val('0');

        const $billingCol = $('#billing-column');

        const oldValues = {
            excise_fee: '{{ old('excise_fee', $purchase->excise_fee) }}',
            composition_vat: '{{ old('composition_vat', $purchase->composition_vat) }}',
            surcharge_on_ca: '{{ old('surcharge_on_ca', $purchase->surcharge_on_ca) }}',
            aed_to_be_paid: '{{ old('aed_to_be_paid', $purchase->aed_to_be_paid) }}',
            guarantee_fulfilled: '{{ old('guarantee_fulfilled', $purchase->guarantee_fulfilled) }}',

            vat: '{{ old('vat', $purchase->vat) }}',
            surcharge_on_vat: '{{ old('surcharge_on_vat', $purchase->surcharge_on_vat) }}',
            blf: '{{ old('blf', $purchase->blf) }}',
            permit_fee: '{{ old('permit_fee', $purchase->permit_fee) }}',
            rsgsm_purchase: '{{ old('rsgsm_purchase', $purchase->rsgsm_purchase) }}',

            permit_fee_excise: '{{ old('permit_fee_excise', $purchase->permit_fee_excise) }}',
            vend_fee_excise: '{{ old('vend_fee_excise', $purchase->vend_fee_excise) }}',
            composite_fee_excise: '{{ old('composite_fee_excise', $purchase->composite_fee_excise) }}',
            excise_total_amount: '{{ old('excise_total_amount', $purchase->excise_total_amount) }}',

            case_purchase_per: '{{ old('case_purchase_per', $purchase->case_purchase_per) }}',
            case_purchase_amt: '{{ old('case_purchase_amt', $purchase->case_purchase_amt) }}',

            tcs: '{{ old('tcs', $purchase->tcs) }}'
        };


        // SHOW LICENSE LEDGER ONLY FOR VENDOR 1 & 2
        if (vendorId === '1' || vendorId === '2') {
            $('#license-ledger-box').removeClass('d-none');
        } else {
            $('#license-ledger-box').addClass('d-none');
        }


        if (vendorId === '1') {
            // Vendor 1: three columns -> Billing no offset, excise visible
            $billingCol.removeClass('offset-lg-4').addClass('offset-lg-0');

            $('.excise-section').removeClass('d-none');
            $('#vendor-1-fields').removeClass('d-none');
            $('.vendor-common').show();

            if (oldValues.permit_fee_excise) $('#permit_fee_excise').val(oldValues.permit_fee_excise);
            if (oldValues.vend_fee_excise) $('#vend_fee_excise').val(oldValues.vend_fee_excise);
            if (oldValues.composite_fee_excise) $('#composite_fee_excise').val(oldValues.composite_fee_excise);

            if (oldValues.excise_fee) $('#excise_fee').val(oldValues.excise_fee);
            if (oldValues.composition_vat) $('#composition_vat').val(oldValues.composition_vat);
            if (oldValues.surcharge_on_ca) $('#surcharge_on_ca').val(oldValues.surcharge_on_ca);
            if (oldValues.aed_to_be_paid) $('#aed_to_be_paid').val(oldValues.aed_to_be_paid);
            if (oldValues.guarantee_fulfilled) $('#guarantee_fulfilled').val(oldValues.guarantee_fulfilled);

            if (oldValues.excise_total_amount) {
                $('#excise_total_amount').text('₹' + parseFloat(oldValues.excise_total_amount));
                $('.excise_total_amount').val(parseFloat(oldValues.excise_total_amount));
            }

            updateExciseSection(); // also sets excise_fee

        } else if (vendorId === '2') {
            // Vendor 2: excise hidden, Billing right side
            $billingCol.removeClass('offset-lg-0').addClass('offset-lg-4');

            $('#vendor-2-fields').removeClass('d-none');

            if (oldValues.vat) $('#vat').val(oldValues.vat);
            if (oldValues.surcharge_on_vat) $('#surcharge_on_vat').val(oldValues.surcharge_on_vat);
            if (oldValues.blf) $('#blf').val(oldValues.blf);
            if (oldValues.permit_fee) $('#permit_fee').val(oldValues.permit_fee);
            if (oldValues.rsgsm_purchase) $('#rsgsm_purchase').val(oldValues.rsgsm_purchase);

            $('.vendor-common').show();
        } else if (vendorId) {
            // Other vendors: excise hidden, show cash purchase, Billing right
            $billingCol.removeClass('offset-lg-0').addClass('offset-lg-4');

            $('#vendor-others-fields').removeClass('d-none');
            if (oldValues.case_purchase_per) $('.pur_dis').val(oldValues.case_purchase_per);
            if (oldValues.case_purchase_amt) $('.pur_amt').val(oldValues.case_purchase_amt);
        } else {
            // No vendor selected -> Billing right, excise hidden
            $billingCol.removeClass('offset-lg-0').addClass('offset-lg-4');
        }

        if (oldValues.tcs) $('#tcs').val(oldValues.tcs);

        calculateProductTotals();
        updateBillingTotal();
    }

    // ---------- MAIN READY ----------

    $(document).ready(function() {
        let srNo = $('#productBody tr').length ? $('#productBody tr').length + 1 : 1;
        $('.vendor-common').hide();

        // Product select -> fetch details
        $('#product_select').change(function() {
            const product_id = $(this).val();
            if (!product_id) return;

            $.ajax({
                url: "{{ url('/vendor/get-product-details/') }}/" + product_id,
                type: "GET",
                dataType: "json",
                success: function(data) {
                    addProduct(data);
                },
                error: function() {
                    alert('Failed to fetch product details. Please try again.');
                }
            });
        });

        function addProduct(data) {
            const brand = data.id;
            const brandVal = data.name;
            const batch = data.batch_no;
            const mfg = data.mfg_date;
            const mrp = data.mrp;
            const rate = data.cost_price;
            const qty = 1;
            const amount = rate * qty;

            let existingRow = null;

            $('#product_table tbody tr').each(function() {
                const rowBrand = $(this).find('input[name*="[brand_name]"]').val();
                const rowBatch = $(this).find('input[name*="[batch]"]').val();
                if (rowBrand === brandVal && rowBatch === batch) {
                    existingRow = $(this);
                    return false;
                }
            });

            if (existingRow) {
                const qtyInput = existingRow.find('input[name*="[qnt]"]');
                const amountInput = existingRow.find('input[name*="[amount]"]');

                let existingQty = parseInt(qtyInput.val()) || 0;
                const newQty = existingQty + qty;
                const newAmount = (newQty * rate);

                qtyInput.val(newQty);
                amountInput.val(newAmount);
                qtyInput.data('prev', newQty);

                calculateProductTotals();
                updateBillingTotal();
            } else {
                const row = `
                <tr>
                    <td>${srNo}</td>
                    <input type="hidden" name="products[${srNo - 1}][product_id]" value="${brand}">
                    <td style="width:25%">
                        <input type="text" name="products[${srNo - 1}][brand_name]" class="form-control" value="${brandVal}" readonly>
                    </td>
                    <td>
                        <input type="text" name="products[${srNo - 1}][batch]" class="form-control" value="${batch}">
                    </td>
                    <td>
                        <input type="date" name="products[${srNo - 1}][mfg_date]" class="form-control" value="${mfg ?? ''}">
                    </td>
                    <td>
                        <input type="hidden" name="products[${srNo - 1}][mrp]" value="${mrp}">
                        <input type="number" class="form-control" value="${mrp}" disabled>
                    </td>
                    <td>
                        <input type="number" name="products[${srNo - 1}][qnt]" class="form-control" value="${qty}" min="1" data-prev="${qty}">
                    </td>
                    <td>
                        <input type="number" step="0.01" name="products[${srNo - 1}][rate]" class="form-control" value="${rate}">
                    </td>
                    <td>
                        <input type="number" step="0.01" name="products[${srNo - 1}][amount]" class="form-control" value="${amount}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove">Remove</button>
                    </td>
                </tr>
                `;

                $('#product_table tbody').append(row);
                srNo++;

                calculateProductTotals();
                updateBillingTotal();
            }

            $('#product_select').val('');
        }

        // Remove row
        $(document).on('click', '.remove', function() {
            $(this).closest('tr').remove();
            calculateProductTotals();
            updateBillingTotal();

            if ($('#productBody tr').length === 0) {
                $('#excise_fee, #composition_vat, #surcharge_on_ca, #aed_to_be_paid').val('');
                $('#vat, #surcharge_on_vat, #blf, #permit_fee, #rsgsm_purchase').val('');
                $('.pur_dis, .pur_amt').val('');
                $('#tcs').val('');
                $('#total_amount').text('₹0.00');
                $('.total_amount').val('0.00');
                $('#excise_total_amount').text('₹0.00');
                $('.excise_total_amount').val('0.00');
            }
        });

        // qty / rate change
        $(document).on('input', 'input[name*="[qnt]"], input[name*="[rate]"]', function() {
            const $input = $(this);
            const $row = $input.closest('tr');

            const qty = parseFloat($row.find('input[name*="[qnt]"]').val()) || 0;
            const rate = parseFloat($row.find('input[name*="[rate]"]').val()) || 0;
            const amount = (qty * rate);

            $row.find('input[name*="[amount]"]').val(amount);
            $input.data('prev', qty);

            calculateProductTotals();
            updateBillingTotal();
        });

        // Amount change -> recalc rate
        $(document).on('input', 'input[name*="[amount]"]', function() {
            const $row = $(this).closest('tr');
            const amount = parseFloat($(this).val()) || 0;
            const qty = parseFloat($row.find('input[name*="[qnt]"]').val()) || 1;
            const rate = amount / qty;
            $row.find('input[name*="[rate]"]').val(rate);

            calculateProductTotals();
            updateBillingTotal();
        });

        // Billing fields
        // Billing fields
        $('#excise_fee, #composition_vat, #surcharge_on_ca, #tcs, #vat, #surcharge_on_vat, #blf, #permit_fee, #rsgsm_purchase, #aed_to_be_paid, #loading_charges')
            .on('input', function() {
                updateBillingTotal();
            });

        // Excise box fields
        $(document).on('input', '#permit_fee_excise, #vend_fee_excise, #composite_fee_excise', function() {
            updateExciseSection();
        });

        // Discount fields
        function updateFromPercentage() {
            const baseTotal = parseFloat($(".total_amt").val()) || 0;

            const percent = parseFloat($('.pur_dis').val()) || 0;
            const discount = (baseTotal * percent) / 100;

            $('.pur_amt').val(discount);

            updateBillingTotal(); // only here
        }

        function updateFromAmount() {
            const baseTotal = parseFloat($(".total_amt").val()) || 0;

            const amount = parseFloat($('.pur_amt').val()) || 0;
            const percent = baseTotal > 0 ? (amount / baseTotal) * 100 : 0;

            $('.pur_dis').val(percent);

            updateBillingTotal(); // only here
        }


        $('.pur_dis').on('input', function() {
            updateFromPercentage();
            updateBillingTotal();
        });
        $('.pur_amt').on('input', function() {
            updateFromAmount();
            updateBillingTotal();
        });

        // Subcategory -> products
        $('#subcategories').on('change', function() {
            const subcatId = $(this).val();
            const $productSelect = $('#product_select');

            $productSelect.empty().append('<option value="">Loading...</option>');

            if (!subcatId) {
                $productSelect.empty().append('<option value="">-- Select Product --</option>');
                return;
            }

            $.ajax({
                url: "/subcategory/" + subcatId + "/products",
                type: "GET",
                dataType: "json",
                success: function(products) {
                    $productSelect.empty().append(
                        '<option value="">-- Select Product --</option>');
                    if (!products || products.length === 0) {
                        $productSelect.append(
                            '<option value="">No products found</option>');
                        return;
                    }

                    products.forEach(function(p) {
                        $productSelect.append(
                            $('<option>', {
                                value: p.id,
                                text: p.name,
                                'data-mrp': p.mrp ?? '',
                                'data-cost_price': p.cost_price ?? '',
                                'data-sell_price': p.sell_price ?? ''
                            })
                        );
                    });

                    const oldProduct = "{{ old('product_select') }}";
                    if (oldProduct) {
                        $productSelect.val(oldProduct);
                    }
                },
                error: function(xhr) {
                    $productSelect.empty().append(
                        '<option value="">-- Select Product --</option>');
                    alert('Failed to fetch products for selected subcategory. Try again.');
                    console.error(xhr);
                }
            });
        });

        // vendor change -> onVendorChange + auto sync ledger
        $('#vendor_id').on('change', function() {
            const vendorId = $(this).val();

            onVendorChange(vendorId); // your existing logic
            filterSubcategoriesByVendor(vendorId); // ✅ subcategory logic

            // Auto-sync purchase ledger
            $('#parchase_ledger').val(vendorId);
        });

    });

    // Initial on page load (after validation error)
    document.addEventListener('DOMContentLoaded', function() {

        calculateProductTotals();
        updateBillingTotal();

        const vendorId = "{{ old('vendor_id', $purchase->vendor_id) }}";

        if (vendorId) {
            onVendorChange(vendorId);
            filterSubcategoriesByVendor(vendorId);

            // restore Excise total display
            const exciseTotal = "{{ old('excise_total_amount', $purchase->excise_total_amount) }}";
            if (exciseTotal) {
                $('#excise_total_amount').text('₹' + parseFloat(exciseTotal));
                $('.excise_total_amount').val(parseFloat(exciseTotal));
            }

            // restore Grand Total
            const totalAmount = "{{ old('total_amount', $purchase->total_amount) }}";
            if (totalAmount) {
                $('#total_amount').text('₹' + parseFloat(totalAmount));
                $('.total_amount').val(parseFloat(totalAmount));
            }

        } else {
            $('#license-ledger-box').addClass('d-none');
        }
    });
</script>
