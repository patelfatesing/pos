@extends('layouts.backend.layouts')
@section('page-content')
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
    @if (session('warehouse_error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Warehouse Closed',
                text: "{{ session('warehouse_error') }}",
                confirmButtonColor: '#d33'
            });
        </script>
    @endif
    <!-- Wrapper Start -->

    <div class="content-page">
        <div class="container-fluid">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h4 class="mb-0">Purchase Invoice</h4>
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
                                    <form action="{{ route('purchase.store') }}" method="POST"
                                        enctype="multipart/form-data" novalidate>
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="bill_no" class="form-label">Bill No</label>
                                                <input type="text" class="form-control" id="bill_no" name="bill_no"
                                                    value="{{ old('bill_no') }}">
                                                @error('bill_no')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-4">
                                                <label for="date" class="form-label">Date</label>
                                                <input type="date" class="form-control" id="date" name="date"
                                                    value="{{ old('date') }}" max="{{ now()->toDateString() }}">
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
                                                            <option value="{{ $vendor->id }}"
                                                                {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                                                {{ $vendor->name }}
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
                                                    <label for="vendor_new_id">Vendor Ledger Name</label>
                                                    <select name="vendor_new_id" id="vendor_new_id" class="form-control">
                                                        <option value="">-- Select Ledger Name --</option>
                                                        @foreach ($ledgersAll as $vendor)
                                                            <option value="{{ $vendor->id }}"
                                                                {{ old('vendor_new_id') == $vendor->id ? 'selected' : '' }}>
                                                                {{ $vendor->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('vendor_new_id')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <a href="{{ route('accounting.ledgers.create', 'purchase') }}"
                                                    class="btn btn-outline-secondary btn-sm">
                                                    Create Ledger
                                                </a>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="parchase_ledger">Purchase Ledger</label>
                                                    <select name="parchase_ledger" id="parchase_ledger"
                                                        class="form-control">
                                                        <option value="">-- Select Ledger --</option>
                                                        @foreach ($ledgers as $ven)
                                                            <option value="{{ $ven->id }}"
                                                                {{ old('parchase_ledger') == $ven->id ? 'selected' : '' }}>
                                                                {{ $ven->name }}
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
                                                    <label for="subcategories">Sub Category</label>
                                                    <select name="subcategories" id="subcategories" class="form-control">
                                                        <option value="">-- Select Sub Category --</option>
                                                        @foreach ($subcategories as $subcat)
                                                            <option value="{{ $subcat->id }}"
                                                                data-id="{{ $subcat->id }}"
                                                                {{ old('subcategories') == $subcat->id ? 'selected' : '' }}>
                                                                {{ $subcat->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('subcategories')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                        </div>

                                        <hr />

                                        {{-- PRODUCTS TABLE --}}
                                        <div class="table-responsive mb-3">
                                            <table class="table table-bordered" id="product_table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Sr No</th>
                                                        <th>Product</th>
                                                        <th>Batch</th>
                                                        <th>MFG Date</th>
                                                        <th>MRP Rate</th>
                                                        <th>Qty</th>
                                                        <th>Cost Price</th>
                                                        <th>Amount</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="productBody">
                                                    @if (old('products'))
                                                        @foreach (old('products') as $i => $product)
                                                            <tr>
                                                                <td>{{ $i + 1 }}</td>
                                                                <td style="width:25%">
                                                                    <select id="product_select_{{ $i }}"
                                                                        name="products[{{ $i }}][product_id]"
                                                                        class="form-control product_select_row">
                                                                        <option value="">Select Product</option>
                                                                        @foreach ($products as $p)
                                                                            <option value="{{ $p['id'] }}"
                                                                                {{ $product['product_id'] == $p['id'] ? 'selected' : '' }}>
                                                                                {{ $p['name'] }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>


                                                                    <input type="hidden"
                                                                        name="products[{{ $i }}][brand_name]"
                                                                        value="{{ $product['brand_name'] }}"
                                                                        class="brand_name">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control"
                                                                        name="products[{{ $i }}][batch]"
                                                                        value="{{ $product['batch'] }}">
                                                                    @error("products.$i.batch")
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <input type="date" class="form-control"
                                                                        name="products[{{ $i }}][mfg_date]"
                                                                        value="{{ $product['mfg_date'] }}">
                                                                    @error("products.$i.mfg_date")
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <input type="hidden"
                                                                        name="products[{{ $i }}][mrp]"
                                                                        value="{{ $product['mrp'] }}">
                                                                    <input type="number" class="form-control mrp"
                                                                        value="{{ $product['mrp'] }}" disabled>
                                                                    @error("products.$i.mrp")
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <input type="number" class="form-control qnt"
                                                                        name="products[{{ $i }}][qnt]"
                                                                        value="{{ $product['qnt'] }}">
                                                                    @error("products.$i.qnt")
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <input type="number" class="form-control rate"
                                                                        name="products[{{ $i }}][rate]"
                                                                        value="{{ $product['rate'] }}">
                                                                    @error("products.$i.rate")
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <input type="number" class="form-control amount"
                                                                        name="products[{{ $i }}][amount]"
                                                                        value="{{ $product['amount'] }}">
                                                                    @error("products.$i.amount")
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-danger remove">
                                                                        Remove
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>

                                        <input type="hidden" name="total" class="total_val" value="" />

                                        <div class="row">
                                            <div class="offset-lg-8 col-lg-4">
                                                <div class="rounded">
                                                    <div class="p-3">
                                                        <span class="mr-4">Sub Total: </span>
                                                        <input hidden class="total_amt">
                                                        <span class="pull-right" id="total"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <hr />

                                        {{-- BOTTOM THREE COLUMNS --}}
                                        <div class="row mt-4 mb-3">
                                            {{-- LEFT: LICENSE LEDGER --}}
                                            <div class="col-lg-4" id="">
                                                <div class="or-detail rounded" id="license-ledger-box">
                                                    <div class="p-3">
                                                        <h5 class="mb-3">Details For License Ledger</h5>
                                                        <div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label>ITP Value: </label>
                                                                        <span id="itp_value"></span>
                                                                        <input type="hidden" name="itp_value"
                                                                            id="itp_value_hidden">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>AED TO BE PAID</label>
                                                                        <input type="number" class="form-control"
                                                                            value="{{ old('aed_to_be_paid') }}"
                                                                            name="aed_to_be_paid" id="aed_to_be_paid" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Guarantee Fulfilled</label>
                                                                        <input type="number" class="form-control"
                                                                            value="{{ old('guarantee_fulfilled') }}"
                                                                            name="guarantee_fulfilled"
                                                                            id="guarantee_fulfilled" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label>Loading Charges (Including Tax)</label>
                                                                        <input type="number" class="form-control"
                                                                            value="{{ old('loading_charges') }}"
                                                                            name="loading_charges" id="loading_charges" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- MIDDLE: EXCISE FEE BOX (vendor 1) --}}
                                            <div class="col-lg-4 excise-section d-none" id="">
                                                <div class="or-detail rounded">
                                                    <div class="p-3">
                                                        <h5 class="mb-3">Excise Fee</h5>
                                                        <div>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Permit Fee</label>
                                                                        <input type="number" class="form-control"
                                                                            name="permit_fee_excise"
                                                                            id="permit_fee_excise"
                                                                            value="{{ old('permit_fee_excise') }}" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Vend Fee</label>
                                                                        <input type="number" class="form-control"
                                                                            name="vend_fee_excise" id="vend_fee_excise"
                                                                            value="{{ old('vend_fee_excise') }}" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label>Composite Fee (For RTDC Shop)</label>
                                                                        <input type="number" class="form-control"
                                                                            name="composite_fee_excise"
                                                                            id="composite_fee_excise"
                                                                            value="{{ old('composite_fee_excise') }}" />
                                                                    </div>
                                                                </div>
                                                                <!-- ✅ NEW 80/20 (ONLY VENDOR 2) -->
                                                                <div class="col-md-6 excise-duty-80-20 d-none">
                                                                    <div class="form-group">
                                                                        <label>Excise Duty 80%</label>
                                                                        <input type="number" step="0.01"
                                                                            class="form-control" name="excise_duty_80"
                                                                            id="excise_duty_80"
                                                                            value="{{ old('excise_duty_80') }}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-6 excise-duty-80-20 d-none">
                                                                    <div class="form-group">
                                                                        <label>Excise Duty 20%</label>
                                                                        <input type="number" step="0.01"
                                                                            class="form-control" name="excise_duty_20"
                                                                            id="excise_duty_20"
                                                                            value="{{ old('excise_duty_20') }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="ttl-amt py-2 px-3 d-flex justify-content-between align-items-center border-top">
                                                        <h6>Total</h6>
                                                        <div>
                                                            <input type="hidden" name="excise_total_amount"
                                                                class="excise_total_amount"
                                                                value="{{ old('excise_total_amount') }}" />
                                                            <h3 class="text-primary font-weight-700"
                                                                id="excise_total_amount">
                                                                @if (old('excise_total_amount'))
                                                                    ₹{{ number_format(old('excise_total_amount'), 2) }}
                                                                @endif
                                                            </h3>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- RIGHT: BILLING DETAILS (must be right side) --}}
                                            <div class="col-lg-4 ml-auto" id="billing-column">
                                                <div class="or-detail rounded">
                                                    <div class="p-3">
                                                        <h5 class="mb-3">Billing Details</h5>

                                                        {{-- Vendor 1 --}}
                                                        <div id="vendor-1-fields" class="vendor-fields d-none vendor-1">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>EXCISE FEE</label>
                                                                        <input type="number" class="form-control"
                                                                            value="{{ old('excise_fee') }}"
                                                                            name="excise_fee" id="excise_fee" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>COMPOSITION VAT</label>
                                                                        <input type="number" class="form-control"
                                                                            value="{{ old('composition_vat') }}"
                                                                            name="composition_vat" id="composition_vat" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label>SURCHARGE ON CA</label>
                                                                        <input type="number" class="form-control"
                                                                            value="{{ old('surcharge_on_ca') }}"
                                                                            name="surcharge_on_ca" id="surcharge_on_ca" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        {{-- Vendor 2 --}}
                                                        <div id="vendor-2-fields" class="vendor-fields d-none vendor-2">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>VAT</label>
                                                                        <input type="number" id="vat"
                                                                            value="{{ old('vat') }}"
                                                                            class="form-control" name="vat" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>SURCHARGE ON VAT</label>
                                                                        <input type="number" id="surcharge_on_vat"
                                                                            value="{{ old('surcharge_on_vat') }}"
                                                                            class="form-control"
                                                                            name="surcharge_on_vat" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>BLF</label>
                                                                        <input type="number" id="blf"
                                                                            value="{{ old('blf') }}"
                                                                            class="form-control" name="blf" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Permit Fee</label>
                                                                        <input type="number" class="form-control"
                                                                            value="{{ old('permit_fee') }}"
                                                                            name="permit_fee" id="permit_fee" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            {{-- <div class="form-group">
                                                                    <label>RSGSM Purchase</label>
                                                                    <input type="number" class="form-control"
                                                                        value="{{ old('rsgsm_purchase') }}"
                                                            name="rsgsm_purchase" id="rsgsm_purchase" />
                                                        </div> --}}
                                                        </div>

                                                        {{-- Common for vendor 1 & 2 --}}
                                                        <div class="vendor-common">
                                                            <div class="form-group">
                                                                <label>TCS</label>
                                                                <input type="number" id="tcs"
                                                                    value="{{ old('tcs') }}" class="form-control"
                                                                    name="tcs" />
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Other vendors: cash purchase --}}
                                                    <div id="vendor-others-fields" class="vendor-fields d-none">
                                                        <div class="ttl-amt py-2 px-3 d-flex justify-content-between align-items-center border border-danger"
                                                            style="background-color: #fdf1f7;">
                                                            <div>
                                                                <strong class="d-block">CASH PURCHASE</strong>
                                                                <div class="d-flex align-items-center">
                                                                    <label class="mr-1 mb-0">(-)</label>
                                                                    <input type="float"
                                                                        class="form-control form-control-sm pur_dis"
                                                                        placeholder="%" name="case_purchase_per"
                                                                        style="width: 80px;" min="0"
                                                                        max="100"
                                                                        value="{{ old('case_purchase_per') }}">
                                                                    <span class="ml-1">%</span>
                                                                </div>
                                                            </div>
                                                            <div class="text-right d-flex align-items-center">
                                                                <label class="mr-1 mb-0">(-)</label>
                                                                <input type="float" name="case_purchase_amt"
                                                                    class="form-control form-control-sm pur_amt text-danger font-weight-bold"
                                                                    placeholder="Amount" style="width: 120px;"
                                                                    min="0"
                                                                    value="{{ old('case_purchase_amt') }}">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div
                                                        class="ttl-amt py-2 px-3 d-flex justify-content-between align-items-center border-top">
                                                        <h6>Total Amount</h6>
                                                        <div>
                                                            <input type="hidden" name="total_amount"
                                                                class="total_amount" value="{{ old('total_amount') }}" />
                                                            <h3 class="text-primary font-weight-700" id="total_amount">
                                                                @if (old('total_amount'))
                                                                    ₹{{ number_format(old('total_amount'), 2) }}
                                                                @endif
                                                            </h3>
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

    <!-- Wrapper End -->


    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // ---------- HELPERS (GLOBAL) ----------

        function calculateProductTotals() {

            let total = 0;

            $('#product_table tbody tr').each(function() {

                const $row = $(this);

                const rate = parseFloat($row.find('input[name*="[rate]"]').val()) || 0;
                const qty = parseFloat($row.find('input[name*="[qnt]"]').val()) || 0;

                const amount = round2(rate * qty);

                $row.find('input[name*="[amount]"]').val(amount.toFixed(2));

                total += amount;
            });

            $('#total').text(Math.round(total));
            $(".total_amt").val(total);
            $('.total_val').val(total);

            return total;
        }

        function updateExciseSection() {
            const permit = parseInt($('#permit_fee_excise').val()) || 0;
            const vend = parseInt($('#vend_fee_excise').val()) || 0;
            const composite = parseInt($('#composite_fee_excise').val()) || 0;

            const totalExcise = permit + vend + composite;

            $('#excise_total_amount').text('₹' + totalExcise);
            $('.excise_total_amount').val(totalExcise);

            // Push to Billing Details -> EXCISE FEE
            $('#excise_fee').val(totalExcise);

            updateBillingTotal();
        }

        function updateBillingTotal() {
            const baseTotal = parseInt($(".total_amt").val()) || 0;

            const excise = parseInt($('#excise_fee').val()) || 0;
            const compVat = parseInt($('#composition_vat').val()) || 0;
            const surcharge = parseInt($('#surcharge_on_ca').val()) || 0;
            const tcs = parseInt($('#tcs').val()) || 0;
            const vat = parseInt($('#vat').val()) || 0;
            const surcharge_on_vat = parseInt($('#surcharge_on_vat').val()) || 0;
            const blf = parseInt($('#blf').val()) || 0;
            const permit_fee = parseInt($('#permit_fee').val()) || 0;
            const rsgsm_purchase = parseInt($('#rsgsm_purchase').val()) || 0;
            const aed = parseInt($('#aed_to_be_paid').val()) || 0;
            const loading = parseInt($('#loading_charges').val()) || 0; // ✅ NEW
            const excise80 = parseFloat($('#excise_duty_80').val()) || 0;
            const excise20 = parseFloat($('#excise_duty_20').val()) || 0;

            // let additionalCharges =
            //     excise +
            //     compVat +
            //     surcharge +
            //     tcs +
            //     aed +
            //     loading +
            //     vat +
            //     surcharge_on_vat +
            //     blf +
            //     permit_fee +
            //     rsgsm_purchase +
            //     excise20 - // ✅ ADD
            //     excise80; // ❌ SUBTRACT

            let additionalCharges =
                excise +
                compVat +
                surcharge +
                tcs +
                aed +
                loading +
                vat +
                surcharge_on_vat +
                rsgsm_purchase +
                excise20; // ❌ SUBTRACT

            let grandTotal = baseTotal + additionalCharges;

            const discountPercent = parseInt($('.pur_dis').val()) || 0;
            const discountAmount = parseInt($('.pur_amt').val()) || 0;

            if (discountPercent > 0) {
                const discount = (grandTotal * discountPercent) / 100;
                grandTotal -= discount;
                $('.pur_amt').val(discount);
            } else if (discountAmount > 0) {
                grandTotal -= discountAmount;
                if (grandTotal > 0) {
                    $('.pur_dis').val(((discountAmount / (grandTotal + discountAmount)) * 100));
                }
            }

            $('#total_amount').text('₹' + grandTotal);
            $('.total_amount').val(grandTotal);

            // ✅ SET ITP VALUE SAME AS TOTAL
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
            $('.excise-duty-80-20').addClass('d-none');

            // Show excise for vendor 1 & 2
            if (vendorId === '1' || vendorId === '2') {
                $('#license-ledger-box').removeClass('d-none');
                $('.excise-section').removeClass('d-none');
            } else {
                $('#license-ledger-box').addClass('d-none');
            }

            if (vendorId === '1') {

                $('#vendor-1-fields').removeClass('d-none');
                $('.vendor-common').show();

                updateExciseSection();

            } else if (vendorId === '2') {

                $('#vendor-2-fields').removeClass('d-none');
                $('.vendor-common').show();

                // ✅ show only vendor 2
                $('.excise-duty-80-20').removeClass('d-none');

            } else if (vendorId) {

                $('#vendor-others-fields').removeClass('d-none');

            }

            calculateProductTotals();
            updateBillingTotal();
        }

        function addEmptyRow() {

            const rowIndex = $('#product_table tbody tr').length;

            const row = `
                <tr>        

                    <td>${rowIndex + 1}</td>

                    <td style="width:25%">
                        <select name="products[${rowIndex}][product_id]" id="product_select_${rowIndex}" class="form-control product_select_row">
                            <option value="">Select Product</option>
                        </select>

                        <input type="hidden" name="products[${rowIndex}][brand_name]" class="brand_name">
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
                        <input type="number" name="products[${rowIndex}][rate]" class="form-control rate">
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

            refreshButtons();

            loadProductsForNewRow();
        }

        $(document).on('change', '.product_select_row', function() {

            const product_id = $(this).val();
            const row = $(this).closest('tr');

            $(this).data('selected', product_id);

            if (!product_id) return;

            $.ajax({
                url: "/vendor/get-product-details/" + product_id,
                type: "GET",
                dataType: "json",
                success: function(data) {

                    row.find('.brand_name').val(data.name);

                    row.find('input[name*="[batch]"]').val(data.batch_no);
                    row.find('input[name*="[mfg_date]"]').val(data.mfg_date);

                    row.find('.mrp').val(data.mrp);
                    row.find('.mrp_hidden').val(data.mrp);

                    row.find('.rate').val(data.cost_price);

                    const qty = row.find('.qnt').val() || 1;
                    const amount = round2(qty * data.cost_price);
                    row.find('.amount').val(amount.toFixed(2));

                    calculateProductTotals();
                    updateBillingTotal();
                }
            });

        });

        $(document).on('click', '.add-row', function() {

            const row = $(this).closest('tr');

            const product = row.find('.product_select_row').val();

            if (!product) {

                alert('Please select product first');

                row.find('.product_select_row').focus();

                return;
            }

            addEmptyRow();

        });

        function refreshButtons() {

            $('#product_table tbody tr').each(function(index) {

                const actionCell = $(this).find('.action-col');

                if (index === $('#product_table tbody tr').length - 1) {

                    actionCell.html(
                        '<button type="button" class="btn btn-success btn-sm add-row">Add Product</button>');

                } else {

                    actionCell.html(
                        '<button type="button" class="btn btn-danger btn-sm remove-row">Remove</button>');

                }

            });

        }

        $(document).on('click', '.remove-row', function() {

            $(this).closest('tr').remove();

            updateSrNo();
            refreshButtons();
            calculateProductTotals();
            updateBillingTotal();

        });

        function validateLastRow() {

            const lastRow = $('#product_table tbody tr:last');

            const product = lastRow.find('.product_select_row').val();

            return product !== '';
        }

        // Subcategory -> products
        // Subcategory -> products
        $('#subcategories').on('change', function() {

            const subcatId = $(this).val();
            if (!subcatId) return;

            $.ajax({
                url: "/subcategory/" + subcatId + "/products",
                type: "GET",
                dataType: "json",
                success: function(products) {

                    let options = '<option value="">Select Product</option>';

                    products.forEach(function(p) {
                        options += `<option value="${p.id}">${p.name}</option>`;
                    });

                    // ONLY update the last added row
                    const lastRow = $('#product_table tbody tr:last');
                    lastRow.find('.product_select_row').html(options);

                }
            });

        });

        // ---------- MAIN READY ----------

        $(document).ready(function() {
            $(document).on('keydown', 'input, select', function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    return false;
                }
            });

            if ($('#product_table tbody tr').length === 0) {
                addEmptyRow();
            }
            // let srNo = $('#productBody tr').length ? $('#productBody tr').length + 1 : 1;
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

                const brand = data.id ?? '';
                const brandVal = data.name ?? '';
                const batch = data.batch_no ?? '';
                const mfg = data.mfg_date ?? '';
                const mrp = formatNumber(data.mrp) ?? 0;
                const rate = formatNumber(data.cost_price) ?? 0;
                const qty = 1;
                const amount = round2(rate * qty);

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
                    let existingQty = parseInt(qtyInput.val()) || 0;
                    const newQty = existingQty + 1;
                    qtyInput.val(newQty);

                    calculateProductTotals();
                    updateBillingTotal();
                    return;
                }

                // 🔥 ALWAYS GET CURRENT ROW COUNT
                const rowIndex = $('#product_table tbody tr').length;

                const row = `
                <tr>
                    <td>${rowIndex + 1}</td>
                    <input type="hidden" name="products[${rowIndex}][brand_name]" id="product_select_${rowIndex}" class="form-control" value="${brandVal}">
                    <td style="width:25%">
                        <span>${brandVal}</span>
                    </td>
                    <td>
                        <input type="text" name="products[${rowIndex}][batch]" class="form-control" value="${batch}">
                    </td>
                    <td>
                        <input type="date" name="products[${rowIndex}][mfg_date]" class="form-control" value="${mfg ?? ''}">
                    </td>
                    <td>
                        <input type="hidden" name="products[${rowIndex}][mrp]" value="${mrp}">
                        <input type="number" class="form-control" value="${mrp}" disabled>
                    </td>
                    <td>
                        <input type="number" name="products[${rowIndex}][qnt]" class="form-control" value="${qty}" min="1">
                    </td>
                    <td>
                        <input type="number"  name="products[${rowIndex}][rate]" class="form-control" value="${rate}">
                    </td>
                    <td>
                        <input type="number"  name="products[${rowIndex}][amount]" class="form-control" value="${amount}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove">Remove</button>
                    </td>
                </tr>
                `;

                $('#product_table tbody').append(row);

                calculateProductTotals();
                updateBillingTotal();
            }

            // Remove row
            $(document).on('click', '.remove', function() {
                $(this).closest('tr').remove();
                updateSrNo();
                calculateProductTotals();
                updateBillingTotal();

                if ($('#productBody tr').length === 0) {
                    $('#excise_fee, #composition_vat, #surcharge_on_ca, #aed_to_be_paid').val('');
                    $('#vat, #surcharge_on_vat, #blf, #permit_fee, #rsgsm_purchase').val('');
                    $('.pur_dis, .pur_amt').val('');
                    $('#tcs').val('');
                    $('#total_amount').text('₹0');
                    $('.total_amount').val('0');
                    $('#excise_total_amount').text('₹0');
                    $('.excise_total_amount').val('0');
                }
            });

            // qty / rate change
            $(document).on('blur', 'input[name*="[qnt]"], input[name*="[rate]"]', function() {

                const $row = $(this).closest('tr');

                const qty = parseFloat($row.find('input[name*="[qnt]"]').val()) || 0;
                const rate = parseFloat($row.find('input[name*="[rate]"]').val()) || 0;

                const amount = qty * rate;

                $row.find('input[name*="[amount]"]').val(amount.toFixed(2));

                calculateProductTotals();
                updateBillingTotal();
            });

            // Amount change -> recalc rate
            $(document).on('blur', 'input[name*="[amount]"]', function() {

                const $row = $(this).closest('tr');

                const amount = parseFloat($(this).val()) || 0;
                const qty = parseFloat($row.find('input[name*="[qnt]"]').val()) || 1;

                const rate = qty > 0 ? amount / qty : 0;

                // ✅ Update ONLY rate
                $row.find('input[name*="[rate]"]').val(rate.toFixed(4));

                // ❌ DO NOT CHANGE AMOUNT AGAIN

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
                let originalAmount = $(".total_val").val() || 0;
                originalAmount = parseInt(originalAmount) || 0;

                let percent = parseInt($('.pur_dis').val()) || 0;
                let discount = (originalAmount * percent) / 100;

                $('.pur_amt').val(discount);

                let ta = originalAmount - discount;
                $('#total_amount').text('₹' + ta);
                $('.total_amount').val(ta);
            }

            function updateFromAmount() {
                let originalAmount = $(".total_val").val() || 0;
                originalAmount = parseInt(originalAmount) || 0;

                let amount = parseInt($('.pur_amt').val()) || 0;
                let percent = originalAmount > 0 ? (amount / originalAmount) * 100 : 0;

                $('.pur_dis').val(percent);

                let ta = originalAmount - amount;
                $('#total_amount').text('₹' + ta);
                $('.total_amount').val(ta);
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

            // Barcode Enter / Scan
            $('#product_barcode').on('keydown', function(e) {

                if (e.which === 13) {
                    e.preventDefault();

                    const barcode = $(this).val().trim();
                    if (!barcode) return;

                    $.ajax({
                        url: "{{ url('/vendor/get-product-by-barcode') }}/" + barcode,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {

                            if (!data || !data.id) {
                                alert('Product not found for this barcode.');
                                return;
                            }

                            addProduct(data); // ✅ now accessible
                            $('#product_barcode').val('');
                            $('#product_barcode').focus(); // optional auto-focus
                        },
                        error: function() {
                            alert('Invalid barcode or product not found.');
                        }
                    });
                }
            });

        });

        // Initial on page load (after validation error)
        document.addEventListener('DOMContentLoaded', function() {

            $('#itp_value').text($('#total_amount').text());
            $('#itp_value_hidden').val($('.total_amount').val());
            calculateProductTotals();
            updateBillingTotal();

            const oldVendorId = "{{ old('vendor_id') }}";

            if (oldVendorId) {
                onVendorChange(oldVendorId);
                filterSubcategoriesByVendor(oldVendorId);
            } else {
                $('#license-ledger-box-div').addClass('d-none');
            }
        });

        function updateSrNo() {
            $('#product_table tbody tr').each(function(index) {

                $(this).find('td:first').text(index + 1);

                const select = $(this).find('.product_select_row');
                select.attr('id', 'product_select_' + index);

                $(this).find('input, select').each(function() {

                    const name = $(this).attr('name');

                    if (name) {
                        const newName = name.replace(/products\[\d+\]/, 'products[' + index + ']');
                        $(this).attr('name', newName);
                    }

                });

            });
        }

        function formatNumber(num) {
            num = parseFloat(num) || 0;
            return Number.isInteger(num) ? num : num.toFixed(2);
        }

        function loadProductsForNewRow() {

            const subcatId = $('#subcategories').val();
            if (!subcatId) return;

            $.ajax({
                url: "/subcategory/" + subcatId + "/products",
                type: "GET",
                dataType: "json",
                success: function(products) {

                    let options = '<option value="">Select Product</option>';

                    products.forEach(function(p) {
                        options += `<option value="${p.id}">${p.name}</option>`;
                    });

                    const lastRow = $('#product_table tbody tr:last');

                    lastRow.find('.product_select_row').html(options);

                }
            });
        }

        $(document).on('input', '#excise_duty_80, #excise_duty_20', function() {
            updateBillingTotal();
        });

        function round2(num) {
            return Math.round((parseFloat(num) || 0) * 100) / 100;
        }
    </script>

@endsection
