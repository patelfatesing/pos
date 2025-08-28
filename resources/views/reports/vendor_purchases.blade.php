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

        .summary-badges {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            margin-bottom: .5rem
        }

        .w-120 {
            width: 120px
        }
    </style>
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <div class="row align-items-center mb-2">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                            <h4 class="mb-0">Vendor Delivery Invoice Report</h4>
                        </div>
                        <div class="summary-badges">
                            <span class="badge bg-dark">Total Qty: <span id="sum_qty">0</span></span>
                            <span class="badge bg-dark">Items Amount: <span id="sum_items_amount">0.00</span></span>
                            <span class="badge bg-dark">Grand Total: <span id="sum_grand_total">0.00</span></span>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row g-2 mb-2">
                    <div class="col-md-3">
                        <select id="vendor_id" class="form-control">
                            <option value="">All Vendors</option>
                            @foreach ($vendors as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="start_date" class="form-control w-120" />
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="end_date" class="form-control w-120" />
                    </div>

                </div>

                <div class="table-responsive rounded">
                    <table class="table table-striped table-bordered nowrap" id="vendor_purchases_table"
                        style="width:100%;">
                        <thead class="bg-white">
                            <tr class="ligth ligth-data">
                                <th>Sr No</th>
                                <th>Date</th>
                                <th>Bill No</th>
                                <th>Vendor</th>
                                <th>Items Qty</th>
                                <th>Items Amount</th>
                                <th>Excise Fee</th>
                                <th>VAT</th>
                                <th>TCS</th>
                                <th>Other Charges</th>
                                <th>Grand Total</th>
                                <th>Status</th>
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

            const table = $('#vendor_purchases_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('reports.vendor_purchases.get_data') }}",
                    type: 'POST',
                    data: function(d) {
                        d.vendor_id = $('#vendor_id').val();
                        d.status = $('#status').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.min_total = $('#min_total').val();
                        d.max_total = $('#max_total').val();
                    }
                },
                columns: [{
                        data: 'sr_no',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'date'
                    },
                    {
                        data: 'bill_no'
                    }, // badge/link
                    {
                        data: 'vendor_name'
                    },
                    {
                        data: 'items_qty'
                    },
                    {
                        data: 'items_amount'
                    },
                    {
                        data: 'excise_fee'
                    },
                    {
                        data: 'vat'
                    },
                    {
                        data: 'tcs'
                    },
                    {
                        data: 'other_charges'
                    },
                    {
                        data: 'grand_total'
                    },
                    {
                        data: 'status'
                    }
                ],
                order: [
                    [1, 'desc']
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
                        title: 'Vendor Purchase Report',
                        filename: 'vendor_purchase_report',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-outline-danger btn-sm',
                        title: 'Vendor Purchase Report',
                        filename: 'vendor_purchase_report',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                initComplete: function() {
                    $('#vendor_id, #status, #start_date, #end_date, #min_total, #max_total').on(
                        'change keyup',
                        function() {
                            table.ajax.reload();
                        });
                }
            });

            // Update totals badges after each fetch
            $('#vendor_purchases_table').on('xhr.dt', function(e, settings, json, xhr) {
                if (json && json.totals) {
                    $('#sum_qty').text(json.totals.qty ?? 0);
                    $('#sum_items_amount').text((json.totals.items_total ?? 0).toFixed(2));
                    $('#sum_grand_total').text((json.totals.grand_total ?? 0).toFixed(2));
                }
            });
        });
    </script>
@endsection
