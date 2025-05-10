@extends('layouts.backend.layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qz-tray/qz-tray.js"></script>


    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">


                <!-- Date Filters -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Credit History</h4>
                                <div class="btn"><a href="#" onClick="openCashDrawer()">print</a></div>

                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                    </div>
                    <div class="col-md-3 mb-2">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select id="customer_id" class="form-control">
                            <option value="">All Party Customer</option>
                            @foreach ($party_users as $cus)
                                <option value="{{ $cus->id }}">{{ $cus->first_name }} {{ $cus->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-primary w-100" id="filter">Search</button>
                    </div>
                </div>

                <!-- Table -->
                <div class="col-lg-12">
                    <div class="table-responsive rounded mb-3">
                        <table class="table table-striped" id="stock-table" style="width:100%">
                            <thead class="bg-white text-uppercase">
                                <tr>
                                    <th>Trasaction Number</th>
                                    <th>Trasaction Date</th>
                                    <th>commission Amount</th>
                                    <th>Trasaction Total</th>
                                    <th>Customer Name</th>
                                    <th>Status</th>

                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- Wrapper End -->

    <div class="modal fade bd-example-modal-lg" id="payCreditModal" tabindex="-1" role="dialog"
        aria-labelledby="payCreditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="priceUpdateForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="payCreditModalLabel">Product Price Change</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="product_id" id="product_id" value="">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Old Price </label>
                                    <input type="text" name="old_price" class="form-control" id="old_price">
                                    <span class="text-danger" id="old_price_error"></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>New Price</label>
                                    <input type="text" name="new_price" class="form-control" id="new_price">
                                    <span class="text-danger" id="new_price_error"></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Price Apply Date</label>
                                    <input type="date" name="changed_at" min="" class="form-control"
                                        id="changed_at">
                                    <span class="text-danger" id="changed_at_error"></span>
                                </div>
                            </div>
                        </div>

                        <span class="mt-2 badge badge-pill border border-secondary text-secondary">
                            {{ __('messages.change_date_msg') }}
                        </span>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var table = $('#stock-table').DataTable({
                processing: true,
                serverSide: false, // if your data is small; otherwise true
                ajax: {
                    url: '{{ route('sales.fetch-commission-data') }}',
                    data: function(d) {
                        d.customer_id = $('#customer_id').val(); // send selected branch_id
                    }
                },
                columns: [{
                        data: 'invoice_number',
                        name: 'invoice_number'
                    },
                    {
                        data: 'invoice_date',
                        name: 'invoice_date'
                    },
                    {
                        data: 'commission_amount',
                        name: 'commission_amount'
                    },
                    {
                        data: 'invoice_total',
                        name: 'invoice_total'
                    },

                    {
                        data: 'commission_user_name',
                        name: 'commission_user_name'
                    },
                    {
                        data: null,
                        render: function(data, type, row) {

                            if (row.status == 'unpaid') {
                                return '<span class="badge bg-danger"><a href="#" onClick="payCredit(' +
                                    data.commission_id + ')">Unpaid</a></span>';
                            } else {
                                return '<span class="badge bg-success">Paid</span>';
                            }
                        },
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            // When filter button is clicked
            $('#filter').click(function() {
                table.ajax.reload();
            });
        });

        function payCredit(id) {

            $('#commission_id').val(id);
            $('#payCreditModal').modal('show');
        }

    </script>
@endsection
