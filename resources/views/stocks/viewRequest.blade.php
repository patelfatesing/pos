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

                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">Stock Request Detail</h4>
                    </div>
                    <div>
                        <a href="{{ route('stock.requestList') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-4">
                                <p><strong>To Store:</strong> {{ $stockRequest->tobranch->name ?? 'warehouse' }}</p>
                            </div>
                            <div class="col-sm-4">
                                <p><strong>Requested By:</strong> {{ $stockRequest->user->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-sm-4">
                                <p><strong>Status:</strong>

                                    @if ($stockRequest->status === 'pending')
                                        <button class="btn btn-warning btn-sm open-approve-modal mt-1"
                                            data-id="{{ $stockRequest->id }}">
                                            Pending
                                        </button>
                                    @else
                                        <span class="badge bg-success">Approved</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-sm-4">
                                <p><strong>Date:</strong> {{ $stockRequest->requested_at->format('d M Y h:i A') }}</p>
                            </div>
                            <div class="col-sm-4">
                                <p><strong>Notes:</strong> {{ $stockRequest->notes ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><strong>Requested Items</strong></div>
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0" id="stock-requests-details-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Size</th>
                                    <th>Quantity</th>
                                    <th>From Store</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" style="text-align:right">Total Quantity:</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <!-- Page end  -->
            </div>
        </div>
    </div>
    <!-- Wrapper End-->

    <script>
        $(document).ready(function() {

            let stockRequestId = '{{ $stockRequest->id ?? '' }}';
            const fromStoreName = '{{ $stockRequest->branch->name ?? 'warehouse' }}';

            $('#stock-requests-details-table').DataTable({
                pageLength: 10,
                responsive: true,
                processing: true,
                ordering: true,
                bLengthChange: true,
                serverSide: true,

                ajax: {
                    url: '{{ url('stock/get-stock-request-details-approved') }}',
                    type: "post",
                    data: function(d) {
                        d.stock_request_id = stockRequestId;
                        d._token = '{{ csrf_token() }}';
                    }
                },

                columns: [
                    {
                        data: null,
                        name: 'serial_number',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'name' },
                    { data: 'size' },
                    { data: 'approved_quantity' },
                    {
                        data: 'source_store_id',
                        name: 'source_store_id',
                        render: function(data, type, row) {
                            return row.source_store_id ? row.source_store_id : 'N/A';
                        }
                    }                ],
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();

                    var intVal = function(i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ? i : 0;
                    };

                    var pageTotal = api
                        .column(3, { page: 'current' })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    $(api.column(3).footer()).html(pageTotal);
                },
                aoColumnDefs: [
                    {
                        bSortable: false,
                        aTargets: [2]
                    }
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
