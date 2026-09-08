@extends('layouts.backend.layouts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script src="{{ asset('assets/js/jquery-3.6.0.min.js')}}"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<style>
.table-responsive #stock-requests-details-table_wrapper #stock-requests-details-table_filter {
    margin-top: 12px;
    margin-bottom: 12px;
}

#stock-requests-details-table_wrapper #stock-requests-details-table_info {
    padding-top: 0px !important;
}

.card #header-card-body {
    padding: 12px;
}

#stock-requests-details-table th:nth-child(3),
#stock-requests-details-table td:nth-child(3),
#stock-requests-details-table th:nth-child(4),
#stock-requests-details-table td:nth-child(4),
#stock-requests-details-table th:nth-child(5),
#stock-requests-details-table td:nth-child(5) {
    text-align: center !important;
}

</style>
@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Wrapper Start -->
    <div class="wrapper">

        <div class="content-page">
            <div class="container-fluid">

                <!-- Enhanced Header -->
                <div class="card shadow-sm mb-2">
                    <div class="card-header text-white d-flex align-items-center justify-content-between py-1" style="background-color: #AFBEFA;">
                        <h4 class="card-title text-white mb-0 text-left">Stock Request Detail</h4>
                        <div>
                            <a href="{{ route('stock.requestList') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </div>

                <div class="card mb-3">
                    <div class="card-body" id="header-card-body">
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
                                    @elseif ($stockRequest->status === 'rejected')
                                        <button class="btn btn-danger btn-sm mt-1">
                                            Rejected
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
                            @if ($stockRequest->status === 'rejected')
                                <div class="col-sm-4">
                                    <p><strong>Reject Reason:</strong> {{ $stockRequest->reject_reason ?? '-' }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="card shadow-sm">
                    <!-- <div class="card-header bg-light py-2"><strong>Requested Items</strong></div> -->
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0" id="stock-requests-details-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>Size</th>
                                        <th>Quantity</th>
                                        <th>From Store</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot class="table-light">
                                    <tr >
                                        <th colspan="3" style="text-align: right;">Total</th>
                                        <th style="text-align: center;"></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

                columns: [{
                        data: null,
                        name: 'serial_number',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
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
                        data: 'approved_quantity'
                    },
                    {
                        data: 'source_store_id',
                        name: 'source_store_id',
                        render: function(data, type, row) {
                            return row.source_store_id ? row.source_store_id : 'N/A';
                        }
                    }
                ],
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();

                    var intVal = function(i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ? i : 0;
                    };

                    var pageTotal = api
                        .column(3, {
                            page: 'current'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    $(api.column(3).footer()).html(pageTotal);
                },
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [0,1,2,4]
                }],
                order: [[3, 'desc']],
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
