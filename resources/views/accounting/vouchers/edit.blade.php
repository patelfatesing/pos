@extends('layouts.backend.layouts')

@section('page-content')
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <style>
        /* ================= TALLY STYLE ================= */
        #linesTable {
            width: 100%;
            font-family: monospace;
            height: calc(100vh - 350px);
        }

        #linesTable tbody {
            height: 100%;
            vertical-align: top;
        }

        #linesTable thead,
        #linesTable tfoot,
        #linesTable tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        #linesTable thead th {
            border-top: 4px solid #a7a3a3;
            border-bottom: 3px solid #bbb8b8;
            font-weight: bold;
            margin-left: 10px;
        }

        #linesTable tfoot td {
            /* border-top: 1px solid #ccc; border-bottom: 1px solid #ccc; */
            font-weight: bold;
        }

        #linesTable td {
            vertical-align: middle;
            font-size: 14px;
            line-height: 1.7;
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
            font-size: 14px;
            padding: 4px 30px;
            display: inline-block;
        }

        .voucher-type-box {
            font-size: 13px;
            line-height: 17px;
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
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
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

        .type-pills-vertical .btn {
            padding: 0.1rem 0.75rem;
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
        .text-end {
            text-align: right;
        }

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
            font-size: 13px;
            line-height: 1;
            text-align: right;
            padding: 0;
        }

        /* remove calendar icon (already discussed, safe to repeat) */
        .voucher-date-input::-webkit-calendar-picker-indicator {
            display: none;
        }

        .voucher-day {
            font-size: 13px;
            color: #5a5a8a;
            font-weight: normal;
            line-height: 1;
            margin-top: 2px;
        }

        #linesTable tfoot tr:first-child td {
            padding-top: 5px;
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

        .avc_card .card-body,
        .card .card-header.avc-header {
            padding: .5rem .9rem;
        }

        .card .card-header.avc-header {
            background: #AFBEFA;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            color: #fff;
        }

        .avc-header h4 {
            font-size: 15px;
        }

        .avc-header .btn-sucess.btn-sm {
            padding: 4px 8px;
            font-size: 12px;
        }

        .voucher-table .cmn-table td {
            font-size: 13px;
            line-height: 1.2;
        }

        .content-page.create-voucher-page {
            min-height: 100%;
            padding: 90px 0 0;
        }

        .min-w-100 {
            width: 100px;
        }

        .tally-particulars_header {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .create-ledger-link .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 16px;
            line-height: 1.3;
            font-size: 16px;
            line-height: 1.5;
        }

        #linesTable .remove_badge {
            padding: 0;
            width: 35px;
            min-width: 35px;
            text-align: center;
        }

        .remove_badge .remove {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            cursor: pointer;
            color: #c0392b;
            font-weight: bold;
            font-size: 18px;
            border-radius: 4px;
        }

        .remove_badge .remove:hover {
            background-color: #ffe6e6;
            color: #ff0000;
        }

        .title-table {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
            min-width: 30%;
        }
    </style>

    <div class="wrapper">
        <div class="content-page create-voucher-page">
            <div class="container-fluid">

                <div class="card avc_card">

                    {{-- ================= HEADER ================= --}}
                    <div class="avc-header card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Accounting Voucher Update</h4>
                        <h5 class="title-table">LIQUOR HUB</h5>

                        <div class="create-ledger-link">
                            <a href="{{ route('accounting.ledgers.create', 'voucher') }}" target="_blank"
                                class="btn btn-success btn-sm">Create Ledger</a>

                            <button onclick="window.history.back()" class="btn btn-secondary">
                                Back
                            </button>
                        </div>
                    </div>

                    <div class="card-body">

                        <form action="{{ route('accounting.vouchers.update', $voucher->id) }}" method="POST"
                            id="voucherForm">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="voucher_type" id="voucher_type"
                                value="{{ $voucher->voucher_type }}">

                            <div class="row g-3">

                                <div class="col-lg-10 voucher-table">

                                    {{-- ================= TYPE + REF + DATE ================= --}}
                                    <div class="tally-particulars_header">
                                        <div class="voucher-type-box">

                                            <span class="voucher-type-label" id="voucherTypeLabel">
                                                {{ $voucher->voucher_type }}
                                            </span>

                                            <strong class="ms-2 ml-1">NO.</strong>

                                            <span id="voucher_no">
                                                {{ $voucher->ref_no }}
                                            </span>

                                            <input type="hidden" name="ref_no" id="ref_no"
                                                value="{{ $voucher->ref_no }}">
                                        </div>

                                        <div class="voucher-date-box">
                                            <input type="date" name="voucher_date"
                                                value="{{ $voucher->voucher_date->format('Y-m-d') }}"
                                                class="voucher-date-input">

                                            <div class="voucher-day">
                                                {{ \Carbon\Carbon::parse($voucher->voucher_date)->format('l') }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ================= TABLE ================= --}}
                                    <div class="table-wrapper">

                                        <table id="linesTable">
                                            <thead>
                                                <tr>
                                                    <th width="5%"></th>
                                                    <th width="70%">Particulars</th>
                                                    <th class="text-end min-w-100">Debit</th>
                                                    <th class="text-end min-w-100">Credit</th>
                                                    <th class="remove_badge"></th>
                                                </tr>
                                            </thead>

                                            <tbody>

                                                @foreach ($voucher->lines as $i => $line)
                                                    <tr class="line">

                                                        <td width="5%">
                                                            <input type="hidden" name="lines[{{ $i }}][amount]"
                                                                class="amount" value="{{ $line->amount }}">

                                                            <select name="lines[{{ $i }}][dc]"
                                                                class="dc-select">
                                                                <option value="Dr"
                                                                    {{ $line->dc == 'Dr' ? 'selected' : '' }}>
                                                                    By</option>
                                                                <option value="Cr"
                                                                    {{ $line->dc == 'Cr' ? 'selected' : '' }}>
                                                                    To</option>
                                                            </select>
                                                        </td>

                                                        <td width="70%">
                                                            <select name="lines[{{ $i }}][ledger_id]"
                                                                class="ledger">
                                                                <option value="">Select Ledger</option>
                                                                @foreach ($ledgers as $l)
                                                                    <option value="{{ $l->id }}"
                                                                        data-group-id="{{ $l->group_id }}"
                                                                        {{ $l->id == $line->ledger_id ? 'selected' : '' }}>
                                                                        {{ $l->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>

                                                        <td class="text-end min-w-100">
                                                            <input type="text" class="dr-input text-end amount-format"
                                                                value="{{ $line->dc == 'Dr' ? $line->amount : '' }}">
                                                        </td>

                                                        <td class="text-end min-w-100">
                                                            <input type="text" class="cr-input text-end amount-format"
                                                                value="{{ $line->dc == 'Cr' ? $line->amount : '' }}">
                                                        </td>

                                                        <td class="text-center remove_badge">
                                                            
                                                            <span class="remove" {{ $loop->count == 1 ? 'd-none' : '' }}">✕</span>
                                                        </td>

                                                    </tr>
                                                @endforeach

                                            </tbody>

                                            <tfoot>
                                                <tr>
                                                    <td colspan="2">
                                                        Narration :
                                                        <input type="text" name="narration"
                                                            value="{{ $voucher->narration }}">
                                                    </td>

                                                    <td class="text-end min-w-100"
                                                        style="border-top:1px solid #ccc;font-weight:bold;border-bottom:1px solid #ccc;">
                                                        <div style="border-bottom:1px solid #ccc;text-align:center;">
                                                            <span id="totalDrText">0.00</span>
                                                        </div>
                                                    </td>

                                                    <td class="text-end min-w-100"
                                                        style="border-top:1px solid #ccc;font-weight:bold;border-bottom:1px solid #ccc;">
                                                        <div style="border-bottom:1px solid #ccc;">
                                                            <span id="totalCrText">0.00</span>
                                                        </div>
                                                    </td>

                                                    <td></td>
                                                </tr>
                                            </tfoot>

                                        </table>

                                    </div>

                                    {{-- ================= SUBMIT ================= --}}
                                    <div class="sticky-actions mt-3">
                                        <button class="btn btn-success" id="btnSubmit">
                                            Update Voucher
                                        </button>
                                    </div>

                                </div>

                                {{-- ================= RIGHT PANEL ================= --}}
                                <div class="col-lg-2 col-md-4">
                                    <div class="type-pills-vertical" id="voucherTypePanel">

                                        @foreach (['Journal', 'Payment', 'Receipt', 'Contra'] as $t)
                                            <button type="button"
                                                class="btn btn-outline-primary me-1 mb-1 type-pill {{ $voucher->voucher_type == $t ? 'active' : '' }}"
                                                data-type="{{ $t }}">
                                                {{ $t }}
                                            </button>
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

    <script>
        let lineIndex = {{ count($voucher->lines) }};

        // ================= INDIAN FORMAT =================
        function formatIndianNumber(value) {

            value = String(value).replace(/,/g, '');

            if (value === '' || isNaN(value)) {
                return '';
            }

            let parts = value.split('.');

            let integerPart = parts[0];
            let decimalPart = parts.length > 1 ? '.' + parts[1] : '';

            let lastThree = integerPart.substring(integerPart.length - 3);
            let otherNumbers = integerPart.substring(0, integerPart.length - 3);

            if (otherNumbers !== '') {
                lastThree = ',' + lastThree;
            }

            let formatted =
                otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree;

            return formatted + decimalPart;
        }

        // ================= FORMAT INPUT =================
        $(document).on('input', '.amount-format', function() {

            let raw = $(this).val().replace(/,/g, '');

            if (raw === '' || isNaN(raw)) {
                $(this).val('');
                return;
            }

            $(this).val(formatIndianNumber(raw));

        });

        // ================= INPUT VISIBILITY =================
        function syncAmountInputs($row) {

            const dc = $row.find('.dc-select').val();

            $row.find('.dr-input').addClass('hidden-amount');
            $row.find('.cr-input').addClass('hidden-amount');

            if (dc === 'Dr') {

                $row.find('.dr-input').removeClass('hidden-amount');
                $row.find('.cr-input').val('');

            } else {

                $row.find('.cr-input').removeClass('hidden-amount');
                $row.find('.dr-input').val('');
            }
        }

        // ================= TOTALS =================
        function updateTotals() {

            let dr = 0;
            let cr = 0;

            $('#linesTable tbody tr.line').each(function() {

                const rowDr = parseFloat(
                    ($(this).find('.dr-input').val() || '0').replace(/,/g, '')
                ) || 0;

                const rowCr = parseFloat(
                    ($(this).find('.cr-input').val() || '0').replace(/,/g, '')
                ) || 0;

                dr += rowDr;
                cr += rowCr;

            });

            $('#totalDrText').text(formatIndianNumber(dr.toFixed(2)));
            $('#totalCrText').text(formatIndianNumber(cr.toFixed(2)));
        }

        // ================= ADD NEW ROW =================
        function addNewRow() {

            const row = `
                <tr class="line">

                    <td width="5%">
                        <input type="hidden"
                            name="lines[${lineIndex}][amount]"
                            class="amount">

                        <select name="lines[${lineIndex}][dc]"
                            class="dc-select">

                            <option value="Dr">By</option>
                            <option value="Cr">To</option>

                        </select>
                    </td>

                    <td width="70%">
                        <select name="lines[${lineIndex}][ledger_id]"
                            class="ledger">

                            <option value="">Select Ledger</option>

                            @foreach ($ledgers as $l)
                                <option value="{{ $l->id }}"
                                    data-group-id="{{ $l->group_id }}">
                                    {{ $l->name }}
                                </option>
                            @endforeach

                        </select>
                    </td>

                    <td class="text-end min-w-100">
                        <input type="text"
                            class="dr-input text-end amount-format">
                    </td>

                    <td class="text-end min-w-100">
                        <input type="text"
                            class="cr-input text-end amount-format">
                    </td>

                    <td class="text-center remove_badge">
                        <span class="remove" style="display:none;">✕</span>
                    </td>

                </tr>
                `;

            $('#linesTable tbody').append(row);

            const $row = $('#linesTable tbody tr.line:last');

            syncAmountInputs($row);

            lineIndex++;

            toggleRemoveButtons();

            return $row;
        }

        // ================= DOCUMENT READY =================
        $(document).ready(function() {

            $('#linesTable tbody tr.line').each(function() {

                const $row = $(this);

                syncAmountInputs($row);

                // format existing values
                let dr = $row.find('.dr-input').val();
                let cr = $row.find('.cr-input').val();

                if (dr) {
                    $row.find('.dr-input')
                        .val(formatIndianNumber(dr));
                }

                if (cr) {
                    $row.find('.cr-input')
                        .val(formatIndianNumber(cr));
                }

            });

            updateTotals();

        });

        // ================= DR / CR BLUR =================
        $(document).on('blur', '.dr-input, .cr-input', function() {

            const $row = $(this).closest('tr');

            const dr = parseFloat(
                ($row.find('.dr-input').val() || '0').replace(/,/g, '')
            ) || 0;

            const cr = parseFloat(
                ($row.find('.cr-input').val() || '0').replace(/,/g, '')
            ) || 0;

            if (dr === 0 && cr === 0) {
                return;
            }

            // store hidden amount
            if (dr > 0) {

                $row.find('.amount').val(dr);
                $row.find('.dc-select').val('Dr');

            } else {

                $row.find('.amount').val(cr);
                $row.find('.dc-select').val('Cr');
            }

            // calculate totals
            let totalDr = 0;
            let totalCr = 0;

            $('#linesTable tbody tr.line').each(function() {

                const rowDr = parseFloat(
                    ($(this).find('.dr-input').val() || '0').replace(/,/g, '')
                ) || 0;

                const rowCr = parseFloat(
                    ($(this).find('.cr-input').val() || '0').replace(/,/g, '')
                ) || 0;

                totalDr += rowDr;
                totalCr += rowCr;

            });

            const balance = totalDr - totalCr;

            // next row
            let $nextRow = $row.nextAll('tr.line:first');

            if (!$nextRow.length) {
                $nextRow = addNewRow();
            }

            // auto fill
            if (balance > 0) {

                $nextRow.find('.dc-select').val('Cr');

                $nextRow.find('.dr-input').val('');

                $nextRow.find('.cr-input')
                    .val(formatIndianNumber(balance.toString()));

                $nextRow.find('.amount').val(balance);

            } else if (balance < 0) {

                $nextRow.find('.dc-select').val('Dr');

                $nextRow.find('.cr-input').val('');

                $nextRow.find('.dr-input')
                    .val(formatIndianNumber(Math.abs(balance).toString()));

                $nextRow.find('.amount')
                    .val(Math.abs(balance));
            }

            syncAmountInputs($nextRow);

            updateTotals();

        });

        // ================= LEDGER CHANGE =================
        $(document).on('change', '.ledger', function() {

            const $row = $(this).closest('tr');

            syncAmountInputs($row);

            const dc = $row.find('.dc-select').val();

            setTimeout(() => {

                const $target = dc === 'Cr' ?
                    $row.find('.cr-input:visible') :
                    $row.find('.dr-input:visible');

                if ($target.length) {

                    $target.focus();

                    if ($target.val()) {
                        $target.select();
                    }
                }

            }, 50);

        });

        // ================= REMOVE =================
        // ================= TOGGLE REMOVE BUTTON =================
        function toggleRemoveButtons() {

            let totalRows = $('#linesTable tbody tr.line').length;

            if (totalRows <= 2) {

                $('#linesTable tbody tr.line .remove').addClass('d-none');

            } else {

                $('#linesTable tbody tr.line .remove').removeClass('d-none');
            }
        }

        // ================= REMOVE =================
        $(document).on('click', '.remove', function() {

            let totalRows = $('#linesTable tbody tr.line').length;

            // minimum 2 rows required (1 Dr + 1 Cr)
            if (totalRows <= 2) {
                return;
            }

            $(this).closest('tr').remove();

            updateTotals();

            toggleRemoveButtons();
        });

        // ================= DC CHANGE =================
        $(document).on('change', '.dc-select', function() {

            const $row = $(this).closest('tr');

            syncAmountInputs($row);

            updateTotals();

        });

        // ================= TYPE CHANGE =================
        $(document).on('click', '.type-pill', function() {

            const type = $(this).data('type');

            $('#voucher_type').val(type);

            $('#voucherTypeLabel').text(type);

            $('.type-pill').removeClass('active');

            $(this).addClass('active');

        });

        $('#voucherForm').on('keydown', function(e) {

            if (e.key === 'Enter') {

                e.preventDefault();

                return false;
            }

        });

        // ================= REMOVE COMMA BEFORE SUBMIT =================
        $('#voucherForm').on('submit', function() {

            $('.amount-format').each(function() {

                $(this).val(
                    $(this).val().replace(/,/g, '')
                );

            });

        });
    </script>
@endsection
