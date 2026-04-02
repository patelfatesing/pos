@extends('layouts.backend.datatable_layouts')

@section('page-content')

    <style>
        /* ===== SAME STYLE AS LEDGER PAGE ===== */
        .tally-top-grid {
            display: flex;
            align-items: center;
            border: 1px solid #aaa;
            border-top: 0;
            border-bottom: none;
        }

        .content-page.group-summary-page {
            padding: 90px 0 0;
        }

        .left-head {
            padding: 6px;
            font-weight: bold;
            font-size: 16px;
            letter-spacing: 1.5px;
        }

        .right-head {
            border-left: 1px solid #aaa;
            display: block;
            margin-left: auto;
            width: 35%;
            max-width: 525px;
        }

        .ledger-info {
            text-align: center;
            padding: 4px 0;
            border-bottom: 1px solid #aaa;
            line-height: 1.4;
        }

        .right-bottom {
            display: flex;
        }

        .txn-head {
            border-right: 1px solid #aaa;
            width: 350px;
        }

        .txn-title {
            text-align: center;
            font-weight: bold;
            border-bottom: 1px solid #aaa;
            padding: 4px 0;
        }

        .txn-cols {
            display: flex;
        }

        .txn-cols div {
            flex: 1;
            text-align: center;
            padding: 4px 0;
            border-right: 1px solid #aaa;
        }

        .txn-cols div:last-child {
            border-right: none;
        }

        .closing-head {
            text-align: center;
            font-weight: bold;
            padding: 6px 0;
            width: 175px;
        }

        .tally-table {
            width: 100%;
            border-collapse: collapse;
            font-family: "Courier New", monospace;
            border: 1px solid #aaa;
        }

        .col-particulars {
            width: 65%;
            padding-left: 10px;
        }

        .col-dr,
        .col-cr,
        .col-closing {
            width: 175px;
        }

        .tally-table td {
            font-size: 14px;
            line-height: 27px;
        }

        .tally-table tr:hover {
            background: #fff3b0;
        }

        .tally-table a {
            color: #000;
            text-decoration: none;
        }

        .tally-table a:hover {
            text-decoration: underline;
        }

        .active-row {
            background: #f5d210 !important;
            font-weight: bold;
        }
        #gs_period:hover {
    text-decoration: underline;
    color: #007bff;
}
    </style>

    <div class="wrapper">
        <div class="content-page group-summary-page">
            <div class="container-fluid">

                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">

                    <h4 class="mb-0">Group Summary - {{ $group->name }}</h4>
                    {{-- <a href="{{ session('back_url') ?? route('reports.pnl_tally.view') }}" class="btn btn-secondary">
                        Back
                    </a> --}}
                    <button onclick="window.history.back()" class="btn btn-secondary">
                        Back
                    </button>
                </div>

                <div class="table-responsive mb-3">
                    {{-- HEADER GRID --}}
                    <div class="tally-top-grid">
                        <div class="left-head">Particulars</div>
                        <div class="right-head">
                            <div class="ledger-info">
                                <div>{{ $group->name }}</div>
                                <div><b>{{ config('app.name') }}</b></div>
                                <div>
                                    <span id="gs_period" style="cursor:pointer;">
                                        {{ request('start_date') }} to {{ request('end_date') }}
                                    </span>

                                    <input type="text" id="gs_daterange" style="position:absolute; opacity:0;">
                                </div>
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

                    {{-- TABLE --}}
                    <table class="tally-table">
                        <tbody>
                            @foreach ($ledgers as $ledger)
                                <tr class="{{ $loop->first ? 'active-row' : '' }}">
                                    <td class="col-particulars">
                                        <a
                                            href="{{ route('reports.monthly', [
                                                'ledger' => $ledger->id,
                                                'start_date' => request('start_date'),
                                                'end_date' => request('end_date'),
                                            ]) }}">

                                            {{ $ledger->name }}
                                        </a>
                                    </td>

                                    <td class="col-dr text-right">
                                        {{ number_format($ledger->dr, 2) }}
                                    </td>

                                    <td class="col-cr text-right">
                                        {{ number_format($ledger->cr, 2) }}
                                    </td>

                                    <td class="col-closing text-right">

                                        {{ number_format(abs($ledger->closing), 2) }}
                                        {{ $ledger->closing >= 0 ? 'Dr' : 'Cr' }}

                                    </td>

                                </tr>
                            @endforeach

                        </tbody>

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
$(document).ready(function () {

    let start = "{{ request('start_date') }}";
    let end   = "{{ request('end_date') }}";

    const groupId = "{{ $group->id }}";

    // ✅ init picker
    $('#gs_daterange').daterangepicker({
        startDate: moment(start),
        endDate: moment(end),
        locale: { format: 'YYYY-MM-DD' }
    });

    // ✅ click label → open picker
    $('#gs_period').on('click', function () {
        $('#gs_daterange').data('daterangepicker').show();
    });

    // ✅ on select → redirect (NO AJAX)
    $('#gs_daterange').on('apply.daterangepicker', function(ev, picker) {

        start = picker.startDate.format('YYYY-MM-DD');
        end   = picker.endDate.format('YYYY-MM-DD');

        // update label instantly
        $('#gs_period').text(start + ' to ' + end);

        // ✅ redirect with params
        const url = `/reports/group-summary/${groupId}?start_date=${start}&end_date=${end}`;

        window.location.href = url;
    });

});
</script>
@endsection