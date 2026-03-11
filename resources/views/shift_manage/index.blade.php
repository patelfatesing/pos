@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Wrapper Start -->

    <div class="content-page">
        <div class="container-fluid">
            <div class="card-header mb-3 d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h4 class="mb-0">Shift Manage</h4>
                </div>
                @if (auth()->user()->role_id == 1 || canCreate(auth()->user()->role_id, 'categories-create'))
                    <button class="btn btn-primary add-list" data-toggle="modal" data-target="#addCategoryModal">
                        <i class="las la-plus mr-3"></i>Create New Category
                    </button>
                @endif
            </div>

            <div class="row">
                <div class="col-md-2 mb-2">
                    <input type="date" id="start_date" class="form-control">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="date" id="end_date" class="form-control">
                </div>
                <div class="col-md-2 mb-2">
                    <select id="branch_id" class="form-control">
                        <option value="">All Branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select id="user_id" class="form-control">
                        <option value="">All Users</option>
                        @foreach ($users as $users)
                            <option value="{{ $users->id }}">{{ $users->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 mb-2">
                    <button class="btn btn-primary w-100" id="shiftSearch">Search</button>
                </div>
                <div class="col-md-2 mb-2">
                    <button class="btn btn-secondary w-100" id="shiftReset">Reset</button>
                </div>

            </div>

            <div class="table-responsive rounded mb-3" id="shiftTableContainer">
                <table class="table table-striped table-bordered nowrap" id="shift_tbl">
                    <thead class="bg-white text-uppercase">
                        <tr class="ligth ligth-data">
                            <th>Shift No</th>
                            <th>Store</th>
                            <th>User</th>
                            <th>Shift Start</th>
                            <th>Shift End</th>
                            <th>Opening Cash</th>
                            <th>Closing Cash</th>
                            <th>Status</th>
                            <th>Total Sales</th>
                            <th>Difference</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr style="font-weight:bold;background:#f8f9fa;">
                            <th colspan="5" class="text-end">Total :</th>
                            <th id="ft_opening_cash">₹0.00</th>
                            <th id="ft_closing_cash">₹0.00</th>
                            <th></th>
                            <th id="ft_total_sales">₹0.00</th>
                            <th id="ft_difference">₹0.00</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- Page end  -->
        </div>
    </div>

    <!-- Wrapper End-->

    <!-- Transactions Modal -->
    <div class="modal fade" id="transactionsModal" tabindex="-1" aria-labelledby="transactionsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transactionsModalLabel">Transactions for Store: <span
                            id="modal-branch-name"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div class="table-responsive">
                        <table class="table table-bordered" id="invoice_table_modal">
                            <thead>
                                <tr>
                                    <th>Invoice No</th>
                                    <th>Cash Amount</th>
                                    <th>UPI Amount</th>
                                    <th>Online Amount</th>
                                    <th>Credit Pay</th>
                                    <th>Payment Mode</th>
                                    <th>Total Items</th>
                                    <th>Sub Total</th>
                                    <th>Tax</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="12" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Shift Summary Modal -->
    <div class="modal fade" id="shiftSummaryModal" tabindex="-1" aria-labelledby="shiftSummaryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="shiftSummaryModalLabel">Shift Close Summary - <span
                            id="modalBranchName">Branch</span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body row" id="shiftSummaryContent">

                </div>
            </div>
        </div>
    </div>

    <!-- Shift Summary Modal -->
    <!-- Modal for image preview -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Physical Stock Photo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <img id="modalImage" src="" alt="Image" class="img-fluid" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <script>
        var pdfLogo = "";
        let headerLeft;

        if (pdfLogo) {
            headerLeft = {
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
            };
        } else {
            headerLeft = {
                width: '33%',
                text: 'LiquorHub',
                fontSize: 11,
                bold: true
            };
        }


        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0'); // months are 0-indexed
            const day = String(today.getDate()).padStart(2, '0');

            const localDate = `${year}-${month}-${day}`;
            document.getElementById('start_date').value = localDate;
            document.getElementById('end_date').value = localDate;
        });

        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#shift_tbl').DataTable().clear().destroy();

            let table = $('#shift_tbl').DataTable({
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
                ajax: {
                    url: '{{ url('shift-manage/get-data') }}',
                    type: 'POST',
                    data: function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.branch_id = $('#branch_id').val();
                        d.user_id = $('#user_id').val();

                    }
                },
                dom: "<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'Bf l>>t<'row'<'col-md-6'i><'col-md-6'p>>",
                initComplete: function() {
                    $('.dataTables_filter input').attr("placeholder", "Search List...");
                },
                columns: [{
                        data: 'shift_no',
                        name: 'shift_no',
                        orderable: false
                    },
                    {
                        data: 'branch_name',
                        name: 'branch_name',
                        orderable: false
                    },
                    {
                        data: 'user_name',
                        name: 'user_name',
                        orderable: false
                    },
                    {
                        data: 'start_time',
                        name: 'start_time',
                        orderable: false
                    },
                    {
                        data: 'end_time',
                        name: 'end_time',
                        orderable: false
                    },
                    {
                        data: 'opening_cash',
                        name: 'opening_cash',
                        render: function(data, type, row) {
                            return '₹' + data;
                        }
                    },
                    {
                        data: 'closing_cash',
                        name: 'closing_cash',
                        render: function(data, type, row) {
                            return '₹' + data;
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false
                    },

                    {
                        data: 'total_transaction',
                        name: 'total_transaction',
                        orderable: false
                    },
                    {
                        data: 'difference',
                        name: 'difference',
                        orderable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                drawCallback: function(settings) {
                    let api = this.api();

                    // helper
                    let intVal = v => typeof v === 'string' ?
                        parseFloat(v.replace(/[₹,]/g, '')) || 0 :
                        typeof v === 'number' ? v : 0;

                    let openingTotal = api.column(5, {
                            page: 'current'
                        })
                        .data().reduce((a, b) => intVal(a) + intVal(b), 0);

                    let closingTotal = api.column(6, {
                            page: 'current'
                        })
                        .data().reduce((a, b) => intVal(a) + intVal(b), 0);

                    let salesTotal = api.column(8, {
                            page: 'current'
                        })
                        .data().reduce((a, b) => intVal(a) + intVal(b), 0);

                    let diffTotal = api.column(9, {
                            page: 'current'
                        })
                        .data().reduce((a, b) => intVal(a) + intVal(b), 0);

                    $('#ft_opening_cash').html('₹' + openingTotal.toFixed(2));
                    $('#ft_closing_cash').html('₹' + closingTotal.toFixed(2));
                    $('#ft_total_sales').html('₹' + salesTotal.toFixed(2));
                    $('#ft_difference').html('₹' + diffTotal.toFixed(2));
                },
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [1, 2, 3, 4, 5, 6, 7] // make "action" column unsortable
                }],
                order: [
                    [3, 'desc']
                ], // Default order on shift_start DESC

                lengthMenu: [
                    [10, 25, 50],
                    ['10 rows', '25 rows', '50 rows']
                ],
                buttons: [{
                    extend: 'collection',
                    text: '<i class="fa fa-download"></i>',
                    className: 'btn btn-info btn-sm',
                    autoClose: true,
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '<i class="fa fa-file-excel-o"></i> Excel',
                            title: 'Shift Manage',
                            filename: 'shift_manage_list',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf-o"></i> PDF',
                            filename: 'shift_manage_list',
                            orientation: 'landscape',
                            pageSize: 'A4',

                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
                            },

                            customize: function(doc) {

                                // REMOVE default title
                                doc.content.splice(0, 1);

                                doc.styles.tableHeader.alignment = 'center';

                                // HEADER
                                doc.content.unshift({
                                    margin: [0, 0, 0, 12],
                                    columns: [
                                        headerLeft,
                                        {
                                            width: '34%',
                                            text: 'Shift Manage',
                                            alignment: 'center',
                                            fontSize: 16,
                                            bold: true
                                        },
                                        {
                                            width: '33%',
                                            text: 'Generated: ' + new Date()
                                                .toLocaleString(),
                                            alignment: 'right',
                                            fontSize: 9
                                        }
                                    ]
                                });

                                doc.styles.tableHeader.fontSize = 10;
                                doc.defaultStyle.fontSize = 9;
                            }
                        }
                    ]
                }]
            });
            $('#shiftSearch').on('click', function() {
                table.draw();
            });
            $('#shiftReset').click(function() {
                $('#start_date').val('');
                $('#end_date').val('');
                $('#branch_id').val('');
                $('#user_id').val('');
                table.ajax.reload();
            });
        });

        // Use event delegation for dynamically created elements:
        $('#shift_tbl tbody').on('click', '.view-transactions', function() {
            var branchId = $(this).data('branch-id');
            var branchName = $(this).data('branch-name');

            $('#modal-branch-name').text(branchName);
            var $tbody = $('#invoice_table_modal tbody');
            $tbody.html('<tr><td colspan="12" class="text-center">Loading...</td></tr>');

            // Show the modal
            var myModal = new bootstrap.Modal(document.getElementById('transactionsModal'));
            myModal.show();

            $.ajax({
                url: '{{ url('shift-manage/invoices-by-branch') }}',
                method: 'POST',
                data: {
                    branch_id: branchId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.data && response.data.length > 0) {
                        var rows = '';
                        var totalCash = 0,
                            totalUPI = 0,
                            totalOnline = 0,
                            totalCredit = 0;
                        var totalSubtotal = 0,
                            totalTax = 0,
                            totalTotal = 0,
                            totalQty = 0;

                        $.each(response.data, function(i, invoice) {
                            var cash = parseFloat(invoice.cash_amount);
                            var upi = parseFloat(invoice.upi_amount);
                            var online = parseFloat(invoice.online_amount);
                            var credit = parseFloat(invoice.creditpay);
                            var subtotal = parseFloat(invoice.sub_total);
                            var tax = parseFloat(invoice.tax);
                            var total = parseFloat(invoice.total);
                            var qty = parseInt(invoice.total_item_qty);

                            totalCash += cash;
                            totalUPI += upi;
                            totalOnline += online;
                            totalCredit += credit;
                            totalSubtotal += subtotal;
                            totalTax += tax;
                            totalTotal += total;
                            totalQty += qty;

                            rows += '<tr>' +
                                '<td>' + invoice.invoice_number + '</td>' +
                                '<td>₹' + cash.toFixed(2) + '</td>' +
                                '<td>₹' + upi.toFixed(2) + '</td>' +
                                '<td>₹' + online.toFixed(2) + '</td>' +
                                '<td>₹' + credit.toFixed(2) + '</td>' +
                                '<td>' + invoice.payment_mode + '</td>' +
                                '<td>' + qty + '</td>' +
                                '<td>₹' + subtotal.toFixed(2) + '</td>' +
                                '<td>₹' + tax.toFixed(2) + '</td>' +
                                '<td>₹' + total.toFixed(2) + '</td>' +
                                '<td>' + invoice.status + '</td>' +
                                '<td>' + invoice.created_at + '</td>' +
                                '</tr>';
                        });

                        // Add totals row
                        rows += '<tr style="font-weight: bold; background: #f8f9fa;">' +
                            '<td class="text-end">Total:</td>' +
                            '<td>₹' + totalCash.toFixed(2) + '</td>' +
                            '<td>₹' + totalUPI.toFixed(2) + '</td>' +
                            '<td>₹' + totalOnline.toFixed(2) + '</td>' +
                            '<td>₹' + totalCredit.toFixed(2) + '</td>' +
                            '<td></td>' +
                            '<td>' + totalQty + '</td>' +
                            '<td>₹' + totalSubtotal.toFixed(2) + '</td>' +
                            '<td>₹' + totalTax.toFixed(2) + '</td>' +
                            '<td>₹' + totalTotal.toFixed(2) + '</td>' +
                            '<td colspan="2"></td>' +
                            '</tr>';

                        $tbody.html(rows);
                    } else {
                        $tbody.html(
                            '<tr><td colspan="12" class="text-center">No transactions found.</td></tr>'
                        );
                    }
                },
                error: function() {
                    $tbody.html(
                        '<tr><td colspan="12" class="text-center text-danger">Error loading transactions.</td></tr>'
                    );
                }
            });
        });

        // Close Shift button click (event delegation)
        $('#shift_tbl tbody').on('click', '.close-shift', function() {
            var shiftId = $(this).data('id');

            // loadShiftSummary(shiftId)
            $.ajax({
                url: '{{ url('shift-manage/close-shift') }}/' + shiftId,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.code != 200) {
                        Swal.fire('Info', response.message, 'info');
                        $('#shift_tbl').DataTable().ajax.reload(null, false);
                    } else {
                        $('#shiftSummaryContent').html(response
                            .html); // show the returned Blade HTML
                        // Show the modal
                        const modal = new bootstrap.Modal(document.getElementById(
                            'shiftSummaryModal'));
                        modal.show();
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to close shift.', 'error');
                }
            });
            // Swal.fire({
            //     title: 'Are you sure?',
            //     text: "Do you want to close this shift?",
            //     icon: 'warning',
            //     showCancelButton: true,
            //     confirmButtonText: 'Yes, close it!',
            //     cancelButtonText: 'Cancel'
            // }).then((result) => {
            //     if (result.isConfirmed) {
            //         $.ajax({
            //             url: '{{ url('shift-manage/close-shift') }}/' + shiftId,
            //             type: 'POST',
            //             data: {
            //                 _token: '{{ csrf_token() }}'
            //             },
            //             success: function(response) {
            //                 if (response.code != 200) {
            //                     Swal.fire('Info', response.message, 'info');
            //                     $('#shift_tbl').DataTable().ajax.reload(null, false);
            //                 } else {
            //                     $('#shiftSummaryContent').html(response
            //                     .html); // show the returned Blade HTML
            //                     // Show the modal
            //                     const modal = new bootstrap.Modal(document.getElementById(
            //                         'shiftSummaryModal'));
            //                     modal.show();
            //                 }
            //             },
            //             error: function() {
            //                 Swal.fire('Error!', 'Failed to close shift.', 'error');
            //             }
            //         });
            //     }
            // });

        });

        function loadShiftSummary(shiftId) {
            fetch(`/shift-summary/${shiftId}`)
                .then(res => res.json())
                .then(data => {
                    // Fill modal fields
                    document.getElementById('modalBranchName').textContent = data.shift.branch_id;
                    document.getElementById('startTime').textContent = data.shift.start_time;
                    document.getElementById('endTime').textContent = data.shift.end_time;

                    const s = data.summary;
                    document.getElementById('openingCash').textContent = s.opening_cash;
                    document.getElementById('totalSales').textContent = s.total_sales;
                    document.getElementById('discount').textContent = s.discount;
                    document.getElementById('withdrawal').textContent = s.withdrawal_payment;
                    document.getElementById('upiPayment').textContent = s.upi_payment;
                    document.getElementById('totalCash').textContent = s.total_cash;
                    document.getElementById('refund').textContent = s.refund;
                    document.getElementById('credit').textContent = s.credit;
                    document.getElementById('refundCredit').textContent = s.refund_credit;

                    document.getElementById('systemCash').textContent = data.system_cash_sales;
                    document.getElementById('countedCash').textContent = data.counted_cash;
                    document.getElementById('discrepancyCash').textContent = data.discrepancy_cash;

                    // Denomination table
                    const tbody = document.getElementById('denominationRows');
                    tbody.innerHTML = '';
                    data.denominations.forEach(row => {
                        tbody.innerHTML += `
          <tr>
            <td>₹${row.note}</td>
            <td>${row.count}</td>
            <td>X</td>
            <td>₹${row.note}</td>
            <td>=</td>
            <td>₹${row.value}</td>
          </tr>`;
                    });
                    document.getElementById('cashTotal').textContent = data.counted_cash;

                    // Show the modal
                    const modal = new bootstrap.Modal(document.getElementById('shiftSummaryModal'));
                    modal.show();
                })
                .catch(err => console.error("Failed to load shift summary", err));
        }

        $('#closeButton').on('click', function() {
            $('#shiftSummaryModal').modal('hide');
        });

        // Function to get the image path
        function getImagePath(imageFile) {
            return '{{ asset('storage/shift-images/') }}/' + imageFile;
        }

        function showImage(imageUrl) {
            let defaultImg = "{{ asset('assets/images/no_img.jpg') }}"; // Your thumbnail image

            // Set image with fallback
            $('#modalImage')
                .attr('src', imageUrl ? imageUrl : defaultImg)
                .on('error', function() {
                    $(this).attr('src', defaultImg);
                });

             var modal = new bootstrap.Modal(document.getElementById('imageModal'));
            modal.show();
        }
    </script>
@endsection
