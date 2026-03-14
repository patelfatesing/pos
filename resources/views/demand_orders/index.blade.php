@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <div class="content-page">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between mb-2">
                <div>
                    <h4 class="mb-0">Demand Order List</h4>
                </div>
                <a href="{{ route('demand-order.step1') }}" class="btn btn-primary add-list">
                    <i class="las la-plus mr-3"></i>Add Demand Order
                </a>
            </div>


            <!-- Table -->
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive rounded">
                        <table class="table table-striped table-bordered nowrap" id="demand_order_tbl" style="width:100%;">
                            <thead class="bg-white">
                                <tr class="ligth ligth-data">
                                    <th>Sr No</th>
                                    <th>Vendor</th>
                                    <th>Purchase Date</th>
                                    <th>Shipping Date</th>
                                    <th>Total Quantity</th>
                                    <th>Total Cost Price</th>
                                    <th>Sub Category</th>
                                    <th>Status</th>
                                    <th>Action</th>
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

    <!-- Modal for PDF Preview -->
    <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">File Preview</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <iframe id="pdfIframe" src="" width="100%" height="600px" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script>
        var pdfLogo = "";
        // Function to open PDF in modal
        function openPDF(fileUrl) {
            $('#pdfIframe').attr('src', fileUrl);
        }

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Check if DataTable is already initialized and destroy it before re-initializing
            if ($.fn.DataTable.isDataTable('#demand_order_tbl')) {
                $('#demand_order_tbl').DataTable().clear().destroy();
            }

            // Initialize DataTable with server-side processing
            $('#demand_order_tbl').DataTable({
                pageLength: 10,
                responsive: true,
                processing: true,
                serverSide: true,
                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },
                ajax: {
                    url: '{{ url('demand-order/get-data') }}',
                    type: 'POST',
                },
                dom: "<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'f l>>t<'row'<'col-md-6'i><'col-md-6'p>>",
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
                    {
                        data: 'vendor'
                    },
                    {
                        data: 'purchase_date'
                    },
                    {
                        data: 'shipping_date'
                    },
                    {
                        data: 'total_quantity'
                    },
                    {
                        data: 'total_sell_price'
                    },
                    {
                        data: 'sub_category'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [4, 5, 6, 7] // make "action" column unsortable
                }],
                order: [
                    [3, 'desc']
                ], // Sort by status DESC by default

                buttons: [{
                    extend: 'collection',
                    text: '<i class="fa fa-download"></i>',
                    className: 'btn btn-info btn-sm',
                    autoClose: true,
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '<i class="fa fa-file-excel-o"></i> Excel',
                            title: 'Demand Order List',
                            filename: 'demand_order_list',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf-o"></i> PDF',
                            filename: 'demand_order_list',
                            orientation: 'landscape',
                            pageSize: 'A4',

                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6, 7]
                            },

                            customize: function(doc) {

                                // REMOVE default title
                                doc.content.splice(0, 1);

                                // CENTER TABLE
                                doc.content[0].alignment = 'center';

                                // MAKE TABLE WIDTH FULL PAGE
                                doc.content[0].table.widths = ['auto', '*', '*', '*', '*',
                                    '*', '*', '*'
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
                                    tableBody[i][6].alignment = 'center';
                                    tableBody[i][7].alignment = 'center';
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
                                            text: 'Demand Order List',
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
                lengthMenu: [
                    [10, 25, 50],
                    ['10 rows', '25 rows', '50 rows']
                ]
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
