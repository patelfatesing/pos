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
                        <div class="d-flex gap-2 ms-auto">
                            <a href="{{ route('products.import') }}" class="btn btn-primary add-list">
                                <i class="las la-file-import me-1"></i>Import Product
                            </a>
                            <a href="{{ route('products.create') }}" class="btn btn-primary add-list ml-2">
                                <i class="las la-plus me-1"></i>Create New Product
                            </a>
                        </div>
                    </div>
                </div>


                <div class="col-lg-12">
                    <div class="table-responsive rounded mb-3">
                        <table class="table data-tables table-striped" id="products_table">
                            <thead class="bg-white">
                                <tr class="ligth ligth-data">
                                    <th>
                                        <b>N</b>ame
                                    </th>
                                    <th>Cotegory</th>
                                    <th>Sub Cotegory</th>
                                    <th>Pack Size</th>
                                    <th>Brand</th>
                                    <th>MRP</th>
                                    <th>Sale Price</th>
                                    <th>Status</th>
                                    <th data-type="date" data-format="YYYY/DD/MM">Created Date</th>
                                    <th data-type="date" data-format="YYYY/DD/MM">Updated Date</th>
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

    @php
        // Calculate tomorrow's date
$minDate = \Carbon\Carbon::today()->format('Y-m-d');
    @endphp

    <div class="modal fade bd-example-modal-lg" id="priceChangeModal" tabindex="-1" role="dialog"
        aria-labelledby="priceChangeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="priceUpdateForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="priceChangeModalLabel">Product Price Change</h5>
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
                                    <input type="date" name="changed_at" min="{{ $minDate }}" class="form-control"
                                        id="changed_at">
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
                        data: 'mrp',
                        orderable: false
                    },
                    {
                        data: 'sell_price',
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
                        data: 'updated_at'
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
                columnDefs: [{
                        width: "20%",
                        targets: 0
                    }, // set width of column 0
                    {
                        width: "7%",
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
                        width: "7%",
                        targets: 5
                    }, {
                        width: "7%",
                        targets: 6
                    }, {
                        width: "10%",
                        targets: 7
                    },
                    {
                        width: "5%",
                        targets: 8
                    }
                ],
                autoWidth: false,
                order: [
                    [7, 'desc']
                ], // 🟢 Sort by created_at DESC by default
                dom: "Bfrtip",
                lengthMenu: [
                    [10, 25, 50],
                    ['10 rows', '25 rows', '50 rows', 'All']
                ],
                buttons: ['pageLength']

            });

            $('#priceUpdateForm').on('submit', function(e) {
                e.preventDefault();

                // Clear previous validation errors
                $('#old_price_error').text('');
                $('#new_price_error').text('');
                $('#changed_at_error').text('');

                let formData = {
                    _token: $('input[name="_token"]').val(),
                    product_id: $('#product_id').val(),
                    old_price: $('#old_price').val(),
                    new_price: $('#new_price').val(),
                    changed_at: $('#changed_at').val()
                };

                $.ajax({
                    type: "POST",
                    url: "{{ route('products.updatePrice') }}", // Adjust to match your route name
                    data: formData,
                    success: function(response) {
                        // Show success message
                        alert(response.message); // or use toastr.success()

                        // Close and reset modal
                        $('#priceChangeModal').modal('hide');
                        $('#priceUpdateForm')[0].reset();

                        // Reload DataTable
                        $('#products_table').DataTable().ajax.reload(null, false);
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
                        } else {
                            alert("An unexpected error occurred.");
                        }
                    }
                });
            });

        });

        function delete_product(id) {

            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "post", // "method" also works
                        url: "{{ url('products/delete') }}", // Ensure correct Laravel URL
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id: id
                        },
                        success: function(response) {
                            Swal.fire("Deleted!", "The product has been deleted.", "success").then(
                                () => {
                                    $('#products_table').DataTable().ajax.reload(null,
                                        false); // ✅ Only reload DataTable
                                });
                        },
                        error: function(xhr) {
                            swal("Error!", "Something went wrong.", "error");
                        }
                    });
                }
            });

        }

        function product_price_change(id, sell_price) {
            // alert(id);
            $('#old_price').val(sell_price);
            $('#product_id').val(id);
            $('#priceChangeModal').modal('show');
        }
    </script>
@endsection
