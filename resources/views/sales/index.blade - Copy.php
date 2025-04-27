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
                                        <input type="text" id="date_range" class="form-control" placeholder="Select date range" readonly />
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
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3 d-flex gap-2 mb-2">
                                        <button id="filter" class="btn btn-primary">Filter</button>
                                        <button id="reset" class="btn btn-secondary">Reset</button>
                                    </div>
                                </div>

                                <table id="sales-table" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Status</th>
                                            <th>Sub Total</th>
                                            <th>Tax</th>
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
    <!-- Wrapper End -->

    <script>
        $(document).ready(function () {
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

            $('#preset_ranges').on('change', function () {
                setPresetRange(this.value);
            });

            function loadDataTable(start_date = '', end_date = '', branch_id = '') {
                $('#sales-table').DataTable({
                    processing: true,
                    serverSide: true,
                    destroy: true,
                    ajax: {
                        url: "{{ route('sales.report.data') }}",
                        data: {
                            start_date: start_date,
                            end_date: end_date,
                            branch_id: branch_id
                        }
                    },
                    columns: [
                        { data: 'invoice_number', name: 'invoice_number' },
                        { data: 'status', name: 'status' },
                        { data: 'sub_total', name: 'sub_total' },
                        { data: 'tax', name: 'tax' },
                        { data: 'total', name: 'total' },
                        { data: 'items_count', name: 'items_count', orderable: false, searchable: false },
                        { data: 'created_at', name: 'created_at' }
                    ]
                });
            }

            loadDataTable();

            $('#filter').on('click', function () {
                const date_range = $('#date_range').val().split(' to ');
                const start_date = date_range[0] ?? '';
                const end_date = date_range[1] ?? '';
                const branch_id = $('#branch_id').val();
                loadDataTable(start_date, end_date, branch_id);
            });

            $('#reset').on('click', function () {
                $('#date_range').val('');
                $('#preset_ranges').val('');
                $('#branch_id').val('');
                loadDataTable();
            });
        });
    </script>
@endsection
