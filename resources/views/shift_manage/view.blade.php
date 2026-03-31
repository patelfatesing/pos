@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="content-page">
        <div class="container-fluid">

            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h4 class="mb-0">View Transaction - {{ $branch_name }}</h4>
                </div>

                <div class="col-md-4"></div>

                <div class="col-md-2">
                    <div class="form-group mb-0">

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
                </div>

                <div class="col-md-2">
                    <a href="{{ route('sales.add-sales', ['branch_id' => $id, 'shift_id' => $shift_id]) }}"
                        class="btn btn-primary-dark mr-2">
                        <i class="fa fa-edit"></i> Add Trasaction
                    </a>
                </div>
                <div>

                    <a href="{{ route('shift-manage.list') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>

            <!-- TABLE -->

            <div class="table-responsive rounded mt-2">
                <table class="table table-striped table-bordered nowrap" id="trasaction_table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice</th>
                            <th>Commission</th>
                            <th>Commission ₹</th>
                            <th>Party</th>
                            <th>Party ₹</th>
                            <th>Credit</th>
                            <th>Sub Total</th>
                            <th>Total</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th>Mode</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody></tbody>

                    <tfoot>
                        <tr>
                            <th></th>
                            <th></th>
                            <th class="text-end"><b>Total:</b></th>
                            <th id="commission_total"></th>
                            <th></th>
                            <th id="party_total"></th>
                            <th id="credit_total"></th>
                            <th id="sub_total_total"></th>
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
    <div class="modal fade" id="invoiceHistoryModal" tabindex="-1" aria-labelledby="invoiceHistoryModalLabel"
        aria-hidden="true">
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
        let pdfLogo = "";

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
                responsive: true,
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
                ], // FIXED

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
                    buttons: [{
                            extend: 'excelHtml5',
                            text: 'Excel',
                            title: 'Transaction List',
                            filename: 'transaction_list',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: 'PDF',
                            filename: 'transaction_list',
                            orientation: 'landscape',
                            pageSize: 'A4',
                            exportOptions: {
                                columns: ':visible'
                            },

                            customize: function(doc) {

                                doc.content.splice(0, 1);

                                let commission = $('#commission_total').text();
                                let party = $('#party_total').text();
                                let credit = $('#credit_total').text();
                                let subtotal = $('#sub_total_total').text();
                                let total = $('#grand_total').text();
                                let items = $('#item_count_total').text();

                                doc.content[0].table.body.push([{
                                        text: 'Total',
                                        colSpan: 3,
                                        alignment: 'right',
                                        bold: true
                                    }, {}, {},
                                    commission,
                                    '',
                                    party,
                                    credit,
                                    subtotal,
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

                columns: [{
                        data: null,
                        render: (data, type, row, meta) => meta.row + 1
                    },
                    {
                        data: 'invoice_number'
                    },
                    {
                        data: 'commission_user'
                    },
                    {
                        data: 'commission_amount',
                        render: formatNumber
                    },
                    {
                        data: 'party_user'
                    },
                    {
                        data: 'party_amount',
                        render: formatNumber
                    },
                    {
                        data: 'creditpay',
                        render: formatNumber
                    },
                    {
                        data: 'sub_total',
                        render: formatNumber
                    },
                    {
                        data: 'total',
                        render: formatNumber
                    },
                    {
                        data: 'items_count'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'payment_mode'
                    },
                    {
                        data: 'action'
                    }
                ],

                footerCallback: function(row, data) {

                    let commission = 0,
                        party = 0,
                        credit = 0,
                        subtotal = 0,
                        total = 0,
                        items = 0;

                    data.forEach(row => {
                        commission += parseFloat(row.commission_amount || 0);
                        party += parseFloat(row.party_amount || 0);
                        credit += parseFloat(row.creditpay || 0);
                        subtotal += parseFloat(row.sub_total || 0);
                        total += parseFloat(row.total || 0);
                        items += parseFloat(row.items_count || 0);
                    });

                    $('#commission_total').html('₹' + formatNumber(commission));
                    $('#party_total').html('₹' + formatNumber(party));
                    $('#credit_total').html('₹' + formatNumber(credit));
                    $('#sub_total_total').html('₹' + formatNumber(subtotal));
                    $('#grand_total').html('₹' + formatNumber(total));
                    $('#item_count_total').html(items);
                }
            });

            // FILTER EVENTS
            $('#party_user_id, #commission_user_id').change(function() {
                table.draw();
            });

            // Attach event using delegation
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
