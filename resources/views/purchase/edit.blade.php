@extends('layouts.backend.layouts')
<style>
    #product_table {
        table-layout: fixed;
        width: 100%;
    }

    #product_table th,
    #product_table td {
        vertical-align: middle;
    }

    /* Column widths */
    #product_table th:nth-child(1),
    #product_table td:nth-child(1) {
        width: 5%;
    }

    #product_table th:nth-child(2),
    #product_table td:nth-child(2) {
        width: 25%;
    }

    #product_table th:nth-child(3),
    #product_table td:nth-child(3) {
        width: 8%;
    }

    #product_table th:nth-child(4),
    #product_table td:nth-child(4) {
        width: 13%;
    }

    #product_table th:nth-child(5),
    #product_table td:nth-child(5) {
        width: 8%;
    }

    #product_table th:nth-child(6),
    #product_table td:nth-child(6) {
        width: 8%;
    }

    #product_table th:nth-child(7),
    #product_table td:nth-child(7) {
        width: 10%;
    }

    #product_table th:nth-child(8),
    #product_table td:nth-child(8) {
        width: 10%;
    }

    #product_table th:nth-child(9),
    #product_table td:nth-child(9) {
        width: 10%;
    }
</style>
@section('page-content')
    <div class="content-page">
        <div class="container-fluid">

            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <h4>Edit Purchase Invoice</h4>
                <a href="{{ route('purchase.list') }}" class="btn btn-secondary">Back</a>
            </div>

            <div class="card">
                <div class="card-body">

                    <form action="{{ route('purchase.update', $purchase->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">

                            <div class="col-md-4">
                                <label>Bill No</label>
                                <input type="text" name="bill_no" class="form-control"
                                    value="{{ old('bill_no', $purchase->bill_no) }}">
                            </div>

                            <div class="col-md-4">
                                <label>Date</label>
                                <input type="date" name="date" class="form-control"
                                    value="{{ old('date', $purchase->date) }}">
                            </div>

                            <div class="col-md-4">
                                <label>Vendor</label>
                                <select name="vendor_id" id="vendor_id" class="form-control">
                                    <option value="">Select Vendor</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->id }}"
                                            {{ old('vendor_id', $purchase->vendor_id) == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Vendor Ledger</label>
                                <select name="vendor_new_id" class="form-control">
                                    <option value="">Select Ledger</option>
                                    @foreach ($ledgersAll as $ledger)
                                        <option value="{{ $ledger->id }}"
                                            {{ old('vendor_new_id', $purchase->vendor_new_id) == $ledger->id ? 'selected' : '' }}>
                                            {{ $ledger->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Purchase Ledger</label>
                                <select name="parchase_ledger" id="parchase_ledger" class="form-control">
                                    <option value="">Select Ledger</option>
                                    @foreach ($ledgers as $ledger)
                                        <option value="{{ $ledger->id }}"
                                            {{ old('parchase_ledger', $purchase->parchase_ledger) == $ledger->id ? 'selected' : '' }}>
                                            {{ $ledger->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Sub Category</label>
                                <select name="subcategories" id="subcategories" class="form-control">
                                    <option value="">Select</option>
                                    @foreach ($subcategories as $sub)
                                        <option value="{{ $sub->id }}" data-id="{{ $sub->id }}"
                                            {{ old('subcategories', $purchaseProducts[0]['subcategory_id'] ?? '') == $sub->id ? 'selected' : '' }}>
                                            {{ $sub->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <hr>

                        <div class="table-responsive">

                            <table class="table table-bordered" id="product_table">

                                <thead>
                                    <tr>
                                        <th>Sr No</th>
                                        <th>Product</th>
                                        <th>Batch</th>
                                        <th>MFG</th>
                                        <th>MRP</th>
                                        <th>Qty</th>
                                        <th>Rate</th>
                                        <th>Amount</th>
                                        <th class="action-col">Action</th>
                                    </tr>
                                </thead>

                                <tbody id="productBody">

                                    @foreach ($purchaseProducts as $i => $item)
                                        <tr>

                                            <td>{{ $i + 1 }}</td>

                                            <td>

                                                <select name="products[{{ $i }}][product_id]"
                                                    id="product_select_{{ $i }}"
                                                    class="form-control product_select_row">

                                                    <option value="">Select Product</option>

                                                    @foreach ($products as $p)
                                                        <option value="{{ $p['id'] }}"
                                                            {{ $item['product_id'] == $p['id'] ? 'selected' : '' }}>
                                                            {{ $p['name'] }}
                                                        </option>
                                                    @endforeach

                                                </select>

                                                <input type="hidden" name="products[{{ $i }}][brand_name]"
                                                    value="{{ $item['brand_name'] }}" class="brand_name">

                                            </td>

                                            <td>
                                                <input type="text" class="form-control"
                                                    name="products[{{ $i }}][batch]"
                                                    value="{{ $item['batch'] }}">
                                            </td>

                                            <td>
                                                <input type="date" class="form-control"
                                                    name="products[{{ $i }}][mfg_date]"
                                                    value="{{ $item['mfg_date'] }}">
                                            </td>

                                            <td>
                                                <input type="hidden" name="products[{{ $i }}][mrp]"
                                                    value="{{ $item['mrp'] }}">

                                                <input type="number" class="form-control mrp" value="{{ $item['mrp'] }}"
                                                    disabled>
                                            </td>

                                            <td>
                                                <input type="number" class="form-control qnt"
                                                    name="products[{{ $i }}][qnt]" value="{{ $item['qnt'] }}">
                                            </td>

                                            <td>
                                                <input type="number" step="0.01" class="form-control rate"
                                                    name="products[{{ $i }}][rate]"
                                                    value="{{ $item['rate'] }}">
                                            </td>

                                            <td>
                                                <input type="number" class="form-control amount"
                                                    name="products[{{ $i }}][amount]"
                                                    value="{{ $item['amount'] }}">
                                            </td>

                                            <td class="action-col">

                                                <button type="button" class="btn btn-sm btn-danger remove-row">
                                                    Remove
                                                </button>

                                            </td>

                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                        <input type="hidden" name="total" class="total_val" value="{{ $purchase->total }}">

                        <div class="text-end">
                            <h5>Sub Total : <span id="total"></span></h5>
                        </div>

                        <hr>
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="or-detail rounded" id="license-ledger-box">
                                    <div class="p-3">
                                        <h5 class="mb-3">Details For License Ledger</h5>

                                        <div class="form-group">
                                            <label>ITP Value</label>
                                            <h5 id="itp_value">
                                                ₹{{ number_format(old('itp_value', $purchase->itp_value), 2) }}
                                            </h5>

                                            <input type="hidden" name="itp_value" id="itp_value_hidden"
                                                value="{{ old('itp_value', $purchase->itp_value) }}">
                                        </div>

                                        <div class="form-group">
                                            <label>AED TO BE PAID</label>
                                            <input type="number" class="form-control" name="aed_to_be_paid"
                                                id="aed_to_be_paid"
                                                value="{{ old('aed_to_be_paid', $purchase->aed_to_be_paid) }}">
                                        </div>

                                        <div class="form-group">
                                            <label>Guarantee Fulfilled</label>
                                            <input type="number" class="form-control" name="guarantee_fulfilled"
                                                id="guarantee_fulfilled"
                                                value="{{ old('guarantee_fulfilled', $purchase->guarantee_fulfilled) }}">
                                        </div>

                                        <div class="form-group">
                                            <label>Loading Charges (Including Tax)</label>
                                            <input type="number" class="form-control" name="loading_charges"
                                                id="loading_charges"
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
                                            <input type="number" class="form-control" name="permit_fee_excise"
                                                id="permit_fee_excise"
                                                value="{{ old('permit_fee_excise', $purchase->permit_fee_excise) }}">
                                        </div>

                                        <div class="form-group">
                                            <label>Vend Fee</label>
                                            <input type="number" class="form-control" name="vend_fee_excise"
                                                id="vend_fee_excise"
                                                value="{{ old('vend_fee_excise', $purchase->vend_fee_excise) }}">
                                        </div>

                                        <div class="form-group">
                                            <label>Composite Fee (For RTDC Shop)</label>
                                            <input type="number" class="form-control" name="composite_fee_excise"
                                                id="composite_fee_excise"
                                                value="{{ old('composite_fee_excise', $purchase->composite_fee_excise) }}">
                                        </div>
                                    </div>

                                    <div
                                        class="ttl-amt py-2 px-3 d-flex justify-content-between align-items-center border-top">
                                        <h6>Total</h6>
                                        <div>
                                            <input type="hidden" name="excise_total_amount" class="excise_total_amount"
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
                                                <input type="number" class="form-control" name="excise_fee"
                                                    id="excise_fee"
                                                    value="{{ old('excise_fee', $purchase->excise_fee) }}">
                                            </div>

                                            <div class="form-group">
                                                <label>COMPOSITION VAT</label>
                                                <input type="number" class="form-control" name="composition_vat"
                                                    id="composition_vat"
                                                    value="{{ old('composition_vat', $purchase->composition_vat) }}">
                                            </div>

                                            <div class="form-group">
                                                <label>SURCHARGE ON CA</label>
                                                <input type="number" class="form-control" name="surcharge_on_ca"
                                                    id="surcharge_on_ca"
                                                    value="{{ old('surcharge_on_ca', $purchase->surcharge_on_ca) }}">
                                            </div>
                                        </div>

                                        {{-- Vendor 2 --}}
                                        <div id="vendor-2-fields" class="vendor-fields d-none">
                                            <div class="form-group">
                                                <label>VAT</label>
                                                <input type="number" class="form-control" name="vat" id="vat"
                                                    value="{{ old('vat', $purchase->vat) }}">
                                            </div>

                                            <div class="form-group">
                                                <label>SURCHARGE ON VAT</label>
                                                <input type="number" class="form-control" name="surcharge_on_vat"
                                                    id="surcharge_on_vat"
                                                    value="{{ old('surcharge_on_vat', $purchase->surcharge_on_vat) }}">
                                            </div>

                                            <div class="form-group">
                                                <label>BLF</label>
                                                <input type="number" class="form-control" name="blf" id="blf"
                                                    value="{{ old('blf', $purchase->blf) }}">
                                            </div>

                                            <div class="form-group">
                                                <label>Permit Fee</label>
                                                <input type="number" class="form-control" name="permit_fee"
                                                    id="permit_fee"
                                                    value="{{ old('permit_fee', $purchase->permit_fee) }}">
                                            </div>
                                        </div>

                                        {{-- Common --}}
                                        <div class="vendor-common">
                                            <div class="form-group">
                                                <label>TCS</label>
                                                <input type="number" class="form-control" name="tcs" id="tcs"
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

                                                <input type="hidden" name="total_amount" class="total_amount"
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


                        <button type="submit" class="btn btn-primary">
                            Update Purchase
                        </button>

                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    let productOptions = `
            <option value="">Select Product</option>
            @foreach ($products as $p)
            <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
            @endforeach
            `;
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

        // ✅ update ITP value
        $('#itp_value').text('₹' + grandTotal);
        $('#itp_value_hidden').val(grandTotal);
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

    function refreshButtons() {

        $('#product_table tbody tr').each(function(index) {

            const actionCell = $(this).find('.action-col');

            if (index == $('#product_table tbody tr').length - 1) {

                actionCell.html(
                    '<button type="button" class="btn btn-success btn-sm add-row">Add Product</button>'
                );

            } else {

                actionCell.html(
                    '<button type="button" class="btn btn-danger btn-sm remove-row">Remove</button>'
                );

            }

        });

    }

    $(document).on('change', '.product_select_row', function() {

        const product_id = $(this).val();
        const row = $(this).closest('tr');

        $.ajax({

            url: "/vendor/get-product-details/" + product_id,
            type: "GET",

            success: function(data) {

                row.find('.brand_name').val(data.name);
                row.find('input[name*="[batch]"]').val(data.batch_no);
                row.find('input[name*="[mfg_date]"]').val(data.mfg_date);
                row.find('.mrp').val(data.mrp);
                row.find('.rate').val(data.cost_price);

                let qty = row.find('.qnt').val() || 1;

                row.find('.amount').val(qty * data.cost_price);

                calculateProductTotals();

            }

        });

    });

    $(document).on('click', '.add-row', function() {

        const row = $(this).closest('tr');

        const product = row.find('.product_select_row').val();

        if (!product) {

            alert('Please select product first');

            return;

        }

        addEmptyRow();

        refreshButtons();

    });

    $(document).on('click', '.remove-row', function() {

        $(this).closest('tr').remove();

        updateSrNo();

        refreshButtons();

        calculateProductTotals();

        updateBillingTotal();

    });


    // ---------- MAIN READY ----------

    $(document).ready(function() {

        refreshButtons();

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
                    <td>
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
                        <input type="number" step="1" name="products[${srNo - 1}][rate]" class="form-control" value="${rate}">
                    </td>
                    <td>
                        <input type="number" step="1" name="products[${srNo - 1}][amount]" class="form-control" value="${amount}">
                    </td>
                    <td class="action-col">
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
        $(document).on('blur', 'input[name*="[qnt]"], input[name*="[rate]"]', function() {
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
        $(document).on('blur', 'input[name*="[amount]"]', function() {
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



        // vendor change -> onVendorChange + auto sync ledger
        $('#vendor_id').on('change', function() {
            const vendorId = $(this).val();

            onVendorChange(vendorId); // your existing logic
            filterSubcategoriesByVendor(vendorId); // ✅ subcategory logic

            // Auto-sync purchase ledger
            $('#parchase_ledger').val(vendorId);
        });

        $('#subcategories').on('change', function() {

            const subcatId = $(this).val();

            if (!subcatId) return;

            $.ajax({
                url: "/subcategory/" + subcatId + "/products",
                type: "GET",
                dataType: "json",
                success: function(products) {

                    productOptions = '<option value="">Select Product</option>';

                    products.forEach(function(p) {
                        productOptions +=
                            `<option value="${p.id}">${p.name}</option>`;
                    });

                    // update only LAST row
                    let lastRow = $('#product_table tbody tr:last');
                    let select = lastRow.find('.product_select_row');

                    select.html(productOptions);

                }
            });

        });

    });

    // Initial on page load (after validation error)
    document.addEventListener('DOMContentLoaded', function() {

        const totalAmount = "{{ old('total_amount', $purchase->total_amount) }}";

        if (totalAmount) {
            $('#itp_value').text('₹' + parseFloat(totalAmount));
            $('#itp_value_hidden').val(parseFloat(totalAmount));
        }
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

    function addEmptyRow() {

        const rowIndex = $('#product_table tbody tr').length;

        const row = `
        <tr>

            <td>${rowIndex + 1}</td>

            <td>

                <select name="products[${rowIndex}][product_id]"
                id="product_select_${rowIndex}"
                class="form-control product_select_row">

                ${productOptions}

                </select>

                <input type="hidden"
                name="products[${rowIndex}][brand_name]"
                class="brand_name">

            </td>

            <td>
                <input type="text" name="products[${rowIndex}][batch]" class="form-control">
            </td>

            <td>
                <input type="date" name="products[${rowIndex}][mfg_date]" class="form-control">
            </td>

            <td>
                <input type="hidden" name="products[${rowIndex}][mrp]" class="mrp_hidden">
                <input type="number" class="form-control mrp" disabled>
            </td>

            <td>
                <input type="number" name="products[${rowIndex}][qnt]" class="form-control qnt" value="1">
            </td>

            <td>
                <input type="number" name="products[${rowIndex}][rate]" step="0.01" class="form-control rate">
            </td>

            <td>
                <input type="number" name="products[${rowIndex}][amount]" class="form-control amount">
            </td>

            <td class="action-col">
                <button type="button" class="btn btn-success btn-sm add-row">Add Product</button>
            </td>

        </tr>
        `;

        $('#product_table tbody').append(row);

    }
</script>
