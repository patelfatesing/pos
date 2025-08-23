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

        .w-120 {
            width: 120px
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
                                <h4 class="mb-3">Discount &amp; Offer Report</h4>
                            </div>
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
                            <option value="">All Party Customers</option>
                            @foreach ($parties as $p)
                                <option value="{{ $p->id }}">{{ $p->first_name }} {{ $p->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="payment_mode" class="form-control">
                            <option value="">Payment: All</option>
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                            <option value="online">Online</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="start_date" class="form-control w-120" />
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="end_date" class="form-control w-120" />
                    </div>
                    <div class="col-md-1">
                        <input type="number" min="0" max="100" step="0.01" id="min_discount_pct"
                            class="form-control" placeholder="% ≥" title="Minimum Discount %" />
                    </div>
                </div>

                <div class="table-responsive rounded">
                    <table class="table table-striped table-bordered nowrap" id="discounts_table" style="width:100%;">
                        <thead class="bg-white">
                            <tr class="ligth ligth-data">
                                <th>Sr No</th>
                                <th>Date</th>
                                <th>Invoice</th>
                                <th>Branch</th>
                                <th>Party</th>
                                <th>Subtotal</th>
                                <th>Commission Disc</th>
                                <th>Party Disc</th>
                                <th>Total Discount</th>
                                <th>Discount %</th>
                                <th>Net Before Tax</th>
                                <th>Tax</th>
                                <th>Computed Total</th>
                                <th>Payment Mode</th>
                                <th>Status</th>
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

            const table = $('#discounts_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('reports.discounts.get_data') }}",
                    type: 'POST',
                    data: function(d) {
                        d.branch_id = $('#branch_id').val();
                        d.party_user_id = $('#party_user_id').val();
                        d.payment_mode = $('#payment_mode').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.min_discount_pct = $('#min_discount_pct').val();
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
                        data: 'invoice'
                    }, // link
                    {
                        data: 'branch_name'
                    },
                    {
                        data: 'party_name'
                    },
                    {
                        data: 'sub_total'
                    },
                    {
                        data: 'commission_disc'
                    },
                    {
                        data: 'party_disc'
                    },
                    {
                        data: 'total_disc'
                    },
                    {
                        data: 'discount_pct'
                    },
                    {
                        data: 'net_before_tax'
                    },
                    {
                        data: 'tax'
                    },
                    {
                        data: 'computed_total'
                    },
                    {
                        data: 'payment_mode'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [1, 'desc']
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
                        title: 'Discount & Offer Report',
                        filename: 'discount_offer_report',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-outline-danger btn-sm',
                        title: 'Discount & Offer Report',
                        filename: 'discount_offer_report',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                initComplete: function() {
                    $('#branch_id, #party_user_id, #payment_mode, #start_date, #end_date, #min_discount_pct')
                        .on('change keyup', function() {
                            table.ajax.reload();
                        });
                }
            });
        });
    </script>
@endsection
