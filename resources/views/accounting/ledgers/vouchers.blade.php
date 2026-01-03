@extends('layouts.backend.datatable_layouts')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

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

        table.dataTable tbody tr:hover {
            background: #eef6f6;
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
            margin-top: 5px;
            background: #fff;
            border: 1px solid #999;
            padding: 6px 10px;
            font-family: monospace;
            font-size: 13px;
            min-width: 240px;
            z-index: 10;
        }

        .tally-footer-right table {
            width: 100%;
        }

        .tally-footer-right td {
            padding: 2px 4px;
        }

        .tally-footer-right td:last-child {
            text-align: right;
            font-weight: bold;
        }

        .tally-closing td {
            border-top: 1px solid #999;
            padding-top: 6px;
        }
    </style>
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <!-- ================= HEADER ================= -->
                <div class="tally-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Ledger Vouchers — {{ $ledger->name }}</h5>
                        <small id="dateRangeLabel">{{ $start }} to {{ $end }}</small>
                    </div>

                    <div class="controls">
                        <a href="{{ route('accounting.vouchers.create') }}" class="btn btn-outline-light btn-sm">
                            Add Voucher
                        </a>

                        <button id="printBtn" class="btn btn-outline-warning btn-sm">Print</button>

                        {{-- <input type="text" id="daterange" class="form-control d-inline-block" style="width:210px" />
                        <button id="applyFilter" class="btn btn-light btn-sm">Apply</button> --}}
                    </div>
                </div>

                <!-- ================= TABLE ================= -->
                <table id="vouchersTable" class="display" style="width:100%">
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
                            <td>Opening :</td>
                            <td id="openingBalance" class="text-right">-</td>
                        </tr>
                        <tr>
                            <td>Current :</td>
                            <td id="currentTotal" class="text-right">-</td>
                        </tr>
                        <tr class="tally-closing">
                            <td>Closing :</td>
                            <td id="closingBalance" class="text-right">-</td>
                        </tr>
                    </table>
                </div>


            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        window.onload = function() {

            $('#printBtn').on('click', function() {
                window.print();
            });

            let start = '{{ $start }}';
            let end = '{{ $end }}';

            function cb(s, e) {
                $('#daterange').val(s.format('YYYY-MM-DD') + ' - ' + e.format('YYYY-MM-DD'));
                $('#dateRangeLabel').text(s.format('YYYY-MM-DD') + ' to ' + e.format('YYYY-MM-DD'));
            }

            // $('#daterange').daterangepicker({
            //     startDate: moment(start),
            //     endDate: moment(end),
            //     locale: {
            //         format: 'YYYY-MM-DD'
            //     }
            // }, cb);

            // cb(moment(start), moment(end));

            let table = $('#vouchersTable').DataTable({
                processing: true,
                serverSide: false,
                ordering: false,
                pageLength: 50,

                ajax: {
                    url: "{{ route('accounting.ledgers.vouchers.data', $ledger->id) }}",
                    data: function(d) {
                        // let dr = $('#datserange').val().split(' - ');
                        d.start_date = start;
                        d.end_date = end;
                        d.vch_type = $('#vchType').val();
                    },
                    dataSrc: function(json) {

                        // ---- OPENING BALANCE ----
                        const opening = json.opening || {
                            balance: 0
                        };
                        $('#openingBalance').text(
                            (opening.balance >= 0 ? 'Dr ' : 'Cr ') +
                            Math.abs(opening.balance).toFixed(2)
                        );

                        // ---- PERIOD TOTAL ----
                        const period = json.period || {
                            total_debit: 0,
                            total_credit: 0
                        };
                        $('#currentTotal').text(
                            'Dr ' + (period.total_debit || 0).toFixed(2) +
                            ' | Cr ' + (period.total_credit || 0).toFixed(2)
                        );

                        // ---- CLOSING ----
                        const closing =
                            (opening.balance || 0) +
                            ((period.total_debit || 0) - (period.total_credit || 0));

                        $('#closingBalance').text(
                            (closing >= 0 ? 'Dr ' : 'Cr ') +
                            Math.abs(closing).toFixed(2)
                        );

                        return json.data || [];
                    }
                },

                columns: [{
                        data: 'date',
                        render: function(data, type, row) {
                            return row.type === 'detail' ? '' : data;
                        }
                    },
                    {
                        data: 'particulars',
                        className: 'col-particulars',
                        render: function(data, type, row) {
                            if (row.type === 'detail') {
                                return `<span style="padding-left:25px;">${data}</span>`;
                            }
                            return `<strong>${data || ''}</strong>`;
                        }
                    },
                    {
                        data: 'vch_type',
                        render: function(data, type, row) {
                            return row.type === 'detail' ? '' : data;
                        }
                    },
                    {
                        data: 'vch_no',
                        render: function(data, type, row) {
                            return row.type === 'detail' ? '' : data;
                        }
                    },
                    {
                        data: 'debit',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'credit',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    }
                ],

                createdRow: function(row, data) {
                    if (data.type === 'detail') {
                        $(row).css({
                            'color': '#555',
                            'background': '#fafafa'
                        });
                    }
                }
            });


            $('#applyFilter').on('click', function() {
                table.ajax.reload();
            });
        };
    </script>
@endsection
