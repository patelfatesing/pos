@extends('layouts.backend.datatable_layouts')

@section('styles')
@endsection

<style>
.content-page.daybook-page { padding: 90px 0 0; min-height: calc(100% - 97px); }
.daybook-page .card-header,
.daybook-header { display: flex; align-items: center; gap: 15px; justify-content: space-between; }
.daybook-header h4 { font-size: 18px; line-height: 22px; }
.daybook-page .card .card-header { background: #528da1; border-top-left-radius: 10px; border-top-right-radius: 10px; color: #fff; }
.title-table { font-size: 14px; font-weight: bold; margin: 0; }
.text-end { text-align: right; }
.daybook-page .table thead th { vertical-align: top; }
.daybook-page .table thead th span { border-top: 1px solid #000; display: block; margin-top: 3px; }
.daybook-page .table td { padding: 4.8px !important; }

</style>
@section('page-content')
    <div class="wrapper">
        <div class="content-page daybook-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex daybook-header flex-wrap align-items-center justify-content-between mb-2">
                            <h4 class="mb-0">Day Book</h4>
                            <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="table-responsive rounded mb-3">

                            {{-- DAY BOOK TABLE --}}
                            <div class="card">
                                <div class="card-header py-2">
                                    <strong>Day Book</strong>
                                    <h5 class="title-table">Liqure HUB</h5>
                                    <span class="float-end small">
                                        For {{ \Carbon\Carbon::parse($fromDate)->format('d-M-y') }}
                                    </span>
                                </div>

                                <div class="card-body p-0">
                                    <table class="table table-sm mb-0" style="font-family: monospace;">
                                        <thead>
                                            <tr>
                                                <th style="width:90px">Date</th>
                                                <th>Particulars</th>
                                                <th style="width:90px">Vch Type</th>
                                                <th style="width:70px">Vch No.</th>
                                                <th class="text-end" style="width:120px">Debit Amount <span>Inward Qty</span></th>
                                                <th class="text-end" style="width:120px">Credit Amount <span>Outwards Qty</span></th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($entries as $e)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($e['date'])->format('d-M-y') }}</td>
                                                    <td><a href="{{ route('accounting.vouchers.edit', $e['ledger_id']) }}"
                                                            class="text-primary text-decoration-none fw-bold">
                                                            {{ $e['particulars'] }}
                                                        </a>
                                                    </td>
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

                                        {{-- <tfoot>
                                            <tr>
                                                <th colspan="4" class="text-end">Total</th>
                                                <th class="text-end">{{ number_format($totalDebit, 2) }}</th>
                                                <th class="text-end">{{ number_format($totalCredit, 2) }}</th>
                                            </tr>
                                        </tfoot> --}}
                                    </table>
                                </div>
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
