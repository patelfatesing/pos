@extends('layouts.backend.datatable_layouts')


@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Wrapper Start -->

    <div class="content-page">
        <div class="container-fluid">
            <div class="row align-items-center mb-2">
                <div class="col-lg-12">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0">Stock Request Details</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive rounded mb-3">
                <table class="table table-striped table-bordered nowrap" id="stock-requests-table">
                    <thead class="bg-white text-uppercase">
                        <tr class="ligth ligth-data">
                            <th>Requested By Store</th>
                            <th data-type="date" data-format="YYYY/DD/MM">Requested At</th>
                            <th>Total Product</th>
                            <th>Total Quantity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <!-- Page end  -->
        </div>
    </div>

    <style>
        .table td {
            padding: 5px 20px !important;
        }
    </style>
    <!-- Wrapper End-->

    <!-- Approve Modal -->
    <!-- Modal -->
    <div class="modal fade bd-example-modal-lg" id="approvedStockModal" tabindex="-1" role="dialog"
        aria-labelledby="approvedStockModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="approveForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="approvedStockModalLabel">Approve Stock Request</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="request_id" id="request_id">
                        <input type="hidden" name="from_store_id" id="from_store_id">

                        <div class="mb-3">
                            <h5 class="mb-0 text-primary">Requested From: <span id="requested-from-text"></span></h5>
                        </div>

                        <div id="request-items-body">
                            <!-- Dynamic table will load here -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade bd-example-modal-lg" id="stockRejectModal" tabindex="-1" role="dialog"
        aria-labelledby="stockRejectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="stockRejectForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="stockRejectModalLabel">Stock Reject</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="store_id" id="store_id">
                            <input type="hidden" name="stock_req_id" id="stock_req_id">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Reason</label>
                                    <input type="text" name="reject_reason" class="form-control" id="reject_reason"
                                        placeholder="Enter Reject Reason">
                                    <span class="text-danger" id="reject_reason_error"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        var pdfLogo = "";

        function getTotalRequestedQty(data, productId) {
            return data
                .filter(row => row.product_id === productId)
                .reduce((sum, row) => sum + row.requested_qty, 0);
        }

        $(document).on('click', '.open-approve-modal', function() {
            const id = $(this).data('id');

            $('#request_id').val(id);
            $('#request-items-body').html('<p>Loading...</p>');

            $.get(`{{ url('stock-requests/popup-details/') }}/${id}`, function(res) {
                $('#from_store_id').val(res.source_id);
                $('#requested-from-text').text(res.stockRequest.branch_name ?? 'N/A');

                // Build table
                let rowsHtml = '';
                res.items_flat.forEach(row => {
                    const availableQty = row.store_ava_quantity ?? 0;
                    rowsHtml += `
                    <tr class="product-row" 
                        data-product-id="${row.product_id}" 
                        data-available="${row.store_ava_quantity}" 
                        data-requested="${row.requested_qty}">
                        <td>
                            <input type="checkbox" class="row-checkbox">
                        </td>
                        <td>${row.product_name}</td>
                        <td>${row.requested_qty}</td>
                        <td>${row.store_ava_quantity}</td>
                        <td>${row.store_name}</td>
                        <td>
                            <input type="number" class="form-control form-control-sm approve-input" 
                                name="items[${row.store_id}][${row.product_id}]" 
                                value="0" 
                                min="0" max="${row.store_ava_quantity}">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-row">🗑️</button>
                        </td>
                    </tr>
                `;
                });

                $('#request-items-body').html(`
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th></th>
                            <th>Product</th>
                            <th>Requested Qty</th>
                            <th>Available</th>
                            <th>Store</th>
                            <th>Approve Qty</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml}
                    </tbody>
                </table>
            `);

                $('#approvedStockModal').modal('show');
            });
        });

        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });

        $('#approveForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#request_id').val();
            const baseUrl = "{{ url('/stock-requests') }}";

            $.ajax({
                url: `${baseUrl}/${id}/approve`,
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    alert(res.message);
                    $('#approvedStockModal').modal('hide');
                    $('#stock-requests-table').DataTable().ajax.reload(null, false);
                },
                error: function() {
                    alert('Approval failed');
                }
            });
        });

        $(document).on('input', '.approve-input', function() {
            const $row = $(this).closest('.product-row');
            const productId = $row.data('product-id');
            applyProductApprovalRules(productId);
        });

        function applyProductApprovalRules() {
            $('.approve-input').off('input').on('input', function() {
                const $input = $(this);
                const $row = $input.closest('.product-row');
                const productId = $row.data('product-id');
                const requestedQty = parseFloat($row.data('requested')) || 0;

                let totalApproved = 0;

                // Calculate total approved qty for this product across all branches
                $(`.product-row[data-product-id="${productId}"]`).each(function() {
                    const val = parseFloat($(this).find('.approve-input').val()) || 0;
                    totalApproved += val;
                });

                // If total exceeds requested, alert and reset current input
                if (totalApproved > requestedQty) {
                    alert(`You can only approve up to ${requestedQty} qty for this product.`);

                    const currentInputVal = parseFloat($input.val()) || 0;
                    const approvedElsewhere = totalApproved - currentInputVal;
                    const remaining = requestedQty - approvedElsewhere;

                    $input.val(remaining >= 0 ? remaining : 0);
                }

                // Toggle checkbox for this row
                const finalVal = parseFloat($input.val()) || 0;
                $row.find('.row-checkbox').prop('checked', finalVal > 0);
            });
        }

        $('#stockRejectForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#stock_req_id').val();
            const baseUrl = "{{ url('/stock-requests') }}";

            $.ajax({
                url: `${baseUrl}/${id}/reject`,
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    alert(res.message);
                    location.reload();

                    $('#stock-requests-table').DataTable().ajax.reload(null, false);
                },
                error: function() {
                    alert('Approval failed');
                }
            });
        });

        function stock_reject(p_id, store_id) {
            $('#stock_req_id').val(p_id);
            $('#store_id').val(store_id);

            // Check if Bootstrap 5 (without jQuery) is being used
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var myModal = new bootstrap.Modal(document.getElementById('stockRejectModal'));
                myModal.show();
            } else {
                // For Bootstrap 4 (with jQuery)
                $('#stockRejectModal').modal('show');
            }
        }

        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if ($.fn.DataTable.isDataTable('#stock-requests-table')) {
                $('#stock-requests-table').DataTable().destroy();
            }

            $('#stock-requests-table').DataTable({

                pageLength: 10,
                responsive: true,
                processing: true,
                ordering: true,
                serverSide: true,

                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },

                ajax: {
                    url: '{{ url('stock/get-request-data') }}',
                    type: 'POST'
                },

                initComplete: function() {
                    $('.dataTables_filter input').attr("placeholder", "Search List...");
                },

                columns: [{
                        data: 'store'
                    },
                    {
                        data: 'requested_at'
                    },
                    {
                        data: 'total_product'
                    },
                    {
                        data: 'total_quantity'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],

                order: [
                    [1, 'desc']
                ],

                dom: "<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'f l>>t<'row'<'col-md-6'i><'col-md-6'p>>",
                lengthMenu: [
                    [10, 25, 50],
                    [10, 25, 50]
                ],
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [0, 4, 5]
                }],
                buttons: [{
                        extend: 'collection',
                        text: '<i class="fa fa-download"></i>',
                        className: 'btn btn-info btn-sm',
                        autoClose: true,

                        buttons: [

                            {
                                extend: 'excelHtml5',
                                text: '<i class="fa fa-file-excel-o"></i> Excel',
                                title: 'Stock Request Report',
                                filename: 'stock_request_report',
                                exportOptions: {
                                    columns: [0, 1, 2, 3, 4]
                                }
                            },

                            {
                                extend: 'pdfHtml5',
                                text: '<i class="fa fa-file-pdf-o"></i> PDF',
                                filename: 'stock_request_report',
                                orientation: 'landscape',
                                pageSize: 'A4',

                                exportOptions: {
                                    columns: [0, 1, 2, 3, 4]
                                },

                                customize: function(doc) {

                                    doc.content.splice(0, 1);

                                    doc.styles.tableHeader.alignment = 'center';

                                    var tableBody = doc.content[0].table.body;

                                    for (var i = 1; i < tableBody.length; i++) {

                                        tableBody[i][0].alignment = 'left';
                                        tableBody[i][1].alignment = 'center';
                                        tableBody[i][2].alignment = 'center';
                                        tableBody[i][3].alignment = 'center';
                                        tableBody[i][4].alignment = 'center';

                                    }

                                    doc.content.unshift({

                                        margin: [0, 0, 0, 12],

                                        columns: [

                                            {
                                                width: '33%',
                                                columns: [{
                                                        image: pdfLogo,
                                                        width: 30
                                                    },
                                                    {
                                                        text: 'LiquorHub',
                                                        fontSize: 11,
                                                        bold: true,
                                                        margin: [5, 8, 0, 0]
                                                    }
                                                ]
                                            },

                                            {
                                                width: '34%',
                                                text: 'Stock Request Report',
                                                alignment: 'center',
                                                fontSize: 16,
                                                bold: true,
                                                margin: [0, 8, 0, 0]
                                            },

                                            {
                                                width: '33%',
                                                text: 'Generated: ' + new Date()
                                                    .toLocaleString(),
                                                alignment: 'right',
                                                fontSize: 9,
                                                margin: [0, 8, 0, 0]
                                            }

                                        ]

                                    });

                                    doc.styles.tableHeader.fontSize = 10;
                                    doc.defaultStyle.fontSize = 9;

                                }

                            }

                        ]

                    }

                ]
            });
        });

        function getBase64Image(url, callback) {
            var img = new Image();
            img.crossOrigin = "Anonymous";

            img.onload = function() {
                var canvas = document.createElement("canvas");
                canvas.width = this.width;
                canvas.height = this.height;

                var ctx = canvas.getContext("2d");
                ctx.drawImage(this, 0, 0);

                var dataURL = canvas.toDataURL("image/png");
                callback(dataURL);
            };

            img.src = url;
        }

        getBase64Image("https://liquorhub.in/assets/images/logo.png", function(base64) {
            pdfLogo = base64;
        });
    </script>
@endsection
