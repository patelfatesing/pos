<div class="row ">

    <div class="col-md-7 no-print">
        <div class="col-md-12">
            <h4 class="text-right">Store Location:: {{ $this->branch_name }}</h4>

        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <form wire:submit.prevent="searchTerm" class="mb-3">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Scen Barcode /Enter Product Name "
                                wire:model.lazy="searchTerm">
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

            <div class="col-md-4">
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
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" id="customer" class="btn btn-primary btn-sm mr-2 " data-toggle="modal"
                                data-target="#captureModal">
                                Take picture
                            </button>
                        </div>

                    </div>
                </div>
            @endif
        </div>
        <div class="table-responsive">
            <div class="cart-table-scroll {{ count($itemCarts) > 5 ? 'scrollable' : '' }}">
                
                <table class="table table-bordered" id="cartTable">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 50%;">Product</th>
                            <th style="width: 20%;">Quantity</th>
                            <th style="width: 10%;">Price</th>
                            <th style="width: 10%;">Total</th>
                            <th style="width: 10%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($itemCarts as $item)
                            <tr>
                                <td class="product-name" style="word-wrap: break-word; width: 50%;">
                                    <strong>{{ $item->product->name }}</strong><br>
                                    <small>{{ $item->product->description }}</small>
                                </td>
                                <td style="width: 20%;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <button class="btn btn-sm btn-outline-success"
                                            wire:click="decrementQty({{ $item->id }})">−</button>
                                        <input type="number" min="1"
                                            class="form-control form-control-sm mx-2 text-center"
                                            wire:model.lazy="quantities.{{ $item->id }}"
                                            wire:change="updateQty({{ $item->id }})" />
                                        <button class="btn btn-sm btn-outline-warning"
                                            wire:click="incrementQty({{ $item->id }})">+</button>
                                    </div>
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
                                    <i class="bi bi-file-earmark-spreadsheet me-2"></i> Hold
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
        <div class="modal-dialog modal-dialog-centered modal-lg">
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
                        <div class="border rounded-3 overflow-hidden mb-3">
                            <video id="video1" class="w-100" autoplay></video>
                            <canvas id="canvas1" class="d-none"></canvas>
                        </div>
                        <button type="button" class="btn btn-outline-primary w-100"
                            onclick="captureImage('product')">
                            <i class="bi bi-camera me-1"></i>Capture Product Image
                        </button>
                    </div>

                    <!-- Step 2: User -->
                    <div id="step2" class="d-none mt-4">
                        <h6 class="text-muted mb-3">Step 2: Capture User Image</h6>
                        <div class="border rounded-3 overflow-hidden mb-3">
                            <video id="video2" class="w-100" autoplay></video>
                            <canvas id="canvas2" class="d-none"></canvas>
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
            <div class="modal-dialog modal-lg">
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
                                <div class="border rounded p-1 mb-1 bg-white">
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
                                        <input type="number"
                                        name="cashNotes[{{ $denomination }}]"
                                        class="form-control note-input"
                                        id="cashnotes_{{ $denomination }}"
                                        data-denomination="{{ $denomination }}"
                                        value="0" min="0">

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
    document.addEventListener('DOMContentLoaded', function () {
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
                        <h5 class="mb-0">🛒 Cart Summary</h5>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="button" id="customer" class="btn btn-primary btn-sm mr-2 " data-toggle="modal"
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
            <div class="card-body">
                @if ($showBox)

                    <div id="cash-payment">

                        <form onsubmit="event.preventDefault(); calculateCash();" class="needs-validation" novalidate>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="cash" class="form-label">Cash Amount</label>

                                    <input type="number" class="form-control" id="cash"
                                        value="{{ $this->total }}" placeholder="Enter Cash Amount"
                                        oninput="calculateChange()" readonly>

                                </div>

                                <div class="col-md-4">
                                    <label for="tender" class="form-label">Tendered Amount</label>
                                    <input type="number" wire:model="cashPaTenderyAmt" class="form-control"
                                        id="tender" placeholder="Enter Tendered Amount"
                                        oninput="calculateChange()">
                                </div>

                                <div class="col-md-4">
                                    <label for="change" class="form-label">Change</label>
                                    <input type="number" wire:model="cashPayChangeAmt" class="form-control"
                                        id="change" readonly>
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- <h6 class="mb-3">💵 Enter Cash Denominations</h6> --}}
                            <div class="row g-3">
                                <div class="col-md-12">

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
                                                        <input type="number"
                                                            wire:model="cashNotes.{{ $key }}.{{ $denomination }}"
                                                            class="form-control" id="notes_{{ $denomination }}"
                                                            value="0" min="0"
                                                            oninput="calculateCashBreakdown()">
                                                    </td>
                                                    <td id="sum_{{ $denomination }}">₹0</td>
                                                </tr>
                                            @endforeach
                                                <tr>
                                                    <td colspan="2" class="text-end fw-bold">Total Cash</td>
                                                    <td id="totalNoteCash">₹0</td>
                                                </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
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
                @elseif($shoeCashUpi)
                <div id="cashupi-payment">
                    <form onsubmit="event.preventDefault(); calculateCash();" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="cash" class="form-label">Cash Amount</label>

                                <input type="number" class="form-control" id="cash"
                                    value="{{ $this->total }}" placeholder="Enter Cash Amount"
                                    oninput="calculateChange()" readonly>

                            </div>

                            <div class="col-md-4">
                                <label for="tender" class="form-label">UPI Amount</label>
                                <input type="number" wire:model="cashPaTenderyAmt" class="form-control"
                                    id="tender" placeholder="Enter UPI Amount"
                                    oninput="calculateChange()">
                            </div>

                            <div class="col-md-4">
                                <label for="change" class="form-label">Change</label>
                                <input type="number" wire:model="cashPayChangeAmt" class="form-control"
                                    id="change" readonly>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- <h6 class="mb-3">💵 Enter Cash Denominations</h6> --}}
                        <div class="row g-3">
                            <div class="col-md-12">

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
                                                    <input type="number"
                                                        wire:model="cashNotes.{{ $key }}.{{ $denomination }}"
                                                        class="form-control" id="notes_{{ $denomination }}"
                                                        value="0" min="0"
                                                        oninput="calculateCashBreakdown()">
                                                </td>
                                                <td id="sum_{{ $denomination }}">₹0</td>
                                            </tr>
                                        @endforeach
                                            <tr>
                                                <td colspan="2" class="text-end fw-bold">Total Cash</td>
                                                <td id="totalNoteCash">₹0</td>
                                            </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
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
       
        <div class="col-lg-12 print-only">
            <div class="card card-block card-stretch card-height print rounded">
                <div class="card-header d-flex justify-content-between bg-primary header-invoice">
                    <div class="iq-header-title">
                        <h4 class="card-title mb-0">Invoice #{{ $invoiceData->invoice_number }}</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <img src="{{ asset('assets/images/logo.png') }}" class="logo-invoice img-fluid mb-3">
                            <h5 class="mb-0">Hello, {{ $invoiceData->customer_name }}</h5>
                            <p>Thank you for your business. Below is the summary of your invoice.</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="table-responsive-sm">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Order Date</th>
                                            <th scope="col">Order Status</th>
                                            <th scope="col">Order ID</th>
                                            {{-- <th scope="col">Billing Address</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ $invoiceData->created_at->format('d M Y') }}</td>
                                            <td>
                                                <span class="badge badge-{{ $invoiceData->status == 'Paid' ? 'success' : 'danger' }}">
                                                    {{ $invoiceData->status }}
                                                </span>
                                            </td>
                                            <td>{{ $invoiceData->invoice_number }}</td>
                                            {{-- <td>
                                                <p class="mb-0">{{ $invoiceData->billing_address }}</p>
                                            </td> --}}
                                           
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <h5 class="mb-3">Order Summary</h5>
                            <div class="table-responsive-sm">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th class="text-center" scope="col">#</th>
                                            <th scope="col">Item</th>
                                            <th class="text-center" scope="col">Quantity</th>
                                            <th class="text-center" scope="col">Price</th>
                                            <th class="text-center" scope="col">Totals</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoiceData->items as $i => $item)
                                        <tr>
                                            <th class="text-center" scope="row">{{ $i + 1 }}</th>
                                            <td>
                                                <h6 class="mb-0">{{ $item['name'] }}</h6>
                                            </td>
                                            <td class="text-center">{{ $item['quantity'] }}</td>
                                            <td class="text-center">₹{{ number_format($item['price'], 2) }}</td>
                                            <td class="text-center"><b>₹{{ number_format($item['price'] * $item['quantity'], 2) }}</b></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4 mb-3">
                        <div class="offset-lg-8 col-lg-4">
                            <div class="or-detail rounded">
                                <div class="p-3">
                                    <h5 class="mb-3">Order Details</h5>
                                    <div class="mb-2">
                                        <h6>Sub Total</h6>
                                        <p>₹{{ number_format($invoiceData->sub_total, 2) }}</p>
                                    </div>
                                    @if($invoiceData->commission_amount > 0)
                                    <div class="mb-2">
                                        <h6>Commission Deduction</h6>
                                        <p>- ₹{{ number_format($invoiceData->commission_amount, 2) }}</p>
                                    </div>
                                    @endif
                                    @if($invoiceData->party_amount > 0)
                                    <div class="mb-2">
                                        <h6>Party Deduction</h6>
                                        <p>- ₹{{ number_format($invoiceData->party_amount, 2) }}</p>
                                    </div>
                                    @endif
                                </div>
                                <div class="ttl-amt py-2 px-3 d-flex justify-content-between align-items-center">
                                    <h6>Total</h6>
                                    <h3 class="text-primary font-weight-700">₹{{ number_format($invoiceData->total, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <b class="text-danger">Notes:</b>
                            <p class="mb-0">Thank you for your business. If you have any questions, feel free to contact us.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
        let notes = [2000, 500, 100, 50, 20, 10, 5, 1];
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
                id: 'notes_2000',
                value: 2000,
                sumId: 'sum_2000'
            },
            {
                id: 'notes_500',
                value: 500,
                sumId: 'sum_500'
            },
            {
                id: 'notes_200',
                value: 200,
                sumId: 'sum_200'
            },
            {
                id: 'notes_100',
                value: 100,
                sumId: 'sum_100'
            },
        ];

        let total = 0;
        let notesum=0;
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

                        if (type === 'product') {
                            document.getElementById('step1').classList.add('d-none');
                            document.getElementById('step2').classList.remove('d-none');
                        } else if (type === 'user') {
                            $('#captureModal').modal('hide');
                            $('.modal-backdrop.show').remove();
                            //bootstrap.Modal.getInstance(document.getElementById('captureModal')).hide();
                            //document.getElementById('submitDiv').classList.remove('d-none');

                        }


                    } else {
                        alert('Upload failed!');
                    }
                })
                .catch(err => console.log(err));
        }, 'image/png');


    }

    $(document).ready(function() {
        $('#captureModal').on('hidden.bs.modal', function() {
            // Reset to Step 1 when modal is closed
            document.getElementById('step1').classList.remove('d-none');
            document.getElementById('step2').classList.add('d-none');
        });
        Livewire.on('alert_remove', () => {
            setTimeout(() => {
                $(".toast").fadeOut("fast");
            },2000);
        });
    });
    $( "#cashInHand" ).click(function() {
        e.preventDefault(); // prevent default form submission
        $('#cashInHand').submit(); // submit the form
        // Perform form submission using AJAX or any logic
        // Example:
        // $.post('/submit-url', $(this).serialize(), function(response) {
        //   $('#myModal').modal('hide'); // hide modal after success
        // });

        // For demo purposes, simulate a successful submission
      
        });
        document.addEventListener('DOMContentLoaded', function () {
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

</script>