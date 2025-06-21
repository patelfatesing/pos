@extends('layouts.backend.layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/rangePlugin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Sales Report</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="table-responsive rounded mb-3">

                            <div class="container">
                                <div class="row mb-3">
                                    <!-- Date Range Picker Input -->
                                    <div class="col-md-3 mb-2">
                                        <input type="text" id="date_range" class="form-control" placeholder="Select Date"
                                            readonly />
                                    </div>

                                    <!-- Preset Dropdown -->
                                    <div class="col-md-3 mb-2">
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
                                    </div>

                                    <!-- Branch Filter -->
                                    <div class="col-md-3 mb-2">
                                        <select id="branch_id" class="form-control">
                                            <option value="">All Branches</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3 d-flex gap-2 mb-2">
                                        <button id="filter" class="btn btn-primary mr-2">Filter</button>
                                        <button id="reset" class="btn btn-secondary">Reset</button>
                                    </div>
                                </div>
                                <div class="table-responsive rounded mb-3">
                                    <table class="table data-tables table-striped" id="sales-table">
                                        <thead class="bg-white text-uppercase">
                                            <tr class="ligth ligth-data">
                                                <th>Store</th>
                                                <th>Invoice #</th>
                                                <th>Status</th>
                                                <th>Sub Total</th>
                                                <th>Tax</th>
                                                <th>Commission Amount</th> <!-- 👈 New -->
                                                <th>Party Amount</th> <!-- 👈 New -->
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
        </div>
    </div>
    <!-- Wrapper End -->

    <script>
        $(document).ready(function() {
            // Initialize Flatpickr
            flatpickr("#date_range", {
                mode: "range",
                dateFormat: "Y-m-d"
            });

            // Preset Date Ranges
            const setPresetRange = (value) => {
                let start = moment();
                let end = moment();

                switch (value) {
                    case 'today':
                        break;
                    case 'this_week':
                        start.startOf('week');
                        end.endOf('week');
                        break;
                    case 'last_week':
                        start.subtract(1, 'weeks').startOf('week');
                        end = start.clone().endOf('week');
                        break;
                    case 'this_month':
                        start.startOf('month');
                        end.endOf('month');
                        break;
                    case 'last_month':
                        start.subtract(1, 'months').startOf('month');
                        end = start.clone().endOf('month');
                        break;
                    case 'this_year':
                        start.startOf('year');
                        end.endOf('year');
                        break;
                    case 'last_year':
                        start.subtract(1, 'years').startOf('year');
                        end = start.clone().endOf('year');
                        break;
                    default:
                        return;
                }

                const range = `${start.format('YYYY-MM-DD')} to ${end.format('YYYY-MM-DD')}`;
                $('#date_range').val(range);
            };

            $('#preset_ranges').on('change', function() {
                setPresetRange(this.value);
            });

            function loadDataTable(start_date = '', end_date = '', branch_id = '') {
                $('#sales-table').DataTable({
                    processing: true,
                    destroy: true,
                    ajax: {
                        url: "{{ route('sales.report.data') }}",
                        data: {
                            start_date: start_date,
                            end_date: end_date,
                            branch_id: branch_id
                        },
                        dataSrc: function(json) {
                            // ✅ Filter out rows where branch_name contains (Summary)
                            json.data = json.data.filter(function(row) {
                                return !row.branch_name.includes('(Summary)');
                            });
                            return json.data;
                        }
                    },
                    columns: [{
                            data: 'branch_name'
                        },
                        {
                            data: 'invoice_number'
                        },
                        {
                            data: 'status'
                        },
                        {
                            data: 'sub_total'
                        },
                        {
                            data: 'tax'
                        },
                        {
                            data: 'commission_amount'
                        }, // 👈 New
                        {
                            data: 'party_amount'
                        }, // 👈 New
                        {
                            data: 'total'
                        },
                        {
                            data: 'items_count'
                        },
                        {
                            data: 'created_at'
                        }
                    ],
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [1,2,3,4,5, 6, 7,8] // make "action" column unsortable
                }],
                    drawCallback: function(settings) {
                        var api = this.api();
                        var rows = api.rows({
                            page: 'current'
                        }).nodes();
                        var lastBranch = null;
                        var branchTotal = 0;
                        var commissionTotal = 0;
                        var discountTotal = 0;
                        var subTotal = 0;
                        var branchItems = 0;
                        var grandTotal = 0;
                        var grandCommissionTotal = 0;
                        var grandDiscountTotal = 0;
                        var grandSubTotal = 0;
                        var grandTotal = 0;
                        var grandItems = 0;

                        api.rows({
                            page: 'current'
                        }).data().each(function(rowData, i) {
                            grandCommissionTotal += parseFloat(rowData.commission_amount || 0);
                            grandDiscountTotal += parseFloat(rowData.party_amount || 0);
                            grandSubTotal += parseFloat(rowData.sub_total || 0);
                            
                            grandTotal += parseFloat(rowData.total || 0);
                            grandItems += parseInt(rowData.items_count || 0);

                            if (lastBranch !== rowData.branch_name) {
                                if (lastBranch !== null) {
                                    // ✅ Insert branch subtotal row before next branch starts
                                    $(rows).eq(i).before(
                                        '<tr class="table-success">' +
                                        '<td colspan="3" style="text-align:right;"><b>' +
                                        lastBranch + ' Summary</b></td>' +
                                        '<td><b>' + subTotal.toFixed(2) + '</b></td>' +
                                        '<td></td>' +
                                        '<td><b>' + commissionTotal.toFixed(2) +
                                        '</b></td>' +
                                        '<td><b>' + discountTotal.toFixed(2) + '</b></td>' +
                                        '<td><b>' + branchTotal.toFixed(2) + '</b></td>' +
                                        '<td><b>' + branchItems + '</b></td>' +
                                        '<td></td>' +
                                        '</tr>'
                                    );
                                }

                                // Insert new branch header
                                $(rows).eq(i).before(
                                    '<tr class="table-primary">' +
                                    '<td colspan="10" style="text-align:left;"><b>Branch: ' +
                                    rowData.branch_name + '</b></td>' +
                                    '</tr>'
                                );

                                subTotal = 0;
                                commissionTotal = 0;
                                discountTotal = 0;
                                branchTotal = 0;
                                branchItems = 0;
                                lastBranch = rowData.branch_name;
                            }

                            branchTotal += parseFloat(rowData.total || 0);
                            commissionTotal += parseFloat(rowData.commission_amount || 0);
                            discountTotal += parseFloat(rowData.party_amount || 0);
                            subTotal += parseFloat(rowData.sub_total || 0);
                            branchItems += parseInt(rowData.items_count || 0);
                        });

                        // ✅ Insert the last branch subtotal
                        if (lastBranch !== null) {
                            $('#sales-table tbody').append(
                                '<tr class="table-success">' +
                                '<td colspan="3" style="text-align:right;"><b>' + lastBranch +
                                ' Summary</b></td>' +
                                '<td><b>' + subTotal.toFixed(2) + '</b></td>' +
                                '<td></td>' +
                                '<td><b>' + commissionTotal.toFixed(2) + '</b></td>' +
                                '<td><b>' + discountTotal.toFixed(2) + '</b></td>' +
                                '<td><b>' + branchTotal.toFixed(2) + '</b></td>' +
                                '<td><b>' + branchItems + '</b></td>' +
                                '<td></td>' +
                                '</tr>'
                            );
                        }

                        // ✅ Then finally, the grand total for all branches
                        $('#sales-table tbody').append(
                            '<tr class="table-warning">' +
                            '<td colspan="3" style="text-align:right;"><b>Grand Total (All Branches)</b></td>' +
                            '<td><b>' + grandSubTotal.toFixed(2) + '</b></td>' +
                            '<td><b></b></td>' +
                            '<td><b>' + grandCommissionTotal.toFixed(2) + '</b></td>' +
                            '<td><b>' + grandDiscountTotal.toFixed(2) + '</b></td>' +
                            '<td><b>' + grandTotal.toFixed(2) + '</b></td>' +
                            '<td><b>' + grandItems + '</b></td>' +
                            '<td></td>' +
                            '</tr>'
                        );
                    }

                });
            }

            loadDataTable();

            $('#filter').on('click', function() {
                const rangeText = $('#date_range').val();
                let start_date = '';
                let end_date = '';

                if (rangeText.includes('to')) {
                    const date_range = rangeText.split(' to ');
                    start_date = date_range[0];
                    end_date = date_range[1];
                }

                const branch_id = $('#branch_id').val();
                loadDataTable(start_date, end_date, branch_id);
            });

            $('#reset').on('click', function() {
                $('#date_range').val('');
                $('#preset_ranges').val('');
                $('#branch_id').val('');
                loadDataTable();
            });
        });
    </script>
@endsection
