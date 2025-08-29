{{-- resources/views/reports/closing_summary.blade.php --}}
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
            align-items: center;
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

        .filters.one-line {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: nowrap;
            overflow: hidden;
            white-space: nowrap;
            /* one row, no scroll */
        }

        .filters.one-line label {
            margin-bottom: 0;
            white-space: nowrap;
            font-size: .85rem;
            color: #6b7280;
        }

        .filters.one-line .form-control {
            flex: 0 1 160px;
            min-width: 120px;
        }

        /* shrink instead of wrapping */
        #branch_id {
            flex: 0 1 240px;
            min-width: 170px;
            text-overflow: ellipsis;
            overflow: hidden;
        }

        #start_date,
        #end_date {
            flex: 0 1 140px;
            min-width: 110px;
        }
    </style>
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h4 class="mb-0">Closing Summary Report</h4>
                </div>

                <div class="table-responsive rounded">
                    <table class="table table-striped table-bordered nowrap" id="closing_summary_table" style="width:100%;">
                        <thead class="bg-white">
                            <tr class="ligth ligth-data">
                                <th>Sr No</th>
                                <th>Branch Name</th>
                                <th>Date</th>
                                <th>Closing Stock</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total Closing Stock:</th>
                                <th id="ft_closing_stock">0</th>
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

            const filtersHtml = `
                <div class="filters one-line">
                    <label class="mb-0">Branch</label>
                    <select id="branch_id" class="form-control form-control-sm">
                    <option value="">All</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                    </select>

                    <input type="date" id="start_date" class="form-control form-control-sm" placeholder="Start date">
                    <input type="date" id="end_date"   class="form-control form-control-sm" placeholder="End date">
                </div>
                `;

            const table = $('#closing_summary_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('reports.closing_summary.data') }}",
                    type: 'POST',
                    data: function(d) {
                        d.branch_id = $('#branch_id').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                columns: [{
                        data: 'sr_no',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'branch_name'
                    },
                    {
                        data: 'date'
                    },
                    {
                        data: 'closing_stock'
                    }
                ],
                order: [
                    [2, 'desc'],
                    [1, 'asc']
                ], // date desc, then branch
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
                buttons: [{
                        extend: 'excelHtml5',
                        className: 'btn btn-outline-success btn-sm me-2',
                        title: 'Closing Summary Report',
                        filename: 'closing_summary_report',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-outline-danger btn-sm',
                        title: 'Closing Summary Report',
                        filename: 'closing_summary_report',
                        orientation: 'portrait',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                initComplete: function() {
                    $(filtersHtml).insertAfter('.dt-buttons');

                    // Prefill last 30 days (today inclusive)
                    (function presetLast30Days() {
                        const toISO = (d) => new Date(d.getTime() - (d.getTimezoneOffset() * 60000))
                            .toISOString().slice(0, 10);
                        const now = new Date();
                        const start = new Date();
                        start.setDate(now.getDate() - 29);
                        if (!$('#start_date').val()) $('#start_date').val(toISO(start));
                        if (!$('#end_date').val()) $('#end_date').val(toISO(now));
                    })();

                    $('#branch_id, #start_date, #end_date').on('change', function() {
                        table.ajax.reload();
                    });
                },
                drawCallback: function(settings) {
                    const json = settings.json || {};
                    if (json.totals) {
                        $('#ft_closing_stock').text(json.totals.closing_stock || '0');
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
