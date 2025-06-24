@extends('layouts.backend.datatable_layouts')

@section('styles')
    <style>
        .table td,
        .table th {
            white-space: nowrap;
            vertical-align: middle;
        }

        .daterangepicker {
            z-index: 3000 !important;
        }

        tfoot th.text-right {
            text-align: right;
        }
    </style>
@endsection

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                    <div class="col-md-2 mb-2">
                        <input type="text" id="reportrange" class="form-control"
                            style="background: white; cursor: pointer;" />
                    </div>
                    <div class="col-md-1 mb-2">
                        <button id="reset-filters" class="btn btn-secondary">Reset</button>
                    </div>
                </div>

                <!-- Table -->
                <div class="col-lg-12">
                    <div class="table-responsive rounded mb-3">
                        <table class="table table-striped nowrap" id="stock-table" style="width:100%;">
                            <thead class="bg-white">
                                <tr class="ligth ligth-data">
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
@endsection

@section('scripts')
    <!-- Moment.js & DateRangePicker -->


    <script>
        $(document).ready(function() {
            const start = moment().subtract(29, 'days');
            const end = moment();

            $('#reportrange').daterangepicker({
                startDate: start,
                endDate: end,
                locale: {
                    format: 'YYYY-MM-DD'
                },
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')]
                }
            }, refreshData);

            let table;

            function moneyRenderer() {
                return function(data, type) {
                    const num = parseFloat(data || 0);
                    return (type === 'sort' || type === 'type') ? num : '₹' + num.toFixed(2);
                };
            }

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
                        alert('Failed to load data.');
                    }
                });
            }

            function initializeDataTable(data) {
                table = $('#stock-table').DataTable({
                    data: data,
                    columns: [{
                            data: null,
                            render: (d, t, r, m) => m.row + 1
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
                            render: moneyRenderer(),
                            className: 'text-end'
                        },
                        {
                            data: 'selling_price',
                            render: moneyRenderer(),
                            className: 'text-end'
                        },
                        {
                            data: 'cost_price',
                            render: moneyRenderer(),
                            className: 'text-end'
                        },
                        {
                            data: 'all_qty',
                            render: d => parseInt(d || 0),
                            className: 'text-end'
                        },
                        {
                            data: 'all_price',
                            render: moneyRenderer(),
                            className: 'text-end'
                        }
                    ],
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "All"]
                    ],
                    pageLength: 25,
                    dom: 'Blfrtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            className: 'btn btn-sm btn-outline-success',
                            title: 'Stock Report',
                            filename: 'stock_report_excel',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            className: 'btn btn-sm btn-outline-danger',
                            title: 'Stock Report',
                            filename: 'stock_report_pdf',
                            orientation: 'landscape',
                            pageSize: 'A4',
                            exportOptions: {
                                columns: ':visible'
                            }
                        }
                    ],
                    footerCallback: function(row, data) {
                        let totalQty = 0,
                            totalPrice = 0,
                            sellingTotal = 0,
                            purchaseTotal = 0;
                        data.forEach(row => {
                            let qty = parseFloat(row.all_qty || 0);
                            let selling = parseFloat(row.selling_price || 0);
                            let cost = parseFloat(row.cost_price || 0);
                            let allPrice = parseFloat(row.all_price || 0);
                            totalQty += qty;
                            totalPrice += allPrice;
                            sellingTotal += selling * qty;
                            purchaseTotal += cost * qty;
                        });
                        $('#total-qty').html(totalQty);
                        $('#total-price').html('₹' + totalPrice.toFixed(2));
                        $('#selling-total').html('₹' + sellingTotal.toFixed(2));
                        $('#purchase-total').html('₹' + purchaseTotal.toFixed(2));
                    }
                });

                $('#store_id, #product_id, #category_id, #subcategory_id').change(refreshData);
                $('#reset-filters').click(function() {
                    $('#store_id, #product_id, #category_id, #subcategory_id').val('');
                    refreshData();
                });
            }

            function refreshData() {
                loadData({
                    store_id: $('#store_id').val(),
                    product_id: $('#product_id').val(),
                    category_id: $('#category_id').val(),
                    subcategory_id: $('#subcategory_id').val(),
                    date_range: $('#reportrange').val()
                });
            }

            loadData(); // initial call
        });
    </script>
@endsection
