<div>
    <!-- Trigger Button -->
    <div class="" wire:click="openCollectModal" title="{{ __('messages.cash_out') }}" style="cursor: pointer;"
        data-placement="top">

        <button class="btn btn-default p-1 m-0 border-0 bg-transparent">
            <img src="{{ asset('public/external/vector4471-k5i.svg') }}" alt="Cash Out Icon"
                style="width: 20px; height: 20px;" />
        </button>

        <span class="">Add Cash</span>
    </div>

    <!-- Modal -->
    @if ($showCollectModal)
        <div class="modal d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-dark">Add Case</h5>
                        
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            wire:click="$set('showCollectModal', false)"></button>
                    </div>

                    <div class="modal-body">

                        <!-- Table -->
                        <table class="customtable table table-bordered">
                            <thead>
                                <tr>
                                    <th>Amount</th>
                                    <th>In</th>
                                    <th>Currency</th>
                                    <th>Out</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($noteDenominations as $key => $denomination)
                                    @php
                                        $inValue = $cashNotes[$key][$denomination]['in'] ?? 0;
                                        $outValue = $cashNotes[$key][$denomination]['out'] ?? 0;
                                    @endphp
                                    <tr>
                                        <td>{{ format_inr($inValue * $denomination) }}</td>
                                        <td>
                                            <div class="cash-adjust mx-auto">
                                                <button
                                                    wire:click="decrementNote('{{ $key }}', '{{ $denomination }}', 'in')">−</button>
                                                <input type="number" value="{{ $inValue }}" readonly>
                                                <button
                                                    wire:click="incrementNote('{{ $key }}', '{{ $denomination }}', 'in')">+</button>
                                            </div>
                                        </td>
                                        <td class="bg-light">{{ format_inr($denomination) }}</td>
                                        <td>
                                            <div class="cash-adjust mx-auto">
                                                <button
                                                    wire:click="decrementNote('{{ $key }}', '{{ $denomination }}', 'out')">−</button>
                                                <input type="number" value="{{ $outValue }}" readonly>
                                                <button
                                                    wire:click="incrementNote('{{ $key }}', '{{ $denomination }}', 'out')">+</button>
                                            </div>
                                        </td>
                                        <td>{{ format_inr($outValue * $denomination) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="table-secondary fw-bold">
                                    <td>{{ format_inr($totals['totalIn']) }}</td>
                                    <td>{{ $totals['totalInCount'] }}</td>
                                    <td class="text-success">TOTAL</td>
                                    <td>{{ $totals['totalOutCount'] }}</td>
                                    <td>{{ format_inr($totals['totalOut']) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Transaction Type & Total Amount -->
                        <div class="row align-items-center mt-3 px-2">
                            <div class="col-md-6 d-flex align-items-center">
                                <label class="fw-semibold me-2 mb-0 text-dark">Transaction Type:</label>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input custom-radio" type="radio"
                                        wire:model="transactionType" wire:click="setTransactionType('change')"
                                        name="transactionType" id="change" value="change">
                                    <label class="form-check-label  text-dark" for="change">Change</label>
                                </div>
                                <div class="form-check form-check-inline mb-0">
                                    <input class="form-check-input custom-radio" type="radio"
                                        wire:model="transactionType" wire:click="setTransactionType('add')"
                                        name="transactionType" id="addMoney" value="add">
                                    <label class="form-check-label  text-dark" for="addMoney">Add Money</label>
                                </div>
                            </div>

                            <div class="col-md-6 text-end">
                                <span class="fw-semibold text-dark">Total Amount:</span>
                                <span class="badge bg-orange fs-6">{{ $this->totalCollected }}</span>
                            </div>
                        </div>

                        <!-- Submit -->
                        @if ($this->totalCollected >= 0)
                            <button wire:click="submitCredit" class="btn pull-left frame-stock-request-group223">
                                <i class="fas fa-paper-plane me-1"></i>
                                SUBMIT
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
