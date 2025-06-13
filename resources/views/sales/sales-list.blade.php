@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 mb-3 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Transaction List</h4>
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
                    <table class="table table-striped nowrap" id="invoice_table" style="width:100%;">
                        <thead class="bg-white text-uppercase">
                            <tr>
                                <th class="text-nowrap">Sr. No.</th>
                                <th class="text-nowrap">Invoice No</th>
                                <th class="text-nowrap">Commission Discount</th>
                                <th class="text-nowrap">Party Discount</th>
                                <th class="text-nowrap">Credit</th>
                                <th class="text-nowrap">Sub Total</th>
                                <th class="text-nowrap">Total</th>
                                <th class="text-nowrap">Sales Qty</th>
                                <th class="text-nowrap">Store</th>
                                <th class="text-nowrap">Payment Status</th>
                                <th class="text-nowrap">Payment Mode</th>
                                <th class="text-nowrap">Date</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th style="text-align:right">Total:</th>
                                <th id="commission_total"></th>
                                <th id="party_total"></th>
                                <th id="credit_total"></th>
                                <th id="sub_total_total"></th>
                                <th id="grand_total"></th>
                                <th id="item_count_total"></th>
                                <th></th><th></th><th></th><th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade bd-example-modal-lg" id="salesCustPhotoShowModal" tabindex="-1" role="dialog"
        aria-labelledby="salesCustPhotoShowModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="salesCustPhotoModalContent"></div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            let table = $('#invoice_table').DataTable({
                scrollX: true,
                responsive: true,
                processing: true,
                serverSide: true,
                autoWidth: false,
                bLengthChange: true,
                order: [[1, 'desc']],
                dom: 'Blfrtip',
                buttons: [
                    {
                        extend: 'csvHtml5',
                        text: 'Export CSV',
                        className: 'btn btn-sm btn-outline-primary',
                        title: 'Transaction Report',
                        filename: 'transaction_report_csv',
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'excelHtml5',
                        text: 'Export Excel',
                        className: 'btn btn-sm btn-outline-success',
                        title: 'Transaction Report',
                        filename: 'transaction_report_excel',
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Export PDF',
                        className: 'btn btn-sm btn-outline-danger',
                        title: 'Transaction Report',
                        filename: 'transaction_report_pdf',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                ajax: {
                    url: '{{ url('sales/get-data') }}',
                    type: 'POST',
                    data: function (d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.branch_id = $('#branch_id').val();
                    }
                },
                columns: [
                    { data: null, render: (data, type, row, meta) => meta.row + 1, className: 'text-center text-nowrap' },
                    { data: 'invoice_number', name: 'invoice_number', className: 'text-nowrap' },
                    {
                        data: 'commission_amount',
                        name: 'commission_amount',
                        render: data => '₹' + parseFloat(data.replace(/,/g, '')).toFixed(2),
                        className: 'text-nowrap'
                    },
                    {
                        data: 'party_amount',
                        name: 'party_amount',
                        render: data => '₹' + parseFloat(data.replace(/,/g, '')).toFixed(2),
                        className: 'text-nowrap'
                    },
                    {
                        data: 'creditpay',
                        name: 'creditpay',
                        render: data => '₹' + parseFloat(data.replace(/,/g, '')).toFixed(2),
                        className: 'text-nowrap'
                    },
                    {
                        data: 'sub_total',
                        name: 'sub_total',
                        render: data => '₹' + parseFloat(data.replace(/,/g, '')).toFixed(2),
                        className: 'text-nowrap'
                    },
                    {
                        data: 'total',
                        name: 'total',
                        render: data => '₹' + parseFloat(data.replace(/,/g, '')).toFixed(2),
                        className: 'text-nowrap'
                    },
                    { data: 'items_count', name: 'items_count', className: 'text-nowrap' },
                    { data: 'branch_name', name: 'branch_name', className: 'text-nowrap' },
                    { data: 'status', name: 'status', className: 'text-nowrap' },
                    { data: 'payment_mode', name: 'payment_mode', orderable: false, searchable: false, className: 'text-nowrap' },
                    { data: 'created_at', name: 'created_at', className: 'text-nowrap' }
                ],
                footerCallback: function (row, data) {
                    const api = this.api();
                    const intVal = i => typeof i === 'string' ? parseFloat(i.replace(/[₹,]/g, '')) || 0 : (typeof i === 'number' ? i : 0);
                    let commission = 0, party = 0, credit = 0, subtotal = 0, total = 0, items = 0;

                    data.forEach(row => {
                        commission += intVal(row.commission_amount);
                        party += intVal(row.party_amount);
                        credit += intVal(row.creditpay);
                        subtotal += intVal(row.sub_total);
                        total += intVal(row.total);
                        items += intVal(row.items_count);
                    });

                    $(api.column(2).footer()).html('₹' + commission.toFixed(2));
                    $(api.column(3).footer()).html('₹' + party.toFixed(2));
                    $(api.column(4).footer()).html('₹' + credit.toFixed(2));
                    $(api.column(5).footer()).html('₹' + subtotal.toFixed(2));
                    $(api.column(6).footer()).html('₹' + total.toFixed(2));
                    $(api.column(7).footer()).html(items);
                }
            });

            $('#storeSearch').on('click', function () {
                table.draw();
            });
        });

        const salesImgViewBase = "{{ url('sales-img-view') }}";

        function showPhoto(id, commission_user_id = '', party_user_id = '', invoice_no = '') {
            const url = `${salesImgViewBase}/${id}?commission_user_id=${commission_user_id}&party_user_id=${party_user_id}&invoice_no=${invoice_no}`;
            $.ajax({
                url: url,
                type: 'GET',
                success: function (response) {
                    $('#salesCustPhotoModalContent').html(response);
                    $('#salesCustPhotoShowModal').modal('show');
                },
                error: function () {
                    alert('Photos not found.');
                }
            });
        }
    </script>
@endsection
