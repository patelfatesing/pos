@extends('layouts.backend.layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>


    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Party Customer List</h4>
                            </div>
                            <a href="{{ route('party-users.create') }}" class="btn btn-primary add-list">
                                <i class="las la-plus mr-3"></i>Add New Party Customer
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="table-responsive rounded mb-3">
                            <table class="table data-tables table-striped" id="party_users_table">
                                <thead class="bg-white text-uppercase">
                                    <tr class="ligth ligth-data">
                                        <th>Customer Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Credit</th>
                                        <th>Status</th>
                                        <th data-type="date" data-format="YYYY/DD/MM">Created Date</th>
                                        <th data-type="date" data-format="YYYY/DD/MM">Updated Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Wrapper End -->

    <div class="modal fade bd-example-modal-lg" id="custPriceChangeModal" tabindex="-1" role="dialog"
        aria-labelledby="custPriceChangeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="custModalContent">
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

            $('#party_users_table').DataTable().clear().destroy();

            $('#party_users_table').DataTable({
                pageLength: 10,
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ url('party-users/get-data') }}',
                    type: 'POST',
                },
                columns: [{
                        data: 'first_name'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'phone'
                    },
                    {
                        data: 'credit_points'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'updated_at'
                    },
                    //  {
                    //     data: 'is_delete'
                    // },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [4, 5] // make "action" column unsortable
                }],
                order: [
                    [6, 'desc']
                ], // 🟢 Sort by created_at DESC by default
                dom: "Bfrtip",
                buttons: ['pageLength'],
                lengthMenu: [
                    [10, 25, 50],
                    ['10 rows', '25 rows', '50 rows']
                ]
            });


        });

        function delete_party_user(id) {
            Swal.fire({
                title: "Are you sure?",
                text: "This action cannot be undone!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE",
                        url: "{{ route('party-users.destroy', ':id') }}".replace(':id', id),
                        success: function(response) {
                            $('#party_users_table').DataTable().ajax.reload();
                            Swal.fire("Deleted!", "The party user has been deleted.", "success");
                        },
                        error: function(xhr) {
                            Swal.fire("Error!", "An error occurred while deleting.", "error");
                        }
                    });
                }
            });
        }

        function party_cust_price_change(id) {

            $.ajax({
                url: '/cust-product-price-change/form/' + id,
                type: 'GET',
                success: function(response) {

                    $('#custModalContent').html(response);
                    $('#custPriceChangeModal').modal('show');
                },
                error: function() {
                    alert('Failed to load form.');
                }
            });
        }

        function statusChange(id, newStatus) {
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
                        url: "{{ url('party-users/status-change') }}", // Update this to your route
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id: id,
                            status: newStatus
                        },
                        success: function(response) {
                            Swal.fire("Success!", "Status has been changed.", "success").then(() => {
                                $('#party_users_table').DataTable().ajax.reload(null,
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
