@extends('layouts.backend.layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="row">
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
                    <div class="col-lg-12">
                        <div class="table-responsive rounded mb-3">
                            <table class="table data-tables table-striped">
                                <thead>
                                    <tr>
                                        <th>Vendor</th>
                                        <th>Purchase Date</th>
                                        <th>Shipping Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($demandOrders as $order)
                                        <tr>
                                            <td>{{ $order->vendor->name }}</td>
                                            <td>{{ $order->purchase_date }}</td>
                                            <td>{{ $order->shipping_date }}</td>
                                            <td>{{ ucfirst($order->status) }}</td>
                                            <td>
                                                <button class="btn btn-warning btn-sm"
                                                    onclick="openPDF('{{ asset('storage/demand/' . $order->file_name) }}')"
                                                    data-toggle="modal" data-target="#pdfModal">
                                                    <i class="las la-file-pdf"></i> View File
                                                    </button>
                                                    <a href="{{ route('demand-order.view', $order->id) }}"
                                                        class="btn btn-info btn-sm">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Wrapper End -->
    <!-- Modal -->
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
        function openPDF(fileUrl) {
            $('#pdfIframe').attr('src', fileUrl);
        }

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if ($.fn.DataTable.isDataTable('#purchase_table')) {
                $('#purchase_table').DataTable().clear().destroy();
            }

            $('#purchase_table').DataTable({
                pageLength: 10,
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ url('purchase/get-data') }}',
                    type: 'POST',
                },
                columns: [{
                        data: 'bill_no'
                    },
                    {
                        data: 'party_name'
                    },
                    {
                        data: 'total'
                    },
                    {
                        data: 'total_amount'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],

                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [4] // make "action" column unsortable
                }],
                order: [
                    [3, 'desc']
                ], // 🟢 Sort by created_at DESC by default
                dom: 'Bfrtip',
                buttons: ['pageLength'],
                lengthMenu: [
                    [10, 25, 50],
                    ['10 rows', '25 rows', '50 rows']
                ]
            });
        });

        function delete_vendor(id) {
            Swal.fire({
                title: "Are you sure?",
                text: "This action cannot be undone!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE",
                        url: "{{ route('vendor.destroy', ':id') }}".replace(':id', id),
                        success: function(response) {
                            $('#vendor_table').DataTable().ajax.reload();
                            Swal.fire("Deleted!", "The party user has been deleted.", "success");
                        },
                        error: function(xhr) {
                            Swal.fire("Error!", "An error occurred while deleting.", "error");
                        }
                    });
                }
            });
        }
    </script>
@endsection
