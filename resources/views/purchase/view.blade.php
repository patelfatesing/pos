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
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0">View Delivery Invoice</h4>
                    </div>
                    <div>
                        <a href="{{ route('purchase.list') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">

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
                                                <label>Bill Date:</label>
                                                <span class="ml-2">
                                                    {{ \Carbon\Carbon::parse($purchase->date)->format('d-m-Y h:i A') }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Created Date:</label>
                                                <span class="ml-2">
                                                    {{ \Carbon\Carbon::parse($purchase->created_at)->format('d-m-Y h:i A') }}</span>
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

                                    <div class="row mt-3">

                                        @if ($purchase->vendor_id == 1 || $purchase->vendor_id == 2)
                                            <div class="col-lg-4">
                                                <div class="card border">
                                                    <div class="card-body">
                                                        <h5>License Ledger Details</h5>

                                                        <p><strong>ITP Value:</strong>
                                                            ₹{{ number_format($purchase->itp_value, 2) }}</p>

                                                        @if ($purchase->aed_to_be_paid)
                                                            <p><strong>AED To Be Paid:</strong>
                                                                ₹{{ number_format($purchase->aed_to_be_paid, 2) }}</p>
                                                        @endif

                                                        @if ($purchase->guarantee_fulfilled)
                                                            <p><strong>Guarantee Fulfilled:</strong>
                                                                ₹{{ number_format($purchase->guarantee_fulfilled, 2) }}</p>
                                                        @endif

                                                        @if ($purchase->loading_charges)
                                                            <p><strong>Loading Charges:</strong>
                                                                ₹{{ number_format($purchase->loading_charges, 2) }}</p>
                                                        @endif

                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($purchase->vendor_id == 1)
                                            <div class="col-lg-4">
                                                <div class="card border">
                                                    <div class="card-body">

                                                        <h5>Excise Fee</h5>

                                                        <p><strong>Permit Fee:</strong>
                                                            ₹{{ number_format($purchase->permit_fee_excise, 2) }}</p>

                                                        <p><strong>Vend Fee:</strong>
                                                            ₹{{ number_format($purchase->vend_fee_excise, 2) }}</p>

                                                        <p><strong>Composite Fee:</strong>
                                                            ₹{{ number_format($purchase->composite_fee_excise, 2) }}</p>

                                                        <hr>

                                                        <p><strong>Total:</strong>
                                                            ₹{{ number_format($purchase->excise_total_amount, 2) }}
                                                        </p>

                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="col-lg-4">
                                            <div class="card border">
                                                <div class="card-body">

                                                    <h5>Billing Details</h5>

                                                    <p><strong>Sub Total:</strong>
                                                        ₹{{ number_format($purchase->total, 2) }}</p>

                                                    @if ($purchase->vendor_id == 1)
                                                        <p><strong>Excise Fee:</strong>
                                                            ₹{{ number_format($purchase->excise_fee, 2) }}</p>

                                                        <p><strong>Composition VAT:</strong>
                                                            ₹{{ number_format($purchase->composition_vat, 2) }}</p>

                                                        <p><strong>Surcharge On CA:</strong>
                                                            ₹{{ number_format($purchase->surcharge_on_ca, 2) }}</p>
                                                    @elseif($purchase->vendor_id == 2)
                                                        <p><strong>VAT:</strong> ₹{{ number_format($purchase->vat, 2) }}
                                                        </p>

                                                        <p><strong>Surcharge On VAT:</strong>
                                                            ₹{{ number_format($purchase->surcharge_on_vat, 2) }}</p>

                                                        <p><strong>BLF:</strong> ₹{{ number_format($purchase->blf, 2) }}
                                                        </p>

                                                        <p><strong>Permit Fee:</strong>
                                                            ₹{{ number_format($purchase->permit_fee, 2) }}</p>
                                                    @else
                                                        <p><strong>Cash Purchase %:</strong>
                                                            {{ $purchase->case_purchase_per }}%</p>

                                                        <p><strong>Cash Purchase Amount:</strong>
                                                            ₹{{ number_format($purchase->case_purchase_amt, 2) }}</p>
                                                    @endif

                                                    <p><strong>TCS:</strong> ₹{{ number_format($purchase->tcs, 2) }}</p>

                                                    <hr>

                                                    <h5>Total Amount: ₹{{ number_format($purchase->total_amount, 2) }}</h5>

                                                </div>
                                            </div>
                                        </div>

                                        <hr>
                             
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
