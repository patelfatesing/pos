@extends('layouts.backend.layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Styles -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <!-- Page Header -->
                <div class="row">
                    <div class="col-lg-12 mb-3">
                        <h4>Stock Summary</h4>
                    </div>

                    <!-- Filters -->
                    <div class="col-md-2 mb-2">
                        <select id="store_id" class="form-control">
                            <option value="">All Branches</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select id="product_id" class="form-control">
                            <option value="">All Products</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select id="category_id" class="form-control">
                            <option value="">All Categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <select id="subcategory_id" class="form-control">
                            <option value="">All Subcategories</option>
                            @foreach ($subcategories as $subcategory)
                                <option value="{{ $subcategory->id }}">{{ $subcategory->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 mb-2">
                        <button id="reset-filters" class="btn btn-secondary">Reset</button>
                    </div>
                </div>

                <!-- Table -->
                <div class="col-lg-12">
                    <div class="table-responsive rounded mb-3">
                        <table class="table table-striped" id="stock-table" style="width:100%">
                            <thead class="bg-white">
                                <tr>
                                    <th>Branch</th>
                                    <th>Product</th>
                                    <th>Barcode</th>
                                    <th>Category</th>
                                    <th>MRP</th>
                                    <th>Cost Price</th>
                                    <th>Selling Price</th>
                                    <th>Qty</th>
                                    <th>All Price</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="6" class="text-right">Total Summary:</th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var table;

            // Fetch initial data
            $.ajax({
                url: '{{ route('sales.fetch-stock-data') }}',
                type: 'GET',
                success: function(response) {
                    initializeDataTable(response.data);
                },
                error: function(xhr) {
                    console.error('Failed to load:', xhr.responseText);
                    alert('Failed to load data.');
                }
            });

            function initializeDataTable(data) {
                table = $('#stock-table').DataTable({
                    data: data,
                    columns: [{
                            data: 'branch_name'
                        },
                        {
                            data: 'product_name'
                        },
                        {
                            data: 'barcode'
                        },
                        {
                            data: 'category_name'
                        },
                        {
                            data: 'mrp',
                            render: data => '₹' + parseFloat(data || 0).toFixed(2)
                        },
                        {
                            data: 'cost_price',
                            render: data => '₹' + parseFloat(data || 0).toFixed(2)
                        },
                        {
                            data: 'selling_price',
                            render: data => '₹' + parseFloat(data || 0).toFixed(2)
                        },
                        {
                            data: 'all_qty',
                            render: data => parseInt(data || 0)
                        },
                        {
                            data: 'all_price',
                            render: data => '₹' + parseFloat(data || 0).toFixed(2)
                        }
                    ],
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "All"]
                    ],
                    pageLength: 25,
                    dom: 'Blfrtip', // ✅ add 'l' here
                    buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                    aoColumnDefs: [{
                        bSortable: false,
                        aTargets: [7, 8]
                    }],
                    footerCallback: function(row, data) {
                        var api = this.api();
                        var numericColumns = [7, 8];

                        numericColumns.forEach(function(colIndex) {
                            var total = api.column(colIndex, {
                                page: 'current'
                            }).data().reduce(function(acc, curr) {
                                return acc + parseFloat(curr || 0);
                            }, 0);

                            var formatted = (colIndex === 7) ? Math.round(total) : '₹' + total
                                .toFixed(2);

                            $(api.column(colIndex).footer()).html(formatted);
                        });
                    }
                });

                // Filter Events
                $('#store_id, #product_id, #category_id, #subcategory_id').change(function() {
                    refreshData();
                });

                $('#reset-filters').click(function() {
                    $('#store_id, #product_id, #category_id, #subcategory_id').val('');
                    refreshData();
                });
            }

            function refreshData() {
                $.ajax({
                    url: '{{ route('sales.fetch-stock-data') }}',
                    type: 'GET',
                    data: {
                        store_id: $('#store_id').val(),
                        product_id: $('#product_id').val(),
                        category_id: $('#category_id').val(),
                        subcategory_id: $('#subcategory_id').val()
                    },
                    success: function(response) {
                        table.clear().rows.add(response.data).draw();
                    },
                    error: function(xhr) {
                        console.error('Refresh failed:', xhr.responseText);
                        alert('Error refreshing data.');
                    }
                });
            }
        });
    </script>
@endsection

@section('styles')
    <style>
        .table td,
        .table th {
            white-space: nowrap;
            vertical-align: middle;
        }

        .dataTables_wrapper .dataTables_length select {
            min-width: 65px;
        }

        .daterangepicker {
            z-index: 3000;
        }

        .buttons-html5,
        .buttons-print {
            margin: 0 5px;
        }

        tfoot th.text-right {
            text-align: right;
        }
    </style>
@endsection
