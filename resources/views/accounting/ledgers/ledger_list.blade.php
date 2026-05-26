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

        .btn-success {
            padding: 4px 8px;
            font-size: 12px;
        }

        /* .controls .btn {
                    background: #fff;
                    border: 1px solid #ccc;
                    color: #333;
                }

                .controls .btn-secondary {
                    background: #32BDEA;
                    border: 1px solid #32BDEA;
                    color: #fff;
                } */

        div.dataTables_wrapper {
            min-height: calc(100vh - 350px);
        }
    </style>
@endsection

@section('page-content')
    <div class="content-page accounting-ledgers-page">
        <div class="container-fluid">

            <!-- ================= HEADER ================= -->
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">

                <!-- LEFT SIDE -->
                <div class="ledger-title">
                    <h4 class="mb-0">Ledger Vouchers — {{ $ledger->name }}</h4>
                </div>

                <!-- CENTER TITLE -->
                <div class="text-center flex-grow-1">
                    <h5 class="title-table mb-0">LIQUOR HUB</h5>
                </div>

                <!-- RIGHT CONTROLS -->
                <div class="controls d-flex align-items-center gap-2">

                    <input type="text" id="daterange" class="form-control" style="width:210px" />

                    <a href="{{ route('accounting.vouchers.create') }}" class="btn btn-success btn-sm">
                        Add Voucher
                    </a>

                    <button id="printBtn" class="btn btn-success btn-sm">Print</button>

                    <button onclick="window.history.back()" class="btn btn-secondary btn-sm">
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

                        <th class="text-end">Debit</th>

                        <th class="text-end">Credit</th>

                        <th class="text-end">Balance</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($rows as $row)
                        <tr>

                            <td>
                                {{ $row['date'] }}
                            </td>

                            <td class="col-particulars">

                                <strong>
                                    {{ $row['particulars'] }}
                                </strong>

                                @if (!empty($row['narration']))
                                    <br>

                                    <small style="color:#777">

                                        {{ $row['narration'] }}

                                    </small>
                                @endif

                            </td>

                            <td class="text-center">

                                <a href="{{ $row['edit_url'] }}" style="color:#007bff; text-decoration:none;">

                                    {{ $row['voucher_type'] }}

                                </a>

                            </td>

                            <td class="text-center">

                                {{ $row['voucher_no'] }}

                            </td>

                            <td class="text-end">

                                @if ($row['debit'] > 0)
                                    {{ number_format($row['debit'], 2) }}
                                @endif

                            </td>

                            <td class="text-end">

                                @if ($row['credit'] > 0)
                                    {{ number_format($row['credit'], 2) }}
                                @endif

                            </td>

                            <td class="text-end">

                                {{ $row['balance'] }}

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7" class="text-center">

                                No voucher entries found

                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </table>

            <!-- ================= FOOTER ================= -->

            <div class="tally-footer-right">

                <table>

                    <tr>

                        <td>Opening Balance:</td>

                        <td>

                            {{ number_format(abs($openingBalance), 2) }}

                            {{ $openingBalance >= 0 ? 'Dr' : 'Cr' }}

                        </td>

                    </tr>

                    <tr>

                        <td>Total Debit:</td>

                        <td>

                            {{ number_format($totalDebit, 2) }}

                        </td>

                    </tr>

                    <tr>

                        <td>Total Credit:</td>

                        <td>

                            {{ number_format($totalCredit, 2) }}

                        </td>

                    </tr>

                    <tr class="tally-closing">

                        <td>Closing Balance:</td>

                        <td>

                            {{ number_format(abs($closingBalance), 2) }}

                            {{ $closingBalance >= 0 ? 'Dr' : 'Cr' }}

                        </td>

                    </tr>

                </table>

            </div>



        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        $(document).ready(function() {

            $('#vouchersTable').DataTable({

                ordering: false,

                paging: true,

                searching: true,

                info: false,

                pageLength: 50
            });

            let start = moment().subtract(29, 'days');

            let end = moment();

            function cb(start, end) {

                $('#daterange').val(
                    start.format('YYYY-MM-DD') +
                    ' to ' +
                    end.format('YYYY-MM-DD')
                );
            }

            $('#daterange').daterangepicker({

                startDate: start,

                endDate: end,

                ranges: {

                    'Today': [moment(), moment()],

                    'Last 7 Days': [
                        moment().subtract(6, 'days'),
                        moment()
                    ],

                    'Last 30 Days': [
                        moment().subtract(29, 'days'),
                        moment()
                    ],

                    'This Month': [
                        moment().startOf('month'),
                        moment().endOf('month')
                    ],

                    'Last Month': [
                        moment().subtract(1, 'month').startOf('month'),
                        moment().subtract(1, 'month').endOf('month')
                    ]
                }

            }, cb);

            cb(start, end);

            $('#daterange').on('apply.daterangepicker', function(ev, picker) {

                let from = picker.startDate.format('YYYY-MM-DD');

                let to = picker.endDate.format('YYYY-MM-DD');

                let url = new URL(window.location.href);

                url.searchParams.set('start_date', from);

                url.searchParams.set('end_date', to);

                window.location.href = url.toString();
            });
        });
    </script>
@endsection
