@extends('layouts.backend.datatable_layouts')
@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Wrapper Start -->

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="row align-items-center mb-3">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                            <div>
                                <h4 class="mb-3">Product List</h4>
                            </div>
                            <div class="ml-auto">
                                <a href="{{ route('products.import') }}" class="btn btn-primary pull-right add-list">
                                    <i class="las la-file-import me-1"></i>Import Product
                                </a>
                                <a href="{{ route('products.create') }}" class="btn btn-primary pull-right add-list ml-2">
                                    <i class="las la-plus me-1"></i>Create New Product
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="col-md-3" style="float: right; margin-bottom: 10px;">
                            <div class="form-group">
                                <select name="subCategorySearch" id="subCategorySearch" class="form-control">
                                    <option value="">All</option>
                                    @foreach ($subcategories as $id => $name)
                                        <option value="{{ $name->id }}">{{ $name->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive rounded">
                            <table class="table table-striped table-bordered nowrap" id="products_table"
                                style="width:100%;">
                                <thead class="bg-white">
                                    <tr class="ligth ligth-data">
                                        <th>Sr No</th> <!-- Added this line -->
                                        <th>
                                            <b>N</b>ame
                                        </th>
                                        <th>Category</th>
                                        {{-- <th>Pack Size</th>
                                        <th>Brand</th> --}}
                                        <th>MRP</th>
                                        <th>Sale Price</th>
                                        <th>Cost Price</th>
                                        <th>Status</th>
                                        <th data-type="date" data-format="YYYY/DD/MM">Created Date</th>
                                        <th data-type="date" data-format="YYYY/DD/MM">Updated Date</th>
                                        <th>Action</th>
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

    @php
        // Calculate tomorrow's date
$minDate = \Carbon\Carbon::today()->addDay()->format('Y-m-d');
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
                                    <input type="text" class="form-control" disabled id="old_price">
                                    <input type="hidden" name="old_price" id="old_price_hidden">
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

    <!-- View Product Modal -->
    <div class="modal fade bd-example-modal-lg" id="productViewModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Product Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>×</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <strong>Name:</strong>
                            <div id="view_name" class="text-muted"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Category:</strong>
                            <div id="view_category" class="text-muted"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Subcategory:</strong>
                            <div id="view_subcategory" class="text-muted"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Pack Size:</strong>
                            <div id="view_size" class="text-muted"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Barcode:</strong>
                            <div id="view_barcode" class="text-muted"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>MRP:</strong>
                            <div id="view_mrp" class="text-muted"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Sell Price:</strong>
                            <div id="view_sell_price" class="text-muted"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Cost Price:</strong>
                            <div id="view_cost_price" class="text-muted"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Discount Price:</strong>
                            <div id="view_discount_price" class="text-muted"></div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <strong>Description:</strong>
                            <div id="view_description" class="text-muted"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Status:</strong>
                            <div id="view_status" class="text-muted"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Created At:</strong>
                            <div id="view_created_at" class="text-muted"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <strong>Updated At:</strong>
                            <div id="view_updated_at" class="text-muted"></div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>

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

            // Initialize DataTable
            var table = $('#products_table').DataTable({
                pageLength: 10,
                responsive: true,
                processing: true,
                ordering: true,
                bLengthChange: true,
                serverSide: true,
                "ajax": {
                    "url": '{{ url('products/get-data') }}',
                    "type": "POST",
                    data: function(d) {
                        // Send subcategory id with the request
                        d.sub_category_id = $('#subCategorySearch').val();
                    }
                },
                aoColumns: [{
                        data: null,
                        name: 'sr_no',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'sub_category',
                        orderable: false
                    },
                    // {
                    //     data: 'size'
                    // },
                    // {
                    //     data: 'brand',
                    //     orderable: false
                    // },
                    {
                        data: 'mrp',
                        orderable: false
                    },
                    {
                        data: 'sell_price',
                        orderable: false
                    },
                    {
                        data: 'cost_price',
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
                ],
                columnDefs: [{
                        width: "20%",
                        targets: 0
                    },
                    {
                        width: "7%",
                        targets: 1
                    },
                    // {
                    //     width: "5%",
                    //     targets: 2
                    // },
                    // {
                    //     width: "5%",
                    //     targets: 3
                    // },
                    // {
                    //     width: "5%",
                    //     targets: 4
                    // },
                    {
                        width: "7%",
                        targets: 5
                    },
                    {
                        width: "7%",
                        targets: 6
                    },
                    {
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
                    [5, 'desc']
                ],
                dom: "Bfrtip",
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
                buttons: [{
                        extend: 'excelHtml5',
                        className: 'btn btn-outline-success btn-sm me-2',
                        title: 'Commission Customer',
                        filename: 'commission_customer_excel',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-outline-danger btn-sm',
                        title: 'Commission Customer',
                        filename: 'commission_customer_pdf',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ]
            });

            // Listen for changes in subCategorySearch dropdown
            $('#subCategorySearch').on('change', function() {
                // Reload DataTable with the new filter value
                table.ajax.reload(null, false);
            });
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
                            if (response.status === 'error') {
                                // If the response contains error status, show the message in Swal
                                Swal.fire("Error!", response.message, "error");
                            } else {
                                // If deletion is successful, show success message
                                Swal.fire("Deleted!", "The product has been deleted.", "success").then(
                                    () => {
                                        $('#products_table').DataTable().ajax.reload(null,
                                            false); // ✅ Only reload DataTable
                                    });
                            }
                        },
                        error: function(xhr, status, error) {
                            // Handle any other errors that happen during the AJAX request (network issues, etc.)
                            Swal.fire("Error!", "Something went wrong. Please try again later.",
                                "error");
                        }
                    });
                }
            });

        }

        function product_price_change(id, sell_price) {

            $('#old_price_hidden').val(sell_price);
            $('#old_price').val(sell_price);
            $('#product_id').val(id);
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var myModal = new bootstrap.Modal(document.getElementById('priceChangeModal'));
                myModal.show();
            } else {
                // For Bootstrap 4 (with jQuery)
                $('#priceChangeModal').modal('show');
            }
            // $('#priceChangeModal').modal('show');
        }

        function statusChange(id, newStatus) {
            Swal.fire({
                title: "Are you sure?",
                text: "Do you want to change the status?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, change it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: "{{ url('products/status-change') }}", // Update this to your route
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id: id,
                            status: newStatus
                        },
                        success: function(response) {
                            Swal.fire("Success!", "Status has been changed.", "success").then(() => {
                                $('#products_table').DataTable().ajax.reload(null,
                                    false); // ✅ Only reload DataTable
                            });
                        },
                        error: function(xhr) {
                            Swal.fire("Error!", "Something went wrong.", "error");
                        }
                    });
                }
            });
        }

        function viewProduct(id) {

            $.ajax({
                url: "/products/view/" + id,
                type: "GET",
                success: function(product) {

                    $("#view_name").text(product.name);
                    $("#view_category").text(product.category ? product.category.name : '-');
                    $("#view_subcategory").text(product.subcategory ? product.subcategory.name : '-');
                    $("#view_size").text(product.size ?? '-');
                    $("#view_barcode").text(product.barcode ?? '-');
                    $("#view_mrp").text(product.mrp);
                    $("#view_sell_price").text(product.sell_price);
                    $("#view_cost_price").text(product.cost_price);
                    $("#view_discount_price").text(product.discount_price ?? '-');
                    $("#view_description").text(product.description ?? '-');
                    $("#view_status").text(product.is_active == 1 ? "Active" : "Inactive");
                    $("#view_created_at").text(product.created_at);
                    $("#view_updated_at").text(product.updated_at);

                    new bootstrap.Modal(document.getElementById('productViewModal')).show();


                }
            });

        }
    </script>
@endsection
