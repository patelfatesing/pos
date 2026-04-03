@extends('layouts.backend.datatable_layouts')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <style>
        /* ================= TALLY LOOK ================= */
        body {
            background: #f2f2f2;
        }

        .tally-header {
            background: #AFBEFA;
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
            /* font-size: 12px;
            height: 26px;
            padding: 2px 6px;
            margin-left: 4px; */
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
            /* background: #fff; */
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

@section('page-content')
    <div class="wrapper">
        <div class="content-page accounting-ledgers-page">
            <div class="container-fluid">

                <!-- ================= HEADER ================= -->
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="ledger-title">
                        <h4 class="mb-0">Ledger Vouchers — {{ $ledger->name }}</h4>
                        <p class="mb-0">
                            <small id="dateRangeLabel" style="cursor:pointer;">
                                {{ $start }} to {{ $end }}
                            </small>
                        </p>

                        <!-- hidden input (required for plugin) -->
                        <input type="text" id="daterange" style="position:absolute; opacity:0;" />
                    </div>
                    <h5 class="title-table">LIQUOR HUB</h5>
                    <div class="controls">
                        <a href="{{ route('accounting.vouchers.create') }}" class="btn btn-success btn-sm">
                            Add Voucher
                        </a>

                        <button id="printBtn" class="btn btn-success btn-sm">Print</button>
                        <button onclick="window.history.back()" class="btn btn-warning btn-sm">
                            Back
                        </button>

                        {{-- <input type="text" id="daterange" class="form-control d-inline-block" style="width:210px" />
                        <button id="applyFilter" class="btn btn-light btn-sm">Apply</button> --}}
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
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        let table;
        let start = '{{ $start }}';
        let end = '{{ $end }}';

        $(document).ready(function() {

            // ✅ Init Date Range Picker
            $('#daterange').daterangepicker({
                startDate: moment(start),
                endDate: moment(end),
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });

            $('#dateRangeLabel').on('click', function() {
                $('#daterange').data('daterangepicker').show();
            });

            // ✅ Auto reload when date selected
            $('#daterange').on('apply.daterangepicker', function(ev, picker) {

                start = picker.startDate.format('YYYY-MM-DD');
                end = picker.endDate.format('YYYY-MM-DD');

                // update label
                $('#dateRangeLabel').text(start + ' to ' + end);

                // reload table
                table.ajax.reload();
            });

            // ✅ DataTable
            table = $('#vouchersTable').DataTable({
                processing: true,
                serverSide: false,
                ordering: false,
                pageLength: 50,

                ajax: {
                    url: "{{ route('accounting.ledgers.vouchers.data', $ledger->id) }}",
                    data: function(d) {
                        d.start_date = start;
                        d.end_date = end;
                    },
                    dataSrc: function(json) {

                        const opening = json.opening || {
                            balance: 0
                        };
                        $('#openingBalance').text(
                            (opening.balance >= 0 ? 'Dr ' : 'Cr ') +
                            Math.abs(opening.balance)
                        );

                        const period = json.period || {
                            total_debit: 0,
                            total_credit: 0
                        };
                        $('#currentTotal').text(
                            'Dr ' + period.total_debit +
                            ' | Cr ' + period.total_credit
                        );

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
                            return r.type === 'detail' ?
                                `<span style="padding-left:30px;">${d}</span>` :
                                `<strong>${d}</strong>`;
                        }
                    },
                    {
                        data: 'vch_type',
                        render: function(d, t, r) {
                            return r.type === 'main' ?
                                `<a href="${r.edit_url}" style="color:#007bff;">${d}</a>` :
                                '';
                        }
                    },
                    {
                        data: 'vch_no',
                        render: (d, t, r) => r.type === 'main' ? d : ''
                    },
                    {
                        data: 'debit',
                        className: 'text-end',
                        render: (d, t, r) => r.type === 'main' && d ? parseFloat(d) : ''
                    },
                    {
                        data: 'credit',
                        className: 'text-end',
                        render: (d, t, r) => r.type === 'main' && d ? parseFloat(d) : ''
                    }
                ]
            });

        });
    </script>
@endsection
