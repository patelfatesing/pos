@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Wrapper Start -->

    <div class="content-page">
        <div class="container-fluid">
            <div class="card-header mb-3 d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h4 class="mb-0">Pack Size List</h4>
                </div>
                @if (auth()->user()->role_id == 1 || canCreate(auth()->user()->role_id, 'pack-size-create'))
                    <button class="btn btn-primary add-list" data-toggle="modal" data-target="#packSizeModal">
                        <i class="las la-plus mr-3"></i>Create New Pack Size
                    </button>
                @endif
            </div>

            <div class="table-responsive rounded mb-3">
                    <table class="table table-striped table-bordered nowrap" id="pack_size_tbl">
                    <thead class="bg-white text-uppercase">
                        <tr class="ligth ligth-data">
                            <th>
                                <b>S</b>ize
                            </th>
                            <th>Status</th>
                            <th data-type="date" data-format="YYYY/DD/MM">Created Date</th>
                            {{-- <th>Action</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <!-- Page end  -->
        </div>
    </div>

    <!-- Wrapper End-->
    <!-- Add Pack Size Modal -->
    <div class="modal fade" id="packSizeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">

                <div class="modal-header card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h5 class="modal-title">Add Pack Size</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <form id="packSizeForm">
                    @csrf
                    <div class="modal-body">

                        <div class="form-group">
                            <label>Size <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="size" placeholder="Enter Size">
                                <div class="input-group-append">
                                    <span class="input-group-text">ML</span>
                                </div>
                            </div>
                            <span class="text-danger error-size"></span>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Add Pack Size</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        var pdfLogo = "";
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#pack_size_tbl').DataTable().clear().destroy();

            $('#pack_size_tbl').DataTable({
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
                "ajax": {
                    "url": '{{ url('pack-size/get-data') }}',
                    "type": "post",
                    "data": function(d) {},
                },
                dom: "<'row mb-2'<'col-md-12 d-flex justify-content-end align-items-center'Bf l>>t<'row'<'col-md-6'i><'col-md-6'p>>",
                initComplete: function() {
                    $('.dataTables_filter input').attr("placeholder", "Search List...");
                },
                aoColumns: [{
                        data: 'size'
                    },
                    {
                        data: 'is_active'
                    },
                    {
                        data: 'created_at'
                    },
                    // {
                    //     data: 'action'
                    // }
                    // Define more columns as per your table structure
                ],
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [1] // make "action" column unsortable
                }],
                order: [
                    [2, 'desc']
                ], // 🟢 Sort by created_at DESC by default
                
                lengthMenu: [
                    [10, 25, 50],
                    ['10 rows', '25 rows', '50 rows', 'All']
                ],
                 buttons: [{
                    extend: 'collection',
                    text: '<i class="fa fa-download"></i>',
                    className: 'btn btn-info btn-sm',
                    autoClose: true,
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '<i class="fa fa-file-excel-o"></i> Excel',
                            title: 'Pack Size',
                            filename: 'pack_size',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf-o"></i> PDF',
                            filename: 'pack_size',
                            orientation: 'landscape',
                            pageSize: 'A4',

                            exportOptions: {
                                columns: [0, 1]
                            },

                            customize: function(doc) {

                                // REMOVE default title
                                doc.content.splice(0, 1);

                                doc.styles.tableHeader.alignment = 'center';


                                // HEADER
                                doc.content.unshift({

                                    margin: [0, 0, 0, 12],

                                    columns: [

                                        {
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
                                            text: 'Pack Size',
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
                }]

            });

        });

        function delete_category(id) {

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

        $(document).on('submit', '#packSizeForm', function(e) {
            e.preventDefault();

            $('.error-size').text('');

            $.ajax({
                url: "{{ route('packsize.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {

                    $('#packSizeModal').modal('hide');
                    $('#packSizeForm')[0].reset();

                    $('#pack_size_tbl').DataTable().ajax.reload(null, false);

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Pack Size added successfully',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        if (errors.size) {
                            $('.error-size').text(errors.size[0]);
                        }
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
