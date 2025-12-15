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
                            <h4 class="mb-3">Categories List</h4>
                        </div>
                        <button class="btn btn-primary add-list" data-toggle="modal" data-target="#addCategoryModal">
                            <i class="las la-plus mr-3"></i>Create New Category
                        </button>
                    </div>
                </div>
                <div class="table-responsive rounded mb-3">
                    <table class="table data-tables table-striped" id="categories_tbl">
                        <thead class="bg-white text-uppercase">
                            <tr class="ligth ligth-data">

                                <th>
                                    <b>N</b>ame
                                </th>
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

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Add Category</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>×</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="addCategoryForm">
                        @csrf

                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter Name">
                            <span class="text-danger error-name"></span>
                        </div>

                        <button type="submit" class="btn btn-primary">Add Category</button>
                        <button type="reset" class="btn btn-danger">Reset</button>

                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>×</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form id="editCategoryForm">
                        @csrf
                        <input type="hidden" name="id" id="edit_id">

                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="name" id="edit_name" class="form-control">
                            <span class="text-danger error-edit-name"></span>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Category</button>
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

        $('#categories_tbl').DataTable().clear().destroy();

        $('#categories_tbl').DataTable({
            pagelength: 10,
            responsive: true,
            processing: true,
            ordering: true,
            bLengthChange: true,
            serverSide: true,

            "ajax": {
                "url": '{{ url('categories/get-data') }}',
                "type": "post",
                "data": function(d) {},
            },
            aoColumns: [

                {
                    data: 'name'
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
                aTargets: [3] // make "action" column unsortable
            }],
            order: [
                [2, 'desc']
            ], // 🟢 Sort by created_at DESC by default
            dom: "Bfrtip",
            lengthMenu: [
                [10, 25, 50],
                ['10 rows', '25 rows', '50 rows', 'All']
            ],
            buttons: ['pageLength']

        });

    });

    function delete_category(id) {

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

    // Submit add form using AJAX
    $("#addCategoryForm").on("submit", function(e) {
        e.preventDefault();

        $(".error-name").text(""); // clear errors

        $.ajax({
            url: "{{ route('categories.store') }}",
            method: "POST",
            data: $(this).serialize(),
            success: function(res) {
                Swal.fire("Success!", "Category added successfully!", "success");

                $("#addCategoryModal").modal("hide");
                $("#addCategoryForm")[0].reset();

                $("#categories_tbl").DataTable().ajax.reload(); // refresh table
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    if (errors.name) {
                        $(".error-name").text(errors.name[0]);
                    }
                } else {
                    Swal.fire("Error", "Something went wrong!", "error");
                }
            }
        });
    });

    function editCategory(id) {

        $.ajax({
            url: "/categories/edit/" + id + "/",
            method: "GET",
            success: function(res) {
                $("#edit_id").val(res.id);
                $("#edit_name").val(res.name);

                $(".error-edit-name").text("");

                $("#editCategoryModal").modal("show");
            }
        });
    }

    $("#editCategoryForm").on("submit", function(e) {
        e.preventDefault();

        let id = $("#edit_id").val();

        $(".error-edit-name").text("");

        $.ajax({
            url: "/categories/update/" + id,
            method: "POST",
            data: $(this).serialize(),
            success: function(res) {
                Swal.fire("Updated!", "Category updated successfully!", "success");

                $("#editCategoryModal").modal("hide");

                $("#categories_tbl").DataTable().ajax.reload();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    if (errors.name) {
                        $(".error-edit-name").text(errors.name[0]);
                    }
                }
            }
        });
    });
</script>
@endsection
