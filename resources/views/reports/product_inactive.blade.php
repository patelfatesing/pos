{{-- resources/views/reports/product_inactive.blade.php --}}
@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0">Product Inactive Report</h4>
                    </div>
                    <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
                </div>

                <div class="table-responsive rounded mt-2">
                    <table class="table table-striped table-bordered nowrap" id="product_inactive_table" style="width:100%;">
                        <thead class="bg-white">
                            <tr class="ligth ligth-data">
                                <th>Sr No</th>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Brand</th>
                                <th>Size</th>
                                <th>Sub Category</th>
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
                    <label class="mb-0">Sub Category</label>
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
                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },
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
                dom: "<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'Bf l>>t<'row'<'col-md-6'i><'col-md-6'p>>",

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
                    [10, 'desc']
                ], // updated desc
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                dom:"<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'Bf l>>t<'row'<'col-md-6'i><'col-md-6'p>>",
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fa fa-file-excel-o"></i> Excel',
                        title: 'Discount & Offer Report',
                        filename: 'discount_offer_report',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },

                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fa fa-file-pdf-o"></i> PDF',
                        filename: 'discount_offer_report',
                        orientation: 'landscape',
                        pageSize: 'A4',

                        exportOptions: {
                            columns: ':visible'
                        },

                        customize: function(doc) {

                            doc.content.splice(0, 1);

                            doc.pageMargins = [15, 55, 15, 25];

                            // Smaller font for many columns
                            doc.defaultStyle.fontSize = 7;

                            var table = doc.content[0].table;

                            // Auto column widths (important)
                            table.widths = new Array(table.body[0].length).fill('*');

                            // Better header style
                            doc.styles.tableHeader = {
                                fillColor: '#2c3e50',
                                color: 'white',
                                alignment: 'center',
                                bold: true,
                                fontSize: 8
                            };

                            var body = table.body;

                            for (var i = 1; i < body.length; i++) {

                                body[i][0].alignment = 'center';
                                body[i][1].alignment = 'center';
                                body[i][2].alignment = 'center';
                                body[i][3].alignment = 'center';
                                body[i][4].alignment = 'left';

                                for (var j = 5; j < body[i].length; j++) {
                                    body[i][j].alignment = 'right';
                                }
                            }

                            // Table layout for clean lines
                            doc.content[0].layout = {
                                hLineWidth: function() {
                                    return .5;
                                },
                                vLineWidth: function() {
                                    return .5;
                                },
                                hLineColor: function() {
                                    return '#aaa';
                                },
                                vLineColor: function() {
                                    return '#aaa';
                                },
                                paddingLeft: function() {
                                    return 4;
                                },
                                paddingRight: function() {
                                    return 4;
                                }
                            };

                            // Header
                            doc.content.unshift({

                                margin: [0, 0, 0, 12],

                                columns: [

                                    {
                                        width: '33%',
                                        columns: [{
                                                image: pdfLogo,
                                                width: 30
                                            },
                                            {
                                                text: 'LiquorHub',
                                                fontSize: 11,
                                                bold: true,
                                                margin: [5, 8, 0, 0]
                                            }
                                        ]
                                    },

                                    {
                                        width: '34%',
                                        text: 'Discount & Offer Report',
                                        alignment: 'center',
                                        fontSize: 14,
                                        bold: true,
                                        margin: [0, 8, 0, 0]
                                    },

                                    {
                                        width: '33%',
                                        text: 'Generated: ' + new Date()
                                        .toLocaleString(),
                                        alignment: 'right',
                                        fontSize: 8,
                                        margin: [0, 8, 0, 0]
                                    }

                                ]
                            });

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
