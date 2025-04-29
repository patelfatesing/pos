@extends('layouts.backend.layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">


                <!-- Date Filters -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Daily Sales List</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <input type="date" id="start_date" class="form-control">
                    </div>
                    <div class="col-md-3 mb-2">
                        <input type="date" id="end_date" class="form-control">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select id="branch_id" class="form-control">
                            <option value="">All Branches</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-primary w-100" id="filter">Search</button>
                    </div>
                </div>

                <!-- Table -->
                <div class="col-lg-12">
                    <div class="table-responsive rounded mb-3">
                        <table class="table table-striped" id="reportTable" style="width:100%">
                            <thead class="bg-white text-uppercase">
                                <tr>
                                    <th>Date</th>
                                    <th>Branch Name</th>
                                    <th>Total Transaction</th>
                                    <th>Total Items</th>
                                    <th>Total Sales (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- Wrapper End -->

    <script>
        $(document).ready(function() {
            // Set today's date by default
            let today = new Date().toISOString().substr(0, 10);
            $('#start_date').val(today);
            $('#end_date').val(today);

            // Initialize DataTable
            var table = $('#reportTable').DataTable({
                processing: true,
                serverSide: true, // <-- change to true
                ajax: {
                    url: '{{ route('sales.branch.sales.report') }}',
                    data: function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.branch_id = $('#branch_id').val();
                    }
                },
                columns: [{
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'branch_name',
                        name: 'branch_name'
                    },
                    {
                        data: 'total_orders',
                        name: 'total_orders'
                    },
                    {
                        data: 'total_items',
                        name: 'total_items'
                    },
                    {
                        data: 'total_sales',
                        name: 'total_sales'
                    }
                ],
                pageLength: 10, // Optional: default rows per page
            });


            // Reload table on button click
            $('#filter').click(function() {
                table.ajax.reload();
            });
        });
    </script>
@endsection
