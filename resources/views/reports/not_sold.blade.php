{{-- resources/views/reports/not_sold.blade.php --}}
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
    </style>
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h4 class="mb-0">Not Sold Product Report</h4>
                </div>

                <div class="table-responsive rounded">
                    <table class="table table-striped table-bordered nowrap" id="not_sold_table" style="width:100%;">
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

            const filtersHtml = `
    <div class="filters">
      <input type="date" id="start_date" class="form-control form-control-sm" placeholder="Start date">
      <input type="date" id="end_date" class="form-control form-control-sm" placeholder="End date">

      <select id="branch_id" class="form-control form-control-sm">
        <option value="">All Branches</option>
        @foreach ($branches as $b)
          <option value="{{ $b->id }}">{{ $b->name }}</option>
        @endforeach
      </select>
    </div>
  `;

            const table = $('#not_sold_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('reports.not_sold.data') }}",
                    type: 'POST',
                    data: function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.branch_id = $('#branch_id').val(); // empty => all branches
                    }
                },
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
                    }, // always 0.00 by design
                    {
                        data: 'branch_name'
                    }
                ],
                order: [
                    [3, 'asc'],
                    [1, 'asc']
                ], // Branch asc, Product asc
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
                buttons: [{
                        extend: 'excelHtml5',
                        className: 'btn btn-outline-success btn-sm me-2',
                        title: 'Not Sold Product Report',
                        filename: 'not_sold_product_report',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-outline-danger btn-sm',
                        title: 'Not Sold Product Report',
                        filename: 'not_sold_product_report',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                initComplete: function() {
                    $(filtersHtml).insertAfter('.dt-buttons');

                    // Prefill date inputs: last 30 days (today inclusive)
                    (function presetLast30Days() {
                        const toISO = (d) => new Date(d.getTime() - (d.getTimezoneOffset() * 60000))
                            .toISOString().slice(0, 10);
                        const now = new Date();
                        const start = new Date();
                        start.setDate(now.getDate() - 29);
                        if (!$('#start_date').val()) $('#start_date').val(toISO(start));
                        if (!$('#end_date').val()) $('#end_date').val(toISO(now));
                    })();

                    // Reload on filter changes
                    $('#start_date, #end_date, #branch_id').on('change', function() {
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
