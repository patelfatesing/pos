@extends('layouts.backend.datatable_layouts')

@section('styles')
    <style>
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

        .custom-toolbar-row .filters {
            order: 3;
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .custom-toolbar-row .dataTables_filter {
            order: 4;
            margin-left: auto;
        }

        .dt-buttons .btn {
            margin-right: 5px;
        }

        @media(max-width:768px) {
            .custom-toolbar-row>div {
                flex: 1 1 100%;
                margin-bottom: 10px;
            }
        }

        .w-140 {
            width: 140px
        }
    </style>
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <div class="row align-items-center mb-3">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Category-wise / Subcategory-wise Sales</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row g-2 mb-2">
                    <div class="col-md-2">
                        <select id="branch_id" class="form-control">
                            <option value="">All Branches</option>
                            @foreach ($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="start_date" class="form-control w-140" />
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="end_date" class="form-control w-140" />
                    </div>
                    <div class="col-md-2">
                        <select id="group_by" class="form-control">
                            <option value="category">Group by: Category</option>
                            <option value="subcategory">Group by: Subcategory</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="category_id" class="form-control">
                            <option value="">All Categories</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select id="sub_category_id" class="form-control">
                            <option value="">All Subcategories</option>
                            @foreach ($subCategories as $sc)
                                <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive rounded">
                    <table class="table table-striped table-bordered nowrap" id="category_sales_table" style="width:100%;">
                        <thead class="bg-white">
                            <tr class="ligth ligth-data">
                                <th>Sr No</th>
                                <th>Group</th>
                                <th>Qty</th>
                                <th>Gross Revenue</th>
                                <th>Discounts</th>
                                <th>Net Sales</th>
                                <th>Tax</th>
                                <th>Total Sales</th>
                                <th>Bills</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
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

            const table = $('#category_sales_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('reports.category_sales.get_data') }}",
                    type: 'POST',
                    data: function(d) {
                        d.branch_id = $('#branch_id').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.group_by = $('#group_by').val(); // category | subcategory
                        d.category_id = $('#category_id').val();
                        d.sub_category_id = $('#sub_category_id').val();
                    }
                },
                columns: [{
                        data: 'sr_no',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'group_name'
                    },
                    {
                        data: 'qty'
                    },
                    {
                        data: 'gross'
                    },
                    {
                        data: 'discounts'
                    },
                    {
                        data: 'net_sales'
                    },
                    {
                        data: 'tax'
                    },
                    {
                        data: 'total'
                    },
                    {
                        data: 'bills'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [7, 'desc']
                ], // Total Sales desc
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
                buttons: [{
                        extend: 'excelHtml5',
                        className: 'btn btn-outline-success btn-sm me-2',
                        title: 'Category/Subcategory Sales',
                        filename: 'category_sales_report',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-outline-danger btn-sm',
                        title: 'Category/Subcategory Sales',
                        filename: 'category_sales_report',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                initComplete: function() {
                    $('#branch_id, #start_date, #end_date, #group_by, #category_id, #sub_category_id')
                        .on('change', function() {
                            table.ajax.reload();
                        });
                }
            });
        });
    </script>
@endsection
