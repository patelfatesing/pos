@extends('layouts.backend.datatable_layouts')

@section('page-content')
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <!-- Page Header -->
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between mb-2">
                    <div>
                        <h4 class="mb-0">Notifications List</h4>
                    </div>
                </div>

                <!-- Table -->
                <div class="row mt-1">
                    <div class="col-12">
                        <div class="table-responsive rounded">

                            <table class="table table-striped table-bordered nowrap" id="notification_table">
                                <thead class="bg-white">
                                    <tr class="ligth ligth-data">
                                        <th>Sr No</th>
                                        <th>
                                            Type
                                        </th>
                                        <th>Content</th>
                                        <th>Notify By </th>
                                        <th>Status</th>
                                        <th>Created At</th>
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
        var pdfLogo = "";
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const table = $('#notification_table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                language: {
                    search: "",
                    lengthMenu: "_MENU_"
                },
                ajax: {
                    url: '{{ url('notifications/fetch-data') }}',
                    type: 'POST',
                    data: function(d) {
                        d.type = $('#type_filter').val();
                    }
                },
                dom: "<'row dt_height'<'col-md-12 d-flex justify-content-end align-items-center'Bf l>>t<'row'<'col-md-6'i><'col-md-6'p>>",

                columns: [{
                        data: null,
                        name: 'sr_no',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    }, {
                        data: 'type'
                    },
                    {
                        data: 'content'
                    },
                    {
                        data: 'created_by'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'created_at'
                    }
                ],
                order: [
                    [4, 'desc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: [0, 1, 2, 3]
                }],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                pageLength: 10,
                buttons: [],
                initComplete: function() {

                    $('.dataTables_filter input').attr("placeholder", "Search List...");

                    $('<div class="status-filter">' +
                        '<select id="type_filter" class="form-control form-control-sm">' +
                        '<option value="">All Types</option>' +
                        '<option value="low_stock">Low Stock</option>' +
                        '<option value="expire_product">Expire Product</option>' +
                        '<option value="request_stock">Stock Request</option>' +
                        '<option value="approved_stock">Approved Stock</option>' +
                        '<option value="transfer_stock">Stock Transfer</option>' +
                        '</select>' +
                        '</div>').insertBefore('.dt-buttons');

                    $('#type_filter').on('change', function() {
                        table.ajax.reload();
                    });
                }
            });
        });
    </script>
@endsection
