{{-- resources/views/reports/stock_transfer.blade.php --}}
@extends('layouts.backend.datatable_layouts')

@section('styles')
    <style>
        /* Toolbar layout */
        .dataTables_wrapper .row {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 6px;
            flex-wrap: nowrap;
        }

        /* Filters container */
        .filters-container {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Export + Search + Length */
        .dt-toolbar {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Filters */
        .filters-container select,
        .filters-container input {
            height: 34px;
            border-radius: 6px;
        }

        /* Reduce field width */
        #mode {
            min-width: 120px;
        }

        #from_branch_id,
        #to_branch_id {
            min-width: 130px;
        }

        #start_date,
        #end_date {
            min-width: 110px;
        }

        /* Search box */
        .dataTables_filter input {
            width: 160px;
        }

        /* Length dropdown */
        .dataTables_length select {
            width: 60px;
        }

        #from_branch_id {
            width: 125px !important;
        }
    </style>
@endsection

@section('page-content')
    <div class="content-page">
        <div class="container-fluid">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h4 class="mb-0">Stock Transfer / Request Report</h4>
                </div>
                <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
            </div>

            <div class="table-responsive rounded">
                <table class="table table-striped table-bordered nowrap" id="stock_transfer_table" style="width:100%;">
                    <thead class="bg-white">
                        <tr class="ligth ligth-data">
                            <th>Sr No</th>
                            <th>Where</th>
                            <th>From</th>
                            <th>QTY</th>
                            <th>Status</th>
                            <th>Transfer Date</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    {{-- ✅ Footer to display totals --}}
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total QTY</th>
                            <th id="qty_total_cell">0</th> {{-- will show "pageTotal (All: X)" --}}
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


            /* ---------- Filters ---------- */

            const filtersHtml = `
                <div class="d-flex align-items-center" style="gap:4px;">

                <select id="mode" class="form-control form-control-sm" style="width:125px">
                <option value="admin" selected>Admin Transfer</option>
                <option value="request">Requested Store</option>
                </select>

                <select id="from_branch_id" class="form-control form-control-sm" style="width:160px">
                <option value="">From Branch</option>
                @foreach ($branches as $b)
                <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
                </select>

                <select id="to_branch_id" class="form-control form-control-sm" style="width:160px">
                <option value="">To Branch</option>
                @foreach ($branches as $b)
                <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
                </select>

                <input type="date" id="start_date" class="form-control form-control-sm" style="width:140px">

                <input type="date" id="end_date" class="form-control form-control-sm" style="width:140px">

                </div>
                `;

            /* ---------- DataTable ---------- */

            let serverTotalQty = null;

            const table = $('#stock_transfer_table').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,

                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },

                ajax: {
                    url: "{{ route('reports.stock_transfer.data') }}",
                    type: 'POST',
                    data: function(d) {

                        d.mode = $('#mode').val();
                        d.from_branch_id = $('#from_branch_id').val();
                        d.to_branch_id = $('#to_branch_id').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();

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
                        data: 'where'
                    },
                    {
                        data: 'from'
                    },
                    {
                        data: 'qty'
                    },
                    {
                        data: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'transfer_date'
                    }
                ],

                order: [
                    [5, 'desc']
                ],

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
                                title: 'Stock Transfer Request Report',
                                filename: 'stock_transfer_request_report',
                                footer: true,
                                exportOptions: {
                                    columns: ':visible'
                                }
                            },

                            {
                                extend: 'pdfHtml5',
                                text: '<i class="fa fa-file-pdf-o"></i> PDF',
                                filename: 'stock_transfer_request_report',
                                orientation: 'landscape',
                                pageSize: 'A4',
                                footer: true,
                                exportOptions: {
                                    columns: ':visible'
                                }
                            }

                        ]

                    }

                ],


                /* ---------- Init ---------- */

                initComplete: function() {

                    $('.filters-container').html(filtersHtml);


                    /* last 30 days */

                    (function() {

                        const toISO = d => new Date(d.getTime() - (d.getTimezoneOffset() * 60000))
                            .toISOString().slice(0, 10);

                        const now = new Date();

                        const start = new Date();
                        start.setDate(now.getDate() - 29);

                        $('#start_date').val(toISO(start));
                        $('#end_date').val(toISO(now));

                    })();


                    /* prevent same branch */

                    function checkFromTo() {

                        const f = $('#from_branch_id').val();
                        const t = $('#to_branch_id').val();

                        if (f && t && f === t) {

                            alert('From and To cannot be the same branch.');
                            $('#to_branch_id').val('');

                        }

                    }

                    $('#from_branch_id,#to_branch_id').on('change', checkFromTo);


                    /* reload filters */

                    $('#mode,#from_branch_id,#to_branch_id,#start_date,#end_date')
                        .on('change', function() {

                            table.ajax.reload();

                        });

                },


                /* ---------- Footer totals ---------- */

                footerCallback: function(row, data, start, end, display) {

                    const api = this.api();

                    const pageTotal = api
                        .column(3, {
                            page: 'current'
                        })
                        .data()
                        .reduce((sum, v) => sum + (parseFloat(String(v).replace(/[, ]/g, '')) || 0), 0);

                    const grand = (serverTotalQty !== null) ? serverTotalQty : pageTotal;

                    $(api.column(3).footer()).html(
                        `${pageTotal.toFixed(0)} <small class="text-muted">(All: ${Number(grand).toFixed(0)})</small>`
                    );

                },


                columnDefs: [{
                    targets: '_all',
                    defaultContent: ''
                }]

            });

        });
    </script>
@endsection
