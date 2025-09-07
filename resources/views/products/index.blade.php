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

    <!-- Wrapper End-->

    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // If already present (back nav), reset it
            if ($.fn.DataTable.isDataTable('#groups_table')) {
                $('#groups_table').DataTable().clear().destroy();
            }

            const table = $('#groups_table').DataTable({
                pageLength: 10,
                responsive: true,
                processing: true,
                ordering: true,
                bLengthChange: true,
                serverSide: true,

                ajax: {
                    url: "{{ route('accounting.groups.data') }}", // POST endpoint returning JSON
                    type: "POST",
                    data: function(d) {
                        // Send filters (if you have these inputs in your toolbar)
                        d.parent_id = $('#parentSearch').val() || '';
                        d.nature = $('#natureSearch').val() || '';
                        d.affects = $('#affectsSearch').val() || ''; // yes/no/blank
                        // Add more if needed
                    }
                },

                // columns MUST match controller response keys
                aoColumns: [{ // Sr No like your pattern
                        data: null,
                        name: 'sr_no',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'nature',
                        name: 'nature'
                    },
                    {
                        data: 'affects_gross',
                        name: 'affects_gross',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'parent_name',
                        name: 'parent_name'
                    },
                    {
                        data: 'sort_order',
                        name: 'sort_order'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],

                // widths (optional)
                columnDefs: [{
                        width: "5%",
                        targets: 0
                    },
                    {
                        width: "20%",
                        targets: 1
                    },
                    {
                        width: "12%",
                        targets: 2
                    },
                    {
                        width: "12%",
                        targets: 3
                    },
                    {
                        width: "18%",
                        targets: 4
                    },
                    {
                        width: "8%",
                        targets: 5
                    },
                    {
                        width: "15%",
                        targets: 6
                    },
                    {
                        width: "10%",
                        targets: 7
                    }
                ],

                autoWidth: false,
                order: [
                    [6, 'desc']
                ], // default sort by created_at DESC

                // same toolbar layout you like
                dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",

                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    ['10 rows', '25 rows', '50 rows', '100 rows', 'All']
                ],

                buttons: [{
                        extend: 'excelHtml5',
                        className: 'btn btn-outline-success btn-sm me-2',
                        title: 'Account Groups',
                        filename: 'account_groups_excel',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-outline-danger btn-sm',
                        title: 'Account Groups',
                        filename: 'account_groups_pdf',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ]
            });

            // Filter listeners -> reload table (no paging reset)
            $(document).on('change', '#parentSearch, #natureSearch, #affectsSearch', function() {
                table.ajax.reload(null, false);
            });

            // DELETE (SweetAlert2 + AJAX) — expects your actions to render a .btn-delete with data-id
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                const id = $(this).data('id');

                Swal.fire({
                    title: "Are you sure?",
                    text: "This group will be permanently deleted.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "DELETE",
                            url: "{{ url('/accounting/groups/delete') }}/" + id,
                            success: function(response) {
                                Swal.fire("Deleted!", "The group has been deleted.",
                                    "success").then(() => {
                                    table.ajax.reload(null, false);
                                });
                            },
                            error: function(xhr) {
                                Swal.fire("Error!", xhr.responseJSON?.message ||
                                    "Something went wrong.", "error");
                            }
                        });
                    }
                });
            });

            // Optional: inline update modal (example) — adapt fields to your use-case
            $('#groupUpdateForm').on('submit', function(e) {
                e.preventDefault();

                // clear errors (if any)
                $('#name_error').text('');
                $('#sort_error').text('');

                const payload = {
                    _token: $('input[name="_token"]').val(),
                    id: $('#edit_group_id').val(),
                    name: $('#edit_group_name').val(),
                    sort_order: $('#edit_sort_order').val(),
                };

                $.ajax({
                    type: "POST",
                    url: "{{ route('accounting.groups.update.inline') }}", // create this route if you want inline updates
                    data: payload,
                    success: function(res) {
                        Swal.fire('Saved', res.message || 'Group updated', 'success');
                        $('#groupEditModal').modal('hide');
                        $('#groupUpdateForm')[0].reset();
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errs = xhr.responseJSON.errors || {};
                            if (errs.name) $('#name_error').text(errs.name[0]);
                            if (errs.sort_order) $('#sort_error').text(errs.sort_order[0]);
                        } else {
                            Swal.fire('Error', 'Unexpected error', 'error');
                        }
                    }
                });
            });

        });

        // Also allow action buttons to call directly
        function delete_group(id) {
            $('.btn-delete[data-id="' + id + '"]').trigger('click');
        }

        function open_group_edit(id, name, sort) {
            $('#edit_group_id').val(id);
            $('#edit_group_name').val(name);
            $('#edit_sort_order').val(sort);
            $('#groupEditModal').modal('show');
        }
    </script>
@endsection
