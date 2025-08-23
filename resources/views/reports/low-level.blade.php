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

                <!-- Page Header -->
                <div class="row align-items-center mb-3">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Low Stock / Out of Stock Report</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters row (optional above table) -->
                <div class="row g-2 mb-2">
                    <div class="col-md-3">
                        <select id="branch_id" class="form-control">
                            <option value="">All Branches</option>
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                 
                    <div class="col-md-3">
                        <select id="sub_category_id" class="form-control">
                            <option value="">All Subcategories</option>
                            @foreach ($subcategories as $sc)
                                <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive rounded">
                            <table class="table table-striped table-bordered nowrap" id="low_stock_table"
                                style="width:100%;">
                                <thead class="bg-white">
                                    <tr class="ligth ligth-data">
                                        <th>Sr No</th>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Subcategory</th>
                                        <th>Branch</th>
                                        <th>Available Qty</th>
                                        <th>Reorder Level</th>
                                        <th>Status</th>
                                        <th>Nearest Expiry</th>
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
@endsection

@section('scripts')
    <script>
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const table = $('#low_stock_table').DataTable({
                processing: true,
                serverSide: false, // we fetch all filtered rows at once
                responsive: true,
                ajax: {
                    url: "{{ route('reports.low_stock.data') }}",
                    type: 'POST',
                    data: function(d) {
                        d.status = $('#status').val(); // 'low' | 'out' | ''
                        d.branch_id = $('#branch_id').val();
                        d.sub_category_id = $('#sub_category_id').val();
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
                    {
                        data: 'category_name'
                    },
                    {
                        data: 'sub_category_name'
                    },
                    {
                        data: 'branch_name'
                    },
                    {
                        data: 'qty'
                    },
                    {
                        data: 'reorder_level'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'nearest_expiry'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [7, 'asc']
                ], // Status column
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
                buttons: [{
                        extend: 'excelHtml5',
                        className: 'btn btn-outline-success btn-sm me-2',
                        title: 'Low/Out Stock',
                        filename: 'low_out_stock_excel',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-outline-danger btn-sm',
                        title: 'Low/Out Stock',
                        filename: 'low_out_stock_pdf',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                initComplete: function() {
                    // Insert status dropdown after buttons to match your order
                    const statusFilter = `
                <div class="status-filter">
                    <select id="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="low">Low Stock</option>
                        <option value="out">Out of Stock</option>
                    </select>
                </div>`;
                    $(statusFilter).insertAfter('.dt-buttons');

                    // Bind change events
                    $('#status, #branch_id,  #sub_category_id').on('change', function() {
                        table.ajax.reload();
                    });
                }
            });
        });
    </script>
@endsection
