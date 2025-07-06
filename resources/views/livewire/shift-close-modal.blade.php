<div>
    <button type="button" class="btn btn-primary ml-2" wire:click.prevent="openModal" title="Close Shift">
        <i class="fas fa-door-closed"></i>
    </button>
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1">
            <div @class([
                'modal-dialog modal-dialog-scrollable modal-xl ',
                'modal-dialog modal-dialog-scrollable  close-modal-xl' =>
                    $this->showYesterDayShiftTime == true,
            ])>
                <div class="modal-content shadow-sm rounded-4 border-0">

                    {{-- Modal Header --}}
                    <div class="modal-header bg-primary text-white rounded-top-4">
                        <div class="d-flex flex-column">
                            <h5 class="modal-title fw-semibold">
                                <i class="bi bi-cash-coin me-2"></i> {{ $this->currentShift->shift_no ?? '' }} - Shift
                                Close Summary - {{ $branch_name ?? 'Shop' }}
                            </h5>
                        </div>
                        @if ($this->showYesterDayShiftTime == false && $this->shiftclosehidecross == false)
                        <button type="button" class="close" wire:click="$set('showModal', false)">
                            <span aria-hidden="true">×</span>
                        </button>
                        @endif
                    </div>

                    {{-- Modal Body --}}
                    <div class="modal-body px-4 py-4">
                        <form wire:submit.prevent="submit">
                            {{-- Hidden Fields --}}
                            <input type="hidden" wire:model="start_time">
                            <input type="hidden" wire:model="end_time">
                            <input type="hidden" wire:model="opening_cash">
                            <input type="hidden" wire:model="today_cash">
                            <input type="hidden" wire:model="total_payments">
                            <input type="hidden" wire:model="closing_sales">


                            {{-- Sales and Cash Section --}}
                            <div class="row g-4 mb-4">
                                {{-- Sales Breakdown --}}
                                <div class="col-md-6">
                                    <div class="card p-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h4 class="mb-0">Sales Details</h4>

                                            <button type="button" wire:click="openClosingStocksModal"
                                                class="btn btn-secondary btn-sm" title="View Stock Status">
                                                View Stock Status
                                            </button>
                                            <button type="button" wire:click="addphysicalStock"
                                                class="btn btn-secondary btn-sm" title="View Stock Status">
                                                Add Physical Stock
                                            </button>
                                            @if ($this->showYesterDayShiftTime)
                                                <button type="button" wire:click="removeHold"
                                                    class="btn btn-secondary btn-sm" title="View Stock Status">
                                                    Remove Hold
                                                </button>
                                            @endif
                                        </div>

                                        <hr class="mb-4">

                                        <div class="row">
                                            @foreach ($categoryTotals as $category => $items)
                                                @php
                                                    $isSummary = $category == 'summary';
                                                    $colClass = $isSummary ? 'col-12 mb-4' : 'col-md-6 mb-4';
                                                @endphp

                                                <div class="{{ $colClass }}">
                                                    <div class="card h-100 border-0 shadow-sm">
                                                        <div class="card-header bg-gradient bg-primary text-white">
                                                            <h5 class="mb-0 text-capitalize">{{ ucfirst($category) }}
                                                            </h5>
                                                        </div>
                                                        <div class="card-body p-0">
                                                            <table class="table mb-0">
                                                                <tbody>
                                                                    @foreach ($items as $key => $value)
                                                                        @php
                                                                            $isTotal = strtoupper($key) === 'TOTAL';
                                                                            $creditDetails =
                                                                                strtoupper($key) === 'CREDIT' ||
                                                                                strtoupper($key) === 'REFUND_CREDIT'
                                                                                    ? '(Excluded from Cash)'
                                                                                    : '';

                                                                            $rowClass = $isTotal
                                                                                ? 'table-success fw-bold'
                                                                                : '';
                                                                        @endphp
                                                                        <tr class="{{ $rowClass }}">
                                                                            <td class="text-muted text-capitalize">
                                                                                {{ str_replace('_', ' ', $key) }}
                                                                                <small>{{ @$creditDetails }}</small>
                                                                            </td>
                                                                            <td class="text-end fw-semibold">
                                                                                {{ format_inr($value) }}
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                </div>

                                {{-- Shift Timing and Cash Details --}}
                                <div class="col-md-6">
                                    <div class="card shadow-sm rounded-3">
                                        <div class="card-body p-4">
                                            {{-- Shift Timing --}}
                                            <div class="d-flex justify-content-between align-items-center mb-4">
                                                <div>
                                                    <div class="row text-left mt-2">
                                                        <div class="col-6 border-end">
                                                            <div class="small text-muted">Start Time</div>
                                                            <div class="fw-semibold">
                                                                {{ $this->currentShift->start_time ?? '-' }}
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="small text-muted">End Time</div>
                                                            <div class="fw-semibold">
                                                                {{ $this->currentShift->end_time ?? '-' }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- Shift Close Button --}}
                                                <div>
                                                    <button type="submit" class="btn btn-success btn-sm mt-3">
                                                        <i class="bi bi-check-circle me-1"></i> Close Shift
                                                    </button>
                                                </div>
                                                @if ($this->showYesterDayShiftTime)
                                                    <button type="button" class="btn btn-outline-danger  "
                                                        data-toggle="tooltip" data-placement="top" title="Logout"
                                                        onclick="confirmLogout()">
                                                        <span class="font-weight-bold"> {{ Auth::user()->name }}</span>
                                                        &nbsp;&nbsp;&nbsp;
                                                        <i class="fas fa-sign-out-alt"></i>
                                                    </button>


                                                    {{-- Logout Form --}}
                                                    <form id="logout-form" action="{{ route('logout') }}"
                                                        method="POST" style="display: none;">
                                                        @csrf
                                                    </form>
                                                @endif
                                            </div>
                                            <hr>
                                            {{-- Cash Breakdown --}}
                                            <h5 class="card-title text-warning text-left mb-3">💵 Cash Details</h5>

                                            <div class="table-responsive">
                                                <table
                                                    class="table table-bordered table-sm text-center align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Denomination</th>
                                                            <th>Notes</th>
                                                            <th>x</th>
                                                            <th>Amount</th>
                                                            <th>=</th>
                                                            <th>Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @if (!empty($shiftcash))
                                                            @php
                                                                $totalNotes = 0;
                                                            @endphp
                                                            @foreach ($shiftcash as $denomination => $quantity)
                                                                @php
                                                                    $rowTotal = $denomination * $quantity;
                                                                    $totalNotes += $rowTotal;
                                                                @endphp
                                                                <tr>
                                                                    <td class="fw-bold">{{ format_inr($denomination) }}
                                                                    </td>
                                                                    <td>{{ abs($quantity) }}</td>
                                                                    <td>X</td>
                                                                    <td>{{ format_inr($denomination) }}</td>
                                                                    <td>=</td>
                                                                    <td class="fw-bold">{{ format_inr($rowTotal) }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                    </tbody>
                                                    <tfoot class="table-light">
                                                        <tr>
                                                            <th colspan="5" class="text-end">Total</th>
                                                            <th class="fw-bold">
                                                                {{ format_inr(@$totalNotes) }}
                                                            </th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>

                                            {{-- Summary Cash Totals --}}
                                            <div class="table-responsive mt-4">
                                                <table class="table table-sm">
                                                    <tbody>
                                                        <tr>
                                                            <td class="text-start fw-bold">System Cash Sales</td>
                                                            <td class="text-end">{{ format_inr($totalNotes ?? 0) }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-start fw-bold">Total Cash Amount</td>
                                                            <td class="text-end">
                                                                {{ format_inr(@$this->categoryTotals['summary']['TOTAL']) }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-start fw-bold">Closing Cash</td>
                                                            <td class="text-end">
                                                                <input type="number"
                                                                    wire:model.live.debounce.500ms="closingCash"
                                                                    wire:change="calculateDiscrepancy"
                                                                    class="form-control @error('closingCash') is-invalid @enderror"
                                                                    min="0" step="0.01"
                                                                    placeholder="Enter closing cash">
                                                                @error('closingCash')
                                                                    <div class="invalid-feedback">
                                                                        {{ $message }}
                                                                    </div>
                                                                @enderror
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-start fw-bold">Discrepancy Cash</td>
                                                            <td class="text-end">
                                                                <input type="text" wire:model="diffCash"
                                                                    class="form-control" readonly>
                                                            </td>
                                                        </tr>

                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        {{-- Modal backdrop --}}
    <div class="modal-backdrop fade show"></div>
    @endif
    
    @if ($showStockModal)
        <div class="modal fade @if ($showStockModal) show d-block @endif" tabindex="-1"
            style="z-index: 1056;" @if ($showStockModal) style="display: block;" @endif>
            <div class="modal-dialog modal-dialog-scrollable modal-xl">
                <div class="modal-content shadow rounded-3">

                    <div class="modal-header bg-secondary text-white">
                        <h5 class="modal-title">Closing Stock Status</h5>
                        <button type="button" class="close" wire:click="$set('showStockModal', false)">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        @if (!empty($this->stockStatus))
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Product</th>
                                            <th>Opening Stock</th>
                                            <th>Transferred IN</th>
                                            <th>Transferred OUT</th>
                                            <th>Sold Qty</th>
                                            <th>Closing Stock</th>
                                            <th>Physical Stock</th>
                                            <th>Difference In Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalOpening = $totalAdded = $totalTransferred = $totalSold = $totalClosing = $totalPhysical = $totalDifference = 0;
                                        @endphp

                                        @foreach ($this->stockStatus as $index => $item)
                                            @php
                                                $totalOpening += $item['opening_stock'];
                                                $totalAdded += $item['added_stock'];
                                                $totalTransferred += $item['transferred_stock'];
                                                $totalSold += $item['sold_stock'];
                                                $totalClosing += $item['closing_stock'];
                                                $totalPhysical += !empty($item['physical_stock'])
                                                    ? $item['physical_stock']
                                                    : 0;
                                                $totalDifference += $item['difference_in_stock'];
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $item['product']['name'] }}</td>
                                                <td>{{ $item['opening_stock'] }}</td>
                                                <td>{{ $item['added_stock'] }}</td>
                                                <td>{{ $item['transferred_stock'] }}</td>
                                                <td>{{ $item['sold_stock'] }}</td>
                                                <td>{{ $item['closing_stock'] }}</td>
                                                <td>{{ !empty($item['physical_stock']) ? $item['physical_stock'] : 0 }}
                                                </td>
                                                <td>{{ $item['difference_in_stock'] }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="fw-bold text-end">
                                            <td colspan="2" class="text-end">Total</td>
                                            <td>{{ $totalOpening }}</td>
                                            <td>{{ $totalAdded }}</td>
                                            <td>{{ $totalTransferred }}</td>
                                            <td>{{ $totalSold }}</td>
                                            <td>{{ $totalClosing }}</td>
                                            <td>{{ $totalPhysical }}</td>
                                            <td>{{ $totalDifference }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">No stock data available.</p>
                        @endif
                    </div>

                    <div class="modal-footer">


                        <button class="btn btn-outline-secondary" wire:click="closeStockModal">Close</button>
                    </div>
                </div>
            </div>
        </div>


        {{-- Backdrop --}}
        <div class="modal-backdrop fade show"></div>
    @endif

    @if ($showPhysicalModal)
        <div class="modal fade @if ($showPhysicalModal) show d-block @endif" id="showPhysicalModal"
            tabindex="-1" style="z-index: 1056;" @if ($showPhysicalModal) style="display: block;" @endif>
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content shadow rounded-3">

                    <div class="modal-header bg-secondary text-white">
                        <h5 class="modal-title">Add Physical Stock</h5>
                        <button type="button" class="close" wire:click="$set('showPhysicalModal', false)">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>

                    <div class="modal-body">
                         <!-- Search Box -->
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" 
                                       wire:model.live="search" 
                                       class="form-control" 
                                       placeholder="Search by product name..">
                            </div>
                        </div>
                        @if (!empty($this->addstockStatus))
                            <div class="table-responsive">
                                <form wire:submit.prevent="save" id="stockPhysicalForm">
                                    <table class="table table-bordered table-striped">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Product Name</th>
                                                <th>Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            <input type="hidden" wire:model="shft_id">
                                            @foreach ($this->addstockStatus as $index => $product)
                                                <tr>
                                                    <td class="border px-4 py-2">
                                                        {{ $product['product']['name'] }}
                                                    </td>
                                                    <td class="border px-4 py-2">
                                                        <input type="number"
                                                            wire:model="products.{{ $product['product_id'] }}.qty"
                                                            class="form-control">
                                                        @error("products.{$product['product_id']}.qty")
                                                            <span class="text-danger small">{{ $message }}</span>
                                                        @enderror
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    {{-- <button type="submit" class="btn btn-primary mt-3">Save</button> --}}
                                </form>

                            </div>
                        @else
                            <p class="text-muted">No stock data available.</p>
                        @endif
                    </div>

                    <div class="modal-footer flex-column align-items-stretch gap-3">

                        <div class="w-100">
                            <label class="form-label fw-bold mb-2">Physical Stock Image Capture</label>
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <div>
                                    <video id="webcam" width="200" height="150" autoplay
                                        class="border rounded"></video>
                                    <canvas id="canvas" width="200" height="150"
                                        style="display: none;"></canvas>
                                </div>
                                <div class="d-flex flex-column align-items-center gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                        onclick="takeSnapshot()">
                                        <i class="bi bi-camera"></i> Capture
                                    </button>
                                    @if ($capturedImage)
                                        <img src="{{ $capturedImage }}" class="img-thumbnail" width="100">
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 w-100">
                            <button type="submit" form="stockPhysicalForm" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save
                            </button>
                            <button class="btn btn-outline-secondary" wire:click="closePhyStockModal">
                                <i class="bi bi-x"></i> Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        {{-- Backdrop --}}
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
<script>
    window.addEventListener('test', (event) => {
        setTimeout(() => {
            let video = document.getElementById('webcam');
            let canvas = document.getElementById('canvas');
            let context = canvas.getContext('2d');

            // Start webcam
            if (navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({
                        video: true
                    })
                    .then(function(stream) {
                        video.srcObject = stream;
                    })
                    .catch(function(error) {
                        console.log("Webcam error: ", error);
                    });
            }

            // function takeSnapshot() {
            //     context.drawImage(video, 0, 0, canvas.width, canvas.height);
            //     let image_data_url = canvas.toDataURL('image/jpeg');
            //     Livewire.dispatch('setCapturedImage', { image: image_data_url });
            // }
            window.takeSnapshot = function() {
                if (!video || !canvas) return;
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageDataUrl = canvas.toDataURL('image/jpeg');
                //console.log('Captured Image Data URL:', imageDataUrl);
                Livewire.dispatch('setCapturedImage', {
                    image: imageDataUrl
                });
            };
        }, 300); // allow DOM to fully render
    });
</script>
