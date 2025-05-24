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

                <div class="col-lg-12">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                        <div>
                            <h4 class="mb-3">Store List</h4>
                        </div>
                        <a href="{{ route('branch.create') }}" class="btn btn-primary add-list">
                            <i class="las la-plus mr-3"></i>Create New Store
                        </a>
                    </div>
                </div>

                <div class="table-responsive rounded mb-3">
                    <table class="table data-tables table-striped" id="branch_table">
                        <thead class="bg-white text-uppercase">
                            <tr class="ligth ligth-data">

                                <th>
                                    Name
                                </th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Store Status</th>
                                <th>Main Branch</th>
                                <th data-type="date" data-format="YYYY/DD/MM">Created Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
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

                $('#branch_table').DataTable().clear().destroy();

                $('#branch_table').DataTable({
                    pagelength: 10,
                    responsive: true,
                    processing: true,
                    ordering: true,
                    bLengthChange: true,
                    serverSide: true,

                    "ajax": {
                        "url": '{{ url('store/get-data') }}',
                        "type": "post",
                        "data": function(d) {},
                    },
                    aoColumns: [

                        {
                            data: 'name'
                        },
                        {
                            data: 'address'
                        },
                        {
                            data: 'is_active'
                        },
                        {
                            data: 'is_deleted'
                        },
                        {
                            data: 'main_branch'
                        },
                        {
                            data: 'created_at'
                        },
                        {
                            data: 'action'
                        }
                        // Define more columns as per your table structure

                    ],
                    aoColumnDefs: [{
                        bSortable: false,
                        aTargets: [1, 2, 3, 5]
                    }],
                    order: [
                        [4, 'desc']
                    ], // 🟢 Sort by created_at DESC by default
                    dom: "Bfrtip",
                    lengthMenu: [
                        [10, 25, 50],
                        ['10 rows', '25 rows', '50 rows', 'All']
                    ],
                    buttons: ['pageLength']

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
                            type: "POST", // "method" also works
                            url: "{{ url('store/delete') }}", // Ensure correct Laravel URL
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                id: id
                            },
                            success: function(response) {
                                location.reload();
                                // swal("Deleted!", "The store has been deleted.", "success")
                                //     .then(() => location.reload());
                            },
                            error: function(xhr) {
                                swal("Error!", "Something went wrong.", "error");
                            }
                        });
                    }
                });

            }
             function branchStatusChange(id, newStatus) {
                Swal.fire({
                    title: "Are you sure?",
                    text: "Do you want to change the status?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, change it!",
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST",
                            url: "{{ url('store/status-change') }}", // Update this to your route
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                id: id,
                                status: newStatus
                            },
                            success: function(response) {
                                Swal.fire("Success!", "Store status has been changed.", "success").then(() => {
                                    $('#branch_table').DataTable().ajax.reload(null,
                                        false); // ✅ Only reload DataTable
                                });
                            },
                            error: function(xhr) {
                                Swal.fire("Error!", "Something went wrong.", "error");
                            }
                        });
                    }
                });
        }
        </script>
    @endsection
