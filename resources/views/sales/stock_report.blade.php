@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="content-page">
        <div class="container-fluid">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <div>
                    <h4 class="mb-0">Stock Summary</h4>
                </div>
                <a href="{{ route('reports.list') }}" class="btn btn-secondary">Back</a>
            </div>
            <!-- Page Header -->
            <div class="row mt-2">

                <!-- Filters -->
                <div class="col-md-2 mb-2">
                    <select id="store_id" class="form-control">
                        <option value="">All Branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select id="product_id" class="form-control">
                        <option value="">All Products</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select id="category_id" class="form-control">
                        <option value="">All Categories</option>
                        @foreach ($subcategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- <div class="col-md-2 mb-2">
                    <select id="subcategory_id" class="form-control">
                        <option value="">All Subcategories</option>
                        @foreach ($subcategories as $subcategory)
                            <option value="{{ $subcategory->id }}">{{ $subcategory->name }}</option>
                        @endforeach
                    </select>
                </div> --}}
                <div class="col-md-2 mb-2">
                    <input type="text" id="reportrange" class="form-control"
                        style="background: white; cursor: pointer;" />
                </div>
                <div class="col-md-1 mb-2">
                    <button id="reset-filters" class="btn btn-secondary">Reset</button>
                </div>
            </div>

            <!-- Table -->
            <div class="col-lg-12">
                <div class="table-responsive rounded mb-3">
                    <table class="table table-striped table-bordered nowrap" id="stock-table">
                        <thead class="bg-white">
                            <tr class="ligth ligth-data">
                                <th>Sr. No.</th>
                                <th>Branch</th>
                                <th>Product</th>
                                <th>Barcode</th>
                                <th>Category</th>
                                <th>MRP</th>
                                <th>Selling Price</th>
                                <th>Cost Price</th>
                                <th>Qty</th>
                                <th>Sold</th>
                                <th>Total Stock Value</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="8" class="text-right">Total Quantity:</th>
                                <th id="total-qty"></th>
                                <th id="total-sold"></th>
                                <th id="total-price"></th>
                            </tr>
                            <tr>
                                <th colspan="5" class="text-right">Selling Total:</th>
                                <th colspan="6" id="selling-total" class="text-left"></th>
                            </tr>
                            <tr>
                                <th colspan="5" class="text-right">Total Stock Value:</th>
                                <th colspan="6" id="purchase-total" class="text-left"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        var pdfLogo = "";
        $(document).ready(function() {
            const start = moment().startOf('month');
            const end = moment();

            $('#reportrange').daterangepicker({
                startDate: start,
                endDate: end,
                locale: {
                    format: 'YYYY-MM-DD'
                },
                autoUpdateInput: true,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')]
                }
            });

            // ✅ Trigger only when Apply is clicked
            $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                    'YYYY-MM-DD'));
                refreshData();
            });


            let table;

            function moneyRenderer() {
                return function(data, type) {
                    const num = parseFloat(data || 0);
                    return (type === 'sort' || type === 'type') ? num : '₹' + num.toFixed(2);
                };
            }

            function loadData(filters = {}) {
                $.ajax({
                    url: '{{ route('sales.fetch-stock-data') }}',
                    type: 'GET',
                    data: filters,
                    success: function(response) {
                        if (table) {
                            table.clear().rows.add(response.data).draw();
                        } else {
                            initializeDataTable(response.data);
                        }
                    },
                    error: function(xhr) {
                        alert('Failed to load data.');
                    }
                });
            }

            function initializeDataTable(data) {
                table = $('#stock-table').DataTable({
                    data: data,
                    columns: [{
                            data: null,
                            render: (d, t, r, m) => m.row + 1
                        },
                        {
                            data: 'branch_name'
                        },
                        {
                            data: 'product_name'
                        },
                        {
                            data: 'barcode',
                            orderable: false
                        },
                        {
                            data: 'category_name'
                        },
                        {
                            data: 'mrp',
                            render: moneyRenderer(),
                            className: 'text-end'
                        },
                        {
                            data: 'selling_price',
                            render: moneyRenderer(),
                            className: 'text-end'
                        },
                        {
                            data: 'cost_price',
                            render: moneyRenderer(),
                            className: 'text-end'
                        },
                        {
                            data: 'all_qty',
                            render: d => parseInt(d || 0),
                            className: 'text-end'
                        },
                        {
                            data: 'sold_stock',
                            render: d => parseInt(d || 0),
                            className: 'text-end'
                        },
                        {
                            data: 'all_price',
                            render: moneyRenderer(),
                            className: 'text-end'
                        }
                    ],
                    language: {
                        search: "",
                        lengthMenu: "_MENU_"
                    },
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "All"]
                    ],
                    pageLength: 25,
                    dom: "<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'Bf l>>t<'row'<'col-md-6'i><'col-md-6'p>>",
                    initComplete: function() {
                        $('.dataTables_filter input').attr("placeholder", "Search List...");
                    },
                    buttons: [{
                        extend: 'collection',
                        text: '<i class="fa fa-download"></i>',
                        className: 'btn btn-info btn-sm',
                        autoClose: true,
                        buttons: [{
                                extend: 'excelHtml5',
                                text: '<i class="fa fa-file-excel-o"></i> Excel',
                                title: 'Stock Summary',
                                filename: 'stock_summary',
                                exportOptions: {
                                    columns: ':visible'
                                }
                            },
                            {
                                extend: 'pdfHtml5',
                                text: '<i class="fa fa-file-pdf-o"></i> PDF',
                                filename: 'stock_summary',
                                orientation: 'landscape',
                                pageSize: 'A4',

                                exportOptions: {
                                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                                },

                                customize: function(doc) {

                                    // remove default title
                                    doc.content.splice(0, 1);

                                    doc.pageMargins = [20, 60, 20, 30];
                                    doc.defaultStyle.fontSize = 8;

                                    // ✅ Correct width count (11 columns)
                                    doc.content[0].table.widths = [
                                        25, // Sr
                                        70, // Branch
                                        150, // Product (larger)
                                        90, // Barcode
                                        70, // Category
                                        50, // MRP
                                        60, // Selling
                                        60, // Cost
                                        35, // Qty
                                        35, // Sold
                                        70 // Stock Value
                                    ];

                                    // header style
                                    doc.styles.tableHeader = {
                                        alignment: 'center',
                                        bold: true,
                                        fontSize: 10,
                                        fillColor: '#34495E',
                                        color: 'white'
                                    };


                                    var body = doc.content[0].table.body;

                                    for (var i = 1; i < body.length; i++) {

                                        body[i][0].alignment = 'center';
                                        body[i][1].alignment = 'left';
                                        body[i][2].alignment = 'left';
                                        body[i][3].alignment = 'left';
                                        body[i][4].alignment = 'left';

                                        body[i][5].alignment = 'right';
                                        body[i][6].alignment = 'right';
                                        body[i][7].alignment = 'right';
                                        body[i][8].alignment = 'right';
                                        body[i][9].alignment = 'right';
                                        body[i][10].alignment = 'right';
                                    }

                                    var totalQty = $('#total-qty').text();
                                    var totalSold = $('#total-sold').text();
                                    var totalPrice = $('#total-price').text();
                                    var sellingTotal = $('#selling-total').text();
                                    var purchaseTotal = $('#purchase-total').text();

                                    // Total row
                                    doc.content[0].table.body.push([{
                                            text: 'Total Quantity:',
                                            colSpan: 8,
                                            alignment: 'right',
                                            bold: true
                                        }, {}, {}, {}, {}, {}, {}, {},
                                        {
                                            text: totalQty,
                                            alignment: 'right',
                                            bold: true
                                        },
                                        {
                                            text: totalSold,
                                            alignment: 'right',
                                            bold: true
                                        },
                                        {
                                            text: totalPrice,
                                            alignment: 'right',
                                            bold: true
                                        }
                                    ]);

                                    // Selling total
                                    doc.content[0].table.body.push([{
                                            text: 'Selling Total:',
                                            colSpan: 10,
                                            alignment: 'right',
                                            bold: true
                                        }, {}, {}, {}, {}, {}, {}, {}, {}, {},
                                        {
                                            text: sellingTotal,
                                            alignment: 'right',
                                            bold: true
                                        }
                                    ]);

                                    // Purchase total
                                    doc.content[0].table.body.push([{
                                            text: 'Purchase Total:',
                                            colSpan: 10,
                                            alignment: 'right',
                                            bold: true
                                        }, {}, {}, {}, {}, {}, {}, {}, {}, {},
                                        {
                                            text: purchaseTotal,
                                            alignment: 'right',
                                            bold: true
                                        }
                                    ]);

                                    // header section
                                    doc.content.unshift({
                                        margin: [0, 0, 0, 12],
                                        columns: [{
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
                                                text: 'Stock Summary',
                                                alignment: 'center',
                                                fontSize: 16,
                                                bold: true,
                                                margin: [0, 8, 0, 0]
                                            },
                                            {
                                                width: '33%',
                                                text: 'Generated: ' + new Date()
                                                    .toLocaleString(),
                                                alignment: 'right',
                                                fontSize: 9,
                                                margin: [0, 8, 0, 0]
                                            }
                                        ]
                                    });

                                }
                            }
                        ]
                    }],
                    footerCallback: function(row, data) {
                        let totalQty = 0,
                            totalPrice = 0,
                            sellingTotal = 0,
                            purchaseTotal = 0,
                            totalSold = 0;
                        data.forEach(row => {
                            let qty = parseFloat(row.all_qty || 0);
                            let selling = parseFloat(row.selling_price || 0);
                            let cost = parseFloat(row.cost_price || 0);
                            let allPrice = parseFloat(row.all_price || 0);
                            let sold = parseFloat(row.out_qty || 0);

                            totalQty += qty;
                            totalPrice += allPrice;
                            sellingTotal += selling * qty;
                            purchaseTotal += cost * qty;
                            totalSold += sold;
                        });
                        $('#total-qty').html(totalQty);
                        $('#total-sold').html(totalSold);

                        $('#total-price').html('₹' + totalPrice.toFixed(2));
                        $('#selling-total').html('₹' + sellingTotal.toFixed(2));
                        $('#purchase-total').html('₹' + purchaseTotal.toFixed(2));
                    }
                });

                $('#store_id, #product_id, #category_id, #subcategory_id').change(refreshData);
                $('#reset-filters').click(function() {
                    $('#store_id, #product_id, #category_id, #subcategory_id').val('');
                    // Reset daterangepicker to default range (e.g., this month)
                    const start = moment().startOf('month');
                    const end = moment();

                    $('#reportrange')
                        .data('daterangepicker')
                        .setStartDate(start);
                    $('#reportrange')
                        .data('daterangepicker')
                        .setEndDate(end);
                    $('#reportrange').val(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));

                    refreshData();
                });
            }

            function refreshData() {
                loadData({
                    store_id: $('#store_id').val(),
                    product_id: $('#product_id').val(),
                    category_id: $('#category_id').val(),
                    subcategory_id: $('#subcategory_id').val(),
                    date_range: $('#reportrange').val()
                });
            }

            loadData(); // initial load
        });

        function getBase64Image(url, callback) {
            var img = new Image();
            img.crossOrigin = "Anonymous";

            img.onload = function() {
                var canvas = document.createElement("canvas");
                canvas.width = this.width;
                canvas.height = this.height;

                var ctx = canvas.getContext("2d");
                ctx.drawImage(this, 0, 0);

                var dataURL = canvas.toDataURL("image/png");
                callback(dataURL);
            };

            img.src = url;
        }

        getBase64Image("https://liquorhub.in/assets/images/logo.png", function(base64) {
            pdfLogo = base64;
        });
    </script>
@endsection
