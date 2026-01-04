@extends('layouts.backend.layouts')

@section('page-content')
    <style>
        /* ===== TALLY COLUMN HEADER ===== */
        .tally-top-grid {
            display: grid;
            grid-template-columns: 45% 55%;
            border: 1px solid #aaa;
            border-bottom: none;
            font-family: "Courier New", monospace;
        }

        /* LEFT */
        .left-head {
            padding: 6px;
        }

        /* RIGHT WRAPPER */
        .right-head {
            border-left: 1px solid #aaa;
            display: grid;
            grid-template-rows: auto auto;
        }

        /* LEDGER INFO (TOP) */
        .ledger-info {
            text-align: center;
            padding: 4px 0;
            border-bottom: 1px solid #aaa;
            line-height: 1.4;
        }

        /* BOTTOM AREA */
        .right-bottom {
            display: grid;
            grid-template-columns: 70% 30%;
        }

        /* TRANSACTIONS */
        .txn-head {
            border-right: 1px solid #aaa;
        }

        .txn-title {
            text-align: center;
            font-weight: bold;
            border-bottom: 1px solid #aaa;
            padding: 4px 0;
        }

        .txn-cols {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .txn-cols div {
            text-align: center;
            padding: 4px 0;
            border-right: 1px solid #aaa;
        }

        .txn-cols div:last-child {
            border-right: none;
        }

        /* CLOSING */
        .closing-head {
            text-align: center;
            font-weight: bold;
            padding: 6px 0;
        }

        /* ===== TALLY TABLE ===== */
        /* ===== TABLE OUTER BORDER (MATCH HEADER) ===== */
        .tally-table {
            width: 100%;
            border-collapse: collapse;
            font-family: "Courier New", monospace;

            border-left: 1px solid #aaa;
            border-right: 1px solid #aaa;
            border-bottom: 1px solid #aaa;
        }


        /* COLUMN WIDTHS (MUST MATCH HEADER GRID) */
        .col-particulars {
            width: 30%;
        }

        .col-dr {
            width: 15%;
            border-left: 1px solid #aaa;
        }

        .col-cr {
            width: 15%;
            border-left: 1px solid #aaa;
        }

        .col-closing {
            width: 15%;
            border-left: 1px solid #aaa;
        }

        /* ROW STYLE */
        .tally-table td {
            /* padding: 4px 8px; */
            /* border-bottom: 1px solid #e0e0e0; */
        }

        /* OPENING BALANCE */
        .opening-row {
            font-weight: bold;
        }

        /* HOVER */
        .tally-table tr:hover {
            background: #fff3b0;
        }

        /* ACTIVE ROW (TALLY YELLOW BAR) */
        .active-row {
            background: #17aedf !important;
            font-weight: bold;
        }

        /* LINKS */
        .tally-table a {
            color: #000;
            text-decoration: none;
        }

        .tally-table a:hover {
            text-decoration: underline;
        }

        /* LEFT SIDE SPACING (PARTICULARS) */
        .col-particulars {
            padding-left: 10px;
        }

        /* RIGHT SIDE SPACING (CLOSING BALANCE) */
        .col-closing {
            padding-right: 10px;
        }
    </style>

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Ledger Monthly Summary - {{ $ledger->name }}</h4>
                            </div>
                            <a href="{{ route('reports.cash-bank.summary') }}" class="btn btn-primary add-list">
                                Back to
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="table-responsive rounded mb-3">
                            {{-- TALLY COLUMN HEADER --}}
                            <div class="tally-top-grid">
                                <div class="left-head">Particulars</div>

                                <div class="right-head">
                                    <div class="ledger-info">
                                        <div>{{ $ledger->name }}</div>
                                        <div><b>{{ config('app.name') }}</b></div>
                                        <div>For {{ \Carbon\Carbon::parse($months[0]['from'])->format('d-M-Y') }}</div>
                                    </div>

                                    <div class="right-bottom">
                                        <div class="txn-head">
                                            <div class="txn-title">Transactions</div>
                                            <div class="txn-cols">
                                                <div>Debit</div>
                                                <div>Credit</div>
                                            </div>
                                        </div>

                                        <div class="closing-head">
                                            Closing<br>Balance
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 🔥 TABLE MUST START HERE --}}
                            <table class="tally-table">
                                <tbody>
                                    {{-- OPENING BALANCE --}}
                                    <tr class="opening-row">
                                        <td class="col-particulars">Opening Balance</td>
                                        <td class="col-dr"></td>
                                        <td class="col-cr"></td>
                                        <td class="col-closing text-right">
                                            {{ number_format(abs($opening), 2) }}
                                            {{ $opening >= 0 ? 'Dr' : 'Cr' }}
                                        </td>
                                    </tr>

                                    {{-- MONTH ROWS --}}
                                    @foreach ($months as $m)
                                        <tr class="{{ $loop->first ? 'active-row' : '' }}">
                                            <td class="col-particulars">
                                                <a
                                                    href="{{ route('accounting.ledgers.vouchers', [
                                                        'ledger' => $ledgerId,
                                                        'start_date' => $m['from'],
                                                        'end_date' => $m['to'],
                                                    ]) }}">
                                                    {{ $m['month'] }}
                                                </a>
                                            </td>

                                            <td class="col-dr text-right">{{ number_format($m['dr'], 2) }}</td>
                                            <td class="col-cr text-right">{{ number_format($m['cr'], 2) }}</td>
                                            <td class="col-closing text-right">
                                                {{ number_format(abs($m['closing']), 2) }}
                                                {{ $m['closing'] >= 0 ? 'Dr' : 'Cr' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
