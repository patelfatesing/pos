@extends('layouts.backend.layouts')
<style>
    .form-group {
        margin-bottom: 0rem !important;
    }
</style>
@section('page-content')
    <!-- Wrapper Start -->
    <div class="wrapper">

        <div class="content-page">

            <div class="container-fluid add-form-list">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">View Transaction - {{ $branch_name }}</h4>
                                </div>
                                <div>
                                    <a href="{{ route('shift-manage.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Invoice No</th>
                                                    <th>Cash Amount</th>
                                                    <th>UPI Amount</th>
                                                    {{-- <th>Online Amount</th> --}}
                                                    <th>Credit Pay</th>
                                                    <th>Payment Mode</th>
                                                    <th>Total Items</th>
                                                    <th>Sub Total</th>
                                                    <th>Total</th>
                                                    <th>Status</th>
                                                    <th>Created At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($invoices as $key => $invoice)
                                                    <tr>
                                                        <td>
                                                            @if ($invoice->status == 'Hold')
                                                                <a class="badge badge-success" data-toggle="tooltip"
                                                                    data-placement="top" title="View"
                                                                    href="{{ url('/view-hold-invoice/' . $invoice->id) }}">
                                                                    {{ $invoice->invoice_number }}
                                                                </a>
                                                            @else
                                                                <a class="badge badge-success" data-toggle="tooltip"
                                                                    data-placement="top" title="View"
                                                                    href="{{ url('/view-invoice/' . $invoice->id) }}">
                                                                    {{ $invoice->invoice_number }}
                                                                </a>
                                                            @endif
                                                        </td>
                                                        <td>{{ $invoice->cash_amount }}</td>
                                                        <td>{{ $invoice->upi_amount + $invoice->online_amount }}</td>
                                                        {{-- <td>{{ $invoice->online_amount }}</td> --}}
                                                        <td>{{ $invoice->creditpay }}</td>
                                                        <td>{{ $invoice->payment_mode }}</td>
                                                        <td>{{ $invoice->total_item_qty }}</td>
                                                        <td>{{ $invoice->sub_total }}</td>
                                                        <td>{{ $invoice->total }}</td>
                                                        <td>{{ $invoice->status }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($invoice->created_at)->format('d-m-Y h:i:s') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <th class="text-end">Total</th>
                                                    <th>{{ number_format($totalCashAmount, 2) }}</th>
                                                    <th>{{ number_format($totalUPIAmount, 2) }}</th>
                                                    <th>{{ number_format($totalCreditPay, 2) }}</th>
                                                    <th></th>
                                                    <th>{{ $totalItems }}</th>
                                                    <th>{{ number_format($totalSubTotal, 2) }}</th>
                                                    <th>{{ number_format($totalTotal, 2) }}</th>
                                                    <th></th>
                                                    <th></th>
                                                </tr>
                                            </tfoot>

                                        </table>
                                        <div class="d-flex justify-content-center mt-3">
                                            {{ $invoices->links('pagination::bootstrap-5') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Page end -->
                </div>
            </div>
        </div>
        <!-- Wrapper End -->
    @endsection
