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
                            <h4 class="mb-3">Sub Categories List</h4>
                        </div>
                        <button class="btn btn-primary add-list" data-toggle="modal" data-target="#addSubCategoryModal">
                            <i class="las la-plus mr-3"></i>Create New Sub Category
                        </button>
                    </div>
                </div>
                <div class="table-responsive rounded mb-3">
                    <table class="table data-tables table-striped" id="subcategories_tbl">
                        <thead class="bg-white text-uppercase">
                            <tr class="ligth ligth-data">
                                <th>
                                    <b>N</b>ame
                                </th>
                                <th>Main Category</th>
                                <th>Status</th>
                                <th data-type="date" data-format="YYYY/DD/MM">Created Date</th>
                                <th data-type="date" data-format="YYYY/DD/MM">Updated Date</th>
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

    <!-- Add Sub Category Modal -->
    <div class="modal fade" id="addSubCategoryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Add Sub Category</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>×</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="addSubCategoryForm">
                        @csrf

                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter Name">
                            <span class="text-danger error-name"></span>
                        </div>

                        <div class="form-group">
                            <label>Main Category *</label>
                            <select name="category_id" class="form-control">
                                <option value="" disabled selected>Select Main Category</option>

                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach

                            </select>
                            <span class="text-danger error-category_id"></span>
                        </div>

                        <button type="submit" class="btn btn-primary">Add Sub Category</button>
                        <button type="reset" class="btn btn-danger">Reset</button>

                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Edit Sub Category Modal -->
    <div class="modal fade" id="editSubCategoryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Sub Category</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>×</span>
                    </button>
                </div>

                <div class="modal-body">

                    <form id="editSubCategoryForm">
                        @csrf
                        <input type="hidden" name="id" id="edit_id">

                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" id="edit_name" name="name" class="form-control">
                            <span class="text-danger error-edit-name"></span>
                        </div>

                        <div class="form-group">
                            <label>Main Category *</label>
                            <select id="edit_category_id" name="category_id" class="form-control">
                                <option value="" disabled>Select Main Category</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger error-edit-category_id"></span>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Sub Category</button>

                    </form>

                </div>

            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#subcategories_tbl').DataTable().clear().destroy();

            $('#subcategories_tbl').DataTable({
                pagelength: 10,
                responsive: true,
                processing: true,
                ordering: true,
                bLengthChange: true,
                serverSide: true,

                "ajax": {
                    "url": '{{ url('subcategories/get-data') }}',
                    "type": "post",
                    "data": function(d) {},
                },
                aoColumns: [

                    {
                        data: 'name'
                    },
                    {
                        data: 'category_name'
                    },
                    {
                        data: 'is_active'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'updated_at'
                    },
                    {
                        data: 'action'
                    }
                    // Define more columns as per your table structure

                ],
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [2, 4] // make "action" column unsortable
                }],
                order: [
                    [3, 'desc']
                ], // 🟢 Sort by created_at DESC by default
                dom: "Bfrtip",
                lengthMenu: [
                    [10, 25, 50],
                    ['10 rows', '25 rows', '50 rows', 'All']
                ],
                buttons: ['pageLength']

            });

        });

        $("#addSubCategoryForm").on("submit", function(e) {
            e.preventDefault();

            $(".error-name").text("");
            $(".error-category_id").text("");

            $.ajax({
                url: "{{ route('subcategories.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {

                    Swal.fire("Success!", "Sub Category created!", "success");

                    $("#addSubCategoryModal").modal("hide");
                    $("#addSubCategoryForm")[0].reset();

                    $("#subcategories_tbl").DataTable().ajax.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;

                        if (errors.name) {
                            $(".error-name").text(errors.name[0]);
                        }

                        if (errors.category_id) {
                            $(".error-category_id").text(errors.category_id[0]);
                        }
                    } else {
                        Swal.fire("Error!", "Something went wrong.", "error");
                    }
                }
            });
        });

        function editSubCategory(id) {
            $.ajax({
                url: "/subcategories/edit/" + id + "",
                method: "GET",
                success: function(res) {
                    $("#edit_id").val(res.id);
                    $("#edit_name").val(res.name);
                    $("#edit_category_id").val(res.category_id);

                    $(".error-edit-name").text("");
                    $(".error-edit-category_id").text("");

                    $("#editSubCategoryModal").modal("show");
                }
            });
        }

        $("#editSubCategoryForm").on("submit", function(e) {
            e.preventDefault();

            $(".error-edit-name").text("");
            $(".error-edit-category_id").text("");

            $.ajax({
                url: "/subcategories/update",
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {

                    Swal.fire("Updated!", "Sub Category updated!", "success");

                    $("#editSubCategoryModal").modal("hide");

                    $("#subcategories_tbl").DataTable().ajax.reload();
                },

                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;

                        $(".error-edit-name").text(errors.name ?? "");
                        $(".error-edit-category_id").text(errors.category_id ?? "");
                    }
                }
            });
        });

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
                        url: "{{ url('subcategories/status-change') }}", // Update this to your route
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id: id,
                            status: newStatus
                        },
                        success: function(response) {
                            Swal.fire("Success!", "Status has been changed.", "success").then(() => {
                                $('#subcategories_tbl').DataTable().ajax.reload(null,
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

        function delete_sub_cat(id) {

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
                        url: "{{ url('subcategories/delete') }}", // Ensure correct Laravel URL
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
