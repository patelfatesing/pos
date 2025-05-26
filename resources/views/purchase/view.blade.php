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
                                    <h4 class="card-title">View Delivery Invoice</h4>
                                </div>
                                <div>
                                    <a href="{{ route('purchase.list') }}" class="btn btn-secondary">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Bill No: </label> <span
                                                    class="ml-2">{{ $purchase->bill_no }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Vendor Name: </label>
                                                <span class="ml-2"> {{ $purchase->vendor->name }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Date:</label>
                                                <span class="ml-2">
                                                    {{ \Carbon\Carbon::parse($purchase->date)->format('d-m-Y h:i A') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>

                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Brand</th>
                                                    <th>Batch</th>
                                                    <th>MFG Date</th>
                                                    <th>MRP</th>
                                                    <th>Qty</th>
                                                    <th>Rate</th>
                                                    <th>Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($purchase->productsItems as $key => $product)
                                                    <tr>
                                                        <td>{{ $key + 1 }}</td>
                                                        <td>{{ $product->brand_name }}</td>
                                                        <td>{{ $product->batch }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($product->mfg_date)->format('m-Y') }}
                                                        </td>
                                                        <td>{{ number_format($product->mrp, 2) }}</td>
                                                        <td>{{ $product->qnt }}</td>
                                                        <td>{{ number_format($product->rate, 2) }}</td>
                                                        <td>{{ number_format($product->amount, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6 offset-md-6">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <tr>
                                                        <th>Total</th>
                                                        <td>₹{{ number_format($purchase->total, 2) }}</td>
                                                    </tr>

                                                    @if ($purchase->vendor_id == 1)
                                                        <tr>
                                                            <th>Excise Fee</th>
                                                            <td>₹{{ number_format($purchase->excise_fee, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Composition VAT</th>
                                                            <td>₹{{ number_format($purchase->composition_vat, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Surcharge on CA</th>
                                                            <td>₹{{ number_format($purchase->surcharge_on_ca, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>TCS</th>
                                                            <td>₹{{ number_format($purchase->tcs, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>AED to be Paid</th>
                                                            <td>₹{{ number_format($purchase->aed_to_be_paid, 2) }}</td>
                                                        </tr>
                                                    @elseif ($purchase->vendor_id == 2)
                                                        <tr>
                                                            <th>VAT</th>
                                                            <td>₹{{ number_format($purchase->vat ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Surcharge on VAT</th>
                                                            <td>₹{{ number_format($purchase->surcharge_on_vat ?? 0, 2) }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>TCS</th>
                                                            <td>₹{{ number_format($purchase->tcs ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>BLF</th>
                                                            <td>₹{{ number_format($purchase->blf ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Permit Fee</th>
                                                            <td>₹{{ number_format($purchase->permit_fee ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>RSGSM Purchase</th>
                                                            <td>₹{{ number_format($purchase->rsgsm_purchase ?? 0, 2) }}
                                                            </td>
                                                        </tr>
                                                    @else
                                                        <tr>
                                                            <th>Case Purchase %</th>
                                                            <td>₹{{ number_format($purchase->case_purchase_per ?? 0, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Case Purchase Amount</th>
                                                            <td>₹{{ number_format($purchase->case_purchase_amt ?? 0, 2) }}
                                                            </td>
                                                        </tr>
                                                    @endif

                                                    <tr class="table-primary">
                                                        <th><strong>Total With Tax</strong></th>
                                                        <td><strong>₹{{ number_format($purchase->total_amount, 2) }}</strong>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>

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
