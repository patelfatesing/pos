@extends('layouts.backend.layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Styles -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    
    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <!-- Date Filters -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Stock Summary</h4>
                            </div>
                        </div>
                    </div>
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
                                    <th>Selling Price</th>
                                    <th>Discount</th>
                                    <th>Purchase Price</th>
                                    <th>Opening Stock</th>
                                    <th>In Qty</th>
                                    <th data-toggle="tooltip" title="Sum of Transferred Stock and Sold Stock">Out Qty</th>
                                    <th>All Qty</th>
                                    <th>All Price</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" style="text-align:right">Total Summary:</th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
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
    <!-- Wrapper End -->

    <script>
        $(document).ready(function() {
            console.log('Document Ready - Stock Report');
            
            // Set up AJAX CSRF token
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // First, let's test if the route is accessible
            $.ajax({
                url: '{{ route('sales.fetch-stock-data') }}',
                type: 'GET',
                success: function(response) {
                    console.log('Test AJAX call successful:', response);
                    initializeDataTable(response);
                },
                error: function(xhr, status, error) {
                    console.error('Test AJAX call failed:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    alert('Failed to load data. Please check console for details.');
                }
            });

            function initializeDataTable(initialData) {
                var table = $('#stock-table').DataTable({
                    data: initialData.data || [],
                    columns: [
                        { data: 'branch_name' },
                        { data: 'product_name' },
                        { data: 'barcode' },
                        { data: 'category_name' },
                        { 
                            data: 'mrp',
                            render: function(data) {
                                return '₹ ' + parseFloat(data || 0).toFixed(2);
                            }
                        },
                        { 
                            data: 'selling_price',
                            render: function(data) {
                                return '₹ ' + parseFloat(data || 0).toFixed(2);
                            }
                        },
                        { 
                            data: 'discount',
                            render: function(data) {
                                return '₹ ' + parseFloat(data || 0).toFixed(2);
                            }
                        },
                        { 
                            data: 'purchase_price',
                            render: function(data) {
                                return '₹ ' + parseFloat(data || 0).toFixed(2);
                            }
                        },
                        { 
                            data: 'opening_stock',
                            render: function(data) {
                                return parseInt(data || 0);
                            }
                        },
                        { 
                            data: 'in_qty',
                            render: function(data) {
                                return parseInt(data || 0);
                            }
                        },
                        { 
                            data: 'out_qty',
                            render: function(data) {
                                return parseInt(data || 0);
                            }
                        },
                        { 
                            data: 'all_qty',
                            render: function(data) {
                                return parseInt(data || 0);
                            }
                        },
                        { 
                            data: 'all_price',
                            render: function(data) {
                                return '₹ ' + parseFloat(data || 0).toFixed(2);
                            }
                        }
                    ],
                    pageLength: 25,
                    dom: 'Bfrtip',
                    buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                    footerCallback: function(row, data, start, end, display) {
                        var api = this.api();
                        
                        // Calculate totals for numeric columns (MRP to All Price)
                        var numericColumns = [4, 5, 6, 7, 8, 9, 10, 11, 12]; // Column indices
                        
                        numericColumns.forEach(function(colIndex) {
                            var total = api
                                .column(colIndex, { page: 'current' })
                                .data()
                                .reduce(function(acc, curr) {
                                    return acc + parseFloat(curr || 0);
                                }, 0);
                            
                            // Format the total based on column type
                            var formattedTotal;
                            if (colIndex >= 8 && colIndex <= 11) {
                                // Integer for quantity columns
                                formattedTotal = Math.round(total);
                            } else {
                                // Add rupee symbol for monetary values
                                formattedTotal = '₹ ' + total.toFixed(2);
                            }
                            
                            $(api.column(colIndex).footer()).html(formattedTotal);
                        });
                    }
                });

                // Initialize tooltips
                $('[data-toggle="tooltip"]').tooltip();

                // Event handlers for filters
                $('#store_id, #product_id, #category_id, #subcategory_id').change(function() {
                    refreshData();
                });

                // $('#daterange').on('apply.daterangepicker', function(ev, picker) {
                //     $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                //     refreshData();
                // });

                // $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
                //     $(this).val('');
                //     refreshData();
                // });

                $('#reset-filters').click(function() {
                    $('#store_id, #product_id, #category_id, #subcategory_id').val('');
                
                    refreshData();
                });

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
                        error: function(xhr, status, error) {
                            console.error('Refresh data failed:', error);
                            alert('Error refreshing data. Please check console for details.');
                        }
                    });
                }
            }
        });
    </script>
@endsection

@section('styles')
<style>
    .table td, .table th {
        white-space: nowrap;
        vertical-align: middle;
    }
    .dataTables_wrapper .dataTables_scroll {
        margin-bottom: 1em;
    }
    .dataTables_wrapper .dataTables_length select {
        min-width: 65px;
    }
    .daterangepicker {
        z-index: 3000;
    }
    .buttons-html5, .buttons-print {
        margin: 0 5px;
    }
</style>
@endsection
