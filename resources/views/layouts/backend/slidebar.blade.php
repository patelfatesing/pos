<div class="iq-sidebar sidebar-default">
    @include('layouts.backend.header')
    <div class="data-scrollbar" data-scroll="1">
        <nav class="iq-sidebar-menu">


            <ul id="iq-sidebar-toggle" class="iq-menu">
                <li class="active">
                    <a href="{{ route('dashboard') }}" class="svg-icon">
                        <svg class="svg-icon" id="p-dash1" width="20" height="20"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z">
                            </path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                        <span class="ml-4">Dashboards</span>
                    </a>
                </li>

                <li class=" ">
                    <a href="#inventory" class="collapsed" data-toggle="collapse" aria-expanded="false">
                        <svg class="svg-icon" id="p-dash2" width="20" height="20"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <span class="ml-4">Inventory</span>
                        <svg class="svg-icon iq-arrow-right arrow-active" width="20" height="20"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="10 15 15 20 20 15"></polyline>
                            <path d="M4 4h7a4 4 0 0 1 4 4v12"></path>
                        </svg>
                    </a>

                    <ul id="inventory" class="iq-submenu collapse" data-parent="#iq-sidebar-toggle">
                        @if (canAccess(auth()->user()->role_id, 'inventory'))
                            <li>
                                <a href="{{ route('inventories.list') }}">
                                    <i class="las la-minus"></i><span>Inventory Details</span>
                                </a>
                            </li>
                        @endif

                        @if (canAccess(auth()->user()->role_id, 'stock-request'))
                            <li>
                                <a href="{{ route('stock.requestList') }}">
                                    <i class="las la-minus"></i><span>Stock Request Manage</span>
                                </a>
                            </li>
                        @endif

                        @if (canAccess(auth()->user()->role_id, 'stock-transfer'))
                            <li>
                                <a href="{{ route('stock-transfer.list') }}">
                                    <i class="las la-minus"></i><span>Stock Transfer</span>
                                </a>
                            </li>
                        @endif

                        @if (canAccess(auth()->user()->role_id, 'product-list'))
                            <li>
                                <a href="{{ route('products.list') }}">
                                    <i class="las la-minus"></i><span>Products Manage</span>
                                </a>
                            </li>
                        @endif

                        @if (canAccess(auth()->user()->role_id, 'categories'))
                            <li>
                                <a href="{{ route('categories.list') }}">
                                    <i class="las la-minus"></i><span>Category Manage</span>
                                </a>
                            </li>
                        @endif

                        @if (canAccess(auth()->user()->role_id, 'sub-categories'))
                            <li>
                                <a href="{{ route('subcategories.list') }}">
                                    <i class="las la-minus"></i><span>Sub Category</span>
                                </a>
                            </li>
                        @endif

                        @if (canAccess(auth()->user()->role_id, 'pack-size'))
                            <li>
                                <a href="{{ route('packsize.list') }}">
                                    <i class="las la-minus"></i><span>Pack Size</span>
                                </a>
                            </li>
                        @endif

                    </ul>
                </li>

                <li class=" ">
                    <a href="#store" class="collapsed" data-toggle="collapse" aria-expanded="false">
                        <svg class="svg-icon" id="p-dash7" width="20" height="20"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        <span class="ml-4">Store Manage</span>
                        <svg class="svg-icon iq-arrow-right arrow-active" width="20" height="20"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="10 15 15 20 20 15"></polyline>
                            <path d="M4 4h7a4 4 0 0 1 4 4v12"></path>
                        </svg>
                    </a>
                    <ul id="store" class="iq-submenu collapse" data-parent="#iq-sidebar-toggle">
                        @if (canAccess(auth()->user()->role_id, 'store-manage'))
                            <li class="">
                                <a href="{{ route('branch.list') }}">
                                    <i class="las la-minus"></i><span>Store Manage</span>
                                </a>
                            </li>
                        @endif
                        @if (canAccess(auth()->user()->role_id, 'shift-manage'))
                            <li class="">
                                <a href="{{ route('shift-manage.list') }}">
                                    <i class="las la-minus"></i><span>Shift Manage</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>

                <li class=" ">
                    <a href="#people" class="collapsed" data-toggle="collapse" aria-expanded="false">
                        <svg class="svg-icon" id="p-dash8" width="20" height="20"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span class="ml-4">Users(Staff)</span>
                        <svg class="svg-icon iq-arrow-right arrow-active" width="20" height="20"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="10 15 15 20 20 15"></polyline>
                            <path d="M4 4h7a4 4 0 0 1 4 4v12"></path>
                        </svg>
                    </a>
                    <ul id="people" class="iq-submenu collapse" data-parent="#iq-sidebar-toggle">
                        <li class="">
                            @if (canAccess(auth()->user()->role_id, 'users'))
                                <a href="{{ route('users.list') }}">
                                    <i class="las la-minus"></i><span>Users</span>
                                </a>
                            @endif
                        </li>
                    </ul>
                </li>

                <li class=" ">
                    <a href="#purchase" class="collapsed" data-toggle="collapse" aria-expanded="false">
                        <svg class="svg-icon" id="p-dash5" width="20" height="20"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                            <line x1="1" y1="10" x2="23" y2="10"></line>
                        </svg>
                        <span class="ml-4">Purchases</span>
                        <svg class="svg-icon iq-arrow-right arrow-active" width="20" height="20"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="10 15 15 20 20 15"></polyline>
                            <path d="M4 4h7a4 4 0 0 1 4 4v12"></path>
                        </svg>
                    </a>
                    <ul id="purchase" class="iq-submenu collapse" data-parent="#iq-sidebar-toggle">
                        @if (canAccess(auth()->user()->role_id, 'purchase-invoice-list'))
                            <li class="">
                                <a href="{{ route('purchase.list') }}">
                                    <i class="las la-minus"></i><span>List Purchases</span>
                                </a>
                            </li>
                        @endif
                        @if (canCreate(auth()->user()->role_id, 'purchase-invoice-create'))
                            <li class="">
                                <a href="{{ route('purchase.create') }}">
                                    <i class="las la-minus"></i><span>Add purchase</span>
                                </a>
                            </li>
                        @endif
                        @if (canAccess(auth()->user()->role_id, 'vendor'))
                            <li class="">
                                <a href="{{ route('vendor.list') }}">
                                    <i class="las la-minus"></i><span>Vendors</span>
                                </a>
                            </li>
                        @endif
                        @if (canAccess(auth()->user()->role_id, 'demand-order'))
                            <li class="">
                                <a href="{{ route('demand-order.list') }}">
                                    <i class="las la-minus"></i><span>Demand Order</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
                @if (canAccess(auth()->user()->role_id, 'accounting-groups'))
                    <li class="">
                        <a href="{{ route('accounting.groups.list') }}" class="svg-icon">
                            <svg class="svg-icon" id="p-dash9" width="20" height="20"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2">
                                </rect>
                                <rect x="7" y="7" width="3" height="9"></rect>
                                <rect x="14" y="7" width="3" height="5"></rect>
                            </svg>
                            <span class="ml-4">Groups</span>
                        </a>
                    </li>
                @endif
                @if (canAccess(auth()->user()->role_id, 'accounting-ledgers'))
                    <li class="">
                        <a href="{{ route('accounting.ledgers.list') }}" class="svg-icon">
                            <svg class="svg-icon" id="p-dash9" width="20" height="20"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2">
                                </rect>
                                <rect x="7" y="7" width="3" height="9"></rect>
                                <rect x="14" y="7" width="3" height="5"></rect>
                            </svg>
                            <span class="ml-4">Ledgers</span>
                        </a>
                    </li>
                @endif
                @if (canAccess(auth()->user()->role_id, 'accounting-voucher'))
                    <li class="">
                        <a href="{{ route('accounting.vouchers.create') }}" class="svg-icon">
                            <svg class="svg-icon" id="p-dash9" width="20" height="20"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2">
                                </rect>
                                <rect x="7" y="7" width="3" height="9"></rect>
                                <rect x="14" y="7" width="3" height="5"></rect>
                            </svg>
                            <span class="ml-4">Vouchers</span>
                        </a>
                    </li>
                @endif


                <li class=" ">
                    <a href="#customer" class="collapsed" data-toggle="collapse" aria-expanded="false">
                        <svg class="svg-icon" id="p-dash10" width="20" height="20"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <polyline points="17 11 19 13 23 9"></polyline>
                        </svg>
                        <span class="ml-4">Customers</span>

                    </a>
                    <ul id="customer" class="iq-submenu collapse" data-parent="#iq-sidebar-toggle">
                        @if (canAccess(auth()->user()->role_id, 'commission-customer'))
                            <li class="">
                                <a href="{{ route('commission-users.list') }}">
                                    <i class="las la-minus"></i><span>Commission Customer</span>
                                </a>
                            </li>
                        @endif
                        @if (canAccess(auth()->user()->role_id, 'party-customer'))
                            <li class="">
                                <a href="{{ route('party-users.list') }}">
                                    <i class="las la-minus"></i><span>Party Customer</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
                @if (canAccess(auth()->user()->role_id, 'expense'))
                    <li class=" ">
                        <a href="#expenses" class="collapsed" data-toggle="collapse" aria-expanded="false">

                            <svg class="svg-icon" id="p-dash16" width="20" height="20"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                                <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                                <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                            </svg>
                            <span class="ml-4">Expenses Manage</span>

                        </a>
                        <ul id="expenses" class="iq-submenu collapse" data-parent="#iq-sidebar-toggle">
                            <li class="">
                                <a href="{{ route('exp.list') }}">
                                    <i class="las la-minus"></i><span>Expense</span>
                                </a>
                            </li>
                           
                        </ul>
                    </li>
                @endif
                @if (canAccess(auth()->user()->role_id, 'reports'))
                    <li class="">
                        <a href="{{ route('reports.list') }}" class="">
                            <svg class="svg-icon" id="p-dash7" width="20" height="20"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            <span class="ml-4">Reports</span>
                        </a>
                        <ul id="reports" class="iq-submenu collapse" data-parent="#iq-sidebar-toggle">
                        </ul>
                    </li>
                @endif
            </ul>
        </nav>
        {{-- <div id="sidebar-bottom" class="position-relative sidebar-bottom">
            <div class="card border-none">
                <div class="card-body p-0">
                    <div class="sidebarbottom-content">
                        <div class="image">
                            <img src="{{ asset('assets/images/layouts/side-bkg.png')}}" class="img-fluid" alt="side-bkg" />
                        </div>
                        <h6 class="mt-4 px-4 body-title">
                            Get More Feature by Upgrading
                        </h6>
                        <button type="button" class="btn sidebar-bottom-btn mt-4">
                            Go Premium
                        </button>
                    </div>
                </div>
            </div>
        </div> --}}
        <div class="p-3"></div>
    </div>
</div>
