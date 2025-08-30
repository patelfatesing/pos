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

                                    {{-- <div class="col-lg-3 col-md-3">
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
                                    </div> --}}

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

                                    {{-- <div class="col-lg-3 col-md-3">
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
                                    </div> --}}

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
                                                <a href="{{ route('reports.pnl_tally.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fa fa-money text-success fa-2x"></i>
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
                                                            <i class="fa fa-random text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Profit Loss Poduct Wise</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                              
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.credit_payments.page') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-info-light">
                                                            <i class="fa fa-credit-card"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Credit Party Customer Ledger Report</p>
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
                                                            <i class="fa fa-rss-square text-danger fa-2x"></i>
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
                                                            <i class="fa fa-tasks text-success fa-2x"></i>
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
                                                            <i class="fa fa-ticket text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Vendor Delivery Invoice Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.discount.product.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            {{-- <i class="fas fa-box-open text-success fa-2x"></i> --}}
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Product Wise Discount</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.day_end.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            {{-- <i class="fas fa-box-open text-success fa-2x"></i> --}}
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">End Day Summary Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.product_inactive.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            {{-- <i class="fas fa-box-open text-success fa-2x"></i> --}}
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Product Inactive Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.best_selling.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fa fa-drivers-license-o text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Best Selling Product Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.worst_selling.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fa fa-cubes text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Worst Selling Product Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.not_sold.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fa fa-bars text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Not Sold Product Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.stock_transfer.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fa fa-binoculars text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Stock Transfer Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.purchase.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fa fa-adjust text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">purchase Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.purchase_by_product.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fa fa-circle text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Purchase By Product Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.closing_summary.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fa fa-crosshairs text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Closing Summary Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.profit_invoice.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fa fa-crosshairs text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Profit on-sales Invoice Report</p>
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
