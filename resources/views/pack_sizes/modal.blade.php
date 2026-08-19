    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="container-fluid">
        <div class="card-header mb-1 d-flex flex-wrap align-items-center justify-content-between">
            <div>
                <h4 class="mb-0">Pack Size List</h4>
            </div>
            @if (auth()->user()->role_id == 1 || canCreate(auth()->user()->role_id, 'pack-size-create'))
                <button class="btn btn-success add-list" data-toggle="modal" data-target="#packSizeModal">
                    <i class="las la-plus mr-3"></i>Create New Pack Size
                </button>
            @endif
        </div>

        <div class="table-responsive rounded mb-3">
                <table class="table table-striped table-bordered nowrap" id="pack_size_tbl">
                <thead class="bg-white text-uppercase">
                    <tr class="ligth ligth-data">
                        <th>
                            <b>S</b>ize
                        </th>
                        <th>Status</th>
                        <th data-type="date" data-format="YYYY/DD/MM">Created Date</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <!-- Page end  -->
    </div>

    <!-- Add Pack Size Modal -->
    <div class="modal fade" id="packSizeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">

                <div class="modal-header card-header d-flex flex-wrap align-items-center justify-content-between">
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
                        <button type="submit" class="btn btn-success">Add Pack Size</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        var pdfLogo = "";
        $(document).ready(function() {

            // Hide parent modal when nested modal opens
            $('#packSizeModal').on('show.bs.modal', function () {
                $('#packSizeModalPopup').modal('hide');
            });

            // Show parent modal when nested modal closes
            $('#packSizeModal').on('hidden.bs.modal', function () {
                $('#packSizeModalPopup').modal('show');
            });

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
                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },
                "ajax": {
                    "url": '{{ url('pack-size/get-data') }}',
                    "type": "post",
                    "data": function(d) {},
                },
                dom: "<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'f l>>t<'row'<'col-md-6'i><'col-md-6'p>>",
               initComplete: function() {
                    $('.dataTables_filter input').attr("placeholder", "Search List...");
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
                ],
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [1] 
                }],
                order: [
                    [2, 'desc']
                ], 
                lengthMenu: [
                    [10, 25, 50],
                    ['10 rows', '25 rows', '50 rows', 'All']
                ],
            });

        });
        
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
