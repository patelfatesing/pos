{{-- resources/views/reports/worst_selling.blade.php --}}
@extends('layouts.backend.datatable_layouts')

<style>
    .dataTables_wrapper .row {
        flex-wrap: nowrap;
        justify-content: flex-end;
        gap: 4px;
    }

    .filters-container {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .dt-toolbar {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .dataTables_filter input {
        width: 160px;
        height: 32px;
        border-radius: 6px;
    }

    .dataTables_length select {
        height: 32px;
    }
</style>

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0">Worst Selling Products</h4>
                    </div>
                    <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
                </div>


                <div class="table-responsive rounded mt-2">
                    <table class="table table-striped table-bordered nowrap" id="worst_selling_table">

                        <thead class="bg-white">
                            <tr class="ligth ligth-data">
                                <th>Sr No</th>
                                <th>Product</th>
                                <th>QTY</th>
                            </tr>
                        </thead>

                        <tbody></tbody>

                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-end">Total QTY:</th>
                                <th id="ft_qty">0.00</th>
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


            /* ---------- Filters HTML ---------- */

            const filtersHtml = `
<div class="d-flex align-items-center" style="gap:4px;">

<input type="date" id="start_date" class="form-control form-control-sm" style="width:140px">

<input type="date" id="end_date" class="form-control form-control-sm" style="width:140px">

<select id="branch_id" class="form-control form-control-sm" style="width:160px">
<option value="">All Branches</option>
@foreach ($branches as $b)
<option value="{{ $b->id }}">{{ $b->name }}</option>
@endforeach
</select>

</div>
`;


            /* ---------- DataTable ---------- */

            const table = $('#worst_selling_table').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,

                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },

                ajax: {
                    url: "{{ route('reports.worst_selling.data') }}",
                    type: 'POST',
                    data: function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.branch_id = $('#branch_id').val();
                    }
                },

                dom: "<'row align-items-center justify-content-end mb-2'<'col-auto filters-container'><'col-auto dt-toolbar'Bfl>>" +
                    "t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",

                columns: [{
                        data: 'sr_no',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'product_name'
                    },
                    {
                        data: 'qty'
                    }
                ],

                order: [
                    [2, 'asc']
                ], // worst first

                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],

                pageLength: 10,


                /* ---------- Export Buttons ---------- */

                buttons: [

                    {
                        extend: 'collection',
                        text: '<i class="fa fa-download"></i>',
                        className: 'btn btn-info btn-sm',
                        autoClose: true,

                        buttons: [

                            {
                                extend: 'excelHtml5',
                                text: '<i class="fa fa-file-excel-o"></i> Excel',
                                title: 'Worst Selling Products Report',
                                filename: 'worst_selling_products',
                                exportOptions: {
                                    columns: ':visible'
                                }
                            },

                            {
                                extend: 'pdfHtml5',
                                text: '<i class="fa fa-file-pdf-o"></i> PDF',
                                filename: 'worst_selling_products',
                                orientation: 'landscape',
                                pageSize: 'A4',

                                exportOptions: {
                                    columns: ':visible'
                                },

                                customize: function(doc) {

                                    doc.content.splice(0, 1);

                                    doc.pageMargins = [15, 55, 15, 25];

                                    doc.defaultStyle.fontSize = 8;

                                    var table = doc.content[0].table;

                                    table.widths = new Array(table.body[0].length).fill('*');

                                    doc.styles.tableHeader = {
                                        fillColor: '#2c3e50',
                                        color: 'white',
                                        alignment: 'center',
                                        bold: true,
                                        fontSize: 9
                                    };

                                    var body = table.body;

                                    for (var i = 1; i < body.length; i++) {

                                        body[i][0].alignment = 'center';
                                        body[i][1].alignment = 'left';
                                        body[i][2].alignment = 'right';

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
                                        }
                                    };


                                    /* ----- Header ----- */

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
                                                text: 'Worst Selling Products Report',
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


                /* ---------- Init ---------- */

                initComplete: function() {

                    $('.filters-container').html(filtersHtml);


                    /* default last 30 days */

                    (function() {

                        const toISO = d => new Date(d.getTime() - (d.getTimezoneOffset() * 60000))
                            .toISOString().slice(0, 10);

                        const now = new Date();

                        const start = new Date();
                        start.setDate(now.getDate() - 29);

                        $('#start_date').val(toISO(start));
                        $('#end_date').val(toISO(now));

                    })();


                    $('#start_date,#end_date,#branch_id').on('change', function() {
                        table.ajax.reload();
                    });

                },


                drawCallback: function(settings) {

                    const json = settings.json || {};

                    if (json.totals) {
                        $('#ft_qty').text(json.totals.qty || '0.00');
                    }
                },

                columnDefs: [{
                    targets: '_all',
                    defaultContent: ''
                }]
            });
        });


        /* ---------- Logo for PDF ---------- */

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
