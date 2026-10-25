@extends('layouts.backend.layouts')

@section('page-content')
    <style>
        #product_table th,
        #product_table td {
            vertical-align: middle;
            padding: 0.6rem 0.5rem;
        }

        #product_table thead th {
            white-space: nowrap;
        }

        #product_table th:nth-child(1),
        #product_table td:nth-child(1) {
            width: 40px;
            min-width: 40px;
            text-align: center;
        }

        #product_table th:nth-child(2),
        #product_table td:nth-child(2) {
            width: 130px;
            min-width: 110px;
            max-width: 330px;
            overflow: hidden;
            position: relative;
            z-index: 5;
        }

        #product_table .product_select_row {
            width: 100% !important;
            max-width: 100% !important;
            background-color: #ffffff !important;
            display: block;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
            box-sizing: border-box;
        }

        #product_table .product_select_row:focus {
            width: 100% !important;
            max-width: 100% !important;
            z-index: 9999;
            box-shadow: none;
        }

        #product_table .product_select_row option {
            white-space: normal;
            padding: 8px 10px;
            background-color: #ffffff;
            color: #333333;
        }

        #product_table th:nth-child(3),
        #product_table td:nth-child(3) { width: 70px; min-width: 70px; } /* Batch */
        #product_table th:nth-child(4),
        #product_table td:nth-child(4) { width: 130px; min-width: 130px; } /* MFG Date */
        #product_table th:nth-child(5),
        #product_table td:nth-child(5) { width: 70px; min-width: 70px; } /* MRP Rate */
        #product_table th:nth-child(6),
        #product_table td:nth-child(6) { width: 70px; min-width: 70px; } /* Qty */
        #product_table th:nth-child(7),
        #product_table td:nth-child(7) { width: 90px; min-width: 90px; } /* Cost Price */
        #product_table th:nth-child(8),
        #product_table td:nth-child(8) { width: 90px; min-width: 90px; } /* Amount */

        #product_table th:nth-child(9),
        #product_table td:nth-child(9),
        #product_table td.action-col {
            width: 50px !important;
            min-width: 50px !important;
            text-align: center;
        }

        #product_table .form-control {
            min-width: 0;
            width: 100%;
            padding: 0.375rem 0.5rem;
            font-size: 0.9rem;
        }

        #product_table .form-control[type="date"] {
            min-width: 135px;
        }

        #product_table .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.875rem;
            white-space: nowrap;
        }

        #product_table .text-danger {
            display: block;
            font-size: 0.75rem;
            margin-top: 0.2rem;
            white-space: normal;
        }

        .ledger-info {
            font-size: 11px;
            color: #6c757d;
            font-style: italic;
        }

        .btn-icon-action {
            width: 32px;
            height: 32px;
            padding: 0;
            border-radius: 50%;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border: none;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s, opacity 0.15s;
            flex-shrink: 0;
            line-height: 1 !important;
            vertical-align: middle;
        }

        .btn-icon-action svg {
            pointer-events: none;
            display: block !important;
            flex-shrink: 0;
            fill: #fff;
        }

        .btn-icon-action:hover {
            transform: scale(1.12);
            box-shadow: 0 2px 8px rgba(0,0,0,0.18);
            opacity: 0.92;
        }

        .btn-add-row {
            background-color: #198754;
            color: #fff;
        }

        .btn-remove-row {
            background-color: #dc3545;
            color: #fff;
        }

        @media (max-width: 767.98px) {
            .card-body form .row > div {
                margin-bottom: 1rem;
            }

            .product-table-wrapper::before {
                content: "← Scroll horizontally to view table items →";
                display: block;
                text-align: center;
                font-size: 11px;
                color: #ff9800;
                background-color: #fff3cd;
                padding: 4px;
                font-weight: 500;
                border-bottom: 1px solid #ffeeba;
            }
        }
    </style>

    <div class="content-page">
        <div class="container-fluid">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <div>
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
                                    <form action="{{ route('purchase.update', $purchase->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="bill_no" class="form-label">Bill No</label>
                                                    <input type="text" class="form-control" id="bill_no" name="bill_no"
                                                        value="{{ old('bill_no', $purchase->bill_no) }}">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="date" class="form-label">Date</label>
                                                    <input type="date" class="form-control" id="date" name="date"
                                                        value="{{ old('date', $purchase->date) }}" max="{{ now()->toDateString() }}">
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="vendor_id">Vendor Name</label>
                                                    <select name="vendor_id" id="vendor_id" class="form-control">
                                                        <option value="">-- Select Party --</option>
                                                        @foreach ($vendors as $vendor)
                                                            <option value="{{ $vendor->id }}"
                                                                {{ old('vendor_id', $purchase->vendor_id) == $vendor->id ? 'selected' : '' }}>
                                                                {{ $vendor->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="vendor_new_id">Vendor Ledger Name</label>
                                                    <select name="vendor_new_id" id="vendor_new_id" class="form-control">
                                                        <option value="">-- Select Ledger Name --</option>
                                                        @foreach ($ledgersAll as $vendor)
                                                            <option value="{{ $vendor->id }}"
                                                                {{ old('vendor_new_id', $purchase->vendor_new_id) == $vendor->id ? 'selected' : '' }}>
                                                                {{ $vendor->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <a href="{{ route('accounting.ledgers.create', 'purchase') }}"
                                                    class="btn btn-outline-secondary btn-sm">
                                                    Create Ledger
                                                </a>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="parchase_ledger">Purchase Ledger</label>
                                                    <select name="parchase_ledger" id="parchase_ledger" class="form-control">
                                                        <option value="">-- Select Ledger --</option>
                                                        @foreach ($ledgers as $ven)
                                                            <option value="{{ $ven->id }}"
                                                                {{ old('parchase_ledger', $purchase->parchase_ledger) == $ven->id ? 'selected' : '' }}>
                                                                {{ $ven->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
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
                                                                {{ old('subcategories', $purchaseProducts[0]['subcategory_id'] ?? '') == $subcat->id ? 'selected' : '' }}>
                                                                {{ $subcat->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <hr />

                                        {{-- PRODUCTS TABLE --}}
                                        <div class="table-responsive mb-3 product-table-wrapper">
                                            <table class="table table-bordered mb-0" id="product_table">
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
                                                                <input type="number" class="form-control mrp"
                                                                    value="{{ $item['mrp'] }}" disabled>
                                                            </td>
                                                            <td>
                                                                <input type="number" class="form-control qnt"
                                                                    name="products[{{ $i }}][qnt]"
                                                                    value="{{ $item['qnt'] }}">
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
                                                                <button type="button" class="btn btn-icon-action btn-remove-row remove-row" title="Remove">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/><path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/></svg>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <input type="hidden" name="total" class="total_val" value="{{ $purchase->total }}">

                                        <div class="row">
                                            <div class="offset-lg-8 col-lg-4">
                                                <div class="rounded">
                                                    <div class="p-3 d-flex justify-content-end align-items-center gap-2">
                                                        <h4 class="mb-0 mr-2">Sub Total: </h4>
                                                        <input hidden class="total_amt" value="{{ $purchase->total }}">
                                                        <h3 class="mb-0 text-primary font-weight-700" id="total">₹0</h3>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <hr />

                                        {{-- BOTTOM THREE COLUMNS --}}
                                        <div class="row mt-4 mb-3">
                                            {{-- LEFT: LICENSE LEDGER --}}
                                            <div class="col-lg-4" id="license-ledger-col">
                                                <div class="or-detail rounded" id="license-ledger-box">
                                                    <div class="p-3">
                                                        <h5 class="mb-3">Details For License Ledger</h5>
                                                        <div>
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label>ITP Value: </label>
                                                                        <span id="itp_value"></span>
                                                                        <input type="hidden" name="itp_value" id="itp_value_hidden"
                                                                            value="{{ old('itp_value', $purchase->itp_value) }}">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>AED TO BE PAID</label>
                                                                        <input type="number" class="form-control"
                                                                            value="{{ old('aed_to_be_paid', $purchase->aed_to_be_paid) }}"
                                                                            name="aed_to_be_paid" id="aed_to_be_paid" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Guarantee Fulfilled</label>
                                                                        <input type="number" class="form-control"
                                                                            value="{{ old('guarantee_fulfilled', $purchase->guarantee_fulfilled) }}"
                                                                            name="guarantee_fulfilled" id="guarantee_fulfilled" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label>Loading Charges (Including Tax)</label>
                                                                        <input type="number" class="form-control"
                                                                            value="{{ old('loading_charges', $purchase->loading_charges) }}"
                                                                            name="loading_charges" id="loading_charges" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- MIDDLE: EXCISE FEE BOX --}}
                                            <div class="col-lg-4 excise-section d-none" id="excise-col">
                                                <div class="or-detail rounded">
                                                    <div class="p-3">
                                                        <h5 class="mb-3">Excise Fee</h5>
                                                        <div>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Permit Fee</label>
                                                                        <input type="number" class="form-control"
                                                                            name="permit_fee_excise" id="permit_fee_excise"
                                                                            value="{{ old('permit_fee_excise', $purchase->permit_fee_excise) }}" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Vend Fee</label>
                                                                        <input type="number" class="form-control"
                                                                            name="vend_fee_excise" id="vend_fee_excise"
                                                                            value="{{ old('vend_fee_excise', $purchase->vend_fee_excise) }}" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label>Composite Fee (For RTDC Shop)</label>
                                                                        <input type="number" class="form-control"
                                                                            name="composite_fee_excise" id="composite_fee_excise"
                                                                            value="{{ old('composite_fee_excise', $purchase->composite_fee_excise) }}" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="ttl-amt py-2 px-3 d-flex justify-content-between align-items-center border-top">
                                                        <h6>Total</h6>
                                                        <div>
                                                            <input type="hidden" name="excise_total_amount" class="excise_total_amount"
                                                                value="{{ old('excise_total_amount', $purchase->excise_total_amount) }}" />
                                                            <h3 class="text-primary font-weight-700" id="excise_total_amount">
                                                                ₹{{ number_format(old('excise_total_amount', $purchase->excise_total_amount), 2) }}
                                                            </h3>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- RIGHT: BILLING DETAILS --}}
                                            <div class="col-lg-4" id="billing-column">
                                                <div class="or-detail rounded">
                                                    <div class="p-3">
                                                        <h5 class="mb-3">Billing Details</h5>

                                                        {{-- Vendor 1 --}}
                                                        <div id="vendor-1-fields" class="vendor-fields d-none vendor-1">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Excise Fee</label>
                                                                        <input type="number" class="form-control"
                                                                            value="{{ old('excise_fee', $purchase->excise_fee) }}"
                                                                            name="excise_fee" id="excise_fee" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Composition Vat</label>
                                                                        <input type="number" class="form-control"
                                                                            value="{{ old('composition_vat', $purchase->composition_vat) }}"
                                                                            name="composition_vat" id="composition_vat" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Surcharge ON CA</label>
                                                                        <input type="number" class="form-control"
                                                                            value="{{ old('surcharge_on_ca', $purchase->surcharge_on_ca) }}"
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
                                                                            value="{{ old('vat', $purchase->vat) }}"
                                                                            class="form-control" name="vat" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Surcharge On CA</label>
                                                                        <input type="number" id="surcharge_on_vat"
                                                                            value="{{ old('surcharge_on_vat', $purchase->surcharge_on_vat) }}"
                                                                            class="form-control" name="surcharge_on_vat" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>BLF</label>
                                                                        <input type="number" id="blf"
                                                                            value="{{ old('blf', $purchase->blf) }}"
                                                                            class="form-control" name="blf" />
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Permit Fee</label>
                                                                        <input type="number" class="form-control"
                                                                            value="{{ old('permit_fee', $purchase->permit_fee) }}"
                                                                            name="permit_fee" id="permit_fee" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- Vendor Common (TCS) --}}
                                                        <div class="vendor-common">
                                                            <div class="form-group">
                                                                <label>TCS</label>
                                                                <input type="number" class="form-control" name="tcs" id="tcs"
                                                                    value="{{ old('tcs', $purchase->tcs) }}">
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
                                                                        <input type="number"
                                                                            class="form-control form-control-sm pur_dis"
                                                                            placeholder="%" name="case_purchase_per"
                                                                            style="width: 80px;" min="0" max="100"
                                                                            value="{{ old('case_purchase_per', $purchase->case_purchase_per) }}">
                                                                        <span class="ml-1">%</span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-right d-flex align-items-center">
                                                                    <label class="mr-1 mb-0">(-)</label>
                                                                    <input type="number" name="case_purchase_amt"
                                                                        class="form-control form-control-sm pur_amt text-danger font-weight-bold"
                                                                        placeholder="Amount" style="width: 120px;" min="0"
                                                                        value="{{ old('case_purchase_amt', $purchase->case_purchase_amt) }}">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="ttl-amt py-2 px-3 d-flex justify-content-end align-items-center gap-2 border-top">
                                                            <h4 class="mb-0 mr-2">Total Amount: </h4>
                                                            <input type="hidden" name="total_amount" class="total_amount"
                                                                value="{{ old('total_amount', $purchase->total_amount) }}" />
                                                            <h3 class="mb-0 text-primary font-weight-700" id="total_amount">
                                                                ₹{{ number_format(old('total_amount', $purchase->total_amount), 2) }}
                                                            </h3>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-right text-end mt-3">
                                            <button type="submit" class="btn btn-success mr-2 me-2">Update Purchase</button>
                                            <button type="button" onclick="window.history.back()" class="btn btn-danger">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script>
        let currentProductsList = @json($products);

        let productOptions = `
            <option value="">Select Product</option>
            @foreach ($products as $p)
                <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
            @endforeach
        `;

        // ---------- CANVAS MEASURE & DYNAMIC PRODUCT COLUMN WIDTH ----------

        function measureTextWidth(text, font) {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            ctx.font = font || '14px sans-serif';
            return ctx.measureText(text).width;
        }

        function applyDynamicProductColumnWidth(products, minPx, maxPx) {
            minPx = minPx || 110;
            maxPx = maxPx || 330;

            const font = '14px sans-serif';
            let maxTextWidth = 0;

            if (products && products.length) {
                products.forEach(function(p) {
                    const w = measureTextWidth(p.name, font);
                    if (w > maxTextWidth) maxTextWidth = w;
                });
            }

            const colWidth = Math.min(Math.ceil(maxTextWidth) + 50, maxPx);
            const finalWidth = Math.max(colWidth, minPx);

            $('#product_table th:nth-child(2), #product_table td:nth-child(2)').css({
                'width': finalWidth + 'px',
                'min-width': finalWidth + 'px',
                'max-width': finalWidth + 'px'
            });

            const otherColsWidth = 1100;
            $('#product_table').css('min-width', (otherColsWidth + finalWidth) + 'px');
        }

        // ---------- HELPERS (GLOBAL) ----------

        function calculateProductTotals() {
            let total = 0;

            $('#product_table tbody tr').each(function() {
                const $row =$(this);
                const rate = parseFloat($row.find('input[name*="[rate]"]').val()) || 0;
                const qty = parseFloat($row.find('input[name*="[qnt]"]').val()) || 0;
                const amount = round2(rate * qty);
                $row.find('input[name*="[amount]"]').val(amount.toFixed(2));
                total += amount;
            });

            $('#total').text('₹' + Math.round(total));
            $(".total_amt").val(total);
            $('.total_val').val(total);

            return total;
        }

        function updateExciseSection() {
            const permit = parseFloat($('#permit_fee_excise').val()) || 0;
            const vend = parseFloat($('#vend_fee_excise').val()) || 0;
            const composite = parseFloat($('#composite_fee_excise').val()) || 0;

            const totalExcise = permit + vend + composite;

            $('#excise_total_amount').text('₹' + totalExcise.toFixed(2));
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

            // apply discount
            const discountPercent = parseFloat($('.pur_dis').val()) || 0;
            const discountAmount = parseFloat($('.pur_amt').val()) || 0;

            if (discountPercent > 0) {
                const discount = (grandTotal * discountPercent) / 100;
                grandTotal -= discount;
                $('.pur_amt').val(discount);
            } else if (discountAmount > 0) {
                grandTotal -= discountAmount;
            }

            $('#total_amount').text('₹' + grandTotal.toFixed(2));
            $('.total_amount').val(grandTotal);

            // update ITP value
            $('#itp_value').text('₹' + grandTotal.toFixed(2));
            $('#itp_value_hidden').val(grandTotal);
        }

        function filterSubcategoriesByVendor(vendorId) {
            const vendor1Subs = ['1', '2'];
            const vendor2Subs = ['3', '4'];

            $('#subcategories option').each(function() {
                const subId = $(this).data('id');
                if (!subId) {
                    $(this).show();
                    return;
                }
                if (vendorId === '1') {
                    $(this).toggle(vendor1Subs.includes(String(subId)));
                } else if (vendorId === '2') {
                    $(this).toggle(vendor2Subs.includes(String(subId)));
                } else {
                    $(this).show();
                }
            });

            if ($('#subcategories option:selected').is(':hidden')) {
                $('#subcategories').val('');
            }
        }

        function onVendorChange(vendorId) {
            $('.vendor-fields').addClass('d-none');
            $('.vendor-common').hide();$('.excise-section').addClass('d-none');

            const oldValues = {
                excise_fee: '{{ old('excise_fee', $purchase->excise_fee) }}',
                composition_vat: '{{ old('composition_vat', $purchase->composition_vat) }}',
                surcharge_on_ca: '{{ old('surcharge_on_ca', $purchase->surcharge_on_ca) }}',
                aed_to_be_paid: '{{ old('aed_to_be_paid', $purchase->aed_to_be_paid) }}',
                guarantee_fulfilled: '{{ old('guarantee_fulfilled', $purchase->guarantee_fulfilled) }}',
                loading_charges: '{{ old('loading_charges', $purchase->loading_charges) }}',
                vat: '{{ old('vat', $purchase->vat) }}',
                surcharge_on_vat: '{{ old('surcharge_on_vat', $purchase->surcharge_on_vat) }}',
                blf: '{{ old('blf', $purchase->blf) }}',
                permit_fee: '{{ old('permit_fee', $purchase->permit_fee) }}',
                permit_fee_excise: '{{ old('permit_fee_excise', $purchase->permit_fee_excise) }}',
                vend_fee_excise: '{{ old('vend_fee_excise', $purchase->vend_fee_excise) }}',
                composite_fee_excise: '{{ old('composite_fee_excise', $purchase->composite_fee_excise) }}',
                excise_total_amount: '{{ old('excise_total_amount', $purchase->excise_total_amount) }}',
                case_purchase_per: '{{ old('case_purchase_per', $purchase->case_purchase_per) }}',
                case_purchase_amt: '{{ old('case_purchase_amt', $purchase->case_purchase_amt) }}',
                tcs: '{{ old('tcs', $purchase->tcs) }}'
            };

            if (vendorId === '1') {
                // 3-column layout
                $('#license-ledger-col').removeClass('d-none col-lg-6 col-lg-12').addClass('col-lg-4');
                $('#excise-col').removeClass('d-none col-lg-6 col-lg-12').addClass('col-lg-4 excise-section');
                $('#billing-column').removeClass('col-lg-6 col-lg-12 offset-lg-4 offset-lg-0').addClass('col-lg-4');

                $('#license-ledger-box').removeClass('d-none');
                $('.excise-section').removeClass('d-none');$('#vendor-1-fields').removeClass('d-none');
                $('.vendor-common').show();

                if (oldValues.permit_fee_excise) $('#permit_fee_excise').val(oldValues.permit_fee_excise);
                if (oldValues.vend_fee_excise) $('#vend_fee_excise').val(oldValues.vend_fee_excise);
                if (oldValues.composite_fee_excise) $('#composite_fee_excise').val(oldValues.composite_fee_excise);

                if (oldValues.excise_fee) $('#excise_fee').val(oldValues.excise_fee);
                if (oldValues.composition_vat) $('#composition_vat').val(oldValues.composition_vat);
                if (oldValues.surcharge_on_ca) $('#surcharge_on_ca').val(oldValues.surcharge_on_ca);
                if (oldValues.aed_to_be_paid) $('#aed_to_be_paid').val(oldValues.aed_to_be_paid);
                if (oldValues.guarantee_fulfilled) $('#guarantee_fulfilled').val(oldValues.guarantee_fulfilled);
                if (oldValues.loading_charges) $('#loading_charges').val(oldValues.loading_charges);

                if (oldValues.excise_total_amount) {
                    $('#excise_total_amount').text('₹' + parseFloat(oldValues.excise_total_amount).toFixed(2));
                    $('.excise_total_amount').val(parseFloat(oldValues.excise_total_amount));
                }

                updateExciseSection();

            } else if (vendorId === '2') {
                // 2-column layout (50%-50%, no middle space!)
                $('#license-ledger-col').removeClass('d-none col-lg-4 col-lg-12').addClass('col-lg-6');
                $('#excise-col').addClass('d-none');
                $('#billing-column').removeClass('col-lg-4 col-lg-12 offset-lg-4 offset-lg-0').addClass('col-lg-6');

                $('#license-ledger-box').removeClass('d-none');
                $('#vendor-2-fields').removeClass('d-none');

                if (oldValues.vat) $('#vat').val(oldValues.vat);
                if (oldValues.surcharge_on_vat) $('#surcharge_on_vat').val(oldValues.surcharge_on_vat);
                if (oldValues.blf) $('#blf').val(oldValues.blf);
                if (oldValues.permit_fee) $('#permit_fee').val(oldValues.permit_fee);
                if (oldValues.aed_to_be_paid) $('#aed_to_be_paid').val(oldValues.aed_to_be_paid);
                if (oldValues.guarantee_fulfilled) $('#guarantee_fulfilled').val(oldValues.guarantee_fulfilled);
                if (oldValues.loading_charges) $('#loading_charges').val(oldValues.loading_charges);

                $('.vendor-common').show();
            } else if (vendorId) {
                // Other vendors: hide license ledger, full-width billing
                $('#license-ledger-col').addClass('d-none');
                $('#excise-col').addClass('d-none');
                $('#billing-column').removeClass('col-lg-4 col-lg-6 offset-lg-4 offset-lg-0').addClass('col-lg-12');

                $('#vendor-others-fields').removeClass('d-none');
                if (oldValues.case_purchase_per) $('.pur_dis').val(oldValues.case_purchase_per);
                if (oldValues.case_purchase_amt) $('.pur_amt').val(oldValues.case_purchase_amt);
            } else {
                $('#license-ledger-col').removeClass('col-lg-6 col-lg-12').addClass('col-lg-4 d-none');
                $('#excise-col').addClass('d-none');
                $('#billing-column').removeClass('col-lg-6 col-lg-12 offset-lg-4 offset-lg-0').addClass('col-lg-4');
                $('#license-ledger-box').addClass('d-none');
            }

            if (oldValues.tcs) $('#tcs').val(oldValues.tcs);

            calculateProductTotals();
            updateBillingTotal();
        }

        function refreshButtons() {
            $('#product_table tbody tr').each(function(index) {
                const actionCell = $(this).find('.action-col');
                if (index === $('#product_table tbody tr').length - 1) {
                    actionCell.html(`
                        <button type="button" class="btn btn-icon-action btn-add-row add-row" title="Add Product">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/></svg>
                        </button>
                    `);
                } else {
                    actionCell.html(`
                        <button type="button" class="btn btn-icon-action btn-remove-row remove-row" title="Remove">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/><path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/></svg>
                        </button>
                    `);
                }
            });
        }

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
                        <button type="button" class="btn btn-icon-action btn-add-row add-row" title="Add Product">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/></svg>
                        </button>
                    </td>
                </tr>
            `;

            $('#product_table tbody').append(row);
            applyDynamicProductColumnWidth(currentProductsList);
        }

        function round2(num) {
            return Math.round((parseFloat(num) || 0) * 100) / 100;
        }

        // ---------- EVENT LISTENERS ----------

        $(document).on('change', '.product_select_row', function() {
            const product_id = $(this).val();
            const row = $(this).closest('tr');

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
                    row.find('.rate').val(data.cost_price);

                    let qty = parseFloat(row.find('.qnt').val()) || 1;
                    let amount = round2(qty * data.cost_price);
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
                return;
            }

            addEmptyRow();
            refreshButtons();
        });

        $(document).on('click', '.remove-row', function() {$(this).closest('tr').remove();
            updateSrNo();
            refreshButtons();
            calculateProductTotals();
            updateBillingTotal();

            if ($('#productBody tr').length === 0) {
                $('#excise_fee, #composition_vat, #surcharge_on_ca, #aed_to_be_paid').val('');
                $('#vat, #surcharge_on_vat, #blf, #permit_fee').val('');
                $('.pur_dis, .pur_amt').val('');$('#tcs').val('');
                $('#total_amount').text('₹0.00');
                $('.total_amount').val('0.00');$('#excise_total_amount').text('₹0.00');
                $('.excise_total_amount').val('0.00');
            }
        });

        $(document).on('blur', 'input[name*="[qnt]"], input[name*="[rate]"]', function() {
            const $row =$(this).closest('tr');
            const qty = parseFloat($row.find('input[name*="[qnt]"]').val()) || 0;
            const rate = parseFloat($row.find('input[name*="[rate]"]').val()) || 0;
            const amount = round2(qty * rate);
            $row.find('input[name*="[amount]"]').val(amount.toFixed(2));

            calculateProductTotals();
            updateBillingTotal();
        });

        $(document).on('blur', 'input[name*="[amount]"]', function() {
            const $row =$(this).closest('tr');
            const amount = parseFloat($(this).val()) || 0;
            const qty = parseFloat($row.find('input[name*="[qnt]"]').val()) || 1;
            const rate = qty > 0 ? (amount / qty) : 0;
            $row.find('input[name*="[rate]"]').val(rate.toFixed(4));

            calculateProductTotals();
            updateBillingTotal();
        });

        $('#excise_fee, #composition_vat, #surcharge_on_ca, #tcs, #vat, #surcharge_on_vat, #blf, #permit_fee, #aed_to_be_paid, #loading_charges')
            .on('input', function() {
                updateBillingTotal();
            });

        $(document).on('input', '#permit_fee_excise, #vend_fee_excise, #composite_fee_excise', function() {
            updateExciseSection();
        });

        function updateFromPercentage() {
            const baseTotal = parseFloat($(".total_amt").val()) || 0;
            const percent = parseFloat($('.pur_dis').val()) || 0;
            const discount = (baseTotal * percent) / 100;

            $('.pur_amt').val(discount);
            updateBillingTotal();
        }

        function updateFromAmount() {
            const baseTotal = parseFloat($(".total_amt").val()) || 0;
            const amount = parseFloat($('.pur_amt').val()) || 0;
            const percent = baseTotal > 0 ? (amount / baseTotal) * 100 : 0;

            $('.pur_dis').val(percent);
            updateBillingTotal();
        }

        $('.pur_dis').on('input', function() {
            updateFromPercentage();
        });

        $('.pur_amt').on('input', function() {
            updateFromAmount();
        });

        $('#vendor_id').on('change', function() {
            const vendorId = $(this).val();
            onVendorChange(vendorId);
            filterSubcategoriesByVendor(vendorId);
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
                    currentProductsList = products;

                    productOptions = '<option value="">Select Product</option>';
                    products.forEach(function(p) {
                        productOptions += `<option value="${p.id}">${p.name}</option>`;
                    });

                    let lastRow = $('#product_table tbody tr:last');
                    lastRow.find('.product_select_row').html(productOptions);

                    applyDynamicProductColumnWidth(products);
                }
            });
        });

        // ================= LEDGER BALANCE =================

        const ledgerMap = {
            aed_to_be_paid: "AED TO BE PAID",
            guarantee_fulfilled: "Guarantee Fulfilled",
            loading_charges: "Loading Charges",
            permit_fee_excise: "Permit Fee",
            vend_fee_excise: "Vend Fee",
            composite_fee_excise: "Composite Fee",
            excise_fee: "EXCISE FEE",
            composition_vat: "COMPOSITION VAT",
            surcharge_on_ca: "SURCHARGE ON CA",
            tcs: "TCS",
            blf: "BLF",
            vat: "VAT",
            surcharge_on_vat: "SURCHARGE ON VAT"
        };

        function loadLedgerBalances() {
            Object.keys(ledgerMap).forEach(function(id) {
                const $input =$('#' + id);
                if ($input.length) {
                    if ($input.next('.ledger-info').length === 0) {$input.after(`
                            <small class="ledger-info" style="font-size:11px;color:#888;display:block;">
                                Loading...
                            </small>
                        `);
                    }

                    $.ajax({
                        url: "/ledger/balance-by-name",
                        type: "GET",
                        data: { name: ledgerMap[id] },
                        success: function(res) {
                            const balance = parseFloat(res.balance) || 0;
                            const text = balance.toLocaleString('en-IN', {
                                minimumFractionDigits: 2
                            }) + ' ' + res.type;

                            $input.next('.ledger-info').text(text);
                        }
                    });
                }
            });
        }

        // ---------- MAIN READY ----------

        $(document).ready(function() {
            refreshButtons();
            $('.vendor-common').hide();
            loadLedgerBalances();

            // પેજ લોડ વખતે પ્રોડક્ટ લિસ્ટ પ્રમાણે કોલમ વિડ્થ ઓટો-સેટ થશે
            applyDynamicProductColumnWidth(currentProductsList);
        });

        $(document).on('change', '#vendor_id', function() {
            setTimeout(function() {
                loadLedgerBalances();
            }, 300);
        });

        document.addEventListener('DOMContentLoaded', function() {
            const totalAmount = "{{ old('total_amount', $purchase->total_amount) }}";
            if (totalAmount) {
                $('#itp_value').text('₹' + parseFloat(totalAmount).toFixed(2));
                $('#itp_value_hidden').val(parseFloat(totalAmount));
            }

            calculateProductTotals();
            updateBillingTotal();

            const vendorId = "{{ old('vendor_id', $purchase->vendor_id) }}";
            if (vendorId) {
                onVendorChange(vendorId);
                filterSubcategoriesByVendor(vendorId);

                const exciseTotal = "{{ old('excise_total_amount', $purchase->excise_total_amount) }}";
                if (exciseTotal) {
                    $('#excise_total_amount').text('₹' + parseFloat(exciseTotal).toFixed(2));
                    $('.excise_total_amount').val(parseFloat(exciseTotal));
                }

                if (totalAmount) {
                    $('#total_amount').text('₹' + parseFloat(totalAmount).toFixed(2));
                    $('.total_amount').val(parseFloat(totalAmount));
                }
            } else {
                $('#license-ledger-box').addClass('d-none');
            }
        });
    </script>
@endsection