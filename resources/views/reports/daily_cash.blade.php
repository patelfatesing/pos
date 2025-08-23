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

        .w-140 {
            width: 140px
        }
    </style>
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <div class="row align-items-center mb-3">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Daily Cash Collection Report</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row g-2 mb-2">
                    <div class="col-md-3">
                        <select id="branch_id" class="form-control">
                            <option value="">All Branches</option>
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" id="start_date" class="form-control w-140" />
                    </div>
                    <div class="col-md-3">
                        <input type="date" id="end_date" class="form-control w-140" />
                    </div>
                    <div class="col-md-3">
                        <select id="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="closing">Closing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive rounded">
                    <table class="table table-striped table-bordered nowrap" id="daily_cash_table" style="width:100%;">
                        <thead class="bg-white">
                            <tr class="ligth ligth-data">
                                <th>Sr No</th>
                                <th>Date</th>
                                <th>Branch</th>
                                <th>Shifts</th>
                                <th>Opening Cash</th>
                                <th>Cash Added</th>
                                <th>UPI Payment</th>
                                <th>Withdrawal</th>
                                <th>Closing Cash</th>
                                <th>Discrepancy</th>
                                <th>Deshi Sales</th>
                                <th>Beer Sales</th>
                                <th>English Sales</th>
                                <th>Discount</th>
                                <th>Total Sales</th>
                                <th>Actions</th>
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

            const table = $('#daily_cash_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('reports.daily_cash.get_data') }}",
                    type: 'POST',
                    data: function(d) {
                        d.branch_id = $('#branch_id').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.status = $('#status').val();
                    }
                },
                columns: [{
                        data: 'sr_no',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'work_date'
                    },
                    {
                        data: 'branch_name'
                    },
                    {
                        data: 'shifts_count'
                    },
                    {
                        data: 'opening_cash'
                    },
                    {
                        data: 'cash_added'
                    },
                    {
                        data: 'upi_payment'
                    },
                    {
                        data: 'withdrawal'
                    },
                    {
                        data: 'closing_cash'
                    },
                    {
                        data: 'discrepancy'
                    },
                    {
                        data: 'deshi_sales'
                    },
                    {
                        data: 'beer_sales'
                    },
                    {
                        data: 'english_sales'
                    },
                    {
                        data: 'discount'
                    },
                    {
                        data: 'total_sales'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [1, 'desc']
                ], // latest day first
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
                buttons: [{
                        extend: 'excelHtml5',
                        className: 'btn btn-outline-success btn-sm me-2',
                        title: 'Daily Cash Collection',
                        filename: 'daily_cash_collection_excel',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-outline-danger btn-sm',
                        title: 'Daily Cash Collection',
                        filename: 'daily_cash_collection_pdf',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                initComplete: function() {
                    $('#branch_id, #start_date, #end_date, #status').on('change', function() {
                        table.ajax.reload();
                    });
                }
            });
        });
    </script>
@endsection
