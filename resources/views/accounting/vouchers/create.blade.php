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
                border-top: 4px solid #a7a3a3;
                border-bottom: 3px solid #bbb8b8;
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

            #linesTable tbody tr.line:hover {
                background: #F3E6A1;
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
                color: #c0392b;
                font-weight: bold;
                font-size: 18px;
                padding: 4px 8px;
                border-radius: 4px;
            }

            .remove:hover {
                background-color: #ffe6e6;
                color: #ff0000;
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

            #linesTable tfoot tr:first-child td {
                padding-top: 12px;
                border-top: 1px solid #ccc;
            }

            /* ================= TALLY ACCOUNT HEADER ================= */

            .tally-account-box {
                font-family: monospace;
                font-size: 15px;
            }

            .tally-account-box td {
                padding: 2px 4px;
                vertical-align: middle;
            }

            .tally-particulars-header {
                font-family: monospace;
                font-weight: bold;
                padding: 4px 6px;
                border-top: 1px solid #999;
                border-bottom: 1px solid #999;
                margin-bottom: 4px;
            }

            /* ================= TALLY STYLE SELECT (NO ARROW) ================= */

            /* Chrome, Edge, Safari */
            .account-ledger,
            .account-ledger:focus {
                -webkit-appearance: none;
                appearance: none;
                background-image: none !important;
                border: none;
                padding-left: 5px;
                padding-right: 0;
                font-family: monospace;
                font-weight: bold;
            }

            /* Firefox */
            .account-ledger {
                -moz-appearance: none;
            }

            /* Old Edge / IE */
            .account-ledger::-ms-expand {
                display: none;
            }

            /* Tally-style disabled amount box */
            .dr-input:disabled,
            .cr-input:disabled {
                color: #999;
                cursor: not-allowed;
            }

            .hidden-amount {
                display: none !important;
            }

            /* Remove number input arrows - Chrome, Edge, Safari */
            input[type=number]::-webkit-inner-spin-button,
            input[type=number]::-webkit-outer-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }

            /* Remove number input arrows - Firefox */
            input[type=number] {
                -moz-appearance: textfield;
            }

            .cur-bal-row {
                color: #444;
                font-style: italic;
            }

            .dc-select {
                border: none;
                background: transparent;
                font-family: monospace;
                font-weight: bold;
                width: 55px;
                appearance: none;
                -webkit-appearance: none;
                -moz-appearance: none;
                cursor: pointer;
            }

            .dc-select:disabled {
                color: #555;
                cursor: not-allowed;
            }

            /* ================= DR / CR INPUT HIGHLIGHT ================= */

            /* Hover effect */
            .dr-input:hover,
            .cr-input:hover {
                background-color: #fff6cc !important;
                border: 1px solid #010101 !important;
                cursor: text !important;
            }

            /* Focus (cursor inside) */
            .dr-input:focus,
            .cr-input:focus {
                background-color: #fff1a8 !important;
                border: 1px solid #010101 !important;
                outline: none !important;
                box-shadow: 0 0 2px rgba(201, 168, 0, 0.6) !important;
            }

            /* Smooth transition */
            .dr-input,
            .cr-input {
                transition: background-color 0.15s ease, border 0.15s ease, box-shadow 0.15s ease !important;
            }

            .dr-input {
                width: 100px !important;
            }

            .cr-input {
                width: 100px !important;
            }

            .dc-select.locked {
                pointer-events: none;
                background: transparent;
            }
        </style>

        <div class="wrapper">
            <div class="content-page">
                <div class="container-fluid">
                    <div class="card">

                        {{-- ================= CARD HEADER ================= --}}
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Accounting Voucher Creation</h4>
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

                                        {{-- ================= TYPE + REF + DATE ================= --}}
                                        <table class="w-100 mb-3">
                                            <tr>
                                                <td width="80%">
                                                    <span class="voucher-type-label" id="voucherTypeLabel">
                                                        {{ old('voucher_type', 'Journal') }}
                                                    </span>

                                                    {{-- enable if needed --}}
                                                    <strong class="ms-2">NO.</strong>
                                                    <span id="voucher_no">
                                                        {{ old('ref_no', $lastVoucher ?? 'JN-0001') }}
                                                    </span>

                                                    <input type="hidden" name="ref_no" id="ref_no"
                                                        value="{{ old('ref_no', $lastVoucher ?? 'JN-0001') }}">
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
                                        <div class="table-wrapper">
                                            <table id="linesTable">
                                                <thead>
                                                    <tr>
                                                        <th width="5%"></th>
                                                        <th width="80%">Particulars</th>
                                                        {{-- <th class="text-end" width="15%"></th> --}}
                                                        {{-- <th width="15%">Amount</th> --}}
                                                        <th class="text-end" width="10%">Debit</th>
                                                        <th class="text-end" width="10%">Credit</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    @php $oldLines = old('lines', []); @endphp
                                                    @if ($oldLines)
                                                        @foreach ($oldLines as $i => $line)
                                                            <tr class="line">
                                                                <td width="5%">
                                                                    <input type="hidden"
                                                                        name="lines[{{ $i }}][amount]"
                                                                        class="amount">
                                                                    <select name="lines[{{ $i }}][dc]"
                                                                        class="dc-select">
                                                                        <option value="Dr"
                                                                            {{ ($line['dc'] ?? '') == 'Dr' ? 'selected' : '' }}>
                                                                            By
                                                                        </option>
                                                                        <option value="Cr"
                                                                            {{ ($line['dc'] ?? '') == 'Cr' ? 'selected' : '' }}>
                                                                            To
                                                                        </option>
                                                                    </select>
                                                                </td>

                                                                <td width="80%">
                                                                    <select name="lines[{{ $i }}][ledger_id]"
                                                                        class="ledger">
                                                                        <option value="">Select Ledger</option>
                                                                        @foreach ($ledgers as $l)
                                                                            <option value="{{ $l->id }}"
                                                                                data-group-id="{{ $l->group_id }}"
                                                                                {{ ($line['ledger_id'] ?? '') == $l->id ? 'selected' : '' }}>
                                                                                {{ $l->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td width="5%" class="text-end">
                                                                    <input type="number" class="dr-input text-end"
                                                                        value="{{ ($line['dc'] ?? '') === 'Dr' ? $line['amount'] : '' }}">
                                                                </td>

                                                                <td width="5%" class="text-end">
                                                                    <input type="number" class="cr-input text-end"
                                                                        value="{{ ($line['dc'] ?? '') === 'Cr' ? $line['amount'] : '' }}">
                                                                </td>

                                                                <td class="text-center" width="5%">
                                                                    <span class="remove"
                                                                        {{ $loop->count == 1 ? 'style=display:none' : '' }}><i
                                                                            class="fa-solid fa-xmark"></i></span>
                                                                </td>
                                                            </tr>

                                                            @if (!empty($line['ledger_id']) && !empty($line['cur_balance_text']))
                                                                <tr class="cur-bal-row ml-4">
                                                                    <td colspan="3"
                                                                        style="padding-left:50px;font-style:italic;">
                                                                        {{ $line['cur_balance_text'] }}
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <tr class="line">
                                                            <td width="5%">
                                                                <input type="hidden" name="lines[0][amount]"
                                                                    class="amount">
                                                                <select name="lines[0][dc]" class="dc-select">
                                                                    <option value="Dr"
                                                                        {{ ($line['dc'] ?? '') == 'Dr' ? 'selected' : '' }}>
                                                                        By
                                                                    </option>
                                                                    <option value="Cr"
                                                                        {{ ($line['dc'] ?? '') == 'Cr' ? 'selected' : '' }}>
                                                                        To
                                                                    </option>
                                                                </select>
                                                            </td>
                                                            <td width="80%">
                                                                <select name="lines[0][ledger_id]" class="ledger">
                                                                    <option value="">Select Ledger</option>
                                                                    @foreach ($ledgers as $l)
                                                                        <option value="{{ $l->id }}"
                                                                            data-group-id="{{ $l->group_id }}">
                                                                            {{ $l->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </td>

                                                            <td width="5%" class="text-end">
                                                                <input type="number" class="dr-input text-end"
                                                                    value="{{ ($line['dc'] ?? '') === 'Dr' ? $line['amount'] : '' }}">
                                                            </td>

                                                            <td width="5%" class="text-end">
                                                                <input type="number" class="cr-input text-end"
                                                                    value="{{ ($line['dc'] ?? '') === 'Cr' ? $line['amount'] : '' }}">
                                                            </td>


                                                            <td class="text-center" width="5%">
                                                                <span class="remove" style="display:none;"><i
                                                                        class="fa-solid fa-xmark"></i></span>
                                                            </td>
                                                        </tr>
                                                    @endif

                                                </tbody>

                                                <tfoot>
                                                    <tr class="">
                                                        <td></td>
                                                        <td>
                                                            <div class="">
                                                                Narration :
                                                                <input type="text" name="narration" class="">
                                                            </div>
                                                        </td>

                                                        <td class="text-end"
                                                            style="border-top:1px solid #ccc;font-weight:bold">
                                                            <div><span id="totalDrText">0.00</span></div>
                                                        </td>

                                                        <td class="text-end"
                                                            style="border-top:1px solid #ccc;font-weight:bold">
                                                            <div><span id="totalCrText">0.00</span></div>
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
                                        </div>

                                        <div class="mt-3">
                                            <a href="{{ route('accounting.ledgers.create', 'voucher') }}" target="_blank"
                                                class="btn btn-outline-secondary btn-sm">
                                                Create Ledger
                                            </a>
                                        </div>
                                        {{-- ================= NARRATION ================= --}}


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
            const HAS_OLD_VALUES = {{ old('lines') ? 'true' : 'false' }};

            $(document).on('click', '.type-pill', function() {
                $('#voucherTypeLabel').text($(this).data('type'));
            });

            const VOUCHER_DC_MAP = {
                Journal: null, // decided by ledger group
                Payment: ['Dr', 'Cr'],
                Receipt: ['Dr', 'Cr'],
                Contra: ['Dr', 'Cr'],
                Sales: ['Dr', 'Cr'],
                Purchase: ['Dr', 'Cr']
            };

            // Prevent form submit on Enter key
            $(document).on('keydown', 'form#voucherForm input', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    return false;
                }
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

                function rowTpl(idx) {
                    return `
                    <tr class="line">
                        <td width="8%">
                                    <select name="lines[${idx}][dc]" class="dc-select">
                                        <option value="Dr">By</option>
                                        <option value="Cr">To</option>
                                    </select>
                                </td>
                        <input type="hidden" name="lines[${idx}][amount]" class="amount">

                        <td width="75%">
                            <select name="lines[${idx}][ledger_id]" class="ledger">
                                <option value="">Select Ledger</option>
                                ${ledgerOptions}
                            </select>
                        </td>

                        <td width="10%" class="text-end">
                            <input type="number" class="dr-input text-end">
                        </td>

                        <td width="10%" class="text-end">
                            <input type="number"  class="cr-input text-end">
                        </td>

                        <td class="text-center" width="5%">
                            <span class="remove" style="display:none;">✕</span>
                        </td>
                    </tr>`;
                }

                function rowHasAnyValue($tr) {
                    const ledger = $tr.find('.ledger').val();
                    const dc = $tr.find('input[name*="[dc]"]').val();
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
                    let hasDr = false;
                    let hasCr = false;

                    $('#linesTable tbody tr').each(function() {
                        const dc = $(this).find('.dc-select').val();
                        const amount = parseFloat($(this).find('.amount').val()) || 0;

                        if (dc === 'Dr' && amount > 0) hasDr = true;
                        if (dc === 'Cr' && amount > 0) hasCr = true;
                    });

                    if (hasDr && hasCr) {
                        $('#btnSubmit').show();
                    } else {
                        $('#btnSubmit').hide();
                    }
                }

                function addLineRow() {
                    const idx = $('#linesTable tbody tr').length;

                    $('#linesTable tbody').append(rowTpl(idx));

                    const $newRow = $('#linesTable tbody tr').last();

                    // ❌ Do NOT auto-copy DC
                    $newRow.find('.dc-select').val('');

                    syncAmountInputs($newRow);
                    toggleRemoveButtons();
                }

                // ✅ expose globally (FINAL FIX)
                window._addLineRow = function() {
                    return addLineRow.apply(this, arguments);
                };

                function setDCForAllRowsByType(force = false) {
                    $('#linesTable tbody tr').each(function() {
                        const $tr = $(this);
                        const hasUserAmount =
                            $tr.find('.dr-input').val() || $tr.find('.cr-input').val();

                        if (hasUserAmount && !force) return;
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

                window.recalc = function() {
                    let dr = 0,
                        cr = 0;

                    $('#linesTable tbody tr').each(function() {
                        const dc = $(this).find('.dc').val();
                        // alert(dc);
                        const amt = parseFloat($(this).find('.amount').val() || 0);

                        if (dc === 'Dr') dr += amt;
                        if (dc === 'Cr') cr += amt;
                    });

                    // $('#totalDr').val(dr);
                    // $('#totalCr').val(cr);

                    // $('#totalDrText').text(dr);
                    // $('#totalCrText').text(cr);
                };

                $(document).on('click', '.remove', function() {
                    const $lineRow = $(this).closest('tr');

                    // If Cur Bal row is immediately after this line, remove it
                    if ($lineRow.next().hasClass('cur-bal-row')) {
                        $lineRow.next().remove();
                    }

                    // Remove the actual line row
                    $lineRow.remove();
                    setTimeout(updateDrCrTotals, 50);
                    toggleRemoveButtons();
                    // recalc();
                });

                $(document).on('input change', '.amount, .dc', recalc);


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
                        $r0.find('.amount').val(amt);

                        const $r1 = ensureRow(1, 'Cr');
                        $r1.find('.ledger').val(party);
                        $r1.find('.amount').val(amt);
                    } else if (t === 'Receipt') {
                        const $r0 = ensureRow(0, 'Dr');
                        $r0.find('.ledger').val(party);
                        $r0.find('.amount').val(amt);

                        const $r1 = ensureRow(1, 'Cr');
                        $r1.find('.ledger').val(counter);
                        $r1.find('.amount').val(amt);
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
                    $r0.find('.amount').val(amt);

                    const $r1 = ensureRow(1, 'Dr');
                    $r1.find('.ledger').val(to);
                    $r1.find('.amount').val(amt);

                    recalc();
                }

                function calcTradeGrand() {
                    const s = parseFloat($('#tr_subtotal').val() || 0);
                    const d = parseFloat($('#tr_discount').val() || 0);
                    const t = parseFloat($('#tr_tax').val() || 0);
                    $('#tr_grand').val((s - d + t));
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
                        $r0.find('.amount').val(amt);

                        const $r1 = ensureRow(1, 'Cr');
                        $r1.find('.amount').val(amt);
                    } else if (t === 'Purchase' || t === 'DebitNote') {
                        const $r0 = ensureRow(0, 'Dr');
                        $r0.find('.amount').val(amt);

                        const $r1 = ensureRow(1, 'Cr');
                        $r1.find('.ledger').val(pl);
                        $r1.find('.amount').val(amt);
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

                    setDCForAllRowsByType(true);

                    filterLedgerDropdownsByVoucherType();
                    applyJournalPrefix();
                    autobuildPR();
                    autobuildCT();
                    calcTradeGrand();
                    autobuildTR();
                    updateSubmitVisibility(); // NEW
                    restoreDrCrFromAmount();
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

                $(document).on('change', '#linesTable tbody tr .ledger', function() {

                    const $row = $(this).closest('tr');

                    const dr = parseFloat($row.find('.dr-input').val()) || 0;
                    const cr = parseFloat($row.find('.cr-input').val()) || 0;

                    // Keep input visibility correct
                    if (dr > 0) {
                        $row.find('.dr-input').removeClass('hidden-amount');
                        $row.find('.cr-input').addClass('hidden-amount');
                        $row.find('.amount').val(dr);
                        $row.find('.dc-select').val('Dr');
                    } else if (cr > 0) {
                        $row.find('.cr-input').removeClass('hidden-amount');
                        $row.find('.dr-input').addClass('hidden-amount');
                        $row.find('.amount').val(cr);
                        $row.find('.dc-select').val('Cr');
                    }

                    // Show current balance
                    if ($(this).val()) {
                        showCurBalanceRow($row);
                    }

                    // ✅ FIX: total will not become 0
                    updateDrCrTotals();
                    updateSubmitVisibility();
                    const dc = $row.find('.dc-select').val();

                    let $targetInput = dc === 'Cr' ?
                        $row.find('.cr-input') :
                        $row.find('.dr-input');

                    // Focus + highlight
                    setTimeout(() => {
                        $targetInput
                            .removeClass('hidden-amount')
                            .focus()
                            .select();
                    }, 50);
                });

                $(document).on('change', '.account-ledger', function() {

                    const p_id = $(this).closest('tr');

                    showCurBalanceRow(p_id, "account-ledger");

                });

                // AMOUNT INPUT → ADD NEW ROW AFTER CUR BAL
                $(document).on('input', '#linesTable tbody tr .amount', function() {

                    const $tr = $(this).closest('tr');
                    const amount = parseFloat($(this).val() || 0);

                    if (amount > 0 && $tr.is('#linesTable tbody tr.line:last')) {
                        addLineRowAfterRow($tr);
                        if ($totalDr && $totalDr.val) $totalDr.val(amount);
                        if ($totalCr && $totalCr.val) $totalCr.val(amount);

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
                setDCForAllRowsByType(true);
                filterLedgerDropdownsByVoucherType();
                updateSubmitVisibility();
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

            function showCurBalanceRow($lineRow, type) {

                const ledgerId = $lineRow.find('.ledger').val();
                if (!ledgerId) return;

                // Remove existing Cur Bal row
                if ($lineRow.next().hasClass('cur-bal-row')) {
                    $lineRow.next().remove();
                }

                // Temporary loading row
                const loadingHtml = `
                    <tr class="cur-bal-row">
                        <td style="padding-left:50px;font-style:italic;" colspan="3">
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

                        const $dc = $lineRow.find('.dc');
                        const $dr = $lineRow.find('.dr-input');
                        const $cr = $lineRow.find('.cr-input');
                        const $amount = $lineRow.find('.amount');


                        const bal = toNumber(res.balance);

                        // console.log("Balance fetched for ledger", bal);
                        const openingBalance = (toNumber(res.balance) || 0);
                        const openingType = res.type; // Dr / Cr

                        // Convert to signed number
                        let baseBalance = openingType === 'Dr' ? openingBalance : -openingBalance;

                        // store for later calculation
                        $lineRow.data('opening-balance', baseBalance);

                        const displayBalance = baseBalance >= 0 ? baseBalance : (-baseBalance);


                        const curBalHtml = `
                            <tr class="cur-bal-row">
                                <td colspan="3" style="padding-left:115px;font-style:italic;">
                                    Current Bal: ${displayBalance} ${openingType}
                                </td>
                            </tr>
                            `;

                        $lineRow.next('.cur-bal-row').replaceWith(curBalHtml);

                        applyJournalPrefix();
                        recalc();
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

            function toNumber(val) {
                return parseFloat(String(val).replace(/,/g, '')) || 0;
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
                applyDcRules();
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
                        $('#ref_no').val(res.next_ref_no);

                    },
                    error: function() {
                        alert('Unable to fetch voucher number');
                    }
                });
            });

            $(document).ready(function() {

                $('#linesTable tbody tr').each(function(index) {

                    if ($(this).find('.ledger').val()) {
                        showCurBalanceRow($(this));
                    }
                });

                applyDcRules();
                lockFirstRowDC();
                // updateDrCrTotals();

                if (HAS_OLD_VALUES) {
                    restoreDrCrFromAmount();
                    recalc();
                    forceShowSubmitIfOld();
                }

            });

            function applyJournalPrefix() {
                $('.ledger').each(function() {
                    const $row = $(this).closest('tr');
                    const dc = $row.find('.dc').val();

                    let prefix = '';
                    if (dc === 'Dr') prefix = 'To';
                    if (dc === 'Cr') prefix = 'By';

                    $(this).find('option').each(function() {
                        if (!$(this).data('original-name')) {
                            $(this).data('original-name', $(this).text());
                        }

                        $(this).text(prefix + ' ' + $(this).data('original-name'));
                    });
                });
            }

            function updateAccountHeader(name, balance, type) {
                $('#accountName').text(name);
                $('#accountBalance')
                    .text(balance + ' ' + type)
                    .removeClass('text-danger text-dark')
                    .addClass(type === 'Cr' ? 'text-danger' : 'text-dark');
            }

            function getSecondRowDC() {
                const $row2 = $('#linesTable tbody tr').eq(1);
                return $row2.length ? $row2.find('.dc-select').val() : null;
            }

            $(document).on('blur', '.dr-input, .cr-input', function() {

                const $row = $(this).closest('tr');
                const rowIndex = $row.index();

                const dr = parseFloat($row.find('.dr-input').val()) || 0;
                const cr = parseFloat($row.find('.cr-input').val()) || 0;

                // Ignore empty rows
                if (dr === 0 && cr === 0) return;

                // Prevent duplicate row creation
                if ($row.data('row-done')) return;
                $row.data('row-done', true);

                // ---- CALCULATE RUNNING BALANCE ----
                let balance = 0;

                $('#linesTable tbody tr').each(function(i) {
                    if (i > rowIndex) return;

                    const d = parseFloat($(this).find('.dr-input').val()) || 0;
                    const c = parseFloat($(this).find('.cr-input').val()) || 0;

                    balance += (d - c);
                });

                // if (balance === 0) return;

                // ---- CREATE NEXT ROW ----
                window._addLineRow();

                const $nextRow = $('#linesTable tbody tr').last();

                if (balance > 0) {
                    // Need CREDIT
                    $nextRow.find('.dc-select').val('Cr');
                    $nextRow.find('.cr-input').val(balance);
                } else {
                    // Need DEBIT
                    $nextRow.find('.dc-select').val('Dr');
                    $nextRow.find('.dr-input').val(Math.abs(balance));
                }

                syncAmountInputs($nextRow);
            });

            $(document).on('focus', '.dr-input, .cr-input', function() {
                $(this).closest('tr').data('auto-filled', false);
            });


            function maybeAddNewRow($row) {
                if ($row.data('row-added')) return;

                const dr = parseFloat($row.find('.dr-input').val()) || 0;
                const cr = parseFloat($row.find('.cr-input').val()) || 0;

                if (dr > 0 || cr > 0) {
                    $row.data('row-added', true);

                    window._addLineRow();

                }
            }

            function lockFirstRowDC() {
                const type = $('#voucher_type').val();

                const defaultMap = {
                    Payment: 'Cr',
                    Receipt: 'Dr',
                    Contra: 'Dr',
                    Journal: 'Cr'
                };

                const dc = defaultMap[type] || 'Dr';

                const $row = $('#linesTable tbody tr:first');

                // Only set default if empty (do NOT lock)
                if (!$row.find('.dc-select').val()) {
                    $row.find('.dc-select').val(dc);
                }

                // Allow change
                $row.find('.dc-select').prop('disabled', false);

                syncAmountInputs($row);
            }

            function isFirstRow($row) {
                return $row.index() === 0;
            }

            function restoreDrCrFromAmount() {
                $('#linesTable tbody tr').each(function() {
                    const $row = $(this);
                    const dc = $row.find('.dc').val();
                    const amount = parseFloat($row.find('.amount').val());

                    if (!dc || !amount) return;

                    if (dc === 'Dr') {
                        $row.find('.dr-input').val(amount).removeClass('hidden-amount');
                        $row.find('.cr-input').val('').addClass('hidden-amount');
                    } else {
                        $row.find('.cr-input').val(amount).removeClass('hidden-amount');
                        $row.find('.dr-input').val('').addClass('hidden-amount');
                    }
                });
            }

            function syncAmountFromInputs() {
                $('#linesTable tbody tr').each(function() {
                    const $row = $(this);

                    const dr = parseFloat($row.find('.dr-input').val() || 0);
                    const cr = parseFloat($row.find('.cr-input').val() || 0);

                    if (dr > 0) {
                        $row.find('.amount').val(dr);
                        $row.find('.dc').val('Dr');
                    } else if (cr > 0) {
                        $row.find('.amount').val(cr);
                        $row.find('.dc').val('Cr');
                    }
                });
            }

            function forceShowSubmitIfOld() {
                if (HAS_OLD_VALUES) {
                    $('#btnSubmit').show();
                }
            }

            // When amount changes
            $(document).on('input', '.dr-input, .cr-input', function() {
                const $row = $(this).closest('tr');

                const dr = parseFloat($row.find('.dr-input').val()) || 0;
                const cr = parseFloat($row.find('.cr-input').val()) || 0;

                if (dr > 0) {
                    $row.find('.amount').val(dr);
                    $row.find('.dc-select').val('Dr');
                } else if (cr > 0) {
                    $row.find('.amount').val(cr);
                    $row.find('.dc-select').val('Cr');
                }

                updateDrCrTotals();
            });

            function updateRunningBalance($row) {

                const opening = parseFloat($row.data('opening-balance') || 0);
                const openingType = $row.data('opening-type'); // Dr / Cr

                const dr = parseFloat($row.find('.dr-input').val() || 0);
                const cr = parseFloat($row.find('.cr-input').val() || 0);

                // Start from opening balance
                let balance = openingType === 'Dr' ? opening : -opening;
                const dc = $row.find('.dc-select').val();

                // Apply transaction
                if (dr > 0) balance += dr;
                if (cr > 0) balance -= cr;

                // Display logic
                const displayBalance = Math.abs(balance).toFixed(2);
                // const displayType = balance >= 0 ? 'Dr' : 'Cr';
                const displayType = dc

                const html = `
                    <tr class="cur-bal-row">
                        <td colspan="3" style="padding-left:60px;font-style:italic;">
                            Current Bal: ${displayBalance} ${displayType}
                        </td>
                    </tr>
                `;

                if ($row.next().hasClass('cur-bal-row')) {
                    $row.next().replaceWith(html);
                } else {
                    $row.after(html);
                }
            }

            function applyDcRules() {
                const type = $('#voucher_type').val();

                // ✅ Correct mapping (as per your requirement)
                const firstRowMap = {
                    Payment: 'Dr', // By
                    Receipt: 'Cr', // To
                    Contra: 'Cr', // To
                    Journal: 'Dr' // By
                };

                const dc = firstRowMap[type] || 'Dr';

                const $firstRow = $('#linesTable tbody tr:first');

                // Force DC
                // $firstRow.find('.dc-select')
                //     .val(dc)
                //     .prop('disabled', true);
                $firstRow.find('.dc-select')
                    .val(dc)
                    .addClass('locked');


                syncAmountInputs($firstRow);
            }

            function syncAmountInputs($row) {
                const dc = $row.find('.dc-select').val();

                // Hide both first
                $row.find('.dr-input').addClass('hidden-amount');
                $row.find('.cr-input').addClass('hidden-amount');

                if (dc === 'Dr') {
                    // BY → Debit
                    $row.find('.dr-input').removeClass('hidden-amount');
                    $row.find('.cr-input').val('');
                } else if (dc === 'Cr') {
                    // TO → Credit
                    $row.find('.cr-input').removeClass('hidden-amount');
                    $row.find('.dr-input').val('');
                }

                updateDrCrTotals();
            }

            $(document).on('change', '.dc-select', function() {
                const $row = $(this).closest('tr');

                if ($(this).val() === 'Dr') {
                    $row.find('.cr-input').val('');
                } else {
                    $row.find('.dr-input').val('');
                }

                updateDrCrAndTotal();

                // ❌ Prevent changing first row
                if ($row.index() === 0) {
                    return;
                }

                syncAmountInputs($row);
            });


            function calculateRunningBalance() {
                let balance = 0;

                $('#linesTable tbody tr.line').each(function() {
                    const dr = parseFloat($(this).find('.dr-input').val()) || 0;
                    const cr = parseFloat($(this).find('.cr-input').val()) || 0;

                    balance += (cr - dr);
                });

                return balance;
            }

            function applyAutoBalanceToNextRow($currentRow) {
                const balance = calculateRunningBalance();

                if (balance === 0) return;

                // ensure next row exists
                if (!$currentRow.next('.line').length) {
                    window._addLineRow();
                }

                const $nextRow = $currentRow.next('.line');
            }

            function autoBalanceNextRow($currentRow) {

                let rows = $('#linesTable tbody tr.line');
                let running = 0;

                rows.each(function() {
                    const $row = $(this);

                    const dr = parseFloat($row.find('.dr-input').val()) || 0;
                    const cr = parseFloat($row.find('.cr-input').val()) || 0;

                    running += (cr - dr);

                    if ($row.is($currentRow)) return false;
                });

                if (running === 0) return;

                // Ensure next row exists
                if (!$currentRow.next('.line').length) {
                    window._addLineRow();
                }

                const $next = $currentRow.next('.line');

                // Apply difference
                if (running > 0) {
                    // Need DR
                    $next.find('.dc-select').val('Dr');
                    $next.find('.dr-input').val(running);
                    $next.find('.cr-input').val('');
                } else {
                    // Need CR
                    $next.find('.dc-select').val('Cr');
                    $next.find('.cr-input').val(Math.abs(running));
                    $next.find('.dr-input').val('');
                }

                syncAmountInputs($next);

                // mark auto-filled
                $next.data('row-added', true);
            }

            function updateDrCrTotals() {
                let dr = 0;
                let cr = 0;

                $('#linesTable tbody tr').each(function() {
                    const dc = $(this).find('.dc-select').val();
                    const amount = parseFloat($(this).find('.amount').val()) || 0;

                    if (dc === 'Dr') dr += amount;
                    if (dc === 'Cr') cr += amount;
                });

                // Update hidden inputs
                $('#totalDr').val(dr.toFixed(2));
                $('#totalCr').val(cr.toFixed(2));

                // Update footer UI
                $('#totalDrText').text(dr.toFixed(2));
                $('#totalCrText').text(cr.toFixed(2));
            }
        </script>
    @endsection
