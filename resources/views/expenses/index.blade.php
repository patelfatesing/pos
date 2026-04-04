@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="content-page">
        <div class="container-fluid">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="mb-0">Expense List</h4>
                </div>
                <a href="{{ route('exp.create') }}" class="btn btn-success add-list">
                    <i class="las la-plus mr-3"></i>Create New Expense
                </a>
            </div>

            <div class="table-responsive rounded mb-3">
                <table class="table table-striped table-bordered nowrap" id="exp_tbl">
                    <thead class="bg-white text-uppercase">
                        <tr class="ligth ligth-data">
                            <th>Expense</th>
                            <th>Ledger</th>
                            <th>Amount</th>
                            <th>Store</th>
                            <th>User</th>
                            <th>description</th>
                            <th data-type="date" data-format="YYYY/DD/MM">Date/Time</th>
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

    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#exp_tbl').DataTable().clear().destroy();

            $('#exp_tbl').DataTable({
                pageLength: 10,
                responsive: true,
                processing: true,
                ordering: true,
                bLengthChange: true,
                serverSide: true,
                responsive: true,
                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },
                "ajax": {
                    "url": '{{ url('exp/get-data') }}',
                    "type": "post",
                    "data": function(d) {},
                },
                dom: "<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'f l>>t<'row'<'col-md-6'i><'col-md-6'p>>",
                aoColumns: [{
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'ledger_name',
                        name: 'ledger_name'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'branch_name',
                        name: 'branch_name'
                    },
                    {
                        data: 'user_name',
                        name: 'user_name'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    // {
                    //     data: 'action',
                    //     name: 'action',
                    //     orderable: false,
                    //     searchable: false
                    // },
                    // Define more columns as per your table structure
                ],
                aoColumnDefs: [{
                    bSortable: false,
                    aTargets: [3] // make "action" column unsortable
                }],
                order: [
                    [5, 'desc']
                ], // 🟢 Sort by created_at DESC by default

                lengthMenu: [
                    [10, 25, 50],
                    ['10 rows', '25 rows', '50 rows', 'All']
                ],
                buttons: ['pageLength'],
                initComplete: function() {
                    $('.dataTables_filter input').attr("placeholder", "Search List...");
                }

            });

        });

        $(document).on('click', '.view-desc', function(e) {
            e.preventDefault();
            const fullDescription = $(this).data('desc');

            Swal.fire({
                title: 'Full Description',
                html: `<div style="text-align:left;max-height:300px;overflow-y:auto">${fullDescription}</div>`,
                confirmButtonText: 'Close'
            });
        });

        function delete_exp(id) {

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
                        url: "{{ url('exp/delete') }}/" + id, // Ensure correct Laravel URL
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
