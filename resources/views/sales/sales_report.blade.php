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

            const modalElement = document.getElementById('invoiceModal');
            const modal = new bootstrap.Modal(modalElement);

            modal.show();

            $('#invoiceModalContent').html('Loading...');

            $.get('/invoice/view-modal/' + id, function(data) {
                $('#invoiceModalContent').html(data);
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

        function changeVerifyStatus(type, shift_id, isChecked) {

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
                            shift_id: shift_id
                        },
                        success: function(response) {
                            Swal.fire("Success!", "Updated", "success")
                                .then(() => location.reload());
                        }
                    });

                } else {
                    location.reload();
                }

            });
        }

        function verifyFullShift(shift_id, isChecked) {

            let status = isChecked ? 'verify' : 'unverify';

            Swal.fire({
                title: "Are you sure want to "+status+" Full Shift?",
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
                            status: status
                        },
                        success: function(response) {
                            Swal.fire("Done!", "Shift Verified", "success")
                                .then(() => location.reload());
                        }
                    });

                }
            });
        }

        function verifyInvoice(invoice_id, isChecked) {

            let status = isChecked ? 'verify' : 'unverify';

            Swal.fire({
                title: "Are you sure you want to "+status+" this invoice?",
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
    </script>
@endsection
