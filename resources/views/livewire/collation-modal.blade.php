<div>
    <!-- Trigger Button -->
    <div class="" wire:click="openModal" title="Collect Credit"
        style="cursor: pointer;">
        <button type="button" class="btn p-1 m-0 border-0 bg-transparent">
            <img src="{{ asset('external/investment114471-sc1b.svg') }}" alt="Collect Credit Icon"
                style="width: 24px; height: 24px;" />
        </button>
        <span class="">Collect Credit</span>
    </div>

    <!-- Main Modal -->
    @if ($showModal)
        <div class="modal d-block" tabindex="-1"
            style="background-color: rgba(0,0,0,0.5);"wire:keydown.escape="$set('showModal', false)">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title cash-summary-text61">Collect Credit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Search Box -->
                        <div class="mb-3 width_300">
                            <input type="text" wire:model.live="search"
                                class="form-control frame-stock-request-searchbar6 Specificity: (0,1,0)"
                                placeholder="Search by name, phone, or credit points...">

                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light table-info">
                                    <tr>
                                        <th class="main-screen-text25">#</th>
                                        <th class="main-screen-text25">Name</th>
                                        <th class="main-screen-text25">Mobile</th>
                                        {{-- <th class="main-screen-text25">Email</th>
                                        <th class="main-screen-text25">Address</th> --}}
                                        <th class="main-screen-text25">Credit</th>
                                        <th class="main-screen-text25">Used Credit</th>
                                        <th class="main-screen-text25">Left Credit</th>
                                        <th class="main-screen-text25">Status</th>
                                        <th class="main-screen-text25">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($partyUsers as $index => $user)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $user->first_name }}</td>
                                            <td>{{ $user->mobile_number }}</td>
                                            {{-- <td>{{ $user->email }}</td>
                                            <td>{{ $user->address }}</td> --}}
                                            <td>{{ number_format($user->credit_points, 2) }}</td>
                                            <td>{{ number_format($user->use_credit, 2) }}</td>
                                            <td>{{ number_format($user->left_credit, 2) }}</td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $user->payment_status)) }}</td>

                                            <td>
                                                @if (number_format($user->use_credit, 2) != '0.00')
                                                    <button wire:click="openCollectModal({{ $user->id }})"
                                                        class="btn btn-primary">
                                                        <i class="fa fa-money-bill"></i> Collect
                                                    </button>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">
                                                @if ($search)
                                                    No users found matching "{{ $search }}"
                                                @else
                                                    No users available
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $partyUsers->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Collect Credit Modal -->
    @if ($showCollectModal)
        <div class="modal d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-dark">Collect Credit for {{ $selectedUser?->first_name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="$set('showCollectModal', false)"></button>
                    </div>
                    <div class="modal-body">

                        <!-- Payment Method Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Payment Method:</label>
                            <div class="d-flex gap-3">
                                <div>
                                    <input type="radio" wire:model="paymentType"
                                        wire:click="paymentTypeChanged('cash')" value="cash" id="pay_cash">
                                    <label for="pay_cash" class="text-dark">Cash</label>&nbsp;&nbsp;&nbsp;&nbsp;

                                </div>
                                <div>

                                    <input type="radio" wire:model="paymentType"
                                        wire:click="paymentTypeChanged('online')" value="online" id="pay_online">
                                    <label for="pay_online" class="text-dark">UPI</label>&nbsp;&nbsp;&nbsp;&nbsp;
                                </div>
                                <div>

                                    <input type="radio" wire:model="paymentType"
                                        wire:click="paymentTypeChanged('cash+upi')" value="cash+upi" id="pay_cash_upi">
                                    <label for="pay_cash_upi" class="text-dark">Cash + UPI</label>
                                </div>
                            </div>

                            <!-- 🔴 Validation Error -->
                            @error('paymentType')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>



                        <!-- Cash Denomination Table -->
                        @if (in_array($paymentType, ['cash', 'cash+upi']))
                            <table class="customtable table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        @if (empty($this->selectedSalesReturn))
                                            <th>{{ __('messages.amount') }}</th>
                                            <th class="text-center">{{ __('messages.in') }}</th>
                                        @endif
                                        <th>{{ __('messages.currency') }}</th>
                                        <th class="text-center">{{ __('messages.out') }}</th>
                                        <th class="text-center">
                                            {{ __('messages.amount') }}
                                            <button wire:click="clearCashNotes" class="btn btn-danger btn-sm">
                                                <i class="fa fa-eraser"></i>
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($noteDenominations as $key => $denomination)
                                        @php
                                            $inValue = $cashNotes[$key][$denomination]['in'] ?? 0;
                                            $outValue = $cashNotes[$key][$denomination]['out'] ?? 0;
                                        @endphp
                                        <tr>
                                            @if (empty($this->selectedSalesReturn))
                                                <td class="text-center fw-bold">
                                                    {{ format_inr($inValue * $denomination) }}
                                                </td>
                                                <td class="text-center">
                                                    <div
                                                        class="cash-adjust d-flex justify-content-center align-items-center gap-2">
                                                        <button class="btn btn-sm btn-outline-secondary custom-btn"
                                                            wire:click="decrementNote('{{ $key }}', '{{ $denomination }}', 'in')">−</button>
                                                        <input type="number" value="{{ $inValue }}" readonly
                                                            class="form-control text-center cash-input" />
                                                        <button class="btn btn-sm btn-outline-secondary custom-btn"
                                                            wire:click="incrementNote('{{ $key }}', '{{ $denomination }}', 'in')">+</button>
                                                    </div>
                                                </td>
                                            @endif

                                            <td class="text-center bg-light fw-bold">{{ format_inr($denomination) }}
                                            </td>

                                            <td class="text-center">
                                                <div
                                                    class="cash-adjust d-flex justify-content-center align-items-center gap-2">
                                                    <button class="btn btn-sm btn-outline-secondary custom-btn"
                                                        wire:click="decrementNote('{{ $key }}', '{{ $denomination }}', 'out')">−</button>
                                                    <input type="number" value="{{ $outValue }}" readonly
                                                        class="form-control text-center cash-input" />
                                                    <button class="btn btn-sm btn-outline-secondary custom-btn"
                                                        wire:click="incrementNote('{{ $key }}', '{{ $denomination }}', 'out')">+</button>
                                                </div>
                                            </td>

                                            <td class="text-center fw-bold">
                                                {{ format_inr($outValue * $denomination) }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    <tr class="table-secondary tbl_footer_bgc fw-bold">
                                        @if (empty($this->selectedSalesReturn))
                                            <td class="text-center">{{ format_inr($totals['totalIn']) }}</td>
                                            <td class="text-center">{{ $totals['totalInCount'] }}</td>
                                        @endif
                                        <td class="text-center">TOTAL</td>
                                        <td class="text-center">{{ $totals['totalOutCount'] }}</td>
                                        <td class="text-center">{{ format_inr($totals['totalOut']) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif

                        <!-- Online Payment Input -->
                        @if ($paymentType === 'online')
                            <div class="form-group mt-3">
                                <label for="onlineAmount">Enter Online Payment Amount</label>

                                <input type="number" wire:model="onlineAmount" wire:input="calculateTotal"
                                    class="form-control" id="onlineAmount" placeholder="Enter amount">
                            </div>
                        @endif

                        <!-- UPI Payment Input -->
                        @if ($paymentType === 'cash+upi')
                            <div class="form-group mt-3">
                                <label for="upiAmount">Enter UPI Payment Amount</label>
                                <input type="number" wire:model="upiAmount" wire:input="calculateTotal"
                                    class="form-control" id="upiAmount" placeholder="Enter UPI amount">
                            </div>
                        @endif


                        <!-- Total Collected Display -->
                        <div class="text-end mt-3">
                            <h5>
                                Total Collected Amount:
                                <span class="badge bg-secondary">
                                    {{ $this->totalCollected }}
                                    {{-- @if ($paymentType === 'cash')
                                        {{ format_inr(($totals['totalIn'] ?? 0) - ($totals['totalOut'] ?? 0)) }}
                                    @elseif($paymentType === 'online')
                                        {{ format_inr($onlineAmount) }}
                                    @elseif($paymentType === 'cash+upi')
                                        {{ format_inr(($totals['totalIn'] ?? 0) - ($totals['totalOut'] ?? 0) + $upiAmount) }}
                                    @endif --}}
                                </span>
                            </h5>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-end">
                            <button wire:click="submitCredit" class="btn btn frame-stock-request-group223 mt-2">
                                Submit Collection
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
