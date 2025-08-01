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
                                                        @if ($invoice->branch_id == 1 && !empty($invoice->creditpay) && $invoice->creditpay > 0)
                                                            <th scope="col">Credit Status</th>
                                                        @endif
                                                        @if ($invoice->branch_id == 1 && !empty($invoice->creditpay) && $invoice->creditpay > 0)
                                                            <th scope="col">Credit</th>
                                                        @endif
                                                        @if ($invoice->ref_no != '')
                                                            <th scope="col">Transaction No(Ref)</th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>{{ $invoice->updated_at->format('Y-m-d H:i:s') }}</td>
                                                        <td>
                                                            <span
                                                                class="badge badge-{{ $invoice->status == 'Paid' ? 'success' : 'danger' }}">
                                                                {{ $invoice->status }}
                                                            </span>
                                                        </td>
                                                        @if ($invoice->branch_id == 1 && !empty($invoice->creditpay) && $invoice->creditpay > 0)
                                                            <td>
                                                                <span
                                                                    class="badge badge-{{ $invoice->invoice_status == 'Paid' ? 'success' : 'danger' }}">
                                                                    {{ $invoice->invoice_status }}
                                                                </span>
                                                            </td>
                                                        @endif
                                                        @if ($invoice->branch_id == 1 && !empty($invoice->creditpay) && $invoice->creditpay > 0)
                                                            <td>
                                                                <span>
                                                                    ₹{{ $invoice->creditpay }}
                                                                </span>
                                                            </td>
                                                        @endif
                                                        @if ($invoice->ref_no != '')
                                                            <td>
                                                                {{ $invoice->ref_no }}
                                                                ({{ $invoice->created_at->format('Y-m-d H:i:s') }})
                                                            </td>
                                                        @endif
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
                                                    <h6>Credit: </h6>

                                                    @if ($invoice->creditpay != '')
                                                        <p> ₹{{ number_format($invoice->creditpay, 2) }}</p>
                                                    @else
                                                        <p>-</p>
                                                    @endif
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
                                                        <h6>Round off: </h6>
                                                        <p> ₹{{ number_format($invoice->roundof, 2) }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                            <div
                                                class="ttl-amt py-2 px-3 d-flex justify-content-between align-items-center">
                                                <h6>Total</h6>
                                                <h3 class="text-primary font-weight-700">
                                                    @if ($invoice->roundof > 0)
                                                        @php
                                                            $cleanTotal = floatval(
                                                                str_replace(',', '', $invoice->sub_total ?? 0),
                                                            );
                                                            $cleanRoundof = floatval(
                                                                str_replace(',', '', $invoice->roundof ?? 0),
                                                            );

                                                            $commisson = 0;
                                                            if ($invoice->commission_amount > 0) {
                                                                $commisson = floatval(
                                                                    str_replace(
                                                                        ',',
                                                                        '',
                                                                        $invoice->commission_amount ?? 0,
                                                                    ),
                                                                );
                                                            }

                                                            if ($invoice->party_amount > 0) {
                                                                $commisson = floatval(
                                                                    str_replace(',', '', $invoice->party_amount ?? 0),
                                                                );
                                                            }

                                                            $grandTotal = $cleanTotal - $commisson + $cleanRoundof;
                                                        @endphp
                                                        ₹{{ number_format($grandTotal, 2) }}
                                                    @else
                                                        @php
                                                            $cleanTotal = floatval(
                                                                str_replace(',', '', $invoice->sub_total ?? 0),
                                                            );

                                                            $commission_amount = floatval(
                                                                str_replace(',', '', $invoice->commission_amount ?? 0),
                                                            );

                                                            $commisson = 0;
                                                            if ($invoice->commission_amount > 0) {
                                                                $commisson = floatval(
                                                                    str_replace(
                                                                        ',',
                                                                        '',
                                                                        $invoice->commission_amount ?? 0,
                                                                    ),
                                                                );
                                                            }

                                                            if ($invoice->party_amount > 0) {
                                                                $commisson = floatval(
                                                                    str_replace(',', '', $invoice->party_amount ?? 0),
                                                                );
                                                            }

                                                            $grandTotal = $cleanTotal - $commisson;
                                                        @endphp
                                                        ₹{{ number_format($grandTotal, 2) }}
                                                    @endif
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

    <!-- Modal -->
    <div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfModalLabel">Invoice PDF Preview</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe src="{{ asset('storage/invoices/' . $invoice->invoice_number . '.pdf') }}" width="100%"
                        height="600px" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>


    <script>
        const salesImgViewBase = "{{ url('sales-img-view') }}";

        function showPhoto(id, commission_user_id = '', party_user_id = '') {
            let url =
                `${salesImgViewBase}/${id}?commission_user_id=${commission_user_id}&party_user_id=${party_user_id}`;

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
