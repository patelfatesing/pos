<div class="row ">

    <div class="col-md-7 no-print">
        <div class="iq-sidebar-logo d-flex align-items-center justify-content-between">
            <!-- Left Side: Logo -->
            <a href="{{ route('items.cart') }}" class="header-logo d-flex align-items-center">
                <img src="{{ asset('assets/images/logo.png')}}" class="img-fluid rounded-normal light-logo" alt="LiquorHub Logo" style="height: 1.2em; width: auto;">
                <h5 class="logo-title light-logo ml-3 mb-0 font-weight-bold text-dark">LiquorHub</h5>
            </a>
            
            <!-- Right Side: Sidebar Toggle Button -->
            <div class="iq-menu-bt-sidebar">
                <h4 class="text-right mb-0 font-weight-bold">Store Location: <span class="text-muted">{{ $this->branch_name }}</span></h4>
            </div>
        </div>
        
        
        
        

        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <form wire:submit.prevent="searchTerm" class="mb-3">
                        <div class="input-group">
                            <input type="text" wire:model.live.debounce.500ms="searchTerm"
                                placeholder="Enter Product Name" class="form-control">

                        </div>
                    </form>
                    @if ($showSuggestions && count($searchResults) > 0)
                        <div class="search-results">

                            <div class="list-group mb-3 ">
                                @foreach ($searchResults as $product)
                                    <a href="#" class="list-group-item list-group-item-action"
                                        wire:click.prevent="addToCart({{ $product->id }})">
                                        <strong>{{ $product->name }} ({{ $product->size }})</strong><br>
                                        <small>₹{{ number_format(@$product->sell_price, 2) }}</small>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            <div class="col-md-3">
                <div class="mb-3">
                    <input type="text" class="form-control" id="searchBarcodeTerm" placeholder="Scan Barcode">
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
                            <option value="">-- Select a Party Customer --</option>
                            @foreach ($partyUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->first_name . ' ' . $user->last_name }}
                                    ({{ $user->credit_points }}pt)
                                </option>
                            @endforeach
                        </select>

                    </div>
                @endif

            </div>
            @if ($selectedPartyUser || $selectedCommissionUser)
                <div class="col-md-3">
                    <button type="button" id="customer" class="btn btn-primary btn-sm mr-2 "
                    data-toggle="modal" data-target="#captureModal">
                    Take picture
                    </button>
                </div>
            @endif
        </div>
        <div class="table-responsive mb-2" id="main_tb">
            <div class="cart-table-scroll {{ count($itemCarts) > 5 ? 'scrollable' : '' }}">

                <table class="table table-bordered" id="cartTable">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 40%;">Product</th>
                            <th style="width: 20%;">Quantity</th>
                            <th style="width: 10%;">Price</th>
                            <th style="width: 10%;">Total</th>
                            <th style="width: 10%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($itemCarts as $item)
                            <tr>
                                <td class="product-name" style="word-wrap: break-word; width: 40%;">
                                    <strong>{{ $item->product->name }}</strong><br>
                                    <small>{{ $item->product->description }}</small>
                                </td>
                                <td style="width: 20%;">
                                    @if (auth()->user()->hasRole('cashier'))
                                        <div class="d-flex align-items-center justify-content-between">
                                            <input type="number" min="1"
                                                class="form-control form-control-sm mx-2 text-center"
                                                wire:model.lazy="quantities.{{ $item->id }}"
                                                wire:change="updateQty({{ $item->id }})" readonly />

                                        </div>
                                    @endif
                                    @if (auth()->user()->hasRole('warehouse'))
                    
                                        <div class="d-flex align-items-center justify-content-between">
                                            <button class="btn btn-sm btn-outline-success"
                                                wire:click="decrementQty({{ $item->id }})">−</button>
                                            <input id="numberInput" type="number" min="1"
                                                class="form-control form-control-sm mx-2 text-center"
                                                wire:model.lazy="quantities.{{ $item->id }}"
                                                wire:change="updateQty({{ $item->id }})" />
                                            <div id="numpad" class="numpad" style="display: none;"></div>

                                            <button class="btn btn-sm btn-outline-warning"
                                                wire:click="incrementQty({{ $item->id }})">+</button>
                                        </div>
                                    @endif
                                </td>
                                <td style="width: 10%;">
                                    @if (@$item->product->discount_price && $this->commissionAmount > 0)
                                        <span class="text-danger">
                                            ₹{{ number_format(@$item->product->sell_price, 2) }}
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <s>₹{{ number_format(@$item->product->discount_price, 2) }}</s>
                                        </small>
                                    @else
                                        ₹{{ number_format(@$item->product->sell_price, 2) }}
                                    @endif
                                </td>
                                <td style="width: 10%;">
                                    @php
                                        $total = @$item->product->sell_price * $item->quantity;
                                        $commission = $commissionAmount ?? 0;
                                        $party = $partyAmount ?? 0;
                                        $finalAmount = $total - $commission - $party;
                                    @endphp
                                    ₹{{ number_format($finalAmount, 2) }}

                                </td>
                                <td style="width: 10%;">
                                    <button class="btn btn-sm btn-danger"
                                        wire:click="removeItem({{ $item->id }})">Remove</button>
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

        <div class="card shadow-sm mb-3">
            <div class="card-body p-0">
                <table class="table table-bordered text-center mb-0">
                    <thead class="">
                        <tr>
                            <th>Quantity</th>
                            <th>MRP</th>
                            <th>Rounded Off</th>
                            <th>Total Payable</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                {{ number_format($this->cartCount, 0) }}
                                <input type="hidden" id="cartCount" value="{{ $this->cartCount }}">
                            </td>
                            <td>
                                ₹{{ number_format($this->total, 2) }}
                                <input type="hidden" id="mrp" value="{{ $this->total }}">
                            </td>
                            <td>
                                ₹{{ number_format(round($this->total), 2) }}
                                <input type="hidden" id="roundedTotal" value="{{ round($this->total) }}">
                            </td>
                            <td class="table-success fw-bold">
                                ₹{{ number_format($this->total, 2) }}
                                <input type="hidden" id="totalPayable" value="{{ $this->total }}">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <button wire:click="toggleBox" class="btn btn-sm btn-primary w-100 shadow-sm">
                                    <i class="bi bi-file-earmark-spreadsheet me-2"></i> Cash
                                </button>

                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary w-100 shadow-sm">
                                    <i class="bi bi-file-earmark-spreadsheet me-2"></i> Online
                                </button>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary w-100 shadow-sm">
                                    <i class="bi bi-file-earmark-spreadsheet me-2"></i> Void Sales 
                                </button>
                            </td>
                            <td>

                                <button wire:click="cashupitoggleBox" class="btn btn-sm btn-primary w-100 shadow-sm">
                                    <i class="bi bi-file-earmark-spreadsheet me-2"></i> Cash + UPI
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>


    </div>


    <!-- Single Modal -->
    <div class="modal fade no-print " id="captureModal" tabindex="-1" aria-labelledby="captureModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
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
                            <img src="{{ asset('assets/images/cold-bottle-beer-with-drops-isolated-white-background.jpg')}}" 
                                 alt="Sample Product" 
                                 class="rounded-3 shadow-sm" 
                                 width="200" height="150" 
                                 id="productImagePreview">
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
                        <h6 class="text-muted mb-3">Step 2: Capture User Image</h6>
                        <div class="border rounded-3 overflow-hidden mb-3 text-center p-2 bg-light">
                            <img src="{{ asset('assets/images/user/07.jpg')}}"
                                 alt="Sample Customer"
                                 class="rounded-circle shadow-sm"
                                 width="150" height="150"
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
    <form action="{{ route('shift-close.store') }}" method="POST">
        @csrf

        <div class="modal fade no-print" id="closeShiftModal" tabindex="-1" aria-labelledby="closeShiftModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content shadow-sm rounded-4 border-0">
                    <div class="modal-header bg-primary text-white rounded-top-4">
                        <h5 class="modal-title fw-semibold" id="closeShiftModalLabel">
                            <i class="bi bi-camera-video me-2"></i>Shift Closing - {{ $branch_name ?? 'Shop' }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>

                    <div class="modal-body px-4 py-4">

                        {{-- Hidden Fields --}}
                        <input type="hidden" name="start_time" value="{{ $shift->start_time??'' }}">
                        <input type="hidden" name="end_time" value="{{ $shift->end_time??'' }}">
                        <input type="hidden" name="opening_cash" value="{{ $shift->opening_cash??'' }}">
                        <input type="hidden" name="today_cash" value="{{ $todayCash??'' }}">
                        <input type="hidden" name="total_payments" value="{{ $shift->total_payments??'' }}">

                        {{-- Time Info --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <h5 class="text-dark text-center w-100">💰 CURRENT REGISTER</h5>
                            </div>

                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Start Time</label>
                                        <div class="">{{ $shift->start_time ?? "" }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">End Time</label>
                                        <div class="">{{ $shift->end_time ?? ""}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Sales --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="border rounded p-3 mb-3 bg-white">
                                    <h5 class="text-dark text-center">💵 SALES</h5>
                                    <table class="table table-bordered table-sm m-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-start">Category</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($categoryTotals as $category => $amount)
                                                <tr>
                                                    <td class="text-start">{{ $category }}</td>
                                                    <td class="text-end">{{ number_format($amount, 2) }}</td>
                                                </tr>
                                                <input type="hidden" name="categories[{{ $category }}]" value="{{ $amount }}">
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <table class="table table-bordered table-sm align-middle">
                                    <tbody>
                                        <tr>
                                            <td class="text-center fw-bold">OPENING CASH</td>
                                            <td class="text-center">{{ $shift->opening_cash ??0 }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center fw-bold">TODAY CASH</td>
                                            <td class="text-center">{{ $todayCash ??0 }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center fw-bold">CLOSING CASH</td>
                                            <td class="text-center">{{ $shift->total_payments ??0 }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                {{-- Submit Button --}}
                                <div class="text-right mt-3">
                                    <button type="submit" class="btn btn-primary px-4">💾 Save Shift Close</button>
                                </div>
                            </div>
                        </div>

                        {{-- Cash Details --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="">
                                    <h5 class="text-dark text-center">💵 CASH DETAILS</h5>
                                    <table class="table table-bordered table-sm align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center">Denomination</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-center">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(!empty($shiftcash))
                                                @foreach ($shiftcash as $key => $item)
                                                    <tr>
                                                        <td class="text-center fw-bold">{{ $key }}</td>
                                                        <td class="text-center">{{ $item }}</td>
                                                        <td class="text-center">{{ $key * $item }}</td>
                                                    </tr>
                                                <input type="hidden" name="cash_breakdown[{{ $key }}]" value="{{ $item }}">
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    
    {{-- <form action="{{ route('shift-close.withdraw') }}" method="POST">
        @csrf --}}

        <div class="modal fade no-print" id="cashout" tabindex="-1" aria-labelledby="cashout" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
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
                                            <table class="table table-bordered align-middle text-center">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Currency</th>
                                                        <th>Nos</th>
                                                        <th>Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($noteDenominations as $key => $denomination)
                                                        <tr>
                                                            <td>₹{{ $denomination }} X</td>
                                                            <td>
                                                                <div class="d-flex justify-content-center align-items-center">
                                                                    <button type="button" class="btn btn-sm btn-secondary"
                                                                        onclick="updateNote('{{ $key }}_{{ $denomination }}', -1, {{ $denomination }})">
                                                                        <i class="fas fa-minus"></i>
                                                                    </button>
                                                                    <span id="display_{{ $key }}_{{ $denomination }}" class="mx-3">0</span>
                                                                    <button type="button" class="btn btn-sm btn-success"
                                                                        onclick="updateNote('{{ $key }}_{{ $denomination }}', 1, {{ $denomination }})">
                                                                        <i class="fas fa-plus"></i>
                                                                    </button>
                                                                    <input type="hidden" name="withcashNotes.{{ $key }}.{{ $denomination }}"
                                                                        id="withcashnotes_{{ $key }}_{{ $denomination }}" value="0">
                                                                </div>
                                                            </td>
                                                            <td id="withcashsum_{{ $key }}_{{ $denomination }}">₹0</td>
                                                        </tr>
                                                    @endforeach
                                                    <tr class="fw-bold">
                                                        <td colspan="2" class="text-end">Total Withdraw</td>
                                                        <td id="totalNoteCashwith">₹0</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                    
                                        <input type="hidden" name="amount" id="withamountTotal" class="form-control mb-3" readonly required>
                    
                                        <div class="mb-3">
                                            <label for="narration" class="form-label fw-semibold">Narration</label>
                                            <input type="text" name="narration" id="narration" class="form-control" required>
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
    {{-- </form> --}}

    <!-- Modal HTML -->
    <div class="modal fade no-print" id="cashInHand" tabindex="-1" aria-labelledby="captureModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('cash-in-hand') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cash In Hand Details</h5>
                    </div>
                    <div class="modal-body">

                        <input type="number" name="amount" id="amountTotal" class="form-control mb-3"
                            placeholder="Enter opening amount" required readonly>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Currency</th>
                                    <th>Nos</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($noteDenominations as $key => $denomination)
                                    <tr>
                                        <td>₹{{ $denomination }}</td>
                                        <td>
                                            <input type="number" name="cashNotes.{{ $key }}.{{ $denomination }}"
                                                class="form-control note-input" id="cashnotes_{{ $denomination }}"
                                                data-denomination="{{ $denomination }}" value="0"
                                                min="0">

                                        </td>
                                        <td id="cashsum_{{ $denomination }}">₹0</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="2" class="text-end fw-bold">Total Cash</td>
                                    <td id="totalNoteCashNew">₹0</td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary btn-sm mr-2">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.note-input');
            const totalCashDisplay = document.getElementById('totalNoteCashNew');
            const amountInput = document.getElementById('amountTotal');

            function updateTotals() {
                let total = 0;
                inputs.forEach(input => {
                    const denom = parseInt(input.dataset.denomination);
                    const qty = parseInt(input.value) || 0;
                    const sum = denom * qty;
                    document.getElementById(`cashsum_${denom}`).innerText = `₹${sum}`;
                    total += sum;
                });
                totalCashDisplay.innerText = `₹${total}`;
                amountInput.value = total;
            }

            inputs.forEach(input => {
                input.addEventListener('input', updateTotals);
            });

            // Initial calculation
            updateTotals();
        });
    </script>



    <div class="col-md-5 no-print">
        @include('layouts.flash-message')

        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-0">Transaction Summary</h5>
                    </div>
                    <div class="col-md-6 text-right">
                        {{-- <form action="{{ url('lang/change') }}" method="GET" class="d-inline-block me-2">
                            <select name="lang" onchange="this.form.submit()" class="form-control form-control-sm" style="width: auto; display: inline-block;">
                                <option value="en" {{ session('locale') == 'en' ? 'selected' : '' }}>English</option>
                                <option value="hi" {{ session('locale') == 'hi' ? 'selected' : '' }}>हिंदी</option>
                            </select>
                        </form> --}}
                        <button type="button" id="customer" class="btn btn-primary btn-sm mr-2 " data-toggle="modal"
                            data-target="#cashout">
                            Cash Out
                        </button>
                        <button type="button" id="closeShiftBtn" class="btn btn-primary btn-sm mr-2 " data-toggle="modal"
                            data-target="#closeShiftModal">
                            Close Shift
                        </button>
                        <button type="button" class="btn btn-outline-danger ms-2" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="Logout"
                            onclick="document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                            style="display: none;">
                            @csrf
                        </form>

                    </div>
                </div>

            </div>
            <div class="card-body" style="padding:5px">
                @if ($showBox)

                    <div id="cash-payment">

                        <form onsubmit="event.preventDefault(); calculateCash();" class="needs-validation" novalidate>
                           

                            {{-- <h6 class="mb-3">💵 Enter Cash Denominations</h6> --}}
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Currency</th>
                                                <th class="text-center">IN</th>
                                                <th class="text-center">OUT</th>
                                                <th class="text-center">Amount <button wire:click="clearCashNotes" class="btn btn-danger btn-sm">
                                                    <i class="fa fa-eraser"></i>
                                                </button></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totalIn = 0;
                                                $totalOut = 0;
                                                $totalAmount = 0;
                                            @endphp
                                    
                                            @foreach ($noteDenominations as $key => $denomination)
                                                @php
                                                    $inValue = $cashNotes[$key][$denomination]['in'] ?? 0;
                                                    $outValue = $cashNotes[$key][$denomination]['out'] ?? 0;
                                                    $rowAmount = ($inValue - $outValue) * $denomination;
                                            
                                                    $totalIn += $inValue * $denomination;
                                                    $totalOut += $outValue * $denomination;
                                                    $totalAmount += $rowAmount;
                                                @endphp
                                                <tr>
                                                    <td>₹{{ $denomination }}</td>
                                                    <td>
                                                        <input type="number"
                                                            wire:model.live.debounce.500ms="cashNotes.{{ $key }}.{{ $denomination }}.in"
                                                            class="form-control text-end"
                                                            min="0"
                                                            style="width: 100px;"
                                                            placeholder="0">
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                            wire:model.live.debounce.500ms="cashNotes.{{ $key }}.{{ $denomination }}.out"
                                                            class="form-control text-end"
                                                            min="0"
                                                            style="width: 100px;"
                                                            placeholder="0">
                                                    </td>
                                                    <td class="text-end fw-bold">
                                                        ₹{{ $rowAmount }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                    
                                            <tr class="table-secondary fw-bold">
                                                @php
                                                    $this->cashPaTenderyAmt=$totalIn;
                                                    $this->cashPayChangeAmt=$totalOut;

                                                @endphp
                                                <td class="text-end">Total</td>
                                                <td class="text-end">₹{{ $totalIn }}</td>
                                                <td class="text-end">₹{{ $totalOut }}</td>
                                                <td class="text-end">₹{{ $totalAmount }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    
                                    
                                </div>

                            </div>
                             <div class="row g-3">
                                <div class="col-md-4">
                                    <input type="hidden" wire-model="paymentType">

                                    <label for="cash" class="form-label">Cash Amount</label>
                                    
                                    <input type="number" class="form-control" id="cash"
                                        value="{{ $this->cashAmount }}" placeholder=""
                                        oninput="calculateChange()" readonly>

                                </div>

                                <div class="col-md-4">
                                    <label for="tender" class="form-label">Tendered Amount</label>
                                    <input type="number" wire:model="cashPaTenderyAmt" class="form-control"
                                        id="tender" placeholder=""readonly>
                                </div>

                                <div class="col-md-4">
                                    <label for="change" class="form-label">Change</label>
                                    <input type="number" wire:model="cashPayChangeAmt" class="form-control"
                                        id="change" readonly>
                                </div>
                            </div>

                            <hr class="">
                            <div class="border p-1 rounded bg-light">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>Subtotal</strong>
                                    <span>₹{{ number_format($sub_total, 2) }}</span>
                                </div>

                                @if ($commissionAmount > 0)
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>Commission Deduction</strong>
                                        <span>- ₹{{ number_format($commissionAmount, 2) }}</span>
                                    </div>
                                @endif
                                @if ($partyAmount > 0)
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>Point Deduction</strong>
                                        <span>- ₹{{ number_format($partyAmount, 2) }}</span>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between">
                                    <strong>Total Payable</strong>
                                    <span>₹{{ number_format($this->total, 2) }}</span>
                                    <input type="text" id="total" value="{{ $this->total }}"
                                        class="d-none" />
                                </div>
                            </div>
                            <p id="result" class="mt-3 fw-bold text-success"></p>
                            <div class="mt-4">
                                @if ($selectedCommissionUser || $selectedPartyUser)
                                    <button id="paymentSubmit" class="btn btn-primary btn-sm mr-2 btn-block mt-4"
                                         wire:click="checkout" wire:loading.attr="disabled">
                                        Submit
                                    </button>
                                    @endif
                                <div wire:loading class=" text-muted">Processing payment...</div>
                            </div>
                        </form>
                    </div>
                @elseif($shoeCashUpi)
                    <div id="cashupi-payment">
                        <form onsubmit="event.preventDefault(); calculateCash();" class="needs-validation" novalidate>
                            
                            {{-- <h6 class="mb-3">💵 Enter Cash Denominations</h6> --}}
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Currency</th>
                                                <th class="text-center">IN</th>
                                                <th class="text-center">OUT</th>
                                                <th class="text-center">Amount 
                                                    <button wire:click="clearCashNotes" class="btn btn-danger btn-sm">
                                                        <i class="fa fa-eraser"></i>
                                                    </button>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totalIn = 0;
                                                $totalOut = 0;
                                                $totalAmount = 0;
                                            @endphp
                            
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
                                                    <td>₹{{ $denomination }}</td>
                                                    <td>
                                                        <input type="number"
                                                            wire:model.live.debounce.500ms="cashupiNotes.{{ $key }}.{{ $denomination }}.in"
                                                            class="form-control text-end"
                                                            min="0"
                                                            id="in_{{ $denomination }}_new"
                                                            style="width: 100px;"
                                                            placeholder="0">
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                            wire:model.live.debounce.500ms="cashupiNotes.{{ $key }}.{{ $denomination }}.out"
                                                            class="form-control text-end"
                                                            min="0"
                                                            id="out_{{ $denomination }}_new"
                                                            style="width: 100px;"
                                                            placeholder="0">
                                                    </td>
                                                    <td class="text-end fw-bold" id="amount_{{ $denomination }}_new">
                                                        ₹{{ $rowAmount }}
                                                    </td>
                                                </tr>
                                            @endforeach
                            
                                            <tr class="table-secondary fw-bold">
                                                @php
                                                    $this->cashPaTenderyAmt = $totalIn;
                                                    $this->cashPayChangeAmt = $totalOut;
                                                @endphp
                                                <td class="text-end">Total</td>
                                                <td class="text-end" id="total_in_new">₹{{ $totalIn }}</td>
                                                <td class="text-end" id="total_out_new">₹{{ $totalOut }}</td>
                                                <td class="text-end" id="total_amount_new">₹{{ $totalAmount }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row">

                                <div class="col-md-6">
                                    <input type="hidden" wire-model="paymentType" >
                                    <input type="hidden" id="actualCash" class="border rounded w-full p-2 bg-gray-100" value="{{ $this->total }}"  readonly>
                                    <label for="cash" class="form-label">Cash Amount</label>
                                    <input type="number" id="cashAmount" step="0.01" wire:model.live.debounce.500ms="cash" class="form-control" min="0" max="{{ $this->total }}">
                                </div>
                            
                                <div class="col-md-6">
                                    <label for="cash" class="form-label">UPI Amount</label>

                                    <input type="number" id="onlineAmount" step="0.01" wire:model.live.debounce.500ms="upi" class="form-control" min="0" max="{{ $this->total }}">
                                </div>
                            </div>
                           
                            <hr class="">

                            
                            <div class="border p-3 rounded bg-light">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>Subtotal</strong>
                                    <span>₹{{ number_format($sub_total, 2) }}</span>
                                </div>

                                @if ($commissionAmount > 0)
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>Commission Deduction</strong>
                                        <span>- ₹{{ number_format($commissionAmount, 2) }}</span>
                                    </div>
                                @endif
                                @if ($partyAmount > 0)
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>Point Deduction</strong>
                                        <span>- ₹{{ number_format($partyAmount, 2) }}</span>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between">
                                    <strong>Total Payable</strong>
                                    <span>₹{{ number_format($this->total, 2) }}</span>
                                    <input type="text" id="total" value="{{ $this->total }}"
                                        class="d-none" />
                                </div>
                            </div>
                            <p id="result" class="mt-3 fw-bold text-success"></p>
                            <div class="mt-4">
                                @if ($selectedCommissionUser || $selectedPartyUser)
                                    <button id="paymentSubmit" class="btn btn-primary btn-sm mr-2 btn-block mt-4"
                                        style="display:none" wire:click="checkout" wire:loading.attr="disabled">
                                        Submit
                                    </button>
                                @endif
                                <div wire:loading class=" text-muted">Processing payment...</div>
                            </div>

                        </form>
                    </div>
                @else
                    <div class="d-flex justify-content-between">
                        <strong>No Data Found</strong>
                    </div>
                @endif


            </div>
        </div>

    </div>

    @if ($invoiceData)
    @include('livewire.printinvoice')
    

    @endif
</div>
<script>
    window.addEventListener('triggerPrint', () => {

        setTimeout(() => {
            window.print();
        }, 300);
    });

    window.onafterprint = () => {
        window.location.reload();
    };
</script>

<!-- Script to show modal -->
  @if($showModal)
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const modal = new bootstrap.Modal(document.getElementById('cashInHand'));
            modal.show();
        });
    </script>
@endif


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
            breakdown = `Remaining amount to collect: ₹${Math.abs(change).toFixed(2)}`;
        } else {
            breakdown = `Exact amount received. No change needed.`;
        }

        //  document.getElementById("notes-breakdown").innerHTML = breakdown;
    }

    function calculateCash() {
        const notes2000 = parseInt(document.getElementById('notes_2000').value) || 0;
        const notes500 = parseInt(document.getElementById('notes_500').value) || 0;

        const total = (notes2000 * 2000) + (notes500 * 500);

        if (total === 4000) {
            document.getElementById('result').innerText = `✅ Total is ₹${total}`;
        } else {
            document.getElementById('result').innerText = `❌ Total is ₹${total}, which is not ₹4000`;
        }
    }

    function calculateCashBreakdown() {
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


        let total = 0;
        let notesum = 0;
        const cash = document.getElementById('cash').value;
        const change = document.getElementById('change').value;
        denominations.forEach(note => {
            //console.log(document.getElementById(note.id).value);
            const count = parseInt(document.getElementById(note.id).value) || 0;
            // console.log(count);

            const subtotal = count * note.value;
            total += subtotal;
            //console.log(subtotal);

            document.getElementById(note.sumId).textContent = `₹${subtotal.toLocaleString()}`;
        });

        document.getElementById('totalNoteCash').textContent = ` ₹${total.toLocaleString()}`;

        total -= change;


        if (cash == total) {
            document.getElementById('paymentSubmit').style.display = 'block';

            // document.getElementById('result').textContent = `Total Cash: ₹${total.toLocaleString()}`;
        }
    }
    function calculateCashUpiBreakdown() {
        const denominations = [
            {
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
        let notesum=0;
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

            document.getElementById(note.sumId).textContent = `₹${subtotal.toLocaleString()}`;
        });
        
        document.getElementById('totalNoteCash').textContent = ` ₹${total.toLocaleString()}`;

        console.log("Cash Amount: ", actualTotal);
        console.log("Actual Total: ", parseFloat(cashAmount) + parseFloat(onlineAmount));

        if (actualTotal == parseFloat(cashAmount) + parseFloat(onlineAmount)) {
            document.getElementById('paymentSubmit').style.display = 'block';
           // document.getElementById('result').textContent = `Total Cash: ₹${total.toLocaleString()}`;
        }
    }

    // Run on load
    document.addEventListener("DOMContentLoaded", calculateCashBreakdown);
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
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
                            document.getElementById('step1').classList.add('d-none');
                            document.getElementById('step2').classList.remove('d-none');
                        } else if (type === 'user') {
                            $('#captureModal').modal('hide');
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
    $(document).ready(function() {

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
        setInterval(checkShiftTime, 30000);
        $('#captureModal').on('hidden.bs.modal', function() {
            // Reset to Step 1 when modal is closed
            document.getElementById('step1').classList.remove('d-none');
            document.getElementById('step2').classList.add('d-none');
        });
        Livewire.on('alert_remove', () => {
            setTimeout(() => {
                $(".toast").fadeOut("fast");
            }, 2000);
        });
    });
    $("#cashInHand").click(function() {
        // e.preventDefault(); // prevent default form submission
        $('#cashInHand').submit(); // submit the form
        // Perform form submission using AJAX or any logic
        // Example:
        // $.post('/submit-url', $(this).serialize(), function(response) {
        //   $('#myModal').modal('hide'); // hide modal after success
        // });

        // For demo purposes, simulate a successful submission

    });
     document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.note-input');
        const totalCashDisplay = document.getElementById('totalNoteCash');
        const amountInput = document.getElementById('amountTotal');

        function updateTotals() {
            let total = 0;
            inputs.forEach(input => {
                const denom = parseInt(input.dataset.denomination);
                const qty = parseInt(input.value) || 0;
                const sum = denom * qty;
                document.getElementById(`cashsum_${denom}`).innerText = `₹${sum}`;
                total += sum;
            });

            totalCashDisplay.innerText = `₹${total}`;
            amountInput.value = total;
        }

        inputs.forEach(input => {
            input.addEventListener('input', updateTotals);
        });

        // Initial calculation
        updateTotals();
    });
    const input = document.getElementById('numberInput');
        const numpad = document.getElementById('numpad');

        const createNumpad = () => {
            const buttons = ['1','2','3','4','5','6','7','8','9','0','Clear','OK'];
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

        input.addEventListener('click', (e) => {
            const rect = input.getBoundingClientRect();
            createNumpad();
            numpad.style.top = `${rect.bottom + window.scrollY + 5}px`;
            numpad.style.left = `${rect.left + window.scrollX}px`;
            numpad.style.display = 'grid';
        });

        // Optional: Close numpad if clicked outside
        document.addEventListener('click', (e) => {
            if (!numpad.contains(e.target) && e.target !== input) {
                numpad.style.display = 'none';
            }
    });
</script>
<script>
    function updateNote(id, delta, denomination) {
        const input = document.getElementById('withcashnotes_' + id);
        let current = parseInt(input.value || 0);

        current += delta;
        if (current < 0) current = 0;

        input.value = current;
        document.getElementById('display_' + id).innerText = current;
        document.getElementById('withcashsum_' + id).innerText = '₹' + (current * denomination);

        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;

        document.querySelectorAll('input[id^="withcashnotes_"]').forEach(input => {
            const count = parseInt(input.value || 0);
            const denom = parseInt(input.id.split('_').pop());
            total += count * denom;
        });

        document.getElementById('totalNoteCashwith').innerText = '₹' + total;
        document.getElementById('withamountTotal').value = total;
    }
</script>
