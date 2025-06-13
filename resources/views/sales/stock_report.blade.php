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
                                    <th>Sr. No.</th>
                                    <th>Branch</th>
                                    <th>Product</th>
                                    <th>Barcode</th>
                                    <th>Category</th>
                                    <th>MRP</th>
                                    <th>Selling Price</th>
                                    <th>Cost Price</th>
                                    <th>Qty</th>
                                    <th>Total Stock Value</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="8" class="text-right">Total Quantity:</th>
                                    <th id="total-qty"></th>
                                    <th id="total-price"></th>
                                </tr>
                                <tr>
                                    <th colspan="5" class="text-right">Selling Total:</th>
                                    <th colspan="5" id="selling-total" class="text-left"></th>
                                </tr>
                                <tr>
                                    <th colspan="5" class="text-right">Purchase Total:</th>
                                    <th colspan="5" id="purchase-total" class="text-left"></th>
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

            function loadData(filters = {}) {
                $.ajax({
                    url: '{{ route('sales.fetch-stock-data') }}',
                    type: 'GET',
                    data: filters,
                    success: function(response) {
                        if (table) {
                            table.clear().rows.add(response.data).draw();
                        } else {
                            initializeDataTable(response.data);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                        alert('Failed to load data.');
                    }
                });
            }

            function initializeDataTable(data) {
                table = $('#stock-table').DataTable({
                    data: data,
                    columns: [{
                            data: null,
                            render: (data, type, row, meta) => meta.row + 1,
                            className: 'text-center'
                        },
                        {
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
                            data: 'selling_price',
                            render: data => '₹' + parseFloat(data || 0).toFixed(2)
                        },
                        {
                            data: 'cost_price',
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
                    dom: 'Blfrtip',
                    buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                    aoColumnDefs: [{
                        bSortable: false,
                        aTargets: [1, 4] // make "action" column unsortable
                    }],
                    footerCallback: function(row, data) {
                        let totalQty = 0;
                        let totalPrice = 0;
                        let sellingTotal = 0;
                        let purchaseTotal = 0;

                        data.forEach(row => {
                            let qty = parseFloat(row.all_qty || 0);
                            let sellingPrice = parseFloat(row.selling_price || 0);
                            let costPrice = parseFloat(row.cost_price || 0);
                            let allPrice = parseFloat(row.all_price || 0);

                            totalQty += qty;
                            totalPrice += allPrice;
                            sellingTotal += sellingPrice * qty;
                            purchaseTotal += costPrice * qty;
                        });

                        $('#total-qty').html(totalQty);
                        $('#total-price').html('₹' + totalPrice.toFixed(2));
                        $('#selling-total').html('₹' + sellingTotal.toFixed(2));
                        $('#purchase-total').html('₹' + purchaseTotal.toFixed(2));
                    }
                });

                $('#store_id, #product_id, #category_id, #subcategory_id').change(function() {
                    refreshData();
                });

                $('#reset-filters').click(function() {
                    $('#store_id, #product_id, #category_id, #subcategory_id').val('');
                    refreshData();
                });
            }

            function refreshData() {
                const filters = {
                    store_id: $('#store_id').val(),
                    product_id: $('#product_id').val(),
                    category_id: $('#category_id').val(),
                    subcategory_id: $('#subcategory_id').val()
                };
                loadData(filters);
            }

            // Load data initially
            loadData();
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
