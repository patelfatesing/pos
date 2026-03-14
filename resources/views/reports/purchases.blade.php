{{-- resources/views/reports/purchases.blade.php --}}
@extends('layouts.backend.datatable_layouts')
<style>
    .custom-toolbar-row {
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
        gap: 6px;
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
        min-width: 130px;
    }

    .dataTables_filter {
        margin: 0 !important;
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
                        <h4 class="mb-0">Purchase Report</h4>
                    </div>
                    <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
                </div>

                <div class="table-responsive rounded mt-2">
                    <table class="table table-striped table-bordered nowrap" id="purchase_table" style="width:100%;">
                        <thead class="bg-white">
                            <tr class="ligth ligth-data">
                                <th>Sr No</th>
                                <th>Vendor</th>
                                <th>Total QTY</th>
                                <th>Total Amount</th>
                                <th>Category (from Subcategory)</th>
                                <th>Status</th>
                                <th>Shipping Date</th>

                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-end">Totals:</th>
                                <th id="ft_qty">0</th>
                                <th id="ft_amt">0.00</th>
                                <th colspan="3"></th>

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

               
                <select id="vendor_id" class="form-control form-control-sm">
                <option value="">All</option>
                @foreach ($vendors as $v)
                <option value="{{ $v->id }}">{{ $v->name }}</option>
                @endforeach
                </select>

                
                <select id="sub_category_id" class="form-control form-control-sm">
                <option value="">All</option>
                @foreach ($subcats as $sc)
                <option value="{{ $sc->id }}">{{ $sc->subcategory_name }}</option>
                @endforeach
                </select>

                <input type="date" id="start_date" class="form-control form-control-sm">
                <input type="date" id="end_date" class="form-control form-control-sm">

                </div>
                `;



            const table = $('#purchase_table').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,

                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },

                ajax: {
                    url: "{{ route('reports.purchase.data') }}",
                    type: 'POST',
                    data: function(d) {

                        d.vendor_id = $('#vendor_id').val();
                        d.sub_category_id = $('#sub_category_id').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();

                    }
                },

                dom: "<'custom-toolbar-row'<'filters-container'><'dt-buttons'B><'dataTables_filter'f><'dataTables_length'l>>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",

                columns: [{
                        data: 'sr_no',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'vendor_name'
                    },
                    {
                        data: 'total_qty'
                    },
                    {
                        data: 'total_amt'
                    },
                    {
                        data: 'category_name'
                    },
                    {
                        data: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'ship_date'
                    }
                ],

                order: [
                    [6, 'desc']
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
                                title: 'Purchase Report',
                                filename: 'purchase_report',
                                exportOptions: {
                                    columns: ':visible'
                                }
                            },

                            {
                                extend: 'pdfHtml5',
                                text: '<i class="fa fa-file-pdf-o"></i> PDF',
                                filename: 'purchase_report',
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
                                        body[i][2].alignment = 'center';
                                        body[i][3].alignment = 'right';
                                        body[i][4].alignment = 'center';
                                        body[i][5].alignment = 'center';
                                        body[i][6].alignment = 'center';

                                    }

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
                                                text: 'Purchase Report',
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

                        if (!$('#start_date').val()) $('#start_date').val(toISO(start));
                        if (!$('#end_date').val()) $('#end_date').val(toISO(now));
                    })();
                    $('#vendor_id,#sub_category_id,#start_date,#end_date').on('change', function() {
                        table.ajax.reload();
                    });
                },
                drawCallback: function(settings) {
                    const json = settings.json || {};
                    if (json.totals) {

                        $('#ft_qty').text(json.totals.qty || '0');
                        $('#ft_amt').text(json.totals.amt || '0.00');
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
