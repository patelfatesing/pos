@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <div class="content-page">
        <div class="container-fluid">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h4 class="mb-0">Vendor Delivery Invoice Report</h4>
                </div>
                <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
            </div>
            <div class="row align-items-center mb-2 mt-2">
                <div class="col-lg-12">

                    <div class="summary-badges">
                        <span class="badge bg-dark">Total Qty: <span id="sum_qty">0</span></span>
                        <span class="badge bg-dark">Items Amount: <span id="sum_items_amount">0.00</span></span>
                        <span class="badge bg-dark">Grand Total: <span id="sum_grand_total">0.00</span></span>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row g-2 mb-2">
                <div class="col-md-3">
                    <select id="vendor_id" class="form-control">
                        <option value="">All Vendors</option>
                        @foreach ($vendors as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- <div class="col-md-2">
                            <select id="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div> --}}
                <div class="col-md-2">
                    <input type="date" id="start_date" class="form-control w-120" />
                </div>
                <div class="col-md-2">
                    <input type="date" id="end_date" class="form-control w-120" />
                </div>

            </div>

            <div class="table-responsive rounded">
                <table class="table table-striped table-bordered nowrap" id="vendor_purchases_table">
                    <thead class="bg-white">
                        <tr class="ligth ligth-data">
                            <th>Sr No</th>
                            <th>Date</th>
                            <th>Bill No</th>
                            <th>Vendor</th>
                            <th>Items Qty</th>
                            <th>Items Amount</th>
                            <th>Excise Fee</th>
                            <th>VAT</th>
                            <th>TCS</th>
                            <th>Other Charges</th>
                            <th>Grand Total</th>
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

            const table = $('#vendor_purchases_table').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,

                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },

                ajax: {
                    url: "{{ route('reports.vendor_purchases.get_data') }}",
                    type: 'POST',
                    data: function(d) {

                        d.vendor_id = $('#vendor_id').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();

                    }
                },

                dom: "<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'Bf l>>t<'row'<'col-md-6'i><'col-md-6'p>>",

                columns: [{
                        data: 'sr_no',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'date'
                    },
                    {
                        data: 'bill_no'
                    },
                    {
                        data: 'vendor_name'
                    },
                    {
                        data: 'items_qty'
                    },
                    {
                        data: 'items_amount'
                    },
                    {
                        data: 'excise_fee'
                    },
                    {
                        data: 'vat'
                    },
                    {
                        data: 'tcs'
                    },
                    {
                        data: 'other_charges'
                    },
                    {
                        data: 'grand_total'
                    }
                ],

                order: [
                    [1, 'desc']
                ],

                pageLength: 10,

                buttons: [{

                    extend: 'collection',
                    text: '<i class="fa fa-download"></i>',
                    className: 'btn btn-info btn-sm',

                    buttons: [

                        {
                            extend: 'excelHtml5',
                            text: '<i class="fa fa-file-excel-o"></i> Excel',
                            title: 'Vendor Delivery Invoice Report',
                            filename: 'vendor_delivery_invoice_report',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },

                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf-o"></i> PDF',
                            filename: 'vendor_delivery_invoice_report',
                            orientation: 'landscape',
                            pageSize: 'A4',

                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                            },

                            customize: function(doc) {

                                doc.content.splice(0, 1);
                                doc.pageMargins = [15, 50, 15, 20];
                                doc.defaultStyle.fontSize = 7;

                                var table = doc.content[0].table;

                                table.widths = [
                                    25,
                                    60,
                                    60,
                                    '*',
                                    40,
                                    60,
                                    45,
                                    40,
                                    40,
                                    55,
                                    65
                                ];

                                doc.styles.tableHeader = {
                                    fillColor: '#2c3e50',
                                    color: 'white',
                                    alignment: 'center',
                                    bold: true,
                                    fontSize: 8
                                };

                                var body = table.body;

                                for (var i = 1; i < body.length; i++) {

                                    body[i][0].alignment = 'center';
                                    body[i][1].alignment = 'center';
                                    body[i][2].alignment = 'center';
                                    body[i][3].alignment = 'left';

                                    for (var j = 4; j < body[i].length; j++) {
                                        body[i][j].alignment = 'right';
                                    }
                                }

                                doc.content.unshift({

                                    margin: [0, 0, 0, 10],

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
                                            text: 'Vendor Delivery Invoice Report',
                                            alignment: 'center',
                                            fontSize: 14,
                                            bold: true,
                                            margin: [0, 8, 0, 0]
                                        },

                                        {
                                            width: '33%',
                                            text: 'Generated: ' + new Date()
                                                .toLocaleString(),
                                            alignment: 'right',
                                            fontSize: 8,
                                            margin: [0, 8, 0, 0]
                                        }

                                    ]

                                });

                            }

                        }

                    ]

                }],

                initComplete: function() {

                    $('.dataTables_filter input').attr("placeholder", "Search List...");

                    $('#vendor_id,#start_date,#end_date').on('change', function() {
                        table.ajax.reload();
                    });

                }

            });


            $('#vendor_purchases_table').on('xhr.dt', function(e, settings, json) {

                if (json && json.totals) {

                    $('#sum_qty').text(json.totals.qty ?? 0);
                    $('#sum_items_amount').text((json.totals.items_total ?? 0).toFixed(2));
                    $('#sum_grand_total').text((json.totals.grand_total ?? 0).toFixed(2));

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

                callback(canvas.toDataURL("image/png"));
            };

            img.src = url;

        }

        getBase64Image("https://liquorhub.in/assets/images/logo.png", function(base64) {
            pdfLogo = base64;
        });
    </script>
@endsection
