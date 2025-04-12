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
                <h1>Send Stock Request Details</h1>

                @if (session('success'))
                    <p>{{ session('success') }}</p>
                @endif

                <table id="stock-requests-table" class="table datatable table-bordered table-striped">

                    <thead>

                        <tr>
                            <th>Store</th>
                            <th>Requested By</th>
                            <th data-type="date" data-format="YYYY/DD/MM">Requested At</th>
                            <th>Status</th>
                            <th>Actions</th>

                        </tr>
                    </thead>

                    <tbody>
                    </tbody>
                </table>

                <!-- Page end  -->
            </div>
        </div>
    </div>
    <!-- Wrapper End-->

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="approveForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Stock Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="request_id" id="request_id">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Requested Quantity</th>
                                    <th>Edit Quantity</th>
                                </tr>
                            </thead>
                            <tbody id="request-items-body">
                                <!-- Dynamically loaded -->
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).on('click', '.open-approve-modal', function() {
            const id = $(this).data('id');
            $('#request_id').val(id);
            $('#request-items-body').html('<tr><td colspan="3">Loading...</td></tr>');

            $.get('{{ url("stock-requests/popup-details/") }}/' + id, function(res) {
                let rows = '';
                console.log(res,"===res===");
                res.items.forEach(item => {
                    rows += `
                        <tr>
                            <td>${item.product.name}</td>
                            <td>${item.quantity}</td>
                            <td>
                                <input type="number" class="form-control" name="items[${item.id}]" value="${item.quantity}" min="1">
                            </td>
                        </tr>`;
                });
                $('#request-items-body').html(rows);
                $('#approveModal').modal('show');
            });
        });

        $('#approveForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#request_id').val();
            const baseUrl = "{{ url('/stock-requests') }}";


            $.ajax({
                url: `${baseUrl}/${id}/approve`,
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    alert(res.message);
                    $('#approveModal').modal('hide');
                    $('#stockRequestTable').DataTable().ajax.reload(null, false);
                },
                error: function(xhr) {
                    alert('Approval failed');
                }
            });
        });

        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#stock-requests-table').DataTable({
                pagelength: 10,
                responsive: true,
                processing: true,
                ordering: true,
                bLengthChange: true,
                serverSide: true,

                "ajax": {
                    "url": '{{ url('stock/get-send-request-data') }}',
                    "type": "post",
                    "data": function(d) {},
                },
                aoColumns: [

                    {
                        data: 'store'
                    },

                    {
                        data: 'requested_by'
                    },
                    {
                        data: 'requested_at'
                    },
                    {
                        data: 'status'
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
