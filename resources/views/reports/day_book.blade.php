@extends('layouts.backend.datatable_layouts')

@section('styles')
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <h4 class="mb-3">Day Book</h4>

                {{-- FILTERS --}}
                <form method="GET" action="{{ route('reports.day-book') }}" class="card mb-3 p-3">
                    <div class="row g-2 align-items-end">
                        {{-- From Date --}}
                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                        </div>

                        {{-- To Date --}}
                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                        </div>

                        {{-- Branch (optional) --}}
                        <div class="col-md-3">
                            <label class="form-label">Branch (optional)</label>
                            <select name="branch_id" class="form-select">
                                <option value="">All Branches</option>
                                @foreach (\App\Models\Branch::all() as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ (string) $branchId === (string) $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Opening Balance --}}
                        <div class="col-md-3">
                            <label class="form-label">Opening Balance</label>
                            <input type="number" step="0.01" name="opening_balance" class="form-control"
                                value="{{ $openingBalance }}">
                        </div>

                        <div class="col-12 mt-2">
                            <button class="btn btn-primary btn-sm">
                                <i class="bi bi-search"></i> Show
                            </button>
                            <a href="{{ route('reports.day-book') }}" class="btn btn-outline-secondary btn-sm">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                {{-- SUMMARY CARDS --}}
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <div class="small text-muted">Opening Balance</div>
                            <div class="fw-bold">{{ number_format($openingBalance, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <div class="small text-muted">Total Debit (Cash In)</div>
                            <div class="fw-bold">{{ number_format($totalDebit, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <div class="small text-muted">Total Credit (Cash Out)</div>
                            <div class="fw-bold">{{ number_format($totalCredit, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded bg-light">
                            <div class="small text-muted">Closing Balance</div>
                            <div class="fw-bold">
                                {{ number_format($closingBalance, 2) }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DAY BOOK TABLE --}}
                <div class="card">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Day Book Entries</strong>
                            <span class="text-muted small">
                                ({{ \Carbon\Carbon::parse($fromDate)->format('d-m-Y') }}
                                to
                                {{ \Carbon\Carbon::parse($toDate)->format('d-m-Y') }})
                            </span>
                        </div>
                        <div class="small text-muted">
                            Click any row to view full voucher (like Tally)
                        </div>
                    </div>

                    <div class="card-body p-2">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Voucher Type</th>
                                    <th>Ref No</th>
                                    <th>Ledger</th>
                                    <th class="text-end">Dr</th>
                                    <th class="text-end">Cr</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($entries as $e)
                                    <tr class="open-voucher" data-id="{{ $e['voucher_id'] }}" style="cursor:pointer;">

                                        <td>{{ date('d-m-Y', strtotime($e['date'])) }}</td>
                                        <td>{{ $e['voucher_type'] }}</td>
                                        <td>{{ $e['ref_no'] }}</td>
                                        <td>{{ $e['ledger'] }}</td>

                                        {{-- show totals (one row per voucher) --}}
                                        <td class="text-end">{{ number_format($e['debit'] ?? 0, 2) }}</td>
                                        <td class="text-end">{{ number_format($e['credit'] ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr>
                                    <th colspan="4">Total</th>
                                    <th class="text-end">{{ number_format($totalDebit, 2) }}</th>
                                    <th class="text-end">{{ number_format($totalCredit, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>

                    </div>
                </div>
            </div>

            {{-- VOUCHER DETAIL MODAL --}}
            <div class="modal fade" id="voucherModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">Voucher Details</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            Loading...
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection


@section('scripts')
    <script>
        $(document).on("click", ".open-voucher", function() {
            let id = $(this).data("id");

            $.get("/reports/day-book/voucher/" + id, function(res) {
                $("#voucherModal .modal-title").text(res.title);
                $("#voucherModal .modal-body").html(res.html);

                var modalEl = document.getElementById('voucherModal');
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
            }).fail(function() {
                $("#voucherModal .modal-title").text('Error');
                $("#voucherModal .modal-body").html('<div class="text-danger p-3">Error loading voucher.</div>');
                var modalEl = document.getElementById('voucherModal');
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
            });
        });
    </script>
@endsection
