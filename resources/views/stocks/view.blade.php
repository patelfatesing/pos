@extends('layouts.backend.datatable_layouts')

<style>
    .card-header {
        background-color: #528da1 !important;
    }
</style>
@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Wrapper Start -->

    <div class="content-page">
        <div class="container-fluid">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <h4 class="mb-0">Stock Request Items Detail</h4>
                <div>
                    <a href="{{ route('stock.requestList') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <p><strong>From Store:</strong> {{ $stockRequest->branch->name ?? 'warehouse' }}</p>
                        </div>
                        <div class="col-sm-4">
                            <p><strong>To Store:</strong> {{ $stockRequest->tobranch->name ?? 'warehouse' }}</p>
                        </div>
                        <div class="col-sm-4">
                            <p><strong>Requested By:</strong> {{ $stockRequest->user->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-sm-4">
                            <p><strong>Status:</strong>
                                @if ($stockRequest->status === 'pending')
                                    <span class="badge bg-warning">Pending
                                    </span>
                                @elseif ($stockRequest->status === 'rejected')
                                    <span class="badge bg-danger">Rejected
                                    </span>
                                @else
                                    <span class="badge bg-success">Approved
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="col-sm-4">
                            <p><strong>Date:</strong> {{ $stockRequest->requested_at->format('d M Y h:i A') }}</p>
                        </div>
                        <div class="col-sm-4">
                            <p><strong>Notes:</strong> {{ $stockRequest->notes ?? '-' }}</p>
                        </div>
                        @if ($stockRequest->status === 'rejected')
                            <div class="col-sm-4">
                                <p><strong>Reject Reason:</strong> {{ $stockRequest->reject_reason ?? '-' }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                {{-- <div class="card-header d-flex flex-wrap align-items-center justify-content-between mb-2"><strong>Requested Items</strong></div> --}}
                <div class="card-body p-0">
                    <table class="table table-bordered mb-2" id="stock-requests-details-table">
                        <thead>
                            <tr>
                                <th>Sr No</th>
                                <th>Product</th>
                                <th>Size</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" style="text-align:right">Total Quantity:</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <!-- Page end  -->
        </div>
    </div>

    <!-- Wrapper End-->

    <div class="modal fade bd-example-modal-lg" id="approvedStockModal" tabindex="-1" role="dialog"
        aria-labelledby="approvedStockModalLabel" aria-hidden="true">
        <div class="modal-dialog  modal-lg" role="document">
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
                        <table class="table table-bordered">
                            <thead>

                            </thead>
                            <tbody id="requested-store-info">
                            </tbody>
                            <tbody id="request-items-body">
                                <!-- Dynamically loaded -->
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


            $(document).on('click', '.open-approve-modal', function() {
                const id = $(this).data('id');

                $('#request_id').val(id);
                $('#request-items-body').html('<tr><td colspan="3">Loading...</td></tr>');

                $.get('{{ url('stock-requests/popup-details/') }}/' + id, function(res) {
                    $('#from_store_id').val(res.stockRequest.store_id);

                    let storeHtml = '';
                    let requestedStoreInfo = `
                        <div class="">
                            <h5 class="mb-2 text-primary">Requested From: ${res.stockRequest.branch_name ?? 'N/A'}</h5>
                        </div>
                    `;
                    // Loop through each store group
                    Object.values(res.items_by_store).forEach(store => {
                        let rows = '';
                        store.items.forEach(item => {
                            rows += `
                                <tr class="mt-2">
                                    <td>${item.product_name}</td>
                                    <td>${item.req_quantity}</td>
                                    <td>${item.store_ava_quantity}</td>
                                    <td>
                                        <input type="number" class="form-control" name="items[${item.store_id}][${item.product_id}]" value="${item.req_quantity}" min="1">
                                    </td>
                                </tr>
                            `;
                        });

                        storeHtml += `
                                <div class="mb-2 mt-2">
                                    <h5 class="text-success">${store.store_name}</h5>
                                    <table class="table table-bordered">
                                        <thead class="table-secondary">
                                            <tr>
                                                <th>Product</th>
                                                <th>Requested Qty</th>
                                                <th>Available in</th>
                                                <th>Approve Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${rows}
                                        </tbody>
                                    </table>
                                </div>
                            `;
                    });

                    // Inject into modal
                    $('#requested-store-info').html(requestedStoreInfo);
                    $('#request-items-body').html(storeHtml);
                    $('#approvedStockModal').modal('show');
                });

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
                        // $('#stock-requests-table').DataTable().ajax.reload(null, false);
                        setTimeout(function() {
                            location.reload();
                        }, 500);

                    },
                    error: function(xhr) {
                        alert('Approval failed');
                    }
                });
            });

            let stockRequestId = '{{ $stockRequest->id ?? '' }}';
            $('#stock-requests-details-table').DataTable({
                pageLength: 10, // fixed typo here: pagelength -> pageLength
                responsive: true,
                processing: true,
                ordering: true,
                bLengthChange: true,
                serverSide: true,
                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },
                ajax: {
                    url: '{{ url('stock/get-stock-request-details') }}',
                    type: "post",
                    data: function(d) {
                        d.stock_request_id = stockRequestId;
                        d._token = '{{ csrf_token() }}';
                    }
                },
                dom: "<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'f l>>t<'row'<'col-md-6'i><'col-md-6'p>>",
                initComplete: function() {
                    $('.dataTables_filter input').attr("placeholder", "Search List...");
                },
                // Add serial number column as the first column
                columns: [{
                        data: null, // serial number doesn't come from server
                        name: 'serial_number',
                        orderable: false, // usually you don't want to sort by serial number
                        searchable: false,
                        render: function(data, type, row, meta) {
                            // meta.row is the index of the row on current page (starting at 0)
                            // meta.settings._iDisplayStart is the offset of the first row on this page
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'size'
                    },
                    {
                        data: 'quantity'
                    }
                    // add other columns if needed
                ],
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();

                    // Remove formatting to get integer for summation
                    var intVal = function(i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                            i : 0;
                    };

                    // Total over this page
                    var pageTotal = api
                        .column(3, {
                            page: 'current'
                        }) // column index 4 = Quantity
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Update footer
                    $(api.column(3).footer()).html(pageTotal);
                },
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [2]
                }],
                lengthMenu: [
                    [10, 25, 50],
                    ['10 rows', '25 rows', '50 rows', 'All']
                ],
                
                buttons: [{
                    extend: 'collection',
                    text: '<i class="fa fa-download"></i>',
                    className: 'btn btn-info btn-sm',
                    autoClose: true,
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '<i class="fa fa-file-excel-o"></i> Excel',
                            title: 'Stock Inventory',
                            filename: 'stock_inventory',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf-o"></i> PDF',
                            filename: 'stock_inventory',
                            orientation: 'landscape',
                            pageSize: 'A4',

                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                            },

                            customize: function(doc) {

                                // REMOVE default title
                                doc.content.splice(0, 1);

                                doc.styles.tableHeader.alignment = 'center';

                                var tableBody = doc.content[0].table.body;

                                for (var i = 1; i < tableBody.length; i++) {
                                    tableBody[i][0].alignment = 'center';
                                    tableBody[i][3].alignment = 'center';
                                    tableBody[i][4].alignment = 'center';
                                    tableBody[i][5].alignment = 'center';
                                    tableBody[i][6].alignment = 'center';
                                    tableBody[i][7].alignment = 'center';
                                    tableBody[i][8].alignment = 'center';
                                    tableBody[i][10].alignment = 'center';
                                }

                                // HEADER
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
                                            text: 'Stock Inventory Report',
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
                }]
            });

        });
    </script>
@endsection
