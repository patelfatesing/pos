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
                            <h4 class="mb-3">Role List</h4>
                        </div>
                        <a href="{{ route('roles.create') }}" class="btn btn-primary add-list">
                            <i class="las la-plus mr-3"></i>Create New Role
                        </a>
                    </div>
                </div>

                <div class="table-responsive rounded mb-3">
                    <table class="table data-tables table-striped" id="roles_table">
                        <thead class="bg-white text-uppercase">
                            <tr class="ligth ligth-data">
                                <th>
                                    <b>N</b>ame
                                </th>
                                <th>Status</th>
                                <th data-type="date" data-format="YYYY/DD/MM">Created Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
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

            $('#roles_table').DataTable().clear().destroy();

            $('#roles_table').DataTable({
                pagelength: 10,
                responsive: true,
                processing: true,
                ordering: true,
                bLengthChange: true,
                serverSide: true,

                "ajax": {
                    "url": '{{ url('roles/get-data') }}',
                    "type": "post",
                    "data": function(d) {},
                },
                aoColumns: [{
                        data: 'name'
                    },
                    {
                        data: 'is_active'
                    },
                    {
                        data: 'created_at'
                    }
                    // Define more columns as per your table structure
                ],
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [1] // make "action" column unsortable
                }],
                order: [
                    [2, 'desc']
                ], // 🟢 Sort by created_at DESC by default
                dom: "Bfrtip",
                lengthMenu: [
                    [10, 25, 50],
                    ['10 rows', '25 rows', '50 rows', 'All']
                ],
                buttons: ['pageLength']
            });
        });

        function delete_user(id) {

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
