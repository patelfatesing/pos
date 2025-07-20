<div>

    <button class="btn btn-default cameraBtnPdg" data-bs-toggle="modal" data-bs-target="#cameraModal"
        @if ($this->selectedSalesReturn == true) disabled @endif>
        <img src="{{ asset('public/external/camera114471-6eja.svg') }}" alt="Separator" class="cameraModalHht" />
    </button>

    <!-- Modal -->
    <div class="modal fade" id="cameraModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-xl">
            <div class="modal-content p-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Capture Product and Customer Photos</h5>
                    <button type="button" class=" btn btn-default close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                @php
                    $hideCameraClass = '';
                    if (!empty($this->partyHoldPic['party_product']) && !empty($this->partyHoldPic['party_customer'])) {
                        $hideCameraClass = 'hideCamera';
                    }
                @endphp
                <div class="modal-body">
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


                    @if ($storedPhotos)
                        <div class="row alert alert-info mb-3 lastsavepic">
                            <div class="col-md-12">
                                <h6 class="mb-2">Last Saved Photos:</h6>
                            </div>
                            <div class="col-md-3">
                                <strong>Product Photo:</strong>
                                @if ($productPhotoUrl)
                                    <a href="javascript:void(0)" class="d-block">
                                        <img src="{{ asset('storage/' . $productPhotoUrl) }}" alt="Product"
                                            class="img-thumbnail mt-2" style="max-height: 100px">
                                    </a>
                                @endif
                            </div>
                            <div class="col-md-3">
                                <strong>Customer Photo:</strong>
                                @if ($customerPhotoUrl)
                                    <a href="javascript:void(0)" class="d-block">
                                        <img src="{{ asset('storage/' . $customerPhotoUrl) }}" alt="Customer"
                                            class="img-thumbnail mt-2" style="max-height: 100px">
                                    </a>
                                @endif
                            </div>
                        </div>

                    @endif
                    <div class="row mb-3 {{ $hideCameraClass }}">
                        <div class="col-md-6 text-center">
                            <h6>Live Camera</h6>
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
                                <button id="captureProduct" class="btn btn-outline-success">
                                    📷 Capture Product
                                </button>
                                <button id="captureCustomer" class="btn btn-outline-info">
                                    📷 Capture Customer
                                </button>
                            </div>
                        </div>

                        <div class="col-md-3 text-center">
                            <h6>Product Preview</h6>
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
                                        <button wire:click="resetPhoto('product')" class="btn btn-sm btn-danger">
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
                            <h6>Customer Preview</h6>
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
                                        <button wire:click="resetPhoto('customer')" class="btn btn-sm btn-danger">
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
                        @if (!empty($this->partyStatic['pic']))
                            <div id="supPreview" class="col-md-4 text-center">
                                <hr>
                                <label for="">{{ $this->partyStatic['first_name'] }}</label>
                                <img src="{{ asset('storage/' . $this->partyStatic['pic']) }}" alt="Customer"
                                    class="img-thumbnail mt-2" style="">
                            </div>
                        @endif
                        @if (!empty($this->commiStatic['pic']))
                            <div id="commissionPreview" class="col-md-4 text-center">
                                <hr>
                                <label for="">{{ $this->commiStatic['first_name'] }}</label>
                                <img src="{{ asset('storage/' . $this->commiStatic['pic']) }}" alt="Customer"
                                    class="img-thumbnail mt-2" style="">

                            </div>
                        @endif
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
                        <button wire:click="resetAll" class="btn btn-outline-secondary" type="button">
                            Reset All
                        </button>
                        <button wire:click="save" class="btn btn-primary"
                            @if (!$canSave) disabled @endif>
                            {{-- <span wire:loading wire:target="save"
                                class="spinner-border spinner-border-sm me-1"></span> --}}
                            Save Both Photos
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
