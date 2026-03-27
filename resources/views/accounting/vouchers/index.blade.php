{{-- resources/views/accounting/vouchers/index.blade.php --}}
@extends('layouts.backend.datatable_layouts')

@section('styles')
    <style>
        /* Toolbar buttons */
        .toolbar-actions {
            gap: .5rem;
        }

        .btn-icon {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .45rem .85rem;
            line-height: 1.2;
            border-radius: .5rem;
            font-size: .875rem;
        }

        .btn-icon i {
            font-size: 1.05rem;
            line-height: 1;
        }

        /* Optional softer variant for Refresh */
        .btn-soft {
            background: rgba(var(--bs-primary-rgb), .08);
            color: var(--bs-primary);
            border: 1px solid rgba(var(--bs-primary-rgb), .15);
        }

        .btn-soft:hover {
            background: rgba(var(--bs-primary-rgb), .15);
            color: var(--bs-primary-dark, #0d6efd);
        }
    </style>
@endsection

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="content-page">
        <div class="container-fluid">
            <div class="row align-items-center mb-2">
                <div class="col-lg-12">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0">Vouchers</h4>
                        </div>
                        <div class="ml-auto">
                            <a href="{{ route('accounting.vouchers.index') }}" class="btn btn-info btn-icon"
                                title="Reload the list">
                                <i class="las la-sync"></i>
                                <span class="d-none d-sm-inline">Refresh</span>
                            </a>
                            @if (auth()->user()->role_id == 1 || canCreate(auth()->user()->role_id, 'accounting-voucher-create'))
                                <a href="{{ route('accounting.vouchers.create') }}" class="btn btn-primary btn-icon"
                                    title="Create voucher">
                                    <i class="las la-plus"></i>
                                    <span class="d-none d-sm-inline">Create</span>
                                </a>
                            @endif
                        </div>

                        <div class="col-md-3 pr-0">
                            <div class="form-group mb-0">
                                <select name="typeFilter" id="typeFilter" class="form-control">
                                    <option value="">All</option>
                                    @foreach ($types as $t)
                                        <option value="{{ $t }}">{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="table-responsive rounded mb-3">
                <table class="table table-striped table-bordered nowrap" id="vouchers_table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Ref No</th>
                            <th>Branch</th>
                            <th>Narration</th>
                            <th class="text-end">Dr Total</th>
                            <th class="text-end">Cr Total</th>
                            <th>Status</th>
                            <th>Verify Status</th>
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

            const table = $('#vouchers_table').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },
                ajax: {
                    url: "{{ route('accounting.vouchers.getData') }}",
                    type: "POST",
                    data: function(d) {
                        d.voucher_type = $('#typeFilter').val() || '';
                    }
                },
                dom: "<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'f l>>t<'row'<'col-md-6'i><'col-md-6'p>>",
                initComplete: function() {
                    $('.dataTables_filter input').attr("placeholder", "Search List...");
                },
                columns: [{
                        data: 'voucher_date',
                        name: 'voucher_date'
                    },
                    {
                        data: 'voucher_type',
                        name: 'voucher_type'
                    },
                    {
                        data: 'ref_no',
                        name: 'ref_no'
                    },
                    {
                        data: 'branch',
                        name: 'branch'
                    },
                    {
                        data: 'narration',
                        name: 'narration'
                    },
                    {
                        data: 'dr_total',
                        name: 'dr_total',
                        className: 'text-end'
                    },
                    {
                        data: 'cr_total',
                        name: 'cr_total',
                        className: 'text-end'
                    },
                    {
                        data: 'admin_status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [0, 'desc']
                ]
            });

            // SweetAlert delete
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                const form = $(this).closest('form');
                const url = form.attr('action');

                Swal.fire({
                    title: "Are you sure?",
                    text: "This voucher will be permanently deleted.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        success: function() {
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            Swal.fire("Error", xhr.responseJSON?.message ||
                                'Server error', "error");
                        }
                    });
                });
            });

            $(document).on('change', '#typeFilter', function() {
                table.ajax.reload(null, false);
            });
        });
    </script>
@endsection
