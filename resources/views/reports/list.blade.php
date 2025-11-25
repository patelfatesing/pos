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

                                    {{-- Sales Report --}}
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
                                                <a href="{{ route('sales.sales.list') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-info-light">
                                                            <i class="fas fa-chart-line text-info fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Sales List</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Stock Summary --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('sales.stock.report') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fas fa-boxes text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Stock Summary</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Low Stock --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.low_stock.page') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-warning-light">
                                                            <i class="fas fa-exclamation-triangle text-warning fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Product Low Stock</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Expiry --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.expiry.page') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-danger-light">
                                                            <i class="fas fa-hourglass-end text-danger fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Product Expiry</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Profit Loss --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.pnl_tally.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fas fa-balance-scale text-success fa-2x"></i>
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
                                                <a href="{{ route('reports.day-book') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fas fa-balance-scale text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Day Book</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Balance Sheet --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.balance-sheet') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-info-light">
                                                            <i class="fas fa-file-invoice-dollar text-info fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Balance Sheet</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Product-wise Profit Loss --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.product_pl.page') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fas fa-chart-pie text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Profit Loss Product Wise</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Credit Party Ledger --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.credit_payments.page') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-info-light">
                                                            <i class="fas fa-credit-card text-info fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Credit Party Customer Ledger Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Category Sales --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.category_sales.page') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-danger-light">
                                                            <i class="fas fa-tags text-danger fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Category Wise Sales Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Discounts --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.discounts.page') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fas fa-percent text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Discounts & Offers Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Vendor Purchases --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.vendor_purchases.page') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-primary-light">
                                                            <i class="fas fa-truck-loading text-primary fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Vendor Delivery Invoice Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Product Discount --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.discount.product.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fas fa-gift text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Product Wise Discount</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Day End --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.day_end.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-info-light">
                                                            <i class="fas fa-calendar-check text-info fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">End Day Summary Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Product Inactive --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.product_inactive.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-secondary-light">
                                                            <i class="fas fa-ban text-secondary fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Product Inactive Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Best Selling --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.best_selling.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fas fa-star text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Best Selling Product Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Worst Selling --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.worst_selling.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-danger-light">
                                                            <i class="fas fa-thumbs-down text-danger fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Worst Selling Product Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Not Sold --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.not_sold.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-warning-light">
                                                            <i class="fas fa-box text-warning fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Not Sold Product Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Stock Transfer --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.stock_transfer.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-primary-light">
                                                            <i class="fas fa-exchange-alt text-primary fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Stock Transfer Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Purchase --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.purchase.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fas fa-shopping-cart text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Purchase Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Purchase by Product --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.purchase_by_product.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-info-light">
                                                            <i class="fas fa-list text-info fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Purchase By Product Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Closing Summary --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.closing_summary.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fas fa-clipboard-check text-success fa-2x"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-2">Closing Summary Report</p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Profit on Invoice --}}
                                    <div class="col-lg-3 col-md-3">
                                        <div class="card card-block card-stretch card-height">
                                            <div class="card-body">
                                                <a href="{{ route('reports.profit_invoice.view') }}">
                                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                                        <div class="icon iq-icon-box-2 bg-success-light">
                                                            <i class="fas fa-file-invoice text-success fa-2x"></i>
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
@endsection
