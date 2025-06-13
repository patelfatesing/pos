@extends('layouts.backend.layouts')

@section('page-content')
    {{-- CSRF & Scripts --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 d-flex flex-wrap align-items-center justify-content-between mb-4">
                        <div>
                            <h4 class="mb-3">Party Customer List</h4>
                        </div>
                        <a href="{{ route('party-users.create') }}" class="btn btn-primary add-list">
                            <i class="las la-plus mr-3"></i>Add New Party Customer
                        </a>
                    </div>

                    <div class="col-lg-12">
                        <div class="table-responsive rounded mb-3">
                            <table class="table table-striped table-bordered nowrap" id="party_users_table" style="width:100%;">
                                <thead class="bg-white text-uppercase">
                                    <tr class="light light-data">
                                        <th>Customer Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Credit</th>
                                        <th>Status</th>
                                        <th>Created Date</th>
                                        <th>Updated Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade bd-example-modal-lg" id="custPriceChangeModal" tabindex="-1" role="dialog"
        aria-labelledby="custPriceChangeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="custModalContent"></div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Setup CSRF token
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Destroy any existing instance
            $('#party_users_table').DataTable().clear().destroy();

            // Initialize DataTable
            $('#party_users_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ url('party-users/get-data') }}',
                    type: 'POST',
                },
                columns: [
                    { data: 'first_name' },
                    { data: 'email' },
                    { data: 'phone' },
                    { data: 'credit_points' },
                    { data: 'status' },
                    { data: 'created_at' },
                    { data: 'updated_at' },
                    { data: 'action', orderable: false, searchable: false }
                ],
                order: [[6, 'desc']], // Sort by updated_at descending
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                dom: '<"row mb-3"<"col-sm-6"l><"col-sm-6"f>>rt<"row mt-3"<"col-sm-6"i><"col-sm-6"p>>'
            });
        });

        // Delete party user
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

        // Open price change modal
        function party_cust_price_change(id) {
            $.ajax({
                url: '/cust-product-price-change/form/' + id,
                type: 'GET',
                success: function(response) {
                    $('#custModalContent').html(response);
                    $('#custPriceChangeModal').modal('show');
                },
                error: function() {
                    Swal.fire("Error", "Failed to load form.", "error");
                }
            });
        }

        // Status change
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
                        url: "{{ url('party-users/status-change') }}",
                        data: {
                            id: id,
                            status: newStatus
                        },
                        success: function(response) {
                            Swal.fire("Success!", "Status has been changed.", "success").then(() => {
                                $('#party_users_table').DataTable().ajax.reload(null, false);
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
