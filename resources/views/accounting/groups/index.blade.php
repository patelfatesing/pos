{{-- resources/views/accounting/groups/index.blade.php --}}
@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0">Account Groups</h4>
                    </div>
                    @if (auth()->user()->role_id == 1 || canCreate(auth()->user()->role_id, 'accounting-groups-create'))
                        <a href="{{ route('accounting.groups.create') }}" class="btn btn-primary">
                            <i class="las la-plus me-1"></i> Add Group
                        </a>
                    @endif
                </div>
                <div class="row mt-1">

                    <div class="col-lg-12">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif

                        <div class="table-responsive rounded mb-3">
                            <table class="table table-striped table-bordered nowrap" id="groups_table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Nature</th>
                                        <th>Affects Gross</th>
                                        <th>Parent</th>
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

    <script>
        var pdfLogo = "";
        $(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            const table = $('#groups_table').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },
                ajax: {
                    url: "{{ route('accounting.groups.getData') }}",
                    type: "POST"
                },
                dom: "<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'Bf l>>t<'row'<'col-md-6'i><'col-md-6'p>>",
                initComplete: function() {
                    $('.dataTables_filter input').attr("placeholder", "Search List...");
                },
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'nature',
                        name: 'nature'
                    },
                    {
                        data: 'affects_gross',
                        name: 'affects_gross',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'parent',
                        name: 'parent'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'asc']
                ],
                 buttons: [{
                    extend: 'collection',
                    text: '<i class="fa fa-download"></i>',
                    className: 'btn btn-info btn-sm',
                    autoClose: true,
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '<i class="fa fa-file-excel-o"></i> Excel',
                            title: 'Group List',
                            filename: 'group_list',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf-o"></i> PDF',
                            filename: 'group_list',
                            orientation: 'landscape',
                            pageSize: 'A4',

                            exportOptions: {
                                columns: [0, 1, 2, 3]
                            },

                            customize: function(doc) {

                                // REMOVE default title
                                doc.content.splice(0, 1);

                                // CENTER TABLE
                                doc.content[0].alignment = 'center';

                                // MAKE TABLE WIDTH FULL PAGE
                                doc.content[0].table.widths = ['auto', '*', '*', '*'
                                ];

                                doc.styles.tableHeader.alignment = 'center';

                                var tableBody = doc.content[0].table.body;

                                for (var i = 1; i < tableBody.length; i++) {
                                    tableBody[i][0].alignment = 'center';
                                    tableBody[i][1].alignment = 'left';
                                    tableBody[i][2].alignment = 'center';
                                    tableBody[i][3].alignment = 'center';
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
                                            text: 'Group List',
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
            });

            // SweetAlert delete
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                const form = $(this).closest('form');
                const url = form.attr('action'); // should point to route('...destroy', id)

                swal({
                    title: "Are you sure?",
                    text: "This group will be permanently deleted.",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((will) => {
                    if (!will) return;

                    $.ajax({
                        url: url,
                        type: 'DELETE', // ✅ correct verb (no spaces)
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function() {
                            // reload DataTable (make sure `table` is in scope)
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            alert('Delete failed: ' + (xhr.responseJSON?.message ||
                                'Server error'));
                        }
                    });
                });
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
