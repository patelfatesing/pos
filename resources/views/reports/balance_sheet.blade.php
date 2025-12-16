@extends('layouts.backend.datatable_layouts')

@section('styles')
    <style>
        .bs-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            background: #fff
        }

        .bs-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px
        }

        .bs-title {
            font-weight: 700;
            font-size: 18px
        }

        .bs-sub {
            color: #6b7280
        }

        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px
        }

        table.bs {
            width: 100%;
            border-collapse: collapse
        }

        table.bs th,
        table.bs td {
            padding: 6px 8px;
            border-bottom: 1px solid #eee
        }

        table.bs th {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            color: #6b7280
        }

        .amount {
            text-align: right
        }

        .row-total {
            font-weight: 700;
            border-top: 2px solid #111
        }

        .child-row td {
            padding-top: 2px;
            padding-bottom: 2px
        }

        .child-label {
            padding-left: 22px;
            position: relative
        }

        .child-label:before {
            content: "•";
            position: absolute;
            left: 10px;
            top: 0;
            color: #9ca3af
        }

        @media(max-width:768px) {
            .two-col {
                grid-template-columns: 1fr
            }
        }
    </style>
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="bs-card">
                    <div class="bs-head">
                        <div class="bs-title">Balance Sheet</div>
                        <div class="bs-sub" id="bs_asof">—</div>
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-2" id="bs_filters">
                        <input type="date" id="bs_start" class="form-control form-control-sm" style="max-width:170px">
                        <input type="date" id="bs_end" class="form-control form-control-sm mr-2" style="max-width:170px">
                        <button id="bs_apply" class="btn btn-primary btn-sm">Apply</button>
                    </div>

                    <div class="two-col">
                        <div>
                            <div class="text-muted mb-1" id="lbl_liab">Liabilities</div>
                            <table class="bs" id="tbl_liabilities">
                                <thead>
                                    <tr>
                                        <th>PARTICULARS</th>
                                        <th class="amount">AMOUNT</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr class="row-total">
                                        <td>Total</td>
                                        <td class="amount" id="liab_total">0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div>
                            <div class="text-muted mb-1" id="lbl_assets">Assets</div>
                            <table class="bs" id="tbl_assets">
                                <thead>
                                    <tr>
                                        <th>PARTICULARS</th>
                                        <th class="amount">AMOUNT</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr class="row-total">
                                        <td>Total</td>
                                        <td class="amount" id="asset_total">0.00</td>
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
            const $ = (id) => document.getElementById(id);
            const fmtDate = d => {
                const t = new Date(d);
                const z = new Date(t.getTime() - t.getTimezoneOffset() * 60000);
                return z.toISOString().slice(0, 10);
            };

            const today = fmtDate(new Date());
            const firstOfMonth = (() => {
                const d = new Date();
                d.setDate(1);
                return fmtDate(d);
            })();

            const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            $('bs_start').value = firstOfMonth;
            $('bs_end').value = today;

            function setHeader(asOf, period) {
                $('bs_asof').textContent = `As at ${asOf} (Period: ${period})`;
            }

            function clearTbody(tbody) {
                while (tbody.firstChild) tbody.removeChild(tbody.firstChild);
            }

            function appendRow(tbody, label, amount) {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${label}</td>
                    <td class="amount">${amount ?? '0.00'}</td>
                `;
                tbody.appendChild(tr);
            }

            function renderSide(selector, rows) {
                const tbody = document.querySelector(selector);
                clearTbody(tbody);

                (rows || []).forEach(r => {
                    appendRow(tbody, r.label, r.amount);

                    (r.children || []).forEach(ch => {
                        const tr = document.createElement('tr');
                        tr.className = 'child-row';
                        tr.innerHTML = `
                            <td class="child-label">${ch.label ?? '—'}</td>
                            <td class="amount">${ch.amount ?? '0.00'}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                });
            }

            function refresh() {
                const payload = {
                    start_date: $('bs_start').value,
                    end_date: $('bs_end').value,
                    _ts: Date.now()
                };

                fetch("{{ route('reports.balance-sheet.data') }}", {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(r => r.json())
                    .then(j => {
                        setHeader(j.header.as_of, j.header.period);

                        $('lbl_liab').textContent = j.liabilities.title;
                        $('lbl_assets').textContent = j.assets.title;

                        renderSide('#tbl_liabilities tbody', j.liabilities.rows);
                        renderSide('#tbl_assets tbody', j.assets.rows);

                        $('liab_total').textContent = j.liabilities.total;
                        $('asset_total').textContent = j.assets.total;
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Failed to load Balance Sheet.');
                    });
            }

            $('bs_apply').addEventListener('click', refresh);
            refresh();
        })();
    </script>
@endsection
