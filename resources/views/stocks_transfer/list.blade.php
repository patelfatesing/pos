@extends('layouts.backend.layouts')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .datatable th {
            white-space: nowrap;
        }

        .badge {
            font-size: 0.85em;
            padding: 0.35em 0.65em;
        }

        .total-info {
            font-size: 0.9em;
            color: #666;
            margin-top: 3px;
        }

        .table td {
            vertical-align: middle;
        }
    </style>
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Stock Transfers</h4>
                                </div>
                                <div>
                                    <a href="{{ route('stock-transfer.craete-transfer') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> New Transfer
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show">
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif

                                <div class="table-responsive">
                                    <table id="stock-transfers-table" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Transfer #</th>
                                                <th>From</th>
                                                <th>To</th>
                                                <th>Products</th>
                                                <th>Total Qty</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Created By</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#stock-transfers-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('stock-transfer.get-transfer-data') }}",
                columns: [{
                        data: 'transfer_number'
                    },
                    {
                        data: 'from'
                    },
                    {
                        data: 'to'
                    },
                    {
                        data: 'total_products',
                        render: function(data) {
                            return data + ' item(s)';
                        }
                    },
                    {
                        data: 'total_quantity',
                        render: function(data) {
                            return data + ' units';
                        }
                    },
                    {
                        data: 'transferred_at'
                    },
                    {
                        data: 'status',
                        render: function(data) {
                            let badgeClass = 'badge badge-';
                            switch (data.toLowerCase()) {
                                case 'completed':
                                    badgeClass += 'success';
                                    break;
                                case 'pending':
                                    badgeClass += 'warning';
                                    break;
                                case 'cancelled':
                                    badgeClass += 'danger';
                                    break;
                                default:
                                    badgeClass += 'info';
                            }
                            return '<span class="' + badgeClass + '">' + data + '</span>';
                        }
                    },
                    {
                        data: 'created_by'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [0, 3, 4, 5,6]
                }],
                order: [
                    [5, 'desc']
                ], // Order by transfer date descending
                pageLength: 10,
                responsive: true
            });
        });
    </script>
@endsection
