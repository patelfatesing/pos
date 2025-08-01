@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <!-- Page Header -->
                <div class="row align-items-center mb-3">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Demand Order List</h4>
                            </div>
                            <a href="{{ route('demand-order.step1') }}" class="btn btn-primary add-list">
                                <i class="las la-plus mr-3"></i>Add Demand Order
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive rounded">
                            <table class="table table-striped table-bordered nowrap" id="demand_order_tbl"
                                style="width:100%;">
                                <thead class="bg-white">
                                    <tr class="ligth ligth-data">
                                        <th>Sr No</th>
                                        <th>Vendor</th>
                                        <th>Purchase Date</th>
                                        <th>Shipping Date</th>
                                        <th>Total Quantity</th>
                                        <th>Total Sell Price</th>
                                        <th>Sub Category</th>
                                        <th>Status</th>
                                        <th>Action</th>
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
    </div>

    <!-- Modal for PDF Preview -->
    <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">File Preview</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <iframe id="pdfIframe" src="" width="100%" height="600px" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Function to open PDF in modal
        function openPDF(fileUrl) {
            $('#pdfIframe').attr('src', fileUrl);
        }

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Check if DataTable is already initialized and destroy it before re-initializing
            if ($.fn.DataTable.isDataTable('#demand_order_tbl')) {
                $('#demand_order_tbl').DataTable().clear().destroy();
            }

            // Initialize DataTable with server-side processing
            $('#demand_order_tbl').DataTable({
                pageLength: 10,
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ url('demand-order/get-data') }}',
                    type: 'POST',
                },
                columns: [{
                        data: null,
                        name: 'sr_no',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    }, 
                    {
                        data: 'vendor'
                    },
                    {
                        data: 'purchase_date'
                    },
                    {
                        data: 'shipping_date'
                    },
                    {
                        data:'total_quantity'
                    },
                    {
                        data:'total_sell_price'
                    },
                    {
                        data:'sub_category'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [4,5,6,7] // make "action" column unsortable
                }],
                order: [
                    [3, 'desc']
                ], // Sort by status DESC by default
                dom: 'Bfrtip',
                buttons: ['pageLength'],
                lengthMenu: [
                    [10, 25, 50],
                    ['10 rows', '25 rows', '50 rows']
                ]
            });
        });
    </script>
@endsection
