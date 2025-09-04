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
                        <div class="col-lg-6">
                            <div class="card card-block card-stretch card-height-helf">
                                <div class="card-body">
                                    <div class="d-flex align-items-top justify-content-between">
                                        <div class="">
                                            <p class="mb-0">Sales Trend</p>
                                            <h5></h5>
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
                                    <div id="apex-basic1"></div>
                                    {{-- <div id="layout1-chart-3" class="layout-chart-1"></div> --}}

                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card card-block card-stretch card-height-helf">
                                <div class="card-body">
                                    <div class="d-flex align-items-top justify-content-between">
                                        <div class="">
                                            <p class="mb-0">Purchase Trend</p>
                                            <h5></h5>
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
                                    <div id="apex-basic2"></div>
                                    {{-- <div id="layout1-chart-4" class="layout-chart-2"></div> --}}
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
                        <div class="col-lg-4">
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
                        <div class="col-lg-4">
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
                        <div class="col-lg-8">
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
                    data: @json($data['data_sales'])
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
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep']
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
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep']
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
