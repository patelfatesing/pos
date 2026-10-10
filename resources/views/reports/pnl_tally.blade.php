@extends('layouts.backend.datatable_layouts')

@section('styles')

    <style>
        .pnl-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            background: #fff
        }

        #pnl_period {
            cursor: pointer;
            font-size: 13px;
        }

        #pnl_period:hover {
            text-decoration: underline;
            color: #007bff;
        }

        /* hidden input fully remove from layout */
        #pnl_daterange {
            position: absolute !important;
            left: -99999px !important;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }

        /* daterangepicker popup above buttons/cards */
        .daterangepicker {
            z-index: 99999 !important;
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
    <div class="content-page">
        <div class="container-fluid">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h4 class="mb-0"> Profit &amp; Loss</h4>
                </div>
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <div class="filters mb-0">
                        <span id="pnl_period" style="cursor: pointer;"></span>
                    </div>
                    <input type="text" id="pnl_daterange" hidden>

                    <a id="pnl_pdf_link" class="btn btn-sm btn-outline-primary" href="#" target="_blank">
                        Download PDF
                    </a>

                    <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
            <div class="pnl-card">

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
@endsection


@section('scripts')
    <script>
        const GROUP_URL = @json(route('reports.pnl.group'));
        const LEDGER_URL = @json(route('reports.pnl.ledger'));
        const PDF_BASE = @json(route('reports.profit-loss.pdf'));
        const GROUP_SUMMARY_BASE = "{{ url('/reports/group-summary') }}";
        // base URL for ledger vouchers (no trailing slash)
        const LEDGER_VOUCHERS_BASE = '/accounting/ledgers';

        (function() {
            const $ = id => document.getElementById(id);
            const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            let start = moment().subtract(29, 'days').format('YYYY-MM-DD');
            let end = moment().format('YYYY-MM-DD');

            // init picker
            $('#pnl_daterange').daterangepicker({
                startDate: moment(start),
                endDate: moment(end),
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });

            // click label → open picker
            $('#pnl_period').on('click', function() {
                $('#pnl_daterange').data('daterangepicker').show();
            });

            // update label
            function updateHeader() {
                $('#pnl_period').text(start + ' to ' + end);
            }

            // update PDF
            function updatePdfLink() {
                const params = new URLSearchParams({
                    start_date: start,
                    end_date: end
                });
                $('pnl_pdf_link').href = `${PDF_BASE}?${params.toString()}`;
            }

            $('#pnl_daterange').on('apply.daterangepicker', function(ev, picker) {
                start = picker.startDate.format('YYYY-MM-DD');
                end = picker.endDate.format('YYYY-MM-DD');

                updateHeader();
                updatePdfLink();
                refresh();
            });

            updateHeader();
            updatePdfLink();

            function refresh() {
                let payload = {
                    start_date: start,
                    end_date: end
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
                    .then(r => {
                        if (!r.ok) throw new Error('Network response was not ok');
                        return r.json();
                    })
                    .then(json => {
                        renderSide('#tbl_trading_dr tbody', json.trading.dr.rows);
                        renderSide('#tbl_trading_cr tbody', json.trading.cr.rows);
                        renderSide('#tbl_pl_dr tbody', json.pl.dr.rows);
                        renderSide('#tbl_pl_cr tbody', json.pl.cr.rows);

                        $('trading_total_dr').textContent = json.trading.table_total;
                        $('trading_total_cr').textContent = json.trading.table_total;
                        $('pl_total_dr').textContent = json.pl.table_total;
                        $('pl_total_cr').textContent = json.pl.table_total;
                    })
                    .catch(err => {
                        console.error('Failed to load P&L data', err);
                    });
            }

            // Render reusable
            function renderSide(selector, rows, maxRows = 0) {

                const tbody = document.querySelector(selector);
                tbody.innerHTML = "";

                const startParam = encodeURIComponent(start);
                const endParam = encodeURIComponent(end);

                let data = rows || [];

                // 👉 Fill empty rows to match opposite side
                if (maxRows > 0) {
                    while (data.length < maxRows) {
                        data.push({
                            label: '',
                            amount: ''
                        });
                    }
                }

                data.forEach(r => {

                    let labelHtml = r.label ? escapeHtml(r.label) : '&nbsp;';
                    let amount = r.amount ? r.amount : '&nbsp;';

                    // Detect group id from API
                    let groupId = r.section_group_id || r.group_id || r.id || null;

                    if (groupId && r.label) {

                        const url =
                            `${GROUP_SUMMARY_BASE}/${groupId}?start_date=${startParam}&end_date=${endParam}`;

                        labelHtml = `<a href="${url}">${escapeHtml(r.label)}</a>`;
                    }

                    const tr = document.createElement("tr");

                    tr.innerHTML = `
                        <td>${labelHtml}</td>
                        <td class="amount">${amount}</td>
                    `;

                    tbody.appendChild(tr);
                });
            }

            // escape to avoid XSS if data ever contains HTML
            function escapeHtml(text) {
                if (text === null || text === undefined) return '';
                return String(text)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            refresh();
        })();
    </script>
@endsection
