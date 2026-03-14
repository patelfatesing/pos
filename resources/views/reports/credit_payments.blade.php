@extends('layouts.backend.datatable_layouts')

@section('styles')
    <style>
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

        .custom-toolbar-row .filters {
            order: 3;
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .custom-toolbar-row .dataTables_filter {
            order: 4;
            margin-left: auto;
        }

        .dt-buttons .btn {
            margin-right: 5px;
        }

        @media(max-width:768px) {
            .custom-toolbar-row>div {
                flex: 1 1 100%;
                margin-bottom: 10px;
            }
        }

        .w-140 {
            width: 140px
        }
    </style>
@endsection

@section('page-content')
    <div class="content-page">
        <div class="container-fluid">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h4 class="mb-0">Credit Party Customer Ledger Report</h4>
                </div>
                <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
            </div>

            <!-- Filters -->
            <div class="row g-2 mb-2 mt-2">
                <div class="col-md-2">
                    <select id="branch_id" class="form-control">
                        <option value="">All Branches</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="party_user_id" class="form-control">
                        <option value="">All Party Customers</option>
                        @foreach ($parties as $p)
                            <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- <div class="col-md-2">
          <select id="type" class="form-control">
            <option value="">Type: All</option>
            <option value="debit">Debit (Payment)</option>
            <option value="credit">Credit (On Credit)</option>
          </select>
        </div> --}}
                <div class="col-md-1">
                    <select id="status" class="form-control">
                        <option value="">Status</option>
                        <option value="paid">Paid</option>
                        <option value="partial_paid">Partial</option>
                        <option value="unpaid">Unpaid</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" id="start_date" class="form-control w-140" />
                </div>
                <div class="col-md-2">
                    <input type="date" id="end_date" class="form-control w-140" />
                </div>
            </div>

            <div class="table-responsive rounded">
                <table class="table table-striped table-bordered nowrap" id="credit_payments_table">
                    <thead class="bg-white">
                        <tr class="ligth ligth-data">
                            <th>Sr No</th>
                            <th>Date</th>
                            <th>Branch</th>
                            <th>Party</th>
                            <th>Invoice</th>
                            <th>Type</th>

                            <th>Total</th>
                            <th>Credit</th>
                            <th>Debit</th>
                            <th>Net (Cr−Dr)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>

         var pdfLogo = "";
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const table = $('#credit_payments_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },
                ajax: {
                    url: "{{ route('reports.credit_payments.get_data') }}",
                    type: 'POST',
                    data: function(d) {
                        d.branch_id = $('#branch_id').val();
                        d.party_user_id = $('#party_user_id').val();
                        d.type = $('#type').val();
                        d.transaction_kind = $('#transaction_kind').val();
                        d.status = $('#status').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                dom: "<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'Bf l>>t<'row'<'col-md-6'i><'col-md-6'p>>",
                initComplete: function() {
                    $('.dataTables_filter input').attr("placeholder", "Search List...");
                },
                columns: [{
                        data: 'sr_no',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'tx_date'
                    },
                    {
                        data: 'branch_name'
                    },
                    {
                        data: 'party_name'
                    },
                    {
                        data: 'invoice'
                    },
                    {
                        data: 'type'
                    }, // badge HTML

                    {
                        data: 'total_amount'
                    },
                    {
                        data: 'credit_amount'
                    },
                    {
                        data: 'debit_amount'
                    },
                    {
                        data: 'net_amount'
                    },
                    {
                        data: 'status'
                    }
                ],
                order: [
                    [1, 'desc']
                ], // date desc
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
                            title: 'Credit Party Customer Ledge Report',
                            filename: 'credit_party_customer_ledge_report',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf-o"></i> PDF',
                            filename: 'credit_party_customer_ledge_report',
                            orientation: 'landscape',
                            pageSize: 'A4',

                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                            },

                            customize: function(doc) {

                                // REMOVE default title
                                doc.content.splice(0, 1);

                                // CENTER TABLE
                                doc.content[0].alignment = 'center';

                                // MAKE TABLE WIDTH FULL PAGE
                                doc.content[0].table.widths = ['auto', '*', '*', '*', '*',
                                    '*', '*', '*', '*', '*', '*'
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
                                    tableBody[i][6].alignment = 'right';
                                    tableBody[i][7].alignment = 'right';
                                    tableBody[i][8].alignment = 'right';
                                    tableBody[i][9].alignment = 'right';
                                    tableBody[i][10].alignment = 'center';  

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
                                            text: 'Credit Party Customer Ledge Report',
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
                    $('#branch_id, #party_user_id, #type, #transaction_kind, #status, #start_date, #end_date')
                        .on('change', function() {
                            table.ajax.reload();
                        });
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
