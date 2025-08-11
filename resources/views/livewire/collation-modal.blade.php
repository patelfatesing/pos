<div>
    <!-- Trigger Button -->
    <div class="" wire:click="openModal" title="Collect Credit"
        style="cursor: pointer;">
        <button type="button" class="btn p-1 m-0 border-0 bg-transparent">
            <img src="{{ asset('external/investment114471-sc1b.svg') }}" alt="Collect Credit Icon"
                style="width: 24px; height: 24px;" />
        </button>
        <span class="ic-txt">Collect Credit</span>
    </div>

    <!-- Main Modal -->
    @if ($showModal)
        <div class="modal d-block" tabindex="-1"
            style="background-color: rgba(0,0,0,0.5);"wire:keydown.escape="$set('showModal', false)">
            <div class="modal-dialog modal-xl modal-dialog-scrollable credits-modal-block">
                <div class="modal-content">
                    <div class="modal-header custom-modal-header">
                        <h5 class="modal-title">Collect Credit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Search Box -->
                        <div class="mb-3 width_300">
                            <input type="text" wire:model.live="search"
                                class="form-control frame-stock-request-searchbar6 Specificity: (0,1,0)"
                                placeholder="Search by name, phone, or credit points...">

                        </div>

                        <div class="table-responsive collect-credit-table">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light table-info">
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Mobile</th>
                                        {{-- <th>Email</th>
                                        <th>Address</th> --}}
                                        <th>Credit</th>
                                        <th>Used Credit</th>
                                        <th>Left Credit</th>
                                        <th>Status</th>
                                        <th>Action</th>
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
                                                        class="btn btn-sm collect-credit-frame2742">
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
                        <div class="d-flex justify-content-center pagination-tab">
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
            <div class="modal-dialog modal-lg modal-dialog-scrollable collect-credit-modal-dialog">
                <div class="modal-content">
                    <div class="modal-header custom-modal-header">
                        <h5 class="modal-title">Collect Credit for {{ $selectedUser?->first_name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="$set('showCollectModal', false)"></button>
                    </div>
                    <div class="modal-body">

                        <!-- Payment Method Selection -->
                        <div class="cash-upi-selection">
                            <!-- <label class="form-label fw-bold">Select Payment Method:</label> -->
                            <div class="d-flex gap-3">
                                <div class="form-check-selection">
                                    <input type="radio" wire:model="paymentType"
                                        wire:click="paymentTypeChanged('cash')" value="cash" id="pay_cash">
                                    <label for="pay_cash" class="text-dark">Cash</label>
                                </div>
                                <div class="form-check-selection">
                                    <input type="radio" wire:model="paymentType"
                                        wire:click="paymentTypeChanged('online')" value="online" id="pay_online">
                                    <label for="pay_online" class="text-dark">UPI</label>
                                </div>
                                <div class="form-check-selection">
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
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        @if (empty($this->selectedSalesReturn))
                                            <th class="text-center" style="width: 15%;">{{ __('messages.amount') }}</th>
                                            <th class="text-center" style="width: 25%;">{{ __('messages.in') }}</th>
                                        @endif
                                        <th class="text-center" style="width: 20%;">{{ __('messages.currency') }}</th>
                                        <th class="text-center" style="width: 25%;">{{ __('messages.out') }}</th>
                                        <th class="text-center" style="width: 15%;">
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
                                                <td class="text-center">
                                                    {{ format_inr($inValue * $denomination) }}
                                                </td>
                                                <td class="text-center">
                                                    <div
                                                        class="d-flex align-items-center counter-add-delete-area">
                                                        <button class="btn btn-gray rounded-start" style="width: 40%;"
                                                            wire:click="decrementNote('{{ $key }}', '{{ $denomination }}', 'in')">−</button>
                                                        <input type="text" value="{{ $inValue }}" readonly
                                                            class="form-control text-center rounded-0" style="width: 60px" />
                                                        <button class="btn btn-gray rounded-end" style="width: 40%;"
                                                            wire:click="incrementNote('{{ $key }}', '{{ $denomination }}', 'in')">+</button>
                                                    </div>
                                                </td>
                                            @endif

                                            <td class="text-center currency-center">{{ format_inr($denomination) }}
                                            </td>

                                            <td class="text-center">
                                                <div
                                                    class="d-flex align-items-center counter-add-delete-area">
                                                    <button class="btn btn-gray rounded-start" style="width: 40%;"
                                                        wire:click="decrementNote('{{ $key }}', '{{ $denomination }}', 'out')">−</button>
                                                    <input type="text" value="{{ $outValue }}" readonly
                                                        class="form-control text-center rounded-0" style="width: 60px" />
                                                    <button class="btn btn-gray rounded-end" style="width: 40%;"
                                                        wire:click="incrementNote('{{ $key }}', '{{ $denomination }}', 'out')">+</button>
                                                </div>
                                            </td>

                                            <td class="text-center">
                                                {{ format_inr($outValue * $denomination) }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    <tr class="table-secondary fw-bold total-summary-block">
                                        @if (empty($this->selectedSalesReturn))
                                            <td class="total_bgc text-center">{{ format_inr($totals['totalIn']) }}</td>
                                            <td class="total_bgc text-center">{{ $totals['totalInCount'] }}</td>
                                        @endif
                                        <td class="text-success text-center total_bgc">TOTAL</td>
                                        <td class="total_bgc text-center">{{ $totals['totalOutCount'] }}</td>
                                        <td class="total_bgc text-center">{{ format_inr($totals['totalOut']) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif

                        <!-- Online Payment Input -->
                        @if ($paymentType === 'online')
                            <div class="form-group mt-3">
                                <!-- <label for="onlineAmount">Enter Online Payment Amount</label> -->

                                <input type="number" wire:model="onlineAmount" wire:input="calculateTotal"
                                    class="form-control" id="onlineAmount" placeholder="Enter amount">
                            </div>
                        @endif

                        <!-- UPI Payment Input -->
                        @if ($paymentType === 'cash+upi')
                            <div class="form-group mt-3">
                                <!-- <label for="upiAmount">Enter UPI Payment Amount</label> -->
                                <input type="number" wire:model="upiAmount" wire:input="calculateTotal"
                                    class="form-control" id="upiAmount" placeholder="Enter UPI amount">
                            </div>
                        @endif


                        <!-- Total Collected Display -->
                        <div class="total-collected-amt">
                            <h5>
                                Total Collected Amount:
                                <span class="badge">
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
                        <div class="collect-credit-submit-btn">
                            <button wire:click="submitCredit" class="btn pull-right rounded-pill submit-btn w-100">Submit</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
