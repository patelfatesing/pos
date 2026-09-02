@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        #trasaction_table th, #trasaction_table td {
            text-align: center;
            vertical-align: middle;
            padding: 8px 12px;
            white-space: nowrap;
        }
        #trasaction_table {
            width: 100% !important;
        }
    </style>

    <div class="content-page">
        <div class="container-fluid">

            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div>
        <h4 class="mb-0">View Transaction - {{ $branch_name }}</h4>
    </div>

    <div class="d-flex align-items-center flex-wrap" style="gap: 20px;">
        <div style="min-width: 200px;">
            @if ($id == 1)
                <select id="party_user_id" class="form-control">
                    <option value="">All Party Customers</option>
                    @foreach ($partyUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->first_name }}</option>
                    @endforeach
                </select>
            @else
                <select id="commission_user_id" class="form-control">
                    <option value="">All</option>
                    <option value="commission">Commission User</option>
                    <option value="one_time">One Time Sale</option>
                </select>
            @endif
        </div>

        <a href="{{ route('sales.add-sales', ['branch_id' => $id, 'shift_id' => $shift_id]) }}"
            class="btn btn-success text-nowrap">
            <i class="fa fa-edit"></i> Add Transaction
        </a>

        <a href="{{ route('shift-manage.list') }}" class="btn btn-secondary text-nowrap">
            Back
        </a>
    </div>
</div>

            <!-- TABLE -->
            <div class="table-responsive rounded mt-2">
                <table class="table table-striped table-bordered w-100" id="trasaction_table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice</th>
                            <th>{{ $branch_name === 'WAREHOUSE' ? 'Party Customer' : 'Commission Customer' }}</th>
                            <th>Sub Total</th>
                            <th>Discount</th>
                            <th>Credit</th>
                            <th>Total</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th>Mode</th>
                            <th>Role</th>
                        </tr>
                    </thead>

                    <tbody></tbody>

                    <tfoot>
                        <tr>
                            <th></th>
                            <th></th>
                            <th class="text-right font-weight-bold">Total:</th>
                            <th id="sub_total_total"></th>
                            <th id="discount_total"></th>
                            <th id="credit_total"></th>
                            <th id="grand_total"></th>
                            <th id="item_count_total"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>

    <!-- Invoice History Modal -->
    <div class="modal fade" id="invoiceHistoryModal" tabindex="-1" aria-labelledby="invoiceHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lowLevelModalLabel">Invoice Activity History</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="invoice-history-content">
                    <div class="text-center p-4">
                        <span class="spinner-border text-secondary"></span>
                        <p>Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let isWarehouse = @json($branch_name === 'WAREHOUSE');

        function formatNumber(val) {
            val = parseFloat(val || 0);
            return val % 1 === 0 ? val : val.toFixed(2);
        }

        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let table = $('#trasaction_table').DataTable({
                pageLength: 10,
                autoWidth: false,
                responsive: false,
                processing: true,
                ordering: true,
                bLengthChange: true,
                serverSide: true,
                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },
                order: [
                    [1, 'desc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: [0]
                }],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                dom: "<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'Bf l>>t<'row'<'col-md-6'i><'col-md-6'p>>",
                initComplete: function() {
                    $('.dataTables_filter input').attr("placeholder", "Search List...");
                },
                buttons: [{
                    extend: 'collection',
                    text: '<i class="fa fa-download"></i>',
                    className: 'btn btn-info btn-sm',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: 'Excel',
                            title: 'Transaction List',
                            filename: 'transaction_list',
                            exportOptions: { columns: ':visible' }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: 'PDF',
                            filename: 'transaction_list',
                            orientation: 'landscape',
                            pageSize: 'A4',
                            exportOptions: { columns: ':visible' },
                            customize: function(doc) {
                                doc.content.splice(0, 1);
                                let subtotal = $('#sub_total_total').text();
                                let discount = $('#discount_total').text();
                                let credit = $('#credit_total').text();
                                let total = $('#grand_total').text();
                                let items = $('#item_count_total').text();

                                doc.content[0].table.body.push([
                                    { text: 'Total', colSpan: 3, alignment: 'right', bold: true },
                                    {}, {},
                                    subtotal,
                                    discount,
                                    credit,
                                    total,
                                    items,
                                    '', '', ''
                                ]);

                                doc.content.unshift({
                                    text: 'Transaction List',
                                    alignment: 'center',
                                    fontSize: 14,
                                    bold: true,
                                    margin: [0, 0, 0, 10]
                                });
                            }
                        }
                    ]
                }],
                ajax: {
                    url: '{{ url('shift-manage/get-trasaction-data') }}',
                    type: 'POST',
                    data: function(d) {
                        d.party_user_id = $('#party_user_id').val();
                        d.commission_user_id = $('#commission_user_id').val();
                        d.type = $('#commission_user_id').val();
                        d.branch_id = {{ $id }};
                        d.shift_id = {{ $shift_id }};
                        d.verify = @json($verify);
                    }
                },
                columns: [
                    {
                        data: null,
                        render: (data, type, row, meta) => meta.row + 1
                    },
                    { data: 'invoice_number' },
                    { data: isWarehouse ? 'party_user' : 'commission_user' },
                    { data: 'sub_total', render: formatNumber },
                    { data: isWarehouse ? 'party_amount' : 'commission_amount', render: formatNumber },
                    { data: 'creditpay', render: formatNumber },
                    { data: 'total', render: formatNumber },
                    { data: 'items_count' },
                    { data: 'status' },
                    { data: 'payment_mode' },
                    { data: 'action' }
                ],
                footerCallback: function(row, data) {
                    let discount = 0,
                        credit = 0,
                        subtotal = 0,
                        total = 0,
                        items = 0;

                    data.forEach(row => {
                        subtotal += parseFloat(row.sub_total || 0);
                        discount += parseFloat((isWarehouse ? row.party_amount : row.commission_amount) || 0);
                        credit += parseFloat(row.creditpay || 0);
                        total += parseFloat(row.total || 0);
                        items += parseFloat(row.items_count || 0);
                    });

                    $('#sub_total_total').html('₹' + formatNumber(subtotal));
                    $('#discount_total').html('₹' + formatNumber(discount));
                    $('#credit_total').html('₹' + formatNumber(credit));
                    $('#grand_total').html('₹' + formatNumber(total));
                    $('#item_count_total').html(items);
                }
            });

            // Filter Trigger
            $('#party_user_id, #commission_user_id').change(function() {
                table.draw();
            });

            // View History Modal
            $(document).on('click', '.view-history-btn', function() {
                const invoiceId = $(this).data('invoice-id');
                var myModal = new bootstrap.Modal(document.getElementById('invoiceHistoryModal'));
                myModal.show();

                $('#invoice-history-content').html(`
                    <div class="text-center p-4">
                        <span class="spinner-border text-secondary"></span>
                        <p>Loading...</p>
                    </div>
                `);

                $.get('{{ url('invoice') }}/' + invoiceId + '/history', function(response) {
                    $('#invoice-history-content').html(response);
                }).fail(function() {
                    $('#invoice-history-content').html(
                        `<p class="text-danger">Failed to load history.</p>`);
                });
            });
        });
    </script>
@endsection