@extends('layouts.backend.layouts')

@section('page-content')
    <style>
        /* ================= TALLY PRIME UI ================= */
        body {
            font-family: "Courier New", monospace;
            background: #f5f6f7;
            font-size: 13.5px;
        }

        /* WINDOW */
        .tally-window {
            border: 1px solid #6f6f6f;
            border-radius: 6px;
            /* 👈 rounded corners */
            background: #fff;
            overflow: hidden;
            /* 👈 VERY IMPORTANT */
        }


        /* HEADER BAR */
        .tally-top {
            background: linear-gradient(#9fc5d1, #7faebd);
            padding: 6px 12px;
            font-weight: bold;
            border-bottom: 1px solid #6f6f6f;
        }

        /* HEADER AREA */
        .tally-header {
            display: grid;
            grid-template-columns: 1fr 280px;
            border-bottom: 1px solid #777;
            padding: 8px 12px;
        }

        .tally-header-right {
            border-left: 1px solid #777;
            padding-left: 10px;
            font-size: 13px;
        }

        .tally-header-right .title {
            font-weight: bold;
            text-align: center;
            margin-bottom: 3px;
        }

        /* BALANCE BOX */
        .balance-box {
            border-top: 1px solid #777;
            margin-top: 6px;
            padding-top: 6px;
        }

        .balance-row {
            display: flex;
            justify-content: space-between;
        }

        /* TABLE */
        .tally-table {
            width: 100%;
            border-collapse: collapse;
        }

        .tally-table td {
            padding: 4px 8px;
        }

        /* COLUMN WIDTH */
        .col-particulars {
            width: 70%;
        }

        .col-amt {
            width: 15%;
        }

        /* GROUP */
        .group-row {
            background: #32BDEA;
            font-weight: bold;
            border-top: 1px solid #888;
            border-bottom: 1px solid #888;
        }

        /* LEDGER */
        .ledger-row td {
            padding-left: 30px;
        }

        /* HOVER */
        .group-row:hover,
        .ledger-row:hover {
            background: #fff3b0;
        }

        /* TOTAL LINE */
        .total-row {
            font-weight: bold;
            border-top: 2px solid #000;
            background: #f7f7f7;
        }

        /* LINKS */
        a {
            color: #000;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* ALIGN */
        .text-right {
            text-align: right;
        }
    </style>


    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Cash/Bank Summary</h4>
                            </div>
                            <a href="{{ route('reports.list') }}" class="btn btn-primary add-list">
                                Back
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive rounded mb-3">
                        <div class="col-lg-12">
                          
                                <div class="tally-window">

                                    <!-- HEADER INFO -->
                                    <div class="tally-header">
                                        <div><b>Particulars</b></div>

                                        <div class="tally-header-right">
                                            <div class="title">Cash / Bank Summary</div>
                                            <div><b>{{ config('app.name') }}</b></div>
                                            <div>As on {{ \Carbon\Carbon::parse($date)->format('d-M-Y') }}</div>

                                            <div class="balance-box">
                                                <div class="balance-row">
                                                    <span>Debit</span>
                                                    <span>{{ number_format($totalDebit, 2) }}</span>
                                                </div>
                                                <div class="balance-row">
                                                    <span>Credit</span>
                                                    <span>{{ number_format($totalCredit, 2) }}</span>
                                                </div>

                                                <div class="balance-row" style="margin-top:4px;font-weight:bold;">
                                                    <span>Net Balance</span>
                                                    <span>
                                                        {{ $netBalance >= 0 ? 'Dr' : 'Cr' }}
                                                        {{ number_format(abs($netBalance), 2) }}
                                                    </span>
                                                </div>
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
                                                    <td class="col-amt text-right">
                                                        {{ number_format($group['debit'], 2) }}</td>
                                                    <td class="col-amt text-right">
                                                        {{ number_format($group['credit'], 2) }}</td>
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
    <!-- Wrapper End -->
@endsection
