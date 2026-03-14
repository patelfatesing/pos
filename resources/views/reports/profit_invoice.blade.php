{{-- resources/views/reports/profit_invoice.blade.php --}}
@extends('layouts.backend.datatable_layouts')

<style>
    .custom-toolbar-row {
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
        gap: 8px;
        margin-bottom: 10px;
        justify-content: flex-end;
    }

    .filters.one-line {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: nowrap;
    }

    .filters.one-line select,
    .filters.one-line input {
        height: 32px;
        min-width: 140px;
    }

    .dataTables_filter {
        margin: 0 !important;
    }

    .dataTables_filter input {
        height: 32px;
        width: 200px;
    }

    .dataTables_length {
        margin: 0 !important;
    }

    .dt-buttons .btn {
        margin-right: 4px;
    }
</style>

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0">Profit on Sales Invoice Report</h4>
                    </div>
                    <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
                </div>

                <div class="table-responsive rounded mt-2">
                    <table class="table table-striped table-bordered nowrap" id="profit_invoice_table" style="width:100%;">
                        <thead class="bg-white">
                            <tr class="ligth ligth-data">
                                <th>Sr No</th>
                                <th>Invoice</th>
                                <th>Branch</th>
                                <th>Sub Total</th>
                                <th>Commission</th>
                                <th>Party</th>
                                <th>Payment Mode</th>
                                <th>Profit</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Totals:</th>
                                <th id="ft_sub_total">0.00</th>
                                <th id="ft_commission">0.00</th>
                                <th id="ft_party">0.00</th>
                                <th></th>
                                <th id="ft_profit">0.00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

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

            const filtersHtml = `
                <div class="filters one-line">

                <select id="branch_id" class="form-control form-control-sm">
                <option value="">All Branches</option>
                @foreach ($branches as $b)
                <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
                </select>

                <input type="date" id="start_date" class="form-control form-control-sm">

                <input type="date" id="end_date" class="form-control form-control-sm">

                </div>
                `;

            const table = $('#profit_invoice_table').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,

                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },

                ajax: {
                    url: "{{ route('reports.profit_invoice.data') }}",
                    type: 'POST',
                    data: function(d) {

                        d.branch_id = $('#branch_id').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();

                    }
                },

                dom: "<'custom-toolbar-row'<'filters-container'><'dt-buttons'B><'dataTables_filter'f><'dataTables_length'l>>" +
                    "t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
                columns: [{
                        data: 'sr_no',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'invoice'
                    },
                    {
                        data: 'branch_name'
                    },
                    {
                        data: 'sub_total'
                    },
                    {
                        data: 'commission_amount'
                    },
                    {
                        data: 'party_amount'
                    },
                    {
                        data: 'payment_mode'
                    },
                    {
                        data: 'profit'
                    }
                ],

                order: [
                    [2, 'asc'],
                    [7, 'desc']
                ],

                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],

                pageLength: 10,

                buttons: [

                    {
                        extend: 'collection',
                        text: '<i class="fa fa-download"></i> Export',
                        className: 'btn btn-info btn-sm',
                        autoClose: true,

                        buttons: [

                            {
                                extend: 'excelHtml5',
                                text: '<i class="fa fa-file-excel-o"></i> Excel',
                                title: 'Profit on Sales Invoice',
                                filename: 'profit_on_sales_invoice',
                                exportOptions: {
                                    columns: ':visible'
                                }
                            },

                            {
                                extend: 'pdfHtml5',
                                text: '<i class="fa fa-file-pdf-o"></i> PDF',
                                filename: 'profit_on_sales_invoice',
                                orientation: 'landscape',
                                pageSize: 'A4',
                                exportOptions: {
                                    columns: ':visible'
                                },
                                customize: function(doc) {

                                    doc.content.splice(0, 1);

                                    doc.pageMargins = [15, 55, 15, 25];

                                    doc.defaultStyle.fontSize = 7;

                                    var table = doc.content[0].table;

                                    table.widths = new Array(table.body[0].length).fill('*');

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
                                        body[i][1].alignment = 'left';
                                        body[i][2].alignment = 'left';
                                        body[i][3].alignment = 'right';
                                        body[i][4].alignment = 'right';
                                        body[i][5].alignment = 'right';
                                        body[i][6].alignment = 'center';
                                        body[i][7].alignment = 'right';

                                    }


                                    /* ADD TOTAL ROW */

                                    body.push([{
                                            text: '',
                                            border: [false, false, false, false]
                                        },
                                        {
                                            text: '',
                                            border: [false, false, false, false]
                                        },
                                        {
                                            text: 'Totals',
                                            bold: true,
                                            alignment: 'right'
                                        },

                                        {
                                            text: $('#ft_sub_total').text(),
                                            bold: true,
                                            alignment: 'right'
                                        },
                                        {
                                            text: $('#ft_commission').text(),
                                            bold: true,
                                            alignment: 'right'
                                        },
                                        {
                                            text: $('#ft_party').text(),
                                            bold: true,
                                            alignment: 'right'
                                        },
                                        {
                                            text: '',
                                            border: [false, false, false, false]
                                        },
                                        {
                                            text: $('#ft_profit').text(),
                                            bold: true,
                                            alignment: 'right'
                                        }
                                    ]);


                                    doc.content[0].layout = {
                                        hLineWidth: function() {
                                            return .5
                                        },
                                        vLineWidth: function() {
                                            return .5
                                        },
                                        hLineColor: function() {
                                            return '#aaa'
                                        },
                                        vLineColor: function() {
                                            return '#aaa'
                                        },
                                        paddingLeft: function() {
                                            return 4
                                        },
                                        paddingRight: function() {
                                            return 4
                                        }
                                    };


                                    /* HEADER */

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
                                                text: 'Profit on Sales Invoice Report',
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

                    }
                ],



                initComplete: function() {

                    $('.filters-container').html(filtersHtml);


                    (function() {

                        const toISO = d => new Date(d.getTime() - (d.getTimezoneOffset() * 60000))
                            .toISOString().slice(0, 10);

                        const now = new Date();

                        const start = new Date();

                        start.setDate(now.getDate() - 29);

                        $('#start_date').val(toISO(start));

                        $('#end_date').val(toISO(now));

                    })();

                    $('#branch_id,#start_date,#end_date').on('change', function() {

                        table.ajax.reload();

                    });

                },


                drawCallback: function(settings) {

                    const json = settings.json || {};

                    if (json.totals) {

                        $('#ft_sub_total').text(json.totals.sub_total || '0.00');
                        $('#ft_commission').text(json.totals.commission || '0.00');
                        $('#ft_party').text(json.totals.party || '0.00');
                        $('#ft_profit').text(json.totals.profit || '0.00');

                    }

                },

                columnDefs: [{
                    targets: '_all',
                    defaultContent: ''
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
