{{-- resources/views/reports/product_inactive.blade.php --}}
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
            align-items: center;
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

        /* single-line filter row (no scroll) */
        .filters.one-line {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: nowrap;
            overflow: hidden;
        }

        .filters.one-line label {
            white-space: nowrap;
            margin-bottom: 0;
        }

        /* make inputs shrink instead of wrapping */
        .filters.one-line .form-control {
            flex: 0 1 160px;
            min-width: 120px;
        }

        /* finer control per input */
        #category_id,
        #sub_category_id {
            flex: 0 1 220px;
            min-width: 160px;
        }

        #start_date,
        #end_date {
            flex: 0 1 140px;
            min-width: 110px;
        }

        /* keep the checkbox compact & inline */
        .filters.one-line .form-check {
            display: flex;
            align-items: center;
            gap: .35rem;
            margin: 0 .25rem 0 0;
            white-space: nowrap;
        }

        .filters.one-line .form-check-input {
            margin-top: 0;
        }
    </style>
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h4 class="mb-0">Product Inactive Report</h4>
                </div>

                <div class="table-responsive rounded">
                    <table class="table table-striped table-bordered nowrap" id="product_inactive_table" style="width:100%;">
                        <thead class="bg-white">
                            <tr class="ligth ligth-data">
                                <th>Sr No</th>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Brand</th>
                                <th>Size</th>
                                <th>Category</th>
                                <th>Subcategory</th>
                                <th>Sell Price</th>
                                <th>Cost Price</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    {{-- sweetalert optional; else use native confirm --}}
@endsection

@section('scripts')
    <script>
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const filtersHtml = `
                <div class="filters one-line">
                    <label class="mb-0">Subcategory</label>
                    <select id="sub_category_id" class="form-control form-control-sm">
                    <option value="">All</option>
                    @foreach ($subcats as $sc)
                        <option value="{{ $sc->id }}">
                        {{ $sc->category_name }}
                        </option>
                    @endforeach
                    </select>

                    <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="include_deleted">
                    <label class="form-check-label" for="include_deleted">Include Deleted</label>
                    </div>
                </div>
                `;

            const table = $('#product_inactive_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('reports.product_inactive.data') }}",
                    type: 'POST',
                    data: function(d) {
                        d.category_id = $('#category_id').val();
                        d.sub_category_id = $('#sub_category_id').val();
                        d.include_deleted = $('#include_deleted').is(':checked') ? 1 : 0;
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                columns: [{
                        data: 'sr_no',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'sku'
                    },
                    {
                        data: 'brand'
                    },
                    {
                        data: 'size'
                    },
                    {
                        data: 'category_name'
                    },
                    {
                        data: 'sub_category_name'
                    },
                    {
                        data: 'sell_price'
                    },
                    {
                        data: 'cost_price'
                    },
                    {
                        data: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'updated_at'
                    }
                ],
                order: [
                    [11, 'desc']
                ], // updated desc
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
                buttons: [{
                        extend: 'excelHtml5',
                        className: 'btn btn-outline-success btn-sm me-2',
                        title: 'Product Inactive Report',
                        filename: 'product_inactive_report',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-outline-danger btn-sm',
                        title: 'Product Inactive Report',
                        filename: 'product_inactive_report',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                initComplete: function() {
                    $(filtersHtml).insertAfter('.dt-buttons');

                    // Prefill last 30 days (today inclusive)
                    (function presetLast30Days() {
                        const toISO = (d) => new Date(d.getTime() - (d.getTimezoneOffset() * 60000))
                            .toISOString().slice(0, 10);
                        const now = new Date();
                        const start = new Date();
                        start.setDate(now.getDate() - 29);
                        if (!$('#start_date').val()) $('#start_date').val(toISO(start));
                        if (!$('#end_date').val()) $('#end_date').val(toISO(now));
                    })();

                    // Optional: filter subcategories by selected category on the client
                    $('#category_id').on('change', function() {
                        const cat = $(this).val();
                        $('#sub_category_id option').each(function() {
                            const optCat = $(this).data('cat');
                            if (!optCat || !cat || String(optCat) === String(cat)) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                        // if current subcat not in cat, reset
                        const cur = $('#sub_category_id').val();
                        if (cur) {
                            const curOpt = $('#sub_category_id option[value="' + cur + '"]');
                            if (curOpt.length && curOpt.is(':hidden')) {
                                $('#sub_category_id').val('');
                            }
                        }
                        table.ajax.reload();
                    });

                    $('#sub_category_id, #include_deleted, #start_date, #end_date').on('change',
                        function() {
                            table.ajax.reload();
                        });
                },
                columnDefs: [{
                    targets: '_all',
                    defaultContent: ''
                }]
            });


        });
    </script>
@endsection
