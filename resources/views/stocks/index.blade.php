@extends('layouts.backend.layouts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Wrapper Start -->
    <div class="wrapper">

        <div class="content-page">
            <div class="container-fluid">
                <h1>Stocks</h1>

                <div class="table-responsive rounded mb-3">
                    <table class="table data-tables table-striped" id="products_table">
                        <thead class="bg-white text-uppercase">
                            <tr class="ligth ligth-data">
                                <th>Product</th>
                                <th>Location</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Batch No</th>
                                <th>Expiry Date</th>
                                <th>Low Stock Alert Level</th>
                                <th>Status</th>
                                <th data-type="date" data-format="YYYY/DD/MM">Last updated</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

                <!-- Page end  -->
            </div>
        </div>
    </div>
    <!-- Wrapper End-->

    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#products_table').DataTable({
                pagelength: 10,
                responsive: true,
                processing: true,
                ordering: true,
                bLengthChange: true,
                serverSide: true,

                "ajax": {
                    "url": '{{ url('stock/get-data') }}',
                    "type": "post",
                    "data": function(d) {},
                },
                aoColumns: [

                    {
                        data: 'name'
                    },

                    {
                        data: 'location'
                    },
                    {
                        data: 'quantity'
                    },
                    {
                        data: 'cost_price'
                    },
                    {
                        data: 'batch_no'
                    },
                    {
                        data: 'expiry_date'
                    },
                    {
                        data: 'reorder_level'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'action'
                    }
                    // Define more columns as per your table structure

                ],
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: []
                }],
                dom: "Bfrtip",
                lengthMenu: [
                    [10, 25, 50],
                    ['10 rows', '25 rows', '50 rows', 'All']
                ],
                buttons: ['pageLength']

            });

        });

    </script>
@endsection
