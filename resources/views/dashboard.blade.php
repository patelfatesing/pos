    @extends('layouts.backend.layouts')
    @section('page-content')
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

                    <div class="row">

                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between">
                                    <div class="header-title">
                                        <h4 class="card-title">Sales Trend</h4>
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
                                </div>
                                <div class="card-body">
                                    <div id="apex-basic2"></div>
                                </div>
                            </div>

                        </div>
                     
                        <div class="col-lg-6">
                            <div class="card card-block card-stretch card-height">
                                <div class="card-header d-flex justify-content-between">
                                    <div class="header-title">
                                        <h4 class="card-title">Overview</h4>
                                    </div>
                                    <div class="card-header-toolbar d-flex align-items-center">
                                        <div class="dropdown">
                                            <span class="dropdown-toggle dropdown-bg btn" id="dropdownMenuButton001"
                                                data-toggle="dropdown">
                                                This Month<i class="ri-arrow-down-s-line ml-1"></i>
                                            </span>
                                            <div class="dropdown-menu dropdown-menu-right shadow-none"
                                                aria-labelledby="dropdownMenuButton001">
                                                <a class="dropdown-item" href="#">Year</a>
                                                <a class="dropdown-item" href="#">Month</a>
                                                <a class="dropdown-item" href="#">Week</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="layout1-chart1"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card card-block card-stretch card-height">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <div class="header-title">
                                        <h4 class="card-title">Revenue Vs Cost</h4>
                                    </div>
                                    <div class="card-header-toolbar d-flex align-items-center">
                                        <div class="dropdown">
                                            <span class="dropdown-toggle dropdown-bg btn" id="dropdownMenuButton002"
                                                data-toggle="dropdown">
                                                This Month<i class="ri-arrow-down-s-line ml-1"></i>
                                            </span>
                                            <div class="dropdown-menu dropdown-menu-right shadow-none"
                                                aria-labelledby="dropdownMenuButton002">
                                                <a class="dropdown-item" href="#">Yearly</a>
                                                <a class="dropdown-item" href="#">Monthly</a>
                                                <a class="dropdown-item" href="#">Weekly</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="layout1-chart-2" style="min-height: 360px"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="card card-block card-stretch card-height">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <div class="header-title">
                                        <h4 class="card-title">Top Products</h4>
                                    </div>
                                    <div class="card-header-toolbar d-flex align-items-center">
                                        <div class="dropdown">
                                            <span class="dropdown-toggle dropdown-bg btn" id="dropdownMenuButton006"
                                                data-toggle="dropdown">
                                                This Month<i class="ri-arrow-down-s-line ml-1"></i>
                                            </span>
                                            <div class="dropdown-menu dropdown-menu-right shadow-none"
                                                aria-labelledby="dropdownMenuButton006">
                                                <a class="dropdown-item" href="#">Year</a>
                                                <a class="dropdown-item" href="#">Month</a>
                                                <a class="dropdown-item" href="#">Week</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled row top-product mb-0">
                                        <li class="col-lg-3">
                                            <div class="card card-block card-stretch card-height mb-0">
                                                <div class="card-body">
                                                    <div class="bg-warning-light rounded">
                                                        <img src="{{ asset('assets/images/product/01.png') }}"
                                                            class="style-img img-fluid m-auto p-3" alt="image" />
                                                    </div>
                                                    <div class="style-text text-left mt-3">
                                                        <h5 class="mb-1">Organic Cream</h5>
                                                        <p class="mb-0">789 Item</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="col-lg-3">
                                            <div class="card card-block card-stretch card-height mb-0">
                                                <div class="card-body">
                                                    <div class="bg-danger-light rounded">
                                                        <img src="{{ asset('assets/images/product/02.png') }}"
                                                            class="style-img img-fluid m-auto p-3" alt="image" />
                                                    </div>
                                                    <div class="style-text text-left mt-3">
                                                        <h5 class="mb-1">Rain Umbrella</h5>
                                                        <p class="mb-0">657 Item</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="col-lg-3">
                                            <div class="card card-block card-stretch card-height mb-0">
                                                <div class="card-body">
                                                    <div class="bg-info-light rounded">
                                                        <img src="{{ asset('assets/images/product/03.png') }}"
                                                            class="style-img img-fluid m-auto p-3" alt="image" />
                                                    </div>
                                                    <div class="style-text text-left mt-3">
                                                        <h5 class="mb-1">Serum Bottle</h5>
                                                        <p class="mb-0">489 Item</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="col-lg-3">
                                            <div class="card card-block card-stretch card-height mb-0">
                                                <div class="card-body">
                                                    <div class="bg-success-light rounded">
                                                        <img src="{{ asset('assets/images/product/02.png') }}"
                                                            class="style-img img-fluid m-auto p-3" alt="image" />
                                                    </div>
                                                    <div class="style-text text-left mt-3">
                                                        <h5 class="mb-1">Organic Cream</h5>
                                                        <p class="mb-0">468 Item</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card card-transparent card-block card-stretch mb-4">
                                <div class="card-header d-flex align-items-center justify-content-between p-0">
                                    <div class="header-title">
                                        <h4 class="card-title mb-0">Best Item All Time</h4>
                                    </div>
                                    <div class="card-header-toolbar d-flex align-items-center">
                                        <div>
                                            <a href="#" class="btn btn-primary view-btn font-size-14">View All</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card card-block card-stretch card-height-helf">
                                <div class="card-body card-item-right">
                                    <div class="d-flex align-items-top">
                                        <div class="bg-warning-light rounded">
                                            <img src="{{ asset('assets/images/product/04.png') }}"
                                                class="style-img img-fluid m-auto" alt="image" />
                                        </div>
                                        <div class="style-text text-left">
                                            <h5 class="mb-2">Coffee Beans Packet</h5>
                                            <p class="mb-2">Total Sell : 45897</p>
                                            <p class="mb-0">Total Earned : $45,89 M</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card card-block card-stretch card-height-helf">
                                <div class="card-body card-item-right">
                                    <div class="d-flex align-items-top">
                                        <div class="bg-danger-light rounded">
                                            <img src="{{ asset('assets/images/product/05.png') }}"
                                                class="style-img img-fluid m-auto" alt="image" />
                                        </div>
                                        <div class="style-text text-left">
                                            <h5 class="mb-2">Bottle Cup Set</h5>
                                            <p class="mb-2">Total Sell : 44359</p>
                                            <p class="mb-0">Total Earned : $45,50 M</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card card-block card-stretch card-height-helf">
                                <div class="card-body">
                                    <div class="d-flex align-items-top justify-content-between">
                                        <div class="">
                                            <p class="mb-0">Income</p>
                                            <h5>$ 98,7800 K</h5>
                                        </div>
                                        <div class="card-header-toolbar d-flex align-items-center">
                                            <div class="dropdown">
                                                <span class="dropdown-toggle dropdown-bg btn" id="dropdownMenuButton003"
                                                    data-toggle="dropdown">
                                                    This Month<i class="ri-arrow-down-s-line ml-1"></i>
                                                </span>
                                                <div class="dropdown-menu dropdown-menu-right shadow-none"
                                                    aria-labelledby="dropdownMenuButton003">
                                                    <a class="dropdown-item" href="#">Year</a>
                                                    <a class="dropdown-item" href="#">Month</a>
                                                    <a class="dropdown-item" href="#">Week</a>
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
                                            <h5>$ 45,8956 K</h5>
                                        </div>
                                        <div class="card-header-toolbar d-flex align-items-center">
                                            <div class="dropdown">
                                                <span class="dropdown-toggle dropdown-bg btn" id="dropdownMenuButton004"
                                                    data-toggle="dropdown">
                                                    This Month<i class="ri-arrow-down-s-line ml-1"></i>
                                                </span>
                                                <div class="dropdown-menu dropdown-menu-right shadow-none"
                                                    aria-labelledby="dropdownMenuButton004">
                                                    <a class="dropdown-item" href="#">Year</a>
                                                    <a class="dropdown-item" href="#">Month</a>
                                                    <a class="dropdown-item" href="#">Week</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="layout1-chart-4" class="layout-chart-2"></div>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="col-lg-8">
                            <div class="card card-block card-stretch card-height">
                                <div class="card-header d-flex justify-content-between">
                                    <div class="header-title">
                                        <h4 class="card-title">Order Summary</h4>
                                    </div>
                                    <div class="card-header-toolbar d-flex align-items-center">
                                        <div class="dropdown">
                                            <span class="dropdown-toggle dropdown-bg btn" id="dropdownMenuButton005"
                                                data-toggle="dropdown">
                                                This Month<i class="ri-arrow-down-s-line ml-1"></i>
                                            </span>
                                            <div class="dropdown-menu dropdown-menu-right shadow-none"
                                                aria-labelledby="dropdownMenuButton005">
                                                <a class="dropdown-item" href="#">Year</a>
                                                <a class="dropdown-item" href="#">Month</a>
                                                <a class="dropdown-item" href="#">Week</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-wrap align-items-center mt-2">
                                        <div class="d-flex align-items-center progress-order-left">
                                            <div class="progress progress-round m-0 orange conversation-bar"
                                                data-percent="46">
                                                <span class="progress-left">
                                                    <span class="progress-bar"></span>
                                                </span>
                                                <span class="progress-right">
                                                    <span class="progress-bar"></span>
                                                </span>
                                                <div class="progress-value text-secondary">46%</div>
                                            </div>
                                            <div class="progress-value ml-3 pr-5 border-right">
                                                <h5>$12,6598</h5>
                                                <p class="mb-0">Average Orders</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center ml-5 progress-order-right">
                                            <div class="progress progress-round m-0 primary conversation-bar"
                                                data-percent="46">
                                                <span class="progress-left">
                                                    <span class="progress-bar"></span>
                                                </span>
                                                <span class="progress-right">
                                                    <span class="progress-bar"></span>
                                                </span>
                                                <div class="progress-value text-primary">46%</div>
                                            </div>
                                            <div class="progress-value ml-3">
                                                <h5>$59,8478</h5>
                                                <p class="mb-0">Top Orders</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <div id="layout1-chart-5"></div>
                                </div>
                            </div>
                        </div> --}}
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
        const seriesData = @json($data['data_sales']); // 12 numbers
        const categories = @json($data['categories']);

        document.addEventListener('DOMContentLoaded', function() {
            const el = document.getElementById('apex-basic1');
            if (!el) return; // Chart container not on this page

            if (!window.ApexCharts) { // ApexCharts not loaded
                console.error('ApexCharts is not loaded. Include it before this script.');
                return;
            }

            let options = {
                chart: {
                    height: 350,
                    type: 'line',
                    zoom: {
                        enabled: false
                    }
                },
                colors: ['#4788ff'],
                series: [{
                    name: 'Desktops',
                    data: seriesData
                }],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'straight'
                },
                title: {
                    text: 'Sales by Month',
                    align: 'left'
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: .5
                    }
                },
                xaxis: {
                    categories: categories,
                    labels: {
                        rotate: -45
                    } // helps avoid overlap
                }
            };

            const chart = new ApexCharts(el, options);
            chart.render();

            const body = document.body;
            if (body.classList.contains('dark') && typeof window.apexChartUpdate === 'function') {
                window.apexChartUpdate(chart, {
                    dark: true
                });
            }

            document.addEventListener('ChangeColorMode', function(e) {
                if (typeof window.apexChartUpdate === 'function') {
                    window.apexChartUpdate(chart, e.detail);
                }
            });

            const el2 = document.getElementById('apex-basic2');
            if (!el2) return; // Chart container not on this page

            if (!window.ApexCharts) { // ApexCharts not loaded
                console.error('ApexCharts is not loaded. Include it before this script.');
                return;
            }

            let options2 = {
                chart: {
                    height: 350,
                    type: 'line',
                    zoom: {
                        enabled: false
                    }
                },
                colors: ['#4788ff'],
                series: [{
                    name: 'Desktops',
                    data: @json($data['data_pur'])
                }],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'straight'
                },
                title: {
                    text: 'Sales by Month',
                    align: 'left'
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: .5
                    }
                },
                xaxis: {
                    categories: categories
                }
            };

            const chart2 = new ApexCharts(el2, options2);
            chart2.render();

            const body2 = document.body;
            if (body2.classList.contains('dark') && typeof window.apexChartUpdate === 'function') {
                window.apexChartUpdate(chart2, {
                    dark: true
                });
            }

            document.addEventListener('ChangeColorMode', function(e) {
                if (typeof window.apexChartUpdate === 'function') {
                    window.apexChartUpdate(chart2, e.detail);
                }
            });
        });
    </script>
