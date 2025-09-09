@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <div class="container-fluid">
        <h5 class="mb-2">
            Ledger Detail — {{ $ledger->name }}
        </h5>
        <div class="mb-3 text-muted">
            Period: {{ $start_date }} to {{ $end_date }}
            @if ($branch_id)
                • Branch: {{ $branch_id }}
            @endif
        </div>

        <div class="card">
            <div class="card-body p-2">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Source</th>
                            <th>Reference</th>
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
                                <td>{{ $r->dc }}</td>
                                <td class="text-end">{{ number_format($r->amount, 2) }}</td>
                                <td>{{ $r->narration }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No transactions</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-2 text-end">
                    <strong>Total: {{ number_format($total, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
@endsection
