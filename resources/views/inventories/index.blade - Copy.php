@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <h1>Stock Inventory</h1>
                <!-- Inventory Table -->
                <div class="col-lg-12">
                    <!-- Store Filter -->
                    <div class="col-md-3" style="float: right; margin-bottom: 10px;">
                        <div class="form-group">
                            <select name="storeSearch" id="storeSearch" class="form-control">
                                <option value="">All</option>
                                @foreach ($branch as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3" style="float: right; margin-bottom: 10px;">
                        <div class="form-group">
                            <select name="subCategorySearch" id="subCategorySearch" class="form-control">
                                <option value="">All</option>
                                @foreach ($subcategories as $id => $name)
                                    <option value="{{ $name->id }}">{{ $name->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive rounded mb-3">
                        <table class="table data-tables table-striped" id="inventory_table">
                            <thead class="bg-white">
                                <tr class="ligth ligth-data">
                                    <th>Product</th>
                                    <th>Store</th>
                                    <th>Quantity</th>
                                    <th>Cost Price</th>
                                    <th>Batch No</th>
                                    <th>Expiry Date</th>
                                    <th>Stock Low Level</th>
                                    <th>Last updated</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="2" style="text-align:right">Total:</th>
                                    <th id="footer_qty"></th>
                                    <th id="footer_cost"></th>
                                    <th colspan="4"></th>
                                </tr>
                            </tfoot>
                            <tbody class="ligth-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Level Modal -->
    <div class="modal fade bd-example-modal-lg" id="lowLevelModal" tabindex="-1" role="dialog"
        aria-labelledby="lowLevelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="lowLevelStockUpdateForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="lowLevelModalLabel">Stock Low Level Set</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="product_id" id="product_id">
                            <input type="hidden" name="store_id" id="store_id">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Low Level Quantity</label>
                                    <input type="number" name="low_level_qty" class="form-control" id="low_level_qty"
                                        placeholder="Enter Low Level Quantity">
                                    <span class="text-danger" id="low_level_qty_error"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Check if DataTable is already initialized and destroy it to prevent multiple initializations
            if ($.fn.dataTable.isDataTable('#inventory_table')) {
                $('#inventory_table').DataTable().destroy();
            }

            // Initialize DataTable
            var table = $('#inventory_table').DataTable({
                pageLength: 10,
                responsive: true,
                processing: true,
                ordering: true,
                bLengthChange: true,
                serverSide: true,
                "ajax": {
                    "url": '{{ url('inventories/get-data') }}',
                    "type": "POST",
                    data: function(d) {
                        d.store_id = $('#storeSearch').val();
                        d.sub_category_id = $('#subCategorySearch').val(); // Add subcategory filter
                    }
                },
                aoColumns: [{
                        data: 'name',
                        orderable: false
                    },
                    {
                        data: 'location',
                        orderable: false
                    },
                    {
                        data: 'quantity',
                        orderable: false
                    },
                    {
                        data: 'cost_price',
                        orderable: false
                    },
                    {
                        data: 'batch_no',
                        orderable: false
                    },
                    {
                        data: 'expiry_date',
                        orderable: true
                    },
                    {
                        data: 'reorder_level',
                        orderable: false
                    },
                    {
                        data: 'updated_at',
                        orderable: true
                    }
                ],
                columnDefs: [{
                        width: "20%",
                        targets: 0
                    },
                    {
                        width: "7%",
                        targets: 1
                    },
                    {
                        width: "5%",
                        targets: 2
                    },
                    {
                        width: "5%",
                        targets: 3
                    },
                    {
                        width: "5%",
                        targets: 4
                    },
                    {
                        width: "7%",
                        targets: 5
                    },
                    {
                        width: "7%",
                        targets: 6
                    },
                    {
                        width: "10%",
                        targets: 7
                    }
                ],
                autoWidth: false,
                order: [
                    [5, 'desc']
                ], // Order by expiry_date
                dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>", // Only define dom once
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                buttons: [{
                        extend: 'excelHtml5',
                        className: 'btn btn-outline-success btn-sm me-2',
                        title: 'Commission Customer',
                        filename: 'commission_customer_excel',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-outline-danger btn-sm',
                        title: 'Commission Customer',
                        filename: 'commission_customer_pdf',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ]
            });

            // Change store filter
            $('#storeSearch').on('change', function() {
                table.ajax.reload(null, false); // Reload DataTable with the new filter value
            });

            // Change subcategory filter
            $('#subCategorySearch').on('change', function() {
                table.ajax.reload(null, false); // Reload DataTable with the new filter value
            });
        });


        // Submit low level form
        $('#lowLevelStockUpdateForm').on('submit', function(e) {
            e.preventDefault();
            $('#low_level_qty_error').text('');

            let formData = {
                _token: $('input[name="_token"]').val(),
                product_id: $('#product_id').val(),
                store_id: $('#store_id').val(),
                low_level_qty: $('#low_level_qty').val(),
            };

            $.ajax({
                type: "POST",
                url: "{{ route('inventories.update-low-level-qty') }}",
                data: formData,
                success: function(response) {
                    alert(response.message);
                    $('#lowLevelModal').modal('hide');
                    $('#lowLevelStockUpdateForm')[0].reset();
                    inventoryTable.ajax.reload(null, false);
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        if (errors.low_level_qty) {
                            $('#low_level_qty_error').text(errors.low_level_qty[0]);
                        }
                    } else {
                        alert("An unexpected error occurred.");
                    }
                }
            });
        });

        function low_level_stock_set(p_id, branch_id, reorder_level) {
            $('#product_id').val(p_id);
            $('#store_id').val(branch_id);
            $('#low_level_qty').val(reorder_level);
            $('#lowLevelModal').modal('show');
        }
    </script>
@endsection
