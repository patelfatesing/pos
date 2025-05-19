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
                <h1>Inventory</h1>
                <div class="col-lg-12">
                    <div class="table-responsive rounded mb-3">
                        <table class="table data-tables table-striped" id="inventory_table">
                            <div class="col-md-3" style="float: right; margin-bottom: 10px;">
                                <div class="form-group">
                                    <select name="storeSearch" id="storeSearch" class="selectpicker form-control"
                                        data-style="py-0">
                                        <option value="">Select Store</option>
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
                                    <th>Price</th>
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

        function delete_store(id) {

            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "delete", // "method" also works
                        url: "{{ url('store/delete') }}/" + id, // Ensure correct Laravel URL
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id: id
                        },
                        success: function(response) {
                            swal("Deleted!", "The store has been deleted.", "success")
                                .then(() => location.reload());
                        },
                        error: function(xhr) {
                            swal("Error!", "Something went wrong.", "error");
                        }
                    });
                }
            });

        }
    </script>
@endsection
