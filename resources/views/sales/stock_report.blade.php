@extends('layouts.backend.layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">


                <!-- Date Filters -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Stock Status List</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                    </div>
                    <div class="col-md-3 mb-2">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select id="branch_id" class="form-control">
                            <option value="">All Branches</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-primary w-100" id="filter">Search</button>
                    </div>
                </div>

                <!-- Table -->
                <div class="col-lg-12">
                    <div class="table-responsive rounded mb-3">
                        <table class="table table-striped" id="stock-table" style="width:100%">
                            <thead class="bg-white text-uppercase">
                                <tr>
                                    <th>Branch</th>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Quantity</th>
                                    <th>Low Level Stock</th>
                                    <th>Sell Price</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- Wrapper End -->

    <script>
        $(document).ready(function() {
            var table = $('#stock-table').DataTable({
                processing: true,
                serverSide: false, // if your data is small; otherwise true
                ajax: {
                    url: '{{ route('sales.fetch-stock-data') }}',
                    data: function(d) {
                        d.branch_id = $('#branch_id').val(); // send selected branch_id
                    }
                },
                columns: [{
                        data: 'branch_name',
                        name: 'branch_name'
                    },
                    {
                        data: 'product_name',
                        name: 'product_name'
                    },
                    {
                        data: 'sku',
                        name: 'sku'
                    },
                    {
                        data: 'quantity',
                        name: 'quantity'
                    },
                    {
                        data: 'reorder_level',
                        name: 'reorder_level'
                    },
                    {
                        data: 'sell_price',
                        name: 'sell_price'
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            if (row.quantity <= row.reorder_level) {
                                return '<span class="badge bg-danger">Low Stock</span>';
                            } else {
                                return '<span class="badge bg-success">OK</span>';
                            }
                        },
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            // When filter button is clicked
            $('#filter').click(function() {
                table.ajax.reload();
            });
        });
    </script>
@endsection
