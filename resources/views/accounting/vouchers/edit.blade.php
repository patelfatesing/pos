@extends('layouts.backend.layouts')

@section('page-content')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        /* ===== SAME CSS FROM CREATE PAGE (UNCHANGED) ===== */
        #linesTable {
            width: 100%;
            font-family: monospace;
            table-layout: fixed;
        }

        #linesTable thead th {
            border-top: 4px solid #a7a3a3;
            border-bottom: 3px solid #bbb8b8;
            font-weight: bold;
        }

        #linesTable td {
            vertical-align: middle;
        }

        #linesTable select,
        #linesTable input {
            border: none;
            background: transparent;
        }

        #linesTable tbody tr.line:hover {
            background: #F3E6A1;
        }

        .hidden-amount {
            display: none;
        }

        .dr-input,
        .cr-input {
            width: 100px;
        }

        .dc-select {
            font-weight: bold;
            width: 55px;
        }

        .remove {
            cursor: pointer;
            color: red;
        }

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
    </style>

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="card">

                    <div class="card-header d-flex justify-content-between">
                        <h4>Edit Accounting Voucher</h4>
                        <a href="{{ route('accounting.vouchers.index') }}" class="btn btn-secondary">Back</a>
                    </div>

                    <div class="card-body">

                        <form action="{{ route('accounting.vouchers.update', $voucher->id) }}" method="POST"
                            id="voucherForm">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="voucher_type" id="voucher_type"
                                value="{{ $voucher->voucher_type }}">

                            {{-- HEADER --}}
                            <table width="100%" class="mb-3">
                                <tr>
                                    <td width="80%">
                                        <span class="voucher-type-label" id="voucherTypeLabel">
                                            {{ $voucher->voucher_type }}
                                        </span>
                                        <strong class="ms-2">NO.</strong>
                                        <span>{{ $voucher->ref_no }}</span>
                                        <input type="hidden" name="ref_no" value="{{ $voucher->ref_no }}">
                                    </td>
                                    <td width="20%" class="text-end">
                                        <input type="date" name="voucher_date"
                                            value="{{ $voucher->voucher_date->format('Y-m-d') }}"
                                            class="voucher-date-input">
                                    </td>
                                </tr>
                            </table>

                            {{-- ================= TABLE ================= --}}
                            <table id="linesTable">
                                <thead>
                                    <tr>
                                        <th width="5%"></th>
                                        <th width="75%">Particulars</th>
                                        <th width="10%" class="text-end">Debit</th>
                                        <th width="10%" class="text-end">Credit</th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach ($voucher->lines as $i => $line)
                                        <tr class="line">
                                            <td>
                                                <input type="hidden" name="lines[{{ $i }}][amount]"
                                                    class="amount" value="{{ $line->amount }}">
                                                <select name="lines[{{ $i }}][dc]" class="dc-select">
                                                    <option value="Dr" {{ $line->dc == 'Dr' ? 'selected' : '' }}>By
                                                    </option>
                                                    <option value="Cr" {{ $line->dc == 'Cr' ? 'selected' : '' }}>To
                                                    </option>
                                                </select>
                                            </td>

                                            <td>
                                                <select name="lines[{{ $i }}][ledger_id]" class="ledger">
                                                    <option value="">Select Ledger</option>
                                                    @foreach ($ledgers as $l)
                                                        <option value="{{ $l->id }}"
                                                            {{ $l->id == $line->ledger_id ? 'selected' : '' }}>
                                                            {{ $l->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td>
                                                <input type="number" class="dr-input"
                                                    value="{{ $line->dc == 'Dr' ? $line->amount : '' }}">
                                            </td>

                                            <td>
                                                <input type="number" class="cr-input"
                                                    value="{{ $line->dc == 'Cr' ? $line->amount : '' }}">
                                            </td>

                                            <td>
                                                <span class="remove"><i class="fa fa-times"></i></span>
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>

                                <tfoot>
                                    <tr>
                                        <td></td>
                                        <td><b>Narration</b></td>
                                        <td><span id="totalDrText">0.00</span></td>
                                        <td><span id="totalCrText">0.00</span></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <div class="mt-3">
                                <button class="btn btn-success" id="btnSubmit">Update Voucher</button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>

        {{-- ================= SCRIPT ================= --}}
        <script>
            function syncAmountInputs(row) {
                const dc = row.find('.dc-select').val();

                row.find('.dr-input, .cr-input').addClass('hidden-amount');

                if (dc === 'Dr') row.find('.dr-input').removeClass('hidden-amount');
                if (dc === 'Cr') row.find('.cr-input').removeClass('hidden-amount');
            }

            function updateTotals() {
                let dr = 0,
                    cr = 0;

                $('#linesTable tbody tr').each(function() {
                    const d = parseFloat($(this).find('.dr-input').val()) || 0;
                    const c = parseFloat($(this).find('.cr-input').val()) || 0;
                    dr += d;
                    cr += c;
                });

                $('#totalDrText').text(dr.toFixed(2));
                $('#totalCrText').text(cr.toFixed(2));
            }

            $(document).ready(function() {

                // Restore UI
                $('#linesTable tbody tr').each(function() {
                    syncAmountInputs($(this));
                });

                updateTotals();

                // Change DC
                $(document).on('change', '.dc-select', function() {
                    const row = $(this).closest('tr');
                    row.find('.dr-input, .cr-input').val('');
                    syncAmountInputs(row);
                    updateTotals();
                });

                // Amount change
                $(document).on('input', '.dr-input, .cr-input', function() {
                    const row = $(this).closest('tr');
                    const dr = parseFloat(row.find('.dr-input').val()) || 0;
                    const cr = parseFloat(row.find('.cr-input').val()) || 0;

                    row.find('.amount').val(dr > 0 ? dr : cr);
                    updateTotals();
                });

                // Remove row
                $(document).on('click', '.remove', function() {
                    $(this).closest('tr').remove();
                    updateTotals();
                });
            });
        </script>
    @endsection
