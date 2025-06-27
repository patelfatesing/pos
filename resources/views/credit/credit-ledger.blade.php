@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 mb-3 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Credit Ledger History</h4>
                    </div>
                    <div class="col-md-3 mb-2">
                        <input type="date" id="start_date" class="form-control">
                    </div>
                    <div class="col-md-3 mb-2">
                        <input type="date" id="end_date" class="form-control">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select id="branch_id" class="form-control">
                            <option value="">All Branches</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-primary w-100" id="storeSearch">Search</button>
                    </div>
                </div>

                <div class="table-responsive rounded mb-3">
                    <table class="table table-striped nowrap" id="credit_history_table" style="width:100%;">
                        <thead class="bg-white">
                            <tr class="ligth ligth-data">
                                <th class="text-nowrap">Sr. No.</th>
                                <th class="text-nowrap">Invoice No</th>
                                <th class="text-nowrap">Party Customer</th>
                                <th class="text-nowrap">Type</th>
                                <th class="text-nowrap">Total Amount</th>
                                <th class="text-nowrap">Credit</th>
                                <th class="text-nowrap">Debit</th>
                                <th class="text-nowrap">Total Items</th>
                                <th class="text-nowrap">Store</th>
                                <th class="text-nowrap">Status</th>
                                <th class="text-nowrap">Date</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {

            // CSRF setup
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            let table = $('#credit_history_table').DataTable({
                scrollX: true,
                responsive: true,
                processing: true,
                serverSide: true,
                autoWidth: false,
                bLengthChange: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                order: [
                    [1, 'desc']
                ],
                dom: 'Blfrtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Export Excel',
                        className: 'btn btn-sm btn-outline-success',
                        title: 'Credit Ledger Report',
                        filename: 'credit_ledger_report_excel',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Export PDF',
                        className: 'btn btn-sm btn-outline-danger',
                        title: 'Credit Ledger Report',
                        filename: 'credit_ledger_report_pdf',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                ajax: {
                    url: '{{ url('credit/credit-ledger-data') }}',
                    type: 'POST',
                    data: function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.branch_id = $('#branch_id').val();
                    }
                },
                columns: [{
                        data: null,
                        render: (data, type, row, meta) => meta.row + 1
                    },
                    {
                        data: 'invoice_number',
                        name: 'invoice_number'
                    },
                    {
                        data: 'party_user',
                        name: 'party_user'
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                        render: data => '₹' + parseFloat(data).toFixed(2)
                    },
                    {
                        data: 'credit_amount',
                        name: 'credit_amount',
                        render: data => '₹' + parseFloat(data).toFixed(2)
                    },
                    {
                        data: 'debit_amount',
                        name: 'debit_amount',
                        render: data => '₹' + parseFloat(data).toFixed(2)
                    },
                    {
                        data: 'total_purchase_items',
                        name: 'total_purchase_items'
                    },
                    {
                        data: 'branch_name',
                        name: 'branch_name'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    }
                ]
            });

            $('#storeSearch').on('click', function() {
                table.draw();
            });
        });
    </script>
@endsection
