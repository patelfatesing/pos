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
                            <h4 class="mb-0">Customer Outstanding Balance Report</h4>
                        </div>
                        <div class="summary-badges">
                            <span class="badge bg-dark">Total Outstanding: <span id="sum_closing">0.00</span></span>
                            <span class="badge bg-secondary">0–30: <span id="sum_0_30">0.00</span></span>
                            <span class="badge bg-secondary">31–60: <span id="sum_31_60">0.00</span></span>
                            <span class="badge bg-secondary">61–90: <span id="sum_61_90">0.00</span></span>
                            <span class="badge bg-secondary">90+: <span id="sum_90p">0.00</span></span>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row g-2 mb-2">
                    <div class="col-md-2">
                        <select id="branch_id" class="form-control">
                            <option value="">All Branches</option>
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="party_user_id" class="form-control">
                            <option value="">All Customers</option>
                            @foreach ($parties as $p)
                                <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="start_date" class="form-control w-120" />
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="end_date" class="form-control w-120" />
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" id="min_outstanding" class="form-control"
                            placeholder="Outstanding ≥" />
                    </div>
                </div>

                <div class="table-responsive rounded">
                    <table class="table table-striped table-bordered nowrap" id="cust_outstanding_table"
                        style="width:100%;">
                        <thead class="bg-white">
                            <tr class="ligth ligth-data">
                                <th>Sr No</th>
                                <th>Customer</th>
                                <th>Branch</th>
                                <th>Mobile</th>
                                <th>Opening</th>
                                <th>Period Credit</th>
                                <th>Period Debit</th>
                                <th>Closing</th>
                                <th>0–30</th>
                                <th>31–60</th>
                                <th>61–90</th>
                                <th>90+</th>
                                <th>Last Tx</th>
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

            const table = $('#cust_outstanding_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('reports.customer_outstanding.get_data') }}",
                    type: 'POST',
                    data: function(d) {
                        d.branch_id = $('#branch_id').val();
                        d.party_user_id = $('#party_user_id').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.min_outstanding = $('#min_outstanding').val();
                    }
                },
                columns: [{
                        data: 'sr_no',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'party_name'
                    },
                    {
                        data: 'branch_name'
                    },
                    {
                        data: 'mobile'
                    },
                    {
                        data: 'opening'
                    },
                    {
                        data: 'period_credit'
                    },
                    {
                        data: 'period_debit'
                    },
                    {
                        data: 'closing'
                    },
                    {
                        data: 'age_0_30'
                    },
                    {
                        data: 'age_31_60'
                    },
                    {
                        data: 'age_61_90'
                    },
                    {
                        data: 'age_90_plus'
                    },
                    {
                        data: 'last_tx_date'
                    }
                ],
                order: [
                    [7, 'desc']
                ], // Closing outstanding desc
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
                buttons: [{
                        extend: 'excelHtml5',
                        className: 'btn btn-outline-success btn-sm me-2',
                        title: 'Customer Outstanding Balance',
                        filename: 'customer_outstanding',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-outline-danger btn-sm',
                        title: 'Customer Outstanding Balance',
                        filename: 'customer_outstanding',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                initComplete: function() {
                    $('#branch_id, #party_user_id, #start_date, #end_date, #min_outstanding').on(
                        'change keyup',
                        function() {
                            table.ajax.reload();
                        });
                }
            });

            // Update totals badges after each fetch
            $('#cust_outstanding_table').on('xhr.dt', function(e, settings, json, xhr) {
                if (json && json.totals) {
                    $('#sum_closing').text((json.totals.closing ?? 0).toFixed(2));
                    $('#sum_0_30').text((json.totals.age_0_30 ?? 0).toFixed(2));
                    $('#sum_31_60').text((json.totals.age_31_60 ?? 0).toFixed(2));
                    $('#sum_61_90').text((json.totals.age_61_90 ?? 0).toFixed(2));
                    $('#sum_90p').text((json.totals.age_90p ?? 0).toFixed(2));
                }
            });
        });
    </script>
@endsection
