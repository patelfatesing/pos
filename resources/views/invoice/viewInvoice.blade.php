@extends('layouts.backend.layouts')

@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Transaction Invoice Details</h4>
                            </div>
                            <div>
                                <a href="{{ route('sales.sales.list') }}" class="btn btn-secondary">Back</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="card card-block card-stretch card-height print rounded">
                            <div class="card-header d-flex justify-content-between bg-primary header-invoice">
                                <div class="iq-header-title">
                                    <h4 class="card-title mb-0">Invoice #{{ $invoice->invoice_number }}</h4>
                                </div>
                                <div class="invoice-btn">

                                    @if ($invoice->party_user_id != '')
                                        <button
                                            onClick="showPhoto({{ $invoice->id }},{{ $invoice->party_user_id }},'{{ $invoice->invoice_number }}')"
                                            class="btn btn-primary-dark mr-2">
                                            <i class="ri-eye-line mr-0"></i> View
                                        </button>
                                    @endif
                                    @if ($invoice->commission_user_id != '')
                                        <button
                                            onClick="showPhoto({{ $invoice->id }},{{ $invoice->commission_user_id }},'{{ $invoice->invoice_number }}')"
                                            class="btn btn-primary-dark mr-2">
                                            <i class="ri-eye-line mr-0"></i> View
                                        </button>
                                    @endif

                                    <button onclick="window.print()" class="btn btn-primary-dark mr-2">
                                        <i class="las la-print"></i> Print
                                    </button>
                                    <a href="{{ route('invoice.download', $invoice->id) }}" class="btn btn-primary-dark">
                                        <i class="las la-file-download"></i> PDF
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <img src="{{ asset('assets/images/logo.png') }}"
                                            class="logo-invoice img-fluid mb-3">
                                        <h5 class="mb-0">Hello, {{ $invoice->customer_name }}</h5>
                                        <p>Thank you for your business. Below is the summary of your invoice.</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="table-responsive-sm">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Transaction Date</th>
                                                        <th scope="col">Transaction Status</th>
                                                        <th scope="col">Transaction No(Ref)</th>

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>{{ $invoice->created_at->format('Y-m-d H:i:s') }}</td>
                                                        <td>
                                                            <span
                                                                class="badge badge-{{ $invoice->status == 'Paid' ? 'success' : 'danger' }}">
                                                                {{ $invoice->status }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            {{ $invoice->invoice_number }}
                                                            @if (Str::startsWith($invoice->invoice_number, 'HOLD-'))
                                                                ({{ $invoice->created_at->format('Y-m-d H:i:s') }})
                                                            @endif
                                                        </td>

                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h5 class="mb-3">Transaction Summary</h5>
                                        <div class="table-responsive-sm">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center" scope="col">#</th>
                                                        <th scope="col">Item</th>
                                                        <th class="text-center" scope="col">Quantity</th>
                                                        <th class="text-center" scope="col">Price</th>
                                                        <th class="text-center" scope="col">Totals</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($invoice->items as $i => $item)
                                                        <tr>
                                                            <th class="text-center" scope="row">{{ $i + 1 }}
                                                            </th>
                                                            <td>
                                                                <h6 class="mb-0">{{ $item['name'] }}</h6>
                                                            </td>
                                                            <td class="text-center">{{ $item['quantity'] }}</td>
                                                            <td class="text-center">
                                                                ₹{{ number_format($item['mrp'], 2) }}</td>
                                                            <td class="text-center">
                                                                <b>₹{{ number_format($item['mrp'] * $item['quantity'], 2) }}</b>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4 mb-3">
                                    <div class="offset-lg-8 col-lg-4">
                                        <div class="or-detail rounded">
                                            <div class="p-3">
                                                <h5 class="mb-3">Transaction Details</h5>
                                                <div class="mb-2 d-flex justify-content-between">
                                                    <h6 class="mb-0">Payment Mode:</h6>
                                                    <p class="mb-0">{{ $invoice->payment_mode }}</p>
                                                </div>
                                                <div class="mb-2 d-flex justify-content-between">
                                                    <h6 class="mb-0">Sub Total:</h6>
                                                    <p class="mb-0">₹{{ number_format($invoice->sub_total, 2) }}</p>

                                                </div>
                                                @if ($invoice->commission_amount > 0)
                                                    <div class="mb-2 d-flex justify-content-between">
                                                        <h6>Commission Deduction: </h6>
                                                        <p>- ₹{{ number_format($invoice->commission_amount, 2) }}</p>
                                                    </div>
                                                @endif
                                                @if ($invoice->party_amount > 0)
                                                    <div class="mb-2 d-flex justify-content-between">
                                                        <h6>Party Deduction: </h6>
                                                        <p>- ₹{{ number_format($invoice->party_amount, 2) }}</p>
                                                    </div>
                                                @endif
                                                @if ($invoice->roundof > 0)
                                                    <div class="mb-2 d-flex justify-content-between">
                                                        <h6>Roundof: </h6>
                                                        <p>- ₹{{ number_format($invoice->roundof, 2) }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                            <div
                                                class="ttl-amt py-2 px-3 d-flex justify-content-between align-items-center">
                                                <h6>Total</h6>
                                                <h3 class="text-primary font-weight-700">
                                                    ₹{{ number_format((float) $invoice->sub_total - (float) $invoice->party_amount, 2) }}
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <b class="text-danger">Notes:</b>
                                        <p class="mb-0">Thank you for your business. If you have any questions, feel
                                            free to contact us.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page end  -->
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
        const salesImgViewBase = "{{ url('sales-img-view') }}";

        function showPhoto(id, commission_user_id = '', party_user_id = '', invoice_no = '') {
            let url =
                `${salesImgViewBase}/${id}?commission_user_id=${commission_user_id}&party_user_id=${party_user_id}&invoice_no=${invoice_no}`;

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
