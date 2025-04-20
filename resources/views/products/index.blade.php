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
                <div class="col-lg-12">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                        <div>
                            <h4 class="mb-3">Product List</h4>
                        </div>
                        <a href="{{ route('products.create') }}" class="btn btn-primary add-list">
                            <i class="las la-plus mr-3"></i>Create New Product
                        </a>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="table-responsive rounded mb-3">
                        <table class="table data-tables table-striped" id="products_table">

                            <thead class="bg-white text-uppercase">
                                <tr class="ligth ligth-data">
                                    <th>
                                        <b>N</b>ame
                                    </th>
                                    <th>Cotegory</th>
                                    <th>Sub Cotegory</th>
                                    <th>Pack Size</th>
                                    <th>Brand</th>
                                    <th>sku</th>
                                    <th>Status</th>
                                    <th data-type="date" data-format="YYYY/DD/MM">Created Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="ligth-body">
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Page end  -->
            </div>
        </div>
    </div>

    <div class="modal fade bd-example-modal-lg" id="approveModal" tabindex="-1" role="dialog"
        aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="priceUpdateForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel">Product Price Change</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="product_id" id="product_id" value="">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Old Price </label>
                                    <input type="text" name="old_price" class="form-control" id="old_price">
                                    <span class="text-danger" id="old_price_error"></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>New Price</label>
                                    <input type="text" name="new_price" class="form-control" id="new_price">
                                    <span class="text-danger" id="new_price_error"></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Price Apply Date</label>
                                    <input type="date" name="changed_at" class="form-control" id="changed_at">
                                    <span class="text-danger" id="changed_at_error"></span>
                                </div>
                            </div>
                        </div>

                        <span class="mt-2 badge badge-pill border border-secondary text-secondary">
                            {{ __('messages.change_date_msg') }}
                        </span>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
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

            $('#products_table').DataTable().clear().destroy();

            $('#products_table').DataTable({
                pagelength: 10,
                responsive: true,
                processing: true,
                ordering: true,
                bLengthChange: true,
                serverSide: true,

                "ajax": {
                    "url": '{{ url('products/get-data') }}',
                    "type": "post",
                    "data": function(d) {},
                },
                aoColumns: [

                    {
                        data: 'name'
                    },

                    {
                        data: 'category',
                        orderable: false
                    },
                    {
                        data: 'sub_category',
                        orderable: false
                    },
                    {
                        data: 'size'
                    },
                    {
                        data: 'brand',
                        orderable: false
                    },
                    {
                        data: 'sku',
                        orderable: false
                    },

                    {
                        data: 'is_active',
                        orderable: false
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'action',
                        orderable: false
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

        function product_price_change(id) {

            $('#approveModal').modal('show');
        }

        $('#priceUpdateForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous errors
            $('#old_price_error').text('');
            $('#new_price_error').text('');
            $('#changed_at_error').text('');

            $.ajax({
                url: "{{ route('products.updatePrice') }}", // define this route
                method: 'POST',
                data: $(this).serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                success: function(response) {
                    alert(response.message);
                    $('#approveModal').modal('hide');
                    location.reload(); // or update the DOM without reloading
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        if (errors.old_price) {
                            $('#old_price_error').text(errors.old_price[0]);
                        }
                        if (errors.new_price) {
                            $('#new_price_error').text(errors.new_price[0]);
                        }
                        if (errors.changed_at) {
                            $('#changed_at_error').text(errors.changed_at[0]);
                        }
                    }
                }
            });
        });
    </script>
@endsection
