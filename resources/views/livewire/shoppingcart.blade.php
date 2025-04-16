<div class="row">
    
    <div class="col-md-8">
        <div class="col-md-12">
            <h4 class="text-right">Store:: {{$this->branch_name}}</h4>
    
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
                    @if (!empty($searchResults))
                        <div class="search-results">

                            <div class="list-group mb-3 ">
                                @foreach ($searchResults as $product)
                                    <a href="#" class="list-group-item list-group-item-action"
                                        wire:click.prevent="addToCart({{ $product->id }})">
                                        <strong>{{ $product->name }} ({{ $product->size }})</strong><br>
                                        <small>₹{{ number_format(@$product->inventorie->sell_price, 2) }}</small>
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

                        {{-- @if ($selectedCommissionUser)
                                <div class="card-body text-center">
                                    <video id="video" class="rounded border" width="100%" height="300" autoplay></video>
                                    <canvas id="canvas" style="display: none;"></canvas>
                                    <button id="snap" class="btn btn-success mt-3">Capture Photo</button>
                                </div>
                            @endif --}}
                    </div>
                @endif
                @if (auth()->user()->hasRole('warehouse'))
                    <div class="form-group">
                        <label for="partyUser">👥 Select Party Customer</label>
                        <select id="partyUser" class="form-control" wire:model="selectedPartyUser"
                            wire:change="calculateParty">
                            <option value="">-- Select a user --</option>
                            @foreach ($partyUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->first_name . ' ' . $user->last_name }}
                                    ({{ $user->credit_points }}pt)</option>
                            @endforeach
                        </select>
                        {{-- @if ($selectedPartyUser)
                            <div class="card-body text-center" wire:ignore>
                                <video id="partyVideo" class="rounded border" width="100%" height="300" autoplay></video>
                                <canvas id="partyCanvas" style="display: none;"></canvas>
                                <button id="partySnap" class="btn btn-success mt-3">Capture Photo</button>
                            </div>
                        @endif --}}

                    </div>
                @endif

            </div>
            @if ($selectedPartyUser || $selectedCommissionUser)
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" id="customer" class="btn btn-primary mt-2" data-toggle="modal" data-target="#customerModal">
                                Add Customer
                                </button>
                           
                        </div>
                        <div class="col-md-6">
                            <!-- Button -->
                            <button type="button" id="product" class="btn btn-primary mt-2" data-toggle="modal" data-target="#productModal">
                                Add Product
                                </button>
                        
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="table-responsive" style="max-height: 700px; min-height: 520px; overflow-y: auto;">
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
                                    <input type="number" min="1"
                                        class="form-control form-control-sm mx-2 text-center"
                                        wire:model.lazy="quantities.{{ $item->id }}"
                                        wire:change="updateQty({{ $item->id }})" />
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
                                ₹{{ number_format($this->cartCount, 2) }}
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
                                <button wire:click="$dispatch('openCashModal')" class="btn btn-lg btn-primary w-100 shadow-sm">
                                    <i class="bi bi-file-earmark-spreadsheet me-2"></i> Cash
                                </button>
                             </td>
                             <td>
                                <button class="btn btn-lg btn-primary w-100 shadow-sm">
                                    <i class="bi bi-file-earmark-spreadsheet me-2"></i> Online
                                </button>
                             </td>
                             <td>
                                <button class="btn btn-lg btn-primary w-100 shadow-sm">
                                    <i class="bi bi-file-earmark-spreadsheet me-2"></i> Hold
                                </button>
                             </td>
                             <td>
                                <button class="btn btn-lg btn-primary w-100 shadow-sm">
                                    <i class="bi bi-file-earmark-spreadsheet me-2"></i> Cash + UPI
                                </button>
                             </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    

    </div>
   
     <div class="modal fade" id="customerModal" tabindex="-1" role="dialog" aria-labelledby="customerModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
           <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">Capture Customer Picture</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                 <span aria-hidden="true">&times;</span>
                 </button>
              </div>
              <div class="modal-body">
                <video id="video" class="rounded border" width="100%" height="300" autoplay></video>
                <canvas id="canvas" style="display: none;"></canvas>
              </div>
              <div class="modal-footer">
                 <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                 <button type="button" id="snap" data-name="customer" class="btn btn-success">Capture Photo</button>
                </div>
           </div>
        </div>
     </div>
      <!-- Modal -->
      <div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
           <div class="modal-content">
              <div class="modal-header">
                 <h5 class="modal-title" id="productModalLabel">Capture Product Picture</h5>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                 <span aria-hidden="true">&times;</span>
                 </button>
              </div>
              <div class="modal-body">
                <video id="pvideo" class="rounded border" width="100%" height="300" autoplay></video>
                <canvas id="pcanvas" style="display: none;"></canvas>
              </div>
              <div class="modal-footer">
                 <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                 <button id="psnap" data-name="product" class="btn btn-success">Capture Photo</button>
                </div>
           </div>
        </div>
     </div>
   

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
                                <input type="number" wire:model="noteDenominations.500" id="500"
                                    min="0" wire:keyup="calculateBreakdown">
                                <span> = {{ $noteDenominations[500] * 500 }}</span>
                            </div>
                            <div>
                                <label for="2000">2000 x </label>
                                <input type="number" wire:model="noteDenominations.2000" id="2000"
                                    min="0" wire:keyup="calculateBreakdown">
                                <span> = {{ $noteDenominations[2000] * 2000 }}</span>
                            </div>
                            <div>
                                <label for="200">200 x </label>
                                <input type="number" wire:model="noteDenominations.200" id="200"
                                    min="0" wire:keyup="calculateBreakdown">
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


            </div>
        </div>
    </div>
</div>
</div>
</div>

<script>
    window.addEventListener('show-cash-modal', () => {
        let modal = new bootstrap.Modal(document.getElementById('cashModal'));
        modal.show();
    });

    function openModal(type) {
        if (type === 'customer') {
            var modal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
        } else if (type === 'product') {
            var modal = new bootstrap.Modal(document.getElementById('addProductModal'));
        }
        modal.show();
    }
</script>

<script>
    navigator.mediaDevices.getUserMedia({
            video: true
        })
        .then(stream => {
            const videoElement = document.getElementById('video');
            if (videoElement) {
                videoElement.srcObject = stream;
            } else {
                console.log('Video element not found.');
            }
        })
        .catch(err => console.log('Error accessing webcam:', err));
    navigator.mediaDevices.getUserMedia({
            video: true
        })
        .then(stream => {
            const videoElement = document.getElementById('pvideo');
            if (videoElement) {
                videoElement.srcObject = stream;
            } else {
                console.log('Video element not found.');
            }
        })
        .catch(err => console.log('Error accessing webcam:', err));
    document.addEventListener('DOMContentLoaded', () => {
        const snapButton = document.getElementById('snap');
        const psnapButton = document.getElementById('psnap');
        if (snapButton) {
            snapButton.addEventListener('click', () => {
                const name = snapButton.getAttribute('data-name');
                const selectedUser = document.getElementById('commissionUser').value;
                if (!selectedUser) {
                    Swal.fire({
                        title: 'No User Selected',
                        text: 'Please select a Commission Customer before capturing the photo.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                const video = document.getElementById('video');
                const canvas = document.getElementById('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);

                canvas.toBlob(blob => {
                    const formData = new FormData();
                    formData.append('photo', blob, 'captured_image.png');
                    formData.append('type', name);
                    formData.append('selectedCommissionUser', document.getElementById(
                        'commissionUser').value);

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
                                Swal.fire({
                                    title: 'Customer Photo Uploaded!',
                                    text: 'Your photo has been uploaded successfully.',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                });
                               // document.getElementById('photo').value = data.path;
                               $('#customerModal').modal('hide');
                               $('#productModal').modal('hide');
                               $('#'+name).prop('disabled', true);

                               $('.modal-backdrop.show').remove();


                            } else {
                                alert('Upload failed!');
                            }
                        })
                        .catch(err => console.log(err));
                }, 'image/png');
            });
        }
        if (psnapButton) {
            psnapButton.addEventListener('click', () => {
                const name = psnapButton.getAttribute('data-name');
                const selectedUser = document.getElementById('commissionUser').value;
                if (!selectedUser) {
                    Swal.fire({
                        title: 'No User Selected',
                        text: 'Please select a Commission Customer before capturing the photo.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                const pvideo = document.getElementById('pvideo');
                const pcanvas = document.getElementById('pcanvas');
                pcanvas.width = pvideo.videoWidth;
                pcanvas.height = pvideo.videoHeight;
                pcanvas.getContext('2d').drawImage(pvideo, 0, 0);

                pcanvas.toBlob(blob => {
                    const formData = new FormData();
                    formData.append('photo', blob, 'captured_image.png');
                    formData.append('type', name);
                    formData.append('selectedCommissionUser', document.getElementById(
                        'commissionUser').value);

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
                                Swal.fire({
                                    title: 'Product Photo Uploaded!',
                                    text: 'Your photo has been uploaded successfully.',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                });
                                
                                $('#productModal').modal('hide');
                               $('#'+name).prop('disabled', true);

                               $('.modal-backdrop.show').remove();

                            } else {
                                alert('Upload failed!');
                            }
                        })
                        .catch(err => console.log(err));
                }, 'image/png');
            });
        }
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
</script>
