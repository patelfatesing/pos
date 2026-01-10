@php
$categoryImages = [
'BEER' => 'assets/images/subcategory/Beer-Category.jpeg',
'CL' => 'assets/images/subcategory/Country-Liqour-Category.jpeg',
'IMFL' => 'assets/images/subcategory/Imfl-Category.jpeg',
'RML' => 'assets/images/subcategory/Rml-Category.jpeg',
];
@endphp
@extends('layouts.backend.layouts')
@section('page-content')

<style>
    /* Mobile only */
    @media (max-width: 767px) {

        #topProductsWrapper .d-flex.align-items-top {
            flex-direction: column;
            text-align: center;
        }

        #topProductsWrapper .bg-warning-light {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-bottom: 12px;
        }

        #topProductsWrapper .style-img {
            width: 100% !important;
            max-width: 260px;
            height: auto !important;
            object-fit: contain;
        }

        #topProductsWrapper .style-text {
            margin-left: 0 !important;
            text-align: center;
        }

        #topProductsWrapper .style-text h5 {
            font-size: 16px;
        }

        #topProductsWrapper .style-text p {
            font-size: 14px;
        }
    }
</style>
<!-- Wrapper Start -->
<div class="wrapper">

    <div class="content-page">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-4">
                    <div class="card card-transparent card-block card-stretch card-height border-none">
                        <div class="card-body p-0 mt-lg-2 mt-0">

                            <h3 class="mb-3">Hi {{ Auth::user()->name }}, {{ __('messages.welcome') }} </h3>
                            <p class="mb-0 mr-4">
                                Your dashboard gives you views of key performance or
                                business process.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-lg-4 col-md-4">
                            <div class="card card-block card-stretch card-height">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                        <div class="icon iq-icon-box-2 bg-info-light">
                                            <i class="fas fa-chart-line text-info fa-2x"></i>
                                        </div>
                                        <div>
                                            <p class="mb-2">Total Sales</p>
                                            <h4>{{ format_inr($data['sales'] ?? 0) }}</h4>
                                        </div>
                                    </div>
                                    @php
                                    $sales = $data['sales'] ?? 0;
                                    $target = $data['target'] ?? 1; // avoid division by zero
                                    $percent = min(100, round(($sales / $target) * 100));
                                    @endphp
                                    <div class="iq-progress-bar mt-2">
                                        <span class="bg-info iq-progress progress-1"
                                            data-percent="{{ $percent }}"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-4">
                            <div class="card card-block card-stretch card-height">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                        <div class="icon iq-icon-box-2 bg-danger-light">
                                            <i class="fas fa-coins text-danger fa-2x"></i>
                                        </div>
                                        <div>
                                            <p class="mb-2">Total Cost</p>
                                            <h4>{{ format_inr($data['total_cost_price'] ?? 0) }}</h4>
                                        </div>
                                    </div>
                                    <div class="iq-progress-bar mt-2">
                                        <span class="bg-danger iq-progress progress-1" data-percent="70"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-4">
                            <div class="card card-block card-stretch card-height">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-4 card-total-sale">
                                        <div class="icon iq-icon-box-2 bg-success-light">
                                            <i class="fas fa-box-open text-success fa-2x"></i>
                                        </div>
                                        <div>
                                            <p class="mb-2">Sold Quantity</p>
                                            <h4>{{ $data['products'] ?? 0 }}</h4>
                                        </div>
                                    </div>
                                    <div class="iq-progress-bar mt-2">
                                        <span class="bg-success iq-progress progress-1" data-percent="75"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3">
                    <div class="card card-block card-stretch card-height">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4 card-total-sale">
                                <div class="icon iq-icon-box-2 orange-bg-light">
                                    <i class="fas fa-clone text-warning fa-2x"></i>
                                </div>
                                <div>
                                    <p class="mb-2">Total Stock Available</p>
                                    <h4>{{ $data['total_quantity'] ?? 0 }}</h4>
                                </div>
                            </div>
                            <div class="iq-progress-bar mt-2">
                                <span class="orange-bg iq-progress progress-1" data-percent="75"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3">
                    <div class="card card-block card-stretch card-height">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4 card-total-sale">
                                <div class="icon iq-icon-box-2 sky-blue-gb">
                                    <i class="fab fa-accusoft text-success fa-2x" title="Available Stock"></i>
                                </div>

                                <div>
                                    <p class="mb-2">Total Transaction</p>
                                    <h4>{{ $data['invoice_count'] ?? 0 }}</h4>
                                </div>
                            </div>
                            <div class="iq-progress-bar mt-2">
                                <span class="sky-blue-gb iq-progress progress-1" data-percent="75"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3">
                    <div class="card card-block card-stretch card-height">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4 card-total-sale">
                                <div class="icon iq-icon-box-2 sky-blue-gb">
                                    <i class="far fa-star text-success fa-2x" title="Available Stock"></i>
                                </div>

                                <div>
                                    <p class="mb-2">Guarantee Fulfilled</p>
                                    <h4>{{ $data['guaranteeFulfilled'] ?? 0 }}</h4>
                                </div>
                            </div>
                            <div class="iq-progress-bar mt-2">
                                <span class="sky-blue-gb iq-progress progress-1" data-percent="75"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3">
                    <div class="card card-block card-stretch card-height">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4 card-total-sale">
                                <div class="icon iq-icon-box-2 sky-blue-gb">
                                    <i class="ri-creative-commons-nc-fill" title="AED to be Paid"></i>
                                </div>

                                <div>
                                    <p class="mb-2">AED to be Paid</p>
                                    <h4>{{ $data['aedToBePaid'] ?? 0 }}</h4>
                                </div>
                            </div>
                            <div class="iq-progress-bar mt-2">
                                <span class="sky-blue-gb iq-progress progress-1" data-percent="75"></span>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Assets/Liabilities --}}
                @php($al = $data['assets_liabilities'] ?? [])
                @php($ca = $al[0] ?? null) {{-- Current Assets --}}
                @php($cl = $al[1] ?? null) {{-- Current Liabilities --}}
                <div class="col-lg-3 col-md-3">
                    <div class="card card-block card-stretch card-height">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4 card-total-sale">
                                <div class="icon iq-icon-box-2 orange-bg-light">
                                    <i class="fas fa-balance-scale text-warning fa-2x"></i>
                                </div>
                                <div>
                                    <p class="mb-2">Assets/Liabilities</p>
                                    <h4>{{ $ca['closing'] ?? '0.00' }} {{ $ca['side'] ?? '' }}</h4>
                                    <div class="small text-muted">{{ $ca['label'] ?? 'Current Assets' }}</div>
                                </div>
                            </div>

                            <div class="small d-flex justify-content-between">
                                <span>{{ $ca['label'] ?? 'Current Assets' }}</span>
                                <span>{{ $ca['closing'] ?? '0.00' }} {{ $ca['side'] ?? '' }}</span>
                            </div>
                            <div class="small d-flex justify-content-between">
                                <span>{{ $cl['label'] ?? 'Current Liabilities' }}</span>
                                <span>{{ $cl['closing'] ?? '0.00' }} {{ $cl['side'] ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cash In/Out Flow --}}
                @php($cf = $data['cash_flow'] ?? [])
                <div class="col-lg-3 col-md-3">
                    <div class="card card-block card-stretch card-height">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4 card-total-sale">
                                <div class="icon iq-icon-box-2 sky-blue-gb">
                                    @php($netUp = !Str::contains($data['cash_flow']['net'] ?? '', '-'))
                                    <i class="fas {{ $netUp ? 'fa-arrow-up text-success' : 'fa-arrow-down text-danger' }} fa-2x"
                                        title="Cash Flow"></i>
                                </div>
                                <div>
                                    <p class="mb-2">Cash In/Out Flow</p>
                                    <h4>{{ $cf['net'] ?? '0.00' }}</h4>
                                    <div class="small text-muted">Net Flow</div>
                                </div>
                            </div>

                            <div class="small d-flex justify-content-between">
                                <span>Inflow</span><span>{{ $cf['inflow'] ?? '0.00' }}</span>
                            </div>
                            <div class="small d-flex justify-content-between">
                                <span>Outflow</span><span>{{ $cf['outflow'] ?? '0.00' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cash/Bank Accounts --}}
                @php($cba = $data['cash_bank_accounts'] ?? [])
                <div class="col-lg-3 col-md-3">
                    <div class="card card-block card-stretch card-height">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4 card-total-sale">
                                <div class="icon iq-icon-box-2 sky-blue-gb">
                                    <i class="fas fa-piggy-bank text-success fa-2x" title="Cash/Bank"></i>
                                </div>
                                <div>
                                    <p class="mb-2">Cash/Bank Accounts</p>
                                    <h4>{{ $cba['closing'] ?? '0.00' }} {{ $cba['side'] ?? '' }}</h4>
                                    <div class="small text-muted">Closing Balance</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Trading Details --}}
                @php($td = $data['trading_details'] ?? [])
                <div class="col-lg-3 col-md-3">
                    <div class="card card-block card-stretch card-height">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4 card-total-sale">
                                <div class="icon iq-icon-box-2 sky-blue-gb">
                                    <i class="fas fa-chart-line text-primary fa-2x" title="Trading"></i>
                                </div>
                                <div>
                                    <p class="mb-2">Trading Details</p>
                                    <h4>{{ $td['gross_profit'] ?? '0.00' }}</h4>
                                    <div class="small text-muted">Gross Profit</div>
                                </div>
                            </div>

                            <div class="small d-flex justify-content-between">
                                <span>Nett Profit</span><span>{{ $td['nett_profit'] ?? '0.00' }}</span>
                            </div>
                            <div class="small d-flex justify-content-between">
                                <span>Sales Accounts</span><span>{{ $td['sales_accounts'] ?? '0.00' }}</span>
                            </div>
                            <div class="small d-flex justify-content-between">
                                <span>Purchase Accounts</span><span>{{ $td['purchase_accounts'] ?? '0.00' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Inventory Details --}}
                @php($inv = $data['inventory'] ?? [])
                <div class="col-lg-3 col-md-3">
                    <div class="card card-block card-stretch card-height">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4 card-total-sale">
                                <div class="icon iq-icon-box-2 sky-blue-gb">
                                    <i class="fas fa-boxes text-info fa-2x" title="Inventory"></i>
                                </div>
                                <div>
                                    <p class="mb-2">Inventory Details</p>
                                    <h4>{{ $inv['closing_value'] ?? '0.00' }}</h4>
                                    <div class="small text-muted">Closing Stock (Value)</div>
                                </div>
                            </div>

                            @if (!empty($inv['inwards_value']))
                            <div class="small d-flex justify-content-between">
                                <span>Inwards</span><span>{{ $inv['inwards_value'] }}</span>
                            </div>
                            @endif
                            @if (!empty($inv['outwards_value']))
                            <div class="small d-flex justify-content-between">
                                <span>Outwards</span><span>{{ $inv['outwards_value'] }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Accounting Ratios --}}
                @php($rat = $data['ratios'] ?? [])
                <div class="col-lg-3 col-md-3">
                    <div class="card card-block card-stretch card-height">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4 card-total-sale">
                                <div class="icon iq-icon-box-2 sky-blue-gb">
                                    <i class="fas fa-percentage text-secondary fa-2x" title="Ratios"></i>
                                </div>
                                <div>
                                    <p class="mb-2">Accounting Ratios</p>
                                    <h4>{{ !empty($rat['roi_percent']) ? $rat['roi_percent'] . ' %' : '—' }}</h4>
                                    <div class="small text-muted">Return on Investment</div>
                                </div>
                            </div>

                            <div class="small d-flex justify-content-between">
                                <span>Inventory Turnover</span><span>{{ $rat['inventory_turnover'] ?? '—' }}</span>
                            </div>
                            <div class="small d-flex justify-content-between">
                                <span>Debt/Equity</span><span>{{ $rat['debt_equity'] ?? '—' }}</span>
                            </div>
                            <div class="small d-flex justify-content-between">
                                <span>Receivable Turnover
                                    (Days)</span><span>{{ $rat['receivable_turnover_days'] ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Receivables/Payables --}}
                @php($rp = $data['receivablesPayables'] ?? [])
                <div class="col-lg-3 col-md-3">
                    <div class="card card-block card-stretch card-height">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4 card-total-sale">
                                <div class="icon iq-icon-box-2 sky-blue-gb">
                                    <i class="fas fa-file-invoice-dollar text-primary fa-2x" title="AR/AP"></i>
                                </div>
                                <div>
                                    <p class="mb-2">Receivables / Payables</p>
                                    <h4>{{ $rp['receivables'] ?? '0.00' }}</h4>
                                    <div class="small text-muted">Receivables</div>
                                </div>
                            </div>

                            <div class="small d-flex justify-content-between">
                                <span>Payables</span><span>{{ $rp['payables'] ?? '0.00' }}</span>
                            </div>
                            @if (array_key_exists('overdue_receivables', $rp) && !is_null($rp['overdue_receivables']))
                            <div class="small d-flex justify-content-between">
                                <span>Overdue Receivables</span><span>{{ $rp['overdue_receivables'] }}</span>
                            </div>
                            @endif
                            @if (array_key_exists('overdue_payables', $rp) && !is_null($rp['overdue_payables']))
                            <div class="small d-flex justify-content-between">
                                <span>Overdue Payables</span><span>{{ $rp['overdue_payables'] }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Top Groups/Ledgers (Bank) --}}
                @php($tbl = $data['top_bank_ledgers'] ?? [])
                <div class="col-lg-3 col-md-3">
                    <div class="card card-block card-stretch card-height">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4 card-total-sale">
                                <div class="icon iq-icon-box-2 sky-blue-gb">
                                    <i class="fas fa-university text-primary fa-2x" title="Top Banks"></i>
                                </div>
                                <div>
                                    <p class="mb-2">Top Groups/Ledgers (Bank)</p>
                                    <h4>{{ isset($tbl[0]) ? $tbl[0]['closing'] . ' ' . $tbl[0]['side'] : '—' }}
                                    </h4>
                                    <div class="small text-muted">{{ $tbl[0]['name'] ?? '' }}</div>
                                </div>
                            </div>

                            @foreach ($tbl ?? [] as $i => $r)
                            @if ($i === 0)
                            @continue
                            @endif
                            <div class="small d-flex justify-content-between">
                                <span>{{ $r['name'] }}</span>
                                <span>{{ $r['closing'] }} {{ $r['side'] }}</span>
                            </div>
                            @endforeach
                            @if (empty($tbl))
                            <div class="small text-muted">No data</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Start Chart -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">Sales Trend</h4>
                            </div>
                            <div class="card-header-toolbar d-flex align-items-center">
                                <div class="dropdown">
                                    <span class="dropdown-toggle dropdown-bg btn"
                                        id="dropdownMenuButtonFySalesTrend"
                                        data-toggle="dropdown">
                                        {{ request('fy') ?? $data['current_fy'] }} <i class="ri-arrow-down-s-line ml-1"></i>
                                    </span>

                                    <div class="dropdown-menu dropdown-menu-right shadow-none"
                                        aria-labelledby="dropdownMenuButtonFySalesTrend">

                                        @foreach ($data['financial_year_dropdown'] as $fy)
                                        <a class="dropdown-item"
                                            href="javascript:void(0)"
                                            onclick="updateSalesTrendChart('{{ $fy }}', this)">
                                            {{ $fy }}
                                        </a>

                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="apex-basic1"></div>
                        </div>
                    </div>

                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">Purchase Trend</h4>
                            </div>
                            <div class="card-header-toolbar d-flex align-items-center">
                                <div class="dropdown">
                                    <span class="dropdown-toggle dropdown-bg btn"
                                        id="dropdownMenuButtonFYPurchaseTrend"
                                        data-toggle="dropdown">
                                        {{ request('fy') ?? $data['current_fy'] }}
                                        <i class="ri-arrow-down-s-line ml-1"></i>
                                    </span>

                                    <div class="dropdown-menu dropdown-menu-right shadow-none"
                                        aria-labelledby="dropdownMenuButtonFYPurchaseTrend">

                                        @foreach ($data['financial_year_dropdown'] as $fy)
                                        <a class="dropdown-item"
                                            href="javascript:void(0)"
                                            onclick="updatePurchaseTrendChart('{{ $fy }}', this)">
                                            {{ $fy }}
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="apex-basic2"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">Sales Overview</h4>
                            </div>
                            <div class="card-header-toolbar d-flex align-items-center">
                                <div class="dropdown">
                                    <span class="dropdown-toggle dropdown-bg btn"
                                        id="dropdownMenuButtonFYSalesOverview"
                                        data-toggle="dropdown">
                                        {{ request('fy') ?? $data['current_fy'] }}
                                        <i class="ri-arrow-down-s-line ml-1"></i>
                                    </span>

                                    <div class="dropdown-menu dropdown-menu-right shadow-none" aria-labelledby="dropdownMenuButtonFYSalesOverview">

                                        @foreach ($data['financial_year_dropdown'] as $fy)
                                        <a class="dropdown-item"
                                            href="javascript:void(0)"
                                            onclick="updateSalesOverviewChart('{{ $fy }}')">
                                            {{ $fy }}
                                        </a>
                                        @endforeach

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="apex-bar"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card card-block card-stretch card-height">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">Revenue Vs Cost</h4>
                            </div>
                            <div class="card-header-toolbar d-flex align-items-center">
                                <div class="dropdown">
                                    <span class="dropdown-toggle dropdown-bg btn"
                                        id="dropdownMenuButtonFYRevenueVsCost"
                                        data-toggle="dropdown">
                                        {{ request('fy') ?? $data['current_fy'] }} <i class="ri-arrow-down-s-line ml-1"></i>
                                    </span>

                                    <div class="dropdown-menu dropdown-menu-right shadow-none"
                                        aria-labelledby="dropdownMenuButtonFYRevenueVsCost">

                                        @foreach ($data['financial_year_dropdown'] as $fy)
                                        <a class="dropdown-item"
                                            href="javascript:void(0)"
                                            onclick="updateRevenueVsCostChart('{{ $fy }}')">
                                            {{ $fy }}
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="layout1-chart-2" style="min-height: 360px"></div>
                        </div>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="col-lg-6">
                    <div class="card card-transparent card-block card-stretch mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between p-0">
                            <div class="header-title">
                                <h4 class="card-title mb-0">Top Products</h4>
                            </div>
                            <div class="card-header-toolbar d-flex align-items-center gap-2">

                                <div class="mr-3">
                                    <a href="{{ route('reports.best_selling.view') }}" class="btn btn-primary">
                                        View All
                                    </a>
                                </div>

                                <div class="dropdown">
                                    <span class="dropdown-toggle dropdown-bg btn"
                                        id="dropdownMenuButtonFYTopProduct"
                                        data-toggle="dropdown">
                                        {{ request('fy') ?? $data['current_fy'] }}
                                        <i class="ri-arrow-down-s-line ml-1"></i>
                                    </span>

                                    <div class="dropdown-menu dropdown-menu-right shadow-none" aria-labelledby="dropdownMenuButtonFYTopProduct">

                                        @foreach ($data['financial_year_dropdown'] as $fy)
                                        <a class="dropdown-item"
                                            href="javascript:void(0)"
                                            onclick="updateTopProducts('{{ $fy }}')">
                                            {{ $fy }}
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="topProductsWrapper">
                        @foreach($data['top_and_worst_product']['top'] as $category => $product)
                        @if($product)
                        <div class="card card-block card-stretch card-height-helf mb-3">
                            <div class="card-body card-item-right">
                                <div class="d-flex align-items-top">
                                    <div class="bg-warning-light rounded">
                                        <img src="{{ asset($categoryImages[$category]) }}"
                                            class="style-img m-auto"
                                            style="width: 250px; height: 180px;"
                                            alt="{{ $category }}" />
                                    </div>
                                    <div class="style-text text-left ml-3">
                                        <h5 class="mb-1">{{ $product->product_name }}</h5>
                                        <p class="mb-1">Category : {{ $category }}</p>
                                        <p class="mb-1">Total Quantity : {{ number_format($product->total_qty) }}</p>
                                        <p class="mb-0">Total Sell Amount : ₹{{ number_format($product->total_amount, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>

                <!-- Worst Products -->
                <div class="col-lg-6">
                    <div class="card card-transparent card-block card-stretch mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between p-0">
                            <div class="header-title">
                                <h4 class="card-title mb-0">Worst Products</h4>
                            </div>
                            <div class="card-header-toolbar d-flex align-items-center">
                                <div class="mr-3">
                                    <a href="{{ route('reports.worst_selling.view') }}" class="btn btn-primary">
                                        View All
                                    </a>
                                </div>
                                <div class="dropdown">
                                    <span class="dropdown-toggle dropdown-bg btn"
                                        id="dropdownMenuButtonFYWorstProduct"
                                        data-toggle="dropdown">
                                        {{ request('fy') ?? $data['current_fy'] }}
                                        <i class="ri-arrow-down-s-line ml-1"></i>
                                    </span>

                                    <div class="dropdown-menu dropdown-menu-right shadow-none" aria-labelledby="dropdownMenuButtonFYWorstProduct">

                                        @foreach ($data['financial_year_dropdown'] as $fy)
                                        <a class="dropdown-item"
                                            href="javascript:void(0)"
                                            onclick="updateWorstProducts('{{ $fy }}')">
                                            {{ $fy }}
                                        </a>
                                        @endforeach

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="worstProductsWrapper">
                        @foreach($data['top_and_worst_product']['worst'] as $category => $product)
                        @if($product)
                        <div class="card card-block card-stretch card-height-helf mb-3">
                            <div class="card-body card-item-right">
                                <div class="d-flex align-items-top">
                                    <div class="bg-danger-light rounded">
                                        <img src="{{ asset($categoryImages[$category]) }}"
                                            class="style-img m-auto"
                                            style="width: 250px; height: 180px;"
                                            alt="{{ $category }}" />
                                    </div>
                                    <div class="style-text text-left ml-3">
                                        <h5 class="mb-1">{{ $product->product_name }}</h5>
                                        <p class="mb-1">Category : {{ $category }}</p>
                                        <p class="mb-1">Total Quantity : {{ number_format($product->total_qty) }}</p>
                                        <p class="mb-0">Total Sell Amount : ₹{{ number_format($product->total_amount, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card card-block card-stretch card-height-helf">
                        <div class="card-body">
                            <div class="d-flex align-items-top justify-content-between">
                                <div class="">
                                    <p class="mb-0">Income</p>
                                    <h5 id="incomeTotal">₹ {{ number_format($data['total_financial_year_income'], 0, '.', ',') }}</h5>
                                </div>
                                <div class="card-header-toolbar d-flex align-items-center">
                                    <div class="dropdown">
                                        <span class="dropdown-toggle dropdown-bg btn"
                                            id="dropdownMenuButtonFYIncome"
                                            data-toggle="dropdown">
                                            {{ $data['current_fy'] }} <i class="ri-arrow-down-s-line ml-1"></i>
                                        </span>

                                        <div class="dropdown-menu dropdown-menu-right shadow-none"
                                            aria-labelledby="dropdownMenuButtonFYIncome">

                                            @foreach ($data['financial_year_dropdown'] as $fy)
                                            <a class="dropdown-item"
                                                href="javascript:void(0)"
                                                onclick="updateIncomeExpenseFinancialChart('income', '{{ $fy }}')">
                                                {{ $fy }}
                                            </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="layout1-chart-3" class="layout-chart-1"></div>
                        </div>
                    </div>
                    <div class="card card-block card-stretch card-height-helf">
                        <div class="card-body">
                            <div class="d-flex align-items-top justify-content-between">
                                <div class="">
                                    <p class="mb-0">Expenses</p>
                                    <h5 id="expenseTotal">₹ {{ number_format($data['total_financial_year_expenses'], 0, '.', ',') }}</h5>
                                </div>
                                <div class="card-header-toolbar d-flex align-items-center">
                                    <div class="dropdown">
                                        <span class="dropdown-toggle dropdown-bg btn"
                                            id="dropdownMenuButtonFYExpense"
                                            data-toggle="dropdown">
                                            {{ $data['current_fy'] }} <i class="ri-arrow-down-s-line ml-1"></i>
                                        </span>

                                        <div class="dropdown-menu dropdown-menu-right shadow-none"
                                            aria-labelledby="dropdownMenuButtonFYExpense">

                                            @foreach ($data['financial_year_dropdown'] as $fy)
                                            <a class="dropdown-item"
                                                href="javascript:void(0)"
                                                onclick="updateIncomeExpenseFinancialChart('expense', '{{ $fy }}')">
                                                {{ $fy }}
                                            </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="layout1-chart-4" class="layout-chart-2"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title"> Sales Ratio Analysis</h4>
                            </div>
                            <div class="card-header-toolbar d-flex align-items-center">
                                <div class="dropdown">
                                    <span class="dropdown-toggle dropdown-bg btn"
                                        id="dropdownMenuButtonFYPie"
                                        data-toggle="dropdown">
                                        {{ $data['current_fy'] }} <i class="ri-arrow-down-s-line ml-1"></i>
                                    </span>

                                    <div class="dropdown-menu dropdown-menu-right shadow-none"
                                        aria-labelledby="dropdownMenuButtonFYPie">

                                        @foreach ($data['financial_year_dropdown'] as $fy)
                                        <a class="dropdown-item"
                                            href="javascript:void(0)"
                                            onclick="updatePieChart('{{ $fy }}')">
                                            {{ $fy }}
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="apex-pie-chart"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="card card-block card-stretch card-height">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">Sales Daily Overview</h4>
                            </div>
                        </div>

                        <div class="card-body pt-0">
                            <div id="salesDailyChart"></div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Page end  -->
        </div>
    </div>
</div>
<?php
// dd($data);
?>
<!-- Wrapper End-->
@endsection

<script defer>
    /* ===================== DATA ===================== */
    const {
        categories,
        sales_quantity_by_month,
        purchase_quantity_by_month,
        sales_amount_total,
        financial_year_income,
        financial_year_expenses,
        all_days_of_current_month,
        sales_quantity_by_day,
        sales_amount_by_day,
        pie_branch_name,
        pie_total_item_qty,
        revenue_value,
        cost_value
    } = @json($data);

    /* ===================== CHART INSTANCES ===================== */
    let charts = {};

    /* ===================== HELPERS ===================== */
    function createLineChart(el, series, color, height = 350, title = '') {
        return new ApexCharts(el, {
            chart: {
                height,
                type: 'line',
                zoom: {
                    enabled: false
                }
            },
            colors: [color],
            series,
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'straight'
            },
            title: title ? {
                text: title,
                align: 'left'
            } : undefined,
            xaxis: {
                categories
            },
            yaxis: {
                title: {
                    text: 'Quantity'
                }
            }
        });
    }

    function createSparkLine(el, data, color) {
        return new ApexCharts(el, {
            chart: {
                type: 'line',
                height: 120,
                sparkline: {
                    enabled: true
                }
            },
            colors: [color],
            series: [{
                data
            }],
            stroke: {
                curve: 'smooth',
                width: 3
            },
            xaxis: {
                categories
            },
            tooltip: {
                y: {
                    title: {
                        formatter: () => ''
                    }
                }
            }
        });
    }

    function updateDropdown(id, fy) {
        const el = document.getElementById(id);
        if (el) el.innerHTML = `${fy} <i class="ri-arrow-down-s-line ml-1"></i>`;
    }

    function fetchAndUpdateChart(url, chart, callback) {
        fetch(url)
            .then(r => r.json())
            .then(data => callback(chart, data));
    }

    /* ===================== INIT ===================== */
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.ApexCharts) return console.error('ApexCharts not loaded');

        /* Sales Trend */
        charts.sales = createLineChart(
            document.getElementById('apex-basic1'),
            [{
                name: 'Sales Qty',
                data: sales_quantity_by_month
            }],
            '#4788ff',
            350,
            'Sales Quantity by Month'
        );
        charts.sales?.render();

        /* Purchase Trend */
        charts.purchase = createLineChart(
            document.getElementById('apex-basic2'),
            [{
                name: 'Purchase Qty',
                data: purchase_quantity_by_month
            }],
            '#4788ff',
            350,
            'Purchase Quantity by Month'
        );
        charts.purchase?.render();

        /* Sales Overview */
        charts.salesOverview = new ApexCharts(
            document.getElementById('apex-bar'), {
                chart: {
                    height: 350,
                    type: 'bar'
                },
                plotOptions: {
                    bar: {
                        horizontal: true
                    }
                },
                colors: ['#4788ff'],
                series: [{
                    data: sales_amount_total
                }],
                xaxis: {
                    categories: categories,
                    title: {
                        text: 'Amount (₹)'
                    }
                },
                tooltip: {
                    y: {
                        title: {
                            formatter: () => ''
                        }
                    }
                }
            }
        );
        charts.salesOverview?.render();

        /* Income */
        charts.income = createSparkLine(
            document.getElementById('layout1-chart-3'),
            financial_year_income,
            '#FF7E41'
        );
        charts.income?.render();

        /* Expense */
        charts.expense = createSparkLine(
            document.getElementById('layout1-chart-4'),
            financial_year_expenses,
            '#32BDEA'
        );
        charts.expense?.render();

        /* Daily Sales */
        charts.dailySales = new ApexCharts(
            document.getElementById('salesDailyChart'), {
                chart: {
                    type: 'bar',
                    height: 430
                },
                colors: ['#ff7a18'],
                series: [{
                    name: 'Sales',
                    data: sales_quantity_by_day
                }],
                xaxis: {
                    categories: all_days_of_current_month
                },
                tooltip: {
                    custom: ({
                        series,
                        dataPointIndex
                    }) => `
                        <div style="padding:10px;background:#ff4d4f;color:#fff">
                            <div><strong>Qty:</strong> ${series[0][dataPointIndex]}</div>
                            <div><strong>Amt:</strong> ₹${sales_amount_by_day[dataPointIndex]}</div>
                        </div>`
                },
                yaxis: {
                    title: {
                        text: 'Quantity'
                    }
                }
            }
        );
        charts.dailySales?.render();

        /* Pie */
        charts.pie = new ApexCharts(
            document.getElementById('apex-pie-chart'), {
                chart: {
                    type: 'pie',
                    height: 380,
                },
                labels: pie_branch_name,
                series: pie_total_item_qty,
                plotOptions: {
                    pie: {
                        dataLabels: {
                            offset: 0
                        }
                    }
                },
                legend: {
                    position: 'right',
                    fontSize: '14px',
                    markers: {
                        width: 12,
                        height: 12,
                        radius: 12
                    },
                    itemMargin: {
                        horizontal: 12,
                        vertical: 8
                    }
                },
                responsive: [{
                    breakpoint: 400,
                    options: {
                        chart: {
                            height: 500
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            }
        );
        charts.pie.render();

        /* Revenue Vs Cost */
        charts.revenueVsCost = new ApexCharts(
            document.getElementById('layout1-chart-2'), {
                chart: {
                    type: 'bar',
                    height: 360,
                    stacked: false,
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#32BDEA', '#FF7E41'],
                series: [{
                        name: 'Revenue',
                        data: revenue_value
                    },
                    {
                        name: 'Cost',
                        data: cost_value
                    }
                ],
                xaxis: {
                    categories: categories
                },
                yaxis: {
                    title: {
                        text: 'Amount (₹)'
                    }
                },
                legend: {
                    position: 'top'
                },
                dataLabels: {
                    enabled: false,
                },
                tooltip: {
                    y: {
                        formatter: val => '₹ ' + val.toLocaleString('en-IN')
                    }
                }
            }
        );

        charts.revenueVsCost.render();
    });

    /* ===================== DROPDOWN UPDATES ===================== */
    function updateSalesTrendChart(fy) {
        updateDropdown('dropdownMenuButtonFySalesTrend', fy);
        fetchAndUpdateChart(`/dashboard/chart/sales-trend?fy=${fy}`, charts.sales, (c, d) => {
            c.updateOptions({
                xaxis: {
                    categories: d.categories
                }
            });
            c.updateSeries(d.series);
        });
    }

    function updatePurchaseTrendChart(fy) {
        updateDropdown('dropdownMenuButtonFYPurchaseTrend', fy);
        fetchAndUpdateChart(`/dashboard/chart/purchase-trend?fy=${fy}`, charts.purchase, (c, d) => {
            c.updateOptions({
                xaxis: {
                    categories: d.categories
                }
            });
            c.updateSeries(d.series);
        });
    }

    function updateSalesOverviewChart(fy) {
        updateDropdown('dropdownMenuButtonFYSalesOverview', fy);

        fetchAndUpdateChart(
            `/dashboard/chart/sales-overview?fy=${fy}`,
            charts.salesOverview,
            (chart, data) => {
                chart.updateOptions({
                    xaxis: {
                        categories: data.categories
                    }
                });

                chart.updateSeries([{
                    data: data.series
                }]);
            }
        );
    }

    function updateIncomeExpenseFinancialChart(type, fy) {

        const map = {
            income: {
                chart: charts.income,
                dropdown: 'dropdownMenuButtonFYIncome',
                totalEl: 'incomeTotal'
            },
            expense: {
                chart: charts.expense,
                dropdown: 'dropdownMenuButtonFYExpense',
                totalEl: 'expenseTotal'
            }
        };

        updateDropdown(map[type].dropdown, fy);

        fetch(`/dashboard/chart/income-expense-financial-chart?type=${type}&fy=${fy}`)
            .then(res => res.json())
            .then(data => {

                map[type].chart.updateOptions({
                    xaxis: {
                        categories: data.categories
                    }
                }, false, true);

                map[type].chart.updateSeries([{
                    data: data.series
                }], true);

                const totalEl = document.getElementById(map[type].totalEl);
                if (totalEl && typeof data.total !== 'undefined') {
                    totalEl.innerText =
                        '₹ ' + Number(data.total).toLocaleString('en-IN');
                }
            })
            .catch(err => console.error(err));

    }

    function updatePieChart(fy) {
        updateDropdown('dropdownMenuButtonFYPie', fy);
        fetchAndUpdateChart(`/dashboard/chart/pie-chart?fy=${fy}`, charts.pie,
            (c, d) => {
                c.updateOptions({
                    labels: d.labels
                });
                c.updateSeries(d.series);
            }
        );
    }

    function updateTopProducts(fy) {
        $('#dropdownMenuButtonFYTopProduct')
            .html(fy + ' <i class="ri-arrow-down-s-line ml-1"></i>');

        $('#topProductsWrapper').html('<p class="text-center">Loading...</p>');

        $.get("{{ route('dashboard.ajax.top-worst-products') }}", {
            fy: fy,
            type: 'top'
        }, function(res) {
            $('#topProductsWrapper').html(res.html);
        }).fail(() => alert('Failed to load top products'));
    }

    function updateWorstProducts(fy) {
        $('#dropdownMenuButtonFYWorstProduct')
            .html(fy + ' <i class="ri-arrow-down-s-line ml-1"></i>');

        $('#worstProductsWrapper').html('<p class="text-center">Loading...</p>');

        $.get("{{ route('dashboard.ajax.top-worst-products') }}", {
            fy: fy,
            type: 'worst'
        }, function(res) {
            $('#worstProductsWrapper').html(res.html);
        }).fail(() => alert('Failed to load worst products'));
    }

    function updateRevenueVsCostChart(fy) {
        updateDropdown('dropdownMenuButtonFYRevenueVsCost', fy);

        fetch(`/dashboard/chart/revenue-vs-cost?fy=${fy}`)
            .then(res => res.json())
            .then(data => {
                charts.revenueVsCost.updateOptions({
                    xaxis: {
                        categories: data.categories
                    }
                });

                charts.revenueVsCost.updateSeries(data.series);
            })
            .catch(err => console.error(err));
    }

    function updateRevenueVsCostChart(fy) {
        updateDropdown('dropdownMenuButtonFYRevenueVsCost', fy);

        fetch(`/dashboard/chart/revenue-vs-cost?fy=${fy}`)
            .then(res => res.json())
            .then(data => {
                charts.revenueVsCost.updateOptions({
                    xaxis: {
                        categories: data.categories
                    }
                });

                charts.revenueVsCost.updateSeries(data.series);
            })
            .catch(err => console.error(err));
    }
</script>