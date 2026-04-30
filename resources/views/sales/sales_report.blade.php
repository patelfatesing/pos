@extends('layouts.backend.datatable_layouts')
<style>
    .table td,
    .table th {
        vertical-align: middle;
        font-size: 14px;
    }

    .table tbody tr:hover {
        background-color: #f5f7fa;
    }

    .store-row:hover {
        background: #eef2f7 !important;
    }

    .sales-row {
        transition: all 0.2s ease;
    }

    .table td,
    .table th {
        vertical-align: middle;
        font-size: 14px;
    }

    .badge.bg-light {
        border: 1px solid #ddd;
        padding: 6px 8px;
    }

    /* Switch container */
    .switch {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 20px;
    }

    /* Hide default checkbox */
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    /* Slider */
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .3s;
    }

    /* Circle */
    .slider:before {
        position: absolute;
        content: "";
        height: 14px;
        width: 14px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
    }

    /* Checked state */
    .switch input:checked+.slider {
        background-color: #28a745;
        /* green */
    }

    .switch input:checked+.slider:before {
        transform: translateX(18px);
    }

    /* Rounded */
    .slider.round {
        border-radius: 20px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    .modal-backdrop.show:nth-of-type(2) {
        z-index: 1060;
    }

    #invoiceModal {
        z-index: 1070;
    }

    .badge:hover {
        transform: scale(1.1);
        transition: 0.2s;
    }

    .store-row td:nth-child(1),
    .store-row td:nth-child(2) {
        cursor: pointer;
    }

    /* Hide default switch */
    .custom-switch .custom-control-label::before {
        display: none;
    }

    .custom-switch .custom-control-label::after {
        display: none;
    }

    /* Pill base */
    .custom-switch-text .custom-control-label {
        padding: 10px 20px;
        border-radius: 30px;
        font-weight: 600;
        color: #fff;
        cursor: pointer;
        display: inline-block;
        transition: 0.3s;
    }

    /* UNVERIFIED (default) */
    .verify-switch+.custom-control-label {
        background: linear-gradient(135deg, #ff5722, #ff7043);
    }

    /* VERIFIED */
    .verify-switch:checked+.custom-control-label {
        background: linear-gradient(135deg, #28a745, #43d67c);
    }

    /* Dynamic text */
    .verify-switch+.custom-control-label::after {
        content: attr(data-off-label);
    }

    .verify-switch:checked+.custom-control-label::after {
        content: attr(data-on-label);
    }

    /* Hover effect */
    .custom-control-label:hover {
        opacity: 0.9;
        transform: scale(1.05);
    }
</style>
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
            <div class="card border-0 shadow-sm">

                <div class="card-body p-0">
                    <div class="table-responsive">

                        <table class="table table-hover mb-0" id="stock-table">

                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Store</th>
                                    <th class="text-end pe-3">Total Amount (₹)</th>
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
    </div>
</div>

<div class="modal fade" id="subAdminModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Sub Admin Verification</h5>
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <div class="verify-box d-flex justify-content-around align-items-center">

                    <div class="verify-item text-center">
                        <small class="text-muted d-block mb-1">Sales</small>
                        <label class="switch">
                            <input type="checkbox" id="modal_sales"
                                onchange="handleModalChange('sales', this.checked)">
                            <span class="slider round"></span>
                        </label>
                    </div>

                    <div class="verify-item text-center">
                        <small class="text-muted d-block mb-1">Transfer</small>
                        <label class="switch">
                            <input type="checkbox" id="modal_transfer"
                                onchange="handleModalChange('transfer', this.checked)">
                            <span class="slider round"></span>
                        </label>
                    </div>

                    <div class="verify-item text-center">
                        <small class="text-muted d-block mb-1">Shift</small>
                        <label class="switch">
                            <input type="checkbox" id="modal_shift"
                                onchange="handleModalChange('shift', this.checked)">
                            <span class="slider round"></span>
                        </label>
                    </div>

                </div>

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

<!-- Invoice Modal -->
<div class="modal fade" id="invoiceModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Invoice Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" id="invoiceModalContent">
                Loading...
            </div>

        </div>
    </div>
</div>

<div class="modal fade bd-example-modal-lg" id="salesCustPhotoShowModal" tabindex="-1" role="dialog"
    aria-labelledby="salesCustPhotoShowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" id="salesCustPhotoModalContent">
        </div>
    </div>
</div>

<!-- PDF Modal -->
<div class="modal fade" id="pdfModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Invoice PDF Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <iframe id="pdfFrame" width="100%" height="600px" frameborder="0"></iframe>
            </div>

        </div>
    </div>
</div>

<!-- Edit PDF Modal -->
<div class="modal fade" id="editPdfModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Edit Invoice PDF</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <iframe id="editPdfFrame" width="100%" height="600px" frameborder="0"></iframe>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="storeRowModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Store Shift Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" id="storeRowContent">
                Loading...
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="addSalesModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addSalesModalTitle">Add Sales</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" id="addSalesContent">
                Loading...
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="transferModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="transferModalTitle">Transfer List</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body" id="transferModalContent">
                Loading...
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="transferActionModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="transferActionTitle">Transfer</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body" id="transferActionContent">
                Loading...
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).on('click', '.store-row td:nth-child(1), .store-row td:nth-child(2)', function(e) {

        // ❌ ignore clicks on buttons/links/switch
        if ($(e.target).closest('a, button, input, label').length) {
            return;
        }

        let row = $(this).closest('.store-row');
        let shiftId = row.find('.open-shift').data('shift');

        openShiftPopup(shiftId);
    });

    function openStorePopup(shiftId) {

        $('#storeRowContent').html('Loading...');

        $('#storeRowModal').modal('show');

        $.ajax({
            url: '{{ url('shift-manage/close-shift-model') }}/' + shiftId,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {

                if (response.code != 200) {
                    $('#storeRowContent').html('<div class="text-danger">' + response.message + '</div>');
                } else {
                    $('#storeRowContent').html(response.html);
                }
            },
            error: function() {
                $('#storeRowContent').html('<div class="text-danger">Failed to load data</div>');
            }
        });
    }

    let currentShiftId = null;
    let currentBranchId = null;

    function openShiftPopup(shiftId) {

        currentShiftId = shiftId; // 🔥 store it

        $('#storeRowContent').html('Loading...');

        const modal = new bootstrap.Modal(document.getElementById('storeRowModal'));
        modal.show();

        $.get('/sales/shift-single-data/' + shiftId, function(data) {
            $('#storeRowContent').html(data);
        });
    }

    function openAddSalesModal(branchId, shiftId) {

        $('#addSalesContent').html('Loading...');

        $('#addSalesModal').modal('show');

        $.get('/sales/add-sales-modal/' + branchId + '/' + shiftId, function(data) {
            $('#addSalesContent').html(data);
        });
    }

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
                    console.log('sdf');
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
            url: '{{ url('shift-manage/close-shift-model') }}/' + storeId,
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

    function openInvoiceModal(id) {

        // ✅ Close store modal first
        $('#storeRowModal').modal('hide');

        // small delay (important)
        setTimeout(function() {

            $('#invoiceModal').modal('show');

            $('#invoiceModalContent').html('Loading...');

            $.get('/invoice/view-modal/' + id, function(data) {
                $('#invoiceModalContent').html(data);
            });

        }, 300);
    }

    function editInvoiceModal(id) {
        $('#addSalesModalTitle').text('Edit Sales'); // ✅ set title
        $('#addSalesContent').html('Loading...');

        $('#addSalesModal').modal('show'); // reuse same modal

        $.get('/sales/edit-sales-modal/' + id, function(data) {
            $('#addSalesContent').html(data);
        });
    }

    $(document).on('click', '.view-pdf', function() {

        let invoiceNo = $(this).data('invoice');

        let url = '/storage/invoices/' + invoiceNo + '.pdf';

        // set iframe src
        $('#pdfFrame').attr('src', url);

        // open modal (Bootstrap 5)
        const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
        modal.show();
    });

    $(document).on('click', '.view-photo', function() {

        let id = $(this).data('id');
        let party = $(this).data('party') || '';
        let commission = $(this).data('commission') || '';

        showPhoto(id, commission, party);
    });

    const salesImgViewBase = "{{ url('sales-img-view') }}";

    function showPhoto(id, commission_user_id = '', party_user_id = '') {
        let url =
            `${salesImgViewBase}/${id}?commission_user_id=${commission_user_id}&party_user_id=${party_user_id}`;

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('#salesCustPhotoModalContent').html(response);
                const modal = new bootstrap.Modal(document.getElementById('salesCustPhotoShowModal'));
                modal.show();

            },
            error: function() {
                alert('Photos not found.');
            }
        });
    }

    function changeVerifyStatus(type, shift_id, isChecked, role) {

        let status = isChecked ? 'verify' : 'unverify';

        Swal.fire({
            title: "Are you sure?",
            text: "Are you sure want to all sales " + status + "?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, change it!",
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    type: "POST",
                    url: "{{ route('shift.verify.status') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        type: type,
                        status: status,
                        shift_id: shift_id,
                        role: role
                    },
                    success: function(response) {
                        Swal.fire("Success!", "Sales has been Updated", "success")
                            .then();
                    }
                });

            } else {
                location.reload();
            }

        });
    }

    function verifyFullShift(shift_id, isChecked, role) {

        let status = isChecked ? 'verify' : 'unverify';

        Swal.fire({
            title: "Are you sure want to " + status + " Full Shift?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes",
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    type: "POST",
                    url: "{{ route('shift.verify.all') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        shift_id: shift_id,
                        status: status,
                        role: role
                    },
                    success: function(response) {
                        Swal.fire("Done!", "Shift has been Verified", "success")
                            .then();
                    }
                });

            }
        });
    }

    // success: function(response) {
    //                     Swal.fire("Done!", "Shift Verified", "success")
    //                         .then(() => location.reload());
    //                 }

    function verifyInvoice(invoice_id, isChecked) {

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
                        status: status
                    },
                    success: function(response) {
                        Swal.fire("Done!", "Invoice has been Verified", "success")
                            .then(() => '');
                    }
                });

            }
        });
    }

    function handleClick(type, shift_id, branch_id) {

        if (type === 'transfer') {

            window.location.href = "{{ route('stock-transfer.list') }}" +
                "?branch_id=" + branch_id +
                "&shift_id=" + shift_id +
                "&verify=yes" +
                "&type=admin";
        }
    }

    $(document).off('submit', '#invoice-items-form').on('submit', '#invoice-items-form', function(e) {

        e.preventDefault(); // 🔥 MUST

        let form = $(this);

        let branchId = $('input[name="branch_id"]').val();
        let partyId = $('#party-id').val();

        // ✅ VALIDATION (MOVED HERE)
        if (branchId == 1 && !partyId) {
            Swal.fire("Validation Error", "Select Party Customer", "warning");
            return false;
        }

        let btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: form.attr('action'),
            type: "POST",
            data: form.serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {

                if (response.status) {

                    // ✅ CLOSE MODAL
                    $('#addSalesModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        timer: 1200,
                        showConfirmButton: false
                    });

                    // ✅ REFRESH SHIFT DATA
                    refreshShiftPopup();

                    if (typeof loadData === 'function') {
                        loadData();
                    }
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                Swal.fire("Error!", "Check console", "error");
            },
            complete: function() {
                btn.prop('disabled', false).text('Save Invoice Items');
            }
        });

    });

    $(document).off('submit', '#invoice-items-form').on('submit', '#invoice-items-form', function(e) {

        e.preventDefault();

        let form = $(this);

        let btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: form.attr('action'),
            type: "POST",
            data: form.serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {

                if (response.status) {

                    // ✅ CLOSE MODAL
                    $('#addSalesModal').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        timer: 1200,
                        showConfirmButton: false
                    });

                    // ✅ REFRESH SHIFT
                    refreshShiftPopup();
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                Swal.fire("Error!", "Check console", "error");
            },
            complete: function() {
                btn.prop('disabled', false).text('Save Invoice Items');
            }
        });

    });

    function refreshShiftPopup() {

        if (!currentShiftId) return;

        $('#storeRowContent').html('Loading...');

        $.get('/sales/shift-single-data/' + currentShiftId, function(data) {
            $('#storeRowContent').html(data);
        });
    }

    function openTransferModal(shift_id, branch_id) {

        // ✅ Set Title
        $('#transferModalTitle').text('Transfer - Shift #' + shift_id);

        // ✅ Show Modal
        $('#transferModal').modal('show');

        // ✅ Show loading
        $('#transferModalContent').html('Loading...');

        // ✅ Load view
        $.get('/stock-transfer/modal-list', function(view) {

            $('#transferModalContent').html(view);

            // ✅ INIT DATATABLE AFTER LOAD
            initTransferTable(branch_id, shift_id, 'admin');
        });
    }

    function openCreateTransfer() {

        // $('#transferModal').modal('hide'); // 👈 close list first

        setTimeout(() => {

            $('#transferActionModal').modal('show');

            $('#transferActionContent').html('Loading...');

            $.get('/transfer/modal/create', {
                branch_id: currentBranchId,
                shift_id: currentShiftId
            }, function(data) {
                $('#transferActionContent').html(data);
            });

        }, 300);
    }

    function openEditTransfer(id) {

        $('#transferActionTitle').text('Edit Transfer');

        $('#transferActionContent').html('Loading...');

        $('#transferActionModal').modal('show');

        $.get('/transfer/modal/edit/' + id, function(data) {
            $('#transferActionContent').html(data);
        });
    }

    function openViewTransfer(id) {

        $('#transferActionTitle').text('View Transfer');

        // 🔥 clear before open
        $('#transferActionContent').html('Loading...');

        $('#transferActionModal').modal('show');

        $.get('/transfer/modal/view/' + id, function(data) {
            $('#transferActionContent').html(data);
        });
    }

    $(document).off('submit', '#transferForm').on('submit', '#transferForm', function(e) {

        e.preventDefault();

        let form = $(this);
        let btn = $('#submitBtn');

        btn.prop('disabled', true).text('Processing...');

        $.ajax({
            url: form.attr('action'),
            type: "POST",
            data: form.serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },

            success: function() {

                // ✅ CLOSE ADD/EDIT MODAL
                $('#transferActionModal').modal('hide');


                $('#transferModalContent').html('Loading...');

                $.get('/stock-transfer/modal-list', function(view) {

                    $('#transferModalContent').html(view);

                    // 🔥 RE-INIT DATATABLE
                    initTransferTable(currentBranchId, currentShiftId, 'admin');

                });



                Swal.fire({
                    icon: 'success',
                    title: 'Transfer Added!',
                    timer: 1200,
                    showConfirmButton: false
                });
            },

            error: function(xhr) {

                let errors = xhr.responseJSON?.errors;

                if (errors) {
                    let msg = Object.values(errors).flat().join("\n");
                    alert(msg);
                } else {
                    alert('Something went wrong');
                }
            },

            complete: function() {
                btn.prop('disabled', false).text('Submit Transfer');
            }
        });

    });

    $('#transferActionModal').on('hidden.bs.modal', function() {

        // ✅ remove leftover focus
        document.activeElement.blur();

        // ✅ force focus to body
        $('body').focus();

    });

    function loadTransferList() {

        // ensure main modal is visible
        $('#transferModal').modal('show');

        $('#transferModalContent').html('Loading...');

        $.get('/stock-transfer/modal-list', function(view) {

            $('#transferModalContent').html(view);

            // 🔥 reload datatable properly
            setTimeout(function() {
                initTransferTable(currentBranchId, currentShiftId, 'admin');
            }, 100);
        });
    }


    function normalize(val) {
        return (val || '').toString().trim().toLowerCase();
    }

    function openSubAdminModal(
        shiftId,
        adminSales, adminTransfer, adminShift,
        subSales, subTransfer, subShift
    ) {
        currentShiftId = shiftId;

        // normalize values
        subSales = normalize(subSales);
        subTransfer = normalize(subTransfer);
        subShift = normalize(subShift);

        // SALES
        let salesCheckbox = document.getElementById('modal_sales');
        salesCheckbox.checked = (subSales === 'verify');

        // TRANSFER
        let transferCheckbox = document.getElementById('modal_transfer');
        transferCheckbox.checked = (subTransfer === 'verify');

        // SHIFT
        let shiftCheckbox = document.getElementById('modal_shift');
        shiftCheckbox.checked = (subShift === 'verify');

        $('#subAdminModal').modal('show');
    }

    function saveSubAdminVerify() {

        let sales = document.getElementById('modal_sales').checked;
        let transfer = document.getElementById('modal_transfer').checked;
        let shift = document.getElementById('modal_shift').checked;

        // SALES
        changeVerifyStatus('sales', currentShiftId, sales, 'sub_admin');

        // TRANSFER
        changeVerifyStatus('transfer', currentShiftId, transfer, 'sub_admin');

        // SHIFT
        verifyFullShift(currentShiftId, shift, 'sub_admin');

        $('#subAdminModal').modal('hide');
    }

    function handleModalChange(type, isChecked) {

        if (!currentShiftId) return;

        // SHIFT अलग function है
        if (type === 'shift') {
            verifyFullShift(currentShiftId, isChecked, 'sub_admin');
        } else {
            changeVerifyStatus(type, currentShiftId, isChecked, 'sub_admin');
        }
    }
</script>
@endsection