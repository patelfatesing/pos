@extends('layouts.backend.layouts')

@section('page-content')
    <!-- Wrapper Start -->
    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div>
                                <h4 class="mb-3">Reports List</h4>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="table-responsive rounded mb-3">
                            <div class="container">
                                <div class="row mb-3">
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('sales.report') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-info-light">
                                                            <i class="fas fa-chart-line text-info fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Sales Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('sales.sales-daily') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-danger-light">
                                                            <i class="fas fa-coins text-danger fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Daily Sales</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('sales.stock.report') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fas fa-box-open text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Stock Summary</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('sales.commission.report') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fi fi-bs-financial-analysis"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Commission Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.low_stock.page') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-info-light">
                                                            <i class="fas fa-chart-line text-info fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Product Low Stock</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.expiry.page') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-danger-light">
                                                            <i class="fas fa-coins text-danger fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Product Expiry</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.pl.page') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fi fi-sr-usd-circle"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Profit Loss</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.product_pl.page') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fas fa-box-open text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Profit Loss Poduct Wise</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.credit_payments.page') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-info-light">
                                                            <i class="fi fi-sr-credit-card"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Credit Customer Ledger Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.category_sales.page') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-danger-light">
                                                           
                                                            <i class="fi fi-rr-copy text-danger fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Category Wise Sales Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.discounts.page') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fas fa-box-open text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Discounts & Offers Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.vendor_purchases.page') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fas fa-box-open text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Vendor Purchase Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Wrapper End -->

    <script></script>
@endsection
