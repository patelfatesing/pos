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
                <h1>Stock Inventory</h1>
                <div class="col-lg-12">
                    <div class="table-responsive rounded mb-3">
                        <table class="table data-tables table-striped" id="inventory_table">
                            <div class="col-md-3" style="float: right; margin-bottom: 10px;">
                                <div class="form-group">
                                    <select name="storeSearch" id="storeSearch" class="selectpicker form-control"
                                        data-style="py-0">
                                        <option value="">All</option>
                                        @foreach ($branch as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <thead class="bg-white">

                                <tr class="ligth ligth-data">
                                    <th>Product</th>
                                    <th>Store</th>
                                    <th>Quantity</th>
                                    <th>Cost Price</th>
                                    <th>Batch No</th>
                                    <th>Expiry Date</th>
                                    <th>Stock Low Level</th>
                                    <th data-type="date" data-format="YYYY/DD/MM">Last updated</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="ligth-body">
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Page end  -->
            </div>
        </div>
    </div>
    <!-- Wrapper End-->

    <div class="modal fade bd-example-modal-lg" id="lowLevelModal" tabindex="-1" role="dialog"
        aria-labelledby="lowLevelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="lowLevelStockUpdateForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="lowLevelModalLabel">Stocl Low Level Set</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="product_id" id="product_id" value="">
                            <input type="hidden" name="store_id" id="store_id" value="">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Low Level Quntity </label>
                                    <input type="number" name="low_level_qty" class="form-control" id="low_level_qty"
                                        placeholder="Enter Low Level Quantity">
                                    <span class="text-danger" id="low_level_qty_error"></span>
                                </div>
                            </div>
                        </div>
                        {{-- <span class="mt-2 badge badge-pill border border-secondary text-secondary">
                            {{ __('messages.reorder_level_qty') }}
                        </span> --}}
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#inventory_table').DataTable().clear().destroy();

            var table = $('#inventory_table').DataTable({
                pagelength: 10,
                responsive: true,
                processing: true,
                ordering: true,
                bLengthChange: true,
                serverSide: true,

                "ajax": {
                    "url": '{{ url('inventories/get-data') }}',
                    "type": "post",
                    data: function(d) {
                        d.store_id = $('#storeSearch').val(); // pass department value
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
                    },
                    {
                        data: 'action',
                        orderable: false
                    }
                ],
                columnDefs: [{
                        width: "20%",
                        targets: 0
                    }, // set width of column 0
                    {
                        width: "7%",
                        targets: 1
                    }, // set width of column 1
                    {
                        width: "5%",
                        targets: 2
                    }, {
                        width: "5%",
                        targets: 3
                    }, {
                        width: "5%",
                        targets: 4
                    }, {
                        width: "7%",
                        targets: 5
                    }, {
                        width: "7%",
                        targets: 6
                    }, {
                        width: "10%",
                        targets: 7
                    },
                    {
                        width: "5%",
                        targets: 8
                    }
                ],
                autoWidth: false,
                order: [
                    [7, 'desc']
                ], // 🟢 Sort by created_at DESC by default
                dom: "Bfrtip",
                lengthMenu: [
                    [10, 25, 50],
                    ['10 rows', '25 rows', '50 rows', 'All']
                ],
                buttons: ['pageLength']
            });

            $('#storeSearch').on('change', function() {
                table.draw();
            });

        });

        function low_level_stock_set(p_id, branch_id, reorder_level) {
            $('#product_id').val(p_id);
            $('#store_id').val(branch_id);
            $('#low_level_qty').val(reorder_level);
            $('#lowLevelModal').modal('show');
        }

        $(document).ready(function() {
            $('#lowLevelStockUpdateForm').on('submit', function(e) {
                e.preventDefault();

                // Clear previous errors
                $('#low_level_qty_error').text('');

                let formData = {
                    _token: $('input[name="_token"]').val(),
                    product_id: $('#product_id').val(),
                    store_id: $('#store_id').val(),
                    low_level_qty: $('#low_level_qty').val(),

                };

                $.ajax({
                    type: "POST",
                    url: "{{ route('inventories.update-low-level-qty') }}", // adjust this route
                    data: formData,
                    success: function(response) {
                        alert(response.message); // or show a toast
                        $('#lowLevelModal').modal('hide');
                        $('#lowLevelStockUpdateForm')[0].reset();
                        $('#inventory_table').DataTable().ajax.reload(null, false);
                        // Optionally reload part of the page
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
    </script>
@endsection
