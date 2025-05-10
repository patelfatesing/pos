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
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Commission Customer List</h4>
                            </div>
                            <a href="{{ route('commission-users.create') }}" class="btn btn-primary add-list">
                                <i class="las la-plus mr-3"></i>Create New Commission Customer
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-12">

                        <div class="table-responsive rounded mb-3">
                            <table class="table data-tables table-striped" id="commission_users_table">
                                <thead class="bg-white text-uppercase">
                                    <tr class="ligth ligth-data">
                                        <th>First Name</th>
                                        <th>Middle Name</th>
                                        <th>Last Name</th>
                                        <th>Commission Type</th>
                                        <th>Commission Value</th>
                                        <th>Applies To</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Created Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
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

                $('#commission_users_table').DataTable().clear().destroy();

                $('#commission_users_table').DataTable({
                    pagelength: 10,
                    responsive: true,
                    processing: true,
                    ordering: true,
                    bLengthChange: true,
                    serverSide: true,

                    "ajax": {
                        "url": '{{ url('commission-users/get-data') }}',
                        "type": "post",
                        "data": function(d) {},
                    },
                    aoColumns: [{
                            data: 'first_name'
                        },
                        {
                            data: 'middle_name'
                        },
                        {
                            data: 'last_name'
                        },
                        {
                            data: 'commission_type'
                        },
                        {
                            data: 'commission_value'
                        },
                        {
                            data: 'applies_to'
                        },
                        {
                            data: 'start_date'
                        },
                        {
                            data: 'end_date'
                        },
                        {
                            data: 'created_at'
                        },
                        {
                            data: 'action'
                        }
                    ],
                    aoColumnDefs: [{
                        bSortable: false,
                        aTargets: [5, 6] // make "action" column unsortable
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

            function delete_commission_user(id) {

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
                            url: "{{ route('commission-users.destroy', ':id') }}".replace(':id', id),
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                id: id
                            },
                            success: function(response) {
                                $('#commission_users_table').DataTable().ajax.reload();

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
