@extends('layouts.backend.layouts')

@section('page-content')
    <style>
        /* ================= TALLY UI ================= */
        body {
            font-family: "Courier New", monospace;
            background: #ffffff;
            font-size: 14px;
        }

        /* WINDOW BAR */
        .tally-window {
            border: 1px solid #9e9e9e;
        }

        /* TOP BAR */
        .tally-top {
            background: #9fc5d1;
            padding: 4px 10px;
            display: flex;
            justify-content: space-between;
            font-weight: bold;
        }

        /* HEADER AREA */
        .tally-header {
            display: grid;
            grid-template-columns: 1fr 260px;
            border-bottom: 1px solid #aaa;
            padding: 6px 10px;
        }

        .tally-header-right {
            text-align: center;
            border-left: 1px solid #aaa;
        }

        .tally-header-right div {
            padding: 2px 0;
        }

        /* TABLE */
        .tally-table {
            width: 100%;
            border-collapse: collapse;
        }

        .tally-table th {
            text-align: left;
            padding: 4px 6px;
            border-bottom: 1px solid #aaa;
            font-weight: normal;
        }

        .tally-table td {
            padding: 3px 6px;
        }

        /* COLUMN WIDTH */
        .col-particulars {
            width: 70%;
        }

        .col-amt {
            width: 15%;
        }

        /* GROUP ROW */
        .group-row {
            background: #f4c542;
            font-weight: bold;
        }

        /* LEDGER ROW */
        .ledger-row td {
            padding-left: 30px;
        }

        /* ALIGN */
        .text-right {
            text-align: right;
        }

        /* HOVER */
        .group-row:hover,
        .ledger-row:hover {
            background: #fff1a8;
        }

        /* BORDER */
        .row-border td {
            border-bottom: 1px solid #ccc;
        }
    </style>


    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Cash/Bank Summary</h4>
                            </div>

                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="table-responsive rounded mb-3">

                            <div class="tally-window">

                                <!-- HEADER INFO -->
                                <div class="tally-header">
                                    <div><b>Particulars</b></div>

                                    <div class="tally-header-right">
                                        <div>Bank Accounts</div>
                                        <div><b>Jb</b></div>
                                        <div>For {{ \Carbon\Carbon::parse($date)->format('d-M-Y') }}</div>
                                        <div style="border-top:1px solid #aaa; margin-top:4px;">
                                            <b>Closing Balance</b>
                                        </div>
                                        <div style="display:flex; justify-content:space-between;">
                                            <span>Debit</span>
                                            <span>Credit</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- TABLE -->
                                <table class="tally-table">
                                    <tbody>
                                        @foreach ($result as $group)
                                            <!-- GROUP -->
                                            <tr class="group-row">
                                                <td class="col-particulars">{{ $group['group'] }}</td>
                                                <td class="col-amt text-right">{{ number_format($group['debit'], 2) }}</td>
                                                <td class="col-amt text-right">{{ number_format($group['credit'], 2) }}</td>
                                            </tr>

                                            <!-- LEDGERS -->
                                            @foreach ($group['ledgers'] as $ledger)
                                                <tr class="ledger-row">
                                                    <td><a href="{{ route('reports.monthly', $ledger['id']) }}">
                                                            {{ $ledger['name'] }}
                                                        </a>
                                                    </td>
                                                    <td class="text-right">
                                                        {{ $ledger['debit'] > 0 ? number_format($ledger['debit'], 2) : '' }}
                                                    </td>
                                                    <td class="text-right">
                                                        {{ $ledger['credit'] > 0 ? number_format($ledger['credit'], 2) : '' }}
                                                    </td>
                                                </tr>
                                            @endforeach

                                            <tr class="row-border">
                                                <td colspan="3"></td>
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
    </div>
@endsection
