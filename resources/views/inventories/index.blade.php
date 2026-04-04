@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <div class="content-page">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row align-items-center mb-2">
                <div class="col-lg-12">
                    <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0">Stock Inventory</h4>
                        </div>
                        <div class="col-md-5">
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-0">
                                <select name="storeSearch" id="storeSearch" class="form-control">
                                    <option value="">Select All Store</option>
                                    @foreach ($branch as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <select name="subCategorySearch" id="subCategorySearch" class="form-control">
                                    <option value="">Select All Sub Category</option>
                                    @foreach ($subcategories as $id => $name)
                                        <option value="{{ $name->id }}">{{ $name->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-12">

                    <div class="table-responsive rounded">
                        <table class="table table-striped table-bordered nowrap" id="inventory_table">
                            <thead class="bg-white">
                                <tr class="ligth ligth-data">
                                    <th>Sr No</th>
                                    <th>Product</th>
                                    <th>Store</th>
                                    <th>In-Stock</th>
                                    <th>Sales Price</th>
                                    <th>Stock Low Level</th>
                                    <th>Last updated</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" style="text-align:right">Total:</th>
                                    <th id="total_stock" class="text-center"></th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Wrapper End-->
    <!-- Low Level Modal -->
    <div class="modal fade bd-example-modal-lg" id="lowLevelModal" tabindex="-1" role="dialog"
        aria-labelledby="lowLevelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="lowLevelStockUpdateForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="lowLevelModalLabel">Stock Low Level Set</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="product_id" id="product_id">
                            <input type="hidden" name="store_id" id="store_id">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Low Level Quantity</label>
                                    <input type="number" name="low_level_qty" class="form-control" id="low_level_qty"
                                        placeholder="Enter Low Level Quantity">
                                    <span class="text-danger" id="low_level_qty_error"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        var pdfLogo = "";
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Check if DataTable is already initialized and destroy it to prevent multiple initializations
            if ($.fn.dataTable.isDataTable('#inventory_table')) {
                $('#inventory_table').DataTable().destroy();
            }

            // Initialize DataTable for products table
            var table = $('#inventory_table').DataTable({
                pageLength: 10,
                responsive: true,
                processing: true,
                ordering: true,
                bLengthChange: true,
                serverSide: true,
                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },
                "ajax": {
                    "url": '{{ url('inventories/get-data') }}',
                    "type": "POST",
                    data: function(d) {
                        d.store_id = $('#storeSearch').val();
                        d.sub_category_id = $('#subCategorySearch').val(); // Add subcategory filter
                    }
                },
                dom: "<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'Bf l>>t<'row'<'col-md-6'i><'col-md-6'p>>",
                initComplete: function() {
                    $('.dataTables_filter input').attr("placeholder", "Search List...");
                },
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();

                    var total = api
                        .column(3, {
                            search: 'applied'
                        }) // In-Stock column index
                        .data()
                        .reduce(function(a, b) {
                            return parseFloat(a) + parseFloat(b);
                        }, 0);

                    $('#total_stock').html(total);
                },
                aoColumns: [{
                        data: null,
                        name: 'sr_no',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    }, {
                        data: 'name',
                        orderable: false
                    },
                    {
                        data: 'location',
                        orderable: false
                    },
                    {
                        data: 'quantity',
                        orderable: false,
                        className: "text-center"
                    },
                    // {
                    //     data: 'cost_price',
                    //     orderable: false,
                    //     className: "text-center"
                    // },
                    // {
                    //     data: 'discount_price',
                    //     orderable: false,
                    //     className: "text-center"
                    // },
                    // {
                    //     data: 'batch_no',
                    //     orderable: false
                    // },

                    // {
                    //     data: 'barcode',
                    //     orderable: false
                    // },
                    {
                        data: 'sell_price',
                        orderable: false,
                        className: "text-center"
                    },
                    // {
                    //     data: 'expiry_date',
                    //     orderable: true
                    // },
                    {
                        data: 'reorder_level',
                        orderable: false,
                        className: "text-center"
                    },
                    {
                        data: 'updated_at',
                        orderable: true
                    }
                ],
                columnDefs: [{
                        width: "3%",
                        targets: 0
                    },
                    {
                        width: "7%",
                        targets: 1
                    },
                    {
                        width: "5%",
                        targets: 2
                    },
                    {
                        width: "5%",
                        targets: 3
                    },
                    {
                        width: "5%",
                        targets: 4
                    },
                    {
                        width: "5%",
                        targets: 5
                    },
                    {
                        width: "5%",
                        targets: 6
                    }
                ],
                autoWidth: false,
                order: [
                    [6, 'desc']
                ], // Order by updated_at
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                buttons: [{
                    extend: 'collection',
                    text: '<i class="fa fa-download"></i>',
                    className: 'btn btn-info btn-sm',
                    autoClose: true,
                    buttons: [{
                            extend: 'excelHtml5',
                            text: 'Excel',
                            title: 'Stock Inventory',
                            filename: 'stock_inventory',

                            customize: function(xlsx) {
                                var sheet = xlsx.xl.worksheets['sheet1.xml'];

                                var total = $('#total_stock').text();

                                var lastRow = $('row', sheet).length + 1;

                                var totalRow = `
                                        <row r="${lastRow}">
                                            <c t="inlineStr" r="A${lastRow}"><is><t>Total</t></is></c>
                                            <c t="inlineStr" r="D${lastRow}"><is><t>${total}</t></is></c>
                                        </row>
                                    `;

                                $('sheetData', sheet).append(totalRow);
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf-o"></i> PDF',
                            filename: 'stock_inventory',
                            orientation: 'landscape',
                            pageSize: 'A4',

                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6]
                            },

                            customize: function(doc) {

                                doc.content.splice(0, 1);
                                var total = $('#total_stock').text();

                                // Add total row at end
                                var tableBody = doc.content[0].table.body;

                                tableBody.push([{
                                        text: 'Total',
                                        colSpan: 3,
                                        alignment: 'right',
                                        bold: true
                                    }, {}, {},
                                    {
                                        text: total,
                                        alignment: 'center',
                                        bold: true
                                    },
                                    {}, {}, {}
                                ]);


                                var headerColumns = [];

                                // ✅ Only add image if available
                                if (pdfLogo && pdfLogo !== "") {
                                    headerColumns.push({
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
                                    });
                                } else {
                                    headerColumns.push({
                                        width: '33%',
                                        text: 'LiquorHub',
                                        fontSize: 11,
                                        bold: true,
                                        margin: [0, 8, 0, 0]
                                    });
                                }

                                headerColumns.push({
                                    width: '34%',
                                    text: 'Stock Inventory Report',
                                    alignment: 'center',
                                    fontSize: 16,
                                    bold: true,
                                    margin: [0, 8, 0, 0]
                                });

                                headerColumns.push({
                                    width: '33%',
                                    text: 'Generated: ' + new Date()
                                        .toLocaleString(),
                                    alignment: 'right',
                                    fontSize: 9,
                                    margin: [0, 8, 0, 0]
                                });

                                doc.content.unshift({
                                    margin: [0, 0, 0, 12],
                                    columns: headerColumns
                                });
                            }
                        }
                    ]
                }],

            });

            // Change store filter
            $('#storeSearch').on('change', function() {
                table.ajax.reload(null, false); // Reload DataTable with the new filter value
            });

            // Change subcategory filter
            $('#subCategorySearch').on('change', function() {
                table.ajax.reload(null, false); // Reload DataTable with the new filter value
            });

            // Submit low level form
            $('#lowLevelStockUpdateForm').on('submit', function(e) {
                e.preventDefault();
                $('#low_level_qty_error').text('');

                let formData = {
                    _token: $('input[name="_token"]').val(),
                    product_id: $('#product_id').val(),
                    store_id: $('#store_id').val(),
                    low_level_qty: $('#low_level_qty').val(),
                };

                $.ajax({
                    type: "POST",
                    url: "{{ route('inventories.update-low-level-qty') }}",
                    data: formData,
                    success: function(response) {
                        var modalEl = document.getElementById('lowLevelModal');
                        var modal = modalEl._modalInstance || new bootstrap.Modal(modalEl);
                        modal.hide();
                        modalEl._modalInstance = modal;
                        alert(response.message);

                        $('#lowLevelStockUpdateForm')[0].reset();
                        location.reload();
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            if (errors.low_level_qty) {
                                $('#low_level_qty_error').text(errors.low_level_qty[0]);
                            }
                        } else {
                            alert("An unexpected error occurred.");
                        }
                    }
                });
            });
        });

        function low_level_stock_set(p_id, branch_id, reorder_level) {
            $('#product_id').val(p_id);
            $('#store_id').val(branch_id);
            $('#low_level_qty').val(reorder_level);
            // Check if Bootstrap 5 (without jQuery) is being used
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var myModal = new bootstrap.Modal(document.getElementById('lowLevelModal'));
                myModal.show();
            } else {
                // For Bootstrap 4 (with jQuery)
                $('#lowLevelModal').modal('show');
            }
        }

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
