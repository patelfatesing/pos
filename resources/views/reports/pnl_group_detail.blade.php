@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <div class="container-fluid">
        <h5 class="mb-2">
            Group Detail — {{ ucfirst($section) }} • {{ $group->name }}
        </h5>
        <div class="mb-3 text-muted">
            Period: {{ $start_date }} to {{ $end_date }}
            @if ($branch_id)
                • Branch: {{ $branch_id }}
            @endif
        </div>

        <div class="card mb-3">
            <div class="card-body p-2">
                <strong>Ledgers in this group</strong>
                <table class="table table-sm mt-2">
                    <thead>
                        <tr>
                            <th>Ledger</th>
                            <th class="text-end">Amount</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ledgers as $l)
                            <tr>
                                <td>{{ $l['ledger_name'] }}</td>
                                <td class="text-end">{{ number_format($l['amount'], 2) }}</td>
                                <td class="text-end">
                                    <a class="btn btn-link btn-sm"
                                        href="{{ route('reports.pnl.ledger', ['ledger_id' => $l['ledger_id'], 'start_date' => $start_date, 'end_date' => $end_date, 'branch_id' => $branch_id]) }}"
                                        target="_blank">View ledger</a>
                                </td>
                            </tr>
                        @endforeach
                        @if (!count($ledgers))
                            <tr>
                                <td colspan="3" class="text-center text-muted">No data</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-2">
                <strong>Transactions</strong>
                <table class="table table-sm mt-2">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Source</th>
                            <th>Reference</th>
                            <th>Ledger</th>
                            <th>Dr/Cr</th>
                            <th class="text-end">Amount</th>
                            <th>Narration</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $r)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($r->tx_date)->toDateString() }}</td>
                                <td>{{ $r->source }}</td>
                                <td>{{ $r->ref_no ?? '—' }}</td>
                                <td>{{ $r->ledger_name }}</td>
                                <td>{{ $r->dc }}</td>
                                <td class="text-end">{{ number_format($r->amount, 2) }}</td>
                                <td>{{ $r->narration }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No transactions</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
