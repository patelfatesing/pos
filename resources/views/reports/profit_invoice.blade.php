{{-- resources/views/reports/profit_invoice.blade.php --}}
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
</style>
@endsection

@section('page-content')
<div class="wrapper">
    <div class="content-page">
        <div class="container-fluid">

            <div class="d-flex align-items-center justify-content-between mb-2">
                <h4 class="mb-0">Profit on Sales Invoice Report</h4>
            </div>

            <div class="table-responsive rounded">
                <table class="table table-striped table-bordered nowrap" id="profit_invoice_table" style="width:100%;">
                    <thead class="bg-white">
                        <tr class="ligth ligth-data">
                            <th>Sr No</th>
                            <th>Invoice</th>
                            <th>Branch</th>
                            <th>Sub Total</th>
                            <th>Commission</th>
                            <th>Party</th>
                            <th>Payment Mode</th>
                            <th>Profit</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Totals:</th>
                            <th id="ft_sub_total">0.00</th>
                            <th id="ft_commission">0.00</th>
                            <th id="ft_party">0.00</th>
                            <th></th>
                            <th id="ft_profit">0.00</th>
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
      <label class="mb-0">Branch</label>
      <select id="branch_id" class="form-control form-control-sm">
        <option value="">All</option>
        @foreach($branches as $b)
          <option value="{{ $b->id }}">{{ $b->name }}</option>
        @endforeach
      </select>

      <input type="date" id="start_date" class="form-control form-control-sm" placeholder="Start date">
      <input type="date" id="end_date" class="form-control form-control-sm" placeholder="End date">
    </div>
  `;

        const table = $('#profit_invoice_table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('reports.profit_invoice.data') }}",
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
                    data: 'invoice',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'branch_name'
                },
                {
                    data: 'sub_total'
                },
                {
                    data: 'commission_amount'
                },
                {
                    data: 'party_amount'
                },
                {
                    data: 'payment_mode'
                },
                {
                    data: 'profit'
                }
            ],
            order: [
                [2, 'asc'],
                [7, 'desc']
            ], // branch, then profit desc (change if you want date)
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            pageLength: 10,
            dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
            buttons: [{
                    extend: 'excelHtml5',
                    className: 'btn btn-outline-success btn-sm me-2',
                    title: 'Profit on Sales Invoice',
                    filename: 'profit_on_sales_invoice',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    className: 'btn btn-outline-danger btn-sm',
                    title: 'Profit on Sales Invoice',
                    filename: 'profit_on_sales_invoice',
                    orientation: 'landscape',
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
                    const toISO = (d) => new Date(d.getTime() - (d.getTimezoneOffset() * 60000)).toISOString().slice(0, 10);
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
                    $('#ft_sub_total').text(json.totals.sub_total || '0.00');
                    $('#ft_commission').text(json.totals.commission || '0.00');
                    $('#ft_party').text(json.totals.party || '0.00');
                    $('#ft_profit').text(json.totals.profit || '0.00');
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