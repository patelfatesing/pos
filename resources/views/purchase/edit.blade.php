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
                                    <h4 class="card-title">
                                        {{ isset($purchase) ? 'Edit Delivery Invoice' : 'Delivery Invoice' }}</h4>
                                    @error('to_store_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <a href="{{ route('purchase.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="card">
                                    <div class="card-body">

                                        <form
                                            action="{{ isset($purchase) ? route('purchase.update', $purchase->id) : route('purchase.store') }}"
                                            method="POST" enctype="multipart/form-data" id="purchaseForm">
                                            @csrf
                                            @if (isset($purchase))
                                                @method('PUT')
                                            @endif

                                            @php
                                                $isEdit = isset($purchase);
                                            @endphp

                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="bill_no" class="form-label">Bill No</label>
                                                    <input type="text" class="form-control" id="bill_no" name="bill_no"
                                                        value="{{ old('bill_no', $isEdit ? $purchase->bill_no : '') }}">
                                                    @error('bill_no')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div class="col-md-4">
                                                    <label for="date" class="form-label">Date</label>
                                                    <input type="date" class="form-control" id="date" name="date"
                                                        value="{{ old('date', $isEdit && $purchase->date ? $purchase->date : '') }}"
                                                        max="{{ now()->toDateString() }}">
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
                                                                    @selected(old('vendor_id', $isEdit ? $purchase->vendor_id : '') == $vendor->id)>
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
                                                        <label for="vendor_new_id">Ledger Name</label>
                                                        <select name="vendor_new_id" id="vendor_new_id"
                                                            class="form-control">
                                                            <option value="">-- Select Ledger Name --</option>
                                                            @foreach ($ledgersAll as $ledger)
                                                                <option value="{{ $ledger->id }}"
                                                                    @selected(old('vendor_new_id', $isEdit ? $purchase->vendor_new_id : '') == $ledger->id)>
                                                                    {{ $ledger->name }}
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
                                                                    @selected(old('parchase_ledger', $isEdit ? $purchase->parchase_ledger : '') == $ven->id)>
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
                                                        <select name="subcategories" id="subcategories"
                                                            class="form-control">
                                                            <option value="">-- Select Subcategory --</option>
                                                            @foreach ($subcategories as $subcat)
                                                                <option value="{{ $subcat->id }}"
                                                                    @selected(old('subcategories', $isEdit && isset($purchaseProducts[0]) ? $purchaseProducts[0]['subcategory_id'] ?? '' : '') == $subcat->id)>
                                                                    {{ $subcat->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('subcategories')
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
                                                            {{-- product options get replaced by subcategory ajax. keep initial list for fallback --}}
                                                            @foreach ($products as $product)
                                                                <option value="{{ $product['id'] }}"
                                                                    data-mrp="{{ $product['mrp'] ?? '' }}"
                                                                    data-cost_price="{{ $product['cost_price'] ?? '' }}"
                                                                    data-sell_price="{{ $product['sell_price'] ?? '' }}">
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
                                                            <th>Product</th>
                                                            <th>Batch</th>
                                                            <th>MFG Date</th>
                                                            <th>MRP Rate</th>
                                                            <th>Qty</th>
                                                            <th> Cost Price</th>
                                                            <th>Amount</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="productBody">
                                                        {{-- priority: old input (validation) -> server-side purchaseProducts -> JS fallback --}}
                                                        @if (old('products'))
                                                            @foreach (old('products') as $i => $product)
                                                                @include('purchase.partials._product_row', [
                                                                    'i' => $i,
                                                                    'product' => $product,
                                                                ])
                                                            @endforeach
                                                        @elseif(!empty($purchaseProducts) && $isEdit)
                                                            @foreach ($purchaseProducts as $i => $p)
                                                                <tr>
                                                                    <td>{{ $i + 1 }}</td>
                                                                    <input type="hidden"
                                                                        name="products[{{ $i }}][product_id]"
                                                                        value="{{ $p['product_id'] }}">
                                                                    <td><input type="text" class="form-control"
                                                                            name="products[{{ $i }}][brand_name]"
                                                                            value="{{ $p['brand_name'] }}" readonly></td>
                                                                    <td><input type="text" class="form-control"
                                                                            name="products[{{ $i }}][batch]"
                                                                            value="{{ $p['batch'] }}"></td>
                                                                    <td><input type="date" class="form-control"
                                                                            name="products[{{ $i }}][mfg_date]"
                                                                            value="{{ $p['mfg_date'] }}"></td>
                                                                    <td>
                                                                        <input type="hidden"
                                                                            name="products[{{ $i }}][mrp]"
                                                                            value="{{ $p['mrp'] }}">
                                                                        <input type="number" class="form-control mrp"
                                                                            step="0.01" value="{{ $p['mrp'] }}"
                                                                            disabled>
                                                                    </td>
                                                                    <td><input type="number" class="form-control qnt"
                                                                            name="products[{{ $i }}][qnt]"
                                                                            value="{{ $p['qnt'] }}"></td>
                                                                    <td><input type="number" class="form-control rate"
                                                                            step="0.01"
                                                                            name="products[{{ $i }}][rate]"
                                                                            value="{{ $p['rate'] }}"></td>
                                                                    <td><input type="number" class="form-control amount"
                                                                            step="0.01"
                                                                            name="products[{{ $i }}][amount]"
                                                                            value="{{ $p['amount'] }}"></td>
                                                                    <td><button type="button"
                                                                            class="btn btn-sm btn-danger remove">Remove</button>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                    </tbody>

                                                </table>
                                            </div>

                                            <input type="hidden" name="total" class="total_val"
                                                value="{{ old('total', $isEdit ? $purchase->total : '') }}" />

                                            <div class="row mt-4 mb-3">
                                                <div class="offset-lg-8 col-lg-4">
                                                    <div class="or-detail rounded">
                                                        <div class="p-3">
                                                            <span colspan="8">Sub Total: </span>
                                                            <input hidden class="total_amt">
                                                            <span id="total"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr />
                                            <div class="row mt-4 mb-3">
                                                <div class="col-lg-4">
                                                    <div class="or-detail rounded">
                                                        <div class="p-3">
                                                            <h5 class="mb-3"></h5>
                                                            <div id="vendor-1-fields"
                                                                class="vendor-fields d-none vendor-1">
                                                                <div class="form-group">
                                                                    <label>AED TO BE PAID</label>
                                                                    <input type="number" class="form-control"
                                                                        value="{{ old('aed_to_be_paid', $isEdit ? $purchase->aed_to_be_paid : '') }}"
                                                                        name="aed_to_be_paid" id="aed_to_be_paid" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Guarantee Fulfilled</label>
                                                                    <input type="number" class="form-control"
                                                                        value="{{ old('guarantee_fulfilled', $isEdit ? $purchase->guarantee_fulfilled : '') }}"
                                                                        name="guarantee_fulfilled"
                                                                        id="guarantee_fulfilled" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="offset-lg-8 col-lg-4">
                                                    <div class="or-detail rounded">
                                                        <div class="p-3">
                                                            <h5 class="mb-3">Billing Details</h5>

                                                            <!-- Vendor 1 Fields -->
                                                            <div id="vendor-1-fields"
                                                                class="vendor-fields d-none vendor-1">
                                                                <div class="form-group">
                                                                    <label>EXCISE FEE</label>
                                                                    <input type="number" class="form-control"
                                                                        value="{{ old('excise_fee', $isEdit ? $purchase->excise_fee : '') }}"
                                                                        name="excise_fee" id="excise_fee" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>COMPOSITION VAT</label>
                                                                    <input type="number" class="form-control"
                                                                        value="{{ old('composition_vat', $isEdit ? $purchase->composition_vat : '') }}"
                                                                        name="composition_vat" id="composition_vat" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>SURCHARGE ON CA</label>
                                                                    <input type="number" class="form-control"
                                                                        value="{{ old('surcharge_on_ca', $isEdit ? $purchase->surcharge_on_ca : '') }}"
                                                                        name="surcharge_on_ca" id="surcharge_on_ca" />
                                                                </div>
                                                            </div>

                                                            <!-- Vendor 2 and Others Fields -->
                                                            <div id="vendor-2-fields"
                                                                class="vendor-fields d-none vendor-2">
                                                                <div class="form-group">
                                                                    <label>VAT</label>
                                                                    <input type="number" id="vat"
                                                                        value="{{ old('vat', $isEdit ? $purchase->vat : '') }}"
                                                                        class="form-control" name="vat" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>SURCHARGE ON VAT</label>
                                                                    <input type="number" id="surcharge_on_vat"
                                                                        value="{{ old('surcharge_on_vat', $isEdit ? $purchase->surcharge_on_vat : '') }}"
                                                                        class="form-control" name="surcharge_on_vat" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>BLF</label>
                                                                    <input type="number" id="blf"
                                                                        value="{{ old('blf', $isEdit ? $purchase->blf : '') }}"
                                                                        class="form-control" name="blf" />
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Permit Fee</label>
                                                                    <input type="number" class="form-control"
                                                                        value="{{ old('permit_fee', $isEdit ? $purchase->permit_fee : '') }}"
                                                                        name="permit_fee" id="permit_fee" />
                                                                </div>
                                                            </div>

                                                            <div class="vendor-common">
                                                                <div class="form-group">
                                                                    <label>TCS</label>
                                                                    <input type="number" id="tcs"
                                                                        value="{{ old('tcs', $isEdit ? $purchase->tcs : '') }}"
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
                                                                        <input type="float"
                                                                            class="form-control form-control-sm pur_dis"
                                                                            placeholder="%" name="case_purchase_per"
                                                                            style="width: 80px;" min="0"
                                                                            max="100"
                                                                            value="{{ old('case_purchase_per', $isEdit ? $purchase->case_purchase_per : '') }}">
                                                                        <span class="ml-1">%</span>
                                                                    </div>
                                                                </div>
                                                                <div class="text-right d-flex align-items-center">
                                                                    <label class="mr-1 mb-0">(-)</label>
                                                                    <input type="float" name="case_purchase_amt"
                                                                        class="form-control form-control-sm pur_amt text-danger font-weight-bold"
                                                                        placeholder="Amount" style="width: 120px;"
                                                                        min="0"
                                                                        value="{{ old('case_purchase_amt', $isEdit ? $purchase->case_purchase_amt : '') }}">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Total after deduction -->
                                                        <div
                                                            class="ttl-amt py-2 px-3 d-flex justify-content-between align-items-center border-top">
                                                            <h6>Total Amount</h6>
                                                            <div>
                                                                <input type="hidden" name="total_amount"
                                                                    class="total_amount"
                                                                    value="{{ old('total_amount', $isEdit ? $purchase->total_amount : '') }}" />
                                                                <h3 class="text-primary font-weight-700"
                                                                    id="total_amount">
                                                                    {{ $isEdit ? '₹' . number_format($purchase->total_amount ?? ($purchase->total ?? 0), 2) : '' }}
                                                                </h3>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                            <button type="submit"
                                                class="btn btn-primary mr-2">{{ $isEdit ? 'Update Purchase' : 'Add Purchase Order' }}</button>
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

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // purchaseProducts comes from controller (array) or fallback to empty array
        const purchaseProducts = @json(old('products') ? old('products') : $purchaseProducts ?? []);
        const isEdit = {{ isset($purchase) ? 'true' : 'false' }};

        $(document).ready(function() {
            // initialize srNo depending on existing rows
            let srNo = 1;
            if ($('#productBody tr').length) {
                srNo = $('#productBody tr').length + 1;
            } else if (purchaseProducts && purchaseProducts.length) {
                srNo = purchaseProducts.length + 1;
            }
            window.srNo = srNo;

            // If there are no old inputs, rebuild rows from purchaseProducts (edit case)
            if (!({{ json_encode(old('products') ? true : false) }})) {
                rebuildProductRowsFromData(purchaseProducts);
            }

            // Set subtotal & totals on load
            calculateProductTotals();
            updateBillingTotal();

            // Preselect subcategory & load its products (for edit)
            const initialSubcat =
                "{{ old('subcategories', isset($purchaseProducts[0]) ? $purchaseProducts[0]['subcategory_id'] ?? '' : '') }}";
            if (initialSubcat) {
                $('#subcategories').val(initialSubcat).trigger('change');

                // after products loaded via AJAX, we may want to set product_select to the first product (not strictly necessary)
                // handled in subcategory AJAX success earlier where old('product_select') will be restored
            }

            // Restore vendor specific sections if vendor selected
            const oldVendorId = "{{ old('vendor_id', $isEdit ? $purchase->vendor_id : '') }}";
            if (oldVendorId) {
                onVendorChange(oldVendorId.toString());
                $('#vendor_id').val(oldVendorId);
            }
        });

        // Rebuild product rows from a JS array of items
        function rebuildProductRowsFromData(items) {
            $('#productBody').empty();
            if (!items || items.length === 0) return;

            items.forEach(function(item, idx) {
                const i = idx;
                const sr = idx + 1;

                const productId = item.product_id ?? item.productId ?? '';
                const brand = item.brand_name ?? item.brandName ?? item.name ?? '';
                const batch = item.batch ?? '';
                const mfg = item.mfg_date ?? item.mfgDate ?? '';
                const mrp = item.mrp ?? 0;
                const rate = item.rate ?? item.cost_price ?? item.rate ?? 0;
                const qty = item.qnt ?? item.qty ?? item.quantity ?? 1;
                const amount = (parseFloat(item.amount ?? (rate * qty)) || 0).toFixed(2);

                const row = `
                <tr>
                    <td>${sr}</td>
                    <input type="hidden" name="products[${i}][product_id]" value="${productId}">
                    <td style="width:25%"><input type="text" name="products[${i}][brand_name]" class="form-control" value="${escapeHtml(brand)}" readonly></td>
                    <td><input type="text" name="products[${i}][batch]" class="form-control" value="${escapeHtml(batch)}"></td>
                    <td><input type="date" name="products[${i}][mfg_date]" class="form-control" value="${mfg}"></td>
                    <td><input type="hidden" name="products[${i}][mrp]" value="${mrp}"><input type="number" class="form-control" value="${mrp}" disabled></td>
                    <td><input type="number" name="products[${i}][qnt]" class="form-control qnt" value="${qty}" min="1" data-prev="${qty}"></td>
                    <td><input type="number" step="0.01" name="products[${i}][rate]" class="form-control rate" value="${parseFloat(rate).toFixed(2)}"></td>
                    <td><input type="number" step="0.01" name="products[${i}][amount]" class="form-control amount" value="${amount}"></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove">Remove</button></td>
                </tr>
            `;
                $('#productBody').append(row);
            });

            reindexProductRows();
        }

        // escape html helper
        function escapeHtml(text) {
            if (!text) return '';
            return String(text).replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        // reindex product rows so input names are contiguous
        function reindexProductRows() {
            $('#productBody tr').each(function(index) {
                $(this).find('input, select, textarea').each(function() {
                    const name = $(this).attr('name');
                    if (!name) return;
                    const newName = name.replace(/products\[\d+\]/, `products[${index}]`);
                    $(this).attr('name', newName);
                });
                // update sr no cell
                $(this).find('td:first').text(index + 1);
            });

            window.srNo = $('#productBody tr').length + 1;
        }

        // delegate remove handler
        $(document).on('click', '.remove', function() {
            $(this).closest('tr').remove();
            reindexProductRows();
            calculateProductTotals();
            updateBillingTotal();
        });

        // existing functions: calculateProductTotals, updateBillingTotal, onVendorChange, etc.
        // I reuse your original functions but ensure they exist — paste original functions here or include your script file.
        // For brevity, I include the core ones used above.

        function calculateProductTotals() {
            let total = 0;
            $('#productBody tr').each(function() {
                const $row = $(this);
                const rate = parseFloat($row.find('input[name*="[rate]"]').val()) || 0;
                const qty = parseFloat($row.find('input[name*="[qnt]"]').val()) || 0;
                const amount = (rate * qty).toFixed(2);
                $row.find('input[name*="[amount]"]').val(amount);
                total += parseFloat(amount);
            });

            $('#total').text(total.toFixed(2));
            $(".total_amt").val(total.toFixed(2));
            $('.total_val').val(total.toFixed(2));
            $('#total_amount').text('₹' + total.toFixed(2));
            $('.total_amount').val(total.toFixed(2));

            return total;
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
            const rsgsm_purchase = parseFloat($('#rsgsm_purchase').val()) || 0;
            const aed = parseFloat($('#aed_to_be_paid').val()) || 0;

            const additionalCharges = excise + compVat + surcharge + tcs + aed + vat +
                surcharge_on_vat + blf + permit_fee + rsgsm_purchase;

            let grandTotal = baseTotal + additionalCharges;

            const discountPercent = parseFloat($('.pur_dis').val()) || 0;
            const discountAmount = parseFloat($('.pur_amt').val()) || 0;

            if (discountPercent > 0) {
                const discount = (grandTotal * discountPercent) / 100;
                grandTotal -= discount;
                $('.pur_amt').val(discount.toFixed(2));
            } else if (discountAmount > 0) {
                grandTotal -= discountAmount;
                $('.pur_dis').val(((discountAmount / grandTotal) * 100).toFixed(2));
            }

            $('#total_amount').text('₹' + grandTotal.toFixed(2));
            $('.total_amount').val(grandTotal.toFixed(2));
        }

        // Keep your vendor change function (copied from original create view)
        function onVendorChange(vendorId) {
            $('.vendor-fields').addClass('d-none');
            $('#excise_fee, #composition_vat, #surcharge_on_ca, #aed_to_be_paid').val(0);
            $('#vat, #surcharge_on_vat, #blf, #permit_fee, #rsgsm_purchase').val(0);
            $('.pur_dis, .pur_amt').val(0);

            const oldValues = {
                excise_fee: '{{ old('excise_fee', $isEdit ? $purchase->excise_fee : '') }}',
                composition_vat: '{{ old('composition_vat', $isEdit ? $purchase->composition_vat : '') }}',
                surcharge_on_ca: '{{ old('surcharge_on_ca', $isEdit ? $purchase->surcharge_on_ca : '') }}',
                aed_to_be_paid: '{{ old('aed_to_be_paid', $isEdit ? $purchase->aed_to_be_paid : '') }}',
                vat: '{{ old('vat', $isEdit ? $purchase->vat : '') }}',
                surcharge_on_vat: '{{ old('surcharge_on_vat', $isEdit ? $purchase->surcharge_on_vat : '') }}',
                blf: '{{ old('blf', $isEdit ? $purchase->blf : '') }}',
                permit_fee: '{{ old('permit_fee', $isEdit ? $purchase->permit_fee : '') }}',
                rsgsm_purchase: '{{ old('rsgsm_purchase', $isEdit ? $purchase->rsgsm_purchase : '') }}',
                case_purchase_per: '{{ old('case_purchase_per', $isEdit ? $purchase->case_purchase_per : '') }}',
                case_purchase_amt: '{{ old('case_purchase_amt', $isEdit ? $purchase->case_purchase_amt : '') }}',
                tcs: '{{ old('tcs', $isEdit ? $purchase->tcs : '') }}',
            };

            if (vendorId === '1') {
                $('#vendor-1-fields').removeClass('d-none');
                if (oldValues.excise_fee) $('#excise_fee').val(oldValues.excise_fee);
                if (oldValues.composition_vat) $('#composition_vat').val(oldValues.composition_vat);
                if (oldValues.surcharge_on_ca) $('#surcharge_on_ca').val(oldValues.surcharge_on_ca);
                if (oldValues.aed_to_be_paid) $('#aed_to_be_paid').val(oldValues.aed_to_be_paid);
                $('.vendor-common').show();
            } else if (vendorId === '2') {
                $('#vendor-2-fields').removeClass('d-none');
                if (oldValues.vat) $('#vat').val(oldValues.vat);
                if (oldValues.surcharge_on_vat) $('#surcharge_on_vat').val(oldValues.surcharge_on_vat);
                if (oldValues.blf) $('#blf').val(oldValues.blf);
                if (oldValues.permit_fee) $('#permit_fee').val(oldValues.permit_fee);
                if (oldValues.rsgsm_purchase) $('#rsgsm_purchase').val(oldValues.rsgsm_purchase);
                $('.vendor-common').hide();
            } else {
                $('#vendor-others-fields').removeClass('d-none');
                $('.vendor-common').hide();
                if (oldValues.case_purchase_per) $('.pur_dis').val(oldValues.case_purchase_per);
                if (oldValues.case_purchase_amt) $('.pur_amt').val(oldValues.case_purchase_amt);
            }

            if (oldValues.tcs) $('#tcs').val(oldValues.tcs);

            calculateProductTotals();
            updateBillingTotal();
        }

        // When subcategory changes, fetch products for it (keeps your create logic)
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
                    $productSelect.empty().append('<option value="">-- Select Product --</option>');
                    if (!products || products.length === 0) {
                        $productSelect.append('<option value="">No products found</option>');
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

                    const oldProduct = "{{ old('product_select', '') }}";
                    if (oldProduct) {
                        $productSelect.val(oldProduct);
                    }
                },
                error: function(xhr) {
                    $productSelect.empty().append('<option value="">-- Select Product --</option>');
                    console.error(xhr);
                }
            });
        });

        // When product selected — use addProduct (your original function) by reading option data attributes
        $('#product_select').on('change', function() {
            const productId = $(this).val();
            if (!productId) return;

            const $opt = $(this).find('option:selected');
            const data = {
                id: productId,
                name: $opt.text(),
                batch_no: $opt.data('batch_no') || '',
                mfg_date: '',
                mrp: $opt.data('mrp') || '',
                cost_price: $opt.data('cost_price') || '',
                sell_price: $opt.data('sell_price') || ''
            };

            // call the same addProduct used in create view; if you kept addProduct above, it will work.
            // If not present, use the rebuild row code:
            addProduct(data);
        });

        // minimal addProduct that matches your create() behavior
        function addProduct(data) {
            const brand = data.id;
            const brandVal = data.name;
            const batch = data.batch_no || '';
            const mfg = data.mfg_date || '';
            const mrp = data.mrp || 0;
            const rate = data.cost_price || data.sell_price || 0;
            const qty = 1;
            const amount = (parseFloat(rate) * qty).toFixed(2);

            // check existing row
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
                const newAmount = (newQty * rate).toFixed(2);
                qtyInput.val(newQty);
                amountInput.val(newAmount);
                qtyInput.data('prev', newQty);
                calculateProductTotals();
                updateBillingTotal();
            } else {
                const i = window.srNo ? window.srNo - 1 : $('#productBody tr').length;
                const row = `
                <tr>
                    <td>${window.srNo}</td>
                    <input type="hidden" name="products[${i}][product_id]" value="${brand}">
                    <td style="width:25%"><input type="text" name="products[${i}][brand_name]" class="form-control" value="${escapeHtml(brandVal)}" readonly></td>
                    <td><input type="text" name="products[${i}][batch]" class="form-control" value="${escapeHtml(batch)}"></td>
                    <td><input type="date" name="products[${i}][mfg_date]" class="form-control" value="${mfg}"></td>
                    <td><input type="hidden" name="products[${i}][mrp]" value="${mrp}"><input type="number" class="form-control" value="${mrp}" disabled></td>
                    <td><input type="number" name="products[${i}][qnt]" class="form-control qnt" value="${qty}" min="1" data-prev="${qty}"></td>
                    <td><input type="number" step="0.01" name="products[${i}][rate]" class="form-control rate" value="${rate}"></td>
                    <td><input type="number" step="0.01" name="products[${i}][amount]" class="form-control amount" value="${amount}"></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove">Remove</button></td>
                </tr>
            `;
                $('#productBody').append(row);
                window.srNo = (window.srNo || 1) + 1;
                reindexProductRows();
                calculateProductTotals();
                updateBillingTotal();
            }

            // reset selects (if you have input fields for batch/mfg/etc. clear them)
            $('#product_select').val('');
        }

        // update quantity/rate/amount handlers
        $(document).on('input', 'input[name*="[qnt]"], input[name*="[rate]"]', function() {
            reindexProductRows();
            calculateProductTotals();
            updateBillingTotal();
        });

        // ensure proper reindex on form submit (so request names are contiguous)
        $('#purchaseForm').on('submit', function() {
            reindexProductRows();
        });
    </script>
@endpush
