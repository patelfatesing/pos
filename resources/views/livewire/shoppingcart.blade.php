<div class="row">

    <div class="col-md-8">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <form wire:submit.prevent="searchTerm" class="mb-3">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Enter Product Name "
                                wire:model.lazy="searchTerm">
                        </div>
                    </form>
                    @if (!empty($searchResults))
                        <div class="list-group mb-3">
                            @foreach ($searchResults as $product)
                                <a href="#" class="list-group-item list-group-item-action"
                                    wire:click.prevent="addToCart({{ $product->id }})">
                                    <strong>{{ $product->name }}</strong><br>
                                    <small>{{ $product->description }}</small>
                                </a>
                            @endforeach
                        </div>
                    @endif
        
                </div>
            </div>
            <div class="col-md-6">
                @if(auth()->user()->hasRole('cashier'))
                    
                        <div class="form-group">
                            <select id="commissionUser" class="form-control" wire:model="selectedCommissionUser" wire:change="calculateCommission">
                                <option value="">-- Select Commission Customer --</option>
                                @foreach($commissionUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->first_name ." ".$user->last_name}} </option>
                                @endforeach
                            </select>
                            
                            @if($selectedCommissionUser)
                                    <div class="card-body text-center">
                                        <video id="video" class="rounded border" width="100%" height="300" autoplay></video>
                                        <canvas id="canvas" style="display: none;"></canvas>
                                        <button id="snap" class="btn btn-success mt-3">Capture Photo</button>
                                    </div>
                                @endif
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
                        @if($selectedPartyUser)
                            <div class="card-body text-center" wire:ignore>
                                <video id="partyVideo" class="rounded border" width="100%" height="300" autoplay></video>
                                <canvas id="partyCanvas" style="display: none;"></canvas>
                                <button id="partySnap" class="btn btn-success mt-3">Capture Photo</button>
                            </div>
                        @endif
                        
                    </div>
                @endif

            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="cartTable">
                <thead class="thead-light">
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($itemCarts as $item)
                        <tr>
                            <td class="product-name">
                                <strong>{{ $item->product->name }}</strong><br>
                                <small>{{ $item->product->description }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-between">
                                    <button class="btn btn-sm btn-outline-success"
                                        wire:click="decrementQty({{ $item->id }})">−</button>
                                    <span class="mx-2">{{ $item->quantity }}</span>
                                    <button class="btn btn-sm btn-outline-warning"
                                        wire:click="incrementQty({{ $item->id }})">+</button>
                                </div>
                            </td>
                            <td>
                                @if (@$item->product->inventorie->discount_price && $this->commissionAmount > 0)
                                    <span class="text-danger">
                                        ₹{{ number_format(@$item->product->inventorie->sell_price, 2) }}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        <s>₹{{ number_format(@$item->product->inventorie->discount_price, 2) }}</s>
                                    </small>
                                @else
                                    ₹{{ number_format(@$item->product->inventorie->sell_price, 2) }}
                                @endif
                            </td>
                            <td>₹{{ number_format(@$item->product->inventorie->sell_price * $item->quantity, 2) }}</td>
                            <td>
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
            <div class="mt-3">
                {{ $itemCarts->links('components.pagination.custom') }}
            </div>
        </div>
        <div class="row g-3">
           
            <div class="col-6 col-md-3">
                <button class="btn btn-lg btn-deafult w-100 shadow-sm">
                    <h3>Quantity</h3>
                <span>₹{{ number_format($this->total, 2) }}</span>
                <input type="text" id="total" value="{{ $this->total }}" class="d-none" />
                </button>

            </div>
            <div class="col-6 col-md-3">
                <button class="btn btn-lg btn-deafult w-100 shadow-sm">
                    <h3>MRP</h3>
                <span>₹{{ number_format($this->total, 2) }}</span>
                <input type="text" id="total" value="{{ $this->total }}" class="d-none" />
                </button>

            </div>
            <div class="col-6 col-md-3">
                <button class="btn btn-lg btn-deafult w-100 shadow-sm">
                    <h3>Rounde Of</h3>
                <span>₹{{ number_format($this->total, 2) }}</span>
                <input type="text" id="total" value="{{ $this->total }}" class="d-none" />
                </button>

            </div>
            <div class="col-6 col-md-3">
                <button class="btn btn-lg btn-deafult w-100 shadow-sm">
                    <h3>Total Payable</h3>
                <span>₹{{ number_format($this->total, 2) }}</span>
                <input type="text" id="total" value="{{ $this->total }}" class="d-none" />
                </button>

            </div>
        </div>
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <button class="btn btn-lg btn-primary w-100 shadow-sm">
                    <i class="bi bi-pause-circle me-2"></i> Hold
                </button>
            </div>
            <div class="col-6 col-md-3">
               
                <button wire:click="$dispatch('openCashModal')" class="btn btn-lg btn-primary w-100 shadow-sm">
                    Cash
                </button>
                
                {{-- <livewire:cash-modal :cash="$this->total" /> --}}

                <livewire:cash-modal  /> 
            </div>
            <div class="col-6 col-md-3">
                <button class="btn btn-lg btn-primary w-100 shadow-sm">
                    <i class="bi bi-credit-card me-2"></i> UPI
                </button>
            </div>
            <div class="col-6 col-md-3">
                <button class="btn btn-lg btn-primary w-100 shadow-sm">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i> Cash + UPI
                </button>
            </div>
        </div>


    </div>

    <!-- Modal for Cash -->
    <div class="modal fade" id="cashModal" tabindex="-1" aria-labelledby="cashModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cashModalLabel">Cash Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">

                            <p>Due Amount:</p>
                            <input type="number" id="cashAmount" wire:model="cashAmount" class="form-control"
                                placeholder="Enter cash amount" oninput="validateAmountInput(this)">
                        </div>
                        <div class="col-md-4">

                            <p>Tendered:</p>
                            <input type="number" id="cashAmount" wire:model="cashAmount" class="form-control"
                                placeholder="Enter cash amount" oninput="validateAmountInput(this)">
                        </div>
                        <div class="col-md-4">

                            <p>Change:</p>
                            <input type="number" id="cashAmount" wire:model="cashAmount" class="form-control"
                                placeholder="Enter cash amount" oninput="validateAmountInput(this)">
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <h4>Cash Note Breakdown</h4>
                            <p>Enter note count for each denomination:</p>
                            <div>
                                <label for="500">500 x </label>
                                <input type="number" wire:model="noteDenominations.500" id="500" min="0"
                                    wire:keyup="calculateBreakdown">
                                <span> = {{ $noteDenominations[500] * 500 }}</span>
                            </div>
                            <div>
                                <label for="2000">2000 x </label>
                                <input type="number" wire:model="noteDenominations.2000" id="2000" min="0"
                                    wire:keyup="calculateBreakdown">
                                <span> = {{ $noteDenominations[2000] * 2000 }}</span>
                            </div>
                            <div>
                                <label for="200">200 x </label>
                                <input type="number" wire:model="noteDenominations.200" id="200" min="0"
                                    wire:keyup="calculateBreakdown">
                                <span> = {{ $noteDenominations[200] * 200 }}</span>
                            </div>
                            <div>
                                <label for="100">100 x </label>
                                <input type="number" wire:model="noteDenominations.100" id="100"
                                    min="0" wire:keyup="calculateBreakdown">
                                <span> = {{ $noteDenominations[100] * 100 }}</span>
                            </div>

                            <hr>

                            <div>
                                <h5>Total Breakdown:</h5>
                                <ul>
                                    @foreach ($totalBreakdown as $note => $amount)
                                        <li>{{ $note }} x {{ $noteDenominations[$note] }} =
                                            {{ $amount }}</li>
                                    @endforeach
                                </ul>

                                <p><strong>Remaining: </strong>
                                    <input type="text" id="remainingAmount" value="{{ $remainingAmount }}"
                                        class="" />

                                </p>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Confirm Payment</button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Cart Summary</h5>
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
              
               
            </div>
        </div>
    </div>
</div>
</div>
</div>
<
<script>
    window.addEventListener('show-cash-modal', () => {
        let modal = new bootstrap.Modal(document.getElementById('cashModal'));
        modal.show();
    });
</script>
