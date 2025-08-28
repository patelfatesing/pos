{{-- resources/views/reports/purchase_by_product.blade.php --}}
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
                    <h4 class="mb-0">Purchase by Product Report</h4>
                </div>

                <div class="table-responsive rounded">
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
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const filtersHtml = `
    <div class="filters">
      <label class="mb-0">Vendor</label>
      <select id="vendor_id" class="form-control form-control-sm">
        <option value="">All</option>
        @foreach ($vendors as $v)
          <option value="{{ $v->id }}">{{ $v->name }}</option>
        @endforeach
      </select>

      <input type="date" id="start_date" class="form-control form-control-sm" placeholder="Start date">
      <input type="date" id="end_date" class="form-control form-control-sm" placeholder="End date">
    </div>
  `;

            const table = $('#pbp_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('reports.purchase_by_product.data') }}",
                    type: 'POST',
                    data: function(d) {
                        d.vendor_id = $('#vendor_id').val();
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
                ], // qty desc
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
                buttons: [{
                        extend: 'excelHtml5',
                        className: 'btn btn-outline-success btn-sm me-2',
                        title: 'Purchase by Product',
                        filename: 'purchase_by_product',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-outline-danger btn-sm',
                        title: 'Purchase by Product',
                        filename: 'purchase_by_product',
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
                        const toISO = (d) => new Date(d.getTime() - (d.getTimezoneOffset() * 60000))
                            .toISOString().slice(0, 10);
                        const now = new Date();
                        const start = new Date();
                        start.setDate(now.getDate() - 29);
                        if (!$('#start_date').val()) $('#start_date').val(toISO(start));
                        if (!$('#end_date').val()) $('#end_date').val(toISO(now));
                    })();

                    $('#vendor_id, #start_date, #end_date').on('change', function() {
                        table.ajax.reload();
                    });
                },
                drawCallback: function(settings) {
                    const json = settings.json || {};
                    if (json.totals) {
                        $('#ft_qty').text(json.totals.qty || '0');
                        $('#ft_cost').text(json.totals.cost || '0.00');
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
