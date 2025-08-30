@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/rangePlugin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <style>
        .filters.one-line {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: nowrap;
            white-space: nowrap
        }

        .filters.one-line .form-control {
            flex: 0 1 200px;
            min-width: 140px
        }

        .filters.one-line .btn {
            flex: 0 0 auto
        }

        .table-primary td,
        .table-success td,
        .table-warning td {
            font-weight: 600
        }
    </style>

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-lg-12 d-flex flex-wrap align-items-center justify-content-between mb-3">
                        <h4 class="mb-0">Sales Report — Shift-wise</h4>
                    </div>

                    <div class="col-lg-12">
                        <div class="table-responsive rounded mb-3 p-2">

                            <!-- one-line filters -->
                            <div class="filters one-line mb-2">
                                <input type="text" id="date_range" class="form-control" placeholder="Select Date Range"
                                    readonly />
                                <select id="preset_ranges" class="form-control">
                                    <option value="">Select Preset</option>
                                    <option value="today">Today</option>
                                    <option value="this_week">This Week</option>
                                    <option value="last_week">Last Week</option>
                                    <option value="this_month">This Month</option>
                                    <option value="last_month">Last Month</option>
                                    <option value="this_year">This Year</option>
                                    <option value="last_year">Last Year</option>
                                </select>
                                <select id="branch_id" class="form-control" data-default="all">
                                    <option value="" selected>All Branches</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <button id="filter" class="btn btn-primary btn-sm">Filter</button>
                                <button id="reset" class="btn btn-outline-secondary btn-sm">Reset</button>
                            </div>

                            <table class="table table-striped" id="sales-table">
                                <thead class="bg-white text-uppercase">
                                    <tr class="ligth ligth-data">
                                        <th style="display:none;">Branch</th> <!-- hidden grouping column -->
                                        <th>Shift No</th>
                                        <th>Sub Total</th>
                                        <th>Commission Amount</th>
                                        <th>Party Amount</th>
                                        <th>Total</th>
                                        <th>Item Count</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        $(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Date picker
            flatpickr("#date_range", {
                mode: "range",
                dateFormat: "Y-m-d"
            });

            // Presets
            function setPresetRange(val) {
                let s = moment(),
                    e = moment();
                switch (val) {
                    case 'today':
                        break;
                    case 'this_week':
                        s.startOf('week');
                        e.endOf('week');
                        break;
                    case 'last_week':
                        s.subtract(1, 'weeks').startOf('week');
                        e = s.clone().endOf('week');
                        break;
                    case 'this_month':
                        s.startOf('month');
                        e.endOf('month');
                        break;
                    case 'last_month':
                        s.subtract(1, 'months').startOf('month');
                        e = s.clone().endOf('month');
                        break;
                    case 'this_year':
                        s.startOf('year');
                        e.endOf('year');
                        break;
                    case 'last_year':
                        s.subtract(1, 'years').startOf('year');
                        e = s.clone().endOf('year');
                        break;
                    default:
                        return;
                }
                $('#date_range').val(s.format('YYYY-MM-DD') + ' to ' + e.format('YYYY-MM-DD'));
            }
            $('#preset_ranges').on('change', function() {
                setPresetRange(this.value);
            });

            function parseRange() {
                const txt = $('#date_range').val() || '';
                if (!txt.includes('to')) return {
                    start_date: '',
                    end_date: ''
                };
                const [s, e] = txt.split(' to ');
                return {
                    start_date: s,
                    end_date: e
                };
            }

            function n(v) {
                const x = parseFloat(v);
                return isNaN(x) ? 0 : x;
            }

            function i(v) {
                const x = parseInt(v, 10);
                return isNaN(x) ? 0 : x;
            }

            function setAllDefaults(scope = '.filters') {
                $(`${scope} select[data-default="all"]`).each(function() {
                    const $sel = $(this);
                    if ($sel.find('option[value=""]').length === 0) {
                        const label = $sel.attr('data-all-label') || 'All';
                        $sel.prepend(`<option value="">${label}</option>`);
                    }
                    $sel.val('');
                }).trigger('change');
            }

            function loadTable() {
                const {
                    start_date,
                    end_date
                } = parseRange();
                const branch_id = $('#branch_id').val();

                $('#sales-table').DataTable({
                    processing: true,
                    destroy: true,
                    ajax: {
                        url: "{{ route('sales.report.data') }}",
                        type: 'POST',
                        data: {
                            start_date,
                            end_date,
                            branch_id
                        }
                    },
                    columns: [{
                            data: 'branch_name'
                        }, // hidden
                        {
                            data: 'shift_no'
                        },
                        {
                            data: 'sub_total'
                        },
                        {
                            data: 'commission_amount'
                        },
                        {
                            data: 'party_amount'
                        },
                        {
                            data: 'total'
                        },
                        {
                            data: 'items_count'
                        },
                        {
                            data: 'shift_date'
                        }
                    ],
                    columnDefs: [{
                        targets: [0],
                        visible: false,
                        searchable: false
                    }],
                    order: [
                        [0, 'asc'],
                        [7, 'desc'],
                        [1, 'asc']
                    ],

                    // 👇 your requested page-size menu
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "All"]
                    ],
                    pageLength: 10,

                    drawCallback: function() {
                        const api = this.api();
                        const rows = api.rows({
                            page: 'current'
                        }).nodes();
                        const data = api.rows({
                            page: 'current'
                        }).data();

                        let lastBranch = null;

                        // branch running sums
                        let bSub = 0,
                            bCom = 0,
                            bPar = 0,
                            bTot = 0,
                            bItems = 0;

                        // grand totals
                        let gSub = 0,
                            gCom = 0,
                            gPar = 0,
                            gTot = 0,
                            gItems = 0;

                        data.each(function(r, idx) {
                            // accumulate grand totals
                            gSub += n(r.sub_total);
                            gCom += n(r.commission_amount);
                            gPar += n(r.party_amount);
                            gTot += n(r.total);
                            gItems += i(r.items_count);

                            // on branch change -> print previous branch summary + add new header
                            if (lastBranch !== r.branch_name) {
                                if (lastBranch !== null) {
                                    $(rows).eq(idx).before(`
                <tr class="table-success">
                  <td colspan="1" style="display:none;"></td>
                  <td style="text-align:right;"><b>${lastBranch} Summary</b></td>
                  <td><b>${bSub.toFixed(2)}</b></td>
                  <td><b>${bCom.toFixed(2)}</b></td>
                  <td><b>${bPar.toFixed(2)}</b></td>
                  <td><b>${bTot.toFixed(2)}</b></td>
                  <td><b>${bItems}</b></td>
                  <td></td>
                </tr>
              `);
                                }
                                $(rows).eq(idx).before(`
              <tr class="table-primary">
                <td colspan="8"><b>Branch: ${r.branch_name}</b></td>
              </tr>
            `);
                                bSub = bCom = bPar = bTot = bItems = 0;
                                lastBranch = r.branch_name;
                            }

                            // accumulate branch totals
                            bSub += n(r.sub_total);
                            bCom += n(r.commission_amount);
                            bPar += n(r.party_amount);
                            bTot += n(r.total);
                            bItems += i(r.items_count);
                        });

                        // final branch summary
                        if (lastBranch !== null) {
                            $('#sales-table tbody').append(`
            <tr class="table-success">
              <td colspan="1" style="display:none;"></td>
              <td style="text-align:right;"><b>${lastBranch} Summary</b></td>
              <td><b>${bSub.toFixed(2)}</b></td>
              <td><b>${bCom.toFixed(2)}</b></td>
              <td><b>${bPar.toFixed(2)}</b></td>
              <td><b>${bTot.toFixed(2)}</b></td>
              <td><b>${bItems}</b></td>
              <td></td>
            </tr>
          `);
                        }

                        // grand total
                        $('#sales-table tbody').append(`
          <tr class="table-warning">
            <td colspan="1" style="display:none;"></td>
            <td style="text-align:right;"><b>Grand Total (All Branches)</b></td>
            <td><b>${gSub.toFixed(2)}</b></td>
            <td><b>${gCom.toFixed(2)}</b></td>
            <td><b>${gPar.toFixed(2)}</b></td>
            <td><b>${gTot.toFixed(2)}</b></td>
            <td><b>${gItems}</b></td>
            <td></td>
          </tr>
        `);
                    }
                });
            }

            // Defaults: last 30 days + All branches
            (function setDefaultRange() {
                const end = moment(),
                    start = moment().subtract(29, 'days');
                $('#date_range').val(start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
            })();
            (function setAllDefaults(scope = '.filters') {
                $(`${scope} select[data-default="all"]`).each(function() {
                    const $sel = $(this);
                    if ($sel.find('option[value=""]').length === 0) {
                        const label = $sel.attr('data-all-label') || 'All';
                        $sel.prepend(`<option value="">${label}</option>`);
                    }
                    $sel.val('');
                }).trigger('change');
            })();

            // initial load
            loadTable();

            // actions
            $('#filter').on('click', function() {
                loadTable();
            });
            $('#reset').on('click', function() {
                $('#preset_ranges').val('');
                const end = moment(),
                    start = moment().subtract(29, 'days');
                $('#date_range').val(start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
                $('#branch_id').val('');
                loadTable();
            });

        });
    </script>
@endsection
