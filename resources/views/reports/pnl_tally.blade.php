{{-- resources/views/reports/pnl_tally.blade.php --}}
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

        #branch_id {
            flex: 0 1 230px;
            min-width: 160px;
            text-overflow: ellipsis;
            overflow: hidden
        }

        #btn_refresh {
            flex: 0 0 auto
        }

        /* nested ledger rows */
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

        @media (max-width: 768px) {
            .two-col {
                grid-template-columns: 1fr
            }

            .filters {
                flex-wrap: wrap;
                white-space: normal
            }
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

                    <div class="filters">
                        <label>Branch</label>
                        <select id="branch_id" class="form-control form-control-sm">
                            <option value="" selected>All</option>
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>

                        <input type="date" id="start_date" class="form-control form-control-sm" placeholder="Start date">
                        <input type="date" id="end_date" class="form-control form-control-sm" placeholder="End date">

                        <button id="btn_refresh" class="btn btn-primary btn-sm">Apply</button>
                    </div>

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
        (function() {
            const iso = d => new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
            const today = new Date(),
                d30 = new Date();
            d30.setDate(today.getDate() - 29);
            document.getElementById('start_date').value = iso(d30);
            document.getElementById('end_date').value = iso(today);

            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            function clearTbody(tbody) {
                while (tbody.firstChild) tbody.removeChild(tbody.firstChild);
            }

            function appendRow(tbody, label, amount, extraHtml = '') {
                const tr = document.createElement('tr');
                const td1 = document.createElement('td');
                td1.innerHTML = label + (extraHtml || '');
                const td2 = document.createElement('td');
                td2.className = 'amount';
                td2.textContent = amount ?? '0.00';
                tr.appendChild(td1);
                tr.appendChild(td2);
                tbody.appendChild(tr);
            }

            // Render a side (Dr/Cr). Supports children for Purchase Accounts.
            function renderSide(tbodySelector, rows) {
                const tbody = document.querySelector(tbodySelector);
                clearTbody(tbody);
                (rows || []).forEach(r => {
                    appendRow(tbody, r.label, r.amount);

                    // If this row has children (e.g., Purchase Ledger breakup)
                    if (Array.isArray(r.children) && r.children.length) {
                        r.children.forEach(ch => {
                            const tr = document.createElement('tr');
                            tr.className = 'child-row';
                            const td1 = document.createElement('td');
                            td1.className = 'child-label';
                            const bills = (typeof ch.bills !== 'undefined') ?
                                `<span class="child-meta"> (Bills: ${ch.bills})</span>` : '';
                            td1.innerHTML = (ch.label ?? 'Ledger') + bills;
                            const td2 = document.createElement('td');
                            td2.className = 'amount';
                            td2.textContent = ch.amount ?? '0.00';
                            tr.appendChild(td1);
                            tr.appendChild(td2);
                            tbody.appendChild(tr);
                        });
                    }
                });
            }

            function refresh() {
                const body = {
                    branch_id: document.getElementById('branch_id').value,
                    start_date: document.getElementById('start_date').value,
                    end_date: document.getElementById('end_date').value
                };

                fetch("{{ route('reports.pnl_tally.data') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify(body)
                    })
                    .then(r => r.json())
                    .then(json => {
                        // Header
                        const header = json.header || {};
                        const period = (header.period || (json.period?.start + ' to ' + json.period?.end)) ?? '';
                        const branch = header.branch ?? json.branch ?? '';
                        document.getElementById('pnl_period').textContent = `${branch} • ${period}`;

                        // Titles (optional from API)
                        document.getElementById('lbl_tr_dr').textContent = json.trading?.dr?.title ??
                            'Trading Account (Dr)';
                        document.getElementById('lbl_tr_cr').textContent = json.trading?.cr?.title ??
                            'Trading Account (Cr)';
                        document.getElementById('lbl_pl_dr').textContent = json.pl?.dr?.title ??
                            'Profit & Loss A/c (Dr)';
                        document.getElementById('lbl_pl_cr').textContent = json.pl?.cr?.title ??
                            'Profit & Loss A/c (Cr)';

                        // Trading
                        renderSide('#tbl_trading_dr tbody', json.trading?.dr?.rows ?? json.trading?.dr ?? []);
                        renderSide('#tbl_trading_cr tbody', json.trading?.cr?.rows ?? json.trading?.cr ?? []);
                        const trTot = json.trading?.table_total ?? json.trading?.total ?? '0.00';
                        document.getElementById('trading_total_dr').textContent = trTot;
                        document.getElementById('trading_total_cr').textContent = trTot;

                        // P&L
                        renderSide('#tbl_pl_dr tbody', json.pl?.dr?.rows ?? json.pl?.dr ?? []);
                        renderSide('#tbl_pl_cr tbody', json.pl?.cr?.rows ?? json.pl?.cr ?? []);
                        const plTot = json.pl?.table_total ?? json.pl?.total ?? '0.00';
                        document.getElementById('pl_total_dr').textContent = plTot;
                        document.getElementById('pl_total_cr').textContent = plTot;
                    })
                    .catch(() => alert('Failed to load P&L.'));
            }

            document.getElementById('btn_refresh').addEventListener('click', refresh);
            refresh();
        })();
    </script>
@endsection
