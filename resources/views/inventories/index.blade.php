@extends('layouts.backend.layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- JS and CSS dependencies -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

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
        let inventoryTable;

        function initDataTable() {
            if ($.fn.DataTable.isDataTable('#inventory_table')) {
                $('#inventory_table').DataTable().clear().destroy();
            }

            inventoryTable = $('#inventory_table').DataTable({
                pagelength: 10,
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ url('inventories/get-data') }}',
                    type: 'POST',
                    data: function(d) {
                        d.store_id = $('#storeSearch').val();
                    }
                },
                columns: [{
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
                order: [
                    [7, 'desc']
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
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();

                    // Total quantity for current page
                    var totalQty = api
                        .column(2, {
                            page: 'current'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return parseFloat(a) + parseFloat(b);
                        }, 0);

                    // Total cost = sum(quantity * cost_price) for current page
                    var qtyData = api.column(2, {
                        page: 'current'
                    }).data();
                    var costData = api.column(3, {
                        page: 'current'
                    }).data();

                    var totalCost = 0;
                    for (let i = 0; i < qtyData.length; i++) {
                        totalCost += parseFloat(qtyData[i]) * parseFloat(costData[i]);
                    }

                    // Update footer
                    $('#footer_qty').html(totalQty);
                    $('#footer_cost').html(totalCost.toFixed(2));
                }

            });
        }

        $(document).ready(function() {
            // CSRF setup
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Init DataTable
            initDataTable();

            // Change store filter
            $('#storeSearch').on('change', function() {
                inventoryTable.draw();
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
        });

        function low_level_stock_set(p_id, branch_id, reorder_level) {
            $('#product_id').val(p_id);
            $('#store_id').val(branch_id);
            $('#low_level_qty').val(reorder_level);
            $('#lowLevelModal').modal('show');
        }
    </script>
@endsection
