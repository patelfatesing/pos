@extends('layouts.backend.layouts')

@section('page-content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Wrapper Start -->
<div class="wrapper">
    <div class="content-page">
        <div class="container-fluid">
            <div class="col-lg-12">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="mb-3">Expense List</h4>
                    </div>
                    <a href="{{ route('exp.create') }}" class="btn btn-primary add-list">
                        <i class="las la-plus mr-2"></i> Add New Expense
                    </a>
                </div>
            </div>

            <div class="table-responsive rounded mb-3">
                <table class="table data-tables table-striped" id="exp_tbl">
                    <thead class="bg-white text-uppercase">
                        <tr class="ligth ligth-data">
                            <th>Name</th>
                            <th>Total Expense</th>
                            <th>Status</th>
                            <th data-type="date" data-format="YYYY/DD/MM">Created Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
<!-- Wrapper End -->

@endsection

@push('scripts')
<!-- Include JQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Include DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        alert('df');
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
            ajax: {
                url: '{{ url('exp/get-data') }}',
                type: 'POST',
            },
            columns: [
                { data: 'name', name: 'name' },
                { data: 'total_expense', name: 'total_expense' },
                { data: 'is_active', name: 'is_active' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            order: [[3, 'desc']], // Default sort by created_at DESC
            dom: "Bfrtip",
            lengthMenu: [
                [10, 25, 50],
                ['10 rows', '25 rows', '50 rows', 'All']
            ],
            buttons: ['pageLength']
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
                    type: "DELETE",
                    url: "{{ url('exp/delete') }}/" + id,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire("Deleted!", "The expense category has been deleted.", "success")
                            .then(() => $('#exp_tbl').DataTable().ajax.reload());
                    },
                    error: function(xhr) {
                        Swal.fire("Error!", "Something went wrong.", "error");
                    }
                });
            }
        });
    }
</script>
@endpush
