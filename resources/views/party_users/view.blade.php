@extends('layouts.backend.datatable_layouts')
@section('page-content')
    <div class="content-page">
        <div class="container-fluid add-form-list">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between mb-2">
                <div>
                    <h4 class="mb-0">Party Customer Information</h4>
                </div>
                <a href="{{ route('party-users.list') }}" class="btn btn-secondary">Back</a>
            </div>
            <div class="row">
                <div class="col-sm-12">

                    <div class="card-body">
                        <div class="container-fluid">

                            <div class="card">

                                <div class="card-body">
                                    <ul class="nav nav-pills mb-3 nav-fill" id="pills-tab-1" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="pills-home-tab-fill" data-toggle="pill"
                                                href="#pills-home-fill" role="tab" aria-controls="pills-home"
                                                aria-selected="true"><span class="text-dark">Customer Info</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link " id="pills-profile-tab-fill" data-toggle="pill"
                                                href="#pills-profile-fill" role="tab" aria-controls="pills-profile"
                                                aria-selected="false"><span class="text-dark">Transaction
                                                    History</span></a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="pills-contact-tab-fill" data-toggle="pill"
                                                href="#pills-contact-fill" role="tab" aria-controls="pills-profile"
                                                aria-selected="false"><span class="text-dark">Credit
                                                    History</span></a>
                                        </li>

                                    </ul>
                                    <div class="tab-content" id="pills-tabContent-1">
                                        <div class="tab-pane fade show active" id="pills-home-fill" role="tabpanel"
                                            aria-labelledby="pills-home-tab-fill">
                                            <div class="card card-block card-stretch card-height">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <div class="d-flex align-items-center mb-3">

                                                                <div class="ml-3">
                                                                    <h4 class="mb-1">{{ $partyUser->first_name }}
                                                                </div>
                                                            </div>

                                                            <ul class="list-inline p-0 m-0">
                                                                <li class="mb-2">
                                                                    <div class="d-flex align-items-center">
                                                                        <svg class="svg-icon mr-3" height="16"
                                                                            width="16"
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            fill="none" viewBox="0 0 24 24"
                                                                            stroke="currentColor">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
                                                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
                                                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                        </svg>
                                                                        <p class="mb-0">{{ $partyUser->address }}</p>
                                                                    </div>
                                                                </li>

                                                                <li class="mb-2">
                                                                    <div class="d-flex align-items-center">
                                                                        <svg class="svg-icon mr-3" height="16"
                                                                            width="16"
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            fill="none" viewBox="0 0 24 24"
                                                                            stroke="currentColor">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
                                                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                                        </svg>
                                                                        <p class="mb-0">{{ $partyUser->phone }}</p>
                                                                    </div>
                                                                </li>
                                                                <li>
                                                                    <div class="d-flex align-items-center">
                                                                        <svg class="svg-icon mr-3" height="16"
                                                                            width="16"
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            fill="none" viewBox="0 0 24 24"
                                                                            stroke="currentColor">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
                                                                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                                        </svg>
                                                                        <p class="mb-0">{{ $partyUser->email }}</p>
                                                                    </div>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="col-lg-6">


                                                            <ul class="list-inline p-0 m-0">
                                                                <li class="mb-2">
                                                                    <div class="d-flex align-items-center">
                                                                        <strong>Total Credit:-</strong>
                                                                        <p class="mb-0">
                                                                            {{ format_inr($partyUser->credit_points) }}
                                                                        </p>
                                                                    </div>
                                                                </li>

                                                                <li class="mb-2">
                                                                    <div class="d-flex align-items-center">
                                                                        <strong>Remaining Credit:-</strong>
                                                                        <p class="mb-0">
                                                                            {{ format_inr($partyUser->left_credit) }}
                                                                        </p>
                                                                    </div>

                                                                </li>

                                                                <li class="mb-2">
                                                                    <div class="d-flex align-items-center">
                                                                        <strong>Used Credit:-</strong>
                                                                        <p class="mb-0">
                                                                            {{ format_inr($partyUser->use_credit) }}
                                                                        </p>
                                                                    </div>

                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="pills-profile-fill" role="tabpanel"
                                            aria-labelledby="pills-profile-tab-fill">
                                            <div class="table-responsive rounded mb-3">
                                                <table class="table data-tables table-striped" id="cust_party_his_table"
                                                    style="width: 100%">

                                                    <thead class="bg-white text-uppercase">

                                                        <tr class="ligth ligth-data">
                                                            <th>Transaction Number</th>
                                                            <th>Transaction Date</th>
                                                            <th>Credit Amount</th>
                                                            <th>Transaction Total</th>
                                                            <th>Photos</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="ligth-body">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="pills-contact-fill" role="tabpanel"
                                            aria-labelledby="pills-contact-tab-fill">
                                            <div class="table-responsive rounded mb-3">
                                                <table class="table data-tables table-striped" id="credit_history_table"
                                                    style="width: 100%">

                                                    <thead class="bg-white text-uppercase">

                                                        <tr class="ligth ligth-data">
                                                            <th>Transaction Number</th>
                                                            <th>Transaction Date</th>
                                                            <th>Credit Amount</th>
                                                            <th>Debit Amount</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="ligth-body">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Page end -->
        </div>
    </div>


    <div class="modal fade bd-example-modal-lg" id="partyCustPhotoShowModal" tabindex="-1" role="dialog"
        aria-labelledby="partyCustPhotoShowModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="partyCustPhotoModalContent">
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            alert('asasdasd');
            const customer_id = {{ $partyUser->id }};

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ================= TRANSACTION HISTORY =================
            if ($.fn.DataTable.isDataTable('#cust_party_his_table')) {
                $('#cust_party_his_table').DataTable().destroy();
            }

            $('#cust_party_his_table').DataTable({
                pageLength: 10,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ url('party-users/get-commission-data') }}',
                    type: 'POST',
                    data: function(d) {
                        d.customer_id = customer_id;
                    }
                },
                columns: [{
                        data: 'invoice_number'
                    },
                    {
                        data: 'invoice_date'
                    },
                    {
                        data: 'credit_amount'
                    },
                    {
                        data: 'invoice_total'
                    },
                    {
                        data: 'image_path',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return data ?
                                `<span class="badge bg-danger" onclick="showPhoto(${row.transaction_id})" style="cursor:pointer;">Show</span>` :
                                `<span class="badge bg-success">Paid</span>`;
                        }
                    },
                    {
                        data: 'status',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            if (parseFloat(row.credit_amount) === 0) {
                                return `<span class="badge bg-info">-</span>`;
                            } else if (row.status === 'unpaid') {
                                return `<span class="badge bg-danger" onclick="payCredit(${row.commission_id})" style="cursor:pointer;">Unpaid</span>`;
                            } else {
                                return `<span class="badge bg-success">Paid</span>`;
                            }
                        }
                    }
                ],
                order: [
                    [1, 'desc']
                ]
            });

            // ================= CREDIT HISTORY =================
            if ($.fn.DataTable.isDataTable('#credit_history_table')) {
                $('#credit_history_table').DataTable().destroy();
            }

            $('#credit_history_table').DataTable({
                pageLength: 10,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ url('party-users/get-credit-history') }}',
                    type: 'POST',
                    data: function(d) {
                        d.customer_id = customer_id;
                    }
                },
                columns: [{
                        data: 'invoice_number'
                    },
                    {
                        data: 'invoice_date',
                        render: function(data, type, row) {
                            return data ?? row.created_at;
                        }
                    },
                    {
                        data: 'credit_amount'
                    },
                    {
                        data: 'debit_amount'
                    }
                ],
                order: [
                    [1, 'desc']
                ]
            });

        });

        // ================= FUNCTIONS =================

        function showPhoto(id) {
            $.ajax({
                url: '/cust-trasaction-photo/view/' + id,
                type: 'GET',
                success: function(response) {
                    $('#partyCustPhotoModalContent').html(response);
                    new bootstrap.Modal(document.getElementById('partyCustPhotoShowModal')).show();
                }
            });
        }

        function payCredit(id) {
            alert("Pay Credit ID: " + id);
        }
    </script>
@endpush
