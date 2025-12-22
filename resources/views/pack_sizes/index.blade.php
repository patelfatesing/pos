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
                            <h4 class="mb-3">Pack Size List</h4>
                        </div>
                        <button class="btn btn-primary add-list" data-toggle="modal" data-target="#packSizeModal">
                            <i class="las la-plus mr-3"></i>Create New Pack Size
                        </button>
                    </div>
                </div>

                <div class="table-responsive rounded mb-3">
                    <table class="table data-tables table-striped" id="pack_size_tbl">
                        <thead class="bg-white text-uppercase">
                            <tr class="ligth ligth-data">
                                <th>
                                    <b>S</b>ize
                                </th>
                                <th>Status</th>
                                <th data-type="date" data-format="YYYY/DD/MM">Created Date</th>
                                {{-- <th>Action</th> --}}
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
    <!-- Add Pack Size Modal -->
    <div class="modal fade" id="packSizeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Add Pack Size</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <form id="packSizeForm">
                    @csrf
                    <div class="modal-body">

                        <div class="form-group">
                            <label>Size <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="size" placeholder="Enter Size">
                                <div class="input-group-append">
                                    <span class="input-group-text">ML</span>
                                </div>
                            </div>
                            <span class="text-danger error-size"></span>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Add Pack Size</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
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

            $('#pack_size_tbl').DataTable().clear().destroy();

            $('#pack_size_tbl').DataTable({
                pagelength: 10,
                responsive: true,
                processing: true,
                ordering: true,
                bLengthChange: true,
                serverSide: true,

                "ajax": {
                    "url": '{{ url('pack-size/get-data') }}',
                    "type": "post",
                    "data": function(d) {},
                },
                aoColumns: [{
                        data: 'size'
                    },
                    {
                        data: 'is_active'
                    },
                    {
                        data: 'created_at'
                    },
                    // {
                    //     data: 'action'
                    // }
                    // Define more columns as per your table structure
                ],
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [1] // make "action" column unsortable
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

        $(document).on('submit', '#packSizeForm', function(e) {
            e.preventDefault();

            $('.error-size').text('');

            $.ajax({
                url: "{{ route('packsize.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {

                    $('#packSizeModal').modal('hide');
                    $('#packSizeForm')[0].reset();

                    $('#pack_size_tbl').DataTable().ajax.reload(null, false);

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Pack Size added successfully',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        if (errors.size) {
                            $('.error-size').text(errors.size[0]);
                        }
                    }
                }
            });
        });
    </script>
@endsection
