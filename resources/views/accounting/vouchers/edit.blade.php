@extends('layouts.backend.layouts')

@section('page-content')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        .badge-balance {
            font-size: 12px
        }

        .type-pills .btn {
            border-radius: 999px;
            padding: .35rem .9rem
        }

        .type-pills .btn.active {
            box-shadow: inset 0 0 0 2px rgba(0, 0, 0, .08)
        }

        .section-card {
            border: 1px solid #eef0f3;
            border-radius: 10px;
            padding: 14px;
            background: #fff
        }

        .section-title {
            font-weight: 600;
            margin-bottom: .5rem;
            display: flex;
            align-items: center;
            gap: .5rem
        }

        .table tfoot input[readonly] {
            background: #f8f9fa
        }

        .sticky-actions {
            position: sticky;
            bottom: 0;
            z-index: 10;
            background: #fff;
            border-top: 1px solid #eef0f3;
            padding: .75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem
        }

        @media (max-width:992px) {
            .grid-2 {
                grid-template-columns: 1fr
            }
        }

        .type-pills-vertical {
            position: sticky;
            top: 1.25rem;
            right: 1rem;
            z-index: 50;
            display: flex;
            flex-direction: column;
            gap: .4rem;
            align-items: flex-start;
            padding: .5rem;
            border-radius: 8px;
            background: #fff;
            border: 1px solid #eef0f3;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
            min-width: 150px;
        }

        .type-pills-vertical .btn {
            width: 100%;
            align-items: center;
            justify-content: flex-start;
            gap: .5rem;
            padding: .45rem .6rem;
            font-weight: 600;
            text-transform: none;
        }

        .type-pills-vertical .btn.active {
            background: #0d6efd;
            color: #fff;
            box-shadow: none;
            border: 1px solid rgba(0, 0, 0, .06);
        }

        @media (max-width:992px) {
            .type-pills-vertical {
                position: static;
                flex-direction: row;
                flex-wrap: wrap;
                gap: .5rem;
                width: 100%;
            }

            .type-pills-vertical .btn {
                width: auto;
                border-radius: 999px;
            }
        }
    </style>

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid add-form-list">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div class="header-title">
                                    <h4 class="card-title mb-1">Edit Voucher</h4>
                                    <div id="balanceBadge" class="mt-1">
                                        <span class="badge badge-balance bg-secondary">Not Calculated</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('accounting.vouchers.index') }}" class="btn btn-secondary">Go To
                                        List</a>
                                </div>
                            </div>

                            <div class="card-body">
                                @if ($errors->any())
                                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                                @endif

                                {{-- Update route: PUT to accounting.vouchers.update --}}
                                <form action="{{ route('accounting.vouchers.update', $voucher->id) }}" method="POST"
                                    id="voucherForm">
                                    @csrf
                                    @method('PUT')

                                    {{-- hidden party ledger used by scripts --}}
                                    <input type="hidden" name="party_ledger_id" id="party_ledger_id"
                                        value="{{ old('party_ledger_id', $voucher->party_ledger_id) }}">

                                    <div class="row g-3 mb-3">
                                        <div class="col-lg-10">
                                            <div class="row">
                                                <div class="col-lg-4 col-md-4">
                                                    <label class="form-label">Date</label>
                                                    <input type="date" class="form-control" name="voucher_date"
                                                        value="{{ old('voucher_date', optional($voucher->voucher_date)->toDateString() ?? $voucher->voucher_date) }}"
                                                        required>
                                                </div>

                                                <input type="hidden" name="voucher_type" id="voucher_type"
                                                    value="{{ old('voucher_type', $voucher->voucher_type ?? 'Journal') }}">

                                                <div class="col-lg-4 col-md-4">
                                                    <label class="form-label">Branch</label>
                                                    <select name="branch_id" class="form-control">
                                                        <option value="">All / None</option>
                                                        @foreach ($branches ?? [] as $b)
                                                            <option value="{{ $b->id }}"
                                                                @selected(old('branch_id', $voucher->branch_id) == $b->id)>{{ $b->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-lg-4 col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Narration</label>
                                                        <textarea name="narration" class="form-control" rows="2">{{ old('narration', $voucher->narration) }}</textarea>
                                                    </div>
                                                </div>

                                                {{-- TYPE-SPECIFIC sections are available but hidden/shown by JS (same as create) --}}
                                                {{-- (Payment/Receipt/Contra/Trade blocks omitted for brevity — copy from create if needed) --}}

                                                {{-- Journal lines (prefilled from $voucher->lines) --}}
                                                <div class="section-card ml-3">
                                                    <div class="section-title">
                                                        <div id="section-trade" style="display:none;">
                                                            <span class="badge bg-secondary">Sales / Purchase / Notes</span>
                                                        </div>
                                                        <div id="section-contra" style="display:none;">
                                                            <span class="badge bg-secondary">Contra</span>
                                                        </div>
                                                        <div id="section-payment-receipt" style="display:none;">
                                                            <span class="badge bg-secondary">Payment/Receipt</span>
                                                        </div>

                                                        <div id="section-journal" style="display:block;">
                                                            <span class="badge bg-secondary">Journal</span>
                                                        </div>

                                                        Add line items (Dr/Cr)
                                                    </div>

                                                    <div class="table-responsive">
                                                        <table class="table table-bordered align-middle mb-0"
                                                            id="linesTable">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th style="width:15%">Dr/Cr</th>
                                                                    <th style="width:40%">Ledger</th>
                                                                    <th style="width:25%">Amount</th>
                                                                    <th style="width:10%">Narration</th>
                                                                    <th style="width:10%"></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @php
                                                                    $oldLines = old('lines');
                                                                    $lines =
                                                                        $oldLines !== null
                                                                            ? $oldLines
                                                                            : ($voucher->lines->count()
                                                                                ? $voucher->lines
                                                                                : null);
                                                                @endphp

                                                                @if ($lines)
                                                                    @foreach ($lines as $i => $ln)
                                                                        {{-- $ln may be array (old) or model (voucher->lines) --}}
                                                                        @php
                                                                            $dc = is_array($ln)
                                                                                ? $ln['dc'] ?? 'Dr'
                                                                                : $ln->dc ?? 'Dr';
                                                                            $ledger_id = is_array($ln)
                                                                                ? $ln['ledger_id'] ?? ''
                                                                                : $ln->ledger_id ?? '';
                                                                            $amount = is_array($ln)
                                                                                ? $ln['amount'] ?? ''
                                                                                : $ln->amount ?? '';
                                                                            $line_narration = is_array($ln)
                                                                                ? $ln['line_narration'] ?? ''
                                                                                : $ln->line_narration ?? '';
                                                                        @endphp
                                                                        <tr class="line">
                                                                            <td>
                                                                                <select
                                                                                    name="lines[{{ $i }}][dc]"
                                                                                    class="form-control dc">
                                                                                    <option @selected($dc === 'Dr')>Dr
                                                                                    </option>
                                                                                    <option @selected($dc === 'Cr')>Cr
                                                                                    </option>
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <select
                                                                                    name="lines[{{ $i }}][ledger_id]"
                                                                                    class="form-control ledger">
                                                                                    <option value="">Select</option>
                                                                                    @foreach ($ledgers as $l)
                                                                                        <option value="{{ $l->id }}"
                                                                                            data-group-id="{{ $l->group_id }}"
                                                                                            @selected($ledger_id == $l->id)>
                                                                                            {{ $l->name }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                                <a href="{{ route('accounting.ledgers.create', 'voucher') }}"
                                                                                    class="btn btn-outline-secondary btn-sm">Create
                                                                                    Ledger</a>
                                                                            </td>
                                                                            <td>
                                                                                <input
                                                                                    name="lines[{{ $i }}][amount]"
                                                                                    class="form-control amount"
                                                                                    type="number" step="0.01"
                                                                                    value="{{ $amount }}">
                                                                            </td>
                                                                            <td>
                                                                                <input
                                                                                    name="lines[{{ $i }}][line_narration]"
                                                                                    class="form-control"
                                                                                    value="{{ $line_narration }}">
                                                                            </td>
                                                                            <td>
                                                                                <button type="button"
                                                                                    class="btn btn-sm btn-danger remove">×</button>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @else
                                                                    {{-- fallback single empty row --}}
                                                                    <tr class="line">
                                                                        <td>
                                                                            <select name="lines[0][dc]"
                                                                                class="form-control dc">
                                                                                <option>Dr</option>
                                                                                <option>Cr</option>
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <select name="lines[0][ledger_id]"
                                                                                class="form-control ledger">
                                                                                <option value="">Select</option>
                                                                                @foreach ($ledgers as $l)
                                                                                    <option value="{{ $l->id }}"
                                                                                        data-group-id="{{ $l->group_id }}">
                                                                                        {{ $l->name }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                            <a href="{{ route('accounting.ledgers.create', 'voucher') }}"
                                                                                class="btn btn-outline-secondary btn-sm">Create
                                                                                Ledger</a>
                                                                        </td>
                                                                        <td><input name="lines[0][amount]"
                                                                                class="form-control amount" type="number"
                                                                                step="0.01"></td>
                                                                        <td><input name="lines[0][line_narration]"
                                                                                class="form-control"></td>
                                                                        <td><button type="button"
                                                                                class="btn btn-sm btn-danger remove">×</button>
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <div class="d-flex justify-content-end align-items-center mt-2">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <button type="button"
                                                                class="btn btn-outline-secondary btn-sm"
                                                                id="copyDrToCr">Copy Dr→Cr</button>
                                                            <button type="button"
                                                                class="btn btn-outline-secondary btn-sm"
                                                                id="copyCrToDr">Copy Cr→Dr</button>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Sticky footer --}}
                                                <div class="sticky-actions mt-3">
                                                    <div class="d-flex flex-wrap align-items-center gap-3">
                                                        <div class="input-group input-group-sm" style="width:180px;">
                                                            <span class="input-group-text">Total Dr</span>
                                                            <input id="totalDr" class="form-control mr-2" readonly>
                                                        </div>
                                                        <div class="input-group input-group-sm" style="width:180px;">
                                                            <span class="input-group-text">Total Cr</span>
                                                            <input id="totalCr" class="form-control" readonly>
                                                        </div>
                                                        <span id="stickyBadge" class="badge bg-secondary">Not
                                                            Calculated</span>
                                                    </div>

                                                    <div>
                                                        <a href="{{ route('accounting.vouchers.index') }}"
                                                            class="btn btn-outline-secondary me-2">Cancel</a>
                                                        <button class="btn btn-success" id="btnSubmit">Update
                                                            Voucher</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-2 col-md-4">
                                            <div class="type-pills-vertical" id="voucherTypePanel"
                                                aria-label="Voucher Type">
                                                @foreach (['Journal', 'Payment', 'Receipt', 'Contra', 'Sales', 'Purchase', 'DebitNote', 'CreditNote'] as $t)
                                                    <button type="button"
                                                        class="btn btn-outline-primary me-1 mb-1 type-pill"
                                                        data-type="{{ $t }}">{{ $t }}</button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- inject JS-data early so the main script can use them --}}
    <script>
        const LEDGERS = @json($ledgers);
        // expose voucher-level values that some JS/autobuild expects
        const VOUCHER_DATA = {
            type: @json(old('voucher_type', $voucher->voucher_type ?? 'Journal')),
            party_ledger_id: @json(old('party_ledger_id', $voucher->party_ledger_id)),
            mode: @json(old('mode', $voucher->mode ?? null)),
            cash_ledger_id: @json(old('cash_ledger_id', $voucher->cash_ledger_id ?? null)),
            bank_ledger_id: @json(old('bank_ledger_id', $voucher->bank_ledger_id ?? null)),
            amount: @json(old('amount', $voucher->amount ?? null)),
        };
    </script>

    {{-- Main script: same as create view but will operate on prefilled fields --}}
    <script>
        (function() {
            const $type = $('#voucher_type');
            const $prMode = $('#pr_mode');
            const $prCashWrap = $('#pr_cash_wrap');
            const $prBankWrap = $('#pr_bank_wrap');
            const $totalDr = $('#totalDr');
            const $totalCr = $('#totalCr');
            const $badgeTop = $('#balanceBadge .badge');
            const $badgeSticky = $('#stickyBadge');
            const createLedgerUrl = "{{ route('accounting.ledgers.create', 'voucher') }}";

            // preload voucher type from server var
            $type.val(VOUCHER_DATA.type || 'Journal');

            const ledgerOptions =
                `@foreach ($ledgers as $l)<option value="{{ $l->id }}" data-group-id="{{ $l->group_id }}">{{ $l->name }}</option>@endforeach`;

            const VOUCHER_GROUP_MAP = {
                Journal: [],
                Payment: [17, 18, 20, 21, 13, 14],
                Receipt: [17, 18, 19, 10, 11],
                Contra: [17, 18],
                Sales: [19, 9, 21],
                Purchase: [12, 21, 20],
                DebitNote: [20, 12, 21],
                CreditNote: [19, 9, 21],
            };

            const DC_MAP = {
                Journal: ['Cr', 'Dr'],
                Contra: ['Cr', 'Dr'],
                Receipt: ['Cr', 'Dr'],
                default: ['Dr', 'Cr'],
            };

            function setBadge(state) {
                const texts = {
                    none: 'Not Calculated',
                    ok: 'Balanced',
                    bad: 'Out of Balance'
                };
                const cls = {
                    none: 'bg-secondary',
                    ok: 'bg-success',
                    bad: 'bg-danger'
                };
                [$badgeTop, $badgeSticky].forEach($b => {
                    $b.removeClass('bg-secondary bg-success bg-danger').addClass(cls[state]).text(texts[state]);
                });
            }

            function showSections() {
                const t = $type.val();
                const isPR = (t === 'Payment' || t === 'Receipt');
                const isCT = (t === 'Contra');
                const isTR = (t === 'Sales' || t === 'Purchase' || t === 'DebitNote' || t === 'CreditNote');
                $('#section-payment-receipt').toggle(isPR);
                $('#section-contra').toggle(isCT);
                $('#section-trade').toggle(isTR);
                $('#section-journal').toggle(t === 'Journal');
                togglePRMode();
            }

            function togglePRMode() {
                const m = $prMode.val();
                $prCashWrap.toggle(m === 'cash');
                $prBankWrap.toggle(m === 'bank' || m === 'upi' || m === 'card');
            }

            let i = $('#linesTable tbody tr').length ? $('#linesTable tbody tr').length : 1;

            function defaultDC(type, index) {
                const arr = DC_MAP[type] || DC_MAP.default;
                return arr[index] || 'Dr';
            }

            function rowTpl(idx, dcDefault) {
                const drSel = (dcDefault === 'Dr') ? 'selected' : '';
                const crSel = (dcDefault === 'Cr') ? 'selected' : '';
                return `
                <tr class="line">
                    <td>
                        <select name="lines[${idx}][dc]" class="form-control dc">
                            <option ${drSel}>Dr</option>
                            <option ${crSel}>Cr</option>
                        </select>
                    </td>
                    <td>
                        <select name="lines[${idx}][ledger_id]" class="form-control ledger">
                            ${ledgerOptions}
                        </select>
                         <a href="${createLedgerUrl}" target="_blank" class="btn btn-outline-secondary btn-sm">Create Ledger</a>
                    </td>
                    <td><input name="lines[${idx}][amount]" class="form-control amount" type="number" step="0.01"></td>
                    <td><input name="lines[${idx}][line_narration]" class="form-control"></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove">×</button></td>
                </tr>`;
            }

            function filterLedgerDropdownsByVoucherType() {
                const t = $type.val();
                const allowedGroups = VOUCHER_GROUP_MAP[t] || [];

                $('.ledger').each(function() {
                    const $select = $(this);
                    const current = $select.val();
                    let html = `<option value="">Select</option>`;
                    LEDGERS.forEach(l => {
                        if (!allowedGroups.length || allowedGroups.includes(l.group_id)) {
                            html +=
                                `<option value="${l.id}" data-group-id="${l.group_id}">${l.name}</option>`;
                        }
                    });
                    $select.html(html);
                    if (current && $select.find(`option[value="${current}"]`).length) {
                        $select.val(current);
                    }
                });
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
                $totalDr.val(dr.toFixed(2));
                $totalCr.val(cr.toFixed(2));
                if (dr === 0 && cr === 0) setBadge('none');
                else if (Math.abs(dr - cr) < 0.005) setBadge('ok');
                else setBadge('bad');
            }

            // events (same as create)
            $(document).on('click', '.remove', function() {
                $(this).closest('tr').remove();
                recalc();
            });
            $(document).on('input change', '.amount, .dc', recalc);

            $('#copyDrToCr').on('click', function() {
                const dr = parseFloat($totalDr.val() || 0);
                if (dr <= 0) return;
                let $row = $('#linesTable tbody tr').filter(function() {
                    return $(this).find('.dc').val() === 'Cr';
                }).first();
                if (!$row.length) {
                    $('#linesTable tbody').append(rowTpl(i, 'Cr'));
                    i++;
                    $row = $('#linesTable tbody tr').last();
                }
                $row.find('.amount').val(dr.toFixed(2));
                recalc();
            });

            $('#copyCrToDr').on('click', function() {
                const cr = parseFloat($totalCr.val() || 0);
                if (cr <= 0) return;
                let $row = $('#linesTable tbody tr').filter(function() {
                    return $(this).find('.dc').val() === 'Dr';
                }).first();
                if (!$row.length) {
                    $('#linesTable tbody').append(rowTpl(i, 'Dr'));
                    i++;
                    $row = $('#linesTable tbody tr').last();
                }
                $row.find('.amount').val(cr.toFixed(2));
                recalc();
            });

            $(document).on('input change',
                '#linesTable tbody tr .ledger, #linesTable tbody tr .dc, #linesTable tbody tr .amount, #linesTable tbody tr input[name*="[line_narration]"]',
                function() {
                    const $tr = $(this).closest('tr');
                    if (!$tr.is(':last-child')) return;
                    const ledger = $tr.find('.ledger').val();
                    const dc = $tr.find('.dc').val();
                    const amt = parseFloat($tr.find('.amount').val() || 0);
                    const narr = $tr.find('input[name*="[line_narration]"]').val();
                    if (ledger || dc || amt || narr) {
                        $('#linesTable tbody').append(rowTpl(i, defaultDC($type.val(), i)));
                        i++;
                        recalc();
                    }
                });

            $('#btnSubmit').on('click', function(e) {
                // sync party ledger back to hidden before submit (if PR/TR types used)
                const party = $('#pr_party_ledger').val() || $('#tr_party_ledger').val() || '';
                $('#party_ledger_id').val(party);
                const dr = parseFloat($totalDr.val() || 0);
                const cr = parseFloat($totalCr.val() || 0);
                if (Math.round(dr * 100) !== Math.round(cr * 100)) {
                    e.preventDefault();
                    alert('Total Debit and Credit must be equal before posting.');
                    return false;
                }
            });

            // initial run: set UI according to voucher values
            filterLedgerDropdownsByVoucherType();
            // set type-pill active
            $('.type-pill').removeClass('active');
            $('.type-pill').each(function() {
                if ($(this).data('type') === $type.val()) $(this).addClass('active');
            });

            showSections();
            recalc();
        })();

        $(document).on('keydown', '.type-pill', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).trigger('click');
            }
        });
    </script>

@endsection
