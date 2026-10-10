@extends('layouts.backend.datatable_layouts')

@section('styles')
    <style>
        .pnl-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            background: #fff
        }

        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px
        }

        table.pnl {
            width: 100%;
            border-collapse: collapse
        }

        table.pnl th,
        table.pnl td {
            padding: 6px 8px;
            border-bottom: 1px solid #eee
        }

        table.pnl th {
            font-weight: 700;
            font-size: 12px;
            color: #6b7280
        }

        .row-total {
            font-weight: 700;
            border-top: 2px solid #111
        }

        .amount {
            text-align: right
        }

        /* --- Date Inputs & Buttons Styling --- */
        .filter-controls-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .date-box {
            display: inline-flex;
            align-items: center;
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 2px 8px;
            height: 35px;
        }

        .date-box label {
            margin-bottom: 0;
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            margin-right: 6px;
        }

        .date-box input[type="date"] {
            border: none;
            outline: none;
            background: transparent;
            font-size: 13px;
            color: #111827;
            cursor: pointer;
            padding: 0;
        }

        .btn-apply {
            background-color: #10b981;
            color: #ffffff;
            border: none;
            height: 35px;
            padding: 0 14px;
            font-size: 13px;
            font-weight: 500;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.2s;
        }

        .btn-apply:hover {
            opacity: 0.9;
            color: #fff;
        }

        .btn-pdf-export {
            background: #ffffff;
            color: #2563eb;
            border: 1px solid #2563eb;
            height: 35px;
            padding: 0 14px;
            font-size: 13px;
            font-weight: 500;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-pdf-export:hover {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .v-divider {
            width: 2px;
            height: 30px;
            background: #fff;
            margin: 0 15px;
        }

        .section-badge {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.3px;
            margin-bottom: 5px;
        }

        /* Debit Side Styling (Soft Red/Rose Accent) */
        .section-badge.dr-badge {
            background-color: #fff1f2;
            color: #e11d48;
            border-left: 4px solid #e11d48;
            border-top: 1px solid #ffe4e6;
            border-right: 1px solid #ffe4e6;
            border-bottom: 1px solid #ffe4e6;
        }

        /* Credit Side Styling (Soft Emerald Accent) */
        .section-badge.cr-badge {
            background-color: #ecfdf5;
            color: #059669;
            border-left: 4px solid #059669;
            border-top: 1px solid #d1fae5;
            border-right: 1px solid #d1fae5;
            border-bottom: 1px solid #d1fae5;
        }

        .section-tag {
            font-size: 11px;
            padding: 2px 7px;
            border-radius: 4px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .dr-badge .section-tag {
            background: #ffe4e6;
            color: #be123c;
        }

        .cr-badge .section-tag {
            background: #d1fae5;
            color: #047857;
        }
    </style>
@endsection

@section('page-content')
    <div class="content-page">
        <div class="container-fluid">

            <!-- Card Header: Default Theme Colors Maintained -->
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <h4 class="mb-0">Profit & Loss</h4>

                <div class="filter-controls-wrap">
                    <!-- From Date -->
                    <div class="date-box">
                        <label for="pnl_from_date">From</label>
                        <input type="date" id="pnl_from_date">
                    </div>

                    <!-- To Date -->
                    <div class="date-box">
                        <label for="pnl_to_date">To</label>
                        <input type="date" id="pnl_to_date">
                    </div>

                    <!-- Apply Filter -->
                    <button type="button" id="btn_apply_filter" class="btn-apply">
                        Apply
                    </button>

                    <!-- Divider 1 -->
                    <div class="v-divider"></div>

                    <!-- PDF Download -->
                    <a id="pnl_pdf_link" class="btn-pdf-export" target="_blank">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"></path>
                        </svg>
                        PDF
                    </a>

                    <!-- Divider 2 -->
                    <div class="v-divider"></div>

                    <!-- Back Button -->
                    <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>

            <div class="pnl-card">

                <div class="two-col">

                    <!-- Trading DR -->
                    <div>
                        <div class="section-badge dr-badge">
                            <span>Trading Account</span>
                            <span class="section-tag">Dr</span>
                        </div>
                        <table class="pnl" id="tbl_trading_dr">
                            <thead>
                                <tr>
                                    <th>Particulars</th>
                                    <th class="amount">Amount</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr class="row-total">
                                    <td>Total</td>
                                    <td class="amount" id="trading_total_dr">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Trading CR -->
                    <div>
                        <div class="section-badge cr-badge">
                            <span>Trading Account</span>
                            <span class="section-tag">Cr</span>
                        </div>
                        <table class="pnl" id="tbl_trading_cr">
                            <thead>
                                <tr>
                                    <th>Particulars</th>
                                    <th class="amount">Amount</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr class="row-total">
                                    <td>Total</td>
                                    <td class="amount" id="trading_total_cr">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>

                <hr>

                <div class="two-col">

                    <!-- P&L DR -->
                    <div>
                        <div class="muted mb-1">Profit & Loss A/c (Dr)</div>
                        <table class="pnl" id="tbl_pl_dr">
                            <thead>
                                <tr>
                                    <th>Particulars</th>
                                    <th class="amount">Amount</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr class="row-total">
                                    <td>Total</td>
                                    <td class="amount" id="pl_total_dr">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- P&L CR -->
                    <div>
                        <div class="muted mb-1">Profit & Loss A/c (Cr)</div>
                        <table class="pnl" id="tbl_pl_cr">
                            <thead>
                                <tr>
                                    <th>Particulars</th>
                                    <th class="amount">Amount</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr class="row-total">
                                    <td>Total</td>
                                    <td class="amount" id="pl_total_cr">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

    <script>
        const PDF_BASE = @json(route('reports.profit-loss.pdf'));
        const CATEGORY_SALES_URL = @json(route('reports.category_sales.page'));

        (function() {

            const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            let start = moment().subtract(29, 'days').format('YYYY-MM-DD');
            let end = moment().format('YYYY-MM-DD');

            // ✅ Set initial values
            $('#pnl_from_date').val(start);
            $('#pnl_to_date').val(end);

            // ✅ Filter Apply
            $('#btn_apply_filter').on('click', function() {
                const newStart = $('#pnl_from_date').val();
                const newEnd = $('#pnl_to_date').val();

                if (!newStart || !newEnd) {
                    alert('Please select both start and end date.');
                    return;
                }

                if (newStart > newEnd) {
                    alert('From date cannot be greater than To date.');
                    return;
                }

                start = newStart;
                end = newEnd;

                updatePdfLink();
                refresh();
            });

            // ✅ update PDF
            function updatePdfLink() {
                const params = new URLSearchParams({
                    start_date: start,
                    end_date: end
                });
                $('#pnl_pdf_link').attr('href', `${PDF_BASE}?${params.toString()}`);
            }

            // ✅ main refresh
            function refresh() {

                fetch(@json(route('reports.pnl_tally.data')), {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": CSRF
                        },
                        body: JSON.stringify({
                            start_date: start,
                            end_date: end
                        })
                    })
                    .then(r => r.json())
                    .then(json => {

                        render('#tbl_trading_dr tbody', json.trading.dr.rows);
                        render('#tbl_trading_cr tbody', json.trading.cr.rows);
                        render('#tbl_pl_dr tbody', json.pl.dr.rows);
                        render('#tbl_pl_cr tbody', json.pl.cr.rows);

                        $('#trading_total_dr').text(json.trading.table_total);
                        $('#trading_total_cr').text(json.trading.table_total);
                        $('#pl_total_dr').text(json.pl.table_total);
                        $('#pl_total_cr').text(json.pl.table_total);
                    });
            }

            function render(selector, rows) {

                const tbody = document.querySelector(selector);

                tbody.innerHTML = '';

                const startParam = encodeURIComponent(start);
                const endParam = encodeURIComponent(end);

                function loop(data, level = 0) {

                    data.forEach(r => {

                        let url = null;

                        /*
                        |--------------------------------------------------------------------------
                        | GROUP LINK
                        |--------------------------------------------------------------------------
                        */

                        if (r.group_id || r.section_group_id) {

                            let gid = r.group_id ?? r.section_group_id;

                            url =
                                `/reports/group-summary/${gid}?start_date=${startParam}&end_date=${endParam}`;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | LEDGER LINK
                        |--------------------------------------------------------------------------
                        */

                        if (r.ledger_id) {

                            url =
                                `/accounting/ledger/view/${r.ledger_id}?start_date=${startParam}&end_date=${endParam}`;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | CATEGORY SALES LINK
                        |--------------------------------------------------------------------------
                        */

                        if (r.report === 'category_sales') {

                            const params = new URLSearchParams({

                                start_date: start,

                                end_date: end,

                                admin_status: 'verify',

                                date_source: 'voucher',

                                group_by: r.group_by || 'category'
                            });

                            if (r.category_id) {

                                params.set(
                                    'category_id',
                                    r.category_id
                                );
                            }

                            if (r.category_name) {

                                params.set(
                                    'category_name',
                                    r.category_name
                                );
                            }

                            if (r.sub_category_id) {

                                params.set(
                                    'sub_category_id',
                                    r.sub_category_id
                                );

                                params.set(
                                    'group_by',
                                    'subcategory'
                                );
                            }

                            if (r.sub_category_name) {

                                params.set(
                                    'sub_category_name',
                                    r.sub_category_name
                                );

                                params.set(
                                    'group_by',
                                    'subcategory'
                                );
                            }

                            url =
                                `${CATEGORY_SALES_URL}?${params.toString()}`;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | STOCK SUMMARY LINK
                        |--------------------------------------------------------------------------
                        */

                        if (r.report === 'stock_summary') {

                            const params = new URLSearchParams({

                                start_date: start,

                                end_date: end,

                                category: r.sub_category_id || ''
                            });

                            url =
                                `/accounting/stock-summary?${params.toString()}`;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | SAFE LABEL
                        |--------------------------------------------------------------------------
                        */

                        const safeLabel = escapeHtml(
                            r.label || ''
                        );

                        let labelHtml = url ?

                            `<a href="${url}" 
                            style="
                                color:#2563eb;
                                text-decoration:none;
                            ">
                            ${safeLabel}
                        </a>`

                            :

                            safeLabel;

                        /*
                        |--------------------------------------------------------------------------
                        | ROW
                        |--------------------------------------------------------------------------
                        */

                        const tr = document.createElement('tr');

                        tr.innerHTML = `
                        <td style="padding-left:${level * 20}px;">

                            ${level === 0

                                ? '<strong>' + labelHtml + '</strong>'

                                : '↳ ' + labelHtml
                            }

                        </td>

                        <td class="amount">

                            ${level === 0

                                ? '<strong>' + r.amount + '</strong>'

                                : r.amount
                            }

                        </td>
                    `;

                        tbody.appendChild(tr);

                        /*
                        |--------------------------------------------------------------------------
                        | CHILD LOOP
                        |--------------------------------------------------------------------------
                        */

                        if (
                            r.children &&
                            r.children.length
                        ) {

                            loop(
                                r.children,
                                level + 1
                            );
                        }
                    });
                }

                loop(rows);
            }

            function escapeHtml(text) {
                return String(text)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            // Init
            updatePdfLink();
            refresh();

        })();
    </script>
@endsection