@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <h5 class="mb-1">Ledger: {{ $ledger->name }}</h5>
                <div class="text-muted mb-3">
                    Group: {{ $ledger->group_name ?? '—' }} •
                    Period: {{ $params['startDate'] }} to {{ $params['endDate'] }}
                    @if ($params['branchId'])
                        • Branch #{{ $params['branchId'] }}
                    @endif
                </div>

                <div class="card mb-3">
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Doc No</th>
                                    <th>Source</th>
                                    <th class="text-right">Debit</th>
                                    <th class="text-right">Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $r)
                                    <tr>
                                        <td>{{ $r->dt }}</td>
                                        <td>{{ $r->doc_no }}</td>
                                        <td>{{ $r->src }}</td>
                                        <td class="text-right">{{ number_format((float) $r->dr, 2) }}</td>
                                        <td class="text-right">{{ number_format((float) $r->cr, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No transactions</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3">Totals</th>
                                    <th class="text-right">{{ number_format($sumDr, 2) }}</th>
                                    <th class="text-right">{{ number_format($sumCr, 2) }}</th>
                                </tr>
                                <tr>
                                    <th colspan="3">Balance (Dr - Cr)</th>
                                    <th colspan="2" class="text-right">{{ number_format($balance, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
