<div class="row ">
    @php
        $this->cashAmount = round_up_to_nearest_10($this->cashAmount) ?? 0;
    @endphp

    <div class="col-md-7">
        <div class="iq-sidebar-logo d-flex align-items-center justify-content-between">
            <!-- Left Side: Logo -->
            <a href="{{ route('items.cart') }}" class="header-logo d-flex align-items-center">
                <img src="{{ asset('assets/images/logo.png') }}" class="img-fluid rounded-normal light-logo"
                    alt="LiquorHub Logo" style="height: 1.2em; width: auto;">
                <h5 class="logo-title light-logo ml-3 mb-0 font-weight-bold text-dark">LiquorHub</h5>
            </a>

            <!-- Right Side: Sidebar Toggle Button -->
            <div class="iq-menu-bt-sidebar">

                <h6 class="text-right mb-0 ">{{ __('messages.store_location') }}<span
                        class="text-muted">{{ $this->branch_name }}</span></h6>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2">
                <div class="mb-3">
                    <form wire:submit.prevent="searchTerm" class="mb-3">
                        {{-- <div class="input-group">
                            <input type="text" wire:model.live.debounce.500ms="searchTerm"
                                placeholder="Enter Product Name" class="form-control">

                        </div> --}}
                        <div class="position-relative mb-3">

                            {{-- Input Field --}}
                            <input type="text" wire:model.live.debounce.500ms="searchTerm"
                                placeholder=" {{ __('messages.enter_product_name') }}" class="form-control text-input"
                                id="searchInput" autocomplete="off" />


                        </div>

                    </form>

                    {{-- On-screen QWERTY Keypad --}}
                    <div id="text-keypad"
                        class="keypad-container position-fixed top-50 start-50 translate-middle bg-white border p-3 rounded-3 shadow-lg d-none"
                        style="z-index: 1050; width: 480px; display: grid; grid-template-columns: repeat(10, 1fr); gap: 5px;">

                        <!-- Alphabet Keys -->
                        @foreach (str_split('QWERTYUIOPASDFGHJKLZXCVBNM') as $key)
                            <button type="button" class="btn btn-light btn-sm fw-bold text-uppercase"
                                onclick="textKeyInsert('{{ $key }}')">{{ $key }}</button>
                        @endforeach

                        <!-- Special Buttons -->
                        <button type="button" class="btn btn-secondary btn-sm fw-bold" style="grid-column: span 5;"
                            onclick="textKeyInsert(' ')">Space</button>
                        <button type="button" class="btn btn-warning btn-sm fw-bold" style="grid-column: span 2;"
                            onclick="textKeyBackspace()">←</button>
                        <button type="button" class="btn btn-danger btn-sm fw-bold" style="grid-column: span 3;"
                            onclick="textKeyClear()">C</button>
                    </div>

                </div>
            </div>

            <div class="col-md-3">
                <div class="mb-3">
                    <input type="number" wire:model.live="search" wire:keydown.enter="addToCartBarCode"
                        class="form-control" placeholder=" {{ __('messages.scan_barcode') }}" autofocus>

                    {{-- @if ($selectedProduct)
                        <div class="search-results">

                            <div class="list-group-item list-group-item-action">
                                <strong>{{ $selectedProduct->name }}</strong>
                                <small>Barcode: {{ $selectedProduct->barcode }}</small>
                                <small>Price: {{ $selectedProduct->sell_price }}</small>
                                <small>Stock: {{ $selectedProduct->quantity }}</small>
                            </div>
                        </div>
                    @endif --}}

                </div>

            </div>
            <div class="col-md-3">
                @if (auth()->user()->hasRole('cashier'))

                    <div class="form-group">
                        <select id="commissionUser" class="form-control" wire:model="selectedCommissionUser"
                            wire:change="calculateCommission">
                            <option value="">-- Select Commission Customer --</option>
                            @foreach ($commissionUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->first_name . ' ' . $user->last_name }}
                                </option>
                            @endforeach
                        </select>

                    </div>
                @endif
                @if (auth()->user()->hasRole('warehouse'))
                    <div class="form-group">
                        <select id="partyUser" class="form-control" wire:model="selectedPartyUser"
                            wire:change="calculateParty">
                            <option value="">-- {{ __('messages.select_party_customer') }} --</option>
                            @foreach ($partyUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->first_name . ' ' . $user->last_name }}
                                </option>
                            @endforeach
                        </select>

                    </div>
                @endif

            </div>
            @if ($selectedPartyUser || $selectedCommissionUser)
                <div class="col-md-1">
                    <button type="button" id="customer" class="btn btn-primary mr-2" data-toggle="modal"
                        data-target="#captureModal" data-toggle="tooltip" data-placement="top" title="Take Picture">
                        <i class="fa fa-camera"></i>
                    </button>

                </div>
            @endif
            @if (auth()->user()->hasRole('warehouse'))
                <div class="col-md-2">

                    <input type="text" wire:model.live="searchSalesReturn" wire:keydown.enter="addToSalesreturn"
                        class="form-control" placeholder="{{ __('messages.scan_invoice_no') }}" autofocus>

                </div>
            @endif

        </div>
        @if ($showSuggestions && count($searchResults) > 0)
            <div id="search-suggestion-wrapper" class="search-results col-md-6">

                <div class="list-group">
                    @foreach ($searchResults as $product)
                        <a href="#"
                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                            wire:click.prevent="addToCart({{ $product->id }})">
                            <span><strong>{{ $product->name }} ({{ $product->size }})</strong></span>
                            <span class="text-muted">{{ format_inr(@$product->sell_price) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="table-responsive" id="main_tb">

            <div class=" {{ count($itemCarts) > 5 ? ' cart-table-scroll scrollable' : '' }}">

                <table class="customtable table table-bordered" id="cartTable">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 45%;">{{ __('messages.product') }}</th>
                            <th class="text-center" style="width: 15%;">{{ __('messages.qty') }}</th>
                            <th style="width: 10%;">{{ __('messages.price') }}</th>
                            <th style="width: 10%;">{{ __('messages.total') }}</th>
                            <th style="width: 8%;">{{ __('messages.actions') }}</th>
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
                            <tr>
                                <td class="product-name" style="word-wrap: break-word; width: 45%;">
                                    <strong>{{ $item->product->name }}</strong><br>
                                    <small>{{ $item->product->description }}</small>
                                </td>
                                <td style="width: 15%;">
                                    @if (auth()->user()->hasRole('cashier'))
                                        <div class="d-flex align-items-center justify-content-between">
                                            <input type="number" min="1"
                                                class="form-control  mx-2 text-center"
                                                wire:model="quantities.{{ $item->id }}"
                                                wire:change="updateQty({{ $item->id }})" readonly />
                                            {{-- <input type="number" value="{{$this->quantities[$item->id]}}" wire:change="updateQty({{ $item->id }})" 
                                                class="form-control  mx-2 text-center"
                                                readonly /> --}}

                                        </div>
                                    @endif
                                    @if (auth()->user()->hasRole('warehouse'))
                                        <div class="d-flex align-items-center justify-content-between">
                                            <button class="btn btn-sm btn-danger custom-btn"
                                                wire:click="decrementQty({{ $item->id }})">−</button>
                                            <div class="relative">
                                                <input id="numberInput-{{ $item->id }}" type="number"
                                                    min="1"
                                                    class="custom-input form-control text-center number-input"
                                                    wire:model.lazy="quantities.{{ $item->id }}"
                                                    wire:keydown="updateQty({{ $item->id }})"
                                                    data-item-id="{{ $item->id }}" />

                                                <!-- Shared Numpad for this input -->
                                                <div id="numpad-{{ $item->id }}"
                                                    class="numpad-container d-none position-absolute bg-white border p-2 mt-1 rounded shadow"
                                                    style="width: 150px; z-index: 999;">
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9, 0] as $num)
                                                            <button type="button" class="btn btn-sm btn-light"
                                                                onclick="numpadInsert('{{ $item->id }}', '{{ $num }}')">{{ $num }}</button>
                                                        @endforeach
                                                        <button type="button" class="btn btn-sm btn-warning"
                                                            onclick="numpadBackspace('{{ $item->id }}')">←</button>
                                                        <button type="button"
                                                            class="btn btn-sm btn-danger custom-btn"
                                                            onclick="numpadClear('{{ $item->id }}')">C</button>
                                                    </div>
                                                </div>
                                            </div>


                                            {{-- <div id="numpad" class="numpad" style="display: none;"></div> --}}
                                            {{-- <input type="number" value="{{$this->quantities[$item->id]}}" wire:change="updateQty({{ $item->id }})" 
                                            class="form-control  mx-2 text-center"
                                            readonly /> --}}
                                            <button class="btn btn-sm btn-success custom-btn"
                                                wire:click="incrementQty({{ $item->id }}, {{ $finalAmount }})">+</button>
                                        </div>
                                    @endif
                                </td>
                                <td style="width: 10%;">

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
                                <td style="width: 10%;">

                                    {{ format_inr($item->net_amount) }}

                                </td>
                                <td style="width: 8%; " class="text-center">
                                    <button class="btn btn-danger" wire:click="removeItem({{ $item->id }})"
                                        title="Remove item">
                                        <i class="fas fa-times"></i>
                                    </button>

                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No products found in the cart.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <table class="customtable table table-bordered text-center mb-0">
                    <thead class="">
                        <tr>
                            <th>{{ __('messages.qty') }}</th>
                            <th>{{ __('messages.rounded_off') }}</th>
                            <th>{{ __('messages.total_payable') }}</th>

                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                {{ $this->cartCount }}
                                <input type="hidden" id="cartCount" value="{{ $this->cartCount }}">
                            </td>

                            <td>
                                {{ format_inr($this->cashAmount) }}
                                <input type="hidden" id="roundedTotal" value="{{ $this->cashAmount }}">
                            </td>
                            <td class="table-success fw-bold">
                                {{ format_inr($this->cashAmount) }}
                                <input type="hidden" id="totalPayable" value="{{ $this->cashAmount }}">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <div class="d-flex justify-content-center flex-wrap w-100">
                                    @if (!empty($this->selectedSalesReturn))
                                        <button wire:click="refundoggleBox"
                                            class="btn btn-sm btn-primary m-2 flex-fill text-nowrap ">
                                            <i class="fa fa-hand-holding-usd me-2 "></i> Refund
                                        </button>
                                        <button wire:click="srtoggleBox"
                                            class="btn btn-sm btn-primary m-2 flex-fill text-nowrap">
                                            <i class="fa fa-hand-holding-usd me-2"></i> Sales Return
                                        </button>
                                    @else
                                        @if (($selectedPartyUser && count($itemCarts) > 0) || count($itemCarts) > 0)
                                            <button wire:click="toggleBox"
                                                class="btn btn-sm btn-primary m-2 flex-fill text-nowrap">
                                                <i class="fa fa-money-bill-wave me-2"></i> {{ __('messages.cash') }}
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-primary m-2 flex-fill text-nowrap" disabled>
                                                <i class="fa fa-money-bill-wave me-2"></i> {{ __('messages.cash') }}
                                            </button>
                                        @endif
                                    @endif
                                    @if (empty($this->selectedSalesReturn))
                                        <button wire:click="voidSale"
                                            class="btn btn-sm btn-primary m-2 flex-fill text-nowrap">
                                            <i class="fa fa-ban me-2"></i> {{ __('messages.void_sales') }}
                                        </button>

                                        <button wire:click="holdSale"
                                            class="btn btn-sm btn-primary m-2 flex-fill text-nowrap">
                                            <i class="fa fa-pause-circle me-2"></i> {{ __('messages.hold') }}
                                        </button>

                                        <button wire:click="onlinePayment"
                                            class="btn btn-sm btn-primary m-2 flex-fill text-nowrap">
                                            <i class="fa fa-credit-card me-2"></i>
                                            {{ __('messages.online') }}
                                        </button>

                                        <button wire:click="cashupitoggleBox"
                                            class="btn btn-sm btn-primary m-2 flex-fill text-nowrap">
                                            <i class="fa fa-hand-holding-usd me-2"></i> Cash + UPI
                                        </button>
                                    @endif
                                </div>
                            </td>

                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- Bootstrap Modal -->
    <div class="modal fade" id="holdTransactionsModal" tabindex="-1" aria-labelledby="holdModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="holdModalLabel">Hold Transactions </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
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
                        <i class="bi bi-camera-video me-2"></i>Image Capture
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="modal-body px-4 py-4">
                    <!-- Step 1: Product -->
                    <div id="step1">
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
                                <i class="bi bi-camera me-1"></i>Capture User Image
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100" data-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>Close
                            </button>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- <form action="{{ route('shift-close.withdraw') }}" method="POST">
        @csrf --}}

    <div class="modal fade" id="cashout" tabindex="-1" aria-labelledby="cashout" aria-hidden="true"
        data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-scrollable modal-mg">
            <div class="modal-content shadow-sm rounded-4 border-0">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-semibold" id="cashout">
                        <i class="bi bi-camera-video me-2"></i>Withdraw Cash Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="modal-body p-6">
                    <div class="row">
                        <div class="col-md-12">
                            <form method="POST" action="{{ route('shift-close.withdraw') }}">
                                @csrf

                                <div class="card shadow-sm rounded-2xl p-4">

                                    <div class="table-responsive">
                                        <table class="customtable table table-bordered align-middle text-center">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>{{ __('messages.currency') }}</th>
                                                    <th>{{ __('messages.notes') }}</th>
                                                    <th>{{ __('messages.amount') }}</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($noteDenominations as $key => $denomination)
                                                    <tr>
                                                        <td>{{ $denomination }} X</td>
                                                        <td>
                                                            <div
                                                                class="d-flex justify-content-center align-items-center">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-danger custom-btn"
                                                                    onclick="updateNote('{{ $key }}_{{ $denomination }}', -1, {{ $denomination }})">
                                                                    <i class="fas fa-minus"></i>
                                                                </button>
                                                                <span
                                                                    id="display_{{ $key }}_{{ $denomination }}"
                                                                    class="mx-3">0</span>
                                                                <button type="button"
                                                                    class="btn btn-sm btn-success custom-btn"
                                                                    onclick="updateNote('{{ $key }}_{{ $denomination }}', 1, {{ $denomination }})">
                                                                    +
                                                                </button>
                                                                <input type="hidden"
                                                                    name="withcashNotes.{{ $key }}.{{ $denomination }}"
                                                                    id="withcashnotes_{{ $key }}_{{ $denomination }}"
                                                                    value="0">
                                                            </div>
                                                        </td>
                                                        <td id="withcashsum_{{ $key }}_{{ $denomination }}">
                                                            0</td>
                                                    </tr>
                                                @endforeach
                                                <tr class="fw-bold">
                                                    <td colspan="2" class="text-end">Total Withdraw</td>
                                                    <td id="totalNoteCashwith">0</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <input type="hidden" name="amount" id="withamountTotal"
                                        class="form-control mb-3" readonly required>

                                    <div class="mb-3">
                                        <label for="narration" class="form-label">Select Reason for Withdrawal</label>
                                        <select name="narration" id="narration" class="form-control" required>
                                            <option value="">-- Select Reason --</option>
                                            @foreach ($narrations as $narration)
                                                <option value="{{ $narration }}">{{ $narration }}</option>
                                            @endforeach
                                        </select>

                                    </div>

                                    <div class="text-right">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane me-1"></i> Click To Transfer
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
        <div class="modal-dialog modal-dialog-scrollable modal-mg">
            <div class="modal-content shadow-sm rounded-4 border-0">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-semibold" id="cashout">
                        <i class="bi bi-camera-video me-2"></i>Stock Request

                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
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
                                            <input type="hidden" name="store_id"
                                                value="{{ @$data->userInfo->branch_id }}">
                                        </div>


                                        <div id="product-items">
                                            <h5>Products</h5>
                                            <div class="item-row mb-3">

                                                <select name="items[0][product_id]" class="form-control d-inline w-50"
                                                    required>
                                                    <option value="">-- Select Product --</option>
                                                    @foreach ($allProducts as $product)
                                                        <option value="{{ $product->id }}">{{ $product->name }}
                                                            ({{ $product->sku }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('items')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                                <input type="number" name="items[0][quantity]"
                                                    class="form-control d-inline w-25 ms-2" placeholder="Qty"
                                                    min="1" required>

                                                <button type="button"
                                                    class="btn btn-danger btn-sm ms-2 remove-item">X</button>
                                            </div>
                                        </div>

                                        <button type="button" id="add-item" class="btn btn-secondary btn-sm mb-3">+
                                            Add
                                            Another Product</button>

                                        <div class="mb-3">
                                            <label for="notes" class="form-label">Notes</label>
                                            <textarea name="notes" id="notes" class="form-control"></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary">Submit Request</button>
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
        <div class="modal-dialog modal-dialog-scrollable modal-mg">
            <div class="modal-content shadow-sm rounded-4 border-0">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-semibold" id="cashout">
                        <i class="bi bi-camera-video me-2"></i>Stock Request

                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="modal-body p-6">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">

                                <div class="card-body">

                                    <form method="POST" action="{{ route('stock.warehouse') }}">
                                        @csrf

                                        <div class="item-row2 product_items2 mb-3">

                                            <select name="store_id" class="form-control d-inline w-50" required>
                                                <option value="">-- Select Store --</option>
                                                @foreach ($stores as $product)
                                                    @if ($product->id != 1)
                                                        <option value="{{ $product->id }}">{{ $product->name }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            @error('items')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror

                                        </div>
                                        {{-- filepath: d:\xampp\htdocs\pos\resources\views\stocks\create.blade.php --}}
                                        <div id="product-items1">
                                            <h5>Products</h5>
                                            <div class="item-row1 product_items1 mb-3">

                                                <select name="items[0][product_id]" class="form-control d-inline w-50"
                                                    required>
                                                    <option value="">-- Select Product --</option>
                                                    @foreach ($allProducts as $product)
                                                        <option value="{{ $product->id }}">{{ $product->name }}
                                                            ({{ $product->sku }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('items')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                                <input type="number" name="items[0][quantity]"
                                                    class="form-control d-inline w-25 ms-2" placeholder="Qty"
                                                    min="1" required>
                                                <button type="button"
                                                    class="btn btn-danger btn-sm ms-2 remove-item">X</button>
                                                <div class="availability-container mt-2 small text-muted">
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
                                        <button type="button" id="add-item-wh" class="btn btn-secondary btn-sm mb-3">+
                                            Add
                                            Another Product</button>

                                        <div class="mb-3">
                                            <label for="notes" class="form-label">Notes</label>
                                            <textarea name="notes" id="notes" class="form-control"></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary">Submit Request</button>
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
                    <div class="modal-header">
                        <h5 class="modal-title">Cash In Hand Details</h5>
                    </div>
                    <div class="modal-body">

                        <input type="hidden" name="amount" id="holdamountTotal" class="form-control mb-3"
                            placeholder="Enter opening amount" readonly>

                        <table class="customtable table">
                            <thead>
                                <tr>
                                    <th>Currency</th>
                                    <th>Notes</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($noteDenominations as $key => $denomination)
                                    <tr>
                                        <td>{{ $denomination }}</td>
                                        <td>
                                            <div class="input-group" style="max-width: 150px;">
                                                <button class="btn btn-sm btn-danger custom-btn btn-decrease"
                                                    type="button" data-denomination="{{ $denomination }}"><i
                                                        class="fa fa-minus"></i></button>
                                                <input type="text"
                                                    name="cashNotes.{{ $key }}.{{ $denomination }}"
                                                    class="form-control text-center note-input"
                                                    id="cashhandsum_{{ $denomination }}"
                                                    data-denomination="{{ $denomination }}" value="0" readonly>
                                                <button class="btn btn-sm btn-success custom-btn btn-increase"
                                                    type="button"
                                                    data-denomination="{{ $denomination }}">+</button>
                                            </div>
                                        </td>
                                        <td id="discashhandsum_{{ $denomination }}">0</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="2" class="text-end fw-bold">Total Cash</td>
                                    <td id="totalNoteCashHand">0</td>
                                </tr>
                            </tbody>
                        </table>
                        @error('amount')
                            <span class="text-red">{{ $message }}</span>
                        @enderror

                        <button type="button" class="btn btn-secondary btn-sm" id="openStockStatusBtn">
                            View Stock Status
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary btn-sm mr-2">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="stockStatusModal" tabindex="-1" aria-labelledby="stockStatusModalLabel"
        aria-hidden="true" data-backdrop="static" data-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="mt-4 mb-2">Product Opening Stock</h6>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"
                        wire:click="#"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <table class="customtable table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Opening Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productStock as $product)
                                <tr>
                                    <td>{{ $product->product->name }}</td>
                                    <td>
                                        <input type="number" name="productStocks[{{ $product->id }}]"
                                            class="form-control text-center" value="{{ $product->opening_stock }}"
                                            readonly>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" aria-label="Close"
                        wire:click="#">Close</button>
                </div>
            </div>
        </div>
    </div>


    <div class="col-md-5">
        {{-- @include('layouts.flash-message') --}}

        <div class="card" style="margin-bottom: 0px; border: aliceblue;">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-end align-items-center flex-wrap gap-2">

                            {{-- Livewire Notification --}}
                            <livewire:notification />

                            <livewire:fullscreen-toggle />
                            {{-- Refresh Button --}}
                            <button onclick="location.reload()" class="btn btn-primary ml-2" title="Refresh Page">
                                <i class="fas fa-sync-alt"></i>
                            </button>

                            {{-- Cashier Button --}}
                            @if (auth()->user()->hasRole('cashier'))
                                <button type="button" class="btn btn-primary ml-2" data-toggle="modal"
                                    data-target="#storeStockRequest" data-toggle="tooltip" data-placement="top"
                                    title="Store Stock Request">
                                    <i class="fas fa-store"></i>
                                </button>
                            @endif

                            {{-- Warehouse Button --}}
                            @if (auth()->user()->hasRole('warehouse'))
                                <button type="button" class="btn btn-primary ml-2" data-toggle="modal"
                                    data-target="#warehouseStockRequest" data-toggle="tooltip" data-placement="top"
                                    title="Warehouse Stock Request">
                                    <i class="fas fa-warehouse"></i>
                                </button>
                            @endif

                            {{-- Show when item cart is empty --}}
                            @if (count($itemCarts) == 0)
                                <button type="button" class="btn btn-primary ml-2" data-toggle="modal"
                                    data-target="#cashout" data-toggle="tooltip" data-placement="top"
                                    title="Cash Out">
                                    <i class="fas fa-cash-register"></i>
                                </button>

                                <button type="button" class="btn btn-primary ml-2" data-toggle="modal"
                                    data-target="#holdTransactionsModal" data-toggle="tooltip" data-placement="top"
                                    title="View Hold">
                                    <i class="fas fa-hand-paper"></i>
                                </button>
                            @endif
                            @if (auth()->user()->hasRole('warehouse'))
                                <button wire:click="printLastInvoice" class="btn btn-primary ml-2"
                                    data-toggle="tooltip" data-placement="top" title="Print the last invoice">
                                    <i class="fas fa-print"></i>
                                </button>
                                <livewire:order-modal />
                            @endif
                            @livewire('button-timer', ['endTime' => $this->shiftEndTime])

                            {{-- Close Shift --}}
                            {{-- <button type="button" class="btn btn-primary ml-2" data-toggle="modal"
                                data-target="#closeShiftModal" data-toggle="tooltip" data-placement="top"
                                title="Close Shift">
                                <i class="fas fa-door-closed"></i>
                            </button> --}}

                            {{-- dashboard.blade.php --}}
                            {{-- @livewire('shift-close-modal') --}}

                            @if (auth()->user()->hasRole('warehouse'))
                                <livewire:language-switcher />
                            @endif
                            <!-- Modal -->
                            {{-- Logout --}}
                            <button type="button" class="btn btn-outline-danger ml-2" data-toggle="tooltip"
                                data-placement="top" title="Logout" onclick="confirmLogout()">
                                <i class="fas fa-sign-out-alt"></i>
                            </button>

                            {{-- Logout Form --}}
                            <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                style="display: none;">
                                @csrf
                            </form>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ $this->headertitle }} {{ __('messages.summary') }}</h5>
            </div>
            <div class="card-body" style="padding:5px">
                @if ($showBox)

                    <div id="cash-payment">

                        <form onsubmit="event.preventDefault();" class="needs-validation" novalidate>


                            {{-- <h6 class="mb-3">💵 Enter Cash Denominations</h6> --}}
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <table class="customtable table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                @if (empty($this->selectedSalesReturn))
                                                    <th>{{ __('messages.amount') }}</th>
                                                    <th class="text-center">{{ __('messages.in') }}</th>
                                                @endif
                                                <th>{{ __('messages.currency') }}</th>
                                                <th class="text-center">{{ __('messages.out') }}</th>
                                                <th class="text-center">
                                                    {{ __('messages.amount') }}
                                                    <button wire:click="clearCashNotes" class="btn btn-danger btn-sm">
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
                                                            {{ format_inr($inValue * $denomination) }}</td>
                                                        <td class="text-center">
                                                            <div
                                                                class="d-flex justify-content-center align-items-center gap-2">
                                                                <button class="btn btn-sm btn-danger custom-btn"
                                                                    wire:click="decrementNote('{{ $key }}', '{{ $denomination }}', 'in')">
                                                                    -
                                                                </button>
                                                                <input type="number"
                                                                    class="form-control  text-center"
                                                                    value="{{ $inValue }}" readonly
                                                                    style="width: 60px;">
                                                                <button class="btn btn-sm btn-success custom-btn"
                                                                    wire:click="incrementNote('{{ $key }}', '{{ $denomination }}', 'in')">
                                                                    +
                                                                </button>
                                                            </div>
                                                        </td>
                                                    @endif

                                                    <td class="text-center">{{ format_inr($denomination) }}</td>

                                                    <td class="text-center">
                                                        <div
                                                            class="d-flex justify-content-center align-items-center gap-2">
                                                            <button class="btn btn-sm btn-danger custom-btn"
                                                                wire:click="decrementNote('{{ $key }}', '{{ $denomination }}', 'out')">
                                                                -
                                                            </button>
                                                            <input type="number" class="form-control  text-center"
                                                                value="{{ $outValue }}" readonly
                                                                style="width: 60px;">
                                                            <button class="btn btn-sm btn-success custom-btn"
                                                                wire:click="incrementNote('{{ $key }}', '{{ $denomination }}', 'out')">
                                                                +
                                                            </button>
                                                        </div>
                                                    </td>

                                                    <td class="text-center fw-bold">
                                                        {{ format_inr($outValue * $denomination) }}</td>
                                                </tr>
                                            @endforeach

                                            <tr class="table-secondary fw-bold">
                                                @if (empty($this->selectedSalesReturn))
                                                    <td class="text-center">{{ format_inr($totals['totalIn']) }}</td>
                                                    <td class="text-center">{{ $totals['totalInCount'] }}</td>
                                                @endif
                                                <td class="text-center">TOTAL</td>
                                                <td class="text-center">{{ $totals['totalOutCount'] }}</td>
                                                <td class="text-center">{{ format_inr($totals['totalOut']) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>


                                </div>

                            </div>
                            @if (empty($this->selectedSalesReturn))
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <input type="hidden" wire-model="paymentType">

                                        <label for="cash"
                                            class="form-label">{{ __('messages.cash_amount') }}</label>

                                        <input type="number" class="form-control" id="cash"
                                            value="{{ $this->cashAmount }}" placeholder=""
                                            oninput="calculateChange()" readonly>

                                    </div>

                                    <div class="col-md-4">
                                        <label for="tender"
                                            class="form-label">{{ __('messages.tendered_amount') }}</label>
                                        <input type="number" wire:model="cashPaTenderyAmt" class="form-control"
                                            id="tender" placeholder=""readonly>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="change"
                                            class="form-label">{{ __('messages.change_amount') }}</label>
                                        <input type="number" wire:model="cashPayChangeAmt" class="form-control"
                                            id="change" readonly>
                                    </div>
                                </div>
                            @endif
                            @if (!empty($this->selectedSalesReturn))
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>Refund Description</label>
                                        <textarea id="refundDesc" class="form-control" wire:model="refundDesc" placeholder="Enter refund description"></textarea>
                                    </div>
                                </div>
                            @endif
                            <hr class="">
                            <div class="border p-1 rounded bg-light">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>{{ __('messages.subtotal') }}</strong>
                                    <span>{{ format_inr($sub_total) }}</span>
                                </div>
                                @if (auth()->user()->hasRole('cashier'))
                                    @if ($commissionAmount > 0)
                                        <div class="d-flex justify-content-between mb-2">
                                            <strong>Commission Deduction</strong>
                                            <span>- {{ format_inr($commissionAmount) }}</span>
                                        </div>
                                    @endif
                                @endif
                                @if (auth()->user()->hasRole('warehouse'))
                                    {{-- @if ($partyAmount > 0) --}}
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>Commission Deduction</strong>
                                        <span>- {{ format_inr($partyAmount) }}</span>
                                    </div>
                                    {{-- @endif --}}
                                    {{-- @if ($partyAmount > 0) --}}
                                    <div class=" mb-2">
                                        <label class="-label" for="useCreditCheck">
                                            <input type="checkbox" wire:model="showCheckbox"
                                                wire:click="toggleCheck" />
                                            <strong>{{ __('messages.use_credit_to_pay') }}</strong>
                                        </label>
                                    </div>

                                    @if ($this->useCredit && $this->showCheckbox)
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="mb-0">
                                                <strong>{{ __('messages.credit') }}</strong>
                                            </label>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-primary fs-6 me-2">
                                                    {{ __('messages.available_credit') }}:
                                                    {{ number_format($this->partyUserDetails->credit_points ?? 0, 2) }}
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
                                    <strong>{{ __('messages.tendered_amount') }}</strong>
                                    <span>{{ format_inr($this->cashAmount) }}</span>
                                    <input type="text" id="total" value="{{ $this->cashAmount }}"
                                        class="d-none" />
                                </div>
                            </div>
                            <p id="result" class="mt-3 fw-bold text-success"></p>
                            @if (count($itemCarts) > 0)
                                <div class="mt-4">

                                    @if (!empty($this->selectedSalesReturn) && $this->cashAmount == $totals['totalOut'])
                                        <button id="paymentSubmit" class="btn btn-primary btn-sm mr-2 btn-block mt-4"
                                            wire:click="refund" wire:loading.attr="disabled">
                                            Refund
                                        </button>
                                    @else
                                        @if ($this->cashAmount == $totals['totalIn'] - $totals['totalOut'] && $errorInCredit == false)
                                            <button id="paymentSubmit"
                                                class="btn btn-primary btn-sm mr-2 btn-block mt-4"
                                                wire:click="checkout" wire:loading.attr="disabled">
                                                {{ __('messages.submit') }}
                                            </button>
                                        @endif
                                    @endif
                                    <div wire:loading class=" text-muted">Processing payment...</div>
                                </div>
                            @endif
                        </form>
                    </div>
                @elseif($shoeCashUpi)
                    <div id="cashupi-payment">
                        <form onsubmit="event.preventDefault(); " class="needs-validation" novalidate>
                            @php
                                $totalIn = 0;
                                $totalOut = 0;
                                $totalAmount = 0;
                            @endphp
                            @if ($this->showOnline == false)
                                {{-- <h6 class="mb-3">💵 Enter Cash Denominations</h6> --}}
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <table class="customtable table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>{{ __('messages.amount') }}</th>
                                                    @if (empty($this->selectedSalesReturn))
                                                        <th class="text-center">{{ __('messages.in') }}</th>
                                                    @endif
                                                    <th>{{ __('messages.currency') }}</th>
                                                    <th class="text-center">{{ __('messages.out') }}</th>
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
                                                            {{ format_inr($inValue * $denomination) }}</td>

                                                        @if (empty($this->selectedSalesReturn))
                                                            <td class="text-center">
                                                                <div
                                                                    class="d-flex justify-content-center align-items-center gap-2">
                                                                    <button class="btn btn-sm btn-danger custom-btn"
                                                                        wire:click="decrementCashUpiNote('{{ $key }}', '{{ $denomination }}', 'in')">
                                                                        -
                                                                    </button>
                                                                    <input type="number"
                                                                        class="form-control text-center"
                                                                        value="{{ $inValue }}" readonly
                                                                        style="width: 60px;">
                                                                    <button class="btn btn-sm btn-success custom-btn"
                                                                        wire:click="incrementCashUpiNote('{{ $key }}', '{{ $denomination }}', 'in')">
                                                                        +
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        @endif

                                                        <td class="text-center">{{ format_inr($denomination) }}</td>

                                                        <td class="text-center">
                                                            <div
                                                                class="d-flex justify-content-center align-items-center gap-2">
                                                                <button class="btn btn-sm btn-danger custom-btn"
                                                                    wire:click="decrementCashUpiNote('{{ $key }}', '{{ $denomination }}', 'out')">
                                                                    -
                                                                </button>
                                                                <input type="number" class="form-control text-center"
                                                                    value="{{ $outValue }}" readonly
                                                                    style="width: 60px;">
                                                                <button class="btn btn-sm btn-success custom-btn"
                                                                    wire:click="incrementCashUpiNote('{{ $key }}', '{{ $denomination }}', 'out')">
                                                                    +
                                                                </button>
                                                            </div>
                                                        </td>

                                                        <td class="text-center fw-bold">{{ format_inr($rowAmount) }}
                                                        </td>
                                                    </tr>
                                                @endforeach

                                                <tr class="table-secondary fw-bold">
                                                    <td class="text-center">{{ format_inr($totalIn) }}</td>
                                                    @if (empty($this->selectedSalesReturn))
                                                        <td class="text-center">{{ $totalIn }}</td>
                                                    @endif
                                                    <td class="text-center">TOTAL</td>
                                                    <td class="text-center">{{ $totalOut }}</td>
                                                    <td class="text-center">{{ format_inr($totalAmount) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
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
                                        <input type="number" id="cashAmount" step="0.01"
                                            wire:model.live.debounce.500ms="cash" class="form-control" min="0"
                                            max="{{ $this->cashAmount }}" readonly>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="cash" class="form-label">UPI Amount</label>

                                        <input type="number" id="onlineAmount" step="0.01"
                                            wire:model.live.debounce.500ms="upi" class="form-control" min="0"
                                            max="{{ $this->cashAmount }}">
                                    </div>
                                </div>
                                <hr class="">
                            @endif



                            <div class="border p-3 rounded bg-light">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>Subtotal</strong>
                                    <span>{{ format_inr($sub_total) }}</span>
                                </div>

                                @if ($commissionAmount > 0)
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>Commission Deduction</strong>
                                        <span>- {{ format_inr($commissionAmount) }}</span>
                                    </div>
                                @endif
                                @if ($partyAmount > 0)
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>Commission Deduction</strong>
                                        <span>- {{ format_inr($partyAmount) }}</span>
                                    </div>
                                @endif
                                {{-- @if ($partyAmount > 0) --}}
                                {{-- <div class="d-flex justify-content-between mb-2">
                                        <strong>Credit</strong>
                                        <input type="number" width="10%"
                                            wire:model.live="creditPay" wire:input="creditPayChanged"
                                            class="form-control" style="width: 80px;" />

                                    </div> --}}
                                @if (auth()->user()->hasRole('warehouse'))
                                    <div class="mb-2">
                                        <label for="useCreditCheck">
                                            <input type="checkbox" wire:click="toggleCheck" />

                                            <strong>{{ __('messages.use_credit_to_pay') }}</strong>
                                        </label>
                                    </div>

                                    @if ($useCredit)
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="mb-0">
                                                <strong>{{ __('messages.credit') }}</strong>
                                            </label>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-primary fs-6 me-2">
                                                    {{ __('messages.available_credit') }}:
                                                    {{ number_format($this->partyUserDetails->credit_points, 2) }}
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
                                    <strong>Total Payable</strong>
                                    <span>{{ format_inr($this->cashAmount) }}</span>
                                    <input type="text" id="total" value="{{ $this->cashAmount }}"
                                        class="d-none" />
                                </div>
                            </div>
                            <p id="result" class="mt-3 fw-bold text-success"></p>
                            <div class="mt-4">
                                @if ($this->showOnline == true)
                                    <button id="paymentSubmit" class="btn btn-primary btn-sm mr-2 btn-block mt-4"
                                        wire:click="onlinePaymentCheckout" wire:loading.attr="disabled">
                                        {{ __('messages.submit') }}
                                    </button>
                                @else
                                    @if ($this->cashAmount == $this->cash + $this->upi && $this->upi >= 0)
                                        <button id="paymentSubmit" class="btn btn-primary btn-sm mr-2 btn-block mt-4"
                                            wire:click="checkout" wire:loading.attr="disabled">
                                            {{ __('messages.submit') }}
                                        </button>
                                    @endif
                                @endif

                                <div wire:loading class=" text-muted">Processing payment...</div>
                            </div>

                        </form>
                    </div>
                @elseif ($showRefund)
                    <div id="refund-data">
                        @livewire('refund-form', ['invoiceId' => $this->searchSalesReturn])

                    </div>
                @else
                    <div class="d-flex justify-content-between">
                        <strong> {{ __('messages.no_data_found') }}</strong>
                    </div>
                @endif


            </div>
        </div>

    </div>

    {{-- @if ($invoiceData)
        <div class="print-only">
            @include('livewire.printinvoice')
        </div>
    @endif --}}


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

                    <div class="row g-2">
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

</div>

<script>
    window.addEventListener('triggerPrint', event => {
        // Clear previous iframe if it exists
        const iframeContainer = document.getElementById('iframe-container');
        iframeContainer.innerHTML = '';

        // Create a new iframe element
        const iframe = document.createElement('iframe');
        iframe.src = event.detail[0].pdfPath;
        iframe.width = '100%';
        iframe.height = '100%';
        iframe.style.border = 'none';
        iframe.style.display = 'none'; // Hide the iframe

        // Append the iframe to the container
        iframeContainer.appendChild(iframe);

        // When iframe is loaded, trigger print
        iframe.onload = function() {
            iframe.contentWindow.focus(); // Ensure iframe is focused
            iframe.contentWindow.print();
            iframe.contentWindow.onafterprint = function() {
                location.reload(); // Reload the page after printing
            };
        };

    });


    // window.addEventListener('triggerPrint', event => {
    //     const iframe = document.createElement('iframe');
    //     iframe.style.display = 'none';
    //     iframe.src = event.detail[0].pdfPath;
    //     document.body.appendChild(iframe);
    //     iframe.onload = () => {
    //         iframe.contentWindow.print();
    //         document.body.removeChild(iframe);
    //     };
    // });
    //window.addEventListener('triggerPrint', event => {
    //const pdfPath = event.detail[0].pdfPath;
    //window.location.href = pdfPath; // opens in same window



    window.addEventListener('DOMContentLoaded', function() {
        $('#storeStockRequest').modal('hide');
    });

    window.addEventListener('DOMContentLoaded', function() {
        $('#warehouseStockRequest').modal('hide');
    });
</script>

<!-- Script to show modal -->

<!-- Script to show modal -->
{{-- @if ($showModal)
 
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var myModal = new bootstrap.Modal(document.getElementById('cashInHand'));
                myModal.show();
            }, 3000); // delay by 300 milliseconds
        });
    </script>
@endif --}}
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
        Livewire.on('closingStocksOpenModal', () => {
            //   var myModal = new bootstrap.Modal(document.getElementById('myModal'));
            const closingStocksModal = new bootstrap.Modal(document.getElementById('closingStocksModal'), {
                backdrop: 'static',
                keyboard: false
            });
            closingStocksModal.show();
        });
    });
</script>

<script>
    window.addEventListener('show-cash-modal', () => {
        let modal = new bootstrap.Modal(document.getElementById('cashModal'));
        modal.show();
    });
</script>

<script>
    window.addEventListener('user-selection-updated', event => {
        const userId = event.detail.userId;
        yourJsFunction(userId);
    });

    function yourJsFunction(userId) {

        console.log("JS function called with user ID:", userId);
        // Your custom logic here
    }

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

    // function calculateCash() {
    //     const notes2000 = parseInt(document.getElementById('notes_2000').value) || 0;
    //     const notes500 = parseInt(document.getElementById('notes_500').value) || 0;

    //     const total = (notes2000 * 2000) + (notes500 * 500);

    //     if (total === 4000) {
    //         document.getElementById('result').innerText = `✅ Total is ${total}`;
    //     } else {
    //         document.getElementById('result').innerText = `❌ Total is ${total}, which is not 4000`;
    //     }
    // }

    // function calculateCashBreakdown() {
    //     const denominations = [{
    //             id: 'notes_10',
    //             value: 10,
    //             sumId: 'sum_10'
    //         },
    //         {
    //             id: 'notes_20',
    //             value: 20,
    //             sumId: 'sum_20'
    //         },
    //         {
    //             id: 'notes_50',
    //             value: 50,
    //             sumId: 'sum_50'
    //         },
    //         {
    //             id: 'notes_100',
    //             value: 100,
    //             sumId: 'sum_100'
    //         },
    //         {
    //             id: 'notes_500',
    //             value: 500,
    //             sumId: 'sum_500'
    //         }
    //     ];


    //     let total = 0;
    //     let notesum = 0;
    //     const cash = document.getElementById('cash').value;
    //     const change = document.getElementById('change').value;
    //     denominations.forEach(note => {
    //         //console.log(document.getElementById(note.id).value);
    //         const count = parseInt(document.getElementById(note.id).value) || 0;
    //         // console.log(count);

    //         const subtotal = count * note.value;
    //         total += subtotal;
    //         //console.log(subtotal);

    //         document.getElementById(note.sumId).textContent = `${subtotal.toLocaleString()}`;
    //     });

    //     document.getElementById('totalNoteCash').textContent = ` ${total.toLocaleString()}`;

    //     total -= change;


    //     if (cash == total) {
    //         document.getElementById('paymentSubmit').style.display = 'block';

    //         // document.getElementById('result').textContent = `Total Cash: ${total.toLocaleString()}`;
    //     }
    // }

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

        console.log("Cash Amount: ", actualTotal);
        console.log("Actual Total: ", parseFloat(cashAmount) + parseFloat(onlineAmount));

        if (actualTotal == parseFloat(cashAmount) + parseFloat(onlineAmount)) {
            document.getElementById('paymentSubmit').style.display = 'block';
            // document.getElementById('result').textContent = `Total Cash: ${total.toLocaleString()}`;
        }
    }

    // Run on load
    // document.addEventListener("DOMContentLoaded", calculateCashBreakdown);
    // var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-toggle="tooltip"]'))
    // tooltipTriggerList.map(function(tooltipTriggerEl) {
    //     return new bootstrap.Tooltip(tooltipTriggerEl)
    // });
</script>
<script>
    // async function startCamera(type) {
    //     const video = document.getElementById(type === 'product' ? 'video1' : 'video2');
    //     try {
    //         const stream = await navigator.mediaDevices.getUserMedia({ video: true });
    //         video.srcObject = stream;
    //     } catch (error) {
    //         console.error('Camera permission denied or not available.', error);
    //         alert('Please allow camera access to capture an image.');
    //     }
    // }

    // // Start the camera when the captureModal is opened
    // document.addEventListener('DOMContentLoaded', function () {
    //     $('#captureModal').on('shown.bs.modal', function () {
    //         startCamera('product'); // Start camera for 'product' type
    //     });
    // });
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
                            document.getElementById('step1').classList.add('d-none');
                            document.getElementById('step2').classList.remove('d-none');
                        } else if (type === 'user') {
                            //$('#captureModal').modal('hide');
                            document.getElementById('captureModal').style.display = 'none';

                            $('.modal-backdrop.show').remove();
                            //bootstrap.Modal.getInstance(document.getElementById('captureModal')).hide();
                            //document.getElementById('submitDiv').classList.remove('d-none');

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
            console.log("Cash Amount: ", cash);
            $("#onlineAmount").val(remaining);
        } else if (source === 'online') {
            console.log("Online Amount: ", online);

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
                    console.error("Camera access denied:", err);
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

    // document.addEventListener('DOMContentLoaded', function() {
    //     const inputs = document.querySelectorAll('.note-input');
    //     const totalCashDisplay = document.getElementById('totalNoteCash');
    //     const amountInput = document.getElementById('amountTotal');

    //     function updateTotals() {
    //         let total = 0;
    //         inputs.forEach(input => {
    //             const denom = parseInt(input.dataset.denomination);
    //             const qty = parseInt(input.value) || 0;
    //             const sum = denom * qty;
    //             document.getElementById(`cashsum_${denom}`).innerText = `${sum}`;
    //             total += sum;
    //         });

    //         totalCashDisplay.innerText = `${total}`;
    //         amountInput.value = total;
    //     }

    //     inputs.forEach(input => {
    //         input.addEventListener('input', updateTotals);
    //     });

    //     // Initial calculation
    //     updateTotals();
    // });
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

    // input.addEventListener('click', (e) => {
    //     const rect = input.getBoundingClientRect();
    //     createNumpad();
    //     numpad.style.top = `${rect.bottom + window.scrollY + 5}px`;
    //     numpad.style.left = `${rect.left + window.scrollX}px`;
    //     numpad.style.display = 'grid';
    // });

    // Optional: Close numpad if clicked outside
    // document.addEventListener('click', (e) => {
    //     if (!numpad.contains(e.target) && e.target !== input) {
    //         numpad.style.display = 'none';
    //     }
    // });
</script>
<script>
    const encoded = "{{ $this->availableNotes }}";
    // Decode HTML entities
    let decoded = new DOMParser().parseFromString(encoded, "text/html").documentElement.textContent;

    // Now parse JSON
    let availableNotes = JSON.parse(decoded);

    function updateNote(id, delta, denomination) {
        const input = document.getElementById('withcashnotes_' + id);
        let current = parseInt(input.value || 0);

        // Always treat denomination key as string
        const key = denomination.toString();
        const maxNotes = availableNotes[key];

        // If delta > 0 (trying to add note) and either:
        // 1. Note is not listed (undefined), or
        // 2. Max notes is 0 or less, or
        // 3. Already reached the limit
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
    // window.addEventListener('cart-voided', (event) => {
    //         Swal.fire({
    //             title: 'LiquorHub!',
    //             text: event.detail[0].message,  // Use the message passed from Livewire through the event
    //             icon: 'success',
    //             confirmButtonText: 'OK'
    //         });
    //     // Reset inputs if needed or perform any additional actions here
    // });
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
            if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                location.reload(); // reload after OK click or auto close
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
</script>
<script>
    // window.addEventListener('show-numpad-modal', () => {
    //     $('#numpadModal').modal('show');

    // });

    // window.addEventListener('hide-numpad-modal', () => {
    //     $('#numpadModal').modal('hide');

    // });
    window.addEventListener('close-hold-modal', function() {
        document.getElementById('holdTransactionsModal').style.display = 'none';
        $('.modal-backdrop.show').remove();
    });
    window.addEventListener('product-added', () => {
        // optional: play sound or flash success
        console.log('Product added to cart!');
    });
</script>
<script>
    //     function calculateDifference(expectedAmount) {
    //     const closingCashInput = document.getElementById('closingCash');
    //     const diffCashInput = document.getElementById('diffCash');
    //     const closingCashValue = closingCashInput.value.trim();

    //     // Validate input
    //     if (closingCashValue === '') {
    //         Swal.fire({
    //             icon: 'warning',
    //             title: 'Missing Input',
    //             text: 'Please enter the closing cash amount.',
    //         });
    //         closingCashInput.focus();
    //         diffCashInput.value = '';
    //         return;
    //     }

    //     const closingCash = parseFloat(closingCashValue);

    //     if (isNaN(closingCash)) {
    //         Swal.fire({
    //             icon: 'error',
    //             title: 'Invalid Input',
    //             text: 'Closing cash must be a valid number.',
    //         });
    //         closingCashInput.focus();
    //         diffCashInput.value = '';
    //         return;
    //     }

    //     if (closingCash < 0) {
    //         Swal.fire({
    //             icon: 'error',
    //             title: 'Invalid Amount',
    //             text: 'Closing cash cannot be negative.',
    //         });
    //         closingCashInput.focus();
    //         diffCashInput.value = '';
    //         return;
    //     }

    //     // Calculate the difference
    //     const diffCash = closingCash - expectedAmount;

    //     // Update the diffCash input with the calculated value
    //     diffCashInput.value = diffCash.toFixed(2);
    // }


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
</script>

<script>
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
    });


    let itemIndex1 = 1;
    document.getElementById('add-item-wh').addEventListener('click', function() {
        const row = document.querySelector('.item-row1').cloneNode(true);
        row.querySelectorAll('select, input').forEach(el => {
            const name = el.getAttribute('name');
            const updatedName = name.replace(/\[\d+\]/, `[${itemIndex1}]`);
            el.setAttribute('name', updatedName);
            if (el.tagName === 'INPUT') el.value = '';
        });
        document.getElementById('product-items1').appendChild(row);
        itemIndex1++;
    });

    // document.addEventListener('DOMContentLoaded', function() {
    //     const inputs = document.querySelectorAll('.note-input');
    //     const totalCashDisplay = document.getElementById('totalNoteCashNew');
    //     const amountInput = document.getElementById('amountTotal');

    //     function updateTotals() {
    //         let total = 0;
    //         inputs.forEach(input => {
    //             const denom = parseInt(input.dataset.denomination);
    //             const qty = parseInt(input.value) || 0;
    //             const sum = denom * qty;
    //             document.getElementById(`cashsum_${denom}`).innerText = `${sum}`;
    //             total += sum;
    //         });
    //         totalCashDisplay.innerText = `${total}`;
    //         amountInput.value = total;
    //     }

    //     inputs.forEach(input => {
    //         input.addEventListener('input', updateTotals);
    //     });

    //     // Initial calculation
    //     updateTotals();
    // });
</script>
<script>
    // document.addEventListener('DOMContentLoaded', () => {
    //     const inputs = document.querySelectorAll('.number-input');

    //     inputs.forEach(input => {
    //         input.addEventListener('focus', () => {
    //             const itemId = input.dataset.itemId;
    //             const numpad = document.getElementById('numpad-' + itemId);
    //             closeAllNumpads();
    //             positionNumpadBelow(input, numpad);
    //             numpad.classList.remove('d-none');
    //         });
    //     });

    //     document.addEventListener('click', (e) => {
    //         if (!e.target.closest('.numpad-container') && !e.target.classList.contains('number-input')) {
    //             closeAllNumpads();
    //         }
    //     });
    // });

    // function positionNumpadBelow(input, numpad) {
    //     const rect = input.getBoundingClientRect();
    //     numpad.style.top = (window.scrollY + rect.bottom + 4) + 'px';
    //     numpad.style.left = (window.scrollX + rect.left) + 'px';
    // }

    // function closeAllNumpads() {
    //     document.querySelectorAll('.numpad-container').forEach(n => n.classList.add('d-none'));
    // }

    // function numpadInsert(itemId, num) {
    //     const input = document.getElementById('numberInput-' + itemId);
    //     input.value += num;
    //     dispatchLivewireEvents(input);
    // }

    // function numpadBackspace(itemId) {
    //     const input = document.getElementById('numberInput-' + itemId);
    //     input.value = input.value.slice(0, -1);
    //     dispatchLivewireEvents(input);
    // }

    // function numpadClear(itemId) {
    //     const input = document.getElementById('numberInput-' + itemId);
    //     input.value = '1'; // Set to 1 instead of empty
    //     dispatchLivewireEvents(input);
    // }


    // function dispatchLivewireEvents(input) {
    //     input.dispatchEvent(new Event('input'));
    //     input.dispatchEvent(new Event('change'));
    // }
</script>
{{-- <script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('searchInput');
        const keypad = document.getElementById('text-keypad');

        // Show keypad on focus
        input.addEventListener('focus', () => {
            positionKeypadBelow(input, keypad);
            keypad.classList.remove('d-none');
        });

        // Hide keypad when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.keypad-container') && e.target !== input) {
                keypad.classList.add('d-none');
            }
        });
    });

    function positionKeypadBelow(input, keypad) {
        const rect = input.getBoundingClientRect();
        keypad.style.top = (window.scrollY + rect.bottom + 4) + 'px';
        keypad.style.left = (window.scrollX + rect.left) + 'px';
    }

    function textKeyInsert(char) {
        const input = document.getElementById('searchInput');
        input.value += char;
        dispatchLivewireEvents(input);
    }

    function textKeyBackspace() {
        const input = document.getElementById('searchInput');
        input.value = input.value.slice(0, -1);
        dispatchLivewireEvents(input);
    }

    function textKeyClear() {
        const input = document.getElementById('searchInput');
        input.value = '';
        dispatchLivewireEvents(input);
    }

    function dispatchLivewireEvents(input) {
        input.dispatchEvent(new Event('input'));
        input.dispatchEvent(new Event('change'));
    } --}}
</script>
<script>
    window.addEventListener('show-order-modal', event => {
        var myModal = new bootstrap.Modal(document.getElementById('orderModal'), {
            keyboard: false
        });
        myModal.show();
    });
</script>
<script>
    document.addEventListener('click', function(event) {
        const searchContainer = document.getElementById('search-container');
        const suggestionBox = document.getElementById('search-suggestion-wrapper');
        const searchInput = document.getElementById('searchInput');

        console.log(searchContainer);
        console.log(suggestionBox);
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
</script>
