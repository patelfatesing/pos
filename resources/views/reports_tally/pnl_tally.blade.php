@extends('layouts.backend.datatable_layouts')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

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

        .filters {
            margin-bottom: 10px
        }

        #pnl_period {
            cursor: pointer;
            font-size: 13px;
        }

        #pnl_period:hover {
            text-decoration: underline;
            color: #007bff;
        }
    </style>
@endsection

@section('page-content')
    <div class="content-page">
        <div class="container-fluid">

            <div class="card-header d-flex justify-content-between">
                <h4>Profit & Loss</h4>
                <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
            </div>

            <div class="pnl-card">

                <!-- ✅ TALLY STYLE DATE -->
                <div class="filters">
                    <span id="pnl_period"></span>
                </div>

                <!-- hidden daterange -->
                <input type="text" id="pnl_daterange" style="position:absolute; opacity:0;">

                <a id="pnl_pdf_link" class="btn btn-sm btn-outline-primary mb-2" target="_blank">
                    Download PDF
                </a>

                <div class="two-col mt-2">

                    <!-- Trading DR -->
                    <div>
                        <div class="muted mb-1">Trading Account (Dr)</div>
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
                        <div class="muted mb-1">Trading Account (Cr)</div>
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
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        const PDF_BASE = @json(route('reports.profit-loss.pdf'));

        (function() {

            const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            let start = moment().subtract(29, 'days').format('YYYY-MM-DD');
            let end = moment().format('YYYY-MM-DD');

            // ✅ init picker
            $('#pnl_daterange').daterangepicker({
                startDate: moment(start),
                endDate: moment(end),
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });

            // ✅ click label → open picker
            $('#pnl_period').on('click', function() {
                $('#pnl_daterange').data('daterangepicker').show();
            });

            // ✅ update label
            function updateHeader() {
                $('#pnl_period').text(start + ' to ' + end);
            }

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

                        // ✅ GROUP LINK
                        if (r.group_id || r.section_group_id) {
                            let gid = r.group_id ?? r.section_group_id;
                            url = `/reports/group-summary/${gid}?start_date=${startParam}&end_date=${endParam}`;
                        }

                        // ✅ LEDGER LINK
                        if (r.ledger_id) {
                            url =
                                `/accounting/ledger/view/${r.ledger_id}?start_date=${startParam}&end_date=${endParam}`;
                        }

                        let labelHtml = url ?
                            `<a href="${url}" style="color:#2563eb;text-decoration:none;">${r.label}</a>` :
                            r.label;

                        const tr = document.createElement('tr');

                        tr.innerHTML = `
                <td style="padding-left:${level * 20}px;">
                    ${level === 0 ? '<strong>' + labelHtml + '</strong>' : '↳ ' + labelHtml}
                </td>
                <td class="amount">
                    ${level === 0 ? '<strong>' + r.amount + '</strong>' : r.amount}
                </td>
            `;

                        tbody.appendChild(tr);

                        // ✅ recursion only (NO duplicate loop)
                        if (r.children && r.children.length) {
                            loop(r.children, level + 1);
                        }
                    });
                }

                loop(rows);
            }

            // ✅ auto apply
            $('#pnl_daterange').on('apply.daterangepicker', function(ev, picker) {

                start = picker.startDate.format('YYYY-MM-DD');
                end = picker.endDate.format('YYYY-MM-DD');

                updateHeader();
                updatePdfLink();
                refresh();
            });

            // init
            updateHeader();
            updatePdfLink();
            refresh();

        })();
    </script>
@endsection
