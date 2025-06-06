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
                <h1>Stock Request Details</h1>
                <div class="table-responsive rounded mb-3">
                    <table class="table data-tables table-striped" id="stock-requests-table">
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
    </div>
    <style>
        .table td {
            padding: 5px 20px !important;
        }
    </style>
    <!-- Wrapper End-->

    <!-- Approve Modal -->

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
                    $('#stock-requests-table').DataTable().ajax.reload(null, false);

                },
                error: function(xhr) {
                    alert('Approval failed');
                }
            });
        });

        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#stock-requests-table').DataTable().clear().destroy();

            $('#stock-requests-table').DataTable({
                pagelength: 10,
                responsive: true,
                processing: true,
                ordering: true,
                bLengthChange: true,
                serverSide: true,

                "ajax": {
                    "url": '{{ url('stock/get-request-data') }}',
                    "type": "post",
                    "data": function(d) {},
                },
                aoColumns: [{
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
                        data: 'action'
                    }
                    // Define more columns as per your table structure

                ],
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [2, 3, 5]
                }],
                order: [
                    [1, 'desc']
                ],
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
