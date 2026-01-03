@extends('layouts.backend.datatable_layouts')

@section('styles')
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Day Book</h4>
                            </div>
                            <div>
                                <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="table-responsive rounded mb-3">

                            {{-- DAY BOOK TABLE --}}
                            <div class="card">
                                <div class="card-header py-2">
                                    <strong>Day Book</strong>
                                    <span class="float-end small">
                                        For {{ \Carbon\Carbon::parse($fromDate)->format('d-M-y') }}
                                    </span>
                                </div>

                                <div class="card-body p-0">
                                    <table class="table table-sm table-bordered mb-0" style="font-family: monospace;">
                                        <thead>
                                            <tr>
                                                <th style="width:90px">Date</th>
                                                <th>Particulars</th>
                                                <th style="width:90px">Vch Type</th>
                                                <th style="width:70px">Vch No.</th>
                                                <th class="text-end" style="width:120px">Debit Amount</th>
                                                <th class="text-end" style="width:120px">Credit Amount</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($entries as $e)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($e['date'])->format('d-M-y') }}</td>
                                                    <td>{{ $e['particulars'] }}</td>
                                                    <td>{{ $e['voucher_type'] }}</td>
                                                    <td>{{ $e['voucher_no'] }}</td>

                                                    <td class="text-end">
                                                        {{ $e['debit'] ? number_format($e['debit'], 2) : '' }}
                                                    </td>

                                                    <td class="text-end">
                                                        {{ $e['credit'] ? number_format($e['credit'], 2) : '' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <th colspan="4" class="text-end">Total</th>
                                                <th class="text-end">{{ number_format($totalDebit, 2) }}</th>
                                                <th class="text-end">{{ number_format($totalCredit, 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <h4 class="mb-3">Day Book</h4>


                {{-- SUMMARY CARDS --}}

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
                $("#voucherModal .modal-body").html(
                    '<div class="text-danger p-3">Error loading voucher.</div>');
                var modalEl = document.getElementById('voucherModal');
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
            });
        });

        $(document).on('click', '#deleteVoucherBtn', function() {
            if (!confirm('Delete voucher and all its lines? This cannot be undone.')) return;
            const id = $(this).data('id');
            $.ajax({
                url: '/accounting/vouchers/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    alert(res.message || 'Voucher deleted');
                    var modalEl = document.getElementById('voucherModal');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON
                        .message : 'Error deleting voucher';
                    alert(msg);
                }
            });
        });
    </script>
@endsection
