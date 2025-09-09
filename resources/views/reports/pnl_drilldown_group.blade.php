@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <h5 class="mb-2">Group: {{ $group->name }}</h5>
                <div class="text-muted mb-3">
                    Period: {{ $params['startDate'] }} to {{ $params['endDate'] }}
                    @if ($params['branchId'])
                        • Branch #{{ $params['branchId'] }}
                    @endif
                </div>

                <div class="card mb-3">
                    <div class="card-header"><strong>Ledger Summary</strong></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Ledger</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($summary as $row)
                                    <tr>
                                        <td>
                                            <a href="{{ route('reports.pnl.ledger', ['ledger' => $row['lid'], 'start_date' => $params['startDate'], 'end_date' => $params['endDate'], 'branch_id' => $params['branchId']]) }}"
                                                target="_blank">
                                                {{ $row['ledger'] }}
                                            </a>
                                        </td>
                                        <td class="text-right">{{ number_format($row['amount'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Total</th>
                                    <th class="text-right">{{ number_format($total, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><strong>Transactions</strong></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Doc No</th>
                                    <th>Source</th>
                                    <th>Ledger</th>
                                    <th class="text-right">Debit</th>
                                    <th class="text-right">Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tx as $t)
                                    <tr>
                                        <td>{{ $t->dt }}</td>
                                        <td>{{ $t->doc_no }}</td>
                                        <td>{{ $t->src }}</td>
                                        <td>{{ $t->ledger ?? '' }}</td>
                                        <td class="text-right">{{ number_format((float) $t->dr, 2) }}</td>
                                        <td class="text-right">{{ number_format((float) $t->cr, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No transactions</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
