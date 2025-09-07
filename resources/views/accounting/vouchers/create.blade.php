@extends('layouts.backend.layouts')

@section('page-content')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        .badge-balance {
            font-size: 12px;
        }

        .table tfoot input[readonly] {
            background: #f8f9fa;
        }
    </style>

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid add-form-list">
                <div class="row">
                    <div class="col-sm-12">

                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">New Voucher</h4>
                                    <div id="balanceBadge" class="mt-1">
                                        <span class="badge badge-balance bg-secondary">Not Calculated</span>
                                    </div>
                                </div>
                                <div><a href="{{ route('accounting.vouchers.index') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>

                            <div class="card-body">
                                @if ($errors->any())
                                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                                @endif

                                <form action="{{ route('accounting.vouchers.store') }}" method="POST" id="voucherForm">
                                    @csrf

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Date</label>
                                            <input type="date" class="form-control" name="voucher_date"
                                                value="{{ old('voucher_date', now()->toDateString()) }}" required>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Type</label>
                                            <select name="voucher_type" class="form-control" required>
                                                @foreach (['Journal', 'Payment', 'Receipt', 'Contra', 'Sales', 'Purchase', 'DebitNote', 'CreditNote'] as $t)
                                                    <option value="{{ $t }}" @selected(old('voucher_type') === $t)>
                                                        {{ $t }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Ref No</label>
                                            <input type="text" class="form-control" name="ref_no"
                                                value="{{ old('ref_no') }}">
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Branch</label>
                                            <select name="branch_id" class="form-control">
                                                <option value="">All / None</option>
                                                @foreach ($branches ?? [] as $b)
                                                    <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>
                                                        {{ $b->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Narration</label>
                                        <textarea name="narration" class="form-control" rows="2">{{ old('narration') }}</textarea>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle" id="linesTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:35%">Ledger</th>
                                                    <th style="width:10%">Dr/Cr</th>
                                                    <th style="width:20%">Amount</th>
                                                    <th>Narration</th>
                                                    <th style="width:5%"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $oldLines = old('lines', []); @endphp
                                                @if ($oldLines)
                                                    @foreach ($oldLines as $i => $ln)
                                                        <tr class="line">
                                                            <td>
                                                                <select name="lines[{{ $i }}][ledger_id]"
                                                                    class="form-control ledger">
                                                                    @foreach ($ledgers as $l)
                                                                        <option value="{{ $l->id }}"
                                                                            @selected($ln['ledger_id'] == $l->id)>
                                                                            {{ $l->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <select name="lines[{{ $i }}][dc]"
                                                                    class="form-control dc">
                                                                    <option @selected(($ln['dc'] ?? 'Dr') === 'Dr')>Dr</option>
                                                                    <option @selected(($ln['dc'] ?? 'Dr') === 'Cr')>Cr</option>
                                                                </select>
                                                            </td>
                                                            <td><input name="lines[{{ $i }}][amount]"
                                                                    class="form-control amount" type="number"
                                                                    step="0.01" value="{{ $ln['amount'] ?? '' }}"></td>
                                                            <td><input name="lines[{{ $i }}][line_narration]"
                                                                    class="form-control"
                                                                    value="{{ $ln['line_narration'] ?? '' }}"></td>
                                                            <td><button type="button"
                                                                    class="btn btn-sm btn-danger remove">×</button></td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr class="line">
                                                        <td>
                                                            <select name="lines[0][ledger_id]" class="form-control ledger">
                                                                @foreach ($ledgers as $l)
                                                                    <option value="{{ $l->id }}">
                                                                        {{ $l->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select name="lines[0][dc]" class="form-control dc">
                                                                <option>Dr</option>
                                                                <option>Cr</option>
                                                            </select>
                                                        </td>
                                                        <td><input name="lines[0][amount]" class="form-control amount"
                                                                type="number" step="0.01"></td>
                                                        <td><input name="lines[0][line_narration]" class="form-control">
                                                        </td>
                                                        <td><button type="button"
                                                                class="btn btn-sm btn-danger remove">×</button></td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="2" class="text-end">Total Dr</th>
                                                    <th><input id="totalDr" class="form-control" readonly></th>
                                                    <th colspan="2" class="text-end">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                                            id="copyDrToCr">Copy Dr→Cr</button>
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="2" class="text-end">Total Cr</th>
                                                    <th><input id="totalCr" class="form-control" readonly></th>
                                                    <th colspan="2" class="text-end">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                                            id="copyCrToDr">Copy Cr→Dr</button>
                                                    </th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="button" id="addLine" class="btn btn-outline-primary mr-2">Add
                                            Line</button>
                                        <button class="btn btn-success" id="btnSubmit">Create Voucher</button>
                                    </div>

                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            let i = $('#linesTable tbody tr').length ? $('#linesTable tbody tr').length : 1;

            const ledgerOptions =
                `@foreach ($ledgers as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach`;

            function rowTpl(idx) {
                return `
      <tr class="line">
        <td><select name="lines[${idx}][ledger_id]" class="form-control ledger">${ledgerOptions}</select></td>
        <td>
          <select name="lines[${idx}][dc]" class="form-control dc">
            <option>Dr</option><option>Cr</option>
          </select>
        </td>
        <td><input name="lines[${idx}][amount]" class="form-control amount" type="number" step="0.01"></td>
        <td><input name="lines[${idx}][line_narration]" class="form-control"></td>
        <td><button type="button" class="btn btn-sm btn-danger remove">×</button></td>
      </tr>`;
            }

            function recalc() {
                let dr = 0,
                    cr = 0;
                $('#linesTable tbody tr').each(function() {
                    const dc = $(this).find('.dc').val();
                    const amt = parseFloat($(this).find('.amount').val() || 0);
                    if (dc === 'Dr') dr += amt;
                    else cr += amt;
                });
                $('#totalDr').val(dr.toFixed(2));
                $('#totalCr').val(cr.toFixed(2));

                const $badge = $('#balanceBadge .badge');
                if (dr === 0 && cr === 0) {
                    $badge.removeClass('bg-success bg-danger').addClass('bg-secondary').text('Not Calculated');
                } else if (Math.abs(dr - cr) < 0.005) {
                    $badge.removeClass('bg-danger bg-secondary').addClass('bg-success').text('Balanced');
                } else {
                    $badge.removeClass('bg-success bg-secondary').addClass('bg-danger').text('Out of Balance');
                }
            }

            // Add line
            $('#addLine').on('click', function() {
                $('#linesTable tbody').append(rowTpl(i));
                i++;
                recalc();
            });

            // Remove line
            $(document).on('click', '.remove', function() {
                $(this).closest('tr').remove();
                recalc();
            });

            // Recalc on change
            $(document).on('input change', '.amount, .dc', recalc);

            // Copy helpers
            $('#copyDrToCr').on('click', function() {
                const dr = parseFloat($('#totalDr').val() || 0);
                if (dr <= 0) return;
                // find first Cr line else add one
                let $row = $('#linesTable tbody tr').filter(function() {
                    return $(this).find('.dc').val() === 'Cr';
                }).first();
                if (!$row.length) {
                    $('#addLine').click();
                    $row = $('#linesTable tbody tr').last();
                    $row.find('.dc').val('Cr');
                }
                $row.find('.amount').val(dr.toFixed(2));
                recalc();
            });

            $('#copyCrToDr').on('click', function() {
                const cr = parseFloat($('#totalCr').val() || 0);
                if (cr <= 0) return;
                let $row = $('#linesTable tbody tr').filter(function() {
                    return $(this).find('.dc').val() === 'Dr';
                }).first();
                if (!$row.length) {
                    $('#addLine').click();
                    $row = $('#linesTable tbody tr').last();
                    $row.find('.dc').val('Dr');
                }
                $row.find('.amount').val(cr.toFixed(2));
                recalc();
            });

            // Prevent submit if not balanced
            $('#btnSubmit').on('click', function(e) {
                const dr = parseFloat($('#totalDr').val() || 0);
                const cr = parseFloat($('#totalCr').val() || 0);
                if (Math.round(dr * 100) !== Math.round(cr * 100)) {
                    e.preventDefault();
                    alert('Total Debit and Credit must be equal before posting.');
                    return false;
                }
            });

            // initial calc
            recalc();
        })();
    </script>
@endsection
