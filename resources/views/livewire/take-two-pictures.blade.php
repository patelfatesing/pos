<div>

    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cameraModal">
        <i class="fas fa-camera me-2"></i>

    </button>

    <!-- Modal -->
    <div class="modal fade" id="cameraModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-xl">
            <div class="modal-content p-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Capture Product and Customer Photos</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>

                <div class="modal-body">
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
                 

                    @if($storedPhotos)
                    <div class="row alert alert-info mb-3 lastsavepic" >
                        <div class="col-md-12">
                            <h6 class="mb-2">Last Saved Photos:</h6>
                        </div>
                        <div class="col-md-3">
                            <strong>Product Photo:</strong>
                            @if($productPhotoUrl)
                                <a href="javascript:void(0)"  class="d-block">
                                    <img src="{{ asset('storage/' . $productPhotoUrl) }}" alt="Product" class="img-thumbnail mt-2" style="max-height: 100px">
                                </a>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <strong>Customer Photo:</strong>
                            @if($customerPhotoUrl)
                                <a href="javascript:void(0)"  class="d-block">
                                    <img src="{{ asset('storage/' . $customerPhotoUrl) }}" alt="Customer" class="img-thumbnail mt-2" style="max-height: 100px">
                                </a>
                            @endif
                        </div>
                    </div>
                     
                    @endif
                    <div class="row mb-3">
                        <div class="col-md-6 text-center">
                            <h6>Live Camera</h6>
                            <div class="position-relative">
                                <div id="loadingIndicator" class="position-absolute top-50 start-50 translate-middle">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                                <video id="video" autoplay playsinline width="320" height="240" class="border"></video>
                                <canvas id="canvas" width="320" height="240" class="d-none"></canvas>
                            </div>
                            <div class="mt-3">
                                <button id="captureProduct" class="btn btn-outline-success" >
                                    📷 Capture Product
                                </button>
                                <button id="captureCustomer" class="btn btn-outline-info" >
                                    📷 Capture Customer
                                </button>
                            </div>
                        </div>

                        <div class="col-md-3 text-center">
                            <h6>Product Preview</h6>
                            <div class="preview-container position-relative" style="min-height: 240px">
                                <div wire:loading wire:target="productPhoto" class="position-absolute top-50 start-50 translate-middle">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                                @error('productPhoto') 
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                <div id="productPreview" class="position-relative">
                                    @if ($productPhoto)
                                        <img src="{{ $productPhoto->temporaryUrl() }}" class="img-fluid" style="max-height: 240px">
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
                                        <img src="{{ $customerPhoto->temporaryUrl() }}" class="img-fluid" style="max-height: 240px">
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
                        <br>
                        <div id="supPreview" class="col-md-4 text-center">
                            <hr>
                       </div>
                       <div id="commissionPreview" class="col-md-4 text-center">
                            <hr>
                       </div>
                    </div>
                </div>

                <input type="file" wire:model="productPhoto" id="productInput" class="d-none" accept="image/*" />
                <input type="file" wire:model="customerPhoto" id="customerInput" class="d-none" accept="image/*" />

                <div class="modal-footer">
                    <button wire:click="resetAll" class="btn btn-outline-secondary" type="button">
                        Reset All
                    </button>
                    <button wire:click="save" class="btn btn-primary" @if(!$canSave) disabled @endif>
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                        Save Both Photos
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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
                            width: { ideal: 1280 },
                            height: { ideal: 720 },
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
                    const previewContainer = target === 'product' ? this.productPreview : this.customerPreview;
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
                            const file = new File([blob], `${target}-${Date.now()}.jpg`, { 
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
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cameraModal = document.getElementById('cameraModal');

    cameraModal.addEventListener('shown.bs.modal', function () {
        // Get the value of partyUser from a hidden input or data attribute
        // Example: <input type="hidden" id="partyUser" value="{{ $selectedPartyUser }}">
        const partyUser = document.getElementById('partyUser') ? document.getElementById('partyUser').value : '';
        // If you want to append an image based on partyUser value
        if (partyUser) {
            // Try .jpg first, then .jpeg, then .png if previous does not exist
            let imgUrl = `/storage/party_user_photos/${partyUser}_partyuser.jpg`;
            const tempImg = new Image();
            tempImg.onload = function() {
                // .jpg exists, use it
                appendPartyUserImage(imgUrl);
            };
            tempImg.onerror = function() {
                // .jpg does not exist, try .jpeg
                imgUrl = `/storage/party_user_photos/${partyUser}_partyuser.jpeg`;
                const tempImg2 = new Image();
                tempImg2.onload = function() {
                    appendPartyUserImage(imgUrl);
                };
                tempImg2.onerror = function() {
                    // .jpeg does not exist, try .png
                    imgUrl = `/storage/party_user_photos/${partyUser}_partyuser.png`;
                    appendPartyUserImage(imgUrl);
                };
                tempImg2.src = imgUrl;
            };
            tempImg.src = imgUrl;
            tempImg.src = imgUrl;

            function appendPartyUserImage(url) {
                const container = document.getElementById('supPreview');
                if (container) {
                    // Remove previous image if exists
                    const oldImg = container.querySelector('.party-user-img');
                    if (oldImg) oldImg.remove();

                    // Remove old label if exists
                    const oldLabel = container.querySelector('.party-user-label');
                    if (oldLabel) oldLabel.remove();

                    // Create label
                    const label = document.createElement('div');
                    label.className = 'mt-2 mb-1 text-muted small party-user-label';
                    label.textContent = 'Party Customer Photo';
                    container.appendChild(label);

                    // Create and append image
                    const img = document.createElement('img');
                    img.src = url;
                    img.className = 'img-fluid party-user-img mt-2';
                    img.style.maxHeight = '120px';
                    img.alt = 'Party User';

                    // If image fails to load, show "No image"
                    img.onerror = function() {
                        img.remove();
                        // Remove any previous "no image" message
                        const oldNoImg = container.querySelector('.party-user-noimg');
                        if (oldNoImg) oldNoImg.remove();
                        const noImg = document.createElement('div');
                        noImg.className = 'text-danger small mt-2 party-user-noimg';
                        noImg.textContent = 'No image';
                        container.appendChild(noImg);
                    };

                    container.appendChild(img);
                }
            }
        }
        const commissionUser = document.getElementById('commissionUser') ? document.getElementById('commissionUser').value : '';
        // If you want to append an image based on commissionUser value
        if (commissionUser) {
            // You can construct the image URL as needed
            // Try .jpg first, then .png if .jpg does not exist
            let imgUrl = `/storage/commission_photos/${commissionUser}_commissionuser.jpg`;
            // Create a temporary image to check if .jpg exists
            const tempImg = new Image();
            tempImg.onload = function() {
                // .jpg exists, use it
                appendCommissionImage(imgUrl);
            };
            tempImg.onerror = function() {
                // .jpg does not exist, try .jpeg
                imgUrl = `/storage/commission_photos/${commissionUser}_commissionuser.jpeg`;
                const tempImg2 = new Image();
                tempImg2.onload = function() {
                    appendCommissionImage(imgUrl);
                };
                tempImg2.onerror = function() {
                    // .jpeg does not exist, try .png
                    imgUrl = `/storage/commission_photos/${commissionUser}_commissionuser.png`;
                    appendCommissionImage(imgUrl);
                };
                tempImg2.src = imgUrl;
            };
            tempImg.src = imgUrl;

            function appendCommissionImage(url) {
                const container = document.getElementById('commissionPreview');
                if (container) {
                    // Remove previous image if exists
                    const oldImg = container.querySelector('.commission-user-img');
                    if (oldImg) oldImg.remove();

                    // Remove old label if exists
                    const oldLabel = container.querySelector('.commission-user-label');
                    if (oldLabel) oldLabel.remove();

                    // Remove any previous "no image" message
                    const oldNoImg = container.querySelector('.commission-user-noimg');
                    if (oldNoImg) oldNoImg.remove();

                    // Create label
                    const label = document.createElement('div');
                    label.className = 'mt-2 mb-1 text-muted small commission-user-label';
                    label.textContent = 'Commission User Photo';
                    container.appendChild(label);

                    // Create and append the image
                    const img = document.createElement('img');
                    img.src = url;
                    img.className = 'img-fluid commission-user-img mt-2';
                    img.style.maxHeight = '120px';
                    img.alt = 'Commission User';

                    // If image fails to load, show "No image"
                    img.onerror = function() {
                        img.remove();
                        // Remove any previous "no image" message
                        const oldNoImg = container.querySelector('.commission-user-noimg');
                        if (oldNoImg) oldNoImg.remove();
                        const noImg = document.createElement('div');
                        noImg.className = 'text-danger small mt-2 commission-user-noimg';
                        noImg.textContent = 'No image';
                        container.appendChild(noImg);
                    };

                    container.appendChild(img);
                }
            }
         
        }
        
    });
});
</script>
</script>
