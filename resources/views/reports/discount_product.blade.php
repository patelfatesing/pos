{{-- resources/views/reports/discount_product.blade.php --}}
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

        /* single-line, no-scroll filter row */
        .filters.one-line {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: nowrap;
            overflow: hidden;
            /* no scrollbars */
        }

        .filters.one-line .form-control {
            flex: 0 1 140px;
            /* shrinkable, ~140px base */
            min-width: 110px;
            /* don’t get too tiny */
        }

        /* wider (but still shrinkable) for user selects */
        #party_user_id,
        #commission_user_id {
            flex: 0 1 220px;
            min-width: 150px;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }

        /* optional: tighten spacing further on smaller widths without wrapping */
        @media (max-width: 1280px) {
            .filters.one-line .form-control {
                flex-basis: 120px;
                min-width: 100px;
            }

            #party_user_id,
            #commission_user_id {
                flex-basis: 200px;
                min-width: 140px;
            }
        }
    </style>
@endsection

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h4 class="mb-0">Product-wise Discount Report</h4>
                </div>

                <div class="table-responsive rounded">
                    <table class="table table-striped table-bordered nowrap" id="discount_product_table" style="width:100%;">
                        <thead class="bg-white">
                            <tr class="ligth ligth-data">
                                <th>Sr No</th>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Qty</th>
                                <th>Gross</th>
                                <th>Party Discount</th>
                                <th>Commission Discount</th>
                                <th>Total Discount</th>
                                <th>Net Sales</th>

                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Totals:</th>
                                <th id="ft_qty">0.00</th>
                                <th id="ft_gross">0.00</th>
                                <th id="ft_party">0.00</th>
                                <th id="ft_comm">0.00</th>
                                <th id="ft_total_disc">0.00</th>
                                <th id="ft_net">0.00</th>

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
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Build filter controls into the toolbar
            const filtersHtml = `
                <div class="filters one-line">
                    <input type="date" id="start_date" class="form-control form-control-sm" placeholder="Start date">
                    <input type="date" id="end_date"   class="form-control form-control-sm" placeholder="End date">

                    <select id="discount_scope" class="form-control form-control-sm">
                    <option value="all" selected>All</option>
                    <option value="party">Party Customer</option>
                    <option value="commission">Commission Customer</option>
                    </select>

                    <select id="party_user_id" class="form-control form-control-sm" style="display:none;">
                    <option value="">All Party Customers</option>
                    @foreach ($partyUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->first_name }}</option>
                    @endforeach
                    </select>

                    <select id="commission_user_id" class="form-control form-control-sm" style="display:none;">
                    <option value="">All Commission Customers</option>
                    @foreach ($commissionUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->first_name }}</option>
                    @endforeach
                    </select>
                </div>
                `;

            const table = $('#discount_product_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('reports.discount.product.data') }}",
                    type: 'POST',
                    data: function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.discount_scope = $('#discount_scope').val();
                        d.party_user_id = $('#party_user_id').val();
                        d.commission_user_id = $('#commission_user_id').val();
                        // if you need branch filter, add d.branch_id = $('#branch_id').val();
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
                        data: 'sku'
                    },
                    {
                        data: 'qty'
                    },
                    {
                        data: 'gross'
                    },
                    {
                        data: 'party_discount'
                    },
                    {
                        data: 'commission_disc'
                    },
                    {
                        data: 'total_discount'
                    },
                    {
                        data: 'net_sales'
                    }
                ],
                order: [
                    [8, 'desc']
                ], // Net Sales desc by default
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                dom: "<'custom-toolbar-row'lfB>t<'row mt-2'<'col-md-6'i><'col-md-6'p>>",
                buttons: [{
                        extend: 'excelHtml5',
                        className: 'btn btn-outline-success btn-sm me-2',
                        title: 'Product-wise Discount',
                        filename: 'discount_product',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-outline-danger btn-sm',
                        title: 'Product-wise Discount',
                        filename: 'discount_product',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                initComplete: function() {
                    // inject filters
                    $(filtersHtml).insertAfter('.dt-buttons');

                    // scope toggle: show correct dropdown
                    function toggleUserDropdowns() {
                        const scope = $('#discount_scope').val();
                        if (scope === 'party') {
                            $('#party_user_id').show();
                            $('#commission_user_id').hide().val('');
                        } else if (scope === 'commission') {
                            $('#party_user_id').hide().val('');
                            $('#commission_user_id').show();
                        } else {
                            $('#party_user_id, #commission_user_id').hide().val('');
                        }
                        table.ajax.reload();
                    }

                    $('#discount_scope').on('change', toggleUserDropdowns);
                    $('#party_user_id, #commission_user_id, #start_date, #end_date')
                        .on('change', function() {
                            table.ajax.reload();
                        });

                    toggleUserDropdowns();
                },
                drawCallback: function(settings) {
                    // Put GRAND totals into footer (server computed, filtered-set totals)
                    const json = settings.json || {};
                    if (json.totals) {
                        $('#ft_qty').text(json.totals.qty || '0.00');
                        $('#ft_gross').text(json.totals.gross || '0.00');
                        $('#ft_party').text(json.totals.party_discount || '0.00');
                        $('#ft_comm').text(json.totals.commission_disc || '0.00');
                        $('#ft_total_disc').text(json.totals.total_discount || '0.00');
                        $('#ft_net').text(json.totals.net_sales || '0.00');
                    }
                },
                columnDefs: [{
                    targets: '_all',
                    defaultContent: ''
                }]
            });

            $(document).on('change', '#discount_scope', function() {
                const v = this.value;
                $('#party_user_id').toggle(v === 'party').prop('disabled', v !== 'party');
                $('#commission_user_id').toggle(v === 'commission').prop('disabled', v !== 'commission');
            });
        });
    </script>
@endsection
