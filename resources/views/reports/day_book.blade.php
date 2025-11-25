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
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-bordered mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 120px">Date</th>
                                        <th style="width: 90px">Voucher No</th>
                                        <th style="width: 120px">Voucher Type</th>
                                        <th>Ledger</th>
                                        <th class="text-end" style="width: 120px">Debit</th>
                                        <th class="text-end" style="width: 120px">Credit</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($entries as $row)
                                        <tr class="daybook-voucher-row" data-type="{{ $row['voucher_type'] ?? '' }}"
                                            data-id="{{ $row['id'] ?? '' }}" style="cursor:pointer">

                                            <td>
                                                {{ \Carbon\Carbon::parse($row['date'])->format('d-m-Y H:i') }}
                                            </td>
                                            <td>{{ $row['voucher_no'] }}</td>
                                            <td>{{ $row['voucher_type'] }}</td>
                                            <td>{{ $row['ledger'] }}</td>
                                            <td class="text-end">
                                                {{ $row['debit'] ? number_format($row['debit'], 2) : '' }}
                                            </td>
                                            <td class="text-end">
                                                {{ $row['credit'] ? number_format($row['credit'], 2) : '' }}
                                            </td>
                                            <td>{{ $row['remarks'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-3">
                                                No transactions found for selected period.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="4" class="text-end">Total</th>
                                        <th class="text-end">{{ number_format($totalDebit, 2) }}</th>
                                        <th class="text-end">{{ number_format($totalCredit, 2) }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- VOUCHER DETAIL MODAL --}}
            <div class="modal fade" id="voucherModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header py-2">
                            <h5 class="modal-title" id="voucherModalLabel">Voucher Details</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body p-2" id="voucherModalBody">
                            <div class="text-center text-muted py-5">
                                Loading...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script>
        // When you click any row, load voucher details via AJAX
        $(document).on('click', '.daybook-voucher-row', function() {

            var type = $(this).data('type');
            var id = $(this).data('id');

            if (!type || !id) {
                // If not mapped (e.g., opening balance row), do nothing
                return;
            }

            // Show loading and open modal
            $('#voucherModalLabel').text('Voucher Details');
            $('#voucherModalBody').html(
                '<div class="text-center text-muted py-5">Loading...</div>'
            );

            var modalEl = document.getElementById('voucherModal');
            var modal = new bootstrap.Modal(modalEl);
            modal.show();

            $.ajax({
                url: "{{ url('/reports/day-book/voucher') }}/" + type + "/" + id,
                type: 'GET',
                success: function(res) {
                    if (res.title) {
                        $('#voucherModalLabel').text(res.title);
                    }
                    $('#voucherModalBody').html(res.html);
                },
                error: function() {
                    $('#voucherModalBody').html(
                        '<div class="text-danger text-center py-5">Error loading voucher.</div>'
                    );
                }
            });
        });
    </script>
@endsection
