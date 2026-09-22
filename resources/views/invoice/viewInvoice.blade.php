@extends('layouts.backend.layouts')
@php
    use Carbon\Carbon;

    // Determine if Edit button should be shown (last 7 days)
    $showEditButton = Carbon::parse($invoice->created_at)->greaterThanOrEqualTo(Carbon::now()->subDays(7));
@endphp
@section('page-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }

        #transaction-details-card {
            margin-bottom: 5px;
        }

        .invoice-header-modern {
            background: #AFBEFA;
            border-radius: 14px;
            padding: 6px 15px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            box-shadow: 0 6px 18px rgba(106, 123, 255, 0.25);
            margin-bottom: 10px;
        }

        .invoice-header-modern h5 {
            color: #fff;
            font-weight: 700;
            margin: 0;
            letter-spacing: .3px;
        }

        .invoice-header-modern .invoice-btn {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            margin: 0;
        }

        .invoice-header-modern .invoice-btn .btn,
        .invoice-header-modern .invoice-btn button {
            background: #fff;
            color: #6a5bff;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 8px;
            padding: 4px 10px;
            font-size: 13px;
            font-weight: 500;
            transition: all .2s ease;
            backdrop-filter: blur(4px);
        }

        .invoice-header-modern .badge-verify {
            background: #ffe27a;
            color: #7a5b00;
            font-weight: 600;
            padding: 1px 12px;
            border-radius: 20px;
            font-size: 12px;
        }

        .summary-info-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
            padding: 10px 24px;
            margin-bottom: 24px;
        }

        .summary-info-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .summary-info-item {
            flex: 1 1 180px;
            border-left: 3px solid #6a7bff;
            padding-left: 14px;
        }

        .summary-info-item .label {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #9aa0ac;
            margin-bottom: 4px;
        }

        .summary-info-item .value {
            font-size: 15px;
            font-weight: 600;
            color: #2b2f3a;
        }

        .status-pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 14px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .3px;
        }

        .status-pill.success {
            background: #e3f9ed;
            color: #1aa15a;
        }

        .status-pill.danger {
            background: #fdeaea;
            color: #e14343;
        }
    </style>

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">

                <div class="invoice-header-modern no-print">
                    <div class="iq-header-title">
                        <h5>Invoice #{{ $invoice->invoice_number }}</h5>
                    </div>
                    <div class="invoice-btn">
                        @if ($invoice->admin_status == 'verify' && $invoice->super_admin_status != 'verify')
                            <span class="badge-verify">Verify Sub Admin</span>
                        @endif

                        @if ($invoice->super_admin_status == 'verify' && $invoice->super_admin_status == 'verify')
                            <span class="badge-verify">Verify this invoice</span>
                        @endif

                        @if ($showEditButton)
                            <a href="{{ route('sales.edit-sales', $invoice->id) }}" class="btn">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                        @endif

                        @if ($invoice->party_user_id != '' && $invoice->sales_type != 'admin_sale')
                            <button onClick="showPhoto({{ $invoice->id }},'',{{ $invoice->party_user_id }})"
                                class="btn">
                                <i class="ri-eye-line mr-0"></i> View Photos
                            </button>
                        @endif

                        @if ($invoice->commission_user_id != '')
                            <button
                                onClick="showPhoto({{ $invoice->id }},{{ $invoice->commission_user_id }},'')"
                                class="btn">
                                <i class="ri-eye-line mr-0"></i> View Photos
                            </button>
                        @endif

                        @if ($invoice->edit_in == 'yes')
                            <button class="btn" data-toggle="modal" data-target="#editPdfModal">
                                <i class="las la-print"></i> Edit View Invoice
                            </button>
                            <button class="btn" data-toggle="modal" data-target="#pdfModal">
                                <i class="las la-print"></i> Original View Invoice
                            </button>
                        @else
                            {{-- <button class="btn text-white mr-2" data-toggle="modal"
                                data-target="#pdfModal">
                                <i class="las la-print"></i>View Invoice
                            </button> --}}
                        @endif

                        {{-- <button class="btn text-white mr-2" data-toggle="modal" data-target="#pdfModal">
                            <i class="las la-print"></i>View Invoice
                        </button> --}}
                        <a href="{{ route('invoice.download', $invoice->id) }}" class="btn">
                            <i class="las la-file-download"></i> Download Invoice
                        </a>
                    </div>
                    @if (!empty($shift_id))
                        <a href="{{ route('shift-manage.view', ['id' => $invoice->branch_id, 'shift_id' => $shift_id]) }}"
                            class="btn btn-secondary px-2 py-1">Back</a>
                    @else
                        {{-- <a href="{{ route('sales.sales.list') }}" class="btn btn-secondary">Back</a> --}}
                        <button onclick="window.history.back()" class="btn btn-secondary px-2 py-1">
                            Back
                        </button>
                    @endif
                </div>

                <div class="row">

                    <div class="col-lg-12">
                        <div class="card card-block card-stretch card-height print rounded">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <!-- <img src="{{ asset('assets/images/logo.png') }}"
                                            class="logo-invoice img-fluid mb-3">
                                        <h5 class="mb-0">Hello, {{ $invoice->customer_name }}</h5>
                                        <p>Thank you for your business. Below is the summary of your invoice.</p> -->
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="summary-info-card">
                                            <div class="summary-info-row">
                                                <div class="summary-info-item">
                                                    <span class="label">Transaction Date</span>
                                                    <span class="value">{{ $invoice->updated_at->format('Y-m-d H:i:s') }}</span>
                                                </div>

                                                <div class="summary-info-item">
                                                    <span class="label">Transaction Status</span>
                                                    <span
                                                        class="status-pill {{ $invoice->status == 'Paid' ? 'success' : 'danger' }}">
                                                        {{ $invoice->status }}
                                                    </span>
                                                </div>

                                                @if ($invoice->branch_id == 1 && !empty($invoice->creditpay) && $invoice->creditpay > 0)
                                                    <div class="summary-info-item">
                                                        <span class="label">Credit Status</span>
                                                        <span
                                                            class="status-pill {{ $invoice->invoice_status == 'Paid' ? 'success' : 'danger' }}">
                                                            {{ $invoice->invoice_status }}
                                                        </span>
                                                    </div>

                                                    <div class="summary-info-item">
                                                        <span class="label">Credit</span>
                                                        <span class="value">₹{{ $invoice->creditpay }}</span>
                                                    </div>
                                                @endif

                                                @if ($invoice->ref_no != '')
                                                    <div class="summary-info-item">
                                                        <span class="label">Transaction No (Ref)</span>
                                                        <span class="value">
                                                            {{ $invoice->ref_no }}
                                                            ({{ $invoice->created_at->format('Y-m-d H:i:s') }})
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
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
                                                                @if ($item['sell_price'] > $item['mrp'])
                                                                    <span
                                                                        style="text-decoration: line-through; color: #999;">
                                                                        ₹{{ number_format($item['sell_price'], 2) }}
                                                                    </span>
                                                                    <br>
                                                                    <span class="text-success font-weight-bold">
                                                                        ₹{{ number_format((float) str_replace(',', '', $item['mrp']), 2) }}
                                                                    </span>
                                                                @else
                                                                    <span class="font-weight-bold">
                                                                        ₹{{ number_format($item['sell_price'], 2) }}
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                <b>₹{{ $item['price'] }}</b>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="offset-lg-8 col-lg-4">
                                        <div class="card border-0 shadow-sm" style="border-radius: 10px; overflow: hidden;" id="transaction-details-card">
                                            <div class="px-3 py-2 text-white d-flex align-items-center" style="background-color: #f36c3d;">
                                                <i class="ri-shopping-cart-line mr-2" style="font-size: 18px;"></i>
                                                <h6 class="mb-0 text-white font-weight-bold">Transaction Details</h6>
                                            </div>
                                            <div class="p-3 bg-white">
                                                <div class="mb-2 d-flex justify-content-between">
                                                    <span class="text-muted">Payment Mode:</span>
                                                    <span class="font-weight-bold text-dark">{{ $invoice->payment_mode }}</span>
                                                </div>

                                                <div class="mb-2 d-flex justify-content-between">
                                                    <span class="text-muted">Credit:</span>
                                                    <span class="font-weight-bold text-dark">
                                                        {{ $invoice->creditpay != '' ? '₹' . number_format($invoice->creditpay, 2) : '-' }}
                                                    </span>
                                                </div>

                                                <div class="mb-2 d-flex justify-content-between">
                                                    <span class="text-muted">Sub Total:</span>
                                                    <span class="font-weight-bold text-primary">₹{{ number_format($invoice->sub_total, 2) }}</span>
                                                </div>

                                                @if ($invoice->commission_amount > 0)
                                                    <div class="mb-2 d-flex justify-content-between">
                                                        <span class="text-muted">Commission Deduction:</span>
                                                        <span class="font-weight-bold text-danger">- ₹{{ number_format($invoice->commission_amount, 2) }}</span>
                                                    </div>
                                                @endif

                                                @if ($invoice->party_amount > 0)
                                                    <div class="mb-2 d-flex justify-content-between">
                                                        <span class="text-muted">Party Deduction:</span>
                                                        <span class="font-weight-bold text-danger">- ₹{{ number_format($invoice->party_amount, 2) }}</span>
                                                    </div>
                                                @endif

                                                @if ($invoice->roundof > 0)
                                                    <div class="mb-2 d-flex justify-content-between">
                                                        <span class="text-muted">Round off:</span>
                                                        <span class="font-weight-bold text-dark">₹{{ number_format($invoice->roundof, 2) }}</span>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="py-2 px-3 d-flex justify-content-between align-items-center text-white" style="background-color: #20c0e8;">
                                                <h6 class="mb-0 text-white font-weight-bold">Total</h6>
                                                <h4 class="mb-0 text-white font-weight-bold">
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
                                                        ₹{{ floatval(str_replace(',', '', $invoice->total ?? 0)) }}
                                                    @endif
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- <div class="row">
                                    <div class="col-sm-12">
                                        <b class="text-danger">Notes:</b>
                                        <p class="mb-0">Thank you for your business. If you have any questions, feel
                                            free to contact us.</p>
                                    </div>
                                </div> -->
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

    <div class="modal fade" id="editPdfModal" tabindex="-1" role="dialog" aria-labelledby="editPModalLabel"
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
                    <iframe src="{{ asset('storage/invoices/edit_' . $invoice->invoice_number . '.pdf') }}"
                        width="100%" height="600px" frameborder="0"></iframe>
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