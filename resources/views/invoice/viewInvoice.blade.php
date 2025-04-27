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
                                <h4 class="mb-3">Order Invoice List</h4>
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
                                                        <th scope="col">Order Date</th>
                                                        <th scope="col">Order Status</th>
                                                        <th scope="col">Order ID</th>
                                                        <th scope="col">Billing Address</th>
                                                        <th scope="col">Shipping Address</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>{{ $invoice->created_at->format('d M Y') }}</td>
                                                        <td>
                                                            <span
                                                                class="badge badge-{{ $invoice->status == 'Paid' ? 'success' : 'danger' }}">
                                                                {{ $invoice->status }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $invoice->invoice_number }}</td>
                                                        <td>
                                                            <p class="mb-0">{{ $invoice->billing_address }}</p>
                                                        </td>
                                                        <td>
                                                            <p class="mb-0">{{ $invoice->shipping_address }}</p>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h5 class="mb-3">Order Summary</h5>
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
                                                                ₹{{ number_format($item['price'], 2) }}</td>
                                                            <td class="text-center">
                                                                <b>₹{{ number_format($item['price'] * $item['quantity'], 2) }}</b>
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
                                                <h5 class="mb-3">Order Details</h5>
                                                <div class="mb-2">
                                                    <h6>Sub Total</h6>
                                                    <p>₹{{ number_format($invoice->sub_total, 2) }}</p>
                                                </div>
                                                @if ($invoice->commission_amount > 0)
                                                    <div class="mb-2">
                                                        <h6>Commission Deduction</h6>
                                                        <p>- ₹{{ number_format($invoice->commission_amount, 2) }}</p>
                                                    </div>
                                                @endif
                                                @if ($invoice->party_amount > 0)
                                                    <div class="mb-2">
                                                        <h6>Party Deduction</h6>
                                                        <p>- ₹{{ number_format($invoice->party_amount, 2) }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                            <div
                                                class="ttl-amt py-2 px-3 d-flex justify-content-between align-items-center">
                                                <h6>Total</h6>
                                                <h3 class="text-primary font-weight-700">
                                                    ₹{{ number_format($invoice->total, 2) }}</h3>
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
@endsection
