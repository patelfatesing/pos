@extends('layouts.backend.layouts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<style>
    .is-invalid {
        border-color: #dc3545;
    }
</style>
@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <div class="col-lg-12">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                        <div>
                            <h4 class="mb-3">Store List</h4>
                        </div>
                        <a href="{{ route('branch.create') }}" class="btn btn-primary add-list">
                            <i class="las la-plus mr-3"></i>Create New Store
                        </a>
                    </div>
                </div>

                <div class="table-responsive rounded mb-3">
                    <table class="table data-tables table-striped" id="branch_table">
                        <thead class="bg-white text-uppercase">
                            <tr class="ligth ligth-data">

                                <th>
                                    Name
                                </th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Main Branch</th>
                                <th data-type="date" data-format="YYYY/DD/MM">Created Date</th>
                                <th data-type="date" data-format="YYYY/DD/MM">Updated Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <!-- Page end  -->
                </div>
            </div>
        </div>
    </div>
    <!-- Wrapper End-->

    <div class="modal fade bd-example-modal-lg" id="lowlevelStockBranchModal" tabindex="-1" role="dialog"
        aria-labelledby="lowlevelStockBranchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="lowLevelForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="lowlevelStockBranchModalLabel">Product Low Stock Set</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="store_id" id="store_id" value="">
                        <div class="scrollable-content p-3" tabindex="0" id="scrollableContent">
                            <table class="table table-bordered" id="lowLevelProductTable">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Low Level Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Filled by AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add this in your Blade layout -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Modal -->
    <div class="modal fade bd-example-modal-lg" id="AddHolidayModal" tabindex="-1" aria-labelledby="AddHolidayModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="addHolidayForm" method="POST" action="{{ route('holidays.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="AddHolidayModalLabel">
                            <i class="ri-calendar-event-line"></i> Add Holiday
                        </h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>

                    <div class="modal-body">
                        <!-- Hidden branch_id -->
                        <input type="hidden" name="branch_id" id="hd_branch_id" value="{{ $currentBranch->id ?? 1 }}">

                        <!-- Title -->
                        <div class="form-group">
                            <label for="holiday_title">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="holiday_title" name="title"
                                value="{{ old('title') }}" maxlength="100">
                        </div>

                        <!-- Holiday Date -->
                        @php
                            $today = \Carbon\Carbon::now('Asia/Kolkata')->toDateString();
                        @endphp
                        <div class="form-group">
                            <label for="holiday_date">Holiday Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="holiday_date" name="holiday_date"
                                value="{{ old('holiday_date', $today) }}" min="{{ $today }}">
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="holiday_desc">Description (optional)</label>
                            <textarea class="form-control" id="holiday_desc" name="description" rows="2" maxlength="255">{{ old('description') }}</textarea>
                        </div>

                        <div id="holidayErrors"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="holidaySaveBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .scrollable-content {
            max-height: 450px;
            overflow-y: auto;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }
    </style>

    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#branch_table').DataTable().clear().destroy();

            $('#branch_table').DataTable({
                pagelength: 10,
                responsive: true,
                processing: true,
                ordering: true,
                bLengthChange: true,
                serverSide: true,

                "ajax": {
                    "url": '{{ url('store/get-data') }}',
                    "type": "post",
                    "data": function(d) {},
                },
                aoColumns: [

                    {
                        data: 'name'
                    },
                    {
                        data: 'address'
                    },
                    {
                        data: 'is_active'
                    },
                    {
                        data: 'main_branch'
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
                    aTargets: [1, 2, 3, 6]
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
                        type: "POST", // "method" also works
                        url: "{{ url('store/delete') }}", // Ensure correct Laravel URL
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id: id
                        },
                        success: function(response) {
                            $('#branch_table').DataTable().ajax.reload();
                            Swal.fire("Deleted!", "The store has been deleted.", "success");

                            // swal("Deleted!", "The store has been deleted.", "success")
                            //     .then(() => location.reload());
                        },
                        error: function(xhr) {
                            swal("Error!", "Something went wrong.", "error");
                        }
                    });
                }
            });

        }

        function branchStatusChange(id, newStatus) {
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
                        url: "{{ url('store/status-change') }}", // Update this to your route
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id: id,
                            status: newStatus
                        },
                        success: function(response) {
                            Swal.fire("Success!", "Store status has been changed.", "success").then(
                                () => {
                                    $('#branch_table').DataTable().ajax.reload(null,
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

        function low_level_stock(storeId) {
            $('#store_id').val(storeId);

            $.ajax({
                url: '/inventories/get-low-level-products/' + storeId,
                type: 'GET',
                success: function(data) {
                    let tbody = $('#lowLevelProductTable tbody');
                    tbody.empty();

                    data.forEach(function(product) {
                        tbody.append(`
                        <tr>
                            <td>${product.name}</td>
                            <td>
                                <input type="number" name="low_level_qty[${product.id}]" class="form-control low-level-qty" value="${product.low_level_qty ?? ''}">
                            </td>
                        </tr>
                    `);
                    });

                    $('#lowlevelStockBranchModal').modal('show');
                },
                error: function() {
                    alert('Failed to load products.');
                }
            });
        }

        $('#lowLevelForm').on('submit', function(e) {
            e.preventDefault();

            let isValid = true;
            let errorMsg = 'Please fill all Low Level Quantity fields.';

            $('#lowLevelProductTable tbody tr').each(function() {
                let qtyInput = $(this).find('.low-level-qty');
                let qtyValue = qtyInput.val();

                if (qtyValue === '' || qtyValue === null) {
                    isValid = false;
                    qtyInput.addClass('is-invalid');
                } else {
                    qtyInput.removeClass('is-invalid');
                }
            });

            if (!isValid) {
                alert(errorMsg);
                return;
            }

            let formData = $('#lowLevelForm').serialize();

            $.ajax({
                url: '/inventories/update-multiple-low-level-qty',
                type: 'POST',
                data: formData,
                success: function(response) {
                    $('#lowlevelStockBranchModal').modal('hide');
                    // $('#inventory_table').DataTable().ajax.reload(null, false);
                    alert('Low level quantities updated successfully.');
                    location.reload();
                },
                error: function(xhr) {
                    alert('Error updating quantities.');
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const scrollable = document.getElementById('scrollableContent');

            // Auto-focus when modal is shown
            $('.modal').on('shown.bs.modal', function() {
                scrollable.focus();
            });

            // Allow focus again on click
            scrollable.addEventListener('click', () => {
                scrollable.focus();
            });
        });

        function add_store_holiday(storeId) {
            $('#hd_store_id').val(storeId);

            $('#AddHolidayModal').modal('show');

        }

        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const offset = today.getTimezoneOffset();
            today.setMinutes(today.getMinutes() - offset); // adjust to local timezone
            const minDate = today.toISOString().split('T')[0];
            document.getElementById('holiday_date').setAttribute('min', minDate);
        });

        $('#addHolidayForm').on('submit', function(e) {
            e.preventDefault();
            let $form = $(this);
            let $submitBtn = $('#holidaySaveBtn');
            $submitBtn.prop('disabled', true);

            let formData = $form.serialize();

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    $('#AddHolidayModal').modal('hide');
                    alert('Holiday added successfully.');
                    location.reload();
                },
                error: function(xhr) {
                    $('#holidayErrors').empty();
                    $('.is-invalid').removeClass('is-invalid');
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, messages) {
                            let input = $('[name="' + key + '"]');
                            input.addClass('is-invalid');
                            input.after('<div class="invalid-feedback d-block">' + messages[0] +
                                '</div>');
                        });
                    } else {
                        alert('Something went wrong.');
                        console.log(xhr.responseText);
                    }
                },
                complete: function() {
                    $submitBtn.prop('disabled', false);
                }
            });
        });
    </script>
@endsection
