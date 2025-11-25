@extends('layouts.backend.datatable_layouts')

@section('styles')
    <style>
        .pnl-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            background: #fff
        }

        .pnl-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px
        }

        .pnl-title {
            font-weight: 700;
            font-size: 18px
        }

        .pnl-sub {
            color: #6b7280
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
            text-transform: uppercase;
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

        .muted {
            color: #6b7280
        }

        .filters {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: nowrap;
            overflow: hidden;
            white-space: nowrap;
            margin-bottom: 10px
        }

        .filters label {
            margin-bottom: 0;
            white-space: nowrap;
            font-size: .85rem;
            color: #6b7280
        }

        .filters .form-control-sm {
            flex: 0 1 170px;
            min-width: 120px
        }

        #btn_refresh {
            flex: 0 0 auto
        }

        .pnl .child-row td {
            padding-top: 2px;
            padding-bottom: 2px
        }

        .pnl .child-label {
            padding-left: 22px;
            position: relative
        }

        .pnl .child-label:before {
            content: "•";
            position: absolute;
            left: 10px;
            top: 0;
            color: #9ca3af
        }

        .pnl .child-meta {
            color: #9ca3af;
            font-size: 12px
        }

        .pnl .grand-child-row td {
            padding-top: 2px;
            padding-bottom: 2px
        }

        .pnl .grand-child-label {
            padding-left: 38px;
            position: relative
        }

        .pnl .grand-child-label:before {
            content: "◦";
            position: absolute;
            left: 28px;
            top: 0;
            color: #cbd5e1
        }

        .pnl .grand-child-total td {
            font-weight: 700;
            border-top: 1px solid #e5e7eb
        }

        @media (max-width:768px) {
            .two-col {
                grid-template-columns: 1fr
            }

            .filters {
                flex-wrap: wrap;
                white-space: normal
            }
        }

        .filters {
            overflow: visible
        }

        .pnl a {
            color: inherit;
            text-decoration: none;
            font: inherit
        }

        .pnl a:hover {
            text-decoration: none
        }

        .pnl .child-label a,
        .pnl .grand-child-label a {
            display: inline;
            cursor: pointer
        }
    </style>
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <div class="pnl-card">
                    <div class="pnl-head">
                        <div class="pnl-title">Profit &amp; Loss</div>
                        <div class="pnl-sub" id="pnl_period">—</div>
                    </div>

                    <div class="filters" id="pnl_filters">

                        {{-- Removed Branch Completely --}}
                        
                        <input type="date" id="pnl_start" class="form-control form-control-sm" autocomplete="off">
                        <input type="date" id="pnl_end" class="form-control form-control-sm" autocomplete="off">

                        <button id="pnl_apply" type="button" class="btn btn-primary btn-sm">Apply</button>
                    </div>

                    <a id="pnl_pdf_link" class="btn btn-sm btn-outline-primary" href="#" target="_blank">
                        Download PDF
                    </a>

                    {{-- Trading Account --}}
                    <div class="two-col mt-2">
                        <div>
                            <div class="muted mb-1" id="lbl_tr_dr">Trading Account (Dr)</div>
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
                        <div>
                            <div class="muted mb-1" id="lbl_tr_cr">Trading Account (Cr)</div>
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

                    {{-- Profit & Loss Account --}}
                    <div class="two-col">
                        <div>
                            <div class="muted mb-1" id="lbl_pl_dr">Profit &amp; Loss A/c (Dr)</div>
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
                        <div>
                            <div class="muted mb-1" id="lbl_pl_cr">Profit &amp; Loss A/c (Cr)</div>
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
    </div>
@endsection


@section('scripts')
<script>
    const GROUP_URL = @json(route('reports.pnl.group'));
    const LEDGER_URL = @json(route('reports.pnl.ledger'));
    const PDF_BASE = @json(route('reports.profit-loss.pdf'));

    (function() {
        const $ = id => document.getElementById(id);
        const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const fmtDate = d => {
            const dt = new Date(d);
            const local = new Date(dt.getTime() - dt.getTimezoneOffset() * 60000);
            return local.toISOString().slice(0, 10);
        };

        const today = fmtDate(new Date());
        const last30 = fmtDate(new Date(Date.now() - 29 * 86400000));

        $('pnl_start').value = last30;
        $('pnl_end').value = today;

        function updateHeader() {
            $('pnl_period').textContent = `${$('pnl_start').value} to ${$('pnl_end').value}`;
        }

        function updatePdfLink() {
            const params = new URLSearchParams({
                start_date: $('pnl_start').value,
                end_date: $('pnl_end').value
            });
            $('pnl_pdf_link').href = `${PDF_BASE}?${params.toString()}`;
        }

        updateHeader();
        updatePdfLink();

        function refresh() {
            let payload = {
                start_date: $('pnl_start').value,
                end_date: $('pnl_end').value
            };

            fetch(@json(route('reports.pnl_tally.data')), {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": CSRF
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(json => {
                renderSide('#tbl_trading_dr tbody', json.trading.dr.rows);
                renderSide('#tbl_trading_cr tbody', json.trading.cr.rows);
                renderSide('#tbl_pl_dr tbody', json.pl.dr.rows);
                renderSide('#tbl_pl_cr tbody', json.pl.cr.rows);

                $('trading_total_dr').textContent = json.trading.table_total;
                $('trading_total_cr').textContent = json.trading.table_total;
                $('pl_total_dr').textContent = json.pl.table_total;
                $('pl_total_cr').textContent = json.pl.table_total;
            });
        }

        // Render reusable
        function renderSide(selector, rows) {
            const tbody = document.querySelector(selector);
            tbody.innerHTML = "";

            rows.forEach(r => {
                const tr = document.createElement("tr");
                tr.innerHTML = `<td>${r.label}</td><td class="amount">${r.amount}</td>`;
                tbody.appendChild(tr);

                if (r.children) {
                    r.children.forEach(c => {
                        const tr2 = document.createElement("tr");
                        tr2.innerHTML = `<td class="child-label">${c.label}</td><td class="amount">${c.amount}</td>`;
                        tbody.appendChild(tr2);
                    });
                }
            });
        }

        $('pnl_apply').addEventListener("click", () => {
            updateHeader();
            updatePdfLink();
            refresh();
        });

        refresh();
    })();
</script>
@endsection
