@extends('layouts.backend.datatable_layouts')

@section('styles')
    <style>
        #invoice_table th,
        #invoice_table td {
            white-space: normal !important;
            word-wrap: break-word;
            vertical-align: middle;
            min-width: 120px;
        }

        .dataTables_wrapper .dataTables_filter input {
            width: auto;
            display: inline-block;
        }
    </style>
@endsection

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <!-- Filters -->
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

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-striped" id="invoice_table" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Sr. No.</th>
                                <th>Invoice No</th>
                                <th>Party Customer</th>
                                <th>Commission Customer</th>
                                <th>Commission Discount</th>
                                <th>Party Discount</th>
                                <th>Credit</th>
                                <th>Sub Total</th>
                                <th>Total</th>
                                <th>Sales Qty</th>
                                <th>Store</th>
                                <th>Payment Status</th>
                                <th>Payment Mode</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" style="text-align:right">Total:</th>
                                <th id="commission_total"></th>
                                <th id="party_total"></th>
                                <th id="credit_total"></th>
                                <th id="sub_total_total"></th>
                                <th id="grand_total"></th>
                                <th id="item_count_total"></th>
                                <th colspan="4"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade bd-example-modal-lg" id="salesCustPhotoShowModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="salesCustPhotoModalContent"></div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let table = $('#invoice_table').DataTable({
                scrollX: true,
                responsive: true,
                processing: true,
                serverSide: true,
                autoWidth: false,
                order: [
                    [13, 'desc']
                ], // created_at column
                dom: 'Blfrtip',
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Export Excel',
                        className: 'btn btn-sm btn-outline-success',
                        title: 'Transaction Report',
                        filename: 'transaction_report_excel',
                        exportOptions: {
                            columns: ':visible'
                        }
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
                        },
                        customize: function(doc) {
                            // Add current date at top-right
                            const currentDate = new Date().toLocaleDateString('en-IN', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric'
                            });

                            doc.content.splice(0, 0, {
                                text: 'Generate Date: ' + currentDate,
                                alignment: 'right',
                                margin: [0, 0, 0, 10],
                                fontSize: 10
                            });

                            // Title styling
                            doc.styles.title = {
                                alignment: 'center',
                                fontSize: 14,
                                bold: true,
                                margin: [0, 0, 0, 10]
                            };

                            // Table header styling
                            doc.styles.tableHeader.alignment = 'left';

                            // General font size
                            doc.defaultStyle.fontSize = 9;
                        }
                    }
                ],
                ajax: {
                    url: '{{ url('sales/get-data') }}',
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
                        data: 'invoice_number'
                    },
                    {
                        data: 'party_user'
                    },
                    {
                        data: 'commission_user'
                    },
                    {
                        data: 'commission_amount'
                    },
                    {
                        data: 'party_amount'
                    },
                    {
                        data: 'creditpay'
                    },
                    {
                        data: 'sub_total'
                    },
                    {
                        data: 'total'
                    },
                    {
                        data: 'items_count'
                    },
                    {
                        data: 'branch_name'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'payment_mode'
                    },
                    {
                        data: 'created_at'
                    }, // Sort by default
                    {
                        data: 'action'
                    }
                ],
                footerCallback: function(row, data) {
                    const api = this.api();
                    const intVal = i => typeof i === 'string' ? parseFloat(i.replace(/[₹,]/g, '')) ||
                        0 : (typeof i === 'number' ? i : 0);
                    let commission = 0,
                        party = 0,
                        credit = 0,
                        subtotal = 0,
                        total = 0,
                        items = 0;

                    data.forEach(row => {
                        commission += intVal(row.commission_amount);
                        party += intVal(row.party_amount);
                        credit += intVal(row.creditpay);
                        subtotal += intVal(row.sub_total);
                        total += intVal(row.total);
                        items += intVal(row.items_count);
                    });

                    $(api.column(4).footer()).html('₹' + commission.toFixed(2));
                    $(api.column(5).footer()).html('₹' + party.toFixed(2));
                    $(api.column(6).footer()).html('₹' + credit.toFixed(2));
                    $(api.column(7).footer()).html('₹' + subtotal.toFixed(2));
                    $(api.column(8).footer()).html('₹' + total.toFixed(2));
                    $(api.column(9).footer()).html(items);
                }
            });

            // Refresh on filter
            $('#storeSearch').on('click', function() {
                table.draw();
            });
        });

        const salesImgViewBase = "{{ url('sales-img-view') }}";

        function showPhoto(id, commission_user_id = '', party_user_id = '', invoice_no = '') {
            const url =
                `${salesImgViewBase}/${id}?commission_user_id=${commission_user_id}&party_user_id=${party_user_id}&invoice_no=${invoice_no}`;
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    $('#salesCustPhotoModalContent').html(response);
                    $('#salesCustPhotoShowModal').modal('show');
                },
                error: function() {
                    alert('Photos not found.');
                }
            });
        }
    </script>
@endsection
