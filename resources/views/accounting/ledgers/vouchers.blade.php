@extends('layouts.backend.datatable_layouts')

@section('styles')
    <!-- daterangepicker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <style>
        .table-fixed {
            width: 100%;
            table-layout: fixed;
        }

        .particulars {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tally-header {
            background: #0b3b3b;
            color: #fff;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .controls .btn {
            margin-right: 6px;
        }
    </style>
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="tally-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5>Ledger Vouchers — {{ $ledger->name }}</h5>
                        <small id="dateRangeLabel">{{ $start }} to {{ $end }}</small>
                    </div>

                    <div class="controls">
                        <a href="{{ route('accounting.vouchers.create') }}" class="btn btn-outline-light btn-sm">Add
                            Voucher</a>
                        {{-- <button id="alterBtn" class="btn btn-outline-light btn-sm">Alter</button>
                        <button id="deleteBtn" class="btn btn-danger btn-sm">Delete</button> --}}
                        <button id="printBtn" class="btn btn-outline-warning btn-sm">Print</button>

                        <input type="text" id="daterange" class="form-control d-inline-block" style="width:220px" />

                        <select id="vchType" class="form-control d-inline-block" style="width:160px">
                            <option value="">All Types</option>
                            @foreach ($voucherTypes as $vt)
                                <option value="{{ $vt }}">{{ $vt }}</option>
                            @endforeach
                        </select>

                        <button id="applyFilter" class="btn btn-light btn-sm">Apply</button>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-4">
                        <div class="card p-2">
                            <strong>Opening Balance:</strong>
                            <div id="openingBalance">-</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card p-2">
                            <strong>Current Total:</strong>
                            <div id="currentTotal">-</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card p-2">
                            <strong>Closing Balance:</strong>
                            <div id="closingBalance">-</div>
                        </div>
                    </div>
                </div>

                <table id="vouchersTable" class="display table-fixed" style="width:100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Particulars</th>
                            <th>Vch Type</th>
                            <th>Vch No</th>
                            <th>Debit</th>
                            <th>Credit</th>
                            {{-- <th style="width:120px">Actions</th> --}}
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- moment & daterangepicker only (we rely on jQuery/DataTables from your deferred bundle) -->
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        // Wait for window.onload so deferred bundle (containing jQuery/DataTables) finishes loading.
        window.onload = function() {

            // Diagnostics in console
            console.log("=== Ledger Vouchers page boot ===");
            console.log("jQuery available:", typeof $.fn !== 'undefined' && typeof $.fn.jquery !== 'undefined' ? $.fn
                .jquery : 'NO jQuery');
            console.log("DataTables plugin:", typeof $.fn !== 'undefined' && typeof $.fn.DataTable !== 'undefined' ?
                'yes' : 'no');
            console.log("daterangepicker plugin:", typeof $.fn !== 'undefined' && typeof $.fn.daterangepicker !==
                'undefined' ? 'yes' : 'no');
            console.log("moment:", typeof moment !== 'undefined' ? 'yes' : 'no');

            // Safety checks

            // Print button
            $('#printBtn').on('click', function() {
                window.print();
            });

            if (typeof $ === 'undefined') {
                console.error(
                    "jQuery not found. Ensure your backend bundle includes jQuery and loads before this script finishes."
                );
                return;
            }
            if (typeof moment === 'undefined') {
                console.error("moment.js not found. DateRangePicker requires moment. Check network or CDN.");
                return;
            }
            if (typeof $.fn.daterangepicker === 'undefined') {
                console.error("daterangepicker plugin not found. Make sure daterangepicker.min.js loaded correctly.");
                return;
            }
            if (typeof $.fn.DataTable === 'undefined') {
                console.warn(
                    "DataTables plugin not found — table features may not work. If missing, include DataTables in your bundle."
                );
            }

            // Initialize date range picker
            let start = '{{ $start }}';
            let end = '{{ $end }}';

            function cb(s, e) {
                $('#daterange').val(s.format('YYYY-MM-DD') + ' - ' + e.format('YYYY-MM-DD'));
                $('#dateRangeLabel').text(s.format('YYYY-MM-DD') + ' to ' + e.format('YYYY-MM-DD'));
            }

            try {
                $('#daterange').daterangepicker({
                    startDate: moment(start),
                    endDate: moment(end),
                    locale: {
                        format: 'YYYY-MM-DD'
                    }
                }, cb);

                cb(moment(start), moment(end));
            } catch (err) {
                console.error("Error initializing daterangepicker:", err);
            }

            // Initialize DataTable (if DataTables plugin exists)
            let table;
            if (typeof $.fn.DataTable !== 'undefined') {
                table = $('#vouchersTable').DataTable({
                    processing: true,
                    serverSide: false, // using our AJAX endpoint for data
                    ajax: {
                        url: "{{ route('accounting.ledgers.vouchers.data', $ledger->id) }}",
                        data: function(d) {
                            let dr = $('#daterange').val().split(' - ');
                            d.start_date = dr[0] || '{{ $start }}';
                            d.end_date = dr[1] || '{{ $end }}';
                            d.vch_type = $('#vchType').val();
                        },
                        dataSrc: function(json) {
                            // Update balances and totals
                            const opening = json.opening || {
                                balance: 0
                            };
                            $('#openingBalance').text((opening.balance >= 0 ? 'Dr ' : 'Cr ') + Math.abs(
                                opening.balance || 0).toFixed(2));

                            const period = json.period || {
                                total_debit: 0,
                                total_credit: 0
                            };
                            $('#currentTotal').text('Dr: ' + (period.total_debit || 0).toFixed(2) +
                                ' | Cr: ' + (period.total_credit || 0).toFixed(2));

                            const closing = (opening.balance || 0) + ((period.total_debit || 0) - (period
                                .total_credit || 0));
                            $('#closingBalance').text((closing >= 0 ? 'Dr ' : 'Cr ') + Math.abs(closing)
                                .toFixed(2));

                            return json.data || [];
                        }
                    },
                    columns: [{
                            data: 'date'
                        },
                        {
                            data: 'particulars',
                            className: 'particulars'
                        },
                        {
                            data: 'vch_type'
                        },
                        {
                            data: 'vch_no'
                        },
                        {
                            data: 'debit',
                            render: $.fn.DataTable ? $.fn.dataTable.render.number(',', '.', 2, '') : null
                        },
                        {
                            data: 'credit',
                            render: $.fn.DataTable ? $.fn.dataTable.render.number(',', '.', 2, '') : null
                        },
                        // {
                        //     data: null,
                        //     orderable: false,
                        //     render: function(row) {
                        //         return `
                    //             <div class="btn-group">
                    //                 <a href="/accounting/vouchers/${row.voucher_id}/edit" class="btn btn-sm btn-outline-secondary">Alter</a>
                    //                 <button class="btn btn-sm btn-outline-danger btn-delete-voucher" data-id="${row.voucher_id}">Delete Vch</button>
                    //                 <button class="btn btn-sm btn-outline-danger btn-delete-line" data-id="${row.line_id}">Delete Line</button>
                    //             </div>
                    //         `;
                        //     }
                        // }
                    ],
                    pageLength: 25
                });
            } else {
                // If DataTables not available, fallback: plain AJAX fetch and render simple rows
                console.warn("DataTables missing — loading rows with simple AJAX render.");

                function loadPlainRows() {
                    let dr = $('#daterange').val().split(' - ');
                    $.get("{{ route('accounting.ledgers.vouchers.data', $ledger->id) }}", {
                        start_date: dr[0] || '{{ $start }}',
                        end_date: dr[1] || '{{ $end }}',
                        vch_type: $('#vchType').val()
                    }, function(json) {
                        const tbody = $('#vouchersTable tbody').empty();
                        $('#openingBalance').text((json.opening.balance >= 0 ? 'Dr ' : 'Cr ') + Math.abs(json
                            .opening.balance || 0).toFixed(2));
                        $('#currentTotal').text('Dr: ' + (json.period.total_debit || 0).toFixed(2) + ' | Cr: ' +
                            (json.period.total_credit || 0).toFixed(2));
                        const closing = (json.opening.balance || 0) + ((json.period.total_debit || 0) - (json
                            .period.total_credit || 0));
                        $('#closingBalance').text((closing >= 0 ? 'Dr ' : 'Cr ') + Math.abs(closing).toFixed(
                            2));
                        (json.data || []).forEach(function(row) {
                            const tr = `<tr>
                                <td>${row.date}</td>
                                <td class="particulars">${row.particulars || ''}</td>
                                <td>${row.vch_type || ''}</td>
                                <td>${row.vch_no || ''}</td>
                                <td>${(row.debit || 0).toFixed ? (row.debit || 0).toFixed(2) : (row.debit || 0)}</td>
                                <td>${(row.credit || 0).toFixed ? (row.credit || 0).toFixed(2) : (row.credit || 0)}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/accounting/vouchers/${row.voucher_id}/edit" class="btn btn-sm btn-outline-secondary">Alter</a>
                                        <button class="btn btn-sm btn-outline-danger btn-delete-voucher" data-id="${row.voucher_id}">Delete Vch</button>
                                        <button class="btn btn-sm btn-outline-danger btn-delete-line" data-id="${row.line_id}">Delete Line</button>
                                    </div>
                                </td>
                            </tr>`;
                            tbody.append(tr);
                        });
                    }).fail(function() {
                        console.error("Failed to fetch voucher data.");
                    });
                }

                // initial load
                loadPlainRows();

                // expose table variable to reload
                table = {
                    ajax: {
                        reload: loadPlainRows
                    }
                };
            }

            // Apply filter button
            $('#applyFilter').on('click', function() {
                if (table && table.ajax && typeof table.ajax.reload === 'function') {
                    table.ajax.reload();
                }
            });

            // Delete voucher (hard delete)
            $(document).on('click', '.btn-delete-voucher', function() {
                if (!confirm('Delete voucher and all its lines? This cannot be undone.')) return;
                const id = $(this).data('id');
                $.ajax({
                    url: '/accounting/vouchers/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        alert(res.message || 'Voucher deleted');
                        if (table && table.ajax && typeof table.ajax.reload === 'function') {
                            table.ajax.reload();
                        } else {
                            // fallback reload
                            if (typeof loadPlainRows === 'function') loadPlainRows();
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON
                            .message : 'Error deleting voucher';
                        alert(msg);
                    }
                });
            });

            // Delete voucher line
            $(document).on('click', '.btn-delete-line', function() {
                if (!confirm('Delete voucher line? This cannot be undone.')) return;
                const id = $(this).data('id');
                $.ajax({
                    url: '/accounting/voucher-lines/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        alert(res.message || 'Voucher line deleted');
                        if (table && table.ajax && typeof table.ajax.reload === 'function') {
                            table.ajax.reload();
                        } else {
                            if (typeof loadPlainRows === 'function') loadPlainRows();
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON
                            .message : 'Error deleting line';
                        alert(msg);
                    }
                });
            });

        }; // end window.onload
    </script>
@endsection
