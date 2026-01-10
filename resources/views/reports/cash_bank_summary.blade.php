@extends('layouts.backend.layouts')

@section('page-content')
<style>
/* ================= TALLY PRIME UI ================= */
body { background: #f5f6f7; font-size: 13.5px; }
/* WINDOW */
.tally-window { border: 1px solid #6f6f6f; border-top: 0; background: #fff; overflow: hidden; /* 👈 VERY IMPORTANT */ }
/* HEADER BAR */
.tally-top { background: linear-gradient(#9fc5d1, #7faebd); padding: 6px 12px; font-weight: bold; border-bottom: 1px solid #6f6f6f; } 
/* HEADER AREA */
.tally-header { display: grid; grid-template-columns: 1fr 280px; align-items: center; padding: 0; } 
.tally-header-right { border-left: 1px solid #777; font-size: 13px; line-height: 1.4;}
.tally-header-right .title { font-weight: bold; text-align: center; margin-bottom: 3px; padding: 0 10px; }
.tally-header-right p { margin-bottom: 0; padding: 0 10px; }
/* BALANCE BOX */
.balance-box { border-top: 1px solid #777; margin-top: 6px; padding: 6px 10px ; } 
.balance-row { display: flex; justify-content: space-between; }
/* TABLE */
.tally-table { width: 100%; border-collapse: collapse; }
/* COLUMN WIDTH */
.group-row > td:first-child { padding-left: 10px;}
.col-particulars { width: 70%; }
.col-amt { width: 15%; }
/* GROUP */
.group-row { background: #f5d210; font-weight: bold; border-top: 1px solid #888; border-bottom: 1px solid #888; }
/* LEDGER */
.ledger-row td { padding-left: 30px; }
.group-row td:last-child,
.ledger-row td:last-child { padding-right: 20px; }
/* HOVER */
.group-row:hover, .ledger-row:hover { background: #fff3b0; }
/* TOTAL LINE */
.total-row { font-weight: bold; border-top: 2px solid #000; background: #f7f7f7; }
/* LINKS */
a { color: #000; text-decoration: none; }
a:hover { text-decoration: underline; }
/* ALIGN */
.text-right { text-align: right; }
.left-head { padding: 6px; font-weight: bold; font-size: 16px; letter-spacing: 1.5px; }
footer a { color: #32BDEA; text-decoration: none; }
.content-page.cash-bank-summary-page { padding: 90px 0 0;}
.cash-bank-summary-page .add-list.btn { padding: 4px 8px; font-size: 12px; }

</style>


    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="content-page cash-bank-summary-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                            <h4 class="mb-0">Cash/Bank Summary</h4>
                            <h5 class="title-table">Liqure HUB</h5>
                            <a href="{{ route('reports.list') }}" class="btn btn-primary add-list"> Back </a>
                        </div>
                    </div>

                    <div class="table-responsive mb-3">
                        <div class="col-lg-12">
                                <div class="tally-window">
                                    <!-- HEADER INFO -->
                                    <div class="tally-header">
                                        <div class="left-head"><b>Particulars</b></div>
                                        <div class="tally-header-right">
                                            <div class="title">Cash / Bank Summary</div>
                                            <p class="text-center"><b>{{ config('app.name') }}</b></p>
                                            <p class="text-center">As on {{ \Carbon\Carbon::parse($date)->format('d-M-Y') }}</p>
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
