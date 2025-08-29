{{-- resources/views/reports/stock_transfer.blade.php --}}
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

        /* shrink to fit */
        #mode {
            flex: 0 1 190px;
            min-width: 150px;
        }

        #from_branch_id,
        #to_branch_id {
            flex: 0 1 210px;
            min-width: 160px;
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
                    <h4 class="mb-0">Stock Transfer / Request Report</h4>
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
                    <label class="mb-0">Type</label>
                    <select id="mode" class="form-control form-control-sm">
                    <option value="admin" selected>Admin Transfer</option>
                    <option value="request">Requested Store</option>
                    </select>

                    <label class="mb-0">From</label>
                    <select id="from_branch_id" class="form-control form-control-sm">
                    <option value="">All</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                    </select>

                    <label class="mb-0">To</label>
                    <select id="to_branch_id" class="form-control form-control-sm">
                    <option value="">All</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                    </select>

                    <input type="date" id="start_date" class="form-control form-control-sm" placeholder="Start date">
                    <input type="date" id="end_date"   class="form-control form-control-sm" placeholder="End date">
                </div>
                `;

            const table = $('#stock_transfer_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
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
                ], // date desc
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
                buttons: [{
                        extend: 'excelHtml5',
                        className: 'btn btn-outline-success btn-sm me-2',
                        title: 'Stock Transfer Request Report',
                        filename: 'stock_transfer_request_report',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-outline-danger btn-sm',
                        title: 'Stock Transfer Request Report',
                        filename: 'stock_transfer_request_report',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                initComplete: function() {
                    $(filtersHtml).insertAfter('.dt-buttons');

                    // Prefill last 30 days
                    (function presetLast30Days() {
                        const toISO = (d) => new Date(d.getTime() - (d.getTimezoneOffset() * 60000))
                            .toISOString().slice(0, 10);
                        const now = new Date();
                        const start = new Date();
                        start.setDate(now.getDate() - 29);
                        if (!$('#start_date').val()) $('#start_date').val(toISO(start));
                        if (!$('#end_date').val()) $('#end_date').val(toISO(now));
                    })();

                    // Prevent same From & To on client side
                    function checkFromTo() {
                        const f = $('#from_branch_id').val();
                        const t = $('#to_branch_id').val();
                        if (f && t && f === t) {
                            alert('From and To cannot be the same branch.');
                            $('#to_branch_id').val(''); // reset "To"
                        }
                    }
                    $('#from_branch_id, #to_branch_id').on('change', checkFromTo);

                    // Reload on filter changes
                    $('#mode, #from_branch_id, #to_branch_id, #start_date, #end_date').on('change',
                        function() {
                            table.ajax.reload();
                        });
                },
                columnDefs: [{
                    targets: '_all',
                    defaultContent: ''
                }]
            });
        });
    </script>
@endsection
