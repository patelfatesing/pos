@extends('layouts.backend.datatable_layouts')

@section('page-content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    body {
        background-color: #f1f7fc;
    }

    /* Top Section Bar */
    .top-header-section {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .header-main-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    /* POS-style Card Design */
    .pos-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        margin-bottom: 20px;
    }

    .pos-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        border-bottom: 1px solid #f1f5f9;
        flex-wrap: nowrap;
        gap: 8px;
    }

    .header-actions-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .header-actions-right {
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: flex-end;
    }

    /* Action & Controls Styling */
    .btn-pos-primary {
        background-color: #009ef7;
        border-color: #009ef7;
        color: #ffffff;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
        height: 31px;
    }
    .btn-pos-primary:hover {
        background-color: #0086d1;
        border-color: #0086d1;
        color: #ffffff;
    }

    div.dt-buttons>.dt-button, div.dt-buttons>div.dt-button-split .dt-button {
        padding: 2px;
        margin-bottom: 0px !important;
    }

    /* Table Styling */
    .table-pos {
        width: 100% !important;
        margin-bottom: 0 !important;
        border-collapse: separate;
        border-spacing: 0;
    }
    .table-pos thead th {
        background-color: #f1f5f9;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 700;
        padding: 10px 12px;
        border-bottom: 1px solid #e2e8f0;
        border-top: none;
        vertical-align: middle;
    }
    .table-pos tbody td {
        padding: 9px 12px;
        vertical-align: middle;
        font-size: 0.84rem;
        color: #334155;
        border-top: 1px solid #f1f5f9;
    }
    .table-pos tbody tr:nth-of-type(even) {
        background-color: #f8fafc;
    }
    .table-pos tbody tr:hover {
        background-color: #f1f5f9;
    }

    /* DataTables Inputs in Header */
    .header-actions-right .dt-select-len {
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 3px 6px;
        font-size: 0.82rem;
        height: 31px;
        width: 65px;
        color: #475569;
        background-color: #fff;
    }
    .header-actions-right .dt-search-box {
        position: relative;
        margin: 0;
    }
    .header-actions-right .dt-search-box input {
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 4px 28px 4px 10px;
        font-size: 0.82rem;
        height: 31px;
        width: 190px;
        color: #334155;
        background: #fff;
        outline: none;
    }
    .header-actions-right .dt-search-box::after {
        content: "\f002";
        font-family: "FontAwesome", "Font Awesome 5 Free", "Line Awesome Free";
        font-weight: 900;
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.75rem;
        pointer-events: none;
    }

    .table-responsive {
        padding-left: 5px;
        padding-right: 5px;
    }

    /* Reduce gap between two columns */
    .content-page .row {
        margin-left: -5px;
        margin-right: -5px;
    }
    .content-page .row > [class*="col-"] {
        padding-left: 5px;
        padding-right: 5px;
    }
    .container-fluid {
        padding-right: 10px;
        padding-left: 10px;
    }
</style>

<div class="content-page">
    <div class="container-fluid">

        <!-- Top Header Titles & Pack Size Button -->
        <div class="top-header-section">
            <div class="col-5 text-center">
                <h5 class="header-main-title">Main Category</h5>
            </div>
            <div class="col-2 text-center px-0">
                <button type="button" class="btn btn-sm btn-info px-3 py-1 font-weight-bold shadow-sm" style="border-radius: 20px;" data-toggle="modal" data-target="#packSizeModalPopup">
                    <i class="las la-box mr-1"></i> Pack Size
                </button>
            </div>
            <div class="col-5 text-center">
                <h5 class="header-main-title">Sub Category</h5>
            </div>
        </div>

        <div class="row">
            <!-- Left: Main Category -->
            <div class="col-lg-6 col-md-12">
                <div class="pos-card">
                    <div class="pos-card-header">
                        <div class="header-actions-left" id="cat_export_wrapper"></div>
                        
                        <div class="header-actions-right" id="cat_controls_wrapper">
                            @if (auth()->user()->role_id == 1 || canCreate(auth()->user()->role_id, 'categories-create'))
                                <button class="btn btn-pos-primary" data-toggle="modal" data-target="#addCategoryModal">
                                    <i class="las la-plus"></i> Create New
                                </button>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-pos w-100" id="categories_tbl">
                                <thead>
                                    <tr>
                                        <th>Name</th>
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

            <!-- Right: Sub Category -->
            <div class="col-lg-6 col-md-12">
                <div class="pos-card">
                    <div class="pos-card-header">
                        <div class="header-actions-left" id="subcat_export_wrapper"></div>

                        <div class="header-actions-right" id="subcat_controls_wrapper">
                            @if (auth()->user()->role_id == 1 || canCreate(auth()->user()->role_id, 'sub-categories-create'))
                                <button class="btn btn-pos-primary" data-toggle="modal" data-target="#addSubCategoryModal">
                                    <i class="las la-plus"></i> Create New
                                </button>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-pos w-100" id="subcategories_tbl">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Main Category</th>
                                        <th>Status</th>
                                        <th>Created Date</th>
                                        <th>Updated Date</th>
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
</div>

<!-- Add Category Modal (Old File Design) -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header card-header d-flex flex-wrap align-items-center justify-content-between">
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
                    <button type="submit" class="btn btn-success">Add Category</button>
                    <button type="reset" class="btn btn-danger">Reset</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal (Old File Design) -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header card-header d-flex flex-wrap align-items-center justify-content-between">
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
                    <button type="submit" class="btn btn-success">Update Category</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Sub Category Modal (Old File Design) -->
<div class="modal fade" id="addSubCategoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header card-header d-flex flex-wrap align-items-center justify-content-between">
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
                        <input type="text" name="name" class="form-control" placeholder="Enter Sub Category Name">
                        <span class="text-danger error-name-sub"></span>
                    </div>
                    <div class="form-group">
                        <label>Main Category *</label>
                        <select name="category_id" class="form-control">
                            <option value="" disabled selected>Select Main Category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <span class="text-danger error-category_id-sub"></span>
                    </div>
                    <button type="submit" class="btn btn-success">Add Sub Category</button>
                    <button type="reset" class="btn btn-danger">Reset</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Sub Category Modal (Old File Design) -->
<div class="modal fade" id="editSubCategoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header card-header d-flex flex-wrap align-items-center justify-content-between">
                <h5 class="modal-title">Edit Sub Category</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>×</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editSubCategoryForm">
                    @csrf
                    <input type="hidden" name="id" id="edit_id_sub">
                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" id="edit_name_sub" name="name" class="form-control">
                        <span class="text-danger error-edit-name-sub"></span>
                    </div>
                    <div class="form-group">
                        <label>Main Category *</label>
                        <select id="edit_category_id_sub" name="category_id" class="form-control">
                            <option value="" disabled>Select Main Category</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <span class="text-danger error-edit-category_id-sub"></span>
                    </div>
                    <button type="submit" class="btn btn-success">Update Sub Category</button>
                </form>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    var pdfLogo = "";

    function getBase64Image(url, callback) {
        var img = new Image();
        img.crossOrigin = "Anonymous";
        img.onload = function() {
            var canvas = document.createElement("canvas");
            canvas.width = this.width;
            canvas.height = this.height;
            var ctx = canvas.getContext("2d");
            ctx.drawImage(this, 0, 0);
            var dataURL = canvas.toDataURL("image/png");
            callback(dataURL);
        };
        img.src = url;
    }

    getBase64Image("https://liquorhub.in/assets/images/logo.png", function(base64) {
        pdfLogo = base64;
    });

    $(document).ready(function() {
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        // 1. Categories Table
        var catTable = $('#categories_tbl').DataTable({
            pageLength: 10,
            responsive: true,
            processing: true,
            serverSide: true,
            ordering: true,
            ajax: { url: '{{ url('categories/get-data') }}', type: "post" },
            dom: "<'row'<'col-12'B>>rt<'row align-items-center px-3 py-2'<'col-sm-6'i><'col-sm-6 d-flex justify-content-end'p>>",
            buttons: [{
                extend: 'collection',
                text: '<i class="fa fa-download"></i>',
                className: 'btn btn-outline-secondary btn-sm',
                autoClose: true,
                buttons: [
                    { 
                        extend: 'excelHtml5', 
                        text: '<i class="fa fa-file-excel-o mr-1"></i> Excel', 
                        title: 'Categories List', 
                        filename: 'categories_list', 
                        exportOptions: { columns: ':visible' } 
                    },
                    { 
                        extend: 'pdfHtml5', 
                        text: '<i class="fa fa-file-pdf-o mr-1"></i> PDF', 
                        filename: 'categories_list', 
                        orientation: 'landscape', 
                        pageSize: 'A4',
                        exportOptions: { columns: [0, 1, 2] },
                        customize: function(doc) {
                            doc.content.splice(0, 1);
                            doc.styles.tableHeader.alignment = 'center';
                            
                            // Build logo column array
                            var logoCol = [];
                            if (pdfLogo && pdfLogo.length > 0) {
                                logoCol.push({ image: pdfLogo, width: 30 });
                            }
                            logoCol.push({ text: 'LiquorHub', fontSize: 11, bold: true, margin: [5, 8, 0, 0] });

                            doc.content.unshift({
                                margin: [0, 0, 0, 12],
                                columns: [
                                    {
                                        width: '33%',
                                        columns: logoCol
                                    },
                                    { width: '34%', text: 'Categories List', alignment: 'center', fontSize: 16, bold: true, margin: [0, 8, 0, 0] },
                                    { width: '33%', text: 'Generated: ' + new Date().toLocaleString(), alignment: 'right', fontSize: 9, margin: [0, 8, 0, 0] }
                                ]
                            });
                            doc.styles.tableHeader.fontSize = 10;
                            doc.defaultStyle.fontSize = 9;
                        }
                    }
                ]
            }],
            aoColumns: [
                { data: 'name' },
                { data: 'is_active' },
                { data: 'created_at' },
                { data: 'updated_at' },
                { data: 'action' }
            ],
            aoColumnDefs: [{
                bSortable: false,
                aTargets: [0, 1, 4]
            }],
            order: [[2, 'desc']],
            initComplete: function () {
                var dtButtons = catTable.buttons().container();
                $('#cat_export_wrapper').append(dtButtons);

                var lengthMenu = $('<select class="dt-select-len"><option value="10">10</option><option value="25">25</option><option value="50">50</option></select>');
                var searchInput = $('<div class="dt-search-box"><input type="search" placeholder="Search List..."></div>');

                lengthMenu.on('change', function() { catTable.page.len($(this).val()).draw(); });
                searchInput.find('input').on('keyup', function() { catTable.search($(this).val()).draw(); });

                $('#cat_controls_wrapper').prepend(searchInput).prepend(lengthMenu);
            }
        });

        // 2. Sub Categories Table
        var subCatTable = $('#subcategories_tbl').DataTable({
            pageLength: 10,
            responsive: true,
            processing: true,
            serverSide: true,
            ordering: true,
            ajax: { url: '{{ url('subcategories/get-data') }}', type: "post" },
            dom: "<'d-none'lfB>rt<'row align-items-center px-3 py-2'<'col-sm-6'i><'col-sm-6 d-flex justify-content-end'p>>",
            buttons: [{
                extend: 'collection',
                text: '<i class="fa fa-download"></i>',
                className: 'btn btn-outline-secondary btn-sm',
                autoClose: true,
                buttons: [
                    { extend: 'excelHtml5', text: '<i class="fa fa-file-excel-o mr-1"></i> Excel', title: 'Sub Categories List' },
                    { extend: 'pdfHtml5', text: '<i class="fa fa-file-pdf-o mr-1"></i> PDF', title: 'Sub Categories List' }
                ]
            }],
            aoColumns: [
                { data: 'name' },
                { data: 'category_name' },
                { data: 'is_active' },
                { data: 'created_at' },
                { data: 'updated_at' },
                { data: 'action', orderable: false, searchable: false }
            ],
            order: [[3, 'desc']],
            initComplete: function () {
                var dtButtons = subCatTable.buttons().container();
                $('#subcat_export_wrapper').append(dtButtons);

                var lengthMenu = $('<select class="dt-select-len"><option value="10">10</option><option value="25">25</option><option value="50">50</option></select>');
                var searchInput = $('<div class="dt-search-box"><input type="search" placeholder="Search List..."></div>');

                lengthMenu.on('change', function() { subCatTable.page.len($(this).val()).draw(); });
                searchInput.find('input').on('keyup', function() { subCatTable.search($(this).val()).draw(); });

                $('#subcat_controls_wrapper').prepend(searchInput).prepend(lengthMenu);
            }
        });
    });

    // Delete Main Category
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
                    type: "delete",
                    url: "{{ url('store/delete') }}/" + id,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: { id: id },
                    success: function(response) {
                        Swal.fire("Deleted!", "The store has been deleted.", "success")
                            .then(() => $('#categories_tbl').DataTable().ajax.reload());
                    },
                    error: function(xhr) {
                        Swal.fire("Error!", "Something went wrong.", "error");
                    }
                });
            }
        });
    }

    // Add Category AJAX
    $("#addCategoryForm").on("submit", function(e) {
        e.preventDefault();
        $(".error-name").text("");
        $.ajax({
            url: "{{ route('categories.store') }}",
            method: "POST",
            data: $(this).serialize(),
            success: function(res) {
                Swal.fire("Success!", "Category added successfully!", "success");
                $("#addCategoryModal").modal("hide");
                $("#addCategoryForm")[0].reset();
                $("#categories_tbl").DataTable().ajax.reload();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    if (errors.name) $(".error-name").text(errors.name[0]);
                } else {
                    Swal.fire("Error", "Something went wrong!", "error");
                }
            }
        });
    });

    // Edit Category AJAX
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

    // Update Category AJAX
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
                    if (errors.name) $(".error-edit-name").text(errors.name[0]);
                }
            }
        });
    });

    // Add Sub Category AJAX
    $("#addSubCategoryForm").on("submit", function(e) {
        e.preventDefault();
        $(".error-name-sub, .error-category_id-sub").text("");
        $.ajax({
            url: "{{ route('subcategories.store') }}",
            method: "POST",
            data: $(this).serialize(),
            success: function(res) {
                Swal.fire({ icon: "success", title: "Success!", text: "Sub Category created!", timer: 1500, showConfirmButton: false });
                $("#addSubCategoryModal").modal("hide");
                $("#addSubCategoryForm")[0].reset();
                $("#subcategories_tbl").DataTable().ajax.reload();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    if (errors.name) $(".error-name-sub").text(errors.name[0]);
                    if (errors.category_id) $(".error-category_id-sub").text(errors.category_id[0]);
                }
            }
        });
    });

    // Edit Sub Category AJAX
    function editSubCategory(id) {
        $.ajax({
            url: "/subcategories/edit/" + id + "",
            method: "GET",
            success: function(res) {
                $("#edit_id_sub").val(res.id);
                $("#edit_name_sub").val(res.name);
                $("#edit_category_id_sub").val(res.category_id);
                $(".error-edit-name-sub, .error-edit-category_id-sub").text("");
                $("#editSubCategoryModal").modal("show");
            }
        });
    }

    // Update Sub Category AJAX
    $("#editSubCategoryForm").on("submit", function(e) {
        e.preventDefault();
        $(".error-edit-name-sub, .error-edit-category_id-sub").text("");
        $.ajax({
            url: "/subcategories/update",
            method: "POST",
            data: $(this).serialize(),
            success: function(res) {
                Swal.fire({ icon: "success", title: "Updated!", text: "Sub Category updated!", timer: 1500, showConfirmButton: false });
                $("#editSubCategoryModal").modal("hide");
                $("#subcategories_tbl").DataTable().ajax.reload();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $(".error-edit-name-sub").text(errors.name ?? "");
                    $(".error-edit-category_id-sub").text(errors.category_id ?? "");
                }
            }
        });
    });

    // Sub Category Status Change
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
                    url: "{{ url('subcategories/status-change') }}",
                    data: { id: id, status: newStatus },
                    success: function(response) {
                        Swal.fire({ icon: "success", title: "Success!", text: "Status has been changed.", timer: 1500, showConfirmButton: false });
                        $('#subcategories_tbl').DataTable().ajax.reload(null, false);
                    }
                });
            }
        });
    }

    // Delete Sub Category
    function delete_sub_cat(id) {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#e53e3e",
            confirmButtonText: "Yes, delete it!",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "{{ url('subcategories/delete') }}",
                    data: { id: id },
                    success: function(response) {
                        Swal.fire({ icon: "success", title: "Deleted!", text: "The subcategory has been deleted.", timer: 1500, showConfirmButton: false });
                        $('#subcategories_tbl').DataTable().ajax.reload(null, false);
                    },
                    error: function() {
                        Swal.fire("Error!", "Something went wrong.", "error");
                    }
                });
            }
        });
    }
</script>
    <!-- Pack Size Modal -->
    <div class="modal fade" id="packSizeModalPopup" tabindex="-1" role="dialog" aria-labelledby="packSizeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="packSizeModalLabel">Pack Size</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="packSizeModalBody">
                    <div class="text-center">Loading...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $('#packSizeModalPopup').on('show.bs.modal', function (e) {
            $('#packSizeModalBody').html('<div class="text-center">Loading...</div>');
            $('#packSizeModalBody').load("{{ url('pack-size/modal') }}");
        });
    </script>
@endsection
@endsection