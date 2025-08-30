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

        .filters {
            overflow: visible;
        }

        /* instead of hidden */
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

                        {{-- No Blade defaults: JS sets them once or restores from localStorage --}}
                        <input type="date" id="pnl_start" class="form-control form-control-sm" autocomplete="off">
                        <input type="date" id="pnl_end" class="form-control form-control-sm" autocomplete="off">

                        <button id="pnl_apply" type="button" class="btn btn-primary btn-sm">Apply</button>
                    </div>
                    <a class="btn btn-sm btn-outline-primary"
                        href="{{ route('reports.profit-loss.pdf', [
                            'start_date' => request('start_date'),
                            'end_date' => request('end_date'),
                            'branch_id' => request('branch_id'),
                        ]) }}"
                        target="_blank">
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
        (function() {
            // ---------- Helpers ----------
            const $ = (id) => document.getElementById(id);

            // Safe YYYY-MM-DD for <input type="date">
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

            // Persist user picks (prevents “jumping back”)
            const LS_KEY = 'pnl_tally_filters_v2';
            const loadSaved = () => {
                try {
                    return JSON.parse(localStorage.getItem(LS_KEY) || '{}');
                } catch {
                    return {};
                }
            };
            const saveCurrent = () => {
                localStorage.setItem(LS_KEY, JSON.stringify({
                    branch_id: $('pnl_branch').value || '',
                    start_date: $('pnl_start').value || '',
                    end_date: $('pnl_end').value || '',
                }));
            };

            // Initialize inputs with saved or last-30-days
            (function initInputs() {
                const saved = loadSaved();
                if (saved.start_date) $('pnl_start').value = saved.start_date;
                if (saved.end_date) $('pnl_end').value = saved.end_date;
                if (saved.branch_id !== undefined) $('pnl_branch').value = saved.branch_id;

                if (!$('pnl_start').value) $('pnl_start').value = daysAgoStr(29);
                if (!$('pnl_end').value) $('pnl_end').value = todayStr();

                // Make sure nothing disabled/readonly
                ['pnl_start', 'pnl_end'].forEach(id => {
                    $(id).removeAttribute('readonly');
                    $(id).disabled = false;
                });
            })();

            function selectedBranchText() {
                const sel = $('pnl_branch');
                return sel?.selectedOptions?.[0]?.textContent?.trim() || 'All';
            }

            function updateHeader() {
                const s = $('pnl_start').value || '';
                const e = $('pnl_end').value || '';
                $('pnl_period').textContent = `${selectedBranchText()} • ${s} to ${e}`;
            }

            function clearTbody(tbody) {
                while (tbody.firstChild) tbody.removeChild(tbody.firstChild);
            }

            function appendRow(tbody, label, amount, extraHtml = '') {
                const tr = document.createElement('tr');
                const td1 = document.createElement('td');
                const td2 = document.createElement('td');
                td1.innerHTML = label + (extraHtml || '');
                td2.className = 'amount';
                td2.textContent = (amount ?? '0.00');
                tr.appendChild(td1);
                tr.appendChild(td2);
                tbody.appendChild(tr);
            }

            function renderSide(tbodySelector, rows) {
                const tbody = document.querySelector(tbodySelector);
                clearTbody(tbody);
                (rows || []).forEach(r => {
                    appendRow(tbody, r.label, r.amount);
                    if (Array.isArray(r.children)) {
                        r.children.forEach(ch => {
                            const tr = document.createElement('tr');
                            tr.className = 'child-row';
                            const td1 = document.createElement('td');
                            td1.className = 'child-label';
                            const td2 = document.createElement('td');
                            td2.className = 'amount';
                            const bills = (typeof ch.bills !== 'undefined') ?
                                `<span class="child-meta"> (Bills: ${ch.bills})</span>` : '';
                            td1.innerHTML = (ch.label ?? 'Ledger') + bills;
                            td2.textContent = ch.amount ?? '0.00';
                            tr.appendChild(td1);
                            tr.appendChild(td2);
                            tbody.appendChild(tr);
                        });
                    }
                });
            }

            // Prevent parent form submit (Enter key)
            $('pnl_filters')?.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') e.preventDefault();
            });

            // ---------- Main loader ----------
            function refresh(e) {
                if (e?.preventDefault) e.preventDefault();

                // Read current values RIGHT NOW (no stale refs)
                const payload = {
                    branch_id: $('pnl_branch').value || '',
                    start_date: $('pnl_start').value || '',
                    end_date: $('pnl_end').value || '',
                    _ts: Date.now()
                };

                // Quick sanity: start <= end
                if (payload.start_date && payload.end_date && payload.start_date > payload.end_date) {
                    alert('Start date cannot be after End date.');
                    return;
                }

                // Reflect & persist
                updateHeader();
                saveCurrent();

                // Debug what we send
                console.log('P&L sending JSON:', payload);

                fetch("{{ route('reports.pnl_tally.data') }}", {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json', // <— send JSON
                            'X-CSRF-TOKEN': CSRF,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Cache-Control': 'no-store'
                        },
                        body: JSON.stringify(payload),
                        cache: 'no-store',
                    })
                    .then(r => r.json())
                    .then(json => {
                        // Titles
                        $('lbl_tr_dr').textContent = json?.trading?.dr?.title ?? 'Trading Account (Dr)';
                        $('lbl_tr_cr').textContent = json?.trading?.cr?.title ?? 'Trading Account (Cr)';
                        $('lbl_pl_dr').textContent = json?.pl?.dr?.title ?? 'Profit & Loss A/c (Dr)';
                        $('lbl_pl_cr').textContent = json?.pl?.cr?.title ?? 'Profit & Loss A/c (Cr)';

                        // Tables
                        renderSide('#tbl_trading_dr tbody', json?.trading?.dr?.rows ?? json?.trading?.dr ?? []);
                        renderSide('#tbl_trading_cr tbody', json?.trading?.cr?.rows ?? json?.trading?.cr ?? []);
                        renderSide('#tbl_pl_dr tbody', json?.pl?.dr?.rows ?? json?.pl?.dr ?? []);
                        renderSide('#tbl_pl_cr tbody', json?.pl?.cr?.rows ?? json?.pl?.cr ?? []);

                        // Totals (mirror across sides)
                        const trTot = json?.trading?.table_total ?? json?.trading?.total ?? '0.00';
                        $('trading_total_dr').textContent = trTot;
                        $('trading_total_cr').textContent = trTot;

                        const plTot = json?.pl?.table_total ?? json?.pl?.total ?? '0.00';
                        $('pl_total_dr').textContent = plTot;
                        $('pl_total_cr').textContent = plTot;
                    })
                    .catch(err => {
                        console.error('P&L fetch error', err);
                        alert('Failed to load P&L.');
                    });
            }

            // Live header update & save on changes
            ['pnl_branch', 'pnl_start', 'pnl_end'].forEach(id => {
                $(id).addEventListener('change', () => {
                    updateHeader();
                    saveCurrent();
                });
            });

            // Apply click (no form submit)
            $('pnl_apply').addEventListener('click', refresh);

            // First render
            updateHeader();
            refresh();
        })();
    </script>
@endsection
