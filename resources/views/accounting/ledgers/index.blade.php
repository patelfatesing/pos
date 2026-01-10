{{-- resources/views/accounting/ledgers/index.blade.php --}}
@extends('layouts.backend.datatable_layouts')

@section('page-content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>



</style>

    <div class="wrapper">
        <div class="content-page accounting-ledgers-page">
            <div class="container-fluid">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between mb-3">
                    <h4 class="mb-0">Account Ledgers</h4>
                    <h5 class="title-table">Liqure HUB</h5>
                    <a href="{{ route('accounting.ledgers.create') }}" class="btn btn-primary">
                        <i class="las la-plus me-1"></i> Add Ledger
                    </a>
                </div>

                {{-- Filters --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <label class="form-label mb-1">Group</label>
                        <select id="groupFilter" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach ($groups as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-1">Branch</label>
                        <select id="branchFilter" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-1">Active</label>
                        <select id="activeFilter" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="yes">Active</option>
                            <option value="no">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive rounded mb-3">
                    <table class="table table-striped align-middle" id="ledgers_table" style="width:100%">
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

                ajax: {
                    url: "{{ route('accounting.ledgers.getData') }}",
                    type: "POST",
                    data: function(d) {
                        d.group_id = $('#groupFilter').val() || '';
                        d.branch_id = $('#branchFilter').val() || '';
                        d.active = $('#activeFilter').val() || '';
                    }
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
                dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    ['10 rows', '25 rows', '50 rows', '100 rows', 'All']
                ],
                buttons: [{
                        extend: 'excelHtml5',
                        className: 'btn btn-outline-success btn-sm me-2',
                        title: 'Account Ledgers',
                        filename: 'account_ledgers_excel',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-outline-danger btn-sm',
                        title: 'Account Ledgers',
                        filename: 'account_ledgers_pdf',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ]
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
