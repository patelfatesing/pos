@extends('layouts.backend.datatable_layouts')

@section('styles')
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
            border-collapse: collapse;
            font-size: 14px;
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
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <!-- Card Header: same as Profit & Loss -->
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h4 class="mb-0">Balance Sheet</h4>

                    <div class="filter-controls-wrap">
                        <!-- From Date -->
                        <div class="date-box">
                            <label for="bs_from_date">From</label>
                            <input type="date" id="bs_from_date">
                        </div>

                        <!-- To Date -->
                        <div class="date-box">
                            <label for="bs_to_date">To</label>
                            <input type="date" id="bs_to_date">
                        </div>

                        <!-- Apply Filter -->
                        <button type="button" id="btn_apply_filter" class="btn-apply">
                            Apply
                        </button>

                        <!-- Divider 1 -->
                        <div class="v-divider"></div>

                        <!-- PDF Download -->
                        <a id="bs_pdf_link" class="btn-pdf-export" target="_blank">
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

                <div class="bs-card">

                    <div class="two-col">

                        <!-- Liabilities (Cr) -->
                        <div>
                            <div class="section-badge cr-badge">
                                <span id="lbl_liab">Liabilities</span>
                                <!-- <span class="section-tag">Cr</span> -->
                            </div>
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

                        <!-- Assets (Dr) -->
                        <div>
                            <div class="section-badge dr-badge">
                                <span id="lbl_assets">Assets</span>
                                <!-- <span class="section-tag">Dr</span> -->
                            </div>
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

    <script>
        const PDF_BASE = @json(Route::has('reports.balance-sheet.pdf') ? route('reports.balance-sheet.pdf') : '#');

        $(document).ready(function() {

            const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            let start = moment().startOf('month').format('YYYY-MM-DD');
            let end = moment().format('YYYY-MM-DD');

            // Set initial values
            $('#bs_from_date').val(start);
            $('#bs_to_date').val(end);

            // Filter Apply
            $('#btn_apply_filter').on('click', function() {
                const newStart = $('#bs_from_date').val();
                const newEnd = $('#bs_to_date').val();

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

            // update PDF link
            function updatePdfLink() {
                if (PDF_BASE === '#') {
                    $('#bs_pdf_link').attr('href', '#');
                    return;
                }
                const params = new URLSearchParams({
                    start_date: start,
                    end_date: end
                });
                $('#bs_pdf_link').attr('href', `${PDF_BASE}?${params.toString()}`);
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

            // init
            updatePdfLink();
            refresh();

        });
    </script>
@endsection