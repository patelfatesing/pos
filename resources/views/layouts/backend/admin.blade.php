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
                        <li class="">
                            <a href="{{ route('inventories.list') }}">
                                <i class="las la-minus"></i><span>Inventory Details</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('stock.requestList') }}">
                                <i class="las la-minus"></i><span>Stock Request Manage</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('stock-transfer.list') }}">
                                <i class="las la-minus"></i><span>Stock Transfer</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('products.list') }}">
                                <i class="las la-minus"></i><span>Products Manage</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('categories.list') }}">
                                <i class="las la-minus"></i><span>Category Manage</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('subcategories.list') }}">
                                <i class="las la-minus"></i><span>Sub Category</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('packsize.list') }}">
                                <i class="las la-minus"></i><span>Pack Size</span>
                            </a>
                        </li>

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
                        <li class="">
                            <a href="{{ route('branch.list') }}">
                                <i class="las la-minus"></i><span>Store Manage</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('shift-manage.list') }}">
                                <i class="las la-minus"></i><span>Shift Manage</span>
                            </a>
                        </li>
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
                        {{-- <li class="">
                            <a href="../backend/page-list-customers.html">
                                <i class="las la-minus"></i><span>Customers</span>
                            </a>
                        </li> --}}
                        <li class="">
                            <a href="{{ route('users.list') }}">
                                <i class="las la-minus"></i><span>Users</span>
                            </a>
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
                        <li class="">
                            <a href="{{ route('purchase.list') }}">
                                <i class="las la-minus"></i><span>List Purchases</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('purchase.create') }}">
                                <i class="las la-minus"></i><span>Add purchase</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('vendor.list') }}">
                                <i class="las la-minus"></i><span>Vendors</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('demand-order.list') }}">
                                <i class="las la-minus"></i><span>Demand Order</span>
                            </a>
                        </li>
                        {{-- <li class="">
                            <a href="{{ route('purchase_ledger.list') }}">
                                <i class="las la-minus"></i><span>Purchase Ledger</span>
                            </a>
                        </li> --}}
                    </ul>
                </li>

                <li class="">
                    <a href="{{ route('accounting.groups.list') }}" class="svg-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="20" height="20" fill="currentColor">
                            <path d="M320 64C355.3 64 384 92.7 384 128C384 163.3 355.3 192 320 192C284.7 192 256 163.3 256 128C256 92.7 284.7 64 320 64zM416 376C416 401 403.3 423 384 435.9L384 528C384 554.5 362.5 576 336 576L304 576C277.5 576 256 554.5 256 528L256 435.9C236.7 423 224 401 224 376L224 336C224 283 267 240 320 240C373 240 416 283 416 336L416 376zM160 96C190.9 96 216 121.1 216 152C216 182.9 190.9 208 160 208C129.1 208 104 182.9 104 152C104 121.1 129.1 96 160 96zM176 336L176 368C176 400.5 188.1 430.1 208 452.7L208 528C208 529.2 208 530.5 208.1 531.7C199.6 539.3 188.4 544 176 544L144 544C117.5 544 96 522.5 96 496L96 439.4C76.9 428.4 64 407.7 64 384L64 352C64 299 107 256 160 256C172.7 256 184.8 258.5 195.9 262.9C183.3 284.3 176 309.3 176 336zM432 528L432 452.7C451.9 430.2 464 400.5 464 368L464 336C464 309.3 456.7 284.4 444.1 262.9C455.2 258.4 467.3 256 480 256C533 256 576 299 576 352L576 384C576 407.7 563.1 428.4 544 439.4L544 496C544 522.5 522.5 544 496 544L464 544C451.7 544 440.4 539.4 431.9 531.7C431.9 530.5 432 529.2 432 528zM480 96C510.9 96 536 121.1 536 152C536 182.9 510.9 208 480 208C449.1 208 424 182.9 424 152C424 121.1 449.1 96 480 96z"/></svg>
                        <span class="ml-4">Groups</span>
                    </a>
                </li>

                <li class="">
                    <a href="{{ route('accounting.ledgers.list') }}" class="svg-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="20" height="20" fill="currentColor">
                            <path d="M128 128C92.7 128 64 156.7 64 192L64 448C64 483.3 92.7 512 128 512L512 512C547.3 512 576 483.3 576 448L576 192C576 156.7 547.3 128 512 128L128 128zM360 352L488 352C501.3 352 512 362.7 512 376C512 389.3 501.3 400 488 400L360 400C346.7 400 336 389.3 336 376C336 362.7 346.7 352 360 352zM336 264C336 250.7 346.7 240 360 240L488 240C501.3 240 512 250.7 512 264C512 277.3 501.3 288 488 288L360 288C346.7 288 336 277.3 336 264zM212 208C223 208 232 217 232 228L232 232L240 232C251 232 260 241 260 252C260 263 251 272 240 272L192.5 272C185.6 272 180 277.6 180 284.5C180 290.6 184.4 295.8 190.4 296.8L232.1 303.8C257.4 308 276 329.9 276 355.6C276 381.7 257 403.3 232 407.4L232 412.1C232 423.1 223 432.1 212 432.1C201 432.1 192 423.1 192 412.1L192 408.1L168 408.1C157 408.1 148 399.1 148 388.1C148 377.1 157 368.1 168 368.1L223.5 368.1C230.4 368.1 236 362.5 236 355.6C236 349.5 231.6 344.3 225.6 343.3L183.9 336.3C158.5 332 140 310.1 140 284.5C140 255.7 163.2 232.3 192 232L192 228C192 217 201 208 212 208z"/></svg>
                        <span class="ml-4">Ledgers</span>
                    </a>
                </li>
                <li class="">
                    <a href="{{ route('accounting.vouchers.create') }}" class="svg-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="20" height="20" fill="currentColor">
                            <path d="M96 128C60.7 128 32 156.7 32 192L32 256C32 264.8 39.4 271.7 47.7 274.6C66.5 281.1 80 299 80 320C80 341 66.5 358.9 47.7 365.4C39.4 368.3 32 375.2 32 384L32 448C32 483.3 60.7 512 96 512L544 512C579.3 512 608 483.3 608 448L608 384C608 375.2 600.6 368.3 592.3 365.4C573.5 358.9 560 341 560 320C560 299 573.5 281.1 592.3 274.6C600.6 271.7 608 264.8 608 256L608 192C608 156.7 579.3 128 544 128L96 128zM448 400L448 240L192 240L192 400L448 400zM144 224C144 206.3 158.3 192 176 192L464 192C481.7 192 496 206.3 496 224L496 416C496 433.7 481.7 448 464 448L176 448C158.3 448 144 433.7 144 416L144 224z"/></svg>
                        <span class="ml-4">Vouchers</span>
                    </a>
                </li>

                {{-- <li class=" ">
                    <a href="#sale" class="collapsed" data-toggle="collapse" aria-expanded="false">
                        <svg class="svg-icon" id="p-dash4" width="20" height="20"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                            <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                        </svg>
                        <span class="ml-4">Sale</span>
                        <svg class="svg-icon iq-arrow-right arrow-active" width="20" height="20"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="10 15 15 20 20 15"></polyline>
                            <path d="M4 4h7a4 4 0 0 1 4 4v12"></path>
                        </svg>
                    </a>
                    <ul id="sale" class="iq-submenu collapse" data-parent="#iq-sidebar-toggle">
                        <li class="">
                            <a href="{{ route('sales.sales.list') }}">
                                <i class="las la-minus"></i><span>List Sale</span>
                            </a>
                        </li>
                    </ul>
                </li> --}}

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
                        <li class="">
                            <a href="{{ route('commission-users.list') }}">
                                <i class="las la-minus"></i><span>Commission Customer</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('party-users.list') }}">
                                <i class="las la-minus"></i><span>Party Customer</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class=" ">
                    <a href="#expenses" class="collapsed" data-toggle="collapse" aria-expanded="false">

                        <svg class="svg-icon" id="p-dash16" width="20" height="20"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                        <li class="">
                            <a href="{{ route('exp_category.list') }}">
                                <i class="las la-minus"></i><span>Expense Category</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="">
                    <a href="{{ route('reports.list') }}" class="">
                        <svg class="svg-icon" id="p-dash7" width="20" height="20"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                {{-- <li class=" ">
                    <a href="{{ route('reports.list') }}" class="collapsed" data-toggle="collapse"
                        aria-expanded="false">
                        <svg class="svg-icon" id="p-dash7" width="20" height="20"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        <span class="ml-4">Reports</span>

                    </a>
                    <ul id="reports" class="iq-submenu collapse" data-parent="#iq-sidebar-toggle">
                        <li class="">
                            <a href="{{ route('sales.report') }}">
                                <i class="las la-minus"></i><span>Sales</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('sales.sales-daily') }}">
                                <i class="las la-minus"></i><span>Daily Sales</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('sales.stock.report') }}">
                                <i class="las la-minus"></i><span>Stock Summary</span>
                            </a>
                        </li>
                        <li class="">
                            <a href="{{ route('sales.commission.report') }}">
                                <i class="las la-minus"></i><span>Commission Report</span>
                            </a>
                        </li>
                    </ul>
                </li> --}}

                <li class=" ">
                    <a href="#return" class="collapsed" data-toggle="collapse" aria-expanded="false">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <span class="ml-4">Access Control</span>
                        <svg class="svg-icon iq-arrow-right arrow-active" width="20" height="20"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="10 15 15 20 20 15"></polyline>
                            <path d="M4 4h7a4 4 0 0 1 4 4v12"></path>
                        </svg>
                    </a>
                    <ul id="return" class="iq-submenu collapse" data-parent="#iq-sidebar-toggle">
                        <li class="">
                            <a href="{{ route('roles.list') }}">
                                <i class="las la-minus"></i><span>Roles Manage</span>
                            </a>
                        </li>
                    </ul>
                </li>

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
