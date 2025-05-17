@extends('layouts.backend.layouts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
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
                                <h2 class="mb-3">Notifications List</h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="table-responsive rounded mb-3">
                            <table class="table data-tables table-striped" id="notification_table">
                                <thead class="bg-white text-uppercase">
                                    <tr class="ligth ligth-data">
                                        <th>
                                            Type
                                        </th>
                                        <th>Content</th>
                                        <th>Notify By </th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white text-uppercase">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Page end  -->
            </div>
        </div>
    </div>
    <!-- Wrapper End-->

    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#notification_table').DataTable().clear().destroy();

            $('#notification_table').DataTable({
                pageLength: 10, // default number of rows per page
                responsive: true,
                processing: true,
                ordering: true,
                lengthChange: true, // shows the dropdown to select limit
                serverSide: true,

                ajax: {
                    url: '{{ url('notifications/fetch-data') }}',
                    type: 'POST',
                    data: function(d) {}
                },

                columns: [{
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

                // Disable sorting on columns 0, 1, 2 (type, content, created_by)
                columnDefs: [{
                    orderable: false,
                    targets: [0, 1, 2]
                }],

                dom: "Bfrtip",
                
                lengthMenu: [
                    [10, 25, 50, -1],
                    ['10 rows', '25 rows', '50 rows', 'All']
                ],
                buttons: ['pageLength']
            });


        });
    </script>
@endsection
