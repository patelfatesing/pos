@extends('layouts.backend.layouts')

@section('page-content')
    <style>
        body {
            font-family: "Courier New", monospace;
        }

        .tally-header {
            display: flex;
            justify-content: space-between;
            background: #9fc5d1;
            padding: 6px 10px;
            border-bottom: 2px solid #888;
            font-weight: bold;
        }

        .table-wrap {
            border: 1px solid #aaa;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 4px 6px;
        }

        th {
            border-bottom: 1px solid #aaa;
        }

        .highlight {
            background: #f4c542;
        }

        .text-right {
            text-align: right;
        }

        .row-hover:hover {
            background: #fff3b0;
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
                                <i class="las la-plus mr-3"></i>Back to
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="table-responsive rounded mb-3">
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th width="40%">Particulars</th>
                                            <th width="20%" class="text-right">Debit</th>
                                            <th width="20%" class="text-right">Credit</th>
                                            <th width="20%" class="text-right">Closing Balance</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>Opening Balance</td>
                                            <td></td>
                                            <td></td>
                                            <td class="text-right">
                                                {{ number_format($months[0]['closing'] ?? 0, 2) }}
                                            </td>
                                        </tr>


                                        @foreach ($months as $month)
                                            <tr class="row-hover {{ $loop->first ? 'highlight' : '' }}">
                                                <td>
                                                    @php
                                                        // Financial year starts from April
                                                        $year = now()->year;
                                                        $monthNumber = $loop->iteration + 3;

                                                        if ($monthNumber > 12) {
                                                            $monthNumber -= 12;
                                                            $year += 1;
                                                        }

                                                        $startDate = \Carbon\Carbon::create($year, $monthNumber, 1)
                                                            ->startOfMonth()
                                                            ->format('Y-m-d');
                                                        $endDate = \Carbon\Carbon::create($year, $monthNumber, 1)
                                                            ->endOfMonth()
                                                            ->format('Y-m-d');
                                                    @endphp
                                                    <a
                                                        href="{{ route('accounting.ledgers.vouchers', [
                                                            'ledger' => $ledgerId,
                                                            'start_date' => $startDate,
                                                            'end_date' => $endDate,
                                                        ]) }}">
                                                        {{ $month['month'] }}
                                                    </a>

                                                </td>
                                                <td class="text-right">{{ number_format($month['dr'], 2) }}</td>
                                                <td class="text-right">{{ number_format($month['cr'], 2) }}</td>
                                                <td class="text-right">
                                                    {{ number_format($month['closing'], 2) }}
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
    </div>
@endsection
