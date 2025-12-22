@extends('layouts.backend.layouts')

@section('page-content')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        /* ================= TALLY STYLE ================= */
        #linesTable {
            width: 100%;
            font-family: monospace;
            table-layout: fixed;
        }

        #linesTable thead th {
            border-top: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            font-weight: bold;
            margin-left: 10px;
        }


        #linesTable tfoot td {
            /* border-top: 1px solid #ccc;
                                                                                                                                                    border-bottom: 1px solid #ccc; */
            font-weight: bold;
        }

        #linesTable td {
            vertical-align: middle;
        }

        #linesTable select,
        #linesTable input {
            border: none;
            background: transparent;
            box-shadow: none;
        }

        #linesTable select:focus,
        #linesTable input:focus {
            outline: none;
        }

        #linesTable tr:hover {
            background: #f7f7f7;
        }

        .voucher-header {
            background: #32bdea;
            color: #fff;
            font-weight: bold;
            padding: 6px;
        }

        .voucher-type-label {
            background: #32bdea;
            color: #fff;
            font-weight: bold;
            padding: 6px 20px;
            display: inline-block;
        }

        .remove {
            cursor: pointer;
            color: red;
            font-weight: bold;
        }

        .sticky-actions {
            position: sticky;
            bottom: 0;
            background: #fff;
            padding: 10px;
            border-top: 1px solid #ddd;
            text-align: right;
        }

        /* Right-side voucher type panel */
        .type-pills-vertical {
            position: sticky;
            top: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: .4rem;
            background: #fff;
            border: 1px solid #ddd;
            padding: .5rem;
        }

        .type-pills-vertical .btn.active {
            background: #32bdea;
            color: #fff;
        }


        .btn-outline-primary {
            color: #000;
            background-color: #fff;
            border-color: #000;
            text-align: center;
            border-radius: 0;
            box-shadow: 2px 2px 4px #ccc;
        }

        /* ================= REMOVE DROPDOWN ICON (TALLY STYLE) ================= */

        /* Chrome, Edge, Safari */
        #linesTable select,
        #linesTable select:focus {
            -webkit-appearance: none;
            appearance: none;
            background-image: none !important;
            padding-right: 4px;
            padding-left: 5px;
            /* small spacing like Tally */
        }

        /* Firefox */
        #linesTable select {
            -moz-appearance: none;
        }

        /* IE / old Edge */
        #linesTable select::-ms-expand {
            display: none;
        }

        /* ================= REMOVE DATE ICON (TALLY STYLE) ================= */

        /* Chrome, Edge, Safari */
        input[type="date"]::-webkit-calendar-picker-indicator {
            display: none;
            -webkit-appearance: none;
        }

        /* Firefox */
        input[type="date"] {
            appearance: none;
            -moz-appearance: textfield;
        }

        /* Prevent extra padding caused by hidden icon */
        input[type="date"] {
            padding-right: 0;
        }

        /* ================= VOUCHER DATE (IMAGE STYLE) ================= */

        .voucher-date-box {
            text-align: right;
            line-height: 1.2;
        }

        .voucher-date-input {
            border: none;
            background: transparent;
            font-weight: bold;
            font-size: 16px;
            text-align: right;
            padding: 0;
        }

        /* remove calendar icon (already discussed, safe to repeat) */
        .voucher-date-input::-webkit-calendar-picker-indicator {
            display: none;
        }

        .voucher-day {
            font-size: 14px;
            color: #5a5a8a;
            /* bluish-grey like image */
            font-weight: normal;
            margin-top: 2px;
        }
    </style>

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="card">

                    {{-- ================= CARD HEADER ================= --}}
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">New Voucher</h4>
                        <a href="{{ route('accounting.vouchers.index') }}" class="btn btn-secondary">
                            Go To List
                        </a>
                    </div>

                    <div class="card-body">

                        @if ($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif

                        <form action="{{ route('accounting.vouchers.store') }}" method="POST" id="voucherForm">
                            @csrf

                            {{-- REQUIRED BY EXISTING JS --}}
                            <input type="hidden" name="voucher_type" id="voucher_type"
                                value="{{ old('voucher_type', 'Journal') }}">

                            <div class="row g-3">
                                <div class="col-lg-10">

                                    {{-- ================= TOP BLUE HEADER ================= --}}
                                    <table class="w-100 mb-2">
                                        <tr>
                                            <td class="voucher-header" width="33%">Accounting Vouchers</td>
                                            <td class="voucher-header text-center" width="33%">
                                                {{ config('app.name') }} 2025-26
                                            </td>
                                            <td class="voucher-header" width="33%"></td>
                                        </tr>
                                    </table>

                                    {{-- ================= TYPE + REF + DATE ================= --}}
                                    <table class="w-100 mb-3">
                                        <tr>
                                            <td width="80%">
                                                <span class="voucher-type-label" id="voucherTypeLabel">
                                                    {{ old('voucher_type', 'Journal') }}
                                                </span>

                                                {{-- enable if needed --}}
                                                <strong class="ms-2">NO.</strong>
                                                <span id="voucher_no">{{ $lastVoucher ?? 'JN-0001' }}</span>
                                                <input name="ref_no" type="hidden"
                                                    value="{{ $lastVoucher ?? 'JN-0001' }}">
                                            </td>

                                            <td width="20%" class="text-end fw-bold">

                                                <div class="voucher-date-box">
                                                    <input type="date" name="voucher_date"
                                                        value="{{ old('voucher_date', now()->toDateString()) }}"
                                                        class="voucher-date-input">

                                                    <div class="voucher-day">
                                                        {{ \Carbon\Carbon::parse(old('voucher_date', now()->toDateString()))->format('l') }}
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>

                                    {{-- ================= ENTRY TABLE ================= --}}
                                    <table id="linesTable">
                                        <thead>
                                            <tr>
                                                {{-- <th width="10%">Dr / Cr</th> --}}
                                                <th width="70%">Particulars</th>
                                                <th class="text-end" width="15%">Amount</th>
                                                <th width="15%"></th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @php $oldLines = old('lines', []); @endphp
                                            @if ($oldLines)
                                                @foreach ($oldLines as $i => $line)
                                                    <tr class="line">
                                                        <input type="hidden" name="lines[{{ $i }}][dc]"
                                                            value="{{ $line['dc'] ?? '' }}">

                                                        <td width="85%">
                                                            <select name="lines[{{ $i }}][ledger_id]"
                                                                class="ledger">
                                                                {{-- <option value="">Select Ledger</option> --}}
                                                                @foreach ($ledgers as $l)
                                                                    <option value="{{ $l->id }}"
                                                                        data-group-id="{{ $l->group_id }}"
                                                                        {{ ($line['ledger_id'] ?? '') == $l->id ? 'selected' : '' }}>
                                                                        {{ $l->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>

                                                        <td class="text-end" width="10%">
                                                            <input name="lines[{{ $i }}][amount]"
                                                                class="amount text-end" type="number" step="0.01"
                                                                value="{{ $line['amount'] ?? '' }}">
                                                        </td>

                                                        <td class="text-center" width="5%">
                                                            <span class="remove"
                                                                {{ $loop->count == 1 ? 'style=display:none' : '' }}>✕</span>
                                                        </td>
                                                    </tr>

                                                    @if (!empty($line['ledger_id']) && !empty($line['cur_balance_text']))
                                                        <tr class="cur-bal-row">
                                                            <td colspan="3" style="padding-left:50px;font-style:italic;">
                                                                {{ $line['cur_balance_text'] }}
                                                            </td>
                                                        </tr>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <tr class="line">
                                                        <input name="lines[0][dc]" type="hidden">
                                                        <td width="85%">
                                                            <select name="lines[0][ledger_id]" class="ledger">
                                                                {{-- <option value="">Select Ledger</option> --}}
                                                                @foreach ($ledgers as $l)
                                                                    <option value="{{ $l->id }}"
                                                                        data-group-id="{{ $l->group_id }}">
                                                                        {{ $l->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>

                                                        <td class="text-end" width="10%">
                                                            <input name="lines[0][amount]" class="amount text-end"
                                                                type="number" step="0.01">
                                                        </td>

                                                        <td class="text-center" width="5%">
                                                            <span class="remove" style="display:none;">✕</span>
                                                        </td>
                                                    </tr>
                                                @endif

                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <td></td>
                                                <td class="text-end"></td>

                                                <td class="text-end"
                                                    style="border-top:1px solid #ccc;border-bottom: 1px solid #ccc;font-weight:bold">
                                                    <input type="text" id="grandTotal" readonly class="text-end"
                                                        value="0.00">
                                                </td>
                                                <td></td>
                                                {{-- keep hidden for logic --}}
                                                <td style="display:none">
                                                    <input type="text" id="totalDr" readonly>
                                                    <input type="text" id="totalCr" readonly>
                                                </td>
                                            </tr>
                                        </tfoot>

                                    </table>

                                    {{-- ================= NARRATION ================= --}}
                                    <div class="mt-2">
                                        <strong>Narration :</strong>
                                        <input type="text" name="narration" class="form-control">
                                    </div>

                                    {{-- ================= SUBMIT ================= --}}
                                    <div class="sticky-actions mt-3">
                                        <button class="btn btn-success" id="btnSubmit">
                                            Create Voucher
                                        </button>
                                    </div>

                                </div>

                                {{-- ================= RIGHT SIDE TYPE PANEL ================= --}}
                                <div class="col-lg-2 col-md-4">
                                    <div class="type-pills-vertical" id="voucherTypePanel" aria-label="Voucher Type">
                                        @foreach (['Journal', 'Payment', 'Receipt', 'Contra', 'Purchase'] as $t)
                                            <button type="button" class="btn btn-outline-primary me-1 mb-1 type-pill"
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

    {{-- ================= SYNC LABEL WITH EXISTING JS ================= --}}
    <script>
        $(document).on('click', '.type-pill', function() {
            $('#voucherTypeLabel').text($(this).data('type'));
        });

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
                    <select name="lines[${idx}][ledger_id]" class="form-control ledger">
                        
                        ${ledgerOptions}
                    </select>
                     <a href="${createLedgerUrl}" target="_blank"
                    class="btn btn-outline-secondary btn-sm">
                    Create Ledger
                </a></td>
                <input name="lines[${idx}][dc]" type="hidden">

                <td><input name="lines[${idx}][amount]" class="form-control amount" type="number" step="0.01"></td>
                <td class="text-center"><span class="remove" style="display:none;">✕</span></td>
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
                updateSubmitVisibility();
                toggleRemoveButtons(); // ✅ REQUIRED
            }


            // ✅ expose globally (FINAL FIX)
            window._addLineRow = function() {
                return addLineRow.apply(this, arguments);
            };



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

                // existing totals (keep)
                $('#totalDr').val(dr.toFixed(2));
                $('#totalCr').val(cr.toFixed(2));

                // ✅ NEW: GRAND TOTAL (Dr + Cr)
                const grand = dr + cr;
                $('#grandTotal').val(grand.toFixed(2));
            }

            $(document).on('click', '.remove', function() {
                const $lineRow = $(this).closest('tr');

                // If Cur Bal row is immediately after this line, remove it
                if ($lineRow.next().hasClass('cur-bal-row')) {
                    $lineRow.next().remove();
                }

                // Remove the actual line row
                $lineRow.remove();

                toggleRemoveButtons();
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

            // $(document).on('input change',
            //     '#linesTable tbody tr .ledger, #linesTable tbody tr .dc, #linesTable tbody tr .amount, #linesTable tbody tr input[name*="[line_narration]"]',
            //     function() {
            //         const $tr = $(this).closest('tr');
            //         if ($tr.is(':last-child') && rowHasAnyValue($tr)) {
            //             addLineRow();
            //         }
            //         recalc(); // recalc also updates button visibility
            //     }
            // );
            // LEDGER CHANGE → SHOW CUR BAL ONLY
            $(document).on('change', '#linesTable tbody tr .ledger', function() {

                const $tr = $(this).closest('tr');

                // Reset auto-add flag if ledger changes
                $tr.removeData('row-added');

                if ($(this).val()) {
                    showCurBalanceRow($tr);
                }
            });



            // AMOUNT INPUT → ADD NEW ROW AFTER CUR BAL
            $(document).on('input', '#linesTable tbody tr .amount', function() {

                const $tr = $(this).closest('tr');
                const amount = parseFloat($(this).val() || 0);

                if (amount > 0 && $tr.is('#linesTable tbody tr.line:last')) {
                    addLineRowAfterRow($tr);
                    if ($totalDr && $totalDr.val) $totalDr.val(amount.toFixed(2));
                    if ($totalCr && $totalCr.val) $totalCr.val(amount.toFixed(2));

                }
            });


            $('#btnSubmit').on('click', function(e) {
                syncPartyHidden();
                const dr = parseFloat($totalDr ? ($totalDr.val() || 0) : 0);
                const cr = parseFloat($totalCr ? ($totalCr.val() || 0) : 0);
                // if (!isNaN(dr) && !isNaN(cr) && Math.round(dr * 100) !== Math.round(cr * 100)) {
                //     e.preventDefault();
                //     alert('Total Debit and Credit must be equal before posting.');
                //     return false;
                // }
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

        function showCurBalanceRow($lineRow) {

            const ledgerId = $lineRow.find('.ledger').val();
            if (!ledgerId) return;

            // Remove existing Cur Bal row
            if ($lineRow.next().hasClass('cur-bal-row')) {
                $lineRow.next().remove();
            }

            // Temporary loading row
            const loadingHtml = `
                <tr class="cur-bal-row">
                    <td style="padding-left:50px;font-style:italic;">
                        Loading current balance...
                    </td>
                </tr>
            `;
            $lineRow.after(loadingHtml);

            // AJAX call
            $.ajax({
                url: "{{ url('/accounting/ledger/current-balance') }}/" + ledgerId,
                type: "GET",
                dataType: "json",
                success: function(res) {

                    // 1️⃣ SET opening_type (IMPORTANT)
                    $lineRow.find('input[name*="[dc]"]').val(res.type);

                    // 2️⃣ Show Cur Balance row (existing logic)
                    const balanceText = `Cur Bal: ${res.balance} ${res.type}`;

                    const curBalHtml = `
                        <tr class="cur-bal-row">
                            <td style="padding-left:50px;font-style:italic;">
                                ${balanceText}
                            </td>
                        </tr>
                    `;

                    $lineRow.next('.cur-bal-row').replaceWith(curBalHtml);
                },
                error: function() {
                    $lineRow.next('.cur-bal-row').html(`
                        <td style="padding-left:50px;color:red;">
                            Failed to load balance
                        </td>
                    `);
                }
            });
        }


        /**
         * Add new voucher row AFTER Cur Bal if exists
         * ✔ Does not break existing addLineRow logic
         */
        function addLineRowAfterCurrent($currentRow) {

            // Do NOT add if already added once
            if ($currentRow.data('row-added')) {
                return;
            }

            $currentRow.data('row-added', true);

            // If Cur Bal exists, insert AFTER it
            if ($currentRow.next().hasClass('cur-bal-row')) {
                addLineRow($currentRow.next());
            } else {
                addLineRow($currentRow);
            }
        }

        function addLineRowAfterRow($afterRow) {

            // Prevent duplicate row creation
            if ($afterRow.data('row-added')) return;

            $afterRow.data('row-added', true);

            // Call original addLineRow safely
            window._addLineRow();

            const $newRow = $('#linesTable tbody tr.line:last');

            // Move new row AFTER Cur Bal if exists
            if ($afterRow.next().hasClass('cur-bal-row')) {
                $afterRow.next().after($newRow);
            } else {
                $afterRow.after($newRow);
            }
        }

        function toggleRemoveButtons() {
            const $rows = $('#linesTable tbody tr.line');
            const count = $rows.length;

            if (count === 1) {
                // only one row → hide remove
                $rows.find('.remove').hide();
            } else {
                // more than one row → show remove on ALL rows (including first)
                $rows.find('.remove').show();
            }
        }

        $(document).on('change', '#voucher_type', function() {

            let voucherType = $(this).val();
            // let branchId = $('#branch_id').val(); // hidden or select

            if (!voucherType) {
                $('#ref_no').val('');
                return;
            }

            $.ajax({
                url: "{{ route('accounting.vouchers.last-ref') }}",
                type: "GET",
                data: {
                    voucher_type: voucherType,
                    // branch_id: branchId
                },
                success: function(res) {
                    $('#voucher_no').text(res.next_ref_no);
                },
                error: function() {
                    alert('Unable to fetch voucher number');
                }
            });
        });

        $(document).ready(function() {
            $('#linesTable tbody tr.line').each(function() {
                if ($(this).find('.ledger').val()) {
                    showCurBalanceRow($(this));
                }
            });
        });
    </script>
@endsection
