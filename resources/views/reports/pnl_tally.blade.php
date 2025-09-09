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

        /* links look like normal text */
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
                        <label for="pnl_branch">Branch</label>
                        <select id="pnl_branch" class="form-control form-control-sm" autocomplete="off">
                            <option value="" selected>All</option>
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>

                        <input type="date" id="pnl_start" class="form-control form-control-sm" autocomplete="off">
                        <input type="date" id="pnl_end" class="form-control form-control-sm" autocomplete="off">

                        <button id="pnl_apply" type="button" class="btn btn-primary btn-sm">Apply</button>
                    </div>

                    <a id="pnl_pdf_link" class="btn btn-sm btn-outline-primary" href="#" target="_blank">Download
                        PDF</a>

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

        (function() {
            const $ = (id) => document.getElementById(id);

            const fmtDate = (d) => {
                const dt = (d instanceof Date) ? d : new Date(d);
                const local = new Date(dt.getTime() - dt.getTimezoneOffset() * 60000);
                return local.toISOString().slice(0, 10);
            };
            const todayStr = () => fmtDate(new Date());
            const daysAgoStr = (n) => {
                const d = new Date();
                d.setDate(d.getDate() - n);
                return fmtDate(d);
            };

            const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const PDF_BASE = @json(route('reports.profit-loss.pdf'));

            const LS_KEY = 'pnl_tally_filters_v2';
            const loadSaved = () => {
                try {
                    return JSON.parse(localStorage.getItem(LS_KEY) || '{}');
                } catch {
                    return {};
                }
            };
            const saveCurrent = () => localStorage.setItem(LS_KEY, JSON.stringify({
                branch_id: $('pnl_branch').value || '',
                start_date: $('pnl_start').value || '',
                end_date: $('pnl_end').value || '',
            }));

            function selectedBranchText() {
                const sel = $('pnl_branch');
                return sel?.selectedOptions?.[0]?.textContent?.trim() || 'All';
            }

            function updateHeader() {
                const s = $('pnl_start').value || '';
                const e = $('pnl_end').value || '';
                $('pnl_period').textContent = `${selectedBranchText()} • ${s} to ${e}`;
            }

            function updatePdfLink() {
                const params = new URLSearchParams({
                    start_date: $('pnl_start').value || '',
                    end_date: $('pnl_end').value || '',
                    branch_id: $('pnl_branch').value || ''
                });
                $('pnl_pdf_link').href = `${PDF_BASE}?${params.toString()}`;
            }

            // SECTION header row (optionally clickable to group)
            function appendSectionRow(tbody, r) {
                const tr = document.createElement('tr');
                const td1 = document.createElement('td');
                const td2 = document.createElement('td');
                td2.className = 'amount';
                td2.textContent = r.amount ?? '0.00';

                if (r.section_group_id) {
                    const a = document.createElement('a');
                    a.textContent = r.label ?? 'Section';
                    a.href =
                        `${GROUP_URL}?section=${encodeURIComponent(r.section || '')}&group_id=${encodeURIComponent(r.section_group_id)}&start_date=${encodeURIComponent($('pnl_start').value)}&end_date=${encodeURIComponent($('pnl_end').value)}&branch_id=${encodeURIComponent($('pnl_branch').value)}`;
                    a.target = '_blank';
                    td1.appendChild(a);
                } else {
                    td1.textContent = r.label ?? 'Section';
                }

                tr.appendChild(td1);
                tr.appendChild(td2);
                tbody.appendChild(tr);
            }

            // Render Section → Groups/ledgers
            function renderSide(tbodySelector, rows) {
                const tbody = document.querySelector(tbodySelector);
                while (tbody.firstChild) tbody.removeChild(tbody.firstChild);

                (rows || []).forEach(r => {
                    // Always draw section header first (clickable if section_group_id exists)
                    appendSectionRow(tbody, r);

                    if (!Array.isArray(r.children)) return;

                    const section = r.section || '';

                    if (r.flatten) {
                        // Children are LEDGERS directly (single-group case)
                        r.children.forEach(led => {
                            const tr = document.createElement('tr');
                            const td1 = document.createElement('td');
                            const td2 = document.createElement('td');
                            td2.className = 'amount';

                            if (led.is_total) {
                                tr.className = 'grand-child-total';
                                td1.textContent = led.label ?? 'Total';
                            } else {
                                tr.className = 'grand-child-row';
                                td1.className = 'grand-child-label';

                                if (led.ledger_id) {
                                    const a = document.createElement('a');
                                    a.textContent = led.label ?? 'Ledger';
                                    a.href =
                                        `${LEDGER_URL}?ledger_id=${encodeURIComponent(led.ledger_id)}&section=${encodeURIComponent(section)}&start_date=${encodeURIComponent($('pnl_start').value)}&end_date=${encodeURIComponent($('pnl_end').value)}&branch_id=${encodeURIComponent($('pnl_branch').value)}`;
                                    a.target = '_blank';
                                    td1.appendChild(a);
                                } else {
                                    td1.textContent = led.label ?? 'Ledger';
                                }
                                if (typeof led.bills !== 'undefined') {
                                    td1.insertAdjacentHTML('beforeend',
                                        ` <span class="child-meta">(Bills: ${led.bills})</span>`);
                                }
                            }

                            td2.textContent = led.amount ?? '0.00';
                            tr.appendChild(td1);
                            tr.appendChild(td2);
                            tbody.appendChild(tr);
                        });

                    } else {
                        // Children are GROUPS
                        r.children.forEach(ch => {
                            const tr = document.createElement('tr');
                            tr.className = 'child-row';
                            const td1 = document.createElement('td');
                            td1.className = 'child-label';
                            const td2 = document.createElement('td');
                            td2.className = 'amount';

                            if (ch.group_id) {
                                const a = document.createElement('a');
                                a.textContent = ch.label ?? 'Group';
                                a.href =
                                    `${GROUP_URL}?section=${encodeURIComponent(section)}&group_id=${encodeURIComponent(ch.group_id)}&start_date=${encodeURIComponent($('pnl_start').value)}&end_date=${encodeURIComponent($('pnl_end').value)}&branch_id=${encodeURIComponent($('pnl_branch').value)}`;
                                a.target = '_blank';
                                td1.appendChild(a);
                            } else {
                                td1.textContent = ch.label ?? 'Group';
                            }
                            if (typeof ch.bills !== 'undefined') {
                                td1.insertAdjacentHTML('beforeend',
                                    ` <span class="child-meta">(Bills: ${ch.bills})</span>`);
                            }

                            td2.textContent = ch.amount ?? '0.00';
                            tr.appendChild(td1);
                            tr.appendChild(td2);
                            tbody.appendChild(tr);

                            if (Array.isArray(ch.children)) {
                                ch.children.forEach(led => {
                                    const tr2 = document.createElement('tr');
                                    const td1g = document.createElement('td');
                                    const td2g = document.createElement('td');
                                    td2g.className = 'amount';

                                    if (led.is_total) {
                                        tr2.className = 'grand-child-total';
                                        td1g.textContent = led.label ?? 'Total';
                                    } else {
                                        tr2.className = 'grand-child-row';
                                        td1g.className = 'grand-child-label';

                                        if (led.ledger_id) {
                                            const a = document.createElement('a');
                                            a.textContent = led.label ?? 'Ledger';
                                            a.href =
                                                `${LEDGER_URL}?ledger_id=${encodeURIComponent(led.ledger_id)}&section=${encodeURIComponent(section)}&start_date=${encodeURIComponent($('pnl_start').value)}&end_date=${encodeURIComponent($('pnl_end').value)}&branch_id=${encodeURIComponent($('pnl_branch').value)}`;
                                            a.target = '_blank';
                                            td1g.appendChild(a);
                                        } else {
                                            td1g.textContent = led.label ?? 'Ledger';
                                        }

                                        if (typeof led.bills !== 'undefined') {
                                            td1g.insertAdjacentHTML('beforeend',
                                                ` <span class="child-meta">(Bills: ${led.bills})</span>`
                                                );
                                        }
                                    }

                                    td2g.textContent = led.amount ?? '0.00';
                                    tr2.appendChild(td1g);
                                    tr2.appendChild(td2g);
                                    tbody.appendChild(tr2);
                                });
                            }
                        });
                    }
                });
            }

            // Init inputs
            (function initInputs() {
                const saved = loadSaved();
                if (saved.start_date) $('pnl_start').value = saved.start_date;
                if (saved.end_date) $('pnl_end').value = saved.end_date;
                if (saved.branch_id !== undefined) $('pnl_branch').value = saved.branch_id;

                if (!$('pnl_start').value) $('pnl_start').value = daysAgoStr(29);
                if (!$('pnl_end').value) $('pnl_end').value = todayStr();

                ['pnl_start', 'pnl_end'].forEach(id => {
                    $(id).removeAttribute('readonly');
                    $(id).disabled = false;
                });

                updateHeader();
                updatePdfLink();
            })();

            $('pnl_filters')?.addEventListener('keydown', e => {
                if (e.key === 'Enter') e.preventDefault();
            });

            function refresh(e) {
                if (e?.preventDefault) e.preventDefault();

                const payload = {
                    branch_id: $('pnl_branch').value || '',
                    start_date: $('pnl_start').value || '',
                    end_date: $('pnl_end').value || '',
                    _ts: Date.now()
                };
                if (payload.start_date && payload.end_date && payload.start_date > payload.end_date) {
                    alert('Start date cannot be after End date.');
                    return;
                }

                updateHeader();
                updatePdfLink();
                saveCurrent();

                fetch(@json(route('reports.pnl_tally.data')), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Cache-Control': 'no-store'
                        },
                        body: JSON.stringify(payload),
                        cache: 'no-store',
                    })
                    .then(r => r.json())
                    .then(json => {
                        $('lbl_tr_dr').textContent = json?.trading?.dr?.title ?? 'Trading Account (Dr)';
                        $('lbl_tr_cr').textContent = json?.trading?.cr?.title ?? 'Trading Account (Cr)';
                        $('lbl_pl_dr').textContent = json?.pl?.dr?.title ?? 'Profit & Loss A/c (Dr)';
                        $('lbl_pl_cr').textContent = json?.pl?.cr?.title ?? 'Profit & Loss A/c (Cr)';

                        renderSide('#tbl_trading_dr tbody', json?.trading?.dr?.rows ?? json?.trading?.dr ?? []);
                        renderSide('#tbl_trading_cr tbody', json?.trading?.cr?.rows ?? json?.trading?.cr ?? []);
                        renderSide('#tbl_pl_dr tbody', json?.pl?.dr?.rows ?? json?.pl?.dr ?? []);
                        renderSide('#tbl_pl_cr tbody', json?.pl?.cr?.rows ?? json?.pl?.cr ?? []);

                        const trTot = json?.trading?.table_total ?? json?.trading?.total ?? '0.00';
                        const plTot = json?.pl?.table_total ?? json?.pl?.total ?? '0.00';
                        $('trading_total_dr').textContent = trTot;
                        $('trading_total_cr').textContent = trTot;
                        $('pl_total_dr').textContent = plTot;
                        $('pl_total_cr').textContent = plTot;
                    })
                    .catch(err => {
                        console.error('P&L fetch error', err);
                        alert('Failed to load P&L.');
                    });
            }

            ['pnl_branch', 'pnl_start', 'pnl_end'].forEach(id => {
                $(id).addEventListener('change', () => {
                    updateHeader();
                    updatePdfLink();
                    saveCurrent();
                });
            });

            $('pnl_apply').addEventListener('click', refresh);

            // First load
            refresh();
        })();
    </script>
@endsection
