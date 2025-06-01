@extends('layouts.backend.layouts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Wrapper Start -->
    <div class="wrapper">

        <div class="content-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Stock Transfer Details</h4>
                                </div>
                                <div>
                                    <a href="{{ route('stock-transfer.list') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to List
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="150">Transfer Number:</th>
                                                <td>{{ $stockTransfer->transfer_number }}</td>
                                            </tr>
                                            <tr>
                                                <th>From Branch:</th>
                                                <td>{{ $stockTransfer->fromBranch->name }}</td>
                                            </tr>
                                            <tr>
                                                <th>To Branch:</th>
                                                <td>{{ $stockTransfer->toBranch->name }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="150">Status:</th>
                                                <td>
                                                    <span class="badge bg-{{ $stockTransfer->status === 'completed' ? 'success' : ($stockTransfer->status === 'pending' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst($stockTransfer->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Transfer Date:</th>
                                                <td>{{ $stockTransfer->transferred_at ? date('d-m-Y H:i', strtotime($stockTransfer->transferred_at)) : 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Transferred By:</th>
                                                <td>{{ $stockTransfer->transferredBy->name }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <h5 class="mb-3">Products Details</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Product</th>
                                                        <th>Quantity</th>
                                                        <th>Category</th>
                                                        <th>Sub Category</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($transferProducts as $index => $product)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $product->product->name }}</td>
                                                        <td>{{ $product->quantity }}</td>
                                                        <td>{{ $product->product->category->name ?? 'N/A' }}</td>
                                                        <td>{{ $product->product->subcategory->name ?? 'N/A' }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="2" class="text-end">Total Products: {{ $stockTransfer->total_products }}</th>
                                                        <th>{{ $stockTransfer->total_quantity }}</th>
                                                        <th colspan="3"></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                @if($stockTransfer->status === 'pending')
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <form action="{{ route('stock-transfer.update-status', $stockTransfer->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" name="status" value="completed" class="btn btn-success">
                                                <i class="fas fa-check"></i> Complete Transfer
                                            </button>
                                            <button type="submit" name="status" value="cancelled" class="btn btn-danger">
                                                <i class="fas fa-times"></i> Cancel Transfer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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

                ajax: {
                    url: '{{ url('stock/get-stock-request-details') }}',
                    type: "post",
                    data: function(d) {
                        d.stock_request_id = stockRequestId;
                        d._token = '{{ csrf_token() }}';
                    }
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
                        data: 'brand'
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
                        .column(4, {
                            page: 'current'
                        }) // column index 4 = Quantity
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Update footer
                    $(api.column(4).footer()).html(pageTotal);
                },
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [2]
                }],
                dom: "Bfrtip",
                lengthMenu: [
                    [10, 25, 50],
                    ['10 rows', '25 rows', '50 rows', 'All']
                ],
                buttons: ['pageLength']
            });

        });
    </script>
@endsection

@section('styles')
<style>
    .table-borderless th,
    .table-borderless td {
        padding: 0.5rem 0;
    }
    .badge {
        font-size: 0.85em;
        padding: 0.35em 0.65em;
    }
</style>
@endsection
