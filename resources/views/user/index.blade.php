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
                                <h2 class="mb-3">Users List</h2>
                            </div>
                            <a href="{{ route('users.create') }}" class="btn btn-primary add-list">
                                <i class="las la-plus mr-3"></i>Create New User
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="table-responsive rounded mb-3">
                            <table class="table data-tables table-striped" id="users_table">

                                <thead class="bg-white">
                                    <tr class="ligth ligth-data">
                                        <th>
                                            <b>N</b>ame
                                        </th>
                                        <th>Email</th>
                                        <th>Phone Number</th>
                                        <th>Role</th>
                                        <th>Branch</th>
                                        <th>Status</th>
                                        <th data-type="date" data-format="YYYY/DD/MM">Created Date</th>
                                         <th data-type="date" data-format="YYYY/DD/MM">Updated Date</th>

                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Page end  -->
            </div>
        </div>
    </div>
    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
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
                            <span 
                                class="position-absolute" 
                                style="top: 65px; right: 25px; cursor: pointer;" 
                                onclick="togglePasswordVisibility()"
                            >
                                <i id="togglePasswordIcon" class="fa fa-eye"></i>
                            </span>
                        </div>
                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                                <span 
                                class="position-absolute" 
                                style="top: 165px; right: 25px; cursor: pointer;" 
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

    <!-- Wrapper End-->

    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#users_table').DataTable().clear().destroy();

            $('#users_table').DataTable({
                pagelength: 10,
                responsive: true,
                processing: true,
                ordering: true,
                bLengthChange: true,
                serverSide: true,

                "ajax": {
                    "url": '{{ url('users/get-data') }}',
                    "type": "post",
                    "data": function(d) {},
                },
                aoColumns: [

                    {
                        data: 'name'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'phone_number'
                    },
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
                        data: 'action'
                    }
                    // Define more columns as per your table structure

                ],
                aoColumnDefs: [  {
                    bSortable: false,
                    aTargets: [0,1,2,3,4,5] // disables sorting on all columns
                },
                {
                    bSortable: true,
                    aTargets: [6] // only enable sorting on 'created_at'
                }],
                order: [
                    [6, 'desc']
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
    $('#changePasswordModal').modal('show');
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
