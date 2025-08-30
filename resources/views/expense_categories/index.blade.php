@extends('layouts.backend.layouts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="row align-items-center mb-3">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Expense Category List</h4>
                            </div>
                            <a href="#" onclick="add_exp_category()" class="btn btn-primary add-list">
                                <i class="las la-plus mr-3"></i>Create New Expense Category
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive rounded">
                            <table class="table table-striped table-bordered nowrap" id="exp_category_tbl"
                                style="width:100%;">
                                <thead class="bg-white">
                                    <tr class="ligth ligth-data">
                                        <th>Sr No</th> <!-- Added this line -->
                                        <th>
                                            <b>N</b>ame
                                        </th>
                                        <th>Expense Type</th>
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
    <!-- Wrapper End-->

    <div class="modal fade bd-example-modal-lg" id="addExpModal" tabindex="-1" role="dialog"
        aria-labelledby="addExpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="addExpenseForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addExpModalLabel">Add Expense Category</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" name="name" class="form-control" id="name"
                                        placeholder="Enter Low Level Quantity">
                                    <span class="text-danger" id="name_error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Select Expense Type</label>
                                    <select name="expense_type_id" id="expense_type_id" class="selectpicker form-control"
                                        data-style="py-0">
                                        <option value="" disabled selected>Select Expenct Type</option>
                                        @foreach ($expMainCategory as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('expense_type_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <span class="text-danger" id="expense_type_id_error"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
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

            $('#exp_category_tbl').DataTable().clear().destroy();

            $('#exp_category_tbl').DataTable({
                pagelength: 10,
                responsive: true,
                processing: true,
                ordering: true,
                bLengthChange: true,
                serverSide: true,

                "ajax": {
                    "url": '{{ url('exp-category/get-data') }}',
                    "type": "post",
                    "data": function(d) {},
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
                        data: 'expense_type'
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
                    aTargets: [5] // make "action" column unsortable
                }],
                order: [
                    [4, 'desc']
                ], // 🟢 Sort by created_at DESC by default

                dom: "Bfrtip",
                lengthMenu: [
                    [10, 25, 50],
                    ['10 rows', '25 rows', '50 rows', 'All']
                ],
                buttons: ['pageLength']

            });

        });

        function delete_exp_category(id) {

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
                        url: "{{ url('exp-category/delete') }}/" + id, // Ensure correct Laravel URL
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

        $('#addExpenseForm').on('submit', function(e) {
            e.preventDefault();
            $('#name_error').text('');

            let formData = {
                _token: $('input[name="_token"]').val(),
                name: $('#name').val(),
                expense_type_id: $('#expense_type_id').val(),
            };

            $.ajax({
                type: "POST",
                url: "{{ route('exp_category.store') }}",
                data: formData,
                success: function(response) {
                    alert(response.message);
                    $('#addExpModal').modal('hide');
                    $('#addExpenseForm')[0].reset();
                    location.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        if (errors.name) {
                            $('#name_error').text(errors.name[0]);
                        }
                    } else {
                        alert("An unexpected error occurred.");
                    }
                }
            });
        });

        function add_exp_category() {

            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var myModal = new bootstrap.Modal(document.getElementById('addExpModal'));
                myModal.show();
            } else {
                // For Bootstrap 4 (with jQuery)
                $('#addExpModal').modal('show');
            }
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
                        url: "{{ url('exp-category/status-change') }}", // Update this to your route
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id: id,
                            status: newStatus
                        },
                        success: function(response) {
                            Swal.fire("Success!", "Status has been changed.", "success").then(() => {
                                $('#purchase_ledger_tbl').DataTable().ajax.reload(null,
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
    </script>
@endsection
