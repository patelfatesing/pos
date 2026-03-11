@extends('layouts.backend.datatable_layouts')
@section('page-content')
    <div class="content-page">
        <div class="container-fluid">
            <!-- Page Header -->

            <div class="row align-items-center mb-3">
                <div class="col-lg-12">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0">Users List</h4>
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
                        <table class="table table-striped table-bordered nowrap" id="users_table">
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
        var pdfLogo = "";
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#users_table').DataTable().clear().destroy();

            const table = $('#users_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },
                ajax: {
                    url: '{{ url('users/get-data') }}',
                    type: 'POST',
                    data: function(d) {
                        d.status = $('#status').val();
                    }
                },
                dom: "<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'Bf l>>t<'row'<'col-md-6'i><'col-md-6'p>>",
                initComplete: function() {
                    $('.dataTables_filter input').attr("placeholder", "Search List...");
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

                buttons: [{
                    extend: 'collection',
                    text: '<i class="fa fa-download"></i>',
                    className: 'btn btn-info btn-sm',
                    autoClose: true,
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '<i class="fa fa-file-excel-o"></i> Excel',
                            title: 'User List',
                            filename: 'user_list',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf-o"></i> PDF',
                            filename: 'user_list',
                            orientation: 'landscape',
                            pageSize: 'A4',

                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5]
                            },

                            customize: function(doc) {

                                // REMOVE default title
                                doc.content.splice(0, 1);

                                // CENTER TABLE
                                doc.content[0].alignment = 'center';

                                // MAKE TABLE WIDTH FULL PAGE
                                doc.content[0].table.widths = ['auto', '*', '*', '*', '*',
                                    '*'
                                ];

                                doc.styles.tableHeader.alignment = 'center';

                                var tableBody = doc.content[0].table.body;

                                for (var i = 1; i < tableBody.length; i++) {
                                    tableBody[i][0].alignment = 'center';
                                    tableBody[i][1].alignment = 'left';
                                    tableBody[i][2].alignment = 'center';
                                    tableBody[i][3].alignment = 'center';
                                    tableBody[i][4].alignment = 'center';
                                    tableBody[i][5].alignment = 'center';
                                }

                                // HEADER
                                doc.content.unshift({
                                    margin: [0, 0, 0, 12],
                                    columns: [{
                                            width: '33%',
                                            columns: [{
                                                    image: pdfLogo,
                                                    width: 30
                                                },
                                                {
                                                    text: 'LiquorHub',
                                                    fontSize: 11,
                                                    bold: true,
                                                    margin: [5, 8, 0, 0]
                                                }
                                            ]
                                        },
                                        {
                                            width: '34%',
                                            text: 'User List',
                                            alignment: 'center',
                                            fontSize: 16,
                                            bold: true,
                                            margin: [0, 8, 0, 0]
                                        },
                                        {
                                            width: '33%',
                                            text: 'Generated: ' + new Date()
                                                .toLocaleString(),
                                            alignment: 'right',
                                            fontSize: 9,
                                            margin: [0, 8, 0, 0]
                                        }
                                    ]
                                });

                                doc.styles.tableHeader.fontSize = 10;
                                doc.defaultStyle.fontSize = 9;
                            }
                        }
                    ]
                }],
                initComplete: function() {
                    // Inject status filter in correct order
                    $('<div class="status-filter d-flex align-items-center mr-2 mb-4">' +
                        '<select id="status" class="form-control form-control-sm">' +
                        '<option value="">All Status</option>' +
                        '<option value="yes">Active</option>' +
                        '<option value="no">Inactive</option>' +
                        '</select>' +
                        '</div>').insertBefore('.dt-buttons');
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

        function getBase64Image(url, callback) {
            var img = new Image();
            img.crossOrigin = "Anonymous";

            img.onload = function() {
                var canvas = document.createElement("canvas");
                canvas.width = this.width;
                canvas.height = this.height;

                var ctx = canvas.getContext("2d");
                ctx.drawImage(this, 0, 0);

                var dataURL = canvas.toDataURL("image/png");
                callback(dataURL);
            };

            img.src = url;
        }

        getBase64Image("https://liquorhub.in/assets/images/logo.png", function(base64) {
            pdfLogo = base64;
        });
    </script>
@endsection
