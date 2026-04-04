@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="content-page">
        <div class="container-fluid">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h4 class="mb-0">Sales Report</h4>
                </div>
                <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
            </div>
            <!-- Page Header -->
            <div class="row mt-2">

                <!-- Branch Filter -->
                <div class="col-md-3 mb-2">
                    <select id="branch_filter" class="form-control">
                        <option value="">All Branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range -->
                <div class="col-md-3 mb-2">
                    <input type="text" id="reportrange" class="form-control" />
                </div>

                <div class="col-md-2 mb-2">
                    <button id="reset-filters" class="btn btn-danger">Reset</button>
                </div>

            </div>

            <!-- Table -->
            <div class="col-lg-12">
                <div class="table-responsive rounded mb-3">
                    <table class="table table-striped table-bordered nowrap" id="stock-table">
                        <div class="container mt-3">
                            <thead>
                                <tr>
                                    <th>Store</th>

                                    <th>Total Amount</th>
                                </tr>
                            </thead>

                            <tbody id="storeData">
                                @include('sales.partials.store-data')
                            </tbody>


                    </table>
                </div>
            </div>

        </div>
    </div>


    <!-- MODAL -->
    <div class="modal fade" id="verifyModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5>Verify Sale</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="modalContent">
                    Loading...
                </div>

            </div>
        </div>
    </div>

    <!-- Shift Summary Modal -->
    <div class="modal fade" id="shiftSummaryModal" tabindex="-1" aria-labelledby="shiftSummaryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="shiftSummaryModalLabel">Shift Close Summary - <span
                            id="modalBranchName">Branch</span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body row" id="shiftSummaryContent">

                </div>
            </div>
        </div>
    </div>

    <!-- ADD SALES MODAL -->
    <div class="modal fade" id="addSalesModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <h5>Add Sale</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <form id="popup-sale-form">
                        @csrf

                        <div class="row">

                            <!-- LEFT SIDE -->
                            <div class="col-md-8">

                                <!-- PRODUCT ADD -->
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <select id="popup-product-id" class="form-control">
                                            <option value="">Select Product</option>
                                            @foreach ($allProducts as $product)
                                                <option value="{{ $product->id }}" data-name="{{ $product->name }}"
                                                    data-mrp="{{ $product->mrp }}"
                                                    data-sell_price="{{ $product->sell_price }}"
                                                    data-discount="{{ $product->discount_price }}">
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <input type="number" id="popup-qty" class="form-control" placeholder="Qty">
                                    </div>

                                    <div class="col-md-2">
                                        <button type="button" id="popup-add-item" class="btn ">
                                            Add
                                        </button>
                                    </div>
                                </div>

                            </div>

                            <!-- RIGHT SIDE (IMPORTANT) -->
                            <div class="col-md-4 mb-4">

                                <!-- PARTY -->
                                <select id="popup-party-id" class="form-control mb-2" name="party_user_id">
                                    <option value="">Party</option>
                                    @foreach ($partyUsers as $cust)
                                        <option value="{{ $cust->id }}">{{ $cust->first_name }}</option>
                                    @endforeach
                                </select>

                                <!-- COMMISSION -->
                                <select id="popup-commission-id" class="form-control mb-2" name="commission_user_id">
                                    <option value="">Commission</option>
                                    @foreach ($commissionUsers as $cust)
                                        <option value="{{ $cust->id }}">{{ $cust->first_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                        <div class="row">

                            <!-- LEFT SIDE -->
                            <div class="col-md-8">



                                <!-- TABLE -->
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Qty</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="popup-items-body"></tbody>
                                </table>

                            </div>

                            <!-- RIGHT SIDE (IMPORTANT) -->
                            <div class="col-md-4">

                                <h5>Order Details</h5>

                                <input type="hidden" id="popup_total_discount" name="total_discount">
                                <input type="hidden" id="popup_gr_total" name="sub_total">
                                <input type="hidden" id="popup_sub_total" name="total">
                                <input type="hidden" id="popup_left_credit_id">

                                <p>Sub Total: ₹<span id="popup-total"></span></p>
                                <p>Discount: ₹<span id="popup-discount"></span></p>

                                <!-- PAYMENT -->
                                <div>
                                    <label><input type="radio" name="payment_method" value="cash" checked>
                                        Cash</label>
                                    <label><input type="radio" name="payment_method" value="online"> UPI</label>
                                    <label><input type="radio" name="payment_method" value="cashupi"> Cash+UPI</label>
                                    <label><input type="radio" name="payment_method" value="credit"> Credit</label>
                                </div>

                                <input type="number" name="cash_amount" id="popup-cash" class="form-control mt-2">
                                <input type="number" name="upi_amount" id="popup-upi" class="form-control mt-2">

                                <h4>Total: ₹<span id="popup-grand-total"></span></h4>

                            </div>

                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            $('#reportrange').daterangepicker({
                startDate: moment(),
                endDate: moment(),
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });

        });

        // ✅ OPEN VERIFY POPUP
        function openVerifyModal(id) {

            $('#verifyModal').modal('show');
            $('#modalContent').html('Loading...');

            $.get('/sale/verify/' + id, function(data) {
                $('#modalContent').html(data);
            });
        }

        $(document).ready(function() {

            // Date Picker
            $('#reportrange').daterangepicker({
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });

            // 🔥 LOAD DATA
            function loadData() {

                $.ajax({
                    url: "{{ route('sales.salas-report') }}",
                    type: "GET",
                    data: {
                        branch_id: $('#branch_filter').val(),
                        date_range: $('#reportrange').val(),
                        shift_id: window.selectedShiftId
                    },
                    success: function(data) {
                        $('#storeData').html(data);
                    }
                });
            }

            // 🔥 FILTER EVENTS
            $('#branch_filter').change(loadData);
            $('#reportrange').on('apply.daterangepicker', loadData);

            $('#reset-filters').click(function() {
                $('#branch_filter').val('');
                $('#reportrange').val('');
                window.selectedShiftId = '';
                loadData();
            });

            // 🔥 ROW TOGGLE
            $(document).on('click', '.store-row', function() {

                let id = $(this).data('id');
                $('#sales-' + id).toggleClass('d-none');
            });

            // 🔥 VIEW BUTTON (EXPAND + OPTIONAL POPUP)
            $(document).on('click', '.view-row', function(e) {

                e.stopPropagation();

                let id = $(this).data('id');

                // expand row
                $('#sales-' + id).removeClass('d-none');

            });

        });

        $(document).on('click', '.open-shift', function(e) {

            e.stopPropagation(); // prevent double trigger

            let storeId = $(this).data('shift');

            // ✅ 1. EXPAND ROW
            $('#sales-' + storeId).removeClass('d-none');

            // OR toggle:
            // $('#sales-' + storeId).toggleClass('d-none');

            // ✅ 2. OPEN SHIFT MODAL (YOUR EXISTING CODE)
            $.ajax({
                url: '{{ url('shift-manage/close-shift') }}/' + storeId,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {

                    if (response.code != 200) {
                        Swal.fire('Info', response.message, 'info');
                    } else {

                        $('#shiftSummaryContent').html(response.html);

                        const modal = new bootstrap.Modal(
                            document.getElementById('shiftSummaryModal')
                        );
                        modal.show();
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to load shift.', 'error');
                }
            });

        });

        let popupIndex = 0;

        /* =========================
           OPEN MODAL + BRANCH LOGIC
        ========================= */
        $(document).on('click', '.open-add-sale', function(e) {

            e.stopPropagation();

            let branchId = $(this).data('branch');
            let shiftId = $(this).data('shift');

            $('#addSalesModal input[name="branch_id"]').val(branchId);
            $('#addSalesModal input[name="shift_id"]').val(shiftId);

            // ✅ PARTY / COMMISSION
            if (branchId == 1) {
                $('#popup-party-id').show();
                $('#popup-commission-id').hide();
            } else {
                $('#popup-party-id').hide();
                $('#popup-commission-id').show();
            }

            // 🔥 DEFAULT PAYMENT = CASH
            $('input[name="payment_method"][value="cash"]').prop('checked', true);

            $('#popup-cash').val('');
            $('#popup-upi').val('');

            let modal = new bootstrap.Modal(document.getElementById('addSalesModal'));
            modal.show();
        });

        /* =========================
           ADD PRODUCT
        ========================= */
        $('#popup-add-item').click(function() {

            let product = $('#popup-product-id option:selected');

            let id = product.val();
            let name = product.data('name');

            // ✅ FIXED PRICE SOURCE
            let price = parseFloat(product.data('sell_price')) || 0;

            let qty = parseInt($('#popup-qty').val()) || 1;

            if (!id) {
                alert('Select product');
                return;
            }

            // ✅ prevent duplicate → increase qty
            let existingRow = null;

            $('#popup-items-body tr').each(function() {
                let existingId = $(this).find('input[name*="product_id"]').val();
                if (existingId == id) {
                    existingRow = $(this);
                }
            });

            if (existingRow) {
                let input = existingRow.find('.qty-input');
                input.val(parseInt(input.val()) + qty);
                popupUpdateTotals();
                return;
            }

            let total = price * qty;

            let row = `
                <tr>
                    <td>
                        ${name}
                        <input type="hidden" name="items[${popupIndex}][product_id]" value="${id}">
                    </td>

                    <td>
                        <input type="number" 
                            name="items[${popupIndex}][quantity]" 
                            value="${qty}" 
                            class="form-control qty-input"
                            data-price="${price}" 
                            data-discount="${price}">
                    </td>

                    <td class="item-price">₹${price}</td>

                    <td class="item-total">₹${Math.ceil(total)}</td>

                    <td>
                        <button type="button" class="btn btn-danger remove-row">X</button>
                    </td>
                </tr>
                `;

            $('#popup-items-body').append(row);
            popupIndex++;

            popupUpdateTotals(); // 🔥 IMPORTANT

            $('#popup-product-id').val('');
            $('#popup-qty').val('');
        });


        /* =========================
           REMOVE ITEM
        ========================= */
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
            popupUpdateTotals();
        });


        /* =========================
           QTY CHANGE
        ========================= */
        $(document).on('input', '.qty-input', function() {

            let qty = parseFloat($(this).val()) || 1;
            let price = parseFloat($(this).data('price')) || 0;
            let discount = parseFloat($(this).data('discount')) || price;

            let partyId = $('#popup-party-id').val();
            let commissionId = $('#popup-commission-id').val();

            let finalPrice = (partyId || commissionId) ? discount : price;

            let rowTotal = finalPrice * qty;

            let row = $(this).closest('tr');

            row.find('.item-price').text('₹' + finalPrice);
            row.find('.item-total').text('₹' + Math.ceil(rowTotal));

            popupUpdateTotals();
        });


        /* =========================
           TOTAL CALCULATION
        ========================= */
        function popupUpdateTotals() {

            let grandTotal = 0;
            let discountTotal = 0;

            const partyId = $('#popup-party-id').val();
            const commissionId = $('#popup-commission-id').val();

            $('#popup-items-body tr').each(function() {

                let qty = parseFloat($(this).find('.qty-input').val()) || 0;
                let price = parseFloat($(this).find('.qty-input').data('price')) || 0;
                let discount = parseFloat($(this).find('.qty-input').data('discount')) || price;

                let finalPrice = (partyId || commissionId) ? discount : price;

                let total = finalPrice * qty;

                $(this).find('.item-price').text('₹' + finalPrice);
                $(this).find('.item-total').text('₹' + Math.ceil(total));

                grandTotal += total;
                discountTotal += (price - discount) * qty;
            });

            $('#popup-total').text(Math.ceil(grandTotal));
            $('#popup-grand-total').text(Math.ceil(grandTotal));
            $('#popup-discount').text(discountTotal.toFixed(2));
        }


        /* =========================
           PARTY DISCOUNT
        ========================= */
        $('#popup-party-id').change(function() {

            let partyId = $(this).val();

            // reset commission
            $('#popup-commission-id').val('');

            if (!partyId) {
                popupUpdateTotals();
                return;
            }

            $('#popup-items-body tr').each(function() {

                let row = $(this);
                let productId = row.find('input[name*="product_id"]').val();

                $.get(`/party-customer-discount/${partyId}/${productId}`, function(res) {

                    let discount = res.discount ? parseFloat(res.discount) : 0;

                    // fallback if no discount
                    if (!discount) {
                        discount = parseFloat(row.find('.qty-input').data('price')) || 0;
                    }

                    row.find('.qty-input').data('discount', discount);

                    popupUpdateTotals();
                });
            });
        });

        /* =========================
           COMMISSION SELECT
        ========================= */
        $('#popup-commission-id').change(function() {

            let commissionId = $(this).val();

            // reset party
            $('#popup-party-id').val('');

            if (!commissionId) {
                popupUpdateTotals();
                return;
            }

            // 🔥 RESET ALL DISCOUNT TO DEFAULT
            $('#popup-items-body tr').each(function() {

                let row = $(this);
                let price = parseFloat(row.find('.qty-input').data('price')) || 0;

                // commission → use default discount (product discount)
                row.find('.qty-input').data('discount', price);
            });

            popupUpdateTotals();
        });

        /* =========================
           PAYMENT METHOD
        ========================= */
        $('input[name="payment_method"]').change(function() {

            let total = parseFloat($('#popup-grand-total').text()) || 0;

            if ($(this).val() === 'cash') {
                $('#popup-cash').val(total);
                $('#popup-upi').val('');
            }

            if ($(this).val() === 'online') {
                $('#popup-upi').val(total);
                $('#popup-cash').val('');
            }

            if ($(this).val() === 'cashupi') {
                $('#popup-cash').val(total);
                $('#popup-upi').val(0);
            }

            if ($(this).val() === 'credit') {
                $('#popup-cash').val('');
                $('#popup-upi').val('');
            }
        });


        /* =========================
           SUBMIT
        ========================= */
        $('#popup-sale-form').submit(function(e) {

            e.preventDefault();

            $.ajax({
                url: "{{ route('sales.invoice.insert-sale') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function() {

                    Swal.fire("Success", "Sale Added Successfully", "success");

                    $('#addSalesModal').modal('hide');

                    location.reload();
                },
                error: function() {
                    Swal.fire("Error", "Something went wrong", "error");
                }
            });
        });

        function resetDiscountToDefault() {

            $('#popup-items-body tr').each(function() {

                let row = $(this);
                let price = parseFloat(row.find('.qty-input').data('price')) || 0;

                row.find('.qty-input').data('discount', price);
            });

            popupUpdateTotals();
        }

        $('#popup-party-id, #popup-commission-id').on('change', function() {

            let party = $('#popup-party-id').val();
            let commission = $('#popup-commission-id').val();

            if (!party && !commission) {
                resetDiscountToDefault();
            }
        });
    </script>
@endsection
