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
            justify-content: flex-end;
            /* changed from space-between */
            align-items: center;
            gap: 1rem
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem
        }

        @media (max-width: 992px) {
            .grid-2 {
                grid-template-columns: 1fr
            }
        }

        /* Right-side vertical voucher type panel (Tally-like) */
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
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        @media (max-width: 992px) {
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
                                    <h4 class="card-title mb-1">New Voucher</h4>
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

                                <form action="{{ route('accounting.vouchers.store') }}" method="POST" id="voucherForm">
                                    @csrf
                                    <input type="hidden" name="party_ledger_id" id="party_ledger_id"
                                        value="{{ old('party_ledger_id') }}">

                                    <div class="row g-3 mb-3">
                                        <div class="col-lg-10">

                                            {{-- Top bar with Date aligned to right --}}
                                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                                                <div></div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <label class="form-label mb-0">Date</label>
                                                    <input type="date" class="form-control" style="max-width: 180px"
                                                        name="voucher_date"
                                                        value="{{ old('voucher_date', now()->toDateString()) }}" required>
                                                </div>
                                            </div>

                                            <input type="hidden" name="voucher_type" id="voucher_type"
                                                value="{{ old('voucher_type', 'Journal') }}">

                                            {{-- Journal lines --}}
                                            <div class="section-card">
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
                                                    <table class="table table-bordered align-middle mb-0" id="linesTable">
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
                                                            @php $oldLines = old('lines', []); @endphp
                                                            @if ($oldLines)
                                                                @foreach ($oldLines as $i => $ln)
                                                                    <tr class="line">
                                                                        <td>
                                                                            <select name="lines[{{ $i }}][dc]"
                                                                                class="form-control dc">
                                                                                <option @selected(($ln['dc'] ?? 'Dr') === 'Dr')>Dr
                                                                                </option>
                                                                                <option @selected(($ln['dc'] ?? 'Dr') === 'Cr')>Cr
                                                                                </option>
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <select
                                                                                name="lines[{{ $i }}][ledger_id]"
                                                                                class="form-control ledger">
                                                                                @foreach ($ledgers as $l)
                                                                                    <option value="{{ $l->id }}"
                                                                                        data-group-id="{{ $l->group_id }}"
                                                                                        @selected(($ln['ledger_id'] ?? null) == $l->id)>
                                                                                        {{ $l->name }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                            <a href="{{ route('accounting.ledgers.create') }}"
                                                                                class="btn btn-outline-secondary btn-sm">Create
                                                                                Ledger
                                                                            </a>
                                                                        </td>

                                                                        <td>
                                                                            <input
                                                                                name="lines[{{ $i }}][amount]"
                                                                                class="form-control amount" type="number"
                                                                                step="0.01"
                                                                                value="{{ $ln['amount'] ?? '' }}">
                                                                        </td>
                                                                        <td>
                                                                            <input
                                                                                name="lines[{{ $i }}][line_narration]"
                                                                                class="form-control"
                                                                                value="{{ $ln['line_narration'] ?? '' }}">
                                                                        </td>
                                                                        <td>
                                                                            <button type="button"
                                                                                class="btn btn-sm btn-danger remove">×</button>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <tr class="line">
                                                                    <td>
                                                                        <select name="lines[0][dc]" class="form-control dc">
                                                                            <option>Dr</option>
                                                                            <option>Cr</option>
                                                                        </select>
                                                                    </td>
                                                                    <td>
                                                                        <select name="lines[0][ledger_id]"
                                                                            class="form-control ledger">
                                                                            @foreach ($ledgers as $l)
                                                                                <option value="{{ $l->id }}"
                                                                                    data-group-id="{{ $l->group_id }}">
                                                                                    {{ $l->name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>

                                                                        <a href="{{ route('accounting.ledgers.create', 'voucher') }}"
                                                                            class="btn btn-outline-secondary btn-sm">
                                                                            Create Ledger
                                                                        </a>
                                                                    </td>

                                                                    <td>
                                                                        <input name="lines[0][amount]"
                                                                            class="form-control amount" type="number"
                                                                            step="0.01">
                                                                    </td>
                                                                    <td>
                                                                        <input name="lines[0][line_narration]"
                                                                            class="form-control">
                                                                    </td>
                                                                    <td>
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-danger remove">×</button>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            {{-- Sticky footer (Create button aligned right) --}}
                                            <div class="sticky-actions mt-3">
                                                <button class="btn btn-success" id="btnSubmit">Create
                                                    Voucher</button>
                                            </div>
                                        </div>

                                        <div class="col-lg-2 col-md-4">
                                            <div class="type-pills-vertical" id="voucherTypePanel"
                                                aria-label="Voucher Type">
                                                @foreach (['Journal', 'Payment', 'Receipt', 'Contra', 'Purchase'] as $t)
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

    <script>
        (function() {
            const $type = $('#voucher_type');

            const $secPR = $('#section-payment-receipt');
            const $secCT = $('#section-contra');
            const $secTR = $('#section-trade');
            const $secJR = $('#section-journal');

            const $prMode = $('#pr_mode');
            const $prCashWrap = $('#pr_cash_wrap');
            const $prBankWrap = $('#pr_bank_wrap');

            const $totalDr = $('#totalDr');
            const $totalCr = $('#totalCr');
            const $badgeTop = $('#balanceBadge .badge');
            const $badgeSticky = $('#stickyBadge');
            const createLedgerUrl = "{{ route('accounting.ledgers.create', 'voucher') }}";
            const $btnSubmit = $('#btnSubmit'); // <-- submit button

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
                    $b.removeClass('bg-secondary bg-success bg-danger')
                        .addClass(cls[state])
                        .text(texts[state]);
                });
            }

            function showSections() {
                const t = $type.val();
                const isPR = (t === 'Payment' || t === 'Receipt');
                const isCT = (t === 'Contra');
                const isTR = (t === 'Sales' || t === 'Purchase' || t === 'DebitNote' || t === 'CreditNote');
                const isJR = (t === 'Journal');
                $secPR.toggle(isPR);
                $secCT.toggle(isCT);
                $secTR.toggle(isTR);
                $secJR.toggle(isJR);
                togglePRMode();
            }

            function togglePRMode() {
                const m = $prMode.val();
                $prCashWrap.toggle(m === 'cash');
                $prBankWrap.toggle(m === 'bank' || m === 'upi' || m === 'card');
            }

            let i = $('#linesTable tbody tr').length ? $('#linesTable tbody tr').length : 1;

            const ledgerOptions =
                `@foreach ($ledgers as $l)<option value="{{ $l->id }}" data-group-id="{{ $l->group_id }}">{{ $l->name }}</option>@endforeach`;

            const VOUCHER_GROUP_MAP = {
                Journal: [],

                Payment: [
                    17, 18, 20, 21, 13, 14
                ],

                Receipt: [
                    17, 18, 19, 10, 11
                ],

                Contra: [
                    17, 18
                ],

                Sales: [
                    19, 9, 21
                ],

                Purchase: [
                    12, 21, 20
                ],

                DebitNote: [
                    20, 12, 21
                ],

                CreditNote: [
                    19, 9, 21
                ],
            };

            const DC_MAP = {
                Journal: ['Cr', 'Dr'],
                Contra: ['Cr', 'Dr'],
                Receipt: ['Cr', 'Dr'],
                default: ['Dr', 'Cr'],
            };

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
                     <a href="${createLedgerUrl}" target="_blank"
                    class="btn btn-outline-secondary btn-sm">
                    Create Ledger
                </a></td>
                
                <td><input name="lines[${idx}][amount]" class="form-control amount" type="number" step="0.01"></td>
                <td><input name="lines[${idx}][line_narration]" class="form-control"></td>
                <td><button type="button" class="btn btn-sm btn-danger remove">×</button></td>
            </tr>`;
            }

            function rowHasAnyValue($tr) {
                const ledger = $tr.find('.ledger').val();
                const dc = $tr.find('.dc').val();
                const amt = parseFloat($tr.find('.amount').val() || 0);
                const narration = $tr.find('input[name*="[line_narration]"]').val();
                return !!(ledger || dc || amt || narration);
            }

            // NEW: check if any row has value
            function anyLineHasValue() {
                let found = false;
                $('#linesTable tbody tr').each(function() {
                    if (rowHasAnyValue($(this))) {
                        found = true;
                        return false; // break
                    }
                });
                return found;
            }

            // NEW: show/hide Create button
            function updateSubmitVisibility() {
                if (anyLineHasValue()) {
                    $btnSubmit.show();
                } else {
                    $btnSubmit.hide();
                }
            }

            function addLineRow(dcDefault) {
                const idx = i;
                const type = $type.val();
                const dc = dcDefault || defaultDC(type, idx);
                $('#linesTable tbody').append(rowTpl(idx, dc));
                i++;

                filterLedgerDropdownsByVoucherType();
                updateSubmitVisibility(); // NEW
            }

            function setDCForAllRowsByType(force = false) {
                const type = $type.val();
                $('#linesTable tbody tr').each(function(idx) {
                    const $tr = $(this);
                    const hasAmt = parseFloat($tr.find('.amount').val() || 0) > 0;
                    if (hasAmt && !force) return;
                    $tr.find('.dc').val(defaultDC(type, idx));
                });
            }

            function setDefaultLedgerForFirstLine() {
                const t = $type.val();
                const allowedGroups = VOUCHER_GROUP_MAP[t] || [];

                if (!allowedGroups.length) return;

                const $firstRow = $('#linesTable tbody tr').first();
                if (!$firstRow.length) return;

                const $ledger = $firstRow.find('.ledger');
                if (!$ledger.length) return;

                if ($ledger.val()) return;

                let selectedVal = null;

                $ledger.find('option').each(function() {
                    const $opt = $(this);
                    const id = $opt.val();
                    if (!id) return;

                    const groupId = parseInt($opt.data('group-id')) || null;
                    if (groupId && allowedGroups.includes(groupId) && !$opt.prop('disabled')) {
                        selectedVal = id;
                        return false;
                    }
                });

                if (selectedVal) {
                    $ledger.val(selectedVal);
                }
            }

            const LEDGERS = @json($ledgers);

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

                setDefaultLedgerForFirstLine();
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
                if ($totalDr && $totalDr.val) $totalDr.val(dr.toFixed(2));
                if ($totalCr && $totalCr.val) $totalCr.val(cr.toFixed(2));

                if (dr === 0 && cr === 0) setBadge('none');
                else if (Math.abs(dr - cr) < 0.005) setBadge('ok');
                else setBadge('bad');

                updateSubmitVisibility(); // NEW
            }

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
                    addLineRow('Cr');
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
                    addLineRow('Dr');
                    $row = $('#linesTable tbody tr').last();
                }
                $row.find('.amount').val(cr.toFixed(2));
                recalc();
            });

            function linesHaveAnyAmount() {
                return $('#linesTable tbody tr .amount').filter(function() {
                    return $(this).val();
                }).length > 0;
            }

            function ensureRow(idx, dc) {
                while ($('#linesTable tbody tr').length <= idx) {
                    addLineRow();
                }
                const $row = $('#linesTable tbody tr').eq(idx);
                if (dc) $row.find('.dc').val(dc);
                return $row;
            }

            function autobuildPR() {
                const t = $type.val();
                const enabled = $('#pr_autobuild').is(':checked');
                if (!enabled) return;
                if (linesHaveAnyAmount()) return;

                const party = $('#pr_party_ledger').val();
                const mode = $('#pr_mode').val();
                const cashL = $('#pr_cash_ledger').val();
                const bankL = $('#pr_bank_ledger').val();
                const amt = parseFloat($('#pr_amount').val() || 0);
                if (!party || !mode || !amt) return;

                const counter = (mode === 'cash') ? cashL : bankL;
                if (!counter) return;

                $('#linesTable tbody tr').find('.amount').val('');

                if (t === 'Payment') {
                    const $r0 = ensureRow(0, 'Dr');
                    $r0.find('.ledger').val(counter);
                    $r0.find('.amount').val(amt.toFixed(2));

                    const $r1 = ensureRow(1, 'Cr');
                    $r1.find('.ledger').val(party);
                    $r1.find('.amount').val(amt.toFixed(2));
                } else if (t === 'Receipt') {
                    const $r0 = ensureRow(0, 'Dr');
                    $r0.find('.ledger').val(party);
                    $r0.find('.amount').val(amt.toFixed(2));

                    const $r1 = ensureRow(1, 'Cr');
                    $r1.find('.ledger').val(counter);
                    $r1.find('.amount').val(amt.toFixed(2));
                }
                recalc();
            }

            function autobuildCT() {
                const enabled = $('#ct_autobuild').is(':checked');
                if (!enabled) return;
                if (linesHaveAnyAmount()) return;

                const from = $('#ct_from').val();
                const to = $('#ct_to').val();
                const amt = parseFloat($('#ct_amount').val() || 0);
                if (!from || !to || !amt) return;

                $('#linesTable tbody tr').find('.amount').val('');

                const $r0 = ensureRow(0, 'Cr');
                $r0.find('.ledger').val(from);
                $r0.find('.amount').val(amt.toFixed(2));

                const $r1 = ensureRow(1, 'Dr');
                $r1.find('.ledger').val(to);
                $r1.find('.amount').val(amt.toFixed(2));

                recalc();
            }

            function calcTradeGrand() {
                const s = parseFloat($('#tr_subtotal').val() || 0);
                const d = parseFloat($('#tr_discount').val() || 0);
                const t = parseFloat($('#tr_tax').val() || 0);
                $('#tr_grand').val((s - d + t).toFixed(2));
            }

            function autobuildTR() {
                const enabled = $('#tr_autobuild').is(':checked');
                if (!enabled) return;
                if (linesHaveAnyAmount()) return;

                const t = $type.val();
                const pl = $('#tr_party_ledger').val();
                const amt = parseFloat($('#tr_grand').val() || 0);
                if (!pl || !amt) return;

                $('#linesTable tbody tr').find('.amount').val('');

                if (t === 'Sales' || t === 'CreditNote') {
                    const $r0 = ensureRow(0, 'Dr');
                    $r0.find('.ledger').val(pl);
                    $r0.find('.amount').val(amt.toFixed(2));

                    const $r1 = ensureRow(1, 'Cr');
                    $r1.find('.amount').val(amt.toFixed(2));
                } else if (t === 'Purchase' || t === 'DebitNote') {
                    const $r0 = ensureRow(0, 'Dr');
                    $r0.find('.amount').val(amt.toFixed(2));

                    const $r1 = ensureRow(1, 'Cr');
                    $r1.find('.ledger').val(pl);
                    $r1.find('.amount').val(amt.toFixed(2));
                }
                recalc();
            }

            function activeType() {
                return $type.val();
            }

            function isPR() {
                const t = activeType();
                return t === 'Payment' || t === 'Receipt';
            }

            function isTR() {
                const t = activeType();
                return t === 'Sales' || t === 'Purchase' || t === 'DebitNote' || t === 'CreditNote';
            }

            function syncPartyHidden() {
                let val = '';
                if (isPR()) val = $('#pr_party_ledger').val() || '';
                if (isTR()) val = $('#tr_party_ledger').val() || '';
                $('#party_ledger_id').val(val);
            }

            $('.type-pill').on('click', function() {
                $type.val($(this).data('type')).trigger('change');
            });

            function syncPills() {
                const t = $type.val();
                $('.type-pill').removeClass('active').each(function() {
                    if ($(this).data('type') === t) $(this).addClass('active');
                });

                $('.type-pill').attr('aria-pressed', 'false');
                $('.type-pill.active').attr('aria-pressed', 'true');
            }

            $type.on('change', function() {
                showSections();
                syncPills();
                togglePRMode();
                syncPartyHidden();

                setDCForAllRowsByType(false);

                filterLedgerDropdownsByVoucherType();

                autobuildPR();
                autobuildCT();
                calcTradeGrand();
                autobuildTR();
                updateSubmitVisibility(); // NEW
            });

            $prMode.on('change', function() {
                togglePRMode();
                autobuildPR();
            });

            $('#pr_party_ledger,#tr_party_ledger').on('change', syncPartyHidden);
            $('#pr_party_ledger,#pr_cash_ledger,#pr_bank_ledger,#pr_amount').on('input change', autobuildPR);
            $('#ct_from,#ct_to,#ct_amount').on('input change', autobuildCT);
            $('#tr_subtotal,#tr_discount,#tr_tax').on('input', function() {
                calcTradeGrand();
                autobuildTR();
            });

            $(document).on('input change',
                '#linesTable tbody tr .ledger, #linesTable tbody tr .dc, #linesTable tbody tr .amount, #linesTable tbody tr input[name*="[line_narration]"]',
                function() {
                    const $tr = $(this).closest('tr');
                    if ($tr.is(':last-child') && rowHasAnyValue($tr)) {
                        addLineRow();
                    }
                    recalc(); // recalc also updates button visibility
                }
            );

            $('#btnSubmit').on('click', function(e) {
                syncPartyHidden();
                const dr = parseFloat($totalDr ? ($totalDr.val() || 0) : 0);
                const cr = parseFloat($totalCr ? ($totalCr.val() || 0) : 0);
                if (!isNaN(dr) && !isNaN(cr) && Math.round(dr * 100) !== Math.round(cr * 100)) {
                    e.preventDefault();
                    alert('Total Debit and Credit must be equal before posting.');
                    return false;
                }
            });

            // Initial state: hide button until some line has value
            $btnSubmit.hide();
            showSections();
            syncPills();
            togglePRMode();
            syncPartyHidden();
            recalc(); // will call updateSubmitVisibility()
            calcTradeGrand();
            setDCForAllRowsByType(false);
            filterLedgerDropdownsByVoucherType();
        })();

        $(document).on('keydown', '.type-pill', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).trigger('click');
            }
        });

        $(document).on('click', '.type-pill', function() {
            let type = $(this).data('type');

            switch (type) {
                case 'Sales':
                    window.location.href = "{{ url('shift-manage/list') }}";
                    break;

                case 'Purchase':
                    window.location.href = "{{ url('purchase/create') }}";
                    break;

                default:
                    console.log("Selected type:", type);
                    break;
            }
        });
    </script>


@endsection
