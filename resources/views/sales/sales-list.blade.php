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
                <h1>Trasaction List</h1>
                <div class="col-lg-12">
                    <div class="table-responsive rounded mb-3">
                        <table class="table data-tables table-striped" id="invoice_table">



                            <thead class="bg-white text-uppercase">

                                <tr class="ligth ligth-data">
                                    <th>Trasaction #</th>
                                    <th>Status</th>
                                    <th>Commission Amount</th>
                                    <th>Party Amount</th>
                                    <th>Sub Total</th>
                                    <th>Total</th>
                                    <th>Item Count</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody class="ligth-body">
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" style="text-align:right">Total:</th>
                                    <th id="commission_total"></th>
                                    <th id="party_total"></th>
                                    <th id="sub_total_total"></th>
                                    <th id="grand_total"></th>
                                    <th id="item_count_total"></th>
                                    <th></th>
                                </tr>
                            </tfoot>

                        </table>
                        </
                    </div>
                </div>
                <!-- Page end  -->
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

                $('#invoice_table').DataTable().clear().destroy();

                var table = $('#invoice_table').DataTable({
                    pagelength: 10,
                    responsive: true,
                    processing: true,
                    ordering: true,
                    bLengthChange: true,
                    serverSide: true,

                    "ajax": {
                        "url": '{{ url('sales/get-data') }}',
                        "type": "post",
                        data: function(d) {
                            d.store_id = $('#storeSearch').val(); // pass department value
                        }
                    },
                    aoColumns: [{
                            data: 'invoice_number',
                            name: 'invoice_number'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'commission_amount',
                            name: 'commission_amount',
                            render: function(data) {
                                return '₹' + data;
                            }
                        },
                        {
                            data: 'party_amount',
                            name: 'party_amount',
                            render: function(data) {
                                return '₹' + data;
                            }
                        },
                        {
                            data: 'sub_total',
                            name: 'sub_total',
                            render: function(data) {
                                return '₹' + data;
                            }
                        },
                        {
                            data: 'total',
                            name: 'total',
                            render: function(data) {
                                return '₹' + data;
                            }
                        },
                        {
                            data: 'items_count',
                            name: 'items_count'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        }
                    ],

                    aoColumnDefs: [{
                        bSortable: false,
                        aTargets: [2, 3, 4, 5] // make "action" column unsortable
                    }],
                    columnDefs: [{
                            width: "10%",
                            targets: 0
                        }, // set width of column 0
                        {
                            width: "10%",
                            targets: 1
                        }, // set width of column 1
                        {
                            width: "5%",
                            targets: 2
                        }, {
                            width: "5%",
                            targets: 3
                        }, {
                            width: "5%",
                            targets: 4
                        }, {
                            width: "10%",
                            targets: 5
                        }
                    ],
                    autoWidth: false,
                    order: [
                        [5, 'desc']
                    ], // 🟢 Sort by created_at DESC by default
                    dom: "Bfrtip",
                    lengthMenu: [
                        [10, 25, 50],
                        ['10 rows', '25 rows', '50 rows', 'All']
                    ],
                    footerCallback: function(row, data, start, end, display) {
                        var api = this.api();

                        function intVal(i) {
                            return typeof i === 'string' ?
                                parseFloat(i.replace(/[\₹,]/g, '')) :
                                typeof i === 'number' ?
                                i : 0;
                        }

                        let commission = 0,
                            party = 0,
                            subtotal = 0,
                            total = 0,
                            item_count = 0;

                        data.forEach(function(row) {
                            commission += intVal(row.commission_amount);
                            party += intVal(row.party_amount);
                            subtotal += intVal(row.sub_total);
                            total += intVal(row.total);
                            item_count += intVal(row.items_count);
                        });

                        // Set values with ₹ symbol
                        $(api.column(2).footer()).html('₹' + commission.toFixed(2));
                        $(api.column(3).footer()).html('₹' + party.toFixed(2));
                        $(api.column(4).footer()).html('₹' + subtotal.toFixed(2));
                        $(api.column(5).footer()).html('₹' + total.toFixed(2));
                        $(api.column(6).footer()).html(item_count);
                    },

                    buttons: ['pageLength']
                });

                $('#storeSearch').on('change', function() {
                    table.draw();
                });

            });

            function delete_store(id) {

                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete it!",
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "delete", // "method" also works
                            url: "{{ url('store/delete') }}/" + id, // Ensure correct Laravel URL
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                id: id
                            },
                            success: function(response) {
                                swal("Deleted!", "The store has been deleted.", "success")
                                    .then(() => location.reload());
                            },
                            error: function(xhr) {
                                swal("Error!", "Something went wrong.", "error");
                            }
                        });
                    }
                });

            }
        </script>
    @endsection
