<div class="container-fluid">
    <!-- Top Bar -->
    @php
        $this->cashAmount = round_up_to_nearest_10($this->cashAmount) ?? 0;
    @endphp

    <div class="topbar d-flex flex-wrap justify-content-between align-items-center  bg-white shadow-sm">

        <!-- Logo -->
        <div class="d-flex align-items-center">
            <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="img-fluid" style="max-height: 40px;" />
        </div>

        <!-- Right Side -->
        <div class="d-flex align-items-center ms-auto gap-2 flex-wrap flex-md-nowrap">

            <!-- Store Location -->
            <div class="main-screen-frame280">
                <span class="main-screen-text72">Store Location: {{ $this->branch_name }}</span>
            </div>

            <!-- Refresh -->
            <div onclick="location.reload()" class="cursor-pointer main-screen-group1">
                <img src="{{ asset('public/external/vector4471-dtw.svg') }}" class="img-fluid main-screen-vector10"
                    style="height: 20px;" />
                <img src="{{ asset('public/external/vector4471-wfwo.svg') }}" class="img-fluid main-screen-vector11"
                    style="height: 20px;" />
            </div>

            <!-- Livewire Components -->
            <livewire:fullscreen-toggle />
            <livewire:notification />
            <livewire:language-switcher />

            <!-- Logout -->
            <button type="button" class="btn btn-light border ms-1" data-bs-toggle="tooltip" title="Logout"
                onclick="confirmLogout()">
                <img src="{{ asset('public/external/fi106093284471-0vjk.svg') }}" class="img-fluid"
                    style="height: 25px;" />
            </button>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>

    <!-- Mobile Toggle Button -->
    <div class="d-md-none  ms-2">
        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
            ☰ Menu
        </button>
    </div>

    {{-- <div class="topbar d-flex justify-content-between align-items-center flex-wrap main-screen-rectangle99">

        <img src="{{ asset('assets/images/logo.png') }}" alt="Logo"
            class="main-screen-image73a1ed2f33b74c9599cb101a7e1b7e5f1" />
        <div class="d-flex align-items-center ms-auto gap-3">
            <div class="main-screen-frame280">
                <span class="main-screen-text72">Store Location: {{ $this->branch_name }}</span>
            </div>
            <div class="main-screen-frame246 d-flex align-items-center">
                <div class="main-screen-refresh1">
                    <div class="main-screen-group1" onclick="location.reload()">
                        <img src="{{ asset('public/external/vector4471-dtw.svg') }}" alt="Vector4471"
                            class="main-screen-vector10" />
                        <img src="{{ asset('public/external/vector4471-wfwo.svg') }}" alt="Vector4471"
                            class="main-screen-vector11" />
                    </div>
                </div>

                <livewire:fullscreen-toggle />
                <livewire:notification />
                <livewire:language-switcher />

                <button type="button" class="btn btn-deafult" data-toggle="tooltip" data-placement="top" title="Logout"
                    onclick="confirmLogout()">
                    <img src="{{ asset('public/external/fi106093284471-0vjk.svg') }}" alt="User Icon"
                        class="main-screen-fi10609328" />
                </button>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
    </div> --}}
    {{-- <div class="main-screen-frame245">
                    <div class="main-screen-group188">
                        <span class="main-screen-text10">English</span>
                        <div class="main-screen-layer12">
                            <div class="main-screen-group2">
                                <img src="{{ asset('public/external/vector4471-dli.svg') }}" alt="Vector4471"
                                    class="main-screen-vector12" />
                            </div>
                        </div>
                    </div>
                </div> --}}

    <div class="row flex-md-nowrap">
        <!-- Desktop Sidebar -->
        <div id="sidebar" class="sidebar d-none d-md-flex flex-column py-2"
            style="max-height: 100vh; overflow-y: auto;">
            {{-- Stock Request (Cashier or Warehouse) --}}
            @auth
                @if (auth()->user()->hasRole('cashier') || auth()->user()->hasRole('warehouse'))
                    <div class="sidebar-item" data-bs-toggle="modal" data-bs-target="#storeStockRequest"
                        title="Stock Request">
                        <button type="button" class="btn btn-default p-1 m-0 border-0 bg-transparent">
                            <img src="{{ asset('public/external/frame2834471-mtm.svg') }}" alt="Stock Request Icon"
                                width="20" height="20">
                        </button>
                        <span>Stock Request</span>
                    </div>
                    <img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}"
                        class="main-screen-rectangle457" />
                @endif
            @endauth

            {{-- Add Cash --}}
            <div class="sidebar-item">
                <livewire:take-cash-modal wire:key="take-cash-modal-static" />
            </div>
            <img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}" class="main-screen-rectangle457" />

            {{-- Cash Out --}}
            <div class="sidebar-item" data-bs-toggle="modal" data-bs-target="#cashout" title="Cash Out">
                <button type="button" class="btn btn-default p-1 m-0 border-0 bg-transparent">
                    <img src="{{ asset('public/external/caseout.png') }}" alt="Cash Out" width="32" height="32">
                </button>
                <span>Cash Out</span>
            </div>
            <img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}"
                class="main-screen-rectangle457" />

            {{-- View Hold --}}
            @if (count($itemCarts) == 0)
                <div class="sidebar-item" data-bs-toggle="modal" data-bs-target="#holdTransactionsModal"
                    title="View Hold">
                    <button type="button" class="btn btn-default p-1 m-0 border-0 bg-transparent">
                        <img src="{{ asset('public/external/vector4471-4bnt.svg') }}" alt="View Hold" width="24"
                            height="24">
                    </button>
                    <span>View Hold</span>
                </div>
                <img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}"
                    class="main-screen-rectangle457" />
            @endif

            {{-- Sales History --}}
            <div class="sidebar-item">
                <livewire:order-modal wire:key="order-modal-static" />
            </div>
            <img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}"
                class="main-screen-rectangle457" />

            {{-- Warehouse Tools --}}
            @auth
                @if (auth()->user()->hasRole('warehouse'))
                    <div class="sidebar-item" wire:click="printLastInvoice" title="Print Invoice">
                        <button type="button" class="btn btn-default p-1 m-0 border-0 bg-transparent">
                            <img src="{{ asset('public/external/pdf_icon_final.jpg') }}" alt="Print Invoice Icon"
                                width="24" height="24">
                        </button>
                        <span>Print Invoice</span>
                    </div>
                    <img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}"
                        class="main-screen-rectangle457" />

                    <div class="sidebar-item">
                        <livewire:customer-credit-ledger-modal wire:key="credit-ledger-modal-static" />
                    </div>
                    <img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}"
                        class="main-screen-rectangle457" />

                    <div class="sidebar-item">
                        <livewire:collation-modal wire:key="collation-modal-static" />
                    </div>
                    <img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}"
                        class="main-screen-rectangle457" />
                @endif
            @endauth

            {{-- Close Shift --}}
            <div class="sidebar-item">
                <livewire:shift-close-modal wire:key="shift-close-modal-static" />
            </div>
        </div>

        <!-- Mobile Offcanvas Sidebar -->
        <div class="offcanvas offcanvas-start d-md-none" id="mobileSidebar">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title">Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                @auth
                    @if (auth()->user()->hasRole('cashier') || auth()->user()->hasRole('warehouse'))
                        <div class="sidebar-item">
                            <button class="btn btn-default" data-bs-toggle="modal" data-bs-target="#storeStockRequest"
                                title="Stock Request">
                                <img src="{{ asset('public/external/frame2834471-mtm.svg') }}"
                                    alt="Stock Request Icon" />
                            </button>
                            <span>Stock Request</span>
                        </div>
                        <img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}"
                            class="main-screen-rectangle457" />
                    @endif
                @endauth

                <div class="sidebar-item">
                    <livewire:take-cash-modal wire:key="mobile-take-cash-modal-static" />
                    <span>Add Cash</span>
                </div>
                <img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}"
                    class="main-screen-rectangle457" />

                <div class="sidebar-item">
                    <button class="btn btn-default" data-bs-toggle="modal" data-bs-target="#cashout"
                        title="Cash Out">
                        <img src="{{ asset('public/external/caseout.png') }}" alt="Cash Out" width="32"
                            height="32" />
                    </button>
                    <span>Cash Out</span>
                </div>
                <img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}"
                    class="main-screen-rectangle457" />

                @if (count($itemCarts) == 0)
                    <div class="sidebar-item">
                        <button class="btn btn-default" data-bs-toggle="modal"
                            data-bs-target="#holdTransactionsModal" title="View Hold">
                            <img src="{{ asset('public/external/vector4471-4bnt.svg') }}" alt="View Hold Icon" />
                        </button>
                        <span>View Hold</span>
                    </div>
                    <img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}"
                        class="main-screen-rectangle457" />
                @endif

                <div class="sidebar-item">
                    <livewire:order-modal wire:key="mobile-order-modal-static" />
                    <span>Sales History</span>
                </div>
                <img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}"
                    class="main-screen-rectangle457" />

                @auth
                    @if (auth()->user()->hasRole('warehouse'))
                        <div class="sidebar-item">
                            <button wire:click="printLastInvoice" class="btn btn-default" title="Print Invoice">
                                <img src="{{ asset('public/external/pdf_icon_final.jpg') }}" alt="Print Invoice Icon" />
                            </button>
                            <span>Print Invoice</span>
                        </div>
                        <img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}"
                            class="main-screen-rectangle457" />

                        <div class="sidebar-item">
                            <livewire:customer-credit-ledger-modal wire:key="mobile-credit-ledger-modal-static" />
                            <span>Customer Credit Ledger</span>
                        </div>
                        <img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}"
                            class="main-screen-rectangle457" />

                        <div class="sidebar-item">
                            <livewire:collation-modal wire:key="mobile-collation-modal-static" />
                            <span>Collect Credit</span>
                        </div>
                        <img src="{{ asset('public/external/rectangle4574471-dhdb-200h.png') }}"
                            class="main-screen-rectangle457" />
                    @endif
                @endauth

                <div class="sidebar-item">
                    <livewire:shift-close-modal wire:key="mobile-shift-close-modal-static" />
                    <span>Close Shift</span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-12 col-md-11 m-2 ml-2">
            <div>

                <div class="d-flex justify-content-between align-items-center flex-wrap mt-2 py-2 ">
                    <div class="text-muted main-screen-text12">
                        Welcome! <strong class="text-teal">{{ Auth::user()->name }}</strong>
                    </div>
                    <div class="text-muted main-screen-text12">
                        Shift Code: <strong>{{ $this->shift->shift_no ?? '' }}</strong> | Date:
                        <span class="text-teal">
                            {{ $this->shift && $this->shift->start_time ? \Carbon\Carbon::parse($this->shift->start_time)->format('d:m:Y, g:i:s A') : '' }}
                        </span>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row  mt-2">
                    <div class="col-12 col-md-3">
                        <div class="position-relative">
                            <input type="number" class="form-control rounded-pill pe-5 custom-border"
                                placeholder="{{ __('messages.scan_barcode') }}" wire:model.live="search"
                                wire:keydown.enter="addToCartBarCode" autofocus>

                            <!-- Barcode icon -->
                            <img src="{{ asset('public/external/barcoderead14471-053n.svg') }}" alt="Barcode"
                                style="position: absolute; right: 40px; top: 50%; transform: translateY(-50%); width: 20px; height: 20px;">

                            <!-- Clear button -->
                            @if ($search)
                                <button type="button" wire:click="clearSearch"
                                    style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
                                    border: none; background: transparent; font-size: 18px; cursor: pointer; color: #888;"
                                    aria-label="Clear input">&times;</button>
                            @endif
                        </div>

                        @if (strlen($notFoundMessage) > 0)
                            <div class="mt-2 text-danger" style="font-size: 0.9rem;">
                                {{ $notFoundMessage }}
                            </div>
                        @endif
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="position-relative ">
                            <img src="{{ asset('public/external/vector4471-t8to.svg') }}" alt="Icon"
                                style="position: absolute; right: 15px; top: 12px; width: 18px; height: 18px; pointer-events: none; z-index: 2;">

                            @if (auth()->user()->hasRole('cashier'))
                                <select id="commissionUser" class="form-control rounded-pill pe-5 custom-border"
                                    wire:model="selectedCommissionUser" wire:change="calculateCommission"
                                    @if ($removeCrossHold || $this->selectedSalesReturn == true) disabled @endif>
                                    <option value="">-- Select Commission Customer --</option>
                                    @foreach ($commissionUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->first_name }}</option>
                                    @endforeach
                                </select>
                            @endif

                            @if (auth()->user()->hasRole('warehouse'))
                                <select id="partyUser" class="form-control rounded-pill pe-5 custom-border"
                                    wire:model="selectedPartyUser" wire:change="calculateParty"
                                    @if ($removeCrossHold || $this->selectedSalesReturn == true) disabled @endif>
                                    <option value="">-- {{ __('messages.select_party_customer') }} --</option>
                                    @foreach ($partyUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->first_name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="position-relative ">
                            <livewire:take-two-pictures />
                        </div>
                    </div>
                    @if (auth()->user()->hasRole('warehouse'))
                        <div class="col-12 col-md-3">
                            <div class="position-relative">
                                <input type="text" wire:model.live="searchSalesReturn"
                                    wire:keydown.enter="addToSalesreturn"
                                    class="form-control rounded-pill ps-4 pe-5 custom-border"
                                    placeholder="{{ __('messages.scan_invoice_no') }}" autofocus>

                                <img src="{{ asset('public/external/qrscan14471-8i6r.svg') }}" alt="QR Scan"
                                    class="position-absolute top-50 end-0 translate-middle-y me-3"
                                    style="width: 20px; height: 20px;">
                            </div>
                        </div>
                    @endif
                </div>
                <!-- Search + Buttons -->
                <div class="row  mt-2">
                    <div class="col-md-9 position-relative">
                        <form wire:submit.prevent="searchTerm" class="mb-0">
                            <div class="input-group rounded-pill overflow-hidden custom-border">
                                <input type="text" wire:model.live.debounce.500ms="searchTerm"
                                    placeholder="{{ __('messages.enter_product_name') }}"
                                    class="form-control border-0 ps-4" id="searchInput" autocomplete="off" />
                                <span class="input-group-text bg-transparent border-0 pe-3">
                                    <img src="{{ asset('public/external/vector4471-m3pl.svg') }}" alt="Search"
                                        style="width: 20px; height: 20px;">
                                </span>
                            </div>
                        </form>

                        @if ($this->showSuggestions && count($searchResults) > 0)
                            <div class="dropdown-menu show w-100 mt-1 shadow-sm overflow-auto"
                                style="max-height: 260px; border-radius: 0.5rem;">
                                <ul class="list-group list-group-flush">
                                    @foreach ($searchResults as $product)
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2"
                                            wire:click.prevent="addToCart({{ $product->id }})"
                                            style="cursor: pointer;">
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold">{{ $product->name }}</span>
                                                @if ($product->description)
                                                    <small class="text-muted">{{ $product->description }}</small>
                                                @endif
                                            </div>
                                            <span
                                                class="text-primary fw-bold ms-3">{{ format_inr(@$product->sell_price) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>


                    <div class="col-md-3 mt-3 mt-md-0">
                        <div class="d-flex justify-content-between align-items-center gap-2">

                            <!-- Button 1 -->
                            <button type="button" class="btn btn-deafult main-screen-container2"
                                wire:click="incrementQty({{ $this->activeItemId }})">
                                <img src="{{ asset('../public/external/systemicon16pxplus4471-kuog.svg') }}"
                                    alt="Left Icon">
                            </button>

                            <!-- Button 2 -->
                            {{-- <button type="button" class="btn btn-deafult main-screen-container2">
                                <img src="{{ asset('../public/external/systemicon16pxplus4471-aqk.svg') }}"
                                    alt="Center Icon">
                            </button> --}}

                            <!-- Button 3 -->
                            <button type="button" class="btn btn-deafult main-screen-container2"
                                wire:click="decrementQty({{ $this->activeItemId, $this->activeProductId }})">
                                <img src="{{ asset('../public/external/systemicon16pxplus4471-jpl.svg') }}"
                                    alt="Right Icon">
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Product Table & Calculator -->
                <div class="row mt-1">
                    <div class="col-md-9">
                        <div class="table-responsive">

                            <table class="table table-bordered product-table" id="cartTable">
                                <thead class="table-info">
                                    <tr>
                                        <th class="col-7 main-screen-text25">{{ __('messages.product') }}</th>
                                        <th class="main-screen-text25">{{ __('messages.qty') }}</th>
                                        <th class="main-screen-text25">{{ __('messages.price') }}</th>
                                        <th class="main-screen-text25">{{ __('messages.total') }}</th>
                                        <th class="main-screen-text25">{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($itemCarts as $item)
                                        @php
                                            $total = @$item->product->sell_price * $item->quantity;
                                            $commission = $commissionAmount ?? 0;
                                            $party = $partyAmount ?? 0;
                                            $finalAmount = $total - $commission - $party;
                                        @endphp
                                        <tr class="{{ $this->activeItemId === $item->id ? 'active' : '' }}">
                                            <td class="col-7"
                                                wire:click="setActiveItem({{ $item->id }}, {{ $item->product->id }})"
                                                style="cursor:pointer">
                                                {{ $item->product->name }}<br>
                                                {{ $item->product->description }}
                                            </td>
                                            <td
                                                wire:click="setActiveItem({{ $item->id }}, {{ $item->product->id }})">

                                                {{ $this->quantities[$item->id] }}
                                            </td>
                                            <td
                                                wire:click="setActiveItem({{ $item->id }}, {{ $item->product->id }})">

                                                @if (@$this->partyUserDiscountAmt && $this->commissionAmount > 0)
                                                    <span class="text-danger">
                                                        @php
                                                            $data = getDiscountPrice(
                                                                $item->product->id,
                                                                $this->selectedPartyUser,
                                                                $this->selectedCommissionUser,
                                                            );
                                                        @endphp
                                                        {{ format_inr(@$data['partyUserDiscountAmt']) }}
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">
                                                        <s>{{ format_inr(@$item->product->sell_price) }}</s>
                                                    </small>
                                                @else
                                                    <span class="text-danger">
                                                        @if ($this->selectedPartyUser)
                                                            {{ format_inr(@$item->net_amount / $this->quantities[$item->id]) }}
                                                        @else
                                                            {{ format_inr(@$item->net_amount / $this->quantities[$item->id]) }}
                                                        @endif
                                                    </span>
                                                    @if ($this->selectedPartyUser && $item->net_amount / $this->quantities[$item->id] != $item->product->sell_price)
                                                        <br>
                                                        <small class="text-muted">
                                                            <s>{{ format_inr(@$item->product->sell_price) }}</s>
                                                        </small>
                                                    @endif
                                                    @if ($this->selectedCommissionUser)
                                                        <br>
                                                        <small class="text-muted">
                                                            <s>{{ format_inr(@$item->product->sell_price) }}</s>
                                                        </small>
                                                    @endif
                                                @endif
                                            </td>

                                            <td wire:click="setActiveItem({{ $item->id }}, {{ $item->product->id }})"
                                                class="text-success fw-bold">

                                                {{ format_inr($item->net_amount) }}

                                            </td>
                                            @if ($this->removeCrossHold == true)
                                                <td>
                                                    <button class="btn btn-danger"
                                                        onclick="Swal.fire({
                                                                title: 'Do you want to remove this product ?',
                                                                text: 'This action cannot be reverted!',
                                                                icon: 'warning',
                                                                showCancelButton: true,
                                                                confirmButtonText: 'Yes, remove it',
                                                                cancelButtonText: 'Cancel',
                                                                reverseButtons: true
                                                            }).then((result) => {
                                                                if (result.isConfirmed) {
                                                                    @this.removeItem({{ $item->id }},'resume','{{ $this->invoice_no }}');
                                                                }
                                                            });"
                                                        title="Remove item">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            @else
                                                <td>
                                                    <button class="btn btn-sm btn-default"
                                                        wire:click="removeItem({{ $item->id }})"
                                                        title="Remove item">
                                                        <img src="{{ asset('public/external/delete24dp1f1f1ffill0wght400grad0opsz2414471-7kar.svg') }}"
                                                            alt="Delete"
                                                            class="main-screen-delete24dp1f1f1ffill0wght400grad0opsz24110">
                                                    </button>

                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No products found in the
                                                cart.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Calculator & Payment -->
                    <div class="col-12 col-md-3">
                        <!-- Calculator -->
                        <div class="px-3 pt-3 pb-2 blue-bg rounded shadow-sm">
                            <div class="d-grid gap-2">
                                <div class="d-flex gap-2">
                                    <button wire:click="addQuantity('1')"
                                        class="btn btn-default main-screen-frame-key11 flex-fill">1</button>
                                    <button wire:click="addQuantity('2')"
                                        class="btn btn-default main-screen-frame-key11 flex-fill">2</button>
                                    <button wire:click="addQuantity('3')"
                                        class="btn btn-default main-screen-frame-key11 flex-fill">3</button>
                                    <button wire:click="addQuantity('10')"
                                        class="btn btn-default main-screen-frame-key11 flex-fill">+10</button>
                                </div>
                                <div class="d-flex gap-2">
                                    <button wire:click="addQuantity('4')"
                                        class="btn btn-default main-screen-frame-key11 flex-fill">4</button>
                                    <button wire:click="addQuantity('5')"
                                        class="btn btn-default main-screen-frame-key11 flex-fill">5</button>
                                    <button wire:click="addQuantity('6')"
                                        class="btn btn-default main-screen-frame-key11 flex-fill">6</button>
                                    <button wire:click="addQuantity('20')"
                                        class="btn btn-default main-screen-frame-key11 flex-fill">+20</button>
                                </div>
                                <div class="d-flex gap-2">
                                    <button wire:click="addQuantity('7')"
                                        class="btn btn-default main-screen-frame-key11 flex-fill">7</button>
                                    <button wire:click="addQuantity('8')"
                                        class="btn btn-default main-screen-frame-key11 flex-fill">8</button>
                                    <button wire:click="addQuantity('9')"
                                        class="btn btn-default main-screen-frame-key11 flex-fill">9</button>
                                    <button wire:click="addQuantity('50')"
                                        class="btn btn-default main-screen-frame-key11 flex-fill">+50</button>
                                </div>
                                <div class="d-flex gap-2">
                                    <button wire:click="$set('search', '')"
                                        class="btn btn-default main-screen-frame-key11 flex-fill">C</button>
                                    <button wire:click="removeItemActivte({{ $this->activeProductId }})"
                                        class="btn btn-default main-screen-frame-key11 flex-fill">0</button>
                                    <button class="btn btn-default main-screen-frame-key11 flex-fill">
                                        <img src="{{ asset('public/external/vector4471-fdk.svg') }}" alt="Icon"
                                            style="height: 20px;">
                                    </button>
                                    <button class="btn btn-default main-screen-frame-key11 flex-fill">
                                        <img src="{{ asset('public/external/right4471-upx2.svg') }}" alt="Right"
                                            style="height: 20px;">
                                    </button>
                                </div>
                            </div>
                        </div>



                        <!-- Action Buttons (Bootstrap) -->
                        <div class="mt-1 row g-2">
                            @if (empty($this->selectedSalesReturn))
                                <div class="col-6">
                                    <button wire:click="holdSale" class="btn btn-deafult btn-hold w-100">
                                        </i> {{ __('messages.hold') }}
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button wire:click="voidSale" class="btn btn-danger btn-void w-72 h-24">
                                        <span> {{ __('messages.void_sales') }}</span>
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button wire:click="toggleBox" type="button"
                                        class="btn btn-primary btn-cash w-100">
                                        Cash
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button wire:click="onlinePayment" class="btn btn-success btn-online w-100">
                                        {{ __('messages.upi') }}
                                    </button>
                                </div>
                                <div class="col-12">
                                    <button type="button" class="btn btn-deafult btn-cash-upi w-100 px-3"
                                        wire:click="cashupitoggleBox">
                                        <span> Cash + UPI</span>
                                        <img src="{{ asset('public/external/right4471-5iuh.svg') }}" alt="Right"
                                            style="height: 18px;" class="float-end">
                                    </button>
                                </div>
                            @endif

                            @if (!empty($this->selectedSalesReturn))
                                <div class="col-6">
                                    <button wire:click="refundoggleBox"
                                        class="btn btn-sm btn-danger w-100 text-nowrap">
                                        <i class="fa fa-hand-holding-usd me-2"></i> Refund
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button wire:click="srtoggleBox" class="btn btn-sm btn-primary w-100 text-nowrap">
                                        <i class="fa fa-hand-holding-usd me-2"></i> Sales Return
                                    </button>
                                </div>
                            @endif
                        </div>

                    </div>


                </div>
                <!-- Bottom Bar -->

                <div class="container-fluid py-2">
                    <div class="row text-center align-items-center header-row" style="min-height:50px;">
                        <div class="col-4 fw-semibold">Qty</div>
                        <div class="col-4 fw-semibold">Round Off</div>
                        <div class="col-4 fw-semibold">Total Payable</div>
                    </div>
                    <div class="row text-center align-items-center" style="min-height:90px;">
                        <div class="col-4 border-end fs-4 fw-bold text-custom-blue">{{ $this->cartCount }}</div>
                        <div class="col-4 border-end fs-4 fw-bold text-custom-blue">@php
                            $this->roundedTotal =
                                (float) $this->cashAmount + (float) $this->creditPay - round($this->cartItemTotalSum);
                        @endphp
                            {{ $this->roundedTotal }}
                            <input type="hidden" id="roundedTotal" value="{{ $this->roundedTotal }}"
                                wire:model="roundedTotal">
                        </div>
                        <div class="col-4 fs-4 fw-bold text-custom-blue">{{ format_inr($this->cashAmount) }}</span>
                            <input type="hidden" id="totalPayable" value="{{ $this->cashAmount }}">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Script to show modal -->
        </div>
    </div>
    <!-- Loader Overlay -->

    <!-- Bootstrap Modal -->
    <div class="modal fade" id="holdTransactionsModal" tabindex="-1" aria-labelledby="holdModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="holdModalLabel">{{ __('messages.hold_transactions') }}
                    </h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @livewire('hold-transactions', ['holdTransactions' => $holdTransactions])
                </div>
            </div>
        </div>
    </div>

    <!-- Single Modal -->
    <div class="modal fade " id="captureModal" tabindex="-1" aria-labelledby="captureModalLabel"
        aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-mg">
            <div class="modal-content shadow-sm rounded-4 border-0">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-semibold" id="captureModalLabel">
                        <i class="bi bi-camera-video me-2"></i>{{ __('messages.image_capture') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 py-4">
                    <!-- Step 1: Product -->

                    <div id="step1"
                        class="{{ !empty($this->productImage) && !empty($this->userImage) ? 'd-none' : '' }}">
                        <h6 class="text-muted mb-3">Step 1: Capture Product Image</h6>
                        <div class="border rounded-3 overflow-hidden mb-3 text-center p-2 bg-light">
                            <img src="{{ asset('assets/images/bottle.png') }}" alt="Sample Product"
                                class="rounded-3 shadow-sm" width="200" height="150" id="productImagePreview">
                            <canvas id="canvas1" class="d-none"></canvas>
                        </div>
                        <div class="border rounded-3 overflow-hidden mb-3">
                            <video id="video1" class="w-100" autoplay></video>
                        </div>
                        <button type="button" class="btn btn-outline-primary w-100"
                            onclick="captureImage('product')">
                            <i class="bi bi-camera me-1"></i>Capture Product Image
                        </button>
                        <button type="button" class="btn btn-primary w-100 mt-2" onclick="goToStep(2)">
                            Next: Customer Image
                        </button>
                    </div>

                    <!-- Step 2: User -->
                    <div id="step2" class="d-none mt-4">
                        <h6 class="text-muted mb-3">Step 2: Capture Customer Image</h6>
                        <div class="border rounded-3 overflow-hidden mb-3 text-center p-2 bg-light">
                            <img src="{{ asset('assets/images/user/07.jpg') }}" alt="Sample Customer"
                                class="rounded-circle shadow-sm" width="150" height="150"
                                id="userImagePreview">
                            <canvas id="canvas2" class="d-none"></canvas>
                        </div>
                        <div class="border rounded-3 overflow-hidden mb-3">
                            <video id="video2" class="w-100" autoplay></video>
                        </div>
                        <div class="d-flex justify-content-between gap-2">
                            <button type="button" class="btn btn-outline-primary w-100"
                                onclick="captureImage('user')">
                                <i class="bi bi-camera me-1"></i>Capture Customer Image
                            </button>
                            <button type="button" class="btn btn-primary w-100" onclick="goToStep(3)">
                                Next: Review
                            </button>

                        </div>
                    </div>

                    <div id="step3"
                        class="{{ !empty($this->productImage) && !empty($this->userImage) ? '' : 'd-none mt-4' }}">
                        @php
                            $stepTitle =
                                !empty($this->productImage) && !empty($this->userImage)
                                    ? 'Uploaded Images'
                                    : 'Step 3: Review & Confirm';
                        @endphp
                        <h6 class="text-muted mb-3">{{ $stepTitle }}</h6>

                        <div class="row mb-3">

                            <div class="col-6 text-center mb-3">
                                <p class="text-sm font-medium text-gray-600 ">Product Image</p>
                                <img id="imgproduct"
                                    src="{{ $this->productImage ? asset('storage/' . $this->productImage) : asset('assets/images/bottle.png') }}"
                                    class="rounded shadow-sm border" width="160" height="150"
                                    alt="Captured Product">

                            </div>
                            <div class="col-6 text-center mb-3">
                                <p class="text-sm font-medium text-gray-600 ">User Image</p>
                                <img id="imguser"
                                    src="{{ $this->userImage ? asset('storage/' . $this->userImage) : asset('assets/images/user/07.jpg') }}"
                                    class="rounded shadow-sm border" width="150" height="150"
                                    alt="Captured Customer">

                            </div>

                        </div>
                        <div class="d-flex justify-content-between gap-2">
                            <button type="button" class="btn btn-outline-warning w-100" onclick="goToStep(1)">
                                <i class="bi bi-arrow-left-circle me-1"></i>Retake Product Image
                            </button>
                            <button type="button" class="btn btn-outline-warning w-100" onclick="goToStep(2)">
                                <i class="bi bi-arrow-left-circle me-1"></i>Retake User Image
                            </button>
                            <button type="button" class="btn btn-success w-100" data-dismiss="modal">
                                <i class="bi bi-check-circle me-1"></i>Confirm
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cashout" tabindex="-1" aria-labelledby="cashout" aria-hidden="true"
        data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content shadow-sm rounded-4 border-0">
                <div class="modal-header custom-modal-header">
                    <h5 class="modal-title cash-summary-text61" id="cashout">
                        <i class="bi bi-camera-video me-2"></i>{{ __('messages.withdraw_cash_details') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-6">
                    <div class="row">
                        <div class="col-md-12">
                            <form method="POST" action="{{ route('shift-close.withdraw') }}">
                                @csrf

                                <div class="card shadow-sm rounded-2xl p-2">

                                    <div class="table-responsive">
                                        <table class=" table table-bordered product-table">
                                            <thead class="table-info ">
                                                <tr>
                                                    <th class="main-screen-text25 text-center">
                                                        {{ __('messages.currency') }}</th>
                                                    <th class="main-screen-text25 text-center">
                                                        {{ __('messages.notes') }}</th>
                                                    <th class="main-screen-text25 text-center">
                                                        {{ __('messages.amount') }}</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($noteDenominations as $key => $denomination)
                                                    <tr class="text-center">
                                                        <td class="" style="width: 28%;">
                                                            {{ $denomination }} x</td>
                                                        <td class="" style="width: 40%;">
                                                            <div class="d-flex align-items-center">
                                                                <button style="width: 40%;" type="button"
                                                                    class="btn btn-gray btn-decrease rounded-start"
                                                                    onclick="updateNote('{{ $key }}_{{ $denomination }}', -1, {{ $denomination }})">−</button>
                                                                <span
                                                                    id="display_{{ $key }}_{{ $denomination }}"
                                                                    class="form-control text-center  bg-white px-1 "
                                                                    style="width: 60px;">0</span>

                                                                <button style="width: 40%;"
                                                                    class="btn btn-gray rounded-end btn-increase"
                                                                    type="button"
                                                                    onclick="updateNote('{{ $key }}_{{ $denomination }}', 1, {{ $denomination }})">+</button>
                                                                <input type="hidden"
                                                                    name="withcashNotes.{{ $key }}.{{ $denomination }}"
                                                                    id="withcashnotes_{{ $key }}_{{ $denomination }}"
                                                                    value="0">
                                                            </div>
                                                        </td>
                                                        <td class=""
                                                            id="withcashsum_{{ $key }}_{{ $denomination }}">
                                                            ₹0.00</td>
                                                    </tr>
                                                @endforeach

                                                <tr class="border table-success fw-bold">
                                                    <td colspan="2" class="">
                                                        <span style="color:#1C5609 " class="fw-bold  fs-6">Total
                                                            Amount</span>

                                                    </td>
                                                    <td class="text-center"> <span class="fw-bold  fs-6"
                                                            id="totalNoteCashwith" style="color:#1C5609 ">₹0.00</span>
                                                    </td>
                                                </tr>

                                            </tbody>
                                        </table>
                                    </div>

                                    <input type="hidden" name="amount" id="withamountTotal"
                                        class="form-control mb-3" readonly required>

                                    <div class="mb-1">
                                        <label for="narration"
                                            class="form-label">{{ __('messages.select_reason_for_withdrawal') }}</label>
                                        <select name="narration" id="narration"
                                            class="form-control frame-stock-request-searchbar6 Specificity: (0,1,0)"
                                            required>
                                            <option value="">-- {{ __('messages.select_reason') }}
                                                --
                                            </option>
                                            @foreach ($narrations as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- Add this new textarea field below --}}
                                    <div class="">
                                        <label for="withdraw_notes"
                                            class="form-label">{{ __('messages.notes') }}</label>
                                        <textarea name="withdraw_notes" id="withdraw_notes" class="form-control " style="height: 40px !important;"
                                            rows="4" placeholder="{{ __('messages.notes') }}"></textarea>
                                    </div>

                                    <div class="text-right">
                                        <br>
                                        <button type="submit" class="btn pull-right rounded-pill submit-btn">
                                            <i class="fas fa-paper-plane me-1"></i>
                                            {{ __('messages.click_to_transfer') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="storeStockRequest" tabindex="-1" aria-labelledby="storeStockRequest"
        aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-scrollable  modal-lg">
            <div class="modal-content shadow-sm rounded-4 border-0">
                <div class="modal-header frame-stock-request-frame303 text-white rounded-top-4">
                    <h5 class="modal-title fw-semibold" id="cashout">
                        <i class="bi bi-camera-video me-2"></i>{{ __('messages.stock_request') }}
                    </h5>
                    {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <i class="bi bi-x-lg"></i>
                                </button> --}}

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                </div>

                <div class="modal-body p-6">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <form method="POST" action="{{ route('stock.store') }}">
                                        @csrf
                                        {{-- filepath: d:\xampp\htdocs\pos\resources\views\stocks\create.blade.php --}}
                                        <div class="mb-3">
                                            <input type="hidden" name="store_id" value="{{ @$branch_id }}">
                                        </div>
                                        <div id="product-items">
                                            <h5>Products</h5>
                                            <div class="item-row mb-3">

                                                <select name="items[0][product_id]"
                                                    class="form-control d-inline w-50 product-select-sh frame-stock-request-searchbar6 Specificity: (0,1,0)"
                                                    required>
                                                    <option value="">--
                                                        {{ __('messages.select_product') }} --
                                                    </option>
                                                    @foreach ($product_in_stocks as $pro)
                                                        <option value="{{ $pro->id }}">
                                                            {{ $pro->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('items')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                                <input type="number" name="items[0][quantity]"
                                                    class="form-control d-inline w-25 ms-2 frame-stock-request-searchbar6"
                                                    placeholder="Qty" min="1" required>

                                                <button type="button"
                                                    class="btn btn-danger btn-sm ms-2 remove-item">X</button>
                                            </div>
                                        </div>

                                        <button type="button" id="add-item" class="btn btn-primary btn-sm mb-3">+
                                            {{ __('messages.add_another_product') }}</button>
                                        <button type="button" id="clear-items"
                                            class="btn btn-warning btn-sm mb-3 ms-2">
                                            Clear
                                        </button>

                                        <div class="mb-3">
                                            <label for="notes"
                                                class="form-label">{{ __('messages.notes') }}</label>
                                            <textarea name="notes" id="notes" class="form-control frame-stock-request-group260"></textarea>
                                        </div>

                                        <button type="submit"
                                            class="btn frame-stock-request-group223">{{ __('messages.submit_request') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="warehouseStockRequest" tabindex="-1" aria-labelledby="warehouseStockRequest"
        aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content shadow-sm rounded-4 border-0">
                <div class="modal-header frame-stock-request-frame303 text-white rounded-top-4">
                    <h5 class="modal-title fw-semibold" id="cashout">
                        <i class="bi bi-camera-video me-2"></i>{{ __('messages.stock_request') }}
                    </h5>
                    {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <i class="bi bi-x-lg"></i>
                                </button> --}}

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-6">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <form id="warehouseForm" method="POST"
                                        action="{{ route('stock.warehouse') }}">
                                        @csrf
                                        <div id="product-items-wh">

                                            <h5>Products</h5>
                                            <div class="row item-row-wh product_items mb-3">
                                                <div class="col-md-4">
                                                    <select name="items[0][product_id]"
                                                        class="form-control product-select frame-stock-request-searchbar6 Specificity: (0,1,0)"
                                                        required>
                                                        <option value="">--
                                                            {{ __('messages.select_product') }}
                                                            --</option>
                                                        @foreach ($allProducts as $product)
                                                            <option value="{{ $product->id }}">
                                                                {{ $product->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    {{-- @error('items.0.product_id')
                                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                                        @enderror --}}
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="number" name="items[0][quantity]"
                                                        class="form-control frame-stock-request-searchbar6  ms-2"
                                                        placeholder="Qty" min="1" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm ms-2 remove-item-wh">X</button>
                                                    {{-- <img src="{{ asset('public/external/delete24dp1f1f1ffill0wght400grad0opsz2414472-853a.svg') }}" alt="Remove Stock Request Product" class="frame-stock-request-delete24dp1f1f1ffill0wght400grad0opsz2417"></button> --}}
                                                </div>

                                                <div class="availability-container-wh mt-2 small text-muted">
                                                    <!-- Filled dynamically with AJAX -->
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            {{-- filepath: d:\xampp\htdocs\pos\resources\views\stocks\create.blade.php --}}
                                            <div id="product-availability" class="mt-3">
                                                <!-- Availability information will be displayed here -->
                                            </div>
                                        </div>
                                        <button type="button" id="add-item-wh" class="btn btn-primary btn-sm mb-3">+
                                            {{ __('messages.add_another_product') }}</button>

                                        <div class="mb-3">
                                            <label for="notes"
                                                class="form-label">{{ __('messages.notes') }}</label>
                                            <textarea name="notes" id="notes" class="form-control frame-stock-request-group260"></textarea>
                                        </div>

                                        <button type="submit"
                                            class="btn frame-stock-request-group223">{{ __('messages.submit_request') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal HTML -->
    <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"
        data-backdrop="static" data-keyboard="false" wire:ignore.self>
        <div class="modal-dialog">
            <form method="POST" action="{{ route('cash-in-hand') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header custom-modal-header">
                        <h5 class="modal-title cash-summary-text61">
                            {{ __('messages.cash_in_hand_details') }}</h5>
                        <button type="button" class="btn btn-light border ms-1" data-bs-toggle="tooltip"
                            title="Logout" onclick="confirmLogout()">
                            <img src="{{ asset('public/external/fi106093284471-0vjk.svg') }}" class="img-fluid"
                                style="height: 25px;" />
                        </button>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="amount" id="holdamountTotal" class="form-control"
                            placeholder="Enter opening amount" readonly>
                        <div class="mb-2">
                            <table id="case_in_hand" class="case_in_hand table table-bordered product-table">
                                <thead class="table-info">
                                    <tr>
                                        <th class="main-screen-text25 text-center">
                                            {{ __('messages.currency') }}</th>
                                        <th class="main-screen-text25 text-center">
                                            {{ __('messages.notes') }}</th>
                                        <th class="main-screen-text25 text-center">
                                            {{ __('messages.amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="tbody-border">
                                    @foreach ($noteDenominations as $key => $denomination)
                                        <tr>
                                            <td class="fw-semibold text-center">{{ $denomination }} <span
                                                    class="mx-3">x</span></td>
                                            <td class="text-center">
                                                <div class="d-flex align-items-center" style="width: 100%;">
                                                    <button style="width: 40%;" type="button"
                                                        class="btn btn-gray btn-decrease rounded-start"
                                                        data-denomination="{{ $denomination }}"
                                                        style="font-size: 1.2rem;">−</button>
                                                    <input type="text"
                                                        name="cashNotes[{{ $key }}][{{ $denomination }}]"
                                                        id="cashhandsum_{{ $denomination }}"
                                                        class="form-control text-center  bg-white px-1 note-input"
                                                        value="0" readonly
                                                        data-denomination="{{ $denomination }}"
                                                        style="width: 60px;">
                                                    <button style="width: 40%;" type="button"
                                                        class="btn btn-gray rounded-end btn-increase"
                                                        data-denomination="{{ $denomination }}"
                                                        style="font-size: 1.2rem;">+</button>
                                                </div>
                                            </td>
                                            <td class="text-center fw-semibold amount-cell"
                                                id="discashhandsum_{{ $denomination }}">
                                                ₹{{ number_format($denomination, 0) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-success-new">
                                        <td class="fw-bold text-start total_bgc" colspan="2">
                                            {{ __('messages.total_cash') }}</td>
                                        <td class="fw-bold text-center total_bgc" id="totalNoteCashHand">
                                            ₹0</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @error('amount')
                            <span class="text-red">{{ $message }}</span>
                        @enderror

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary  rounded-pill" id="openStockStatusBtn">
                            {{ __('messages.view_stock_status') }}
                        </button>
                        <button type="submit"
                            class="btn btn-default submit-btn rounded-pill">{{ __('messages.submit') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="stockStatusModal" tabindex="-1" aria-labelledby="stockStatusModalLabel"
        aria-hidden="true" data-backdrop="static" data-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title cash-summary-text61">
                        {{ __('messages.product_opening_stock') }}</h6>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered product-table">
                        <thead class="table-info">
                            <tr>
                                <th class="main-screen-text25 text-center">{{ __('messages.product') }}
                                </th>
                                <th class="main-screen-text25 text-center">
                                    {{ __('messages.opening_stock') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $sum = 0;
                            @endphp
                            @foreach ($productStock as $product)
                                <tr>
                                    <td>{{ $product->product->name }}</td>
                                    <td>
                                        @php
                                            $stock = '';
                                            $lastShift = App\Models\UserShift::getYesterdayShift(
                                                auth()->user()->id,
                                                $branch_id,
                                            );
                                            if (empty($lastShift)) {
                                                $stock = $product->opening_stock;
                                            } else {
                                                $stock = $product->closing_stock;
                                            }
                                            $sum += $stock;

                                        @endphp
                                        <input type="number" name="productStocks[{{ $product->id }}]"
                                            class="form-control text-center" value="{{ $stock }}" readonly>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <!-- Add total in footer -->
                        <tfoot>
                            <tr class="table-success-new">
                                <td class="fw-bold text-start total_bgc">Total Stock Quantity</td>
                                <td class="fw-bold text-start total_bgc">
                                    <span>
                                        {{ $sum }}</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                {{-- <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"
                                    aria-label="Close" wire:click="#">Close</button>
                            </div> --}}
            </div>
        </div>
    </div>

    <!-- Numpad Modal -->
    <div wire:ignore.self class="modal fade" id="numpadModal" tabindex="-1" aria-labelledby="numpadLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header bg-primary text-white rounded-top">
                    <h5 class="modal-title fw-bold" id="numpadLabel">Enter Amount</h5>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"
                        wire:click="clearNumpad"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="display-4 text-center fw-bold mb-4">{{ $numpadValue }}</div>

                    <div class="row ">
                        @foreach (array_chunk([1, 2, 3, 4, 5, 6, 7, 8, 9], 3) as $row)
                            <div class="col-12 d-flex justify-content-center">
                                @foreach ($row as $num)
                                    <button wire:click="appendNumpadValue('{{ $num }}')"
                                        class="btn btn-light border fw-bold fs-4 mx-2"
                                        style="width: 70px; height: 70px;">
                                        {{ $num }}
                                    </button>
                                @endforeach
                            </div>
                        @endforeach

                        <div class="col-12 d-flex justify-content-center mt-2">
                            <button wire:click="appendNumpadValue('0')" class="btn btn-light border fw-bold fs-4 mx-2"
                                style="width: 70px; height: 70px;">0</button>
                            <button wire:click="backspaceNumpad" class="btn btn-danger fw-bold fs-4 mx-2"
                                style="width: 70px; height: 70px;">⌫</button>
                            <button wire:click="clearNumpad" class="btn btn-warning fw-bold fs-4 mx-2"
                                style="width: 70px; height: 70px;">C</button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-center bg-light rounded-bottom">
                    <button wire:click="applyNumpadValue" class="btn btn-success btn-lg w-75 fw-bold fs-5">OK</button>
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="cashModal" tabindex="-1" aria-labelledby="CashModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header custom-modal-header">
                    <span class="cash-summary-text61">{{ $this->headertitle }}
                        {{ __('messages.summary') }}</span>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div id="cash-payment">
                        <form onsubmit="event.preventDefault();" class="needs-validation" novalidate>
                            {{-- <h6 class="mb-3">💵 {{ __('messages.enter_cash_denominations') }}</h6> --}}
                            @if ($inOutStatus)
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <table class=" table table-bordered ">
                                            <thead class="table-dark">
                                                <tr>
                                                    @if (empty($this->selectedSalesReturn))
                                                        <th class="text-center" style="width:20%">
                                                            {{ __('messages.amount') }}</th>
                                                        <th class="text-center" style="width:20%">
                                                            {{ __('messages.in') }}</th>
                                                    @endif
                                                    <th style="width: 24%;" class="text-center">
                                                        {{ __('messages.currency') }}</th>
                                                    <th class="text-center" style="width:20%">
                                                        {{ __('messages.out') }}</th>
                                                    <th style="width:20%" class="text-center">
                                                        {{ __('messages.amount') }}
                                                        <button wire:click="clearCashNotes"
                                                            class="btn btn-danger btn-sm">
                                                            <i class="fa fa-eraser"></i>
                                                        </button>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($noteDenominations as $key => $denomination)
                                                    @php
                                                        $inValue = $cashNotes[$key][$denomination]['in'] ?? 0;
                                                        $outValue = $cashNotes[$key][$denomination]['out'] ?? 0;
                                                        // $rowAmount = ($inValue - $outValue) * $denomination;
                                                    @endphp
                                                    <tr>
                                                        @if (empty($this->selectedSalesReturn))
                                                            <td class="text-center fw-bold">
                                                                {{ format_inr($inValue * $denomination) }}
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="d-flex align-items-center"
                                                                    style="width: 100%">
                                                                    <button class="btn btn-gray rounded-start"
                                                                        style="width: 40px;"
                                                                        wire:click="decrementNote('{{ $key }}', '{{ $denomination }}', 'in')">
                                                                        −
                                                                    </button>


                                                                    <input type="number"
                                                                        class="form-control text-center rounded-0"
                                                                        value="{{ $inValue }}" readonly
                                                                        style="width: 60px;" />

                                                                    <button class="btn btn-gray rounded-end"
                                                                        style="width: 40px;"
                                                                        wire:click="incrementNote('{{ $key }}', '{{ $denomination }}', 'in')">
                                                                        +
                                                                    </button>
                                                                </div>

                                                            </td>
                                                        @endif
                                                        @if (!empty($this->selectedSalesReturn))
                                                            <td class="text-center ">
                                                            @else
                                                            <td class="text-center currency-center">
                                                        @endif
                                                        {{ format_inr($denomination) }}</td>

                                                        <td class="text-center">
                                                            <div class="d-flex align-items-center"
                                                                style="width: 100%">
                                                                <button class="btn btn-gray rounded-start"
                                                                    style="width: 40px;"
                                                                    wire:click="decrementNote('{{ $key }}', '{{ $denomination }}', 'out')">
                                                                    −
                                                                </button>

                                                                <input type="number"
                                                                    class="form-control text-center rounded-0"
                                                                    value="{{ $outValue }}" readonly
                                                                    style="width: 60px;" />

                                                                <button class="btn btn-gray rounded-end"
                                                                    style="width: 40px;"
                                                                    wire:click="incrementNote('{{ $key }}', '{{ $denomination }}', 'out')">
                                                                    +
                                                                </button>
                                                            </div>
                                                        </td>

                                                        <td class="text-center fw-bold">
                                                            {{ format_inr($outValue * $denomination) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <tr class="table-dark">
                                                    @if (empty($this->selectedSalesReturn))
                                                        <td class="text-center">
                                                            {{ format_inr($totals['totalIn']) }}</td>
                                                        <td class="text-center">
                                                            {{ $totals['totalInCount'] }}
                                                        </td>
                                                    @endif
                                                    <td class="text-center">TOTAL</td>
                                                    <td class="text-center">
                                                        {{ $totals['totalOutCount'] }}
                                                    </td>
                                                    <td class="text-center">
                                                        {{ format_inr($totals['totalOut']) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            @if (empty($this->selectedSalesReturn))
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <input type="hidden" wire-model="paymentType">

                                        <label for="cash"
                                            class="form-label">{{ __('messages.cash_amount') }}</label>
                                        <input type="number" class="form-control rounded-pill" id="cash"
                                            value="{{ $this->cashAmount }}" placeholder=""
                                            oninput="calculateChange()" readonly>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="tender"
                                            class="form-label">{{ __('messages.tendered_amount') }}</label>
                                        <input type="number" wire:model="cashPaTenderyAmt"
                                            class="form-control rounded-pill" id="tender" placeholder="" readonly>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="change"
                                            class="form-label">{{ __('messages.change_amount') }}</label>
                                        @if (!$inOutStatus)
                                            <input type="number" class="form-control rounded-pill" value="0"
                                                readonly>
                                        @else
                                            <input type="number" wire:model="cashPayChangeAmt"
                                                class="form-control rounded-pill" id="change" readonly>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if (!empty($this->selectedSalesReturn))
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>{{ __('messages.refund_description') }}</label>
                                        <textarea id="refundDesc" class="form-control" wire:model="refundDesc"
                                            placeholder="{{ __('messages.enter_refund_description') }}"></textarea>
                                    </div>
                                </div>
                            @endif
                            <hr class="custom-hr">
                            <div class="cash-summary-frame282">
                                <div class="d-flex justify-content-between ">
                                    {{ __('messages.subtotal') }}
                                    <span>{{ format_inr($sub_total) }}</span>
                                </div>
                                @if (auth()->user()->hasRole('cashier'))
                                    @if ($commissionAmount > 0)
                                        <div class="d-flex justify-content-between ">
                                            {{ __('messages.commission_deduction') }}
                                            <span>- {{ format_inr($commissionAmount) }}</span>
                                        </div>
                                    @endif
                                @endif
                                @if (auth()->user()->hasRole('warehouse'))
                                    {{-- @if ($partyAmount > 0) --}}
                                    <div class="d-flex justify-content-between ">
                                        {{ __('messages.commission_deduction') }}
                                        <span>- {{ format_inr($partyAmount) }}</span>
                                    </div>
                                    {{-- @endif --}}
                                    {{-- @if ($partyAmount > 0) --}}
                                    <div class=" ">
                                        <label class="-label" for="useCreditCheck">
                                            <input type="checkbox" wire:model="showCheckbox"
                                                wire:click="toggleCheck" />
                                            {{ __('messages.use_credit_to_pay') }}
                                        </label>
                                    </div>

                                    @if ($this->useCredit && $this->showCheckbox)
                                        <div class="d-flex justify-content-between align-items-center ">
                                            <label class="mb-0">
                                                {{ __('messages.credit') }}
                                            </label>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-primary fs-6 me-2">
                                                    {{ __('messages.available_credit') }}:
                                                    {{ number_format(($this->partyUserDetails->credit_points ?? 0) - ($this->partyUserDetails->use_credit ?? 0), 2) }}
                                                </span>
                                                <input type="number" wire:model="creditPay"
                                                    wire:input="creditPayChanged" class="form-control"
                                                    style="width: 100px;" />
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                {{-- @endif --}}
                                <div class="d-flex justify-content-between">
                                    {{ __('messages.tendered_amount') }}
                                    <span>{{ format_inr($this->cashAmount) }}</span>
                                    <input type="text" id="total" value="{{ $this->cashAmount }}"
                                        class="d-none" />
                                </div>
                            </div>
                            <p id="result" class="mt-3 fw-bold text-success"></p>
                            @if (count($itemCarts) > 0)
                                <div class="">

                                    @if (!empty($this->selectedSalesReturn) && $this->cashAmount == $totals['totalOut'])
                                        <button id="paymentSubmit"
                                            class="btn btn-default submit-btn btn-lg rounded-pill fw-bold w-100"
                                            wire:click="refund" wire:loading.attr="disabled">
                                            Refund
                                        </button>
                                    @else
                                        @if ($this->cashAmount == $totals['totalIn'] - $totals['totalOut'] && $errorInCredit == false)
                                            <button id="paymentSubmit"
                                                class="btn btn-default submit-btn btn-lg rounded-pill fw-bold w-100"
                                                wire:click="checkout" wire:loading.attr="disabled"
                                                wire:target="checkout">

                                                <span wire:loading.remove wire:target="checkout">
                                                    {{ __('messages.submit') }}
                                                </span>
                                                <span wire:loading wire:target="checkout">
                                                    Loading...
                                                </span>
                                            </button>
                                        @endif

                                        @if (!$inOutStatus)
                                            <button id="paymentSubmit"
                                                class="btn btn-default submit-btn btn-lg rounded-pill fw-bold w-100"
                                                wire:click="checkout" wire:loading.attr="disabled"
                                                wire:target="checkout">

                                                <span wire:loading.remove wire:target="checkout">
                                                    {{ __('messages.submit') }}
                                                </span>
                                                <span wire:loading wire:target="checkout">
                                                    Loading...
                                                </span>
                                            </button>
                                        @endif

                                    @endif
                                    {{-- <div wire:loading class=" text-muted">{{ __('messages.processing_payment') }}...
                                    </div> --}}
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="onliineModal" tabindex="-1" aria-labelledby="onlineModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header custom-modal-header">
                    <span class="cash-summary-text61">{{ $this->headertitle }}
                        {{ __('messages.summary') }}</span>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div id="cashupi-payment">
                        <form onsubmit="event.preventDefault(); " class="needs-validation" novalidate>
                            @php
                                $totalIn = 0;
                                $totalOut = 0;
                                $totalAmount = 0;
                            @endphp

                            <div class="cash-summary-frame282">
                                <div class="d-flex justify-content-between ">
                                    {{ __('messages.subtotal') }}
                                    <span>{{ format_inr($sub_total) }}</span>
                                </div>

                                @if ($commissionAmount > 0)
                                    <div class="d-flex justify-content-between ">
                                        {{ __('messages.commission_deduction') }}
                                        <span>- {{ format_inr($commissionAmount) }}</span>
                                    </div>
                                @endif
                                @if ($partyAmount > 0)
                                    <div class="d-flex justify-content-between ">
                                        {{ __('messages.commission_deduction') }}
                                        <span>- {{ format_inr($partyAmount) }}</span>
                                    </div>
                                @endif

                                @if (auth()->user()->hasRole('warehouse'))
                                    <div class="">
                                        <label for="useCreditCheck">
                                            <input type="checkbox" wire:model="showCheckbox"
                                                wire:click="toggleCheck" />

                                            {{ __('messages.use_credit_to_pay') }}
                                        </label>
                                    </div>

                                    @if ($this->useCredit && $this->showCheckbox)
                                        <div class="d-flex justify-content-between align-items-center ">
                                            <label class="mb-0">
                                                {{ __('messages.credit') }}
                                            </label>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-primary fs-6 me-2">
                                                    {{ __('messages.available_credit') }}:
                                                    {{ number_format(($this->partyUserDetails->credit_points ?? 0) - ($this->partyUserDetails->use_credit ?? 0), 2) }}
                                                </span>
                                                <input type="number" wire:model="creditPay"
                                                    wire:input="creditPayChanged" class="form-control"
                                                    style="width: 100px;" />
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                {{-- @endif --}}
                                <div class="d-flex justify-content-between">
                                    <strong>{{ __('messages.total_payable') }}</strong>
                                    <span>{{ format_inr($this->cashAmount) }}</span>
                                    <input type="text" id="total" value="{{ $this->cashAmount }}"
                                        class="d-none" />
                                </div>
                            </div>

                            <p id="result" class="mt-3 fw-bold text-success"></p>
                            <div class="mt-4">
                                <button id="paymentSubmit"
                                    class="btn btn-default submit-btn btn-lg rounded-pill fw-bold w-100"
                                    wire:click="onlinePaymentCheckout" wire:loading.attr="disabled"
                                    wire:target="onlinePaymentCheckout">
                                    <span wire:loading.remove
                                        wire:target="onlinePaymentCheckout">{{ __('messages.submit') }}</span>
                                    <span wire:loading wire:target="onlinePaymentCheckout">Loading...</span>
                                </button>
                                {{-- <div wire:loading class=" text-muted">{{ __('messages.processing_payment') }}...
                                </div> --}}
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="caseUpiModal" tabindex="-1"
        aria-labelledby="caseUpiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header custom-modal-header">
                    <span class="cash-summary-text61">{{ $this->headertitle }}
                        {{ __('messages.summary') }}</span>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div id="cashupi-payment">
                        <form onsubmit="event.preventDefault(); " class="needs-validation" novalidate>
                            @php
                                $totalIn = 0;
                                $totalOut = 0;
                                $totalAmount = 0;
                            @endphp

                            @if ($inOutStatus)
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <table class="customtable table table-bordered">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>{{ __('messages.amount') }}</th>
                                                    @if (empty($this->selectedSalesReturn))
                                                        <th class="text-center" style="width:20%">
                                                            {{ __('messages.in') }}</th>
                                                    @endif
                                                    <th>{{ __('messages.currency') }}</th>
                                                    <th class="text-center" style="width:20%">
                                                        {{ __('messages.out') }}</th>
                                                    <th class="text-center">
                                                        {{ __('messages.amount') }}
                                                        <button wire:click="clearCashUpiNotes"
                                                            class="btn btn-danger btn-sm">
                                                            <i class="fa fa-eraser"></i>
                                                        </button>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($noteDenominations as $key => $denomination)
                                                    @php
                                                        $inValue = $cashupiNotes[$key][$denomination]['in'] ?? 0;
                                                        $outValue = $cashupiNotes[$key][$denomination]['out'] ?? 0;
                                                        $rowAmount = ($inValue - $outValue) * $denomination;

                                                        $totalIn += $inValue * $denomination;
                                                        $totalOut += $outValue * $denomination;
                                                        $totalAmount += $rowAmount;
                                                    @endphp

                                                    <tr>
                                                        <td class="text-center fw-bold">
                                                            {{ format_inr($inValue * $denomination) }}
                                                        </td>

                                                        @if (empty($this->selectedSalesReturn))
                                                            <td class="text-center">
                                                                <div class="d-flex align-items-center"
                                                                    style="width: 100%">
                                                                    <button class="btn btn-gray rounded-start"
                                                                        style="width: 40px;"
                                                                        wire:click="decrementCashUpiNote('{{ $key }}', '{{ $denomination }}', 'in')">
                                                                        -
                                                                    </button>
                                                                    <input type="number"
                                                                        class="form-control text-center rounded-0"
                                                                        value="{{ $inValue }}" readonly
                                                                        style="width: 60px;">
                                                                    <button class="btn btn-gray rounded-end"
                                                                        style="width: 40px;"
                                                                        wire:click="incrementCashUpiNote('{{ $key }}', '{{ $denomination }}', 'in')">
                                                                        +
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        @endif

                                                        <td class="text-center currency-center">
                                                            {{ format_inr($denomination) }}</td>

                                                        <td class="text-center">
                                                            <div class="d-flex align-items-center"
                                                                style="width: 100%">
                                                                <button class="btn btn-gray rounded-start"
                                                                    style="width: 40px;"
                                                                    wire:click="decrementCashUpiNote('{{ $key }}', '{{ $denomination }}', 'out')">
                                                                    -
                                                                </button>
                                                                <input type="number"
                                                                    class="form-control text-center"
                                                                    value="{{ $outValue }}" readonly
                                                                    style="width: 60px;">
                                                                <button class="btn btn-gray rounded-end"
                                                                    style="width: 40px;"
                                                                    wire:click="incrementCashUpiNote('{{ $key }}', '{{ $denomination }}', 'out')">
                                                                    +
                                                                </button>
                                                            </div>
                                                        </td>

                                                        <td class="text-center fw-bold">
                                                            {{ format_inr($rowAmount) }}
                                                        </td>
                                                    </tr>
                                                @endforeach

                                                <tr class="table-dark">
                                                    <td class="text-center">
                                                        {{ format_inr($totalIn) }}
                                                    </td>
                                                    @if (empty($this->selectedSalesReturn))
                                                        <td class="text-center">{{ $totalIn }}
                                                        </td>
                                                    @endif
                                                    <td class="text-center">TOTAL</td>
                                                    <td class="text-center">{{ $totalOut }}</td>
                                                    <td class="text-center">
                                                        {{ format_inr($totalAmount) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <input type="hidden" wire-model="paymentType">
                                    <input type="hidden" id="actualCash"
                                        class="border rounded w-full p-2 bg-gray-100"
                                        value="{{ $this->cashAmount }}" readonly>
                                    @php
                                        $this->cash = $totalAmount;
                                        $this->upi = $this->cashAmount - $totalAmount;

                                    @endphp

                                    <label for="cash" class="form-label">Cash Amount</label>
                                    <!-- Input Field with Debounce and Max Value -->
                                    <input type="number" id="cashAmount" step="1"
                                        wire:model.debounce.500ms="cash" class="form-control rounded-pill"
                                        min="0" max="{{ $this->cashAmount }}">
                                    <!-- Dynamically set the max value from Livewire -->

                                    <!-- Error Message -->
                                    @error('cash')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror

                                </div>

                                <div class="col-md-6">
                                    <label for="cash"
                                        class="form-label">{{ __('messages.upi_amount') }}</label>

                                    <input type="number" id="onlineAmount" step="1"
                                        wire:model.live.debounce.500ms="upi" class="form-control rounded-pill"
                                        min="0" max="{{ $this->cashAmount }}">
                                </div>
                            </div>
                            <hr class="custom-hr">
                            <p id="result" class="mt-3 fw-bold text-success"></p>
                            <div class="mt-4">
                                @if ($inOutStatus && $this->showOnline == true && $this->cashAmount > 0)
                                    <button id="paymentSubmit"
                                        class="btn btn-default submit-btn btn-lg rounded-pill fw-bold w-100"
                                        wire:click="onlinePaymentCheckout" wire:loading.attr="disabled"
                                        wire:target="onlinePaymentCheckout">
                                        <span wire:loading.remove
                                            wire:target="onlinePaymentCheckout">{{ __('messages.submit') }}</span>
                                        <span wire:loading wire:target="onlinePaymentCheckout">Loading...</span>
                                    </button>
                                @else
                                    @if ($inOutStatus && $this->cashAmount == $this->cash + $this->upi && $this->upi >= 0)
                                        <button id="paymentSubmit"
                                            class="btn btn-default submit-btn btn-lg rounded-pill fw-bold w-100"
                                            wire:click="checkout" wire:loading.attr="disabled"
                                            wire:target="checkout">
                                            <span wire:loading.remove
                                                wire:target="checkout">{{ __('messages.submit') }}</span>
                                            <span wire:loading wire:target="checkout">Loading...</span>
                                        </button>
                                    @endif
                                @endif
                                @if (!$inOutStatus)
                                    <button id="paymentSubmit"
                                        class="btn btn-default submit-btn btn-lg rounded-pill fw-bold w-100"
                                        wire:click="checkout" wire:loading.attr="disabled" wire:target="checkout">

                                        <span wire:loading.remove wire:target="checkout">
                                            {{ __('messages.submit') }}
                                        </span>
                                        <span wire:loading wire:target="checkout">
                                            Loading...
                                        </span>
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Before reload, set a flag to restore fullscreen
    // function reloadWithFullscreen() {
    //     localStorage.setItem('restoreFullscreen', 'true');
    //     location.reload();
    // }

    // After reload, check flag and request fullscreen
    // document.addEventListener("DOMContentLoaded", function() {
    //     console.log(localStorage.getItem('restoreFullscreen'),
    //         "==localStorage.getItem('restoreFullscreen')===");
    //     if (localStorage.getItem('restoreFullscreen') === true) {
    //         localStorage.removeItem('restoreFullscreen');

    //         document.addEventListener('fullscreenchange', () => {
    //             alert("dfg");
    //             if (!document.fullscreenElement) {
    //                 // Try to re-enter fullscreen after print, using a small timeout
    //                 setTimeout(() => {
    //                     document.documentElement.requestFullscreen().catch(err => {
    //                         console.log('Fullscreen request failed:', err);
    //                     });
    //                 }, 500); // Delay to allow print dialog to close
    //             }
    //         });
    //     }
    // });

    window.addEventListener('open-cash-modal', event => {
        const modal = new bootstrap.Modal(document.getElementById('cashModal'));
        modal.show();
    });

    window.addEventListener('online-cash-modal', event => {
        const modal = new bootstrap.Modal(document.getElementById('onliineModal'));
        modal.show();
    });

    window.addEventListener('online-cash-upi-modal', event => {
        const modal = new bootstrap.Modal(document.getElementById('caseUpiModal'));
        modal.show();
    });

    window.addEventListener('hide-open-cash-modal', event => {
        const modalEl = document.getElementById('cashModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.hide();
        // Hide modal element
        modalEl.style.display = 'none';
        modalEl.classList.remove('show');
        modalEl.setAttribute('aria-hidden', 'true');
        // Remove backdrop manually
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        // Remove 'modal-open' class from body to restore scroll
        document.body.classList.remove('modal-open');
        document.body.style.paddingRight = '';
    });

    window.addEventListener('hide-online-cash-modal', event => {
        const modalEl = document.getElementById('onliineModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.hide();
        // Hide modal element
        modalEl.style.display = 'none';
        modalEl.classList.remove('show');
        modalEl.setAttribute('aria-hidden', 'true');
        // Remove backdrop manually
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        // Remove 'modal-open' class from body to restore scroll
        document.body.classList.remove('modal-open');
        document.body.style.paddingRight = '';

    });

    window.addEventListener('hide-cash-upi-modal', event => {
        const modalEl = document.getElementById('caseUpiModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.hide();
        // Hide modal element
        modalEl.style.display = 'none';
        modalEl.classList.remove('show');
        modalEl.setAttribute('aria-hidden', 'true');
        // Remove backdrop manually
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        // Remove 'modal-open' class from body to restore scroll
        document.body.classList.remove('modal-open');
        document.body.style.paddingRight = '';
    });

    window.addEventListener('triggerPrint', event => {
        // Hide preview or image if any
        const el = document.getElementsByClassName('lastsavepic')[0];
        if (el) {
            el.classList.add('d-none');
        }

        // Clear previous iframe
        const iframeContainer = document.getElementById('iframe-container');
        iframeContainer.innerHTML = '';

        // Create and style iframe
        const iframe = document.createElement('iframe');
        iframe.src = event.detail[0].pdfPath;
        iframe.style.position = 'absolute';
        iframe.style.top = '-9999px';
        iframe.style.left = '-9999px';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = 'none';

        iframeContainer.appendChild(iframe);

        // Flag to trigger fullscreen prompt
        let needsFullscreen = false;

        iframe.onload = function() {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();

            iframe.contentWindow.onafterprint = () => {
                // Clean up iframe
                iframe.remove();

                // Ensure page is responsive again
                setTimeout(() => {
                    window.focus();
                    document.body.focus();
                }, 100);

                // Show fullscreen button
                needsFullscreen = true;
                // showFullscreenPrompt();

                document.addEventListener('fullscreenchange', () => {
                    if (!document.fullscreenElement) {
                        // Try to re-enter fullscreen after print, using a small timeout
                        setTimeout(() => {
                            document.documentElement.requestFullscreen().catch(err => {
                                console.log('Fullscreen request failed:', err);
                            });
                        }, 500); // Delay to allow print dialog to close
                    }
                });
            };
        };

        // Function to show fullscreen prompt button
        function showFullscreenPrompt() {
            const btn = document.createElement('button');
            btn.innerText = 'Click to Restore Fullscreen';
            btn.style.position = 'fixed';
            btn.style.top = '50%';
            btn.style.left = '50%';
            btn.style.transform = 'translate(-50%, -50%)';
            btn.style.padding = '1rem 2rem';
            btn.style.fontSize = '1.2rem';
            btn.style.zIndex = '99999';
            btn.style.backgroundColor = '#007BFF';
            btn.style.color = '#fff';
            btn.style.border = 'none';
            btn.style.borderRadius = '10px';
            btn.style.boxShadow = '0 5px 10px rgba(0, 0, 0, 0.2)';
            btn.style.cursor = 'pointer';

            btn.addEventListener('click', function() {
                restoreFullscreen();
                btn.remove();
            });

            document.body.appendChild(btn);
        }

        // Fullscreen restore function — must be called on user interaction
        function restoreFullscreen() {
            const docEl = document.documentElement;
            if (!document.fullscreenElement) {
                docEl.requestFullscreen?.().catch(err => {
                    console.warn("Fullscreen restore failed:", err);
                });
            }
        }
    });

    window.addEventListener('DOMContentLoaded', function() {
        $('#storeStockRequest').modal('hide');
    });

    window.addEventListener('DOMContentLoaded', function() {
        $('#warehouseStockRequest').modal('hide');
    });
</script>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('openModal', () => {
            //   var myModal = new bootstrap.Modal(document.getElementById('myModal'));
            const myModal = new bootstrap.Modal(document.getElementById('myModal'), {
                backdrop: 'static',
                keyboard: false
            });
            myModal.show();
        });
    });

    document.addEventListener('livewire:init', () => {
        Livewire.on('openCustomerCreditModal', () => {
            //   var myModal = new bootstrap.Modal(document.getElementById('myModal'));
            const myModal = new bootstrap.Modal(document.getElementById('myModal'), {
                backdrop: 'static',
                keyboard: false
            });
            myModal.show();
        });
    });

    document.addEventListener('livewire:init', () => {
        Livewire.on('openModal', () => {
            //   var myModal = new bootstrap.Modal(document.getElementById('myModal'));
            const myModal = new bootstrap.Modal(document.getElementById('myModal'), {
                backdrop: 'static',
                keyboard: false
            });
            myModal.show();
        });
    });

    document.addEventListener('livewire:init', () => {
        Livewire.on('closingStocksOpenModal', () => {
            //   var myModal = new bootstrap.Modal(document.getElementById('myModal'));
            const closingStocksModal = new bootstrap.Modal(document.getElementById(
                'closingStocksModal'), {
                backdrop: 'static',
                keyboard: false
            });
            closingStocksModal.show();
        });
    });

    document.addEventListener('livewire:load', function() {
        // Listen for the 'close-modal' event from Livewire
        Livewire.on('close-modal', function() {
            // Close the modal using jQuery
            $('#cashModal').modal('hide');
        });
    });
</script>

<script>
    window.addEventListener('user-selection-updated', event => {
        const userId = event.detail.userId;
        yourJsFunction(userId);
    });

    function calculateChange() {
        let cash = parseFloat(document.getElementById("cash").value);
        let tender = parseFloat(document.getElementById("tender").value);

        if (isNaN(cash) || isNaN(tender)) {
            document.getElementById("change").value = '';
            document.getElementById("notes-breakdown").innerHTML = '';
            return;
        }

        let change = tender - cash;
        document.getElementById("change").value = change.toFixed(2);

        // Only show note breakdown if there's positive change
        let notes = [10, 20, 50, 100, 100, 500];
        let breakdown = '';
        let remaining = change;

        if (change >= 1) {
            notes.forEach(function(note) {
                if (remaining >= note) {
                    let qty = Math.floor(remaining / note);
                    breakdown += `${note} x ${qty} note(s)<br>`;
                    remaining %= note;
                }
            });
        } else if (change < 0) {
            breakdown = `Remaining amount to collect: ${Math.abs(change).toFixed(2)}`;
        } else {
            breakdown = `Exact amount received. No change needed.`;
        }

        //  document.getElementById("notes-breakdown").innerHTML = breakdown;
    }

    function calculateCashUpiBreakdown() {
        const denominations = [{
                id: 'notes_10',
                value: 10,
                sumId: 'sum_10'
            },
            {
                id: 'notes_20',
                value: 20,
                sumId: 'sum_20'
            },
            {
                id: 'notes_50',
                value: 50,
                sumId: 'sum_50'
            },
            {
                id: 'notes_100',
                value: 100,
                sumId: 'sum_100'
            },
            {
                id: 'notes_500',
                value: 500,
                sumId: 'sum_500'
            }
        ];

        //
        let total = 0;
        let notesum = 0;
        const cashAmount = document.getElementById('cashAmount').value;
        const onlineAmount = document.getElementById('onlineAmount').value;
        actualTotal = document.getElementById('actualCash').value;
        denominations.forEach(note => {
            //console.log(document.getElementById(note.id).value);
            const count = parseInt(document.getElementById(note.id).value) || 0;
            // console.log(count);

            const subtotal = count * note.value;
            total += subtotal;
            //console.log(subtotal);

            document.getElementById(note.sumId).textContent = `${subtotal.toLocaleString()}`;
        });

        document.getElementById('totalNoteCash').textContent = ` ${total.toLocaleString()}`;

        //console.log("Cash Amount: ", actualTotal);
        //console.log("Actual Total: ", parseFloat(cashAmount) + parseFloat(onlineAmount));

        if (actualTotal == parseFloat(cashAmount) + parseFloat(onlineAmount)) {
            document.getElementById('paymentSubmit').style.display = 'block';
            // document.getElementById('result').textContent = `Total Cash: ${total.toLocaleString()}`;
        }
    }
</script>
<script>
    let stream;

    navigator.mediaDevices.getUserMedia({
        video: true
    }).then(mediaStream => {
        stream = mediaStream;
        document.getElementById('video1').srcObject = mediaStream;
        document.getElementById('video2').srcObject = mediaStream;
    });

    function captureImage(type) {
        const video = document.getElementById(type === 'product' ? 'video1' : 'video2');
        const canvas = document.getElementById(type === 'product' ? 'canvas1' : 'canvas2');
        const input = document.getElementById(type === 'product' ? 'productImageInput' : 'userImageInput');

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        canvas.toBlob(blob => {
            const formData = new FormData();
            formData.append('photo', blob, 'captured_image.png');
            formData.append('type', type);
            const commissionUserInput = document.getElementById('commissionUser');
            if (commissionUserInput) {
                formData.append('selectedCommissionUser', commissionUserInput.value);
            }
            const partyUserInput = document.getElementById('partyUser');
            if (partyUserInput) {
                formData.append('selectedPartyUser', partyUserInput.value);
            }
            fetch('{{ route('products.uploadpic') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.path) {
                        var alertmsg = (type === 'product') ? 'Product' : 'Customer';
                        if (type === 'product') {
                            document.getElementById('imgproduct').src = data.orignal_path + '?t=' +
                                new Date().getTime();
                            goToStep(2);
                        } else if (type === 'user') {
                            document.getElementById('imguser').src = data.orignal_path + '?t=' + new Date()
                                .getTime();
                            goToStep(3);
                        }
                        Swal.fire({
                            title: 'Photo Uploaded!',
                            text: 'Your ' + alertmsg + ' photo has been uploaded successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert('Upload failed!');
                    }
                })
                .catch(err => console.log(err));
        }, 'image/png');
    }

    function updateCashOnlineFields(source) {
        const totalAmount = parseFloat($("#total").val()) || 0;
        const cash = parseFloat($("#cashAmount").val()) || 0;
        const online = parseFloat($("#onlineAmount").val()) || 0;

        if (source === 'cash') {
            remaining = Math.max(totalAmount - cash, 0);
            if (totalAmount < cash) {
                Swal.fire({
                    icon: 'error',
                    title: 'Total Amount greater than Cash Amount',
                    text: 'The cash amount cannot be greater than the allowed total amount.',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    // After clicking OK, set cashAmount to totalAmount
                    if (result.isConfirmed) {
                        $("#cashAmount").val(totalAmount); // Set the cash amount input to total amount
                    }
                });
            }

            //console.log("Cash Amount: ", cash);
            $("#onlineAmount").val(remaining);
        } else if (source === 'online') {
            //console.log("Online Amount: ", online);

            remaining = Math.max(totalAmount - online, 0);
            $("#cashAmount").val(remaining);
        }
    }

    $("#cashAmount").on("input", () => updateCashOnlineFields('cash'));
    $("#onlineAmount").on("input", () => updateCashOnlineFields('online'));
    document.addEventListener('DOMContentLoaded', function() {
        $('#holdTransactionsModal').on('show.bs.modal', function() {
            Livewire.dispatch('loadHoldTransactions');
        });
    });

    $(document).ready(function() {
        $('#captureModal').on('shown.bs.modal', function() {
            // Access camera stream
            navigator.mediaDevices.getUserMedia({
                    video: true
                })
                .then(function(stream) {
                    // Attach the stream to the video element
                    document.getElementById('video1').srcObject = stream;
                })
                .catch(function(err) {
                    //console.error("Camera access denied:", err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Camera Access Denied',
                        text: 'Please grant camera permission to capture a picture.',
                        confirmButtonText: 'OK'
                    });
                });
        });

        // Optional: Stop the camera when the modal is closed
        $('#captureModal').on('hidden.bs.modal', function() {
            document.getElementById('step1').classList.remove('d-none');
            document.getElementById('step2').classList.add('d-none');
            const video = document.getElementById('video1');
            const stream = video.srcObject;
            if (stream) {
                const tracks = stream.getTracks();
                tracks.forEach(track => track.stop());
                video.srcObject = null;
            }
            location.reload();

        });
        //  // Set your shift end time here

        //  let shiftEnd = new Date("{{ $this->shiftEndTime }}"); // Example: 2025-04-23 18:00:00

        // function checkShiftTime() {
        //     let now = new Date();
        //     let diffMinutes = (shiftEnd - now) / 1000 / 60;
        //     if (diffMinutes <= 10 && diffMinutes > 0) {
        //         $('#closeShiftBtn').removeClass('d-none');
        //     } else {
        //         $('#closeShiftBtn').addClass('d-none');
        //     }
        // }

        // // Check immediately
        // checkShiftTime();

        // Check every 30 seconds
        //setInterval(checkShiftTime, 30000);
        // $('#captureModal').on('hidden.bs.modal', function() {
        //     // Reset to Step 1 when modal is closed
        //     document.getElementById('step1').classList.remove('d-none');
        //     document.getElementById('step2').classList.add('d-none');
        // });
        // Livewire.on('alert_remove', () => {
        //     setTimeout(() => {
        //         $(".toast").fadeOut("fast");
        //     }, 2000);
        // });
    });

    const input = document.getElementById('numberInput');
    const numpad = document.getElementById('numpad');

    const createNumpad = () => {
        const buttons = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'Clear', 'OK'];
        numpad.innerHTML = '';

        buttons.forEach(btn => {
            const button = document.createElement('button');
            button.textContent = btn;
            button.addEventListener('click', () => handleNumpadClick(btn));
            numpad.appendChild(button);
        });
    };

    const handleNumpadClick = (value) => {
        if (value === 'Clear') {
            input.value = '';
        } else if (value === 'OK') {
            numpad.style.display = 'none';
        } else {
            input.value += value;
        }
    };
</script>

<script>
    function updateNote(id, delta, denomination) {
        fetch('/get-available-notes', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(availableNotes => {
                const input = document.getElementById('withcashnotes_' + id);
                let current = parseInt(input.value || 0);

                const key = denomination.toString();
                const maxNotes = availableNotes[key];

                if (delta > 0 && (!maxNotes || current >= maxNotes)) {
                    Swal.fire({
                        title: 'Note Limit Reached',
                        text: `No more ${denomination} notes available.`,
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                current += delta;
                if (current < 0) current = 0;

                input.value = current;
                document.getElementById('display_' + id).innerText = current;
                document.getElementById('withcashsum_' + id).innerText = '' + (current * denomination);

                calculateTotal();
            })
            .catch(error => {
                //console.error('Error fetching available notes:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Could not fetch available notes. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
    }

    function calculateTotal() {
        let total = 0;

        document.querySelectorAll('input[id^="withcashnotes_"]').forEach(input => {
            const count = parseInt(input.value || 0);
            const denom = parseInt(input.id.split('_').pop());
            total += count * denom;
        });

        document.getElementById('totalNoteCashwith').innerText = '' + total;
        document.getElementById('withamountTotal').value = total;
    }

    // Function to display dynamic SweetAlert
    function showAlert(type, title, message) {
        Swal.fire({
            title: title || (type === 'success' ? 'Success!' : 'Error!'),
            text: message || (type === 'success' ? 'Operation completed successfully.' :
                'Something went wrong.'),
            icon: type, // 'success' or 'error'
            confirmButtonText: 'OK',
            timer: 2000, // Auto-close after 2 seconds for small alert
            showConfirmButton: true, // Show the confirm button
            position: 'center', // Center the alert in the middle of the screen
            toast: false, // Disable toast style (centered alert)
            timerProgressBar: true, // Show progress bar
            backdrop: true, // Enable backdrop
            allowOutsideClick: false, // Prevent closing the alert by clicking outside
            showCloseButton: true, // Optional: Show a close button in the top-right corner
            customClass: {
                popup: 'small-alert' // Apply custom class for small size
            }
        });
    }

    // Event listeners for success and error
    window.addEventListener('notiffication-sucess', (event) => {
        // Success Example
        showAlert('success', 'LiquorHub!', event.detail[0].message ||
            'Your cart has been voided successfully.');
    });

    window.addEventListener('notiffication-error', (event) => {
        // Error Example
        showAlert('error', 'LiquorHub!', event.detail[0].message || 'Failed to void the cart.');
    });

    window.addEventListener('notiffication-error-close-shift', (event) => {
        // Error Example
        showAlert('error', 'LiquorHub!', event.detail[0].message || 'Failed to void the cart.');
    });

    window.addEventListener('order-saved', event => {
        const {
            type,
            title,
            message
        } = event.detail;
        Swal.fire({
            title: 'Success!',
            text: 'Transaction completed successfully.',
            icon: type, // 'success' or 'error'
            confirmButtonText: 'OK',
            timer: 3000,
            showConfirmButton: true,
            position: 'center',
            toast: false,
            timerProgressBar: true,
            backdrop: true,
            allowOutsideClick: false,
            showCloseButton: true,
            customClass: {
                popup: 'small-alert'
            }

        }).then((result) => {
            $('#cashModal').modal('hide');
            if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                // reloadWithFullscreen(); // Sets flag and reloads the page
                // location.reload(); // reload after OK click or auto close
            }
        });
    });

    window.addEventListener('close-shift-error', event => {
        const {
            type,
            title,
            message
        } = event.detail;
        Swal.fire({
            title: 'Error!',
            text: 'You have pending invoices on hold. Please clear them before closing the shift.',
            icon: 'error', // Explicitly set to 'error'
            confirmButtonText: 'OK',
            showConfirmButton: true,
            position: 'center',
            toast: false,
            timerProgressBar: false,
            backdrop: true,
            allowOutsideClick: false,
            showCloseButton: true,
            customClass: {
                popup: 'small-alert'
            }
        });

    });

    window.addEventListener('close-shift-12am', event => {
        Swal.fire({
            title: 'Error!',
            text: 'Shift start is not allowed before 12:00 AM. Please try again after 12:00 AM.',
            icon: 'error',
            position: 'center',
            toast: false,
            backdrop: true,
            allowOutsideClick: false,
            showCloseButton: false,
            showCancelButton: false,
            confirmButtonText: 'Logout',
            customClass: {
                popup: 'large-alert'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit the POST logout form
                document.getElementById('logout-form').submit();
            }
        });
    });
</script>
<script>
    window.addEventListener('close-hold-modal', function() {
        // Hide modal
        const modal = document.getElementById('holdTransactionsModal');
        modal.style.display = 'none';
        modal.classList.remove('show'); // Optional: remove show class
        modal.setAttribute('aria-hidden', 'true');

        // Remove backdrop manually
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());

        // Optional: remove 'modal-open' class from body to restore scroll
        document.body.classList.remove('modal-open');
        document.body.style.paddingRight = '';
    });
</script>

<script>
    function updateAmounts() {
        let total = 0;
        const amountInput = document.getElementById('holdamountTotal');
        document.querySelectorAll('.note-input').forEach(function(input) {
            const denomination = parseInt(input.dataset.denomination);
            const quantity = parseInt(input.value) || 0;
            const amount = denomination * quantity;
            document.getElementById('discashhandsum_' + denomination).innerText = '' + amount;

            document.getElementById('cashhandsum_' + denomination).innerText = '' + amount;
            total += amount;

            amountInput.value = total;
        });
        document.getElementById('totalNoteCashHand').innerText = '' + total;
    }

    document.querySelectorAll('.btn-increase').forEach(function(button) {
        button.addEventListener('click', function() {
            const denomination = this.dataset.denomination;
            const input = document.getElementById('cashhandsum_' + denomination);
            input.value = parseInt(input.value || 0) + 1;
            updateAmounts();
        });
    });

    document.querySelectorAll('.btn-decrease').forEach(function(button) {
        button.addEventListener('click', function() {
            const denomination = this.dataset.denomination;
            const input = document.getElementById('cashhandsum_' + denomination);
            if (parseInt(input.value) > 0) {
                input.value = parseInt(input.value) - 1;
                updateAmounts();
            }
        });
    });

    // Optional: If you still want to allow manual update via text field
    document.querySelectorAll('.note-input').forEach(function(input) {
        input.addEventListener('input', function() {
            updateAmounts();
        });
    });

    let itemIndex = 1;
    document.getElementById('add-item').addEventListener('click', function() {
        const row = document.querySelector('.item-row').cloneNode(true);
        row.querySelectorAll('select, input').forEach(el => {
            const name = el.getAttribute('name');
            const updatedName = name.replace(/\[\d+\]/, `[${itemIndex}]`);
            el.setAttribute('name', updatedName);
            if (el.tagName === 'INPUT') el.value = '';
        });
        document.getElementById('product-items').appendChild(row);
        itemIndex++;
    });

    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-item')) {
            if (document.querySelectorAll('.item-row').length > 1) {
                e.target.closest('.item-row').remove();
            }
        }

        if (e.target && e.target.id === 'clear-items') {
            const container = document.getElementById('product-items');
            const firstRow = container.querySelector('.item-row');
            // Remove all item rows
            container.querySelectorAll('.item-row').forEach((row, index) => {
                if (index > 0) row.remove();
            });

            // Reset first row's inputs
            firstRow.querySelectorAll('input').forEach(input => input.value = '');
            firstRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

            // Reset index
            itemIndex = 1;
        }

        // Clear All Rows (except first)
        if (e.target && e.target.id === 'clear-item-wh') {
            const container = document.getElementById('product-items-wh');
            const firstRow = container.querySelector('.item-row-wh');

            container.querySelectorAll('.item-row-wh').forEach((row, index) => {
                if (index > 0) row.remove();
            });

            // Reset inputs in first row
            firstRow.querySelectorAll('input').forEach(input => input.value = '');
            firstRow.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

            itemIndexWh = 1;
        }

        if (e.target && e.target.classList.contains('remove-item-wh')) {
            if (document.querySelectorAll('.item-row-wh').length > 1) {
                e.target.closest('.item-row-wh').remove();
            }
        }
    });

    let itemIndex1 = 1;

    document.getElementById('add-item-wh').addEventListener('click', function() {

        const row = document.querySelector('.item-row-wh').cloneNode(true);
        row.querySelectorAll('select, input').forEach(el => {
            const name = el.getAttribute('name');
            const updatedName = name.replace(/\[\d+\]/, `[${itemIndex}]`);
            el.setAttribute('name', updatedName);
            if (el.tagName === 'INPUT') el.value = '';
        });
        document.getElementById('product-items-wh').appendChild(row);
        itemIndex++;
    });
</script>
<script>
    document.addEventListener('click', function(event) {
        const searchContainer = document.getElementById('search-container');
        const suggestionBox = document.getElementById('search-suggestion-wrapper');
        const searchInput = document.getElementById('searchInput');

        //console.log(searchContainer);
        //console.log(suggestionBox);
        if (suggestionBox) {
            suggestionBox.style.display = 'none';
            if (searchInput) {
                searchInput.value = ''; // clear input field
                // Optionally also trigger input event to notify Livewire
                searchInput.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            }
        }
    });

    // Optional: Show suggestions again on input focus
    document.getElementById('searchInput').addEventListener('focus', function() {
        const suggestionBox = document.getElementById('search-suggestion-wrapper');
        if (suggestionBox && suggestionBox.innerHTML.trim() !== '') {
            suggestionBox.style.display = 'block';
        }
    });

    function confirmLogout() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, logout',
            cancelButtonText: 'No, cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logout-form').submit();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const openStockBtn = document.getElementById('openStockStatusBtn');

        openStockBtn?.addEventListener('click', function() {
            const stockStatusModal = new bootstrap.Modal(document.getElementById('stockStatusModal'));

            stockStatusModal.show();
        });
    });

    function goToStep(stepNumber) {
        // Hide all steps
        document.getElementById('step1').classList.add('d-none');
        document.getElementById('step2').classList.add('d-none');
        document.getElementById('step3').classList.add('d-none');

        // Show selected step
        document.getElementById('step' + stepNumber).classList.remove('d-none');

        // On step 3, set preview images
        if (stepNumber === 3) {
            const productImage = document.getElementById('productImagePreview').src;
            const userImage = document.getElementById('userImagePreview').src;
            // document.getElementById('reviewProductImage').src = productImage;
            //document.getElementById('reviewUserImage').src = userImage;
        }
    }

    var pusher = new Pusher("{{ config('broadcasting.connections.pusher.key') }}", {
        cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
        encrypted: true,
    });

    var branch_id = '{{ @$branch_id }}';
    var channel = pusher.subscribe('drawer-channel');

    channel.bind('DrawerOpened', function(data) {

        if (data.notify_to == branch_id) {
            Swal.fire({
                title: '📢 New Notification!',
                text: `${data.message} (Notify By: Admin`,
                icon: 'info',
                confirmButtonText: 'Okay'
            }).then((result) => {
                if (result.isConfirmed) {
                    // This code runs when "Okay" is clicked
                    //console.log('User clicked Okay');

                    // $.ajax({
                    //     url: '/popup/form/' + data.type + "?id=" + data.value + "&nfid=" + data
                    //         .nfid,
                    //     type: 'GET',
                    //     success: function(response) {

                    //         $('#modalContent').html(response);

                    //         $('#approveModal').modal('show');
                    //     },
                    //     error: function() {
                    //         alert('Failed to load form.');
                    //     }
                    // });
                }
            });
        }
    });

    $(document).ready(function() {
        // Event listener for product selection change
        // Event listener for product selection change
        $(document).on('change', '.product-select-sh', function() {
            const productId = $(this).val();
            const currentSelect = $(this);

            // Check if this product is already selected in another row
            if (productId) {
                const isDuplicate = $('.product-select-sh').not(this).toArray().some(select => select
                    .value ===
                    productId);
                if (isDuplicate) {
                    showAlert('error', 'LiquorHub!',
                        'This product is already selected. Please choose a different product');
                    currentSelect.val('');
                    container.empty();
                    return false;
                }
            }
        });

        $(document).on('change', '.product-select', function() {
            const productId = $(this).val();
            const from_store_id = branch_id;
            const to_store_id = $("#main_store_id").val();
            const itemRow = $(this).closest('.item-row-wh');
            const container = itemRow.find('.availability-container-wh');
            const indexMatch = $(this).attr('name').match(/\[(\d+)\]/);
            const itemIndex = indexMatch ? indexMatch[1] : 0;

            const currentSelect = $(this);

            // Check if this product is already selected in another row
            if (productId) {
                const isDuplicate = $('.product-select').not(this).toArray().some(select => select
                    .value ===
                    productId);
                if (isDuplicate) {
                    showAlert('error', 'LiquorHub!',
                        'This product is already selected. Please choose a different product');
                    currentSelect.val('');
                    container.empty();
                    return false;
                }
            }
            if (from_store_id == "") {
                showAlert('error', 'LiquorHub!', 'Please first select from store.!');
                return false;
            }



            if (productId) {
                // AJAX request to fetch product availability
                // $.ajax({
                //     url: "{{ url('/products/get-availability-branch') }}/" + productId +
                //         "?from=" + encodeURIComponent(from_store_id) +
                //         "&to=" + encodeURIComponent(to_store_id),
                //     type: "GET",
                //     dataType: "json",
                //     success: function(data) {
                //         //console.log(data);

                //         let html = `<div class="row">`;
                //         html += `
                //                 <div class="col-md-12">
                //                     <div class="form-check">
                //                         <label class="form-check-label" for="branch_">
                //                              (Available Stock: ${data.to_count})
                //                         </label>
                //                     </div>
                //                 </div>

                //             `;


                //         html += '</div>';
                //         container.html(html);
                //     },
                //     error: function() {
                //         container.html(
                //             '<span class="text-danger">Failed to load availability. Please try again.</span>'
                //         );
                //     }
                // });
            } else {
                container.empty(); // Clear container if no product is selected
            }
        });

        // Enable/disable quantity input based on checkbox selection
        $(document).on('change', '.branch-checkbox', function() {
            const quantityInput = $(this).closest('.col-md-6').next('.col-md-6').find(
                '.branch-quantity');
            if ($(this).is(':checked')) {
                quantityInput.prop('disabled', false);
            } else {
                quantityInput.prop('disabled', true).val(''); // Clear value when disabled
            }
        });

        // Validate branch quantities
        $(document).on('input', '.branch-quantity', function() {
            const itemRow = $(this).closest('.item-row');
            const totalRequestedQty = parseInt(itemRow.find('input[name$="[quantity]"]').val()) || 0;
            let totalBranchQty = 0;

            // Calculate the total quantity across all branches
            itemRow.find('.branch-quantity').each(function() {
                const branchQty = parseInt($(this).val()) || 0;
                totalBranchQty += branchQty;
            });

            // Check if the total branch quantity exceeds the requested quantity
            if (totalBranchQty > totalRequestedQty) {
                alert('The total quantity across branches cannot exceed the requested quantity.');
                $(this).val(''); // Clear the invalid input
            }
        });
    });
</script>

<script>
    $(document).ready(function() {

        $('#warehouseForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous errors
            $('.text-danger').remove();
            $('.is-invalid').removeClass('is-invalid');

            let form = $(this);
            let formData = new FormData(this);

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,

                success: function(response) {

                    showAlert('success', 'LiquorHub!', 'Stock submitted successfully!');
                    $('#warehouseStockRequest').modal(
                        'hide'); // Replace with your actual modal ID
                    $('.modal-backdrop.show').remove();
                    $('.availability-container-wh').html("");

                    form.trigger("reset");
                    // Reload the current page
                    // location.reload();

                    // reloadWithFullscreen(); // Sets flag and reloads the page 
                },

                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        //console.log(errors);
                        // Loop through the errors
                        Object.keys(errors).forEach(function(key) {
                            //console.log(key);
                            let nameAttr = key.replace(/\.(\d+)\./g, '[$1][')
                                .replace(/\./g, ']') + ']';
                            let selector = `[name="${nameAttr}"]`;

                            let field = $(selector);
                            if (field.length) {
                                field.addClass('is-invalid');
                                field.after(
                                    `<div class="text-danger">${errors[key]}</div>`
                                );
                            } else {
                                // Fallback for unmatched errors
                                $('#warehouseForm').prepend(
                                    `<div class="text-danger">${errors[key]}</div>`
                                );
                            }
                        });

                        // Show the modal again (if it's closed somehow)
                    }
                }
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        let cameraStream = {
            modal: document.getElementById('cameraModal'),
            video: document.getElementById('video'),
            canvas: document.getElementById('canvas'),
            error: document.getElementById('cameraError'),
            loading: document.getElementById('loadingIndicator'),
            captureProduct: document.getElementById('captureProduct'),
            captureCustomer: document.getElementById('captureCustomer'),
            productPreview: document.getElementById('productPreview'),
            customerPreview: document.getElementById('customerPreview'),
            stream: null,
            isCapturing: false,
            hasProductPhoto: false,
            hasCustomerPhoto: false,

            async init() {
                this.setupEventListeners();
            },

            setupEventListeners() {
                this.modal.addEventListener('shown.bs.modal', () => this.startCamera());
                this.modal.addEventListener('hidden.bs.modal', () => this.stopCamera());
                this.captureProduct.addEventListener('click', () => this.capture('product'));
                this.captureCustomer.addEventListener('click', () => this.capture('customer'));

                //Listen for Livewire events
                window.Livewire.on('photos-saved', () => {
                    // Reset states before closing modal
                    this.hasProductPhoto = false;
                    this.hasCustomerPhoto = false;
                    this.updateButtonStates();

                    // Clear previews
                    if (this.productPreview) this.productPreview.innerHTML = '';
                    if (this.customerPreview) this.customerPreview.innerHTML = '';

                    // Restart camera
                    this.stopCamera();
                    this.startCamera();

                    // Close modal after a short delay to ensure camera cleanup
                    setTimeout(() => {
                        $('#cameraModal').modal('hide'); // jQuery-based approach

                    }, 100);
                });

                window.Livewire.on('photo-reset', (type) => {
                    if (type === 'product') {
                        this.hasProductPhoto = false;
                    } else if (type === 'customer') {
                        this.hasCustomerPhoto = false;
                    }
                    this.updateButtonStates();
                });

                window.Livewire.on('photos-reset', () => {
                    this.hasProductPhoto = false;
                    this.hasCustomerPhoto = false;
                    this.updateButtonStates();
                });
            },

            async startCamera() {
                try {
                    this.error.classList.add('d-none');
                    this.loading.classList.remove('d-none');
                    this.disableAllButtons(true);

                    if (this.stream) {
                        this.stopCamera(); // Ensure any existing stream is stopped
                    }

                    this.stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            width: {
                                ideal: 1280
                            },
                            height: {
                                ideal: 720
                            },
                            facingMode: 'environment'
                        }
                    });

                    this.video.srcObject = this.stream;

                    await this.video.play();

                    // Only enable buttons if we have a valid stream
                    if (this.stream.active) {
                        this.disableAllButtons(false);
                    }
                    this.updateButtonStates();
                } catch (err) {

                    console.error('Camera access error:', err);
                    this.error.classList.remove('d-none');
                } finally {
                    this.loading.classList.add('d-none');
                }
            },

            stopCamera() {
                if (this.stream) {
                    this.stream.getTracks().forEach(track => {
                        track.stop();
                    });
                    this.video.srcObject = null;
                    this.stream = null;
                }
            },

            disableAllButtons(disabled) {
                this.captureProduct.disabled = disabled;
                this.captureCustomer.disabled = disabled;
            },

            updateButtonStates() {
                if (this.isCapturing) return;

                // Enable/disable buttons based on which photos are captured
                this.captureProduct.disabled = this.hasProductPhoto;
                this.captureCustomer.disabled = this.hasCustomerPhoto;

                // Update button text to show status
                this.captureProduct.innerHTML = this.hasProductPhoto ?
                    '✅ Product Photo Taken' :
                    '📷 Capture Product';
                this.captureCustomer.innerHTML = this.hasCustomerPhoto ?
                    '✅ Customer Photo Taken' :
                    '📷 Capture Customer';

                // Update button styles
                if (this.hasProductPhoto) {
                    this.captureProduct.classList.remove('btn-outline-success');
                    this.captureProduct.classList.add('btn-success');
                } else {
                    this.captureProduct.classList.add('btn-outline-success');
                    this.captureProduct.classList.remove('btn-success');
                }

                if (this.hasCustomerPhoto) {
                    this.captureCustomer.classList.remove('btn-outline-info');
                    this.captureCustomer.classList.add('btn-info');
                } else {
                    this.captureCustomer.classList.add('btn-outline-info');
                    this.captureCustomer.classList.remove('btn-info');
                }
            },

            async capture(target) {
                if (this.isCapturing) return;
                this.isCapturing = true;

                try {
                    // Temporarily disable both buttons during capture
                    this.disableAllButtons(true);

                    const context = this.canvas.getContext('2d');
                    context.drawImage(this.video, 0, 0, this.canvas.width, this.canvas.height);

                    // Show immediate preview
                    const previewContainer = target === 'product' ? this.productPreview : this
                        .customerPreview;
                    const tempPreview = document.createElement('div');
                    tempPreview.className = 'position-relative';
                    tempPreview.innerHTML = `
                        <img src="${this.canvas.toDataURL('image/jpeg')}" class="img-fluid" style="max-height: 240px">
                        <button class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2">×</button>
                    `;
                    previewContainer.innerHTML = '';
                    previewContainer.appendChild(tempPreview);

                    // Convert to blob and trigger file input
                    await new Promise(resolve => {
                        this.canvas.toBlob(blob => {
                            const file = new File([blob],
                                `${target}-${Date.now()}.jpg`, {
                                    type: 'image/jpeg'
                                });

                            const dt = new DataTransfer();
                            dt.items.add(file);

                            const input = document.getElementById(`${target}Input`);
                            input.files = dt.files;

                            // Create a new event that Livewire will detect
                            const event = new Event('change', {
                                bubbles: true,
                                cancelable: true,
                            });

                            // Dispatch the event to trigger Livewire's file upload
                            input.dispatchEvent(event);
                            resolve();
                        }, 'image/jpeg', 0.9);
                    });

                    // Update photo states
                    if (target === 'product') {
                        this.hasProductPhoto = true;
                    } else {
                        this.hasCustomerPhoto = true;
                    }

                } catch (error) {
                    console.error('Capture error:', error);
                } finally {
                    this.isCapturing = false;
                    // Update button states after capture
                    this.updateButtonStates();
                }
            }
        };

        cameraStream.init();
    });

    window.addEventListener('note-unavailable', event => {
        Swal.fire({
            title: 'Note Limit Reached',
            text: event.detail[0].message,
            icon: 'warning',
            confirmButtonText: 'OK'
        });
    });

    window.addEventListener('note-add', event => {

        Swal.fire({
            title: 'Add Cash',
            text: event.detail[0].message,
            icon: 'warning',
            confirmButtonText: 'OK'
        });
    });

    window.addEventListener('hold-saved', event => {
        location.reload();
        // reloadWithFullscreen();
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let shiftPending = false;
        const attachClickAlert = (btn) => {
            btn.addEventListener('click', function(e) {
                // Fetch shift status from Livewire on load
                Livewire.dispatch('checkShiftStatus');

            }, true); // ← use capture phase to block Livewire early
        };

        // Attach to existing buttons
        document.querySelectorAll('button').forEach(btn => {
            if (!btn.dataset.shiftCheckAttached) {
                attachClickAlert(btn);
                btn.dataset.shiftCheckAttached = true;
            }
        });

        // Re-attach after Livewire updates
        document.addEventListener("livewire:load", () => {
            Livewire.hook('message.processed', () => {
                document.querySelectorAll('button').forEach(btn => {
                    if (!btn.dataset.shiftCheckAttached) {
                        attachClickAlert(btn);
                        btn.dataset.shiftCheckAttached = true;
                    }
                });

                // Also refresh shift status
                Livewire.dispatch('checkShiftStatus');
            });
        });
    });

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('collapsed');
    }
</script>
