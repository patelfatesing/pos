{{-- resources/views/reports/purchase_by_product.blade.php --}}
@extends('layouts.backend.datatable_layouts')
<style>
    .custom-toolbar-row {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
        flex-wrap: nowrap;
        margin-bottom: 10px;
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
                        <h4 class="mb-0">Purchase by Product Report</h4>
                    </div>
                    <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
                </div>

                <div class="table-responsive rounded mt-2">
                    <table class="table table-striped table-bordered nowrap" id="pbp_table" style="width:100%;">
                        <thead class="bg-white">
                            <tr class="ligth ligth-data">
                                <th>Sr</th>
                                <th>Supplier Name</th>
                                <th>Product Name</th>
                                <th>QTY</th>
                                <th>Retail Price</th>
                                <th>Total Cost</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Totals:</th>
                                <th id="ft_qty">0</th>
                                <th></th>
                                <th id="ft_cost">0.00</th>
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
        var totalQty = 0;
        var totalCost = 0;

        $(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


            const filtersHtml = `
<div class="filters one-line">

<select id="vendor_id" class="form-control form-control-sm">
<option value="">All Vendors</option>
@foreach ($vendors as $v)
<option value="{{ $v->id }}">{{ $v->name }}</option>
@endforeach
</select>

<input type="date" id="start_date" class="form-control form-control-sm">

<input type="date" id="end_date" class="form-control form-control-sm">

</div>
`;

            const table = $('#pbp_table').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,

                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },

                ajax: {
                    url: "{{ route('reports.purchase_by_product.data') }}",
                    type: 'POST',
                    data: function(d) {

                        d.vendor_id = $('#vendor_id').val();
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
                        data: 'vendor_name'
                    },
                    {
                        data: 'product_name'
                    },
                    {
                        data: 'qty'
                    },
                    {
                        data: 'retail_price'
                    },
                    {
                        data: 'total_cost'
                    }
                ],

                order: [
                    [3, 'desc']
                ],

                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,

                buttons: [{
                    extend: 'collection',
                    text: '<i class="fa fa-download"></i> Export',
                    className: 'btn btn-info btn-sm',
                    autoClose: true,

                    buttons: [{
                            extend: 'excelHtml5',
                            text: '<i class="fa fa-file-excel-o"></i> Excel',
                            title: 'Purchase by Product',
                            filename: 'purchase_by_product',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },

                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf-o"></i> PDF',
                            filename: 'purchase_by_product',
                            orientation: 'landscape',
                            pageSize: 'A4',

                            exportOptions: {
                                columns: ':visible'
                            },

                            customize: function(doc) {

                                doc.content.splice(0, 1);

                                var table = doc.content[0].table;
                                var body = table.body;

                                var qtyTotal = 0;
                                var costTotal = 0;

                                for (var i = 1; i < body.length; i++) {

                                    qtyTotal += parseFloat(body[i][3].text || 0);
                                    costTotal += parseFloat(body[i][5].text || 0);

                                }

                                // ADD TOTAL ROW IN PDF TABLE
                                body.push([{
                                        text: '',
                                        border: [false, false, false, false]
                                    },
                                    {
                                        text: '',
                                        border: [false, false, false, false]
                                    },
                                    {
                                        text: 'TOTAL',
                                        bold: true,
                                        alignment: 'right'
                                    },
                                    {
                                        text: qtyTotal,
                                        bold: true,
                                        alignment: 'center'
                                    },
                                    {
                                        text: '',
                                        border: [false, false, false, false]
                                    },
                                    {
                                        text: costTotal.toFixed(2),
                                        bold: true,
                                        alignment: 'right'
                                    }
                                ]);

                            }
                        }
                    ]
                }],
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
                    $('#vendor_id,#start_date,#end_date').on('change', function() {
                        table.ajax.reload();
                    });
                },
                drawCallback: function(settings) {

                    const json = settings.json || {};

                    if (json.totals) {

                        totalQty = json.totals.qty || 0;
                        totalCost = json.totals.cost || '0.00';

                        $('#ft_qty').text(totalQty);
                        $('#ft_cost').text(totalCost);
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
