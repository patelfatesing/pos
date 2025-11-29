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

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="mb-3">Vouchers</h4>
                    </div>
                    <!-- Updated Buttons -->
                    <div class="d-flex align-items-center ms-auto toolbar-actions">
                        <a href="{{ route('accounting.vouchers.index') }}" class="btn btn-info btn-icon"
                            title="Reload the list">
                            <i class="las la-sync"></i>
                            <span class="d-none d-sm-inline">Refresh</span>
                        </a>

                        <a href="{{ route('accounting.vouchers.create') }}" class="btn btn-primary btn-icon"
                            title="Create voucher">
                            <i class="las la-plus"></i>
                            <span class="d-none d-sm-inline">Create</span>
                        </a>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <label class="form-label mb-1">Type</label>
                        <select id="typeFilter" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach ($types as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive rounded mb-3">
                    <table class="table table-striped align-middle" id="vouchers_table" style="width:100%">
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

            const table = $('#vouchers_table').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ajax: {
                    url: "{{ route('accounting.vouchers.getData') }}",
                    type: "POST",
                    data: function(d) {
                        d.voucher_type = $('#typeFilter').val() || '';
                    }
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
