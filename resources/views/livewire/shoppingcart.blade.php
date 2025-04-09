<div class="container mt-5">

    @include('layouts.flash-message')

    <h3 class="mb-4">🛒 Your Shopping Cart</h3>

    @if($cartitems->isEmpty())
        <div class="alert alert-info">Your cart is empty.</div>
    @else
    <div class="mb-3">
        <input type="text" id="cartSearch" class="form-control" placeholder="Search products in cart..." oninput="validateSearchInput(this)">
        <small id="cartSearchError" class="text-danger d-none">Please enter valid characters.</small>
    </div>
    <div class="table-responsive mb-4">
        <table class="table table-bordered" id="cartTable">
            <thead class="thead-light">
                <tr>
                    <th></th>
                    <th>Product</th>
                    <th style="width: 150px;">Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cartitems as $item)
                <tr>
                    <td>
                        <img src="{{ asset('storage/' . $item->product->image) }}" width="50" class="rounded" />
                    </td>
                    <td class="product-name">
                        <strong>{{ $item->product->name }}</strong><br>
                        <small>{{ $item->product->description }}</small>
                    </td>
                    <td>
                        <div class="d-flex align-items-center justify-content-between">
                            <button class="btn btn-sm btn-outline-success" wire:click="decrementQty({{ $item->id }})">−</button>
                            <span class="mx-2">{{ $item->quantity }}</span>
                            <button class="btn btn-sm btn-outline-warning" wire:click="incrementQty({{ $item->id }})">+</button>
                        </div>
                    </td>
                    <td>₹{{ number_format(@$item->product->inventorie->sell_price, 2) }}</td>
                    <td>₹{{ number_format(@$item->product->inventorie->sell_price * $item->quantity, 2) }}</td>
                    <td>
                        <button class="btn btn-sm btn-danger" wire:click="removeItem({{ $item->id }})">Remove</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Payment Section -->
    <div class="row">
        <div class="col-md-6">
            @auth
                @if(auth()->user()->hasRole('cashier'))
                    <div class="form-group">
                        <label for="commissionUser">👤 Select Commission Customer</label>
                        <select id="commissionUser" class="form-control" wire:model="selectedCommissionUser" wire:change="calculateCommission">
                            <option value="">-- Select a user --</option>
                            @foreach($commissionUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->first_name ." ".$user->last_name}} ({{ $user->commission_value }}%)</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if(auth()->user()->hasRole('warehouse'))
                    <div class="form-group">
                        <label for="partyUser">👥 Select Party Customer</label>
                        <select id="partyUser" class="form-control" wire:model="selectedPartyUser" wire:change="calculateParty">
                            <option value="">-- Select a user --</option>
                            @foreach($partyUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->first_name ." ".$user->last_name}} ({{ $user->credit_points }}pt)</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            @endauth

          
            
            <h5>💳 Payment Details</h5>
            <div class="form-group">
                <label>Cash Payment (₹)</label>
                <input type="number" id="cashAmount" wire:model="cashAmount" class="form-control" placeholder="Enter cash amount" oninput="validateAmountInput(this)" >
                <small id="cashAmountError" class="text-danger d-none">Please enter a valid amount.</small>
            </div>
            <div class="form-group">
                <label>Online Payment (₹)</label>
                <input type="number" id="onlineAmount" wire:model="onlineAmount" class="form-control" placeholder="Enter online amount" oninput="validateAmountInput(this)">
                <small id="onlineAmountError" class="text-danger d-none">Please enter a valid amount.</small>
            </div>

            <div class="mt-4">
                <h6 class="text-muted">💰 Cash Note Breakdown</h6>
                <div class="d-flex justify-content-between">
                    <span>₹1000 Notes</span>
                    <span id="note1000">{{ $this->noteBreakdown['thousand'] }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>₹500 Notes</span>
                    <span id="note500">{{ $this->noteBreakdown['five_hundred'] }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>₹200 Notes</span>
                    <span id="note200">{{ $this->noteBreakdown['two_hundred'] }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <h5>🧾 Order Summary</h5>
            <div class="border p-3 rounded bg-light">
                <div class="d-flex justify-content-between mb-2">
                    <strong>Subtotal</strong>
                    <span>₹{{ number_format($sub_total, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <strong>Tax (18%)</strong>
                    <span>₹{{ number_format($tax, 2) }}</span>
                </div>
                @if($commissionAmount > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Commission Deduction</strong>
                        <span>- ₹{{ number_format($commissionAmount, 2) }}</span>
                    </div>
                @endif
                @if($partyAmount > 0)
                <div class="d-flex justify-content-between mb-2">
                    <strong>Point Deduction</strong>
                    <span>- ₹{{ number_format($partyAmount, 2) }}</span>
                </div>
            @endif
                <div class="d-flex justify-content-between border-top pt-2">
                    <strong>Total</strong>
                    <span>₹{{ number_format($this->total, 2) }}</span>
                    <input type="text" id="total" value="{{$this->total}}" class="d-none" />
                </div>
            </div>

            <button class="btn btn-primary btn-block mt-4" wire:click="checkout" wire:loading.attr="disabled">
                ✅ Proceed to Checkout
            </button>
            <div wire:loading class="mt-2 text-muted">Processing payment...</div>
        </div>
    </div>
    @endif

</div>

<script>
    function validateSearchInput(input) {
        const regex = /^[a-zA-Z0-9\s]*$/;
        const errorElement = document.getElementById('cartSearchError');
        if (!regex.test(input.value)) {
            errorElement.classList.remove('d-none');
        } else {
            errorElement.classList.add('d-none');
        }
    }

    function validateAmountInput(input) {
        const errorElement = input.id === 'cashAmount' ? document.getElementById('cashAmountError') : document.getElementById('onlineAmountError');
        if (input.value < 0 || isNaN(input.value)) {
            errorElement.classList.remove('d-none');
        } else {
            errorElement.classList.add('d-none');
        }
    }
</script>
<script>
    $(document).ready(function () {
        // Search functionality
        $("#cartSearch").on("input", function () {
            const query = $(this).val().toLowerCase();
            $("#cartTable tbody tr").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(query) > -1);
            });
        });

        
        function updateBreakdown(cash) {
            let remaining = cash;
            const thousands = Math.floor(remaining / 1000);
            remaining %= 1000;

            const fiveHundreds = Math.floor(remaining / 500);
            remaining %= 500;

            const twoHundreds = Math.floor(remaining / 200);
            remaining %= 200;

            $("#note1000").text(thousands);
            $("#note500").text(fiveHundreds);
            $("#note200").text(twoHundreds);
        }

        function updateCashOnlineFields(source) {

            let totalAmount = parseFloat($("#total").val()) || 0;
            let cash = parseFloat($("#cashAmount").val()) || 0;
            let online = parseFloat($("#onlineAmount").val()) || 0;

            if (source === 'cash') {
                const remaining = (totalAmount - cash) > 0 ? totalAmount - cash : 0;
                $("#onlineAmount").val(remaining);
                updateBreakdown(cash);
            } else if (source === 'online') {
                const remaining = (totalAmount - online) > 0 ? totalAmount - online : 0;
                $("#cashAmount").val(remaining);
                updateBreakdown(remaining);
            }
        }

        $("#cashAmount").on("input", function () {
            updateCashOnlineFields('cash');
        });

        $("#onlineAmount").on("input", function () {
            updateCashOnlineFields('online');
        });

        // Initial run
       // $("#onlineAmount").trigger("input");
    });
</script>
