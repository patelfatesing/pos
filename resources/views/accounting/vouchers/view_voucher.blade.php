@extends('layouts.backend.datatable_layouts')

@section('styles')
    <style>
        /* ================= TALLY LOOK ================= */
        body {
            background: #f2f2f2;
        }

        .tally-header {
            background: #32bdea;
            color: #fff;
            padding: 8px 12px;
            font-family: monospace;
            margin-bottom: 4px;
        }

        .tally-header h5 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .tally-header small {
            font-size: 12px;
            opacity: 0.9;
        }

        .controls .btn,
        .controls select,
        .controls input {
            font-size: 12px;
            height: 26px;
            padding: 2px 6px;
            margin-left: 4px;
        }

        /* ================= TABLE ================= */
        table.dataTable {
            font-family: monospace;
            font-size: 13px;
            background: #fff;
            border-collapse: collapse !important;
        }

        table.dataTable thead th {
            border-top: 1px solid #999 !important;
            border-bottom: 1px solid #999 !important;
            font-weight: bold;
            padding: 4px 6px;
        }

        table.dataTable tbody td {
            padding: 3px 6px;
            border: none !important;
            white-space: nowrap;
        }

        table.dataTable tbody tr.odd:hover,
        table.dataTable tbody tr.even:hover {
            background: #fff3b0 !important;
            color: #000;
        }

        table.dataTable tbody tr:hover {
            background: #fff3b0;
            color: #000;
        }

        .col-particulars {
            text-align: left;
            width: 40%;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .col-center {
            text-align: center;
        }

        .col-right {
            text-align: right;
        }

        .content-page.accounting-ledgers-page {
            padding: 90px 0 0;
            min-height: auto;
        }

        /* ================= FOOTER BALANCE ================= */
        .tally-balance {
            font-family: monospace;
            font-size: 13px;
            background: #ffffff;
            border-top: 1px solid #999;
            padding: 6px 10px;
            display: flex;
            justify-content: space-between;
        }

        /* Hide DataTable UI */
        .dataTables_length,
        .dataTables_filter,
        .dataTables_info,
        .dataTables_paginate {
            display: none;
        }

        /* === RIGHT SIDE FIXED TOTAL (TALLY STYLE) === */
        .tally-footer-right {
            position: sticky;
            bottom: 0;
            float: right;
            background: #fff;
            border: 1px solid #999;
            padding: 6px 10px;
            font-family: monospace;
            font-size: 13px;
            min-width: 240px;
            z-index: 10;
            width: 100%;
            border-width: 1px 0px;
        }

        .tally-footer-right table {
            width: 100%;
            border-collapse: collapse;
        }

        .tally-footer-right td {
            padding: 2px 4px;
        }

        .tally-footer-right td:first-child {
            width: 85%;
            text-align: right;
        }

        .tally-footer-right td:last-child {
            text-align: left;
            font-weight: bold;
        }

        .tally-closing td {
            border-top: 1px solid #999;
            padding-top: 6px;
        }

        .accounting-ledgers-page table.dataTable {
            margin: 0 !important;
        }

        .ledger-title {
            font-size: 0;
        }

        .ledger-title p {
            margin: 0;
            font-size: 14px;
            line-height: 1.2;
        }

        .controls .btn {
            background: #fff;
            border: 1px solid #ccc;
            color: #333;
        }

        .controls .btn-secondary {
            background: #32BDEA;
            border: 1px solid #32BDEA;
            color: #fff;
        }

        div.dataTables_wrapper {
            min-height: calc(100vh - 350px);
        }
    </style>
@endsection
@php
    if ($voucher->voucher_type == 'Purchase') {
        if ($voucher->gen_id != null) {
            $editUrl = route('purchase.edit', $voucher->gen_id);
        } else {
            $editUrl = route('accounting.vouchers.edit', $voucher->id);
        }
    } elseif ($voucher->voucher_type == 'Sales') {
        $editUrl = route('sales.edit-sales', $voucher->gen_id);
    } else {
        $editUrl = route('accounting.vouchers.edit', $voucher->id);
    }
@endphp

<a href="{{ $editUrl }}" class="btn btn-secondary btn-sm">
    ✏️ Edit
</a>
@section('page-content')
    <div class="wrapper">
        <div class="content-page accounting-ledgers-page">
            <div class="container-fluid">

                <!-- ================= HEADER ================= -->
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="ledger-title">
                        <h4 class="mb-0">Ledger Vouchers — {{ $voucher->name }}</h4>
                        {{-- <p><small id="dateRangeLabel">{{ $start }} to {{ $end }}</small></p> --}}
                    </div>
                    <h5 class="title-table">LIQUOR HUB</h5>

                    <div class="controls d-flex align-items-center gap-2 flex-nowrap">
                        @if (auth()->user()->role_id == 1 || canCreate(auth()->user()->role_id, 'accounting-voucher-create'))
                            <!-- Add Voucher -->
                            <a href="{{ route('accounting.vouchers.create') }}" class="btn btn-outline-light btn-sm">
                                Add Voucher
                            </a>
                        @endif
                        @if (auth()->user()->role_id == 1 || canCreate(auth()->user()->role_id, 'shift-verify'))
                            <!-- Verify Checkbox -->
                            <div class="d-flex align-items-center gap-1 verify-box voucher">
                                <input type="checkbox"
                                    onchange="changeVoucherVerifyStatus({{ $voucher->id }}, this.checked)"
                                    {{ $voucher->admin_status == 'verify' ? 'checked' : '' }}>

                                <span onclick="handleVoucherClick({{ $voucher->id }})">
                                    ✔ Verify
                                </span>
                            </div>
                        @endif
                        @if (auth()->user()->role_id == 1 || canCreate(auth()->user()->role_id, 'accounting-voucher-edit'))
                            <a href="{{ $editUrl }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif

                        <!-- Back Button -->
                        <button onclick="window.history.back()" class="btn btn-secondary">
                            Back
                        </button>

                    </div>
                </div>

                <!-- ================= TABLE ================= -->
                <table id="vouchersTable" class="display border" style="width:100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Particulars</th>
                            <th>Vch Type</th>
                            <th>Vch No</th>
                            <th>Debit</th>
                            <th>Credit</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <!-- ================= BALANCE FOOTER ================= -->
                <div class="tally-footer-right">
                    <table>
                        <tr>
                            <td>Opening Balance:</td>
                            <td id="openingBalance">-</td>
                        </tr>
                        <tr>
                            <td>Current Balance:</td>
                            <td id="currentTotal">-</td>
                        </tr>
                        <tr class="tally-closing">
                            <td>Closing Balance:</td>
                            <td id="closingBalance">-</td>
                        </tr>
                    </table>
                </div>


            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        window.onload = function() {

            let start = '';
            let end = '';

            let table = $('#vouchersTable').DataTable({
                processing: true,
                serverSide: false,
                ordering: false,
                pageLength: 50,

                ajax: {
                    url: "{{ route('accounting.ledgers.particular.vouchers.data', $voucher->id) }}",
                    data: function(d) {
                        d.start_date = start;
                        d.end_date = end;
                    },
                    dataSrc: function(json) {

                        /* ---------- OPENING ---------- */
                        const opening = json.opening || {
                            balance: 0
                        };
                        $('#openingBalance').text(
                            (opening.balance >= 0 ? 'Dr ' : 'Cr ') +
                            Math.abs(opening.balance)
                        );

                        /* ---------- CURRENT ---------- */
                        const period = json.period || {
                            total_debit: 0,
                            total_credit: 0
                        };
                        $('#currentTotal').text(
                            'Dr ' + period.total_debit +
                            ' | Cr ' + period.total_credit
                        );

                        /* ---------- CLOSING ---------- */
                        const closing =
                            opening.balance +
                            (period.total_debit - period.total_credit);

                        $('#closingBalance').text(
                            (closing >= 0 ? 'Dr ' : 'Cr ') +
                            Math.abs(closing)
                        );

                        return json.data || [];
                    }
                },

                columns: [{
                        data: 'date',
                        render: (d, t, r) => r.type === 'main' ? d : ''
                    },
                    {
                        data: 'particulars',
                        className: 'col-particulars',
                        render: function(d, t, r) {
                            if (r.type === 'detail') {
                                return `<span style="padding-left:30px;">${d}</span>`;
                            }
                            return `<strong>${d}</strong>`;
                        }
                    },
                    {
                        data: 'vch_type',
                        render: function(d, t, r) {
                            if (r.type === 'main') {
                                return `<a href="${r.edit_url}" 
                       style="color:#007bff;text-decoration:none;">
                        ${d}
                    </a>`;
                            }
                            return '';
                        }
                    },
                    {
                        data: 'vch_no',
                        render: (d, t, r) => r.type === 'main' ? d : ''
                    },
                    {
                        data: 'debit',
                        className: 'text-end',
                        render: (d, t, r) =>
                            r.type === 'main' && d !== null ?
                            parseFloat(d) : ''
                    },
                    {
                        data: 'credit',
                        className: 'text-end',
                        render: (d, t, r) =>
                            r.type === 'main' && d !== null ?
                            parseFloat(d) : ''
                    }
                ],
                createdRow: function(row, data) {
                    if (data.type === 'details_header') {
                        $(row).css({
                            color: '#555',
                            background: '#f7f7f7'
                        });
                    }
                    if (data.type === 'detail') {
                        $(row).css('background', '#fafafa');
                    }
                }
            });
        };

        function changeVoucherVerifyStatus(voucherId, isChecked) {

            let status = isChecked ? 'verify' : 'unverify';

            Swal.fire({
                title: 'Are you sure?',
                text: isChecked ? "Verify this voucher?" : "Unverify this voucher?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, confirm'
            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({
                        url: '/accounting/vouchers/update-status',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: voucherId,
                            status: status
                        },
                        success: function(res) {
                            Swal.fire(
                                'Success!',
                                'Voucher status updated',
                                'success'
                            );
                        },
                        error: function() {
                            Swal.fire(
                                'Error!',
                                'Something went wrong',
                                'error'
                            );
                        }
                    });

                } else {
                    // ❌ revert checkbox if cancelled
                    let checkbox = document.querySelector(`input[onchange*="${voucherId}"]`);
                    checkbox.checked = !isChecked;
                }

            });
        }

        function handleVoucherClick(voucherId) {
            // optional: toggle checkbox when clicking text
            let checkbox = document.querySelector(`input[onchange*="${voucherId}"]`);
            checkbox.checked = !checkbox.checked;
            checkbox.dispatchEvent(new Event('change'));
        }
    </script>
@endsection
