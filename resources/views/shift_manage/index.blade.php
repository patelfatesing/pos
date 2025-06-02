@extends('layouts.backend.layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Wrapper Start -->
    <div class="wrapper">

        <div class="content-page">
            <div class="container-fluid">
                <div class="col-lg-12">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                        <div>
                            <h4 class="mb-3">Shift Manage</h4>
                        </div>
                    </div>
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
                <div class="table-responsive rounded mb-3">
                    <table class="table data-tables table-striped" id="shift_tbl">
                        <thead class="bg-white text-uppercase">
                            <tr class="ligth ligth-data">
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
                    </table>
                </div>
                <!-- Page end  -->
            </div>
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

                <div class="modal-body row">
                    <!-- Sales & Summary -->
                    <div class="col-md-6">
                        <h5>Sales Details</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <button class="btn btn-warning btn-sm">View Stock Status</button>
                           </div>

                        <div class="row border p-2 mb-3">
                            <div class="col-6"><strong>Sales</strong><br>IMFL: ₹<span id="imflSales">0</span></div>
                            <div class="col-6"><strong>Payment</strong><br>CASH: ₹<span id="cashPayment">0</span></div>
                        </div>

                        <table class="table table-bordered small">
                            <tbody>
                                <tr>
                                    <td>OPENING CASH</td>
                                    <td class="text-end">₹<span id="openingCash">0</span></td>
                                </tr>
                                <tr>
                                    <td>TOTAL SALES</td>
                                    <td class="text-end">₹<span id="totalSales">0</span></td>
                                </tr>
                                <tr>
                                    <td>DISCOUNT</td>
                                    <td class="text-end">₹<span id="discount">0</span></td>
                                </tr>
                                <tr>
                                    <td>WITHDRAWAL PAYMENT</td>
                                    <td class="text-end">₹<span id="withdrawal">0</span></td>
                                </tr>
                                <tr>
                                    <td>UPI PAYMENT</td>
                                    <td class="text-end">₹<span id="upiPayment">0</span></td>
                                </tr>
                                <tr class="table-success fw-bold">
                                    <td>TOTAL</td>
                                    <td class="text-end">₹<span id="totalCash">0</span></td>
                                </tr>
                                <tr>
                                    <td>REFUND</td>
                                    <td class="text-end">₹<span id="refund">0</span></td>
                                </tr>
                                <tr>
                                    <td>CREDIT</td>
                                    <td class="text-end">₹<span id="credit">0</span></td>
                                </tr>
                                <tr>
                                    <td>REFUND CREDIT</td>
                                    <td class="text-end">₹<span id="refundCredit">0</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Cash Breakdown -->
                    <div class="col-md-6">
                        <h5>💵 Cash Details</h5>
                        <p><strong>Start Time:</strong> <span id="startTime"></span><br>
                            <strong>End Time:</strong> <span id="endTime"></span>
                        </p>

                        <table class="table table-bordered text-center small">
                            <thead class="table-light">
                                <tr>
                                    <th>Denomination</th>
                                    <th>Notes</th>
                                    <th>x</th>
                                    <th>Amount</th>
                                    <th>=</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="denominationRows"></tbody>
                            <tfoot>
                                <tr class="table-success">
                                    <td colspan="5" class="text-end"><strong>Total</strong></td>
                                    <td><strong>₹<span id="cashTotal">0</span></strong></td>
                                </tr>
                            </tfoot>
                        </table>

                        <p><strong>System Cash Sales:</strong> ₹<span id="systemCash">0</span><br>
                            <strong>Total Cash Amount:</strong> ₹<span id="countedCash">0</span><br>
                        </p>

                        <div class="mb-2">
                            <label>Closing Cash</label>
                            <input type="number" class="form-control form-control-sm" id="closingCashInput">
                        </div>

                        <p><strong>Discrepancy Cash:</strong> ₹<span id="discrepancyCash">0</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0]; // format: YYYY-MM-DD
            document.getElementById('start_date').value = today;
            document.getElementById('end_date').value = today;
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
                columns: [{
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
                            return '₹' + parseFloat(data).toFixed(2);
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
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [1, 3, 4, 5, 6, 7] // make "action" column unsortable
                }],
                order: [
                    [2, 'desc']
                ], // Default order on shift_start DESC
                dom: "Bfrtip",
                lengthMenu: [
                    [10, 25, 50],
                    ['10 rows', '25 rows', '50 rows']
                ],
                buttons: ['pageLength']
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
            //                 Swal.fire('Closed!', 'Shift has been closed.', 'success');
            //                 $('#shift_tbl').DataTable().ajax.reload(null, false);
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
    </script>
@endsection
