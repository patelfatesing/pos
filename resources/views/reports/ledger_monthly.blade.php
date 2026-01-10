@extends('layouts.backend.layouts')

@section('page-content')
<style>
/* ===== TALLY COLUMN HEADER ===== */ 
.tally-top-grid { display: flex; align-items: center; border: 1px solid #aaa; border-top: 0; border-bottom: none; }
.content-page.ledger-monthly-page { padding: 90px 0 0;}
.ledger-monthly-page .card-header { padding: .5rem .9rem;}
.ledger-monthly-page .add-list.btn { padding: 4px 8px; font-size: 12px; }
/* LEFT */ 
.left-head { padding: 6px; font-weight: bold; font-size: 16px; letter-spacing: 1.5px; } 
/* RIGHT WRAPPER */ 
.right-head { border-left: 1px solid #aaa; display: block; margin-left: auto; width: 35%; max-width: 525px; }
/* LEDGER INFO (TOP) */
.ledger-info { text-align: center; padding: 4px 0; border-bottom: 1px solid #aaa; line-height: 1.4; }
/* BOTTOM AREA */
.right-bottom { display: flex; }
/* TRANSACTIONS */
.txn-head { border-right: 1px solid #aaa; width: 350px; }
.txn-title { text-align: center; font-weight: bold; border-bottom: 1px solid #aaa; padding: 4px 0; }
.txn-cols { display: flex; }
.txn-cols div { flex: 1; text-align: center; padding: 4px 0; border-right: 1px solid #aaa; }
.txn-cols div:last-child { border-right: none; }
/* CLOSING */
.closing-head { text-align: center; font-weight: bold; padding: 6px 0; width: 175px; }
/* ===== TALLY TABLE ===== */
/* ===== TABLE OUTER BORDER (MATCH HEADER) ===== */
.tally-table { width: 100%; border-collapse: collapse; font-family: "Courier New", monospace; border: 1px solid #aaa; }
/* COLUMN WIDTHS (MUST MATCH HEADER GRID) */
.col-particulars { width: 65%; }
.col-dr, .col-cr, .col-closing { width: 175px; }
/* OPENING BALANCE */
.opening-row { font-weight: bold; }
/* HOVER */
.tally-table tr:hover { background: #fff3b0; }
.tally-table td { font-size: 14px; line-height: 27px; padding-top: 0; padding-bottom: 0;}
/* ACTIVE ROW (TALLY YELLOW BAR) */
.active-row { background: #f5d210 !important; font-weight: bold; }
.opening-row .col-particulars { font-style: italic; }
/* LINKS */
.tally-table a { color: #000; text-decoration: none; }
.tally-table a:hover { text-decoration: underline; }
/* LEFT SIDE SPACING (PARTICULARS) */
.col-particulars { padding-left: 10px; }
/* RIGHT SIDE SPACING (CLOSING BALANCE) */
.col-closing { padding-right: 10px; }


</style>
<div class="wrapper">
    <div class="content-page ledger-monthly-page">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                        <h4 class="mb-0">Ledger Monthly Summary - {{ $ledger->name }}</h4>
                        <h5 class="title-table">Liqure HUB</h5>
                        <a href="{{ route('reports.cash-bank.summary') }}" class="btn btn-primary add-list">
                        Back to </a>
                </div>
                <div class="col-lg-12 p-0">
                    <div class="table-responsive mb-3">
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