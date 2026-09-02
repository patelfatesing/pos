@extends('layouts.backend.datatable_layouts')

@section('styles')
    <style>
        #scrollToTop {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            text-align: center;
            line-height: 42px;
            color: #007bff;
            z-index: 1000;
            transition: background 0.3s, color 0.3s;
            text-decoration: none;
            display: none;
        }
        #scrollToTop:hover {
            background: #007bff;
            color: white;
        }
    </style>
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <!-- Page Header -->
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between mb-2">
                    <div>
                        <h4 class="mb-0">Party Customer List</h4>
                    </div>
                    @if (auth()->user()->role_id == 1 || canCreate(auth()->user()->role_id, 'party-customer-create'))
                        <a href="{{ route('party-users.create') }}" class="btn btn-success add-list">
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
    <a href="#" id="scrollToTop"><i class="fa fa-arrow-up"></i></a>

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
                        <button type="submit" class="btn btn-success">Save changes</button>
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
            // Scroll to top
            $(window).scroll(function() {
                if ($(this).scrollTop() > 200) {
                    $('#scrollToTop').fadeIn();
                } else {
                    $('#scrollToTop').fadeOut();
                }
            });
            $('#scrollToTop').click(function(e) {
                e.preventDefault();
                $('html, body').animate({scrollTop: 0}, 'slow');
            });

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const table = $('#party_users_table').DataTable({
                pagelength: 10,
                responsive: true,
                processing: true,
                ordering: true,
                bLengthChange: true,
                serverSide: true,
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


                buttons: [],


                initComplete: function() {

                    $('.dataTables_filter input').attr("placeholder", "Search List...");

                    $('<div class="status-filter">' +
                        '<select id="status" class="form-control form-control-sm">' +
                        '<option value="">All Status</option>' +
                        '<option value="active">Active</option>' +
                        '<option value="inactive">Inactive</option>' +
                        '</select>' +
                        '</div>').insertBefore('.dt-buttons');

                    $('.status-filter').on('change', function() {
                        table.ajax.reload();
                    });

                    // Fix dropdown clipping in responsive table
                    $('.table-responsive').on('shown.bs.dropdown', function(e) {
                        var $menu = $(e.target).find('.dropdown-menu');
                        $('body').append($menu.detach());
                        $menu.css({
                            'display': 'block',
                            'top': $(e.target).find('[data-toggle="dropdown"]').offset().top + $(e.target).find('[data-toggle="dropdown"]').outerHeight(),
                            'left': $(e.target).find('[data-toggle="dropdown"]').offset().left - ($menu.outerWidth() - $(e.target).find('[data-toggle="dropdown"]').outerWidth())
                        });
                    });

                    $('.table-responsive').on('hide.bs.dropdown', function(e) {
                        var $menu = $('body > .dropdown-menu:last-child');
                        $(e.target).append($menu.detach().css({
                            'display': '',
                            'top': '',
                            'left': ''
                        }));
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
