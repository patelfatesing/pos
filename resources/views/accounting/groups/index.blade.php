{{-- resources/views/accounting/groups/index.blade.php --}}
@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Account Groups</h4>
                            </div>
                            @if (auth()->user()->role_id == 1 || canCreate(auth()->user()->role_id, 'accounting-groups-create'))
                                <a href="{{ route('accounting.groups.create') }}" class="btn btn-primary">
                                    <i class="las la-plus me-1"></i> Add Group
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="col-lg-12">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif

                        <div class="table-responsive rounded mb-3">
                            <table class="table table-striped align-middle" id="groups_table" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Nature</th>
                                        <th>Affects Gross</th>
                                        <th>Parent</th>
                                        <th>Actions</th>
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

    <script>
        $(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            const table = $('#groups_table').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: "{{ route('accounting.groups.getData') }}",
                    type: "POST"
                },
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'nature',
                        name: 'nature'
                    },
                    {
                        data: 'affects_gross',
                        name: 'affects_gross',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'parent',
                        name: 'parent'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'asc']
                ]
            });

            // SweetAlert delete
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                const form = $(this).closest('form');
                const url = form.attr('action'); // should point to route('...destroy', id)

                swal({
                    title: "Are you sure?",
                    text: "This group will be permanently deleted.",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((will) => {
                    if (!will) return;

                    $.ajax({
                        url: url,
                        type: 'DELETE', // ✅ correct verb (no spaces)
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function() {
                            // reload DataTable (make sure `table` is in scope)
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            alert('Delete failed: ' + (xhr.responseJSON?.message ||
                                'Server error'));
                        }
                    });
                });
            });

        });
    </script>
@endsection
