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
                                    <h4 class="card-title">View Transaction</h4>
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
                                                        <td>{{ $invoice->invoice_number }}</td>
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
