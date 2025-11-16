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

        @media (max-width: 992px) {
            .grid-2 {
                grid-template-columns: 1fr
            }
        }

        /* Right-side vertical voucher type panel (Tally-like) */
        .type-pills-vertical {
            position: sticky;
            top: 1.25rem;
            /* keeps it visible when scrolling inside card */
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

        /* make buttons block-level and Tally-like */
        .type-pills-vertical .btn {
            width: 100%;
            /* display: flex; */
            align-items: center;
            justify-content: flex-start;
            gap: .5rem;
            padding: .45rem .6rem;
            /* border-radius: 999px; */
            font-weight: 600;
            text-transform: none;
        }

        /* active style */
        .type-pills-vertical .btn.active {
            background: #0d6efd;
            color: #fff;
            box-shadow: none;
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        /* smaller screens: float under header instead of right-side */
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
                                    <a href="{{ route('accounting.vouchers.index') }}" class="btn btn-secondary">Back</a>
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


                                    <!-- Tally-style vertical voucher type panel (right side) -->


                                    {{-- Header row --}}
                                    <div class="row g-3 mb-3">
                                        <div class="col-lg-10">
                                            <div class="row">
                                                <div class="col-lg-4 col-md-4">
                                                    <label class="form-label">Date</label>
                                                    <input type="date" class="form-control" name="voucher_date"
                                                        value="{{ old('voucher_date', now()->toDateString()) }}" required>
                                                </div>

                                                {{-- <div class="col-lg-3 col-md-4"> --}}
                                                {{-- <label class="form-label">Type</label> --}}

                                                <input type="hidden" name="voucher_type" id="voucher_type"
                                                    value="{{ old('voucher_type', 'Journal') }}">
                                                {{-- <select name="voucher_type" id="voucher_type" class="form-control" required>
                                                @foreach (['Journal', 'Payment', 'Receipt', 'Contra', 'Sales', 'Purchase', 'DebitNote', 'CreditNote'] as $t)
                                                    <option value="{{ $t }}" @selected(old('voucher_type', 'Journal') === $t)>
                                                        {{ $t }}</option>
                                                @endforeach
                                            </select> --}}
                                                {{-- </div> --}}

                                                <div class="col-lg-4 col-md-4">
                                                    <label class="form-label">Ref No</label>
                                                    <input type="text" class="form-control" name="ref_no"
                                                        value="{{ old('ref_no') }}">
                                                </div>

                                                <div class="col-lg-4 col-md-4">
                                                    <label class="form-label">Branch</label>
                                                    <select name="branch_id" class="form-control">
                                                        <option value="">All / None</option>
                                                        @foreach ($branches ?? [] as $b)
                                                            <option value="{{ $b->id }}"
                                                                @selected(old('branch_id') == $b->id)>
                                                                {{ $b->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-lg-12 col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">Narration</label>
                                                        <textarea name="narration" class="form-control" rows="2">{{ old('narration') }}</textarea>
                                                    </div>
                                                </div>

                                                {{-- ===== TYPE-SPECIFIC SECTIONS ===== --}}

                                                {{-- Payment / Receipt --}}
                                                <div id="section-payment-receipt" class="section-card mb-3"
                                                    style="display:none;">
                                                    <div class="section-title">
                                                        <span class="badge bg-info">Payment / Receipt</span> Fill the
                                                        instrument & party
                                                        details
                                                    </div>
                                                    <div class="row g-3">
                                                        <div class="col-lg-4 col-md-6">
                                                            <label class="form-label">Party Ledger</label>
                                                            <select id="pr_party_ledger" class="form-control">
                                                                <option value="">Select</option>
                                                                @foreach ($ledgers as $l)
                                                                    <option value="{{ $l->id }}"
                                                                        @selected(old('party_ledger_id') == $l->id)>
                                                                        {{ $l->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="col-lg-2 col-md-4">
                                                            <label class="form-label">Mode</label>
                                                            <select id="pr_mode" name="mode" class="form-control">
                                                                <option value="">Select</option>
                                                                <option value="cash" @selected(old('mode') === 'cash')>Cash
                                                                </option>
                                                                <option value="bank" @selected(old('mode') === 'bank')>Bank
                                                                </option>
                                                                <option value="upi" @selected(old('mode') === 'upi')>UPI
                                                                </option>
                                                                <option value="card" @selected(old('mode') === 'card')>Card
                                                                </option>
                                                            </select>
                                                        </div>

                                                        <div class="col-lg-3 col-md-6" id="pr_cash_wrap"
                                                            style="display:none;">
                                                            <label class="form-label">Cash Ledger</label>
                                                            <select id="pr_cash_ledger" name="cash_ledger_id"
                                                                class="form-control">
                                                                <option value="">Select</option>
                                                                @foreach ($ledgers as $l)
                                                                    <option value="{{ $l->id }}"
                                                                        @selected(old('cash_ledger_id') == $l->id)>
                                                                        {{ $l->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="col-lg-3 col-md-6" id="pr_bank_wrap"
                                                            style="display:none;">
                                                            <label class="form-label">Bank Ledger</label>
                                                            <select id="pr_bank_ledger" name="bank_ledger_id"
                                                                class="form-control">
                                                                <option value="">Select</option>
                                                                @foreach ($ledgers as $l)
                                                                    <option value="{{ $l->id }}"
                                                                        @selected(old('bank_ledger_id') == $l->id)>
                                                                        {{ $l->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="col-lg-2 col-md-4">
                                                            <label class="form-label">Amount</label>
                                                            <input id="pr_amount" name="amount" type="number"
                                                                step="0.01" class="form-control"
                                                                value="{{ old('amount') }}">
                                                        </div>

                                                        <div class="col-lg-2 col-md-4">
                                                            <label class="form-label">Instrument No</label>
                                                            <input id="pr_inst_no" name="instrument_no"
                                                                class="form-control" value="{{ old('instrument_no') }}">
                                                        </div>

                                                        <div class="col-lg-2 col-md-4">
                                                            <label class="form-label">Instrument Date</label>
                                                            <input id="pr_inst_date" name="instrument_date"
                                                                type="date" class="form-control"
                                                                value="{{ old('instrument_date') }}">
                                                        </div>

                                                        <div class="col-12">
                                                            <div class="form-check mt-2">
                                                                <input class="form-check-input" type="checkbox"
                                                                    id="pr_autobuild" checked>
                                                                <label class="form-check-label"
                                                                    for="pr_autobuild">Auto-build balanced
                                                                    lines from these fields</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Contra --}}
                                                <div id="section-contra" class="section-card mb-3" style="display:none;">
                                                    <div class="section-title">
                                                        <span class="badge bg-warning text-dark">Contra</span> Move amount
                                                        between
                                                        ledgers
                                                    </div>
                                                    <div class="row g-3">
                                                        <div class="col-md-5">
                                                            <label class="form-label">From Ledger</label>
                                                            <select id="ct_from" name="from_ledger_id"
                                                                class="form-control">
                                                                <option value="">Select</option>
                                                                @foreach ($ledgers as $l)
                                                                    <option value="{{ $l->id }}"
                                                                        @selected(old('from_ledger_id') == $l->id)>
                                                                        {{ $l->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <label class="form-label">To Ledger</label>
                                                            <select id="ct_to" name="to_ledger_id"
                                                                class="form-control">
                                                                <option value="">Select</option>
                                                                @foreach ($ledgers as $l)
                                                                    <option value="{{ $l->id }}"
                                                                        @selected(old('to_ledger_id') == $l->id)>
                                                                        {{ $l->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">Amount</label>
                                                            <input id="ct_amount" name="contra_amount" type="number"
                                                                step="0.01" class="form-control"
                                                                value="{{ old('contra_amount') }}">
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="form-check mt-2">
                                                                <input class="form-check-input" type="checkbox"
                                                                    id="ct_autobuild" checked>
                                                                <label class="form-check-label"
                                                                    for="ct_autobuild">Auto-build
                                                                    lines</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Sales / Purchase / Notes --}}
                                                <div id="section-trade" class="section-card mb-3" style="display:none;">
                                                    <div class="section-title">
                                                        <span class="badge bg-primary">Sales / Purchase / Notes</span>
                                                        Totals with live
                                                        calculation
                                                    </div>
                                                    <div class="grid-2">
                                                        <div>
                                                            <label class="form-label">Party Ledger</label>
                                                            <select id="tr_party_ledger" class="form-control">
                                                                <option value="">Select</option>
                                                                @foreach ($ledgers as $l)
                                                                    <option value="{{ $l->id }}"
                                                                        @selected(old('party_ledger_id') == $l->id)>
                                                                        {{ $l->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="grid-2">
                                                            <div>
                                                                <label class="form-label">Sub Total</label>
                                                                <input id="tr_subtotal" name="sub_total" type="number"
                                                                    step="0.01" class="form-control"
                                                                    value="{{ old('sub_total', 0) }}">
                                                            </div>
                                                            <div>
                                                                <label class="form-label">Discount</label>
                                                                <input id="tr_discount" name="discount" type="number"
                                                                    step="0.01" class="form-control"
                                                                    value="{{ old('discount', 0) }}">
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <label class="form-label">Tax</label>
                                                            <input id="tr_tax" name="tax" type="number"
                                                                step="0.01" class="form-control"
                                                                value="{{ old('tax', 0) }}">
                                                        </div>
                                                        <div>
                                                            <label class="form-label">Grand Total</label>
                                                            <input id="tr_grand" name="grand_total" type="number"
                                                                step="0.01" class="form-control"
                                                                value="{{ old('grand_total', 0) }}" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" id="tr_autobuild"
                                                            checked>
                                                        <label class="form-check-label" for="tr_autobuild">Auto-build
                                                            lines from
                                                            totals</label>
                                                    </div>
                                                </div>

                                                {{-- Journal lines --}}
                                                <div class="section-card">
                                                    <div class="section-title">
                                                        <span class="badge bg-secondary">Journal</span> Add line items
                                                        (Dr/Cr)
                                                    </div>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered align-middle mb-0"
                                                            id="linesTable">
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
                                                                                <select
                                                                                    name="lines[{{ $i }}][ledger_id]"
                                                                                    class="form-control ledger">
                                                                                    @foreach ($ledgers as $l)
                                                                                        <option
                                                                                            value="{{ $l->id }}"
                                                                                            @selected(($ln['ledger_id'] ?? null) == $l->id)>
                                                                                            {{ $l->name }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <select
                                                                                    name="lines[{{ $i }}][dc]"
                                                                                    class="form-control dc">
                                                                                    <option @selected(($ln['dc'] ?? 'Dr') === 'Dr')>Dr
                                                                                    </option>
                                                                                    <option @selected(($ln['dc'] ?? 'Dr') === 'Cr')>Cr
                                                                                    </option>
                                                                                </select>
                                                                            </td>
                                                                            <td><input
                                                                                    name="lines[{{ $i }}][amount]"
                                                                                    class="form-control amount"
                                                                                    type="number" step="0.01"
                                                                                    value="{{ $ln['amount'] ?? '' }}">
                                                                            </td>
                                                                            <td><input
                                                                                    name="lines[{{ $i }}][line_narration]"
                                                                                    class="form-control"
                                                                                    value="{{ $ln['line_narration'] ?? '' }}">
                                                                            </td>
                                                                            <td><button type="button"
                                                                                    class="btn btn-sm btn-danger remove">×</button>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @else
                                                                    <tr class="line">
                                                                        <td>
                                                                            <select name="lines[0][ledger_id]"
                                                                                class="form-control ledger">
                                                                                @foreach ($ledgers as $l)
                                                                                    <option value="{{ $l->id }}">
                                                                                        {{ $l->name }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <select name="lines[0][dc]"
                                                                                class="form-control dc">
                                                                                <option>Dr</option>
                                                                                <option>Cr</option>
                                                                            </select>
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
                                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                                        <button type="button" id="addLine"
                                                            class="btn btn-outline-primary">+ Add
                                                            Line</button>
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
                                                            <input id="totalDr" class="form-control" readonly>
                                                        </div>
                                                        <div class="input-group input-group-sm" style="width:180px;">
                                                            <span class="input-group-text">Total Cr</span>
                                                            <input id="totalCr" class="form-control" readonly>
                                                        </div>
                                                        <span id="stickyBadge" class="badge bg-secondary">Not
                                                            Calculated</span>
                                                    </div>
                                                    <button class="btn btn-success" id="btnSubmit">Create Voucher</button>
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

    <script>
        (function() {
            // ---------- Helpers ----------
            const $type = $('#voucher_type');
            const typePillClass = 'active';

            const $secPR = $('#section-payment-receipt');
            const $secCT = $('#section-contra');
            const $secTR = $('#section-trade');

            const $prMode = $('#pr_mode');
            const $prCashWrap = $('#pr_cash_wrap');
            const $prBankWrap = $('#pr_bank_wrap');

            const $totalDr = $('#totalDr');
            const $totalCr = $('#totalCr');
            const $badgeTop = $('#balanceBadge .badge');
            const $badgeSticky = $('#stickyBadge');

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
                $secPR.toggle(isPR);
                $secCT.toggle(isCT);
                $secTR.toggle(isTR);
                togglePRMode();
            }

            function togglePRMode() {
                const m = $prMode.val();
                $prCashWrap.toggle(m === 'cash');
                $prBankWrap.toggle(m === 'bank' || m === 'upi' || m === 'card');
            }

            // ---------- Lines handling ----------
            let i = $('#linesTable tbody tr').length ? $('#linesTable tbody tr').length : 1;
            const ledgerOptions =
                `@foreach ($ledgers as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach`;

            // ==== AUTO DC BY TYPE ====
            // Adjust defaults here if you want a different pattern per type.
            const DC_MAP = {
                Journal: ['Cr', 'Dr'], // row0 -> Cr, row1 -> Dr
                Contra: ['Cr', 'Dr'],
                Receipt: ['Cr', 'Dr'],
                default: ['Dr', 'Cr'], // most types
            };

            function defaultDC(type, index) {
                const arr = DC_MAP[type] || DC_MAP.default;
                return arr[index] || 'Dr';
            }
            // Set DC on existing rows; skip rows that already have an amount (unless force = true)
            function setDCForAllRowsByType(force = false) {
                const type = $type.val();
                $('#linesTable tbody tr').each(function(idx) {
                    const $tr = $(this);
                    const hasAmt = parseFloat($tr.find('.amount').val() || 0) > 0;
                    if (hasAmt && !force) return;
                    $tr.find('.dc').val(defaultDC(type, idx));
                });
            }

            function rowTpl(idx, dcDefault) {
                const drSel = (dcDefault === 'Dr') ? 'selected' : '';
                const crSel = (dcDefault === 'Cr') ? 'selected' : '';
                return `
                <tr class="line">
                    <td><select name="lines[${idx}][ledger_id]" class="form-control ledger">${ledgerOptions}</select></td>
                    <td>
                        <select name="lines[${idx}][dc]" class="form-control dc">
                            <option ${drSel}>Dr</option>
                            <option ${crSel}>Cr</option>
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
                $totalDr.val(dr.toFixed(2));
                $totalCr.val(cr.toFixed(2));
                if (dr === 0 && cr === 0) setBadge('none');
                else if (Math.abs(dr - cr) < 0.005) setBadge('ok');
                else setBadge('bad');
            }

            $('#addLine').on('click', function() {
                const t = $type.val();
                const dcDefault = defaultDC(t, i);
                $('#linesTable tbody').append(rowTpl(i, dcDefault));
                i++;
                recalc();
            });
            $(document).on('click', '.remove', function() {
                $(this).closest('tr').remove();
                recalc();
            });
            $(document).on('input change', '.amount, .dc', recalc);

            // Copy helpers
            $('#copyDrToCr').on('click', function() {
                const dr = parseFloat($totalDr.val() || 0);
                if (dr <= 0) return;
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
                const cr = parseFloat($totalCr.val() || 0);
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

            // Guard: don't autobuild if lines already have values
            function linesHaveAnyAmount() {
                return $('#linesTable tbody tr .amount').filter(function() {
                    return $(this).val();
                }).length > 0;
            }

            // ----- Auto-build helpers -----
            function ensureRow(idx, dc) {
                while ($('#linesTable tbody tr').length <= idx) {
                    $('#addLine').click();
                }
                const $row = $('#linesTable tbody tr').eq(idx);
                $row.find('.dc').val(dc);
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
                    $r1.find('.amount').val(amt.toFixed(2)); // user picks Sales ledger
                } else if (t === 'Purchase' || t === 'DebitNote') {
                    const $r0 = ensureRow(0, 'Dr');
                    $r0.find('.amount').val(amt.toFixed(2)); // user picks Purchase ledger
                    const $r1 = ensureRow(1, 'Cr');
                    $r1.find('.ledger').val(pl);
                    $r1.find('.amount').val(amt.toFixed(2));
                }
                recalc();
            }

            // Party hidden sync
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

            // Events
            $('.type-pill').on('click', function() {
                $type.val($(this).data('type')).trigger('change');
            });

            function syncPills() {
                const t = $type.val();
                $('.type-pill').removeClass('active').each(function() {
                    if ($(this).data('type') === t) $(this).addClass('active');
                });
            }

            $type.on('change', function() {
                showSections();
                syncPills();
                togglePRMode();
                syncPartyHidden();

                // apply Dr/Cr defaults for current type (won't overwrite rows with amounts)
                setDCForAllRowsByType(false);

                // existing logic
                autobuildPR();
                autobuildCT();
                calcTradeGrand();
                autobuildTR();
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

            // Submit guard
            $('#btnSubmit').on('click', function(e) {
                syncPartyHidden();
                const dr = parseFloat($totalDr.val() || 0);
                const cr = parseFloat($totalCr.val() || 0);
                if (Math.round(dr * 100) !== Math.round(cr * 100)) {
                    e.preventDefault();
                    alert('Total Debit and Credit must be equal before posting.');
                    return false;
                }
            });

            // Initial paint
            showSections();
            syncPills();
            togglePRMode();
            syncPartyHidden();
            recalc();
            calcTradeGrand();

            // set initial Dr/Cr defaults once (won't overwrite any old() amounts)
            setDCForAllRowsByType(false);
        })();

        // ensure syncPills uses .type-pill anywhere (horizontal or vertical)
        function syncPills() {
            const t = $type.val();
            $('.type-pill').removeClass('active').each(function() {
                if ($(this).data('type') === t) $(this).addClass('active');
            });

            // Also update aria-pressed for accessibility
            $('.type-pill').attr('aria-pressed', 'false');
            $('.type-pill.active').attr('aria-pressed', 'true');
        }

        // allow keyboard activation on Enter/Space for accessibility
        $(document).on('keydown', '.type-pill', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).trigger('click');
            }
        });
    </script>
@endsection
