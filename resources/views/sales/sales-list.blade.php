@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="content-page">
        <div class="container-fluid">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="mb-0">Transaction List</h4>
                </div>
                <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
            </div>
            <!-- Filters -->
            <div class="row mt-2">

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
                    <button class="btn btn-success w-100" id="storeSearch">Search</button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered nowrap" id="invoice_table">
                    <thead>
                        <tr>
                            <th>Sr. No.</th>
                            <th>Invoice No</th>
                            <th>Commission Customer</th>
                            <th>Commission Discount</th>
                            <th>Party Customer</th>
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
                            <th></th>
                            <th></th>
                            <th></th>
                            <th class="text-end"><b>Total:</b></th>
                            <th id="commission_total" class="text-end"></th>
                            <th id="party_total" class="text-end"></th>
                            <th id="credit_total" class="text-end"></th>
                            <th id="sub_total_total" class="text-end"></th>
                            <th id="grand_total" class="text-end"></th>
                            <th id="item_count_total" class="text-end"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade bd-example-modal-lg" id="salesCustPhotoShowModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="salesCustPhotoModalContent"></div>
        </div>
    </div>

    <!-- Invoice History Modal -->
    <div class="modal fade" id="invoiceHistoryModal" tabindex="-1" aria-labelledby="invoiceHistoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title">Invoice Activity History</h5>

                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
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
        var pdfLogo = "";
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let table = $('#invoice_table').DataTable({
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
                    [13, 'desc']
                ], // created_at column
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [0, 10, 11, 12]
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
                    autoClose: true,
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '<i class="fa fa-file-excel-o"></i> Excel',
                            title: 'Transaction List',
                            filename: 'transaction_list',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf-o"></i> PDF',
                            filename: 'transaction_list',
                            orientation: 'landscape',
                            pageSize: 'A4',

                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]
                            },

                            customize: function(doc) {

                                // REMOVE default title
                                doc.content.splice(0, 1);

                                // CENTER TABLE
                                doc.content[0].alignment = 'center';

                                // MAKE TABLE WIDTH FULL PAGE
                                doc.content[0].table.widths = [
                                    30, 90, 100, 100, 70, 70, 50, 70, 70, 60, 80, 70,
                                    70, 80, 60
                                ];
                                // Center table
                                doc.content[0].alignment = 'center';

                                // Header style
                                doc.styles.tableHeader = {
                                    alignment: 'center',
                                    bold: true,
                                    fontSize: 10,
                                    fillColor: '#34495E',
                                    color: 'white'
                                };

                                // Align body columns
                                var tableBody = doc.content[0].table.body;

                                for (var i = 1; i < tableBody.length; i++) {

                                    tableBody[i][0].alignment = 'center';
                                    tableBody[i][1].alignment = 'left';
                                    tableBody[i][2].alignment = 'left';
                                    tableBody[i][3].alignment = 'left';

                                    tableBody[i][4].alignment = 'right';
                                    tableBody[i][5].alignment = 'right';
                                    tableBody[i][6].alignment = 'right';
                                    tableBody[i][7].alignment = 'right';
                                    tableBody[i][8].alignment = 'right';
                                    tableBody[i][9].alignment = 'right';
                                }

                                // Get footer totals from page
                                var commission = $('#commission_total').text();
                                var party = $('#party_total').text();
                                var credit = $('#credit_total').text();
                                var subtotal = $('#sub_total_total').text();
                                var total = $('#grand_total').text();
                                var items = $('#item_count_total').text();

                                // Add footer totals row
                                doc.content[0].table.body.push([

                                    {
                                        text: 'Total',
                                        colSpan: 4,
                                        alignment: 'right',
                                        bold: true
                                    }, {}, {}, {},
                                    {
                                        text: party,
                                        alignment: 'right',
                                        bold: true
                                    },
                                    {
                                        text: credit,
                                        alignment: 'right',
                                        bold: true
                                    },
                                    {
                                        text: commission,
                                        alignment: 'right',
                                        bold: true
                                    },
                                    {
                                        text: subtotal,
                                        alignment: 'right',
                                        bold: true
                                    },
                                    {
                                        text: total,
                                        alignment: 'right',
                                        bold: true
                                    },
                                    {
                                        text: items,
                                        alignment: 'right',
                                        bold: true
                                    },

                                    {
                                        text: '',
                                        colSpan: 5
                                    }, {}, {}, {}, {}

                                ]);

                                // HEADER
                                doc.content.unshift({
                                    margin: [0, 0, 0, 12],
                                    columns: [{
                                            width: '33%',
                                            columns: [{
                                                    image: pdfLogo,
                                                    width: 30
                                                },
                                                {
                                                    text: 'LiquorHub',
                                                    fontSize: 11,
                                                    bold: true,
                                                    margin: [5, 8, 0, 0]
                                                }
                                            ]
                                        },
                                        {
                                            width: '34%',
                                            text: 'Transaction List',
                                            alignment: 'center',
                                            fontSize: 16,
                                            bold: true,
                                            margin: [0, 8, 0, 0]
                                        },
                                        {
                                            width: '33%',
                                            text: 'Generated: ' + new Date()
                                                .toLocaleString(),
                                            alignment: 'right',
                                            fontSize: 9,
                                            margin: [0, 8, 0, 0]
                                        }
                                    ]
                                });

                                doc.styles.tableHeader.fontSize = 10;
                                doc.defaultStyle.fontSize = 9;
                            }
                        }
                    ]
                }],
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
                        data: 'commission_user'
                    },
                    {
                        data: 'commission_amount',
                        render: function(data) {
                            return parseFloat(data) % 1 === 0 ? parseInt(data) : parseFloat(data)
                                .toFixed(2);
                        }
                    },
                    {
                        data: 'party_user'
                    },
                    {
                        data: 'party_amount',
                        render: function(data) {
                            return parseFloat(data) % 1 === 0 ? parseInt(data) : parseFloat(data)
                                .toFixed(2);
                        }
                    },
                    {
                        data: 'creditpay',
                        render: function(data) {
                            return parseFloat(data) % 1 === 0 ? parseInt(data) : parseFloat(data)
                                .toFixed(2);
                        }
                    },
                    {
                        data: 'sub_total',
                        render: function(data) {
                            return parseFloat(data) % 1 === 0 ? parseInt(data) : parseFloat(data)
                                .toFixed(2);
                        }
                    },
                    {
                        data: 'total',
                        render: function(data) {
                            return parseFloat(data) % 1 === 0 ? parseInt(data) : parseFloat(data)
                                .toFixed(2);
                        }
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
                    $('#commission_total').html('₹' + commission);
                    $('#party_total').html('₹' + party);
                    $('#credit_total').html('₹' + credit);
                    $('#sub_total_total').html('₹' + subtotal);
                    $('#grand_total').html('₹' + total);
                    $('#item_count_total').html(items);
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

        // Attach event using delegation
        $(document).on('click', '.view-history-btn', function() {
            const invoiceId = $(this).data('invoice-id');
            $('#invoiceHistoryModal').modal('show');

            $('#invoice-history-content').html(`
                <div class="text-center p-4">
                    <span class="spinner-border text-secondary"></span>
                    <p>Loading...</p>
                </div>
            `);

            $.get('{{ url('invoice') }}/' + invoiceId + '/history', function(response) {
                $('#invoice-history-content').html(response);
            }).fail(function() {
                $('#invoice-history-content').html(`<p class="text-danger">Failed to load history.</p>`);
            });
        });

        function formatNumber(val) {
            return parseFloat(val) % 1 === 0 ? parseInt(val) : parseFloat(val).toFixed(2);
        }

        function getBase64Image(url, callback) {
            var img = new Image();
            img.crossOrigin = "Anonymous";

            img.onload = function() {
                var canvas = document.createElement("canvas");
                canvas.width = this.width;
                canvas.height = this.height;

                var ctx = canvas.getContext("2d");
                ctx.drawImage(this, 0, 0);

                var dataURL = canvas.toDataURL("image/png");
                callback(dataURL);
            };

            img.src = url;
        }

        getBase64Image("https://liquorhub.in/assets/images/logo.png", function(base64) {
            pdfLogo = base64;
        });
    </script>
@endsection
