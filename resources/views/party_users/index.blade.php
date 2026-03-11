@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <!-- Page Header -->
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0">Party Customer List</h4>
                    </div>
                    @if (auth()->user()->role_id == 1 || canCreate(auth()->user()->role_id, 'party-customer-create'))
                        <a href="{{ route('party-users.create') }}" class="btn btn-primary add-list">
                            <i class="las la-plus mr-3"></i>Create New Party Customer
                        </a>
                    @endif
                </div>

                <!-- Table -->
                <div class="row mt-1">
                    <div class="col-12">
                        <div class="table-responsive rounded">

                            <table class="table table-striped table-bordered nowrap" id="party_users_table">
                                <thead class="bg-white">
                                    <tr class="ligth ligth-data">
                                        <th>Sr No</th> <!-- Added this line -->
                                        <th>Customer Name</th>
                                        {{-- <th>Email</th> --}}
                                        <th>Phone</th>
                                        <th>Credit</th>
                                        <th>Status</th>
                                        <th>Pending Credit</th>
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

    @php
        // Calculate tomorrow's date
$minDate = \Carbon\Carbon::today()->addDay()->format('Y-m-d');
    @endphp

    <div class="modal fade bd-example-modal-lg" id="dueDateModal" tabindex="-1" role="dialog"
        aria-labelledby="dueDateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="durDateForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="dueDateModalLabel">Due Date Set</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="party_user_id" id="party_user_id" value="">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Due Date</label>
                                    <input type="date" name="due_date" min="{{ $minDate }}" class="form-control"
                                        id="due_date">
                                    <span class="text-danger" id="due_date_error"></span>
                                </div>
                            </div>
                        </div>

                        {{-- <span class="mt-2 badge badge-pill border border-secondary text-secondary">
                            {{ __('messages.change_date_msg') }}
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

            const table = $('#party_users_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                 language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },
                ajax: {
                    url: '{{ url('party-users/get-data') }}',
                    type: 'POST',
                    data: function(d) {
                        d.status = $('#status').val();
                    }
                },
                dom: "<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'Bf l>>t<'row'<'col-md-6'i><'col-md-6'p>>",
                
                columns: [{
                        data: null,
                        name: 'sr_no',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    }, {
                        data: 'first_name'
                    },
                    // {
                    //     data: 'email'
                    // },
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
                        data: 'use_credit',
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
                    aTargets: [1, 2, 3, 4, 5, 6] // make "action" column unsortable
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
                            title: 'Party Customer List',
                            filename: 'Party_Customer_List',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf-o"></i> PDF',
                            filename: 'Party_Customer_List',
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
                                            text: 'Party Customer List',
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

                $('.dataTables_filter input').attr("placeholder", "Search List...");

                $('<div class="status-filter d-flex align-items-center mr-2 mb-4">' +
                    '<select id="status" class="form-control form-control-sm">' +
                    '<option value="">All Status</option>' +
                    '<option value="active">Active</option>' +
                    '<option value="inactive">Inactive</option>' +
                    '</select>' +
                    '</div>').insertBefore('.dt-buttons');

                $('#status').on('change', function () {
                    table.ajax.reload();
                });
            }
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
                        success: function(res) {
                            if (res.status === "error") {
                                Swal.fire("Error!", res.message, "error");
                                return;
                            }
                            $('#party_users_table').DataTable().ajax.reload();
                            Swal.fire("Deleted!", "The party user has been deleted.", "success");
                        },
                        error: function() {
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
                    Swal.fire("Error", "Failed to load form.", "error");
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
                    $.post("{{ url('party-users/status-change') }}", {
                        id: id,
                        status: newStatus
                    }).done(function() {
                        Swal.fire("Success!", "Status has been changed.", "success");
                        $('#party_users_table').DataTable().ajax.reload(null, false);
                    }).fail(function() {
                        Swal.fire("Error!", "Something went wrong.", "error");
                    });
                }
            });
        }

        function set_due_date(u_id) {


            $('#party_user_id').val(u_id);
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var myModal = new bootstrap.Modal(document.getElementById('dueDateModal'));
                myModal.show();
            } else {
                // For Bootstrap 4 (with jQuery)
                $('#dueDateModal').modal('show');
            }
            // $('#dueDateModal').modal('show');
        }

        $('#durDateForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous validation errors

            $('#due_date_error').text('');

            let formData = {
                _token: $('input[name="_token"]').val(),
                party_user_id: $('#party_user_id').val(),
                due_date: $('#due_date').val()
            };

            $.ajax({
                type: "POST",
                url: "{{ route('party-users.set.due.date') }}", // Adjust to match your route name
                data: formData,
                success: function(response) {
                    // Show success message
                    alert(response.message); // or use toastr.success()

                    // Close and reset modal
                    var modalEl = document.getElementById('dueDateModal');
                    var myModal = new bootstrap.Modal(modalEl);
                    myModal.hide();
                    $('#durDateForm')[0].reset();

                    // Reload DataTable
                    $('#party_users_table').DataTable().ajax.reload(null, false);
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;

                        if (errors.due_date) {
                            $('#due_date_error').text(errors.due_date[0]);
                        }
                    } else {
                        alert("An unexpected error occurred.");
                    }
                }
            });
        });

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
