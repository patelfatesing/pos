<div class="container mt-5">

    @include('layouts.flash-message')

    {{-- @if($cartitems->isEmpty())
        <div class="alert alert-info text-center">Your cart is empty.</div>
    @else --}}
    <div class="row">
        <div class="col-md-12 text-right mb-4">
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Back</a>
        </div>
       
        <!-- Product List Section -->
        <div class="col-md-6 mb-4">

            <div class="card shadow-sm">
                <div class="card-body">
                    <livewire:productlist />
                </div>
            </div>
        </div>

        <!-- Order Summary and Payment Section -->
        <div class="col-md-6">
            <div class="card">
                
                <div class="card-body" wire:ignore>
                    <h5 class="card-title">Scan Barcode using Webcam</h5>
                    <div id="camera" style="width:100%; height: 300px; border: 1px solid #ddd; border-radius: 5px; overflow: hidden;"></div>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Order List</h5>
                    </div>
                <div class="card-body">
                    
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
                                @foreach($cartitems as $item)
                                <tr>
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
                                    <td>
                                        @if(@$item->product->inventorie->discount_price && $this->commissionAmount > 0)
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
                                        <button class="btn btn-sm btn-danger" wire:click="removeItem({{ $item->id }})">Remove</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @auth
                    @if(auth()->user()->hasRole('cashier'))
                    
                        <div class="form-group">
                            <div class="card shadow-sm">
                                <div class="card-header">
                                    <label for="commissionUser">👤 Select Commission Customer</label>
                                    <select id="commissionUser" class="form-control" wire:model="selectedCommissionUser" wire:change="calculateCommission">
                                        <option value="">-- Select a user --</option>
                                        @foreach($commissionUsers as $user)
                                            <option value="{{ $user->id }}">{{ $user->first_name ." ".$user->last_name}} </option>
                                        @endforeach
                                    </select>
                                </div>
                                @if($selectedCommissionUser)
                                    <div class="card-body text-center" wire:ignore>
                                        <video id="video" class="rounded border" width="100%" height="300" autoplay></video>
                                        <canvas id="canvas" style="display: none;"></canvas>
                                        <button id="snap" class="btn btn-success mt-3">Capture Photo</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    @if(auth()->user()->hasRole('warehouse'))
                        <div class="form-group">
                            <div class="card shadow-sm">
                                <div class="card-header">
                                    <label for="partyUser">👥 Select Party Customer</label>
                                    <select id="partyUser" class="form-control" wire:model="selectedPartyUser" wire:change="calculateParty">
                                        <option value="">-- Select a user --</option>
                                        @foreach($partyUsers as $user)
                                            <option value="{{ $user->id }}">{{ $user->first_name ." ".$user->last_name}} ({{ $user->credit_points }}pt)</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if($selectedPartyUser)
                                    <div class="card-body text-center" wire:ignore>
                                        <video id="partyVideo" class="rounded border" width="100%" height="300" autoplay></video>
                                        <canvas id="partyCanvas" style="display: none;"></canvas>
                                        <button id="partySnap" class="btn btn-success mt-3">Capture Photo</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endauth
                    <div class="form-group">
                          
                            <label>Online Payment (₹)</label>
                            
                            <input type="number" id="onlineAmount" wire:model="onlineAmount" class="form-control" placeholder="Enter online amount" oninput="validateAmountInput(this)">
                            <small id="onlineAmountError" class="text-danger d-none">Please enter a valid amount.</small>
                        </div>
                        <h5 class="mb-3">💳 Payment Details</h5>
                        <div class="form-group">
                            <label>Cash Payment (₹)</label>
                            <input type="number" id="cashAmount" wire:model="cashAmount" class="form-control" placeholder="Enter cash amount" oninput="validateAmountInput(this)">
                            <small id="cashAmountError" class="text-danger d-none">Please enter a valid amount.</small>
                        </div>
                        <h5 class="mb-3">🧾 Order Summary</h5>
                        <div class="form-group">
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
                        <div class="border p-3 rounded bg-light">
                            <div class="d-flex justify-content-between mb-2">
                                <strong>Subtotal</strong>
                                <span>₹{{ number_format($sub_total, 2) }}</span>
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
                            <div class="d-flex justify-content-between">
                                <strong>Total Payable</strong>
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
            </div>
        </div>
    </div>
    {{-- @endif --}}

</div>
<script src="https://cdn.jsdelivr.net/npm/@ericblade/quagga2/dist/quagga.min.js"></script>

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

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const quaggaConf = {
            inputStream: {
                target: document.querySelector("#camera"),
                type: "LiveStream",
                constraints: {
                    width: { min: 640 },
                    height: { min: 480 },
                    facingMode: "environment",
                    aspectRatio: { min: 1, max: 2 }
                }
            },
            decoder: {
                readers: ['code_128_reader']
            },
        };

        Quagga.init(quaggaConf, function (err) {
            if (err) {
                console.error("Quagga initialization failed:", err);
                return;
            }
            Quagga.start();
        });

        Quagga.onDetected(function (result) {
            const code = result.codeResult.code;
            if (code) {
                alert("Detected barcode: " + code);
                Quagga.stop(); // Stop scanning after detecting a barcode
            }
        });
            // Re-run Quagga after any Livewire update
        document.addEventListener("livewire:load", () => {
            Livewire.hook('message.processed', (message, component) => {
                startQuagga();
            });
        });
    });
</script>
<script>
    // Access camera
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            document.getElementById('video').srcObject = stream;
        });

    // Capture image
    document.getElementById('snap').addEventListener('click', () => {
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
    
        canvas.toBlob(blob => {
        const formData = new FormData();
        formData.append('photo', blob, 'captured_image.png');
        formData.append('selectedCommissionUser', document.getElementById('commissionUser').value);
        
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
            alert('Photo uploaded! Path: ' + data.path);
            document.getElementById('photo').value = data.path;
            } else {
            alert('Upload failed!');
            }
        })
        .catch(err => console.error(err));
        }, 'image/png');
    });

    // Upload to server
    document.getElementById('photoForm').addEventListener('submit', e => {
        e.preventDefault();
    
        const formData = new FormData();
        formData.append('photo', document.getElementById('photo').value);
    
        fetch('{{ route('products.uploadpic') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
        })
        .then(res => res.json())
        .then(data => alert('Photo uploaded!'))
        .catch(err => console.error(err));
    });
</script>