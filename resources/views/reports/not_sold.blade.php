{{-- resources/views/reports/not_sold.blade.php --}}
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
                        <h4 class="mb-0">Not Sold Product Report</h4>
                    </div>
                    <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
                </div>


                <div class="table-responsive rounded mt-2">

                    <table class="table table-striped table-bordered nowrap" id="not_sold_table">

                        <thead class="bg-white">
                            <tr class="ligth ligth-data">
                                <th>Sr No</th>
                                <th>Product</th>
                                <th>QTY</th>
                                <th>Branch</th>
                            </tr>
                        </thead>

                        <tbody></tbody>

                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-end">Total QTY:</th>
                                <th id="ft_qty">0.00</th>
                                <th></th>
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
        $(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


            /* ---------- Filters ---------- */

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

            const table = $('#not_sold_table').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,

                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },

                ajax: {
                    url: "{{ route('reports.not_sold.data') }}",
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
                    },
                    {
                        data: 'branch_name'
                    }
                ],


                order: [
                    [3, 'asc'],
                    [1, 'asc']
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
                                title: 'Not Sold Product Report',
                                filename: 'not_sold_products',
                                exportOptions: {
                                    columns: ':visible'
                                },
                                footer: true
                            },
                            {
                                extend: 'pdfHtml5',
                                text: '<i class="fa fa-file-pdf-o"></i> PDF',
                                filename: 'not_sold_products',
                                orientation: 'landscape',
                                pageSize: 'A4',
                                exportOptions: {
                                    columns: ':visible'
                                },
                                footer: true
                            }
                        ]
                    }
                ],
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
    </script>
@endsection
