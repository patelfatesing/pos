@extends('layouts.backend.datatable_layouts')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <style>
        .bs-card {
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

        #bs_period {
            cursor: pointer;
            font-size: 13px;
        }

        #bs_period:hover {
            text-decoration: underline;
            color: #007bff;
        }
    </style>
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <div class="card-header d-flex justify-content-between">
                    <h4>Balance Sheet</h4>
                    <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
                </div>

                <div class="bs-card">

                    <!-- ✅ TALLY STYLE DATE -->
                    <div class="mb-2">
                        <span id="bs_period"></span>
                    </div>

                    <!-- hidden daterange -->
                    <input type="text" id="bs_daterange" style="position:absolute; opacity:0;">

                    <div class="two-col">

                        <!-- Liabilities -->
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

                        <!-- Assets -->
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
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        $(document).ready(function() {

            const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            let start = moment().startOf('month').format('YYYY-MM-DD');
            let end = moment().format('YYYY-MM-DD');

            // ✅ init picker AFTER DOM ready
            $('#bs_daterange').daterangepicker({
                startDate: moment(start),
                endDate: moment(end),
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });

            // ✅ click label → open picker (SAFE CHECK)
            $('#bs_period').on('click', function() {

                const picker = $('#bs_daterange').data('daterangepicker');

                if (picker) {
                    picker.show();
                } else {
                    console.error('DateRangePicker not initialized');
                }
            });

            function updateHeader() {
                $('#bs_period').text(start + ' to ' + end);
            }

            function clearTbody(tbody) {
                while (tbody.firstChild) tbody.removeChild(tbody.firstChild);
            }

            function renderSide(selector, rows) {
                const tbody = document.querySelector(selector);
                clearTbody(tbody);

                (rows || []).forEach(r => {

                    const url = `/reports/group-summary/${r.id}?start_date=${start}&end_date=${end}`;

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                <td><a href="${url}" style="color:#2563eb">${r.label}</a></td>
                <td class="amount">${r.amount ?? '0.00'}</td>
            `;
                    tbody.appendChild(tr);
                });
            }

            function refresh() {

                fetch("{{ route('reports.balance-sheet.data') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF
                        },
                        body: JSON.stringify({
                            start_date: start,
                            end_date: end
                        })
                    })
                    .then(r => r.json())
                    .then(j => {

                        $('#lbl_liab').text(j.liabilities.title);
                        $('#lbl_assets').text(j.assets.title);

                        renderSide('#tbl_liabilities tbody', j.liabilities.rows);
                        renderSide('#tbl_assets tbody', j.assets.rows);

                        $('#liab_total').text(j.liabilities.total);
                        $('#asset_total').text(j.assets.total);
                    });
            }

            // ✅ auto apply
            $('#bs_daterange').on('apply.daterangepicker', function(ev, picker) {

                start = picker.startDate.format('YYYY-MM-DD');
                end = picker.endDate.format('YYYY-MM-DD');

                updateHeader();
                refresh();
            });

            // init
            updateHeader();
            refresh();

        });
    </script>
@endsection
