{{-- resources/views/accounting/ledgers/index.blade.php --}}
@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="content-page accounting-ledgers-page">
        <div class="container-fluid">
            <div class="row align-items-center mb-3">
                <div class="col-lg-12">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0">Account Ledgers</h4>
                        </div>
                        <div class="ml-auto">
                            @if (auth()->user()->role_id == 1 || canCreate(auth()->user()->role_id, 'accounting-ledgers-create'))
                                <a href="{{ route('accounting.ledgers.create') }}" class="btn btn-primary">
                                    <i class="las la-plus me-1"></i> Add Ledger
                                </a>
                            @endif
                        </div>
                        <div class="col-md-2 pr-0">
                            <div class="form-group mb-0">
                                <select id="groupFilter" class="form-control">
                                    <option value="">All Groups </option>
                                    @foreach ($groups as $g)
                                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 pr-0">
                            <div class="form-group mb-0">
                                <select id="branchFilter" class="form-control">
                                    <option value="">All Branch</option>
                                    @foreach ($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 pr-0">
                            <div class="form-group mb-0">
                                <select id="activeFilter" class="form-control">
                                    <option value="">All Status </option>
                                    <option value="yes">Active</option>
                                    <option value="no">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive rounded mb-3">
                <table class="table table-striped table-bordered nowrap" id="ledgers_table">
                    <thead>
                        <tr>
                            <th>Sr</th>
                            <th>Name</th>
                            <th>Group</th>
                            <th>Branch</th>
                            <th>Opening</th>
                            <th>Type</th>
                            <th>Active</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>
    </div>

    <script>
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if ($.fn.DataTable.isDataTable('#ledgers_table')) {
                $('#ledgers_table').DataTable().clear().destroy();
            }

            const dt = $('#ledgers_table').DataTable({
                pageLength: 10,
                responsive: true,
                processing: true,
                serverSide: true,
                ordering: true,
                bLengthChange: true,
                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },
                ajax: {
                    url: "{{ route('accounting.ledgers.getData') }}",
                    type: "POST",
                    data: function(d) {
                        d.group_id = $('#groupFilter').val() || '';
                        d.branch_id = $('#branchFilter').val() || '';
                        d.active = $('#activeFilter').val() || '';
                    }
                },
               dom: "<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'f l>>t<'row'<'col-md-6'i><'col-md-6'p>>",
                initComplete: function() {
                    $('.dataTables_filter input').attr("placeholder", "Search List...");
                },
                aoColumns: [{
                        data: null,
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
                        data: 'group_name',
                        name: 'group_name'
                    },
                    {
                        data: 'branch_name',
                        name: 'branch_name'
                    },
                    {
                        data: 'opening_balance',
                        name: 'opening_balance'
                    },
                    {
                        data: 'opening_type',
                        name: 'opening_type'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        orderable: false,
                        searchable: false
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

                order: [
                    [7, 'desc']
                ], // created_at desc
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    ['10 rows', '25 rows', '50 rows', '100 rows', 'All']
                ],
                buttons: ['pageLength'],
            });

            // filters
            $(document).on('change', '#groupFilter, #branchFilter, #activeFilter', function() {
                dt.ajax.reload(null, false);
            });

            // delete
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                const id = $(this).data('id');

                Swal.fire({
                    title: "Are you sure?",
                    text: "This ledger will be permanently deleted.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "DELETE",
                            url: "{{ url('/accounting/ledgers/delete') }}/" + id,
                            success: function(res) {

                                // swal("Deleted!", res.message || "Ledger deleted.",
                                //         "success")
                                //     .then(() => location.reload());
                                $('#ledgers_table').DataTable().ajax.reload();
                            },
                            error: function(xhr) {
                                Swal.fire("Error!", xhr.responseJSON?.message ||
                                    "Delete failed.", "error");
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
