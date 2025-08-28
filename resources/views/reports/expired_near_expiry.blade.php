@extends('layouts.backend.datatable_layouts')

@section('styles')
    <style>
        .add-list {
            white-space: nowrap;
        }

        .custom-toolbar-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .custom-toolbar-row .dataTables_length {
            order: 1;
        }

        .custom-toolbar-row .dt-buttons {
            order: 2;
        }

        .custom-toolbar-row .status-filter {
            order: 3;
        }

        .custom-toolbar-row .days-filter {
            order: 3;
        }

        /* next to status */
        .custom-toolbar-row .dataTables_filter {
            order: 4;
            margin-left: auto;
        }

        .dataTables_wrapper .dataTables_filter label,
        .dataTables_wrapper .dataTables_length label {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 0;
        }

        .dt-buttons .btn {
            margin-right: 5px;
        }

        @media (max-width: 768px) {
            .custom-toolbar-row>div {
                flex: 1 1 100%;
                margin-bottom: 10px;
            }
        }
    </style>
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <!-- Header -->
                <div class="row align-items-center mb-3">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Expired / Near Expiry Products</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter row (above table) -->
                <div class="row g-2 mb-2">
                    <div class="col-md-3">
                        <select id="branch_id" class="form-control">
                            <option value="">All Branches</option>
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- <div class="col-md-3">
                        <select id="category_id" class="form-control">
                            <option value="">All Categories</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div> --}}
                    <div class="col-md-3">
                        <select id="sub_category_id" class="form-control">
                            <option value="">All Subcategories</option>
                            @foreach ($subCategories as $sc)
                                <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive rounded">
                            <table class="table table-striped table-bordered nowrap" id="expiry_table" style="width:100%;">
                                <thead class="bg-white">
                                    <tr class="ligth ligth-data">
                                        <th>Sr No</th>
                                        <th>Product</th>
                                        {{-- <th>Category</th> --}}
                                        <th>Subcategory</th>
                                        <th>Branch</th>
                                        <th>Batch No</th>
                                        <th>Expiry Date</th>
                                        <th>Qty</th>
                                        <th>Status</th>
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
@endsection

@section('scripts')
    <script>
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const table = $('#expiry_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('reports.expiry.get_data') }}",
                    type: 'POST',
                    data: function(d) {
                        d.branch_id = $('#branch_id').val();
                        d.category_id = $('#category_id').val();
                        d.sub_category_id = $('#sub_category_id').val();
                        d.type = $('#type').val(); // 'expired' | 'near' | ''
                        d.days = $('#days').val(); // number of days for 'near'
                    }
                },
                columns: [{
                        data: 'sr_no',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'product_name'
                    },
                    // {
                    //     data: 'category_name'
                    // },
                    {
                        data: 'sub_category_name'
                    },
                    {
                        data: 'branch_name'
                    },
                    {
                        data: 'batch_no'
                    },
                    {
                        data: 'expiry_date'
                    },
                    {
                        data: 'qty'
                    },
                    {
                        data: 'status'
                    }
                ],
                order: [
                    [6, 'asc']
                ], // Expiry date
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
                buttons: [{
                        extend: 'excelHtml5',
                        className: 'btn btn-outline-success btn-sm me-2',
                        title: 'Expired / Near Expiry',
                        filename: 'expired_near_expiry_excel',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-outline-danger btn-sm',
                        title: 'Expired / Near Expiry',
                        filename: 'expired_near_expiry_pdf',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                initComplete: function() {
                    // Type dropdown
                    $('<div class="status-filter me-2">' +
                        '<select id="type" class="form-control">' +
                        '<option value="">All</option>' +
                        '<option value="expired" selected>Expired</option>' +
                        '<option value="near">Near Expiry</option>' +
                        '</select>' +
                        '</div>').insertAfter('.dt-buttons');

                    // Days input (for Near)
                    $('<div class="days-filter">' +
                        '<input type="number" id="days" class="form-control" min="1" value="30" title="Days for Near Expiry" />' +
                        '</div>').insertAfter('.status-filter');

                    $('#type, #days, #branch_id, #category_id, #sub_category_id').on('change keyup',
                        function() {
                            table.ajax.reload();
                        });
                }
            });
        });
    </script>
@endsection
