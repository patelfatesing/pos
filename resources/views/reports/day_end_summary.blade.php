{{-- resources/views/reports/day_end_summary.blade.php --}}
@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <div class="content-page">
        <div class="container-fluid">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h4 class="mb-0">Day End Sales Summary (Consolidated — Branch-wise)</h4>
                </div>
                <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
            </div>


            <div class="table-responsive rounded mt-2">
                <table class="table table-striped table-bordered nowrap" id="day_end_table" style="width:100%;">
                    <thead class="bg-white">
                        <tr class="ligth ligth-data">
                            <th>Sr No</th>
                            <th>Date</th>
                            <th>Branch</th> {{-- NEW --}}
                            <th>Opening Cash</th>
                            <th>Closing Cash</th>
                            <th>Total Sales</th>
                            <th>Sales Items</th>
                            <th>Stock Difference</th>
                            <th>Case Dispense</th>
                            <th style="display:none;">RowType</th> {{-- hidden sort key --}}

                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Overall Totals:</th>
                            <th id="ft_opening">0.00</th>
                            <th id="ft_closing">0.00</th>
                            <th id="ft_sales">0.00</th>
                            <th id="ft_items">0.00</th>
                            <th id="ft_diff">0.00</th>
                            <th id="ft_case">0.00</th>
                            <th style="display:none;"></th>

                        </tr>
                    </tfoot>
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

            const filtersHtml = `
                <div class="filters">
                <select id="period" class="form-control form-control-sm">
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly" selected>Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
                </div>
            `;

            const table = $('#day_end_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },
                ajax: {
                    url: "{{ route('reports.day_end.data') }}",
                    type: 'POST',
                    data: function(d) {
                        d.period = $('#period').val();
                    }
                },
                dom: "<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'Bf l>>t<'row'<'col-md-6'i><'col-md-6'p>>",

                columns: [{
                        data: 'sr_no',
                        orderable: false,
                        searchable: false
                    }, // 0
                    {
                        data: 'date'
                    }, // 1
                    {
                        data: 'branch_name'
                    }, // 2
                    {
                        data: 'opening_cash'
                    }, // 3
                    {
                        data: 'closing_cash'
                    }, // 4
                    {
                        data: 'total_sales'
                    }, // 5
                    {
                        data: 'sales_items'
                    }, // 6
                    {
                        data: 'stock_diff'
                    }, // 7
                    {
                        data: 'case_dispense'
                    }, // 8
                    {
                        data: 'row_type',
                        visible: false,
                        searchable: false
                    }
                ],
                // Keep totals under each day: sort by date desc, then row_type asc (normal rows first, then day total), then branch asc
                order: [
                    [1, 'desc'],
                    [9, 'asc'],
                    [2, 'asc']
                ],
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
                            title: 'Day End Sales Summary Report',
                            filename: 'day_end_summary_report',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf-o"></i> PDF',
                            filename: 'day_end_summary_report',
                            orientation: 'landscape',
                            pageSize: 'A4',

                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                            },

                            customize: function(doc) {

                                // REMOVE default title
                                doc.content.splice(0, 1);

                                // CENTER TABLE
                                doc.content[0].alignment = 'center';

                                // MAKE TABLE WIDTH FULL PAGE
                                doc.content[0].table.widths = ['auto', '*', '*', '*', '*',
                                    '*', '*', '*', '*'
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
                                }

                                // ADD FOOTER TOTALS TO PDF
                                var footer = [{
                                        text: 'Overall Totals',
                                        colSpan: 3,
                                        alignment: 'right',
                                        bold: true
                                    }, {}, {},
                                    {
                                        text: $('#ft_opening').text(),
                                        alignment: 'right',
                                        bold: true
                                    },
                                    {
                                        text: $('#ft_closing').text(),
                                        alignment: 'right',
                                        bold: true
                                    },
                                    {
                                        text: $('#ft_sales').text(),
                                        alignment: 'right',
                                        bold: true
                                    },
                                    {
                                        text: $('#ft_items').text(),
                                        alignment: 'right',
                                        bold: true
                                    },
                                    {
                                        text: $('#ft_diff').text(),
                                        alignment: 'right',
                                        bold: true
                                    },
                                    {
                                        text: $('#ft_case').text(),
                                        alignment: 'right',
                                        bold: true
                                    }
                                ];

                                doc.content[0].table.body.push(footer);

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
                                            text: 'Day End Sales Summary Report',
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
                    $(filtersHtml).insertAfter('.dt-buttons');
                    $('#period').on('change', function() {
                        table.ajax.reload();
                    });
                    $('.dataTables_filter input').attr("placeholder", "Search List...");
                },
                drawCallback: function(settings) {
                    const json = settings.json || {};
                    if (json.totals) {
                        $('#ft_opening').text(json.totals.opening_cash || '0.00');
                        $('#ft_closing').text(json.totals.closing_cash || '0.00');
                        $('#ft_sales').text(json.totals.total_sales || '0.00');
                        $('#ft_items').text(json.totals.sales_items || '0.00');
                        $('#ft_diff').text(json.totals.stock_diff || '0.00');
                        $('#ft_case').text(json.totals.case_dispense || '0.00');
                    }
                },
                rowCallback: function(row, data) {
                    // Style total rows
                    if (data.row_type === 1 || data.row_type === '1') {
                        $(row).addClass('day-total');
                    } else if (data.row_type === 2 || data.row_type === '2') {
                        $(row).addClass('overall-total');
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
