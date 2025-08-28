{{-- resources/views/reports/pnl_tally.blade.php --}}
@extends('layouts.backend.datatable_layouts')

@section('styles')
    <style>
        .pnl-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            background: #fff;
        }

        .pnl-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .pnl-title {
            font-weight: 700;
            font-size: 18px;
        }

        .pnl-sub {
            color: #6b7280;
        }

        .two-col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        table.pnl {
            width: 100%;
            border-collapse: collapse;
        }

        table.pnl th,
        table.pnl td {
            padding: 6px 8px;
            border-bottom: 1px solid #eee;
        }

        table.pnl th {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            color: #6b7280;
        }

        .row-total {
            font-weight: 700;
            border-top: 2px solid #111;
        }

        .amount {
            text-align: right;
        }

        .muted {
            color: #6b7280;
        }

        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            align-items: center;
            margin-bottom: 10px;
        }
    </style>
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <div class="pnl-card">
                    <div class="pnl-head">
                        <div class="pnl-title">Profit &amp; Loss (Tally Style)</div>
                        <div class="pnl-sub" id="pnl_period">—</div>
                    </div>

                    <div class="filters">
                        <label class="mb-0">Branch</label>
                        <select id="branch_id" class="form-control form-control-sm" style="min-width:220px;">
                            <option value="">All</option>
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>

                        <input type="date" id="start_date" class="form-control form-control-sm" placeholder="Start date">
                        <input type="date" id="end_date" class="form-control form-control-sm" placeholder="End date">

                        <button id="btn_refresh" class="btn btn-primary btn-sm">Apply</button>
                    </div>

                    {{-- Trading Account --}}
                    <div class="two-col">
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

                    {{-- Profit & Loss Account --}}
                    <div class="two-col">
                        <div>
                            <div class="muted mb-1">Profit &amp; Loss A/c (Dr)</div>
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
                            <div class="muted mb-1">Profit &amp; Loss A/c (Cr)</div>
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
            const toISO = (d) => new Date(d.getTime() - (d.getTimezoneOffset() * 60000)).toISOString().slice(0, 10);
            const now = new Date();
            const start = new Date();
            start.setDate(now.getDate() - 29);
            document.getElementById('start_date').value = toISO(start);
            document.getElementById('end_date').value = toISO(now);

            function fillTable(tbodyId, rows) {
                const tb = document.querySelector(tbodyId);
                tb.innerHTML = '';
                (rows || []).forEach(r => {
                    const tr = document.createElement('tr');
                    const td1 = document.createElement('td');
                    td1.textContent = r.label;
                    const td2 = document.createElement('td');
                    td2.className = 'amount';
                    td2.textContent = r.amount;
                    tr.appendChild(td1);
                    tr.appendChild(td2);
                    tb.appendChild(tr);
                });
            }

            function refresh() {
                const payload = {
                    branch_id: document.getElementById('branch_id').value,
                    start_date: document.getElementById('start_date').value,
                    end_date: document.getElementById('end_date').value,
                    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                };
                fetch("{{ route('reports.pnl_tally.data') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': payload._token
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(r => r.json())
                    .then(json => {
                        document.getElementById('pnl_period').textContent =
                            `${json.branch} • ${json.period.start} → ${json.period.end}`;

                        fillTable('#tbl_trading_dr tbody', json.trading.dr);
                        fillTable('#tbl_trading_cr tbody', json.trading.cr);
                        document.getElementById('trading_total_dr').textContent = json.trading.total;
                        document.getElementById('trading_total_cr').textContent = json.trading.total;

                        fillTable('#tbl_pl_dr tbody', json.pl.dr);
                        fillTable('#tbl_pl_cr tbody', json.pl.cr);
                        document.getElementById('pl_total_dr').textContent = json.pl.total;
                        document.getElementById('pl_total_cr').textContent = json.pl.total;
                    })
                    .catch(() => alert('Failed to load P&L.'));
            }

            document.getElementById('btn_refresh').addEventListener('click', refresh);
            refresh();
        })();
    </script>
@endsection
