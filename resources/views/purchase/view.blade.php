@extends('layouts.backend.layouts')
<style>
    .form-group {
        margin-bottom: 0rem !important;
    }

    .font-size-15-px {
        font-size: 15px;
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
                        <div class="card border-0 shadow-sm">

                            <div class="card-body">
                                <div class="card-body">
                                    <!-- Invoice Metadata Card -->
                                    <div class="card shadow-sm border-0 bg-white rounded-3 mb-4">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-2 mb-2 mb-md-0 font-size-15-px">
                                                    <span class="text-muted small font-weight-bold mr-1">BILL NO:</span>
                                                    <span class="badge badge-primary px-3 py-1 font-weight-bold" style="font-size: 0.95rem;">{{ $purchase->bill_no }}</span>
                                                </div>
                                                <div class="col-md-3 mb-2 mb-md-0 font-size-15-px">
                                                    <span class="text-muted small font-weight-bold mr-1">VENDOR NAME:</span>
                                                    <span class="text-dark font-weight-bold" style="font-size: 1rem;"><i class="fas fa-store text-primary mr-1"></i> {{ $purchase->vendor->name }}</span>
                                                </div>
                                                <div class="col-md-3 mb-2 mb-md-0 font-size-15-px">
                                                    <span class="text-muted small font-weight-bold mr-1">BILL DATE:</span>
                                                    <span class="text-dark font-weight-semibold"><i class="far fa-calendar-alt text-muted mr-1"></i> {{ \Carbon\Carbon::parse($purchase->date)->format('d-m-Y h:i A') }}</span>
                                                </div>
                                                <div class="col-md-4 font-size-15-px">
                                                    <span class="text-muted small font-weight-bold mr-1">CREATED DATE:</span>
                                                    <span class="text-dark font-weight-semibold"><i class="far fa-clock text-muted mr-1"></i> {{ \Carbon\Carbon::parse($purchase->created_at)->format('d-m-Y h:i A') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
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

                                    <div class="row mt-1">

                                        @if ($purchase->vendor_id == 1 || $purchase->vendor_id == 2)
                                            <div class="col-lg-4 mb-3">
                                                <div class="card shadow-sm border-0 h-100 rounded-3">
                                                    <div class="card-header bg-info border-bottom py-2 px-4" style="min-height: initial">
                                                        <h6 class="mb-0 font-weight-bold"><i class="fas fa-balance-scale mr-2"></i>License Ledger Details</h6>
                                                    </div>
                                                    <div class="card-body px-4">
                                                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                            <span class="text-muted font-weight-normal">ITP Value</span>
                                                            <span class="font-weight-bold text-dark">₹{{ number_format($purchase->itp_value, 2) }}</span>
                                                        </div>

                                                        @if ($purchase->aed_to_be_paid)
                                                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                                <span class="text-muted font-weight-normal">AED To Be Paid</span>
                                                                <span class="font-weight-bold text-dark">₹{{ number_format($purchase->aed_to_be_paid, 2) }}</span>
                                                            </div>
                                                        @endif

                                                        @if ($purchase->guarantee_fulfilled)
                                                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                                <span class="text-muted font-weight-normal">Guarantee Fulfilled</span>
                                                                <span class="font-weight-bold text-dark">₹{{ number_format($purchase->guarantee_fulfilled, 2) }}</span>
                                                            </div>
                                                        @endif

                                                        @if ($purchase->loading_charges)
                                                            <div class="d-flex justify-content-between align-items-center py-2">
                                                                <span class="text-muted font-weight-normal">Loading Charges</span>
                                                                <span class="font-weight-bold text-dark">₹{{ number_format($purchase->loading_charges, 2) }}</span>
                                                            </div>
                                                        @endif

                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($purchase->vendor_id == 1)
                                            <div class="col-lg-4 mb-3">
                                                <div class="card shadow-sm border-0 h-100 rounded-3">
                                                    <div class="card-header bg-white border-bottom py-3 px-4">
                                                        <h6 class="mb-0 font-weight-bold text-warning"><i class="fas fa-receipt mr-2"></i>Excise Fee</h6>
                                                    </div>
                                                    <div class="card-body px-4 py-3">
                                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-light">
                                                            <span class="text-muted font-weight-normal">Permit Fee</span>
                                                            <span class="font-weight-bold text-dark">₹{{ number_format($purchase->permit_fee_excise, 2) }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-light">
                                                            <span class="text-muted font-weight-normal">Vend Fee</span>
                                                            <span class="font-weight-bold text-dark">₹{{ number_format($purchase->vend_fee_excise, 2) }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center py-2">
                                                            <span class="text-muted font-weight-normal">Composite Fee</span>
                                                            <span class="font-weight-bold text-dark">₹{{ number_format($purchase->composite_fee_excise, 2) }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer bg-light border-top py-2 px-4 d-flex justify-content-between align-items-center rounded-bottom">
                                                        <span class="font-weight-bold text-secondary">Total Excise</span>
                                                        <span class="font-weight-bold text-warning">₹{{ number_format($purchase->excise_total_amount, 2) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="col-lg-4 mb-3">
                                            <div class="card shadow-sm border-0 h-100 rounded-3">
                                                <div class="card-header bg-info border-bottom py-2 px-4" style="min-height: initial">
                                                    <h6 class="mb-0 font-weight-bold"><i class="fas fa-file-invoice-dollar mr-2"></i>Billing Details</h6>
                                                </div>
                                                <div class="card-body px-4 pb-0">
                                                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                        <span class="text-muted font-weight-normal">Sub Total</span>
                                                        <span class="font-weight-bold text-dark">₹{{ number_format($purchase->total, 2) }}</span>
                                                    </div>

                                                    @if ($purchase->vendor_id == 1)
                                                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                            <span class="text-muted font-weight-normal">Excise Fee</span>
                                                            <span class="font-weight-bold text-dark">₹{{ number_format($purchase->excise_fee, 2) }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                            <span class="text-muted font-weight-normal">Composition VAT</span>
                                                            <span class="font-weight-bold text-dark">₹{{ number_format($purchase->composition_vat, 2) }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                            <span class="text-muted font-weight-normal">Surcharge On CA</span>
                                                            <span class="font-weight-bold text-dark">₹{{ number_format($purchase->surcharge_on_ca, 2) }}</span>
                                                        </div>
                                                    @elseif($purchase->vendor_id == 2)
                                                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                            <span class="text-muted font-weight-normal">VAT</span>
                                                            <span class="font-weight-bold text-dark">₹{{ number_format($purchase->vat, 2) }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                            <span class="text-muted font-weight-normal">Surcharge On VAT</span>
                                                            <span class="font-weight-bold text-dark">₹{{ number_format($purchase->surcharge_on_vat, 2) }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                            <span class="text-muted font-weight-normal">BLF</span>
                                                            <span class="font-weight-bold text-dark">₹{{ number_format($purchase->blf, 2) }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                            <span class="text-muted font-weight-normal">Permit Fee</span>
                                                            <span class="font-weight-bold text-dark">₹{{ number_format($purchase->permit_fee, 2) }}</span>
                                                        </div>
                                                    @else
                                                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                            <span class="text-muted font-weight-normal">Cash Purchase %</span>
                                                            <span class="font-weight-bold text-dark">{{ $purchase->case_purchase_per }}%</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                                            <span class="text-muted font-weight-normal">Cash Purchase Amount</span>
                                                            <span class="font-weight-bold text-dark">₹{{ number_format($purchase->case_purchase_amt, 2) }}</span>
                                                        </div>
                                                    @endif

                                                    <div class="d-flex justify-content-between align-items-center py-1">
                                                        <span class="text-muted font-weight-normal">TCS</span>
                                                        <span class="font-weight-bold text-dark">₹{{ number_format($purchase->tcs, 2) }}</span>
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-light border-top py-1 px-4 d-flex justify-content-between align-items-center rounded-bottom">
                                                    <span class="font-weight-bold text-dark" style="font-size: 1.05rem;">Total Amount</span>
                                                    <span class="font-weight-bold text-success" style="font-size: 1.2rem;">₹{{ number_format($purchase->total_amount, 2) }}</span>
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
            </div>
            <!-- Wrapper End -->
        @endsection
