<div>
    <div class="" wire:click.prevent="openModal" title="Close Shift" style="cursor: pointer;">
        <button type="button" class="btn btn-default p-1 m-0 border-0 bg-transparent">
            <img src="{{ asset('assets/images/sidebar-imgs/closeshit-img.svg') }}" alt="Close Shift Icon" />
        </button>
        <span class="ic-txt">Close Shift</span>
    </div>

    <style>
        .size-divider {
            border-right: 2px solid #dee2e6 !important;
        }

        .sticky-table thead th {
            position: sticky;
            top: 0;
            z-index: 5;
            background: #2f9e9e;
            color: #fff;
            font-size: 12px;
        }

        .stock-table-wrapper {
            max-height: 400px;
            overflow-y: auto;
        }
        .custom-xl-modal {
            max-width: 80%;
        }

        .sticky-table thead tr:nth-child(1) th {
            position: sticky;
            top: 0;
        }

        .sticky-table thead tr:nth-child(2) th {
            position: sticky;
            top: 30px;
        }

        .sticky-table input.form-control {
            width: 60px !important;
            padding: 2px 4px;
            font-size: 10px;
        }

        .stock-table-wrapper::-webkit-scrollbar {
            width: 14px; 
        }

        .stock-table-wrapper::-webkit-scrollbar-track {
            background: #e9ecef; 
            border-radius: 10px;
        }

        .stock-table-wrapper::-webkit-scrollbar-thumb {
            background-color: #2f9e9e; 
            border-radius: 10px;
            border: 3px solid #e9ecef; 
        }

        .stock-table-wrapper::-webkit-scrollbar-thumb:hover {
            background-color: #247a7a; 
        }

        .stock-table-wrapper {
            max-height: 550px; 
            overflow-y: auto;
            scrollbar-width: auto; /* Firefox mate */
            scrollbar-color: #2f9e9e #e9ecef; /* Firefox mate */
        }

    </style>

    @if ($showModal)
    <div class="modal fade show d-block roboto-fonts" tabindex="-1">
        <div @class([ 'modal-dialog modal-dialog-scrollable modal-xl' , 'modal-dialog modal-dialog-scrollable close-modal-xl'=> $this->showYesterDayShiftTime == true,
            ])>
            <div class="modal-content shadow-sm rounded-4 border-0">

                {{-- Modal Header --}}
                <div class="modal-header custom-modal-header">
                    <div class="d-flex flex-column">
                        <h5 class="modal-title fw-semibold cash-summary-text61">
                            <i class="bi bi-cash-coin me-2"></i> {{ $this->currentShift->shift_no ?? '' }} - Shift
                            Close Summary - {{ $branch_name ?? 'Shop' }}
                        </h5>
                    </div>
                    <button type="button" class="btn btn-light border ms-1" data-bs-toggle="tooltip"
                        title="Logout" onclick="confirmLogout()">
                        <img src="{{ asset('external/fi106093284471-0vjk.svg') }}" class="img-fluid"
                            style="height: 25px;" />
                    </button>
                    @if ($this->showYesterDayShiftTime == false && $this->shiftclosehidecross == false)
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        wire:click="$set('showModal', false)"></button>
                    @endif
                </div>

                {{-- Modal Body --}}
                <div class="modal-body px-4 py-4 close-shift-modal-body">
                    <form wire:submit.prevent="submit">
                        <input type="hidden" wire:model="start_time">
                        <input type="hidden" wire:model="end_time">
                        <input type="hidden" wire:model="opening_cash">
                        <input type="hidden" wire:model="today_cash">
                        <input type="hidden" wire:model="total_payments">
                        <input type="hidden" wire:model="closing_sales">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body close-shift-card-body">
                                        <div class="row">
                                            <div class="col-md-6 col-lg-12 col-xl-6 stock-status-btns">
                                                <button type="button" wire:click="openClosingStocksModal"
                                                    class="btn btn-primary rounded-pill"
                                                    title="View Stock Status">
                                                    View Stock Status
                                                </button>
                                                <button type="button" wire:click="addphysicalStock"
                                                    class="btn btn-warning rounded-pill"
                                                    title="View Stock Status">
                                                    Add Physical Stock
                                                </button>
                                                @if ($this->showYesterDayShiftTime)
                                                <button type="button" wire:click="removeHold"
                                                    class="btn btn-secondary rounded-pill remove_hold_btn"
                                                    title="View Stock Status">
                                                    Remove Hold
                                                </button>
                                                @endif
                                            </div>
                                            <div class="col-md-6 col-lg-12 col-xl-6">
                                                <div class="row status-time-area">
                                                    <div class="col-lg-6 col-md-12">
                                                        <div class="time-flex">
                                                            <h6 class="text-start close-text">Start Time</h6>
                                                            <h6 class="text-start close-text">
                                                                {{ $this->currentShift->start_time ?? '-' }}
                                                            </h6>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-12">
                                                        <div class="time-flex">
                                                            <h6 class="text-start close-text">End Time</h6>
                                                            <h6 class="text-start close-text">
                                                                {{ $this->currentShift->end_time ?? '-' }}
                                                            </h6>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>

                        <div class="row g-4">
                            <div class="col-lg-6 col-md-12">
                                <div class="row">
                                    @foreach ($categoryTotals as $category => $items)
                                    @php
                                    $isSummary = $category == 'summary';
                                    $colClass = $isSummary ? 'col-12' : 'col-md-6 mb-4';
                                    @endphp
                                    <div class="{{ $colClass }}">
                                        <div class="card shadow-sm">
                                            <div class="card-header custom-modal-header">
                                                <h5 class="mb-0 cash-summary-text61">{{ ucfirst($category) }}</h5>
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
                                                        ? 'table-success text-success fw-bold'
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

                            <div class="col-lg-6 col-md-12">
                                @if ($inOutStatus)
                                <div class="card cash-details_table">
                                    <div class="card-header custom-modal-header">
                                        <h5 class="mb-0 cash-summary-text61">Cash Details</h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table
                                                class="table table-bordered table-sm text-center align-middle mb-0">
                                                <thead class="submit-btn">
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
                                                    @php $totalNotes = 0; @endphp
                                                    @foreach ($shiftcash as $denomination => $quantity)
                                                    @php
                                                    $rowTotal = $denomination * $quantity;
                                                    $totalNotes += $rowTotal;
                                                    @endphp
                                                    <tr>
                                                        <td>{{ format_inr($denomination) }}</td>
                                                        <td>{{ abs($quantity) }}</td>
                                                        <td>X</td>
                                                        <td>{{ format_inr($denomination) }}</td>
                                                        <td>=</td>
                                                        <td class="fw-bold">{{ format_inr($rowTotal) }}</td>
                                                    </tr>
                                                    @endforeach
                                                    @endif
                                                </tbody>
                                                <tfoot class="border table-success fw-bold">
                                                    <tr>
                                                        <th colspan="5" class="text-end">Total</th>
                                                        <th class="fw-bold">{{ format_inr(@$totalNotes) }}</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="table-responsive mt-4 finalcash-details_table">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <td class="text-start">System Cash Sales</td>
                                                @if ($inOutStatus)
                                                <td class="text-end">{{ format_inr($totalNotes ?? 0) }}</td>
                                                @else
                                                <td class="text-end">{{ format_inr(@$this->categoryTotals['summary']['TOTAL']) }}</td>
                                                @endif
                                            </tr>
                                            <tr>
                                                <td class="text-start">Total Cash Amount</td>
                                                <td class="text-end">
                                                    {{ format_inr(@$this->categoryTotals['summary']['TOTAL']) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-start">Closing Cash</td>
                                                <td class="text-end">
                                                    <input type="number"
                                                        wire:model.live.debounce.500ms="closingCash"
                                                        wire:change="calculateDiscrepancy"
                                                        class="form-control rounded-pill @error('closingCash') is-invalid @enderror"
                                                        min="0" step="0.01"
                                                        placeholder="Enter closing cash">
                                                    @error('closingCash')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-start">Discrepancy Cash</td>
                                                <td class="text-end">
                                                    <input type="text" wire:model="diffCash"
                                                        class="form-control rounded-pill" readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-start">
                                                    @if ($this->showYesterDayShiftTime)
                                                    <button type="button" class="btn btn-outline-danger"
                                                        data-toggle="tooltip" data-placement="top"
                                                        title="Logout" onclick="confirmLogout()">
                                                        <span class="font-weight-bold">{{ Auth::user()->name }}</span>
                                                        &nbsp;&nbsp;&nbsp;
                                                        <i class="fas fa-sign-out-alt"></i>
                                                    </button>
                                                    <form id="logout-form" action="{{ route('logout') }}"
                                                        method="POST" style="display: none;">
                                                        @csrf
                                                    </form>
                                                    @endif
                                                </td>
                                                <td class="text-end"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="shift-close-btn-block">
                                    <div class="footer_btn_block">
                                        <button type="submit" class="btn rounded-pill reset-btn">Reset</button>
                                        <button type="submit" class="btn rounded-pill submit-btn">
                                            <i class="bi bi-check-circle me-1"></i> Close Shift
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif

    {{-- ==================== STOCK STATUS MODAL ==================== --}}
    @if ($showStockModal)
    <div class="modal fade show d-block" tabindex="-1" style="z-index: 1056;">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content shadow rounded-3">
                <div class="modal-header custom-modal-header">
                    <h5 class="modal-title cash-summary-text61">Closing Stock Status</h5>
                    <button type="button" class="btn-close" wire:click="$set('showStockModal', false)">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body closing-stock-modal-body">
                    @if (!empty($this->stockStatus))
                    <div class="table-responsive">
                        <table class="table table-bordered physical-table">
                            <thead class="table-info">
                                <tr>
                                    <th>#</th>
                                    <th class="text-start">Product</th>
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
                                $totalPhysical += !empty($item['physical_stock']) ? $item['physical_stock'] : 0;
                                $totalDifference += $item['difference_in_stock'];
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="text-start">{{ $item['product']['name'] }}</td>
                                    <td>{{ $item['opening_stock'] }}</td>
                                    <td>{{ $item['added_stock'] }}</td>
                                    <td>{{ $item['transferred_stock'] }}</td>
                                    <td>{{ $item['sold_stock'] }}</td>
                                    <td>{{ $item['closing_stock'] }}</td>
                                    <td>{{ !empty($item['physical_stock']) ? $item['physical_stock'] : 0 }}</td>
                                    <td>{{ $item['difference_in_stock'] }}</td>
                                </tr>
                                @endforeach
                                <tr class="border table-success table-success-new fw-bold">
                                    <td colspan="2" class="text-start">Total</td>
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
    <div class="modal-backdrop fade show"></div>
    @endif

    {{-- ==================== PHYSICAL STOCK MODAL ==================== --}}
    @if ($showPhysicalModal)
    <div class="modal fade show d-block" id="showPhysicalModal" tabindex="-1" style="z-index: 1056;">
        <div class="modal-dialog modal-dialog-scrollable modal-fullscreen py-4 px-5">
            <div class="modal-content shadow rounded-3">

                {{-- Header --}}
                <div class="modal-header custom-modal-header text-white">
                    <h5 class="modal-title cash-summary-text61">Add Physical Stock</h5>
                    <button type="button" class="close btn btn-default"
                        wire:click="$set('showPhysicalModal', false)">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                {{-- Body --}}
                <div class="modal-body">
                    <div class="product_search-panel">
                        <div class="row">

                            {{-- ✅ Category Tabs: Sirf non-warehouse branch mate --}}
                            @if ($branch_name != 'WAREHOUSE')
                            <ul class="nav nav-pills mb-3 nav-fill" role="tablist">
                                <li class="nav-item">
                                    <a href="#" wire:click.prevent="setCategory('IMFL')"
                                        class="nav-link {{ $selectedCategory == 'IMFL' ? 'active' : '' }}">
                                        <span class="fs-6 {{ $selectedCategory == 'IMFL' ? 'text-white' : 'text-dark' }}">IMFL</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" wire:click.prevent="setCategory('BEER')"
                                        class="nav-link {{ $selectedCategory == 'BEER' ? 'active' : '' }}">
                                        <span class="fs-6 {{ $selectedCategory == 'BEER' ? 'text-white' : 'text-dark' }}">BEER</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" wire:click.prevent="setCategory('CL_RML')"
                                        class="nav-link {{ $selectedCategory == 'CL_RML' ? 'active' : '' }}">
                                        <span class="fs-6 {{ $selectedCategory == 'CL_RML' ? 'text-white' : 'text-dark' }}">CL &amp; RML</span>
                                    </a>
                                </li>
                            </ul>
                            @endif

                            {{-- Search --}}
                            <div class="mb-3">
                                <div class="row align-items-end">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fa fa-search" aria-hidden="true"></i>
                                            </span>
                                            <input type="text" wire:model.live="search" class="form-control"
                                                placeholder="Search by product name..">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        @if (auth()->user()->hasRole('cashier'))
                                            <div class="position-relative">
                                                <select id="commissionUser" class="form-control rounded-pill custom-border w-100" wire:model="selectedCommissionUser">
                                                    <option value="">Select Commission Customer</option>
                                                    @foreach ($commissionUsers as $user)
                                                        <option value="{{ $user->id }}" {{ $selectedCommissionUser == $user->id ? 'selected' : '' }}>
                                                            {{ $user->first_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                        @if (auth()->user()->hasRole('warehouse'))
                                            <div class="position-relative">
                                                <select id="partyUser" class="form-control rounded-pill custom-border w-100" wire:model="selectedPartyUser">
                                                    <option value="">{{ __('messages.select_party_customer') }}</option>
                                                    @foreach ($partyUsers as $user)
                                                        <option value="{{ $user->id }}" {{ $selectedPartyUser == $user->id ? 'selected' : '' }}>
                                                            {{ $user->first_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <form wire:submit.prevent="save" id="stockPhysicalForm">
                                <input type="hidden" wire:model="shft_id">

                                {{-- ===================== WAREHOUSE UI ===================== --}}
                                @if ($branch_name == 'WAREHOUSE')

                                @if (!empty($this->addstockStatus))
                                <div class="table-responsive">
                                    <table class="table table-bordered physical-table mb-0">
                                        <thead class="table-info">
                                            <tr>
                                                <th>Product Name</th>
                                                <th>Opening</th>
                                                <th>Sales</th>
                                                <th>Physical</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($this->addstockStatus as $product)
                                            <tr>
                                                <td class="border">{{ $product['product']['name'] }}</td>
                                                <td class="border">{{ $product['opening_stock'] }}</td>
                                                <td class="border">{{ $product['sold_stock'] }}</td>
                                                <td class="border">
                                                    <input type="number"
                                                        wire:model="products.{{ $product['product_id'] }}.qty"
                                                        class="form-control rounded-pill">
                                                    @error("products.{$product['product_id']}.qty")
                                                    <span class="text-danger small">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="d-flex justify-content-center mt-1">
                                    <div class="form-check d-flex align-items-center gap-2">
                                        <input class="form-check-input start-zero-checkbox"
                                            wire:model="no_sale_product" type="checkbox"
                                            id="no_sale_product" name="no_sale_product" value="1">
                                        <label class="form-check-label mb-0">
                                            Save with no sale product
                                        </label>
                                        @error('no_sale_product')
                                        <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                @endif

                                {{-- ===================== NON-WAREHOUSE UI (Grouped by Name x Size) ===================== --}}
                                @else

                                @if (!empty($groupedProducts))
                                @php $sizes = $this->getSizes(); @endphp
                                <div class="table-responsive stock-table-wrapper">
                                    <table class="table table-bordered physical-table mb-0 sticky-table">
                                        <thead class="table-info">
                                          
                                            <tr>
                                                <th rowspan="2" class="align-center" style="min-width: 250px;max-width: 400px">Product Name</th>
                                                @foreach ($sizes as $size)
                                                <th colspan="3" class="text-center size-divider">{{ $size }}</th>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                @foreach ($sizes as $size)
                                                    <th class="text-center">Opening</th>
                                                    <th class="text-center">Sales</th>
                                                    <th class="text-center size-divider">Physical</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($groupedProducts as $product)
                                            <tr wire:key="row-{{ $selectedCategory }}-{{ $product['name'] }}">
                                                <td>{{ $product['name'] }}</td>

                                                @foreach ($sizes as $size)
                                                @if (isset($product['sizes'][$size]))
                                                <td class="text-center">
                                                    {{ $product['sizes'][$size]['opening'] }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $product['sizes'][$size]['sales'] }}
                                                </td>
                                                <td class="text-center size-divider">
                                                    <input type="number"
                                                        wire:key="input-{{ $selectedCategory }}-{{ $product['sizes'][$size]['product_id'] }}"
                                                        wire:model="products.{{ $selectedCategory }}.{{ $product['sizes'][$size]['product_id'] }}.qty"
                                                        class="form-control rounded-pill text-center"
                                                        style="width:80px; margin:auto;"
                                                        min="0">
                                                </td>
                                                @else
                                                <td class="text-center text-muted">-</td>
                                                <td class="text-center text-muted">-</td>
                                                <td class="text-center text-muted size-divider">-</td>
                                                @endif
                                                @endforeach
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" wire:model.live="cash" required
                                                class="form-control rounded-pill @error('cash') is-invalid @enderror"
                                                step="1" placeholder="Enter cash amount">
                                            @error('cash')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" wire:model.live="upi" required
                                                class="form-control rounded-pill @error('upi') is-invalid @enderror"
                                                 step="1" placeholder="Enter UPI amount">
                                            @error('upi')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" wire:model.live="roundedAmount"
                                                class="form-control rounded-pill @error('roundedAmount') is-invalid @enderror"
                                                min="0" step="1" placeholder="Enter rounded amount">
                                            @error('roundedAmount')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                @else
                                {{-- No products found --}}
                                <div class="d-flex justify-content-center mt-1">
                                    <div class="form-check d-flex align-items-center gap-2">
                                        <input class="form-check-input start-zero-checkbox"
                                            wire:model="no_sale_product" type="checkbox"
                                            id="no_sale_product" name="no_sale_product" value="1">
                                        <label class="form-check-label mb-0">
                                            Save with no sale product
                                        </label>
                                        @error('no_sale_product')
                                        <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                @endif

                                @endif
                            </form>
                        </div>
                    </div>

                    {{-- Camera Section --}}
                    <div class="capture-modal-block">
                        <div class="row">
                            <div class="col-md-4">
                                <video id="webcam" width="200" height="150" autoplay
                                    class="border rounded"></video>
                                <canvas id="canvas" width="200" height="150"
                                    style="display: none;"></canvas>
                            </div>
                            <div class="col-md-4">
                                <div class="align-items-center capture-img2">
                                    @if ($capturedImage)
                                    <img class="w-100" src="{{ $capturedImage }}">
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4"></div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary rounded-pill capture-btn"
                                onclick="takeSnapshot()">
                                <i class="bi bi-camera"></i> Capture
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="modal-footer flex-column align-items-stretch gap-3">
                    <div class="justify-content-end w-100">
                        <button type="submit" form="stockPhysicalForm"
                            class="btn submit-btn pull-right rounded-pill mr-2">
                            <i class="bi bi-save"></i> Submit
                        </button>
                        <button class="btn btn-outline-warning ml-2 rounded-pill pull-right"
                            wire:click="closePhyStockModal" style="margin-right: 12px !important;">
                            <i class="bi bi-x"></i> Close
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>

<script>
    window.addEventListener('test', (event) => {
        setTimeout(() => {
            let video = document.getElementById('webcam');
            let canvas = document.getElementById('canvas');

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

            window.takeSnapshot = function() {
                if (!video || !canvas) return;
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageDataUrl = canvas.toDataURL('image/jpeg');
                Livewire.dispatch('setCapturedImage', {
                    image: imageDataUrl
                });
            };
        }, 300);
    });
</script>
