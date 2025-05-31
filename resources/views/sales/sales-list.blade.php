@extends('layouts.backend.layouts')

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<!-- Buttons CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<!-- Buttons JS -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<!-- Export Dependencies -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>


@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Wrapper Start -->
    <div class="wrapper">

        <div class="content-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Trasaction List</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <input type="date" id="start_date" class="form-control">
                    </div>
                    <div class="col-md-3 mb-2">
                        <input type="date" id="end_date" class="form-control">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select id="branch_id" class="form-control">
                            <option value="">All Branches</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-primary w-100" id="storeSearch">Search</button>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="table-responsive rounded mb-3">
                        <table class="table data-tables table-striped" id="invoice_table">
                            <thead class="bg-white text-uppercase">
                                <tr class="ligth ligth-data">
                                    <th>Trasaction #</th>
                                    <th>Status</th>
                                    <th>Photo</th>
                                    <th>Commission Amount</th>
                                    <th>Sub Total</th>
                                    <th>Party Dicount</th>
                                    <th>Total</th>
                                    <th>Item Count</th>
                                    <th>Store</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody class="ligth-body">
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" style="text-align:right">Total:</th>
                                    <th id="commission_total"></th>
                                    <th id="party_total"></th>
                                    <th id="sub_total_total"></th>
                                    <th id="grand_total"></th>
                                    <th id="item_count_total"></th>
                                    <th></th>
                                </tr>
                            </tfoot>

                        </table>
                        </ </div>
                    </div>
                    <!-- Page end  -->
                </div>
            </div>
            <!-- Wrapper End-->
        </div>
    </div>
     <div class="modal fade bd-example-modal-lg" id="salesCustPhotoShowModal" tabindex="-1" role="dialog"
        aria-labelledby="salesCustPhotoShowModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="salesCustPhotoModalContent">
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#invoice_table').DataTable().clear().destroy();

            let table = $('#invoice_table').DataTable({
                pagelength: 10,
                responsive: true,
                processing: true,
                ordering: true,
                serverSide: true,
                autoWidth: false,
                bLengthChange: true,
                order: [
                    [1, 'desc']
                ], // Sort by created_at
                columnDefs: [{
                        targets: [1, 2, 3, 4, 5, 6],
                        orderable: false
                    }, // Disable sorting for these columns
                ],
                dom: 'Blfrtip',
                buttons: [
                    'pageLength',
                    {
                        extend: 'csvHtml5',
                        text: 'Export CSV',
                        className: 'btn btn-sm btn-outline-primary',
                        title: 'Transaction Report',
                        filename: 'transaction_report_csv',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: 'Export Excel',
                        className: 'btn btn-sm btn-outline-success',
                        title: 'Transaction Report',
                        filename: 'transaction_report_excel',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Export PDF',
                        className: 'btn btn-sm btn-outline-danger',
                        title: 'Transaction Report',
                        filename: 'transaction_report_pdf',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                ajax: {
                    url: '{{ url('sales/get-data') }}',
                    type: 'POST',
                    data: function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.branch_id = $('#branch_id').val();
                    }
                },
                columns: [{
                        data: 'invoice_number',
                        name: 'invoice_number'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                        {
                        data: 'photo',
                        name: 'photo',
                        orderable: false,
                        searchable: false
                    },
                 
                    {
                        data: 'commission_amount',
                        name: 'commission_amount',
                        render: data => '₹' + parseFloat(data.replace(/,/g, '')).toFixed(2)
                    },
                    {
                        data: 'sub_total',
                        name: 'sub_total',
                        render: data => '₹' + parseFloat(data.replace(/,/g, '')).toFixed(2)
                    },
                    {
                        data: 'party_amount',
                        name: 'party_amount',
                        render: data => '₹' + parseFloat(data.replace(/,/g, '')).toFixed(2)
                    },
                    {
                        data: 'total',
                        name: 'total',
                        render: data => '₹' + parseFloat(data.replace(/,/g, '')).toFixed(2)
                    },
                    {
                        data: 'items_count',
                        name: 'items_count'
                    },
                    {
                        data: 'branch_name',
                        name: 'branch_name'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    }
                ],
                footerCallback: function(row, data) {
                    var api = this.api();

                    const intVal = i => typeof i === 'string' ?
                        parseFloat(i.replace(/[₹,]/g, '')) || 0 :
                        typeof i === 'number' ?
                        i :
                        0;

                    let commission = 0,
                        party = 0,
                        subtotal = 0,
                        total = 0,
                        items = 0;

                    data.forEach(row => {
                        commission += intVal(row.commission_amount);
                        party += intVal(row.party_amount);
                        subtotal += intVal(row.sub_total);
                        total += intVal(row.total);
                        items += intVal(row.items_count);
                    });

                    $(api.column(2).footer()).html('₹' + commission.toFixed(2));
                    $(api.column(3).footer()).html('₹' + subtotal.toFixed(2));
                    $(api.column(4).footer()).html('₹' + party.toFixed(2));
                    $(api.column(5).footer()).html('₹' + total.toFixed(2));
                    $(api.column(6).footer()).html(items);
                }
            });

            $('#storeSearch').on('click', function() {
                table.draw();
            });
        });
         const salesImgViewBase = "{{ url('sales-img-view') }}";

        function showPhoto(id,commission_user_id='',party_user_id='',invoice_no='') {
            let url = `${salesImgViewBase}/${id}?commission_user_id=${commission_user_id}&party_user_id=${party_user_id}&invoice_no=${invoice_no}`;

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    $('#salesCustPhotoModalContent').html(response);
                    $('#salesCustPhotoShowModal').modal('show');
                },
                error: function() {
                    alert('Photos not found.');
                }
            });
        }
    </script>
@endsection
