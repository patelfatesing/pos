{{-- resources/views/reports/day_end_summary.blade.php --}}
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

        /* Optional: highlight total rows */
        tr.day-total {
            background: #fff8e1;
            font-weight: 600;
        }

        tr.overall-total {
            background: #e8f5e9;
            font-weight: 700;
        }
    </style>
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h4 class="mb-0">Day End Sales Summary (Consolidated — Branch-wise)</h4>
                </div>

                <div class="table-responsive rounded">
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
                ajax: {
                    url: "{{ route('reports.day_end.data') }}",
                    type: 'POST',
                    data: function(d) {
                        d.period = $('#period').val();
                    }
                },
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
                dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
                buttons: [{
                        extend: 'excelHtml5',
                        className: 'btn btn-outline-success btn-sm me-2',
                        title: 'Day End Sales Summary (Branch-wise)',
                        filename: 'day_end_sales_summary_branch_wise',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-outline-danger btn-sm',
                        title: 'Day End Sales Summary (Branch-wise)',
                        filename: 'day_end_sales_summary_branch_wise',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                initComplete: function() {
                    $(filtersHtml).insertAfter('.dt-buttons');
                    $('#period').on('change', function() {
                        table.ajax.reload();
                    });
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
    </script>
@endsection
