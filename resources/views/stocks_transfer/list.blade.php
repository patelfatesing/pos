@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <div class="content-page">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col-lg-12">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0">Stock Transfers </h4>
                        </div>
                        <div>
                            @if (auth()->user()->role_id == 1 || canCreate(auth()->user()->role_id, 'stock-transfer'))
                                <a href="{{ route('stock-transfer.craete-transfer') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> New Transfer
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-12">

                    <div class="table-responsive rounded">
                        <table class="table table-striped table-bordered nowrap" id="stock-transfers-table">
                            <thead class="bg-white">
                                <tr class="ligth ligth-data">
                                     <th>Transfer #</th>
                                            <th>From</th>
                                            <th>To</th>
                                            <th>Products</th>
                                            <th>Total Qty</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Created By</th>
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

    
@endsection

@section('scripts')
    <script>
        var pdfLogo = "";

        $(document).ready(function() {
            $('#stock-transfers-table').DataTable({
                processing: true,
                serverSide: true,
                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },
                ajax: "{{ route('stock-transfer.get-transfer-data') }}",
                 dom: "<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'f l>>t<'row'<'col-md-6'i><'col-md-6'p>>",
               initComplete: function() {
                    $('.dataTables_filter input').attr("placeholder", "Search List...");
                },
                columns: [{
                        data: 'transfer_number'
                    },
                    {
                        data: 'from'
                    },
                    {
                        data: 'to'
                    },
                    {
                        data: 'total_products',
                        render: function(data) {
                            return data + ' item(s)';
                        }
                    },
                    {
                        data: 'total_quantity',
                        render: function(data) {
                            return data + ' units';
                        }
                    },
                    {
                        data: 'transferred_at'
                    },
                    {
                        data: 'status',
                        render: function(data) {
                            let badgeClass = 'badge badge-';
                            switch (data.toLowerCase()) {
                                case 'completed':
                                    badgeClass += 'success';
                                    break;
                                case 'pending':
                                    badgeClass += 'warning';
                                    break;
                                case 'cancelled':
                                    badgeClass += 'danger';
                                    break;
                                default:
                                    badgeClass += 'info';
                            }
                            return '<span class="' + badgeClass + '">' + data + '</span>';
                        }
                    },
                    {
                        data: 'created_by'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [0, 3, 4, 5, 6]
                }],
                order: [
                    [5, 'desc']
                ], // Order by transfer date descending
                pageLength: 10,
                responsive: true,
                buttons: [{
                    extend: 'collection',
                    text: '<i class="fa fa-download"></i>',
                    className: 'btn btn-info btn-sm',
                    autoClose: true,
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '<i class="fa fa-file-excel-o"></i> Excel',
                            title: 'Stock Inventory',
                            filename: 'stock_transfers',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf-o"></i> PDF',
                            filename: 'stock_transfers',
                            orientation: 'landscape',
                            pageSize: 'A4',

                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6]
                            },

                            customize: function(doc) {

                                // REMOVE default title
                                doc.content.splice(0, 1);

                                doc.styles.tableHeader.alignment = 'center';

                                var tableBody = doc.content[0].table.body;

                                for (var i = 1; i < tableBody.length; i++) {

                                    tableBody[i][3].alignment = 'center';
                                    tableBody[i][4].alignment = 'center';

                                }

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
                                            text: 'Stock Transfers',
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
