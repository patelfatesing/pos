<div>

    <button class="btn btn-default cameraBtnPdg" data-bs-toggle="modal" data-bs-target="#cameraModal"
        @if ($this->selectedSalesReturn == true) disabled @endif>
        <img src="{{ asset('external/camera114471-6eja.svg') }}" alt="Separator" class="cameraModalHht" />
    </button>

    <!-- Modal -->
    <div class="modal fade" id="cameraModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-xl">
            <div class="modal-content ">
                <div class="modal-header custom-modal-header">
                    <h5 class="cash-summary-text61">Capture Product and Customer Photos</h5>
                    <button type="button" class=" btn btn-default close" data-bs-dismiss="modal"
                        aria-label="Close">×</button>
                </div>
                @php
                    $hideCameraClass = '';
                    if (!empty($this->partyHoldPic['party_product']) && !empty($this->partyHoldPic['party_customer'])) {
                        $hideCameraClass = 'hideCamera';
                    }
                @endphp
                <div class="modal-body">
                <div class="row align-items-center mb-4">
                    {{-- Current Customer Info --}}
                    <div class="col-md-3 text-center">
                        @php
                            $customerImage = $this->partyStatic['pic'] ?? $this->commiStatic['pic'] ?? null;
                            $customerName = $this->partyStatic['first_name'] ?? $this->commiStatic['first_name'] ?? 'N/A';
                            $customerType = !empty($this->partyStatic['first_name']) ? 'Party Customer' : ' Customer';
                        @endphp

                        @if ($customerImage)
                            <img src="{{ asset('storage/' . $customerImage) }}" alt="{{ $customerType }}"
                                class="img-thumbnail shadow" style="width: 100px; height: 100px; object-fit: cover;">
                        @else
                            <img src="{{ asset('assets/images/anonymous.png') }}" alt="Default Customer"
                                class="img-thumbnail " style="width: 100px; height: 100px; object-fit: cover;">
                        @endif
                    </div>

                    <div class="col-md-3">
                        <div class="text-muted small">{{ $customerType }}</div>
                        <h5 class="text-primary mb-0">{{ $customerName }}</h5>
                    </div>

                    {{-- Previous Hold Images + Saved Preview --}}
                    <div class="col-md-6">
                        <div class="row g-2">
                            @foreach ($previousHoldImages ?? [] as $image)
                                <div class="col-6 text-center">
                                    <img src="{{ asset('storage/' . $image) }}" alt="Previous Hold"
                                        class="img-thumbnail shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                            @endforeach
                        </div>

                        @if ($storedPhotos)
                            <div class="card mt-3">
                                <div class="card-header py-2 px-3 bg-info text-white">
                                    <h6 class="mb-0">Last Saved Photos</h6>
                                </div>
                                <div class="card-body py-2 px-3">
                                    <div class="row">
                                        <div class="col-md-6 text-center">
                                            <strong>Product Photo:</strong><br>
                                            @if ($productPhotoUrl)
                                                <img src="{{ asset('storage/' . $productPhotoUrl) }}" alt="Product"
                                                    class="img-thumbnail mt-2" style="max-height: 100px;">
                                            @endif
                                        </div>
                                        <div class="col-md-6 text-center">
                                            <strong>Customer Photo:</strong><br>
                                            @if ($customerPhotoUrl)
                                                <img src="{{ asset('storage/' . $customerPhotoUrl) }}" alt="Customer"
                                                    class="img-thumbnail mt-2" style="max-height: 100px;">
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>


                    @if (!empty($this->partyHoldPic['party_product']) && !empty($this->partyHoldPic['party_customer']))
                        <div class="row alert alert-info mb-3 lastsavepic">
                            <div class="col-md-12">
                                <h6 class="mb-2">Last Hold Saved Photos:</h6>
                            </div>
                            <div class="col-md-6">
                                <strong>Product Photo :</strong>
                                @if (!empty($this->partyHoldPic['party_product']))
                                    <a href="javascript:void(0)" class="d-block">
                                        <img src="{{ asset('storage/' . $this->partyHoldPic['party_product']) }}"
                                            alt="Product" class="img-thumbnail mt-2" style="">
                                    </a>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <strong>Customer Photo:</strong>
                                @if (!empty($this->partyHoldPic['party_customer']))
                                    <a href="javascript:void(0)" class="d-block">
                                        <img src="{{ asset('storage/' . $this->partyHoldPic['party_customer']) }}"
                                            alt="Customer" class="img-thumbnail mt-2" style="">
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                    {{-- @elseif($this->showHoldImg && !empty($this->partyHoldPic['commission_product']) && !empty($this->partyHoldPic['commission_customer'])) --}}
                    @if (!empty($this->partyHoldPic['commission_product']) && !empty($this->partyHoldPic['commission_customer']))
                        <div class="row alert alert-info mb-3 lastsavepic">
                            <div class="col-md-12">
                                <h6 class="mb-2">Last Hold Saved Photos:</h6>
                            </div>
                            <div class="col-md-6">
                                <strong>Product Photo :</strong>
                                @if (!empty($this->partyHoldPic['commission_product']))
                                    <a href="javascript:void(0)" class="d-block">
                                        <img src="{{ asset('storage/' . $this->partyHoldPic['commission_product']) }}"
                                            alt="Product" class="img-thumbnail mt-2" style="">
                                    </a>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <strong>Customer Photo:</strong>
                                @if (!empty($this->partyHoldPic['commission_customer']))
                                    <a href="javascript:void(0)" class="d-block">
                                        <img src="{{ asset('storage/' . $this->partyHoldPic['commission_customer']) }}"
                                            alt="Customer" class="img-thumbnail mt-2" style="">
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                    {{-- @else  --}}

                    @if (session()->has('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div id="cameraError" class="alert alert-danger d-none" role="alert">
                        Unable to access camera. Please ensure camera permissions are granted.
                    </div>

                    <hr style="width: 90%;margin-left: 5%;">
                    <div class="row mb-3 {{ $hideCameraClass }}">
                        <div class="col-md-6 text-center">
                            <h6 class="text-gray">Live Camera</h6>
                            <div class="position-relative">
                                <div id="loadingIndicator" class="position-absolute top-50 start-50 translate-middle">
                                    {{-- <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div> --}}
                                </div>
                                <video id="video" autoplay playsinline width="320" height="240"
                                    class="border"></video>
                                <canvas id="canvas" width="320" height="240" class="d-none"></canvas>
                            </div>
                            <div class="mt-3">
                                <button id="captureProduct" class="btn btn-outline-success btn-sm rounded-pill">
                                    Capture Product
                                </button>
                                <button id="captureCustomer" class="btn btn-outline-info btn-sm rounded-pill">
                                    Capture Customer
                                </button>
                            </div>
                        </div>

                        <div class="col-md-3 text-center"> 
                            <h6 class="text-gray">Product Preview</h6>
                            <div class="preview-container position-relative" style="min-height: 240px">
                                <div wire:loading wire:target="productPhoto"
                                    class="position-absolute top-50 start-50 translate-middle">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                                @error('productPhoto')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                <div id="productPreview" class="position-relative">
                                    @if ($productPhoto)
                                        <img src="{{ $productPhoto->temporaryUrl() }}" class="img-fluid"
                                            style="max-height: 240px">
                                        <button wire:click="resetPhoto('product')" class="btn btn-sm btn-danger rounded-pill remove-pic">
                                            Remvove Photo
                                        </button>
                                    @else
                                        {{-- <div class="border d-flex align-items-center justify-content-center" style="height: 240px">
                                                <span class="text-muted">No product photo</span>
                                            </div> --}}

                                        <img src="{{ asset('assets/images/bottle.png') }}" alt="Sample Product"
                                            class="img-fluid" style="max-height: 240px">
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 text-center">
                            <h6 class="text-gray">Customer Preview</h6>
                            <div class="preview-container position-relative" style="min-height: 240px">
                                {{-- <div wire:loading wire:target="customerPhoto" class="position-absolute top-50 start-50 translate-middle">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div> --}}
                                @error('customerPhoto')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                <div id="customerPreview" class="position-relative">
                                    @if ($customerPhoto)
                                        <img src="{{ $customerPhoto->temporaryUrl() }}" class="img-fluid"
                                            style="max-height: 240px">
                                        <button wire:click="resetPhoto('customer')" class="btn btn-sm btn-danger rounded-pill remove-pic">
                                            Remvove Photo
                                        </button>
                                    @else
                                        {{-- <div class="border d-flex align-items-center justify-content-center" style="height: 240px">
                                                <span class="text-muted">No customer photo</span>
                                            </div> --}}
                                        <img src="{{ asset('assets/images/user/07.jpg') }}" alt="Sample Product"
                                            class="img-fluid" style="max-height: 240px">
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">

                        </div>

                    </div>
                    {{-- @endif --}}
                </div>

                <input type="file" wire:model="productPhoto" id="productInput" class="d-none"
                    accept="image/*" />
                <input type="file" wire:model="customerPhoto" id="customerInput" class="d-none"
                    accept="image/*" />
                @if (empty($this->partyHoldPic['party_product']) &&
                        empty($this->partyHoldPic['party_customer']) &&
                        empty($this->partyHoldPic['commission_product']) &&
                        empty($this->partyHoldPic['commission_customer']) &&
                        $this->showHoldImg == false)
                    <div class="modal-footer">
                        <button wire:click="resetAll" class="btn btn-outline-secondary rounded-pill" type="button">
                            Reset All
                        </button>
                        <button wire:click="save" class="btn btn-primary rounded-pill"
                            @if (!$canSave) disabled @endif>
                            {{-- <span wire:loading wire:target="save"
                                class="spinner-border spinner-border-sm me-1"></span> --}}
                            Save Both Photos
                        </button>
                        {{-- <button type="button" class="btn btn-secondary rounded-pill"
                            data-bs-dismiss="modal">Close</button> --}}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cameraModal = document.getElementById('cameraModal');

        cameraModal.addEventListener('shown.bs.modal', function() {
            Livewire.dispatch('setImg');
            const partyUser = document.getElementById('partyUser') ? document.getElementById(
                'partyUser').value : '';
            const commissionUser = document.getElementById('commissionUser') ? document.getElementById(
                'commissionUser').value : '';
            Livewire.dispatch('handleSetImg', {
                partyUser: partyUser,
                commissionUser: commissionUser
            });
        });
    });
</script>
