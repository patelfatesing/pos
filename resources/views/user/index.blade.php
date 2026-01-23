@extends('layouts.backend.datatable_layouts')

@section('styles')
    <style>
        .add-list {
            white-space: nowrap;
        }

        .custom-toolbar-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .custom-toolbar-row .dataTables_length {
            order: 1;
        }

        .custom-toolbar-row .dt-buttons {
            order: 2;
        }

        .custom-toolbar-row .status-filter {
            order: 3;
        }

        .custom-toolbar-row .dataTables_filter {
            order: 4;
            margin-left: auto;
        }

        .dataTables_wrapper .dataTables_filter label,
        .dataTables_wrapper .dataTables_length label {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 0;
        }

        .dt-buttons .btn {
            margin-right: 5px;
        }

        @media (max-width: 768px) {
            .custom-toolbar-row>div {
                flex: 1 1 100%;
                margin-bottom: 10px;
            }
        }
    </style>
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <!-- Page Header -->
                <div class="row align-items-center mb-3">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h2 class="mb-3">Users List</h2>
                            </div>
                            @if (auth()->user()->role_id == 1 || canCreate(auth()->user()->role_id, 'users-create'))
                                <a href="{{ route('users.create') }}" class="btn btn-primary add-list">
                                    <i class="las la-plus mr-3"></i>Create New User
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive rounded">
                            <table class="table table-striped table-bordered nowrap" id="users_table" style="width:100%;">
                                <thead class="bg-white">
                                    <tr class="ligth ligth-data">
                                        <th>Sr No</th> <!-- Added this line -->
                                        {{-- <th>
                                            <b>N</b>ame
                                        </th> --}}
                                        <th>Username</th>
                                        {{-- <th>Email</th>
                                        <th>Phone Number</th> --}}
                                        <th>Role</th>
                                        <th>Branch</th>
                                        <th>Status</th>
                                        <th>Created Date</th>
                                        <th>Updated Date</th>
                                        <th>Action</th>
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

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="changePasswordForm">
                @csrf
                <input type="hidden" name="user_id" id="password_user_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <!-- Eye icon -->
                            <span class="position-absolute" style="top: 65px; right: 25px; cursor: pointer;"
                                onclick="togglePasswordVisibility()">
                                <i id="togglePasswordIcon" class="fa fa-eye"></i>
                            </span>
                        </div>
                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="new_password_confirmation"
                                name="new_password_confirmation" required>
                            <span class="position-absolute" style="top: 165px; right: 25px; cursor: pointer;"
                                onclick="togglePasswordVisibilityNew()">
                                <i id="togglePasswordIconnew" class="fa fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Password</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const table = $('#users_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ url('users/get-data') }}',
                    type: 'POST',
                    data: function(d) {
                        d.status = $('#status').val();
                    }
                },
                columns: [{
                        data: null,
                        name: 'sr_no',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    // {
                    //     data: 'name'
                    // },
                    {
                        data: 'username'
                    },
                    // {
                    //     data: 'email'
                    // },
                    // {
                    //     data: 'phone_number'
                    // },
                    {
                        data: 'role_name'
                    },

                    {
                        data: 'branch_name'
                    },
                    {
                        data: 'is_active'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'updated_at'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [6, 'desc']
                ],
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [2, 3, 4, 6]
                }],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,

                dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",

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
                ],
                initComplete: function() {
                    // Inject status filter in correct order
                    $('<div class="status-filter">' +
                        '<select id="status" class="form-control">' +
                        '<option value="">All Status</option>' +
                        '<option value="yes">Active</option>' +
                        '<option value="no">Inactive</option>' +
                        '</select>' +
                        '</div>').insertAfter('.dt-buttons');

                    $('#status').on('change', function() {
                        table.ajax.reload();
                    });
                }
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
                        type: "POST", // "method" also works
                        url: "{{ url('users/delete') }}", // Ensure correct Laravel URL
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id: id
                        },
                        success: function(response) {
                            Swal.fire("Deleted!", "The user has been deleted.", "success").then(() => {
                                $('#users_table').DataTable().ajax.reload(null,
                                    false); // ✅ Only reload DataTable
                            });
                        },
                        error: function(xhr) {
                            swal("Error!", "Something went wrong.", "error");
                        }
                    });
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
                        url: "{{ url('users/status-change') }}", // Update this to your route
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id: id,
                            status: newStatus
                        },
                        success: function(response) {
                            Swal.fire("Success!", "Status has been changed.", "success").then(() => {
                                $('#users_table').DataTable().ajax.reload(null,
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

        function openChangePasswordModal(userId) {
            $('#password_user_id').val(userId);
            // $('#changePasswordModal').modal('show');
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var myModal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
                myModal.show();
            } else {
                // For Bootstrap 4 (with jQuery)
                $('#changePasswordModal').modal('show');
            }
        }

        $('#changePasswordForm').submit(function(e) {
            e.preventDefault();

            const formData = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                user_id: $('#password_user_id').val(),
                new_password: $('#new_password').val(),
                new_password_confirmation: $('#new_password_confirmation').val(),
            };

            $.ajax({
                url: "{{ url('users/change-password') }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    Swal.fire("Success!", "Password updated successfully.", "success");
                    $('#changePasswordModal').modal('hide');
                    $('#changePasswordForm')[0].reset();
                },
                error: function(xhr) {
                    let errorMsg = "Failed to update password.";

                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMsg = Object.values(errors).flat().join('\n');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }

                    Swal.fire("Error!", errorMsg, "error");
                }

            });
        });

        function togglePasswordVisibility() {
            const input = document.getElementById('new_password');
            const icon = document.getElementById('togglePasswordIcon');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function togglePasswordVisibilityNew() {
            const input = document.getElementById('new_password_confirmation');
            const icon = document.getElementById('togglePasswordIconnew');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
@endsection
