@extends('layouts.backend.datatable_layouts')

@section('styles')
    <style>
        .add-list {
            white-space: nowrap;
        }

        .custom-toolbar-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .custom-toolbar-row .dataTables_length {
            order: 1;
        }

        .custom-toolbar-row .dt-buttons {
            order: 2;
        }

        .custom-toolbar-row .status-filter {
            order: 3;
        }

        .custom-toolbar-row .dataTables_filter {
            order: 4;
            margin-left: auto;
        }

        .dataTables_wrapper .dataTables_filter label,
        .dataTables_wrapper .dataTables_length label {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 0;
        }

        .dt-buttons .btn {
            margin-right: 5px;
        }

        @media (max-width: 768px) {
            .custom-toolbar-row>div {
                flex: 1 1 100%;
                margin-bottom: 10px;
            }
        }
    </style>
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <!-- Page Header -->
                <div class="row align-items-center mb-3">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h5 class="mb-2">
                                    Ledger Detail — {{ $ledger->name }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>

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
        </div>
    </div>
@endsection
