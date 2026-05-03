@extends('layouts.backend.layouts')
<style>
    .report-box {
        background: #fff;
        font-size: 15px;
        font-weight: 500;
        transition: 0.2s;
    }

    .report-box:hover {
        background: #f5f5f5;
        border-color: #000;
    }
</style>
@section('page-content')
    <!-- Wrapper Start -->

    <div class="wrapper">
        <div class="content-page">
            <div class="container-fluid">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0">Reports List</h4>
                    </div>


                </div>
                <div class="col-12 mt-3">
                    <div class="row">

                        @php
                            $reports = [
                                [
                                    'name' => 'Profit Loss',
                                    'slug' => 'profit-loss',
                                    'route' => route('reports.pnl_tally.view'),
                                ],
                                ['name' => 'Day Book', 'slug' => 'day-book', 'route' => route('reports.day-book')],
                                [
                                    'name' => 'Cash & Bank Summary',
                                    'slug' => 'cash-bank-summary',
                                    'route' => route('reports.cash-bank.summary'),
                                ],
                                [
                                    'name' => 'Balance Sheet',
                                    'slug' => 'balance-sheet',
                                    'route' => route('reports.balance-sheet'),
                                ],
                                [
                                    'name' => 'Stock Summary',
                                    'slug' => 'stock-summary',
                                    'route' => route('accounting.stock.summary'),
                                ],

                                [
                                    'name' => 'Sales Report',
                                    'slug' => 'sales-report',
                                    'route' => route('sales.salas-report'),
                                ],
                                ['name' => 'Sales List', 'slug' => 'sales-list', 'route' => route('sales.sales.list')],
                                [
                                    'name' => 'Stock Summary',
                                    'slug' => 'stock-summary',
                                    'route' => route('sales.stock.report'),
                                ],
                                [
                                    'name' => 'Product Low Stock',
                                    'slug' => 'product-low-stock',
                                    'route' => route('reports.low_stock.page'),
                                ],
                                [
                                    'name' => 'Product Expiry',
                                    'slug' => 'product-expiry',
                                    'route' => route('reports.expiry.page'),
                                ],
                                [
                                    'name' => 'Profit Loss Product Wise',
                                    'slug' => 'profit-loss-product-wise',
                                    'route' => route('reports.product_pl.page'),
                                ],
                                [
                                    'name' => 'Credit Customer Ledger',
                                    'slug' => 'credit-customer-ledger',
                                    'route' => route('reports.credit_payments.page'),
                                ],
                                [
                                    'name' => 'Category Sales',
                                    'slug' => 'category-sales',
                                    'route' => route('reports.category_sales.page'),
                                ],
                                [
                                    'name' => 'Discounts Report',
                                    'slug' => 'discounts-report',
                                    'route' => route('reports.discounts.page'),
                                ],
                                [
                                    'name' => 'Vendor Purchase',
                                    'slug' => 'vendor-purchase',
                                    'route' => route('reports.vendor_purchases.page'),
                                ],
                                [
                                    'name' => 'Product Discount',
                                    'slug' => 'product-discount',
                                    'route' => route('reports.discount.product.view'),
                                ],
                                [
                                    'name' => 'Day End Summary',
                                    'slug' => 'day-end-summary',
                                    'route' => route('reports.day_end.view'),
                                ],
                                [
                                    'name' => 'Inactive Product',
                                    'slug' => 'inactive-product',
                                    'route' => route('reports.product_inactive.view'),
                                ],
                                [
                                    'name' => 'Best Selling',
                                    'slug' => 'best-selling',
                                    'route' => route('reports.best_selling.view'),
                                ],
                                [
                                    'name' => 'Worst Selling',
                                    'slug' => 'worst-selling',
                                    'route' => route('reports.worst_selling.view'),
                                ],
                                [
                                    'name' => 'Not Sold Product',
                                    'slug' => 'not-sold-product',
                                    'route' => route('reports.not_sold.view'),
                                ],
                                [
                                    'name' => 'Stock Transfer',
                                    'slug' => 'stock-transfer',
                                    'route' => route('reports.stock_transfer.view'),
                                ],
                                [
                                    'name' => 'Purchase Report',
                                    'slug' => 'purchase-report',
                                    'route' => route('reports.purchase.view'),
                                ],
                                [
                                    'name' => 'Purchase By Product',
                                    'slug' => 'purchase-by-product',
                                    'route' => route('reports.purchase_by_product.view'),
                                ],
                                [
                                    'name' => 'Closing Summary',
                                    'slug' => 'closing-summary',
                                    'route' => route('reports.closing_summary.view'),
                                ],
                                [
                                    'name' => 'Profit Invoice',
                                    'slug' => 'profit-invoice',
                                    'route' => route('reports.profit_invoice.view'),
                                ],
                            ];
                        @endphp

                        @php
                            $roleId = auth()->user()->role_id;
                        @endphp

                        @foreach ($reports as $report)
                            @php
                                $access = getAccess($roleId, $report['slug']);
                            @endphp

                            @if (!in_array($access, ['none', 'no']))
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                    <a href="{{ $report['route'] }}" class="text-decoration-none">
                                        <div class="border rounded p-3 text-center report-box">
                                            <text class="dark">{{ $report['name'] }}</text>
                                        </div>
                                    </a>
                                </div>
                            @endif
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Wrapper End -->
@endsection
